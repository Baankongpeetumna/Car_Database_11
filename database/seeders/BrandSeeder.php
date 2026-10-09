<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['brand_name' => 'Toyota', 'country' => 'Japan'],
            ['brand_name' => 'Honda', 'country' => 'Japan'],
            ['brand_name' => 'Mazda', 'country' => 'Japan'],
            ['brand_name' => 'BMW', 'country' => 'Germany'],
            ['brand_name' => 'Mercedes-Benz', 'country' => 'Germany'],
            ['brand_name' => 'Ford', 'country' => 'USA'],
            ['brand_name' => 'Tesla', 'country' => 'USA'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['brand_name' => $brand['brand_name']], $brand);
        }
    }
}