<?php

namespace App\Http\Controllers;

use App\Models\MembershipTier;
use Illuminate\Http\Request;
use Illuminate\View\View;

// หน้าระดับสมาชิก: ทุกคนดูได้ว่ามีกี่ระดับ ลดเท่าไร
// สมาชิกที่ login จะเห็นระดับของตัวเองและคะแนนที่ต้องสะสมถึงระดับถัดไป
class MembershipController extends Controller
{
    // 1,000 บาท = 1 คะแนน (ตรงกับ Money::points)
    private const BAHT_PER_POINT = 1000;

    public function index(Request $request): View
    {
        $tiers = MembershipTier::orderBy('min_points')->get();

        $user = $request->user();
        $progress = null;

        if ($user?->isMember()) {
            $points = (int) $user->points;
            $current = $user->tier;

            // ระดับถัดไป = tier แรกที่ต้องใช้คะแนนมากกว่าที่มีตอนนี้
            $next = $tiers->first(fn ($tier) => $tier->min_points > $points);

            $progress = [
                'points' => $points,
                'current' => $current,
                'next' => $next,
                'pointsNeeded' => $next ? $next->min_points - $points : 0,
                'bahtNeeded' => $next ? ($next->min_points - $points) * self::BAHT_PER_POINT : 0,
                'percent' => $this->percentToNext($points, $current?->min_points ?? 0, $next?->min_points),
            ];
        }

        return view('membership.index', [
            'title' => 'Membership',
            'tiers' => $tiers,
            'progress' => $progress,
            'bahtPerPoint' => self::BAHT_PER_POINT,
        ]);
    }

    private function percentToNext(int $points, int $from, ?int $to): int
    {
        if ($to === null) {
            return 100;
        }

        if ($to <= $from) {
            return 0;
        }

        return (int) max(0, min(100, floor(($points - $from) * 100 / ($to - $from))));
    }
}
