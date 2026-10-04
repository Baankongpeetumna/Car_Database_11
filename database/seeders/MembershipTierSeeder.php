<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipTierSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('MEMBERSHIP_TIER')
            ->where('min_points', 0)
            ->exists();

        if (! $exists) {
            DB::table('MEMBERSHIP_TIER')->insert([
                'tier_name' => 'Basic',
                'min_points' => 0,
                'discount_percent' => 0,
            ]);
        }
    }
}