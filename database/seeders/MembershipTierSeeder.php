<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['tier_name' => 'Basic',    'min_points' => 0,    'discount_percent' => 0],
            ['tier_name' => 'Silver',   'min_points' => 500,  'discount_percent' => 3],
            ['tier_name' => 'Gold',     'min_points' => 2000, 'discount_percent' => 5],
            ['tier_name' => 'Platinum', 'min_points' => 5000, 'discount_percent' => 10],
        ];

        foreach ($tiers as $tier) {
            // มีชื่อ tier นี้แล้วจะอัปเดตค่า ไม่มีจะสร้างใหม่ รันซ้ำได้ไม่ซ้ำแถว
            DB::table('MEMBERSHIP_TIER')->updateOrInsert(
                ['tier_name' => $tier['tier_name']],
                $tier
            );
        }
    }
}