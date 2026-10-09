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
        return view('admin.tiers.create', [
            'tier' => new MembershipTier(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tier = MembershipTier::create($this->validated($request));

        AdminLog::record(
            'created',
            $tier,
            $tier->tier_name,
            AdminLog::snapshot($tier),
        );

        return redirect()->route('admin.tiers.index')
            ->with('success', "Tier {$tier->tier_name} added.");
    }

    public function edit(MembershipTier $tier): View
    {
        return view('admin.tiers.edit', compact('tier'));
    }

    public function update(
        Request $request,
        MembershipTier $tier
    ): RedirectResponse {
        $data = $this->validated($request, $tier);

        // ระดับเริ่มต้นต้องมีคะแนนขั้นต่ำเป็น 0
        if (
            $this->isBaseTier($tier)
            && (int) $data['min_points'] !== self::BASE_POINTS
        ) {
            throw ValidationException::withMessages([
                'min_points' => 'The default tier for new members must keep 0 minimum points.',
            ]);
        }

        // รวมสีไว้ในข้อมูลที่บันทึกและตรวจการเปลี่ยนแปลง
        $tier->fill($data);

        $changes = AdminLog::pendingChanges($tier);

        $tier->save();

        if ($changes !== []) {
            AdminLog::record(
                'updated',
                $tier,
                $tier->tier_name,
                $changes,
            );
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

        $memberCount = $tier->members()->count();

        if ($memberCount > 0) {
            return back()->withErrors([
                'tier' => "Cannot delete tier {$tier->tier_name} "
                    ."because {$memberCount} "
                    .Str::plural('member', $memberCount)
                    .' still belong to it.',
            ]);
        }

        try {
            $tier->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'tier' => "Cannot delete tier {$tier->tier_name} because other records still reference it.",
            ]);
        }

        AdminLog::record(
            'deleted',
            $tier,
            $tier->tier_name,
            AdminLog::snapshot($tier, deleted: true),
        );

        return redirect()->route('admin.tiers.index')
            ->with('success', "Tier {$tier->tier_name} deleted.");
    }

    private function isBaseTier(MembershipTier $tier): bool
    {
        return (int) $tier->getOriginal('min_points') === self::BASE_POINTS;
    }

    private function validated(
        Request $request,
        ?MembershipTier $tier = null
    ): array {
        $data = $request->validate(
            [
                'tier_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('MEMBERSHIP_TIER', 'tier_name')
                        ->ignore($tier?->tier_id, 'tier_id'),
                ],

                'min_points' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:4294967295',
                    Rule::unique('MEMBERSHIP_TIER', 'min_points')
                        ->ignore($tier?->tier_id, 'tier_id'),
                ],

                'discount_percent' => [
                    'required',
                    'numeric',
                    'decimal:0,2',
                    'min:0',
                    'max:100',
                ],

                // รับสีแบบ HEX เช่น #F97316
                'color' => [
                    'required',
                    'string',
                    'regex:/^#[0-9a-fA-F]{6}$/',
                ],
            ],
            [
                'tier_name.unique' => 'This tier name already exists.',
                'min_points.unique' => 'Another tier already uses this minimum points value.',
                'color.required' => 'กรุณาเลือกสีของระดับสมาชิก',
                'color.regex' => 'สีต้องเป็นรูปแบบ HEX เช่น #F97316',
            ],
            [
                'tier_name' => 'tier name',
                'min_points' => 'minimum points',
                'discount_percent' => 'discount (%)',
                'color' => 'tier color',
            ],
        );

        // เก็บรหัสสีเป็นตัวพิมพ์ใหญ่ให้เหมือนกันทุกครั้ง
        $data['color'] = strtoupper($data['color']);

        return $data;
    }
}