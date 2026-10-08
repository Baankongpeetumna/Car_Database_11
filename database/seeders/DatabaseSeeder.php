<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            MembershipTierSeeder::class,
            AdminSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            CarSeeder::class,
        ]);
    }
}