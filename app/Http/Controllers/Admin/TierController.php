<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\MembershipTier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TierController extends Controller
{
    // tier ที่ min_points = 0 คือระดับเริ่มต้นของสมาชิกใหม่ (CreateNewUser ใช้) ต้องมีอยู่เสมอ
    private const BASE_POINTS = 0;

    public function index(): View
    {
        $tiers = MembershipTier::withCount('members')
            ->orderBy('min_points')
            ->get();

        return view('admin.tiers.index', compact('tiers'));
    }

    public function create(): View
    {
        return view('admin.tiers.create', ['tier' => new MembershipTier()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tier = MembershipTier::create($this->validated($request));

        AdminLog::record('created', $tier, $tier->tier_name, AdminLog::snapshot($tier));

        return redirect()->route('admin.tiers.index')
            ->with('success', "Tier {$tier->tier_name} added.");
    }

    public function edit(MembershipTier $tier): View
    {
        return view('admin.tiers.edit', compact('tier'));
    }

    public function update(Request $request, MembershipTier $tier): RedirectResponse
    {
        $data = $this->validated($request, $tier);

        // ห้ามเปลี่ยน min_points ของระดับเริ่มต้น ไม่งั้นสมัครสมาชิกใหม่ไม่ได้
        if ($this->isBaseTier($tier) && (int) $data['min_points'] !== self::BASE_POINTS) {
            throw ValidationException::withMessages([
                'min_points' => 'The default tier for new members must keep 0 minimum points.',
            ]);
        }

        $tier->fill($data);
        $changes = AdminLog::pendingChanges($tier);
        $tier->save();

        // บันทึก log เฉพาะเมื่อมีค่าเปลี่ยนจริง
        if ($changes !== []) {
            AdminLog::record('updated', $tier, $tier->tier_name, $changes);
        }

        return redirect()->route('admin.tiers.index')
            ->with('success', "Tier {$tier->tier_name} updated.");
    }

    public function destroy(MembershipTier $tier): RedirectResponse
    {
        if ($this->isBaseTier($tier)) {
            return back()->withErrors([
                'tier' => "Cannot delete tier {$tier->tier_name} because it is the default tier for new members.",
            ]);
        }

        // FK ของ MEMBER ไม่มี onDelete จึงต้องเช็กก่อนลบ
        $memberCount = $tier->members()->count();

        if ($memberCount > 0) {
            return back()->withErrors([
                'tier' => "Cannot delete tier {$tier->tier_name} "
                    ."because {$memberCount} ".Str::plural('member', $memberCount).' still belong to it.',
            ]);
        }

        try {
            $tier->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'tier' => "Cannot delete tier {$tier->tier_name} because other records still reference it.",
            ]);
        }

        AdminLog::record('deleted', $tier, $tier->tier_name, AdminLog::snapshot($tier, deleted: true));

        return redirect()->route('admin.tiers.index')
            ->with('success', "Tier {$tier->tier_name} deleted.");
    }

    private function isBaseTier(MembershipTier $tier): bool
    {
        return (int) $tier->getOriginal('min_points') === self::BASE_POINTS;
    }

    private function validated(Request $request, ?MembershipTier $tier = null): array
    {
        return $request->validate(
            [
                'tier_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('MEMBERSHIP_TIER', 'tier_name')
                        ->ignore($tier?->tier_id, 'tier_id'),
                ],
                // min_points ห้ามซ้ำ เพราะ CommerceService เลือก tier จาก min_points
                'min_points' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:4294967295',
                    Rule::unique('MEMBERSHIP_TIER', 'min_points')
                        ->ignore($tier?->tier_id, 'tier_id'),
                ],
                // DECIMAL(5,2): 0.00 - 100.00
                'discount_percent' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            ],
            [
                'tier_name.unique' => 'This tier name already exists.',
                'min_points.unique' => 'Another tier already uses this minimum points value.',
            ],
            [
                'tier_name' => 'tier name',
                'min_points' => 'minimum points',
                'discount_percent' => 'discount (%)',
            ],
        );
    }
}
