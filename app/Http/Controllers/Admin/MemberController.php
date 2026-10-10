<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in(['member', 'admin'])],
            'tier_id' => [
                'nullable',
                'integer',
                Rule::exists('MEMBERSHIP_TIER', 'tier_id'),
            ],
        ]);

        $query = User::with('tier')
            ->withCount('orders')
            ->orderByDesc('member_id');

        $search = trim($input['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($input['role'])) {
            $query->where('role', $input['role']);
        }

        if (!empty($input['tier_id'])) {
            $query->where('tier_id', $input['tier_id']);
        }

        return view('admin.members.index', [
            'members' => $query->paginate(15)->withQueryString(),
            'tiers' => MembershipTier::orderBy('min_points')
                ->orderBy('tier_id')
                ->get(),
        ]);
    }

    public function edit(User $member): View
    {
        return view('admin.members.edit', [
            'member' => $member,
            'tiers' => MembershipTier::orderBy('min_points')
                ->orderBy('tier_id')
                ->get(),
            'version' => $this->version($member),
        ]);
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        DB::transaction(function () use ($request, $member) {
            $admins = $this->lockAdmins($request);

            $target = User::whereKey($member->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $input = $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('MEMBER', 'email')
                        ->ignore($target->getKey(), 'member_id'),
                ],
                'phone' => ['nullable', 'string', 'max:30'],
                'address' => ['nullable', 'string', 'max:5000'],
                'role' => ['required', Rule::in(['member', 'admin'])],
                'points' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:4294967295',
                ],
                'tier_id' => [
                    'required',
                    'integer',
                    Rule::exists('MEMBERSHIP_TIER', 'tier_id'),
                ],
                'member_version' => ['required', 'string', 'size:64'],
            ]);

            // ป้องกันบันทึกทับคะแนนที่เพิ่งได้ หรือข้อมูลที่คนอื่นเพิ่งแก้
            if (!hash_equals(
                $this->version($target),
                $input['member_version']
            )) {
                $this->fail(
                    'ข้อมูลสมาชิกเปลี่ยนไปแล้ว กรุณากดเปิดข้อมูลล่าสุดก่อนแก้ไขอีกครั้ง'
                );
            }

            $isSelf = (int) $target->getKey()
                === (int) $request->user()->getKey();

            if ($isSelf && $input['role'] !== 'admin') {
                $this->fail('ไม่สามารถลดสิทธิ์ Admin ของตัวเองได้');
            }

            if (
                $target->isAdmin()
                && $input['role'] === 'member'
                && $admins->count() <= 1
            ) {
                $this->fail('ต้องเหลือ Admin อย่างน้อย 1 คน');
            }

            $points = (int) $input['points'];

            // ใช้เกณฑ์เดียวกับ CommerceService
            $tier = MembershipTier::where('min_points', '<=', $points)
                ->orderByDesc('min_points')
                ->orderBy('tier_id')
                ->lockForUpdate()
                ->first();

            if (!$tier) {
                $this->fail('ไม่พบระดับสมาชิกที่ตรงกับคะแนนนี้');
            }

            if ((int) $tier->tier_id !== (int) $input['tier_id']) {
                throw ValidationException::withMessages([
                    'tier_id' => "คะแนน {$points} อยู่ในระดับ {$tier->tier_name} "
                        .'กรุณาเลือก Tier หรือปรับคะแนนให้ตรงกัน',
                ]);
            }

            $oldEmail = $target->email;

            $target->fill([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'phone' => $input['phone'] ?? null,
                'address' => $input['address'] ?? null,
            ]);

            // ฟิลด์สิทธิ์กำหนดในส่วน Admin โดยตรง
            $target->forceFill([
                'role' => $input['role'],
                'points' => $points,
                'tier_id' => $tier->tier_id,
            ]);

            $changes = AdminLog::pendingChanges($target);

            if ($changes !== []) {
                $target->save();

                if ($oldEmail !== $target->email) {
                    $this->deleteResetTokens($oldEmail);
                }

                AdminLog::record(
                    'updated',
                    $target,
                    "Member #{$target->member_id}: {$target->name}",
                    $changes
                );
            }
        }, 3);

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member saved successfully.');
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        DB::transaction(function () use ($request, $member) {
            $admins = $this->lockAdmins($request);

            $target = User::whereKey($member->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $target->getKey()
                === (int) $request->user()->getKey()
            ) {
                $this->fail('ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้');
            }

            if ($target->isAdmin() && $admins->count() <= 1) {
                $this->fail('ไม่สามารถลบ Admin คนสุดท้ายได้');
            }

            // เก็บประวัติซื้อไว้ รวมถึงคำสั่งซื้อที่ยกเลิกแล้ว
            if ($target->orders()->exists()) {
                $this->fail(
                    'สมาชิกนี้มีประวัติคำสั่งซื้อ จึงไม่สามารถลบบัญชีได้'
                );
            }

            $snapshot = AdminLog::snapshot($target, true);
            $description = "Member #{$target->member_id}: {$target->name}";

            $cart = $target->cart()
                ->lockForUpdate()
                ->first();

            if ($cart) {
                $cart->cars()->detach();
                $cart->delete();
            }

            $target->reviews()->delete();

            $this->deleteResetTokens($target->email);

            // เก็บ Log เดิมไว้ แต่ตัด FK ของบัญชีที่กำลังจะถูกลบ
            AdminLog::where('member_id', $target->getKey())
                ->update(['member_id' => null]);

            AdminLog::record(
                'deleted',
                $target,
                $description,
                $snapshot
            );

            $target->delete();
        }, 3);

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'ลบสมาชิกแล้ว');
    }

    private function lockAdmins(Request $request): Collection
    {
        // ล็อกก่อนเปลี่ยนสิทธิ์ ป้องกัน Admin หลายคนลดสิทธิ์พร้อมกัน
        $admins = User::where('role', 'admin')
            ->orderBy('member_id')
            ->lockForUpdate()
            ->get();

        $actorId = (int) $request->user()->getKey();

        // ตรวจสิทธิ์ล่าสุดอีกครั้งหลังล็อก
        abort_unless(
            $admins->contains(
                fn (User $admin) => (int) $admin->getKey() === $actorId
            ),
            403,
            'คุณไม่มีสิทธิ์จัดการสมาชิก'
        );

        return $admins;
    }

    private function version(User $member): string
    {
        return hash('sha256', json_encode([
            $member->first_name,
            $member->last_name,
            $member->email,
            $member->phone,
            $member->address,
            $member->role,
            (int) $member->points,
            (int) $member->tier_id,
        ], JSON_THROW_ON_ERROR));
    }

    private function deleteResetTokens(string $email): void
    {
        $broker = config('auth.defaults.passwords');

        if (!$broker) {
            return;
        }

        $settings = config("auth.passwords.{$broker}", []);

        if (($settings['driver'] ?? 'database') !== 'database') {
            return;
        }

        $table = $settings['table'] ?? 'password_reset_tokens';
        $connection = $settings['connection'] ?? null;

        if (Schema::connection($connection)->hasTable($table)) {
            DB::connection($connection)
                ->table($table)
                ->where('email', $email)
                ->delete();
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'member' => $message,
        ]);
    }
}