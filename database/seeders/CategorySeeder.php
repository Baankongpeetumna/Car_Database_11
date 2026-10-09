<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Sedan', 'SUV', 'Hatchback', 'Pickup', 'Coupe', 'Van', 'Electric'];

        foreach ($categories as $name) {
            Category::firstOrCreate(['category_name' => $name]);
        }
    }
}