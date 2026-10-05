<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::pluck('brand_id', 'brand_name');
        $cats = Category::pluck('category_id', 'category_name');

        $cars = [
            ['Toyota', 'Sedan', 'Camry 2.5 HEV', 2024, 'White', 'Hybrid', 'Automatic', 2487, 0, 'New', 1599000, 5],
            ['Toyota', 'Pickup', 'Hilux Revo 2.4', 2023, 'Silver', 'Diesel', 'Manual', 2393, 18500, 'Used', 689000, 2],
            ['Toyota', 'SUV', 'Fortuner 2.8 Legender', 2024, 'Black', 'Diesel', 'Automatic', 2755, 0, 'New', 1759000, 4],
            ['Honda', 'Sedan', 'Civic e:HEV RS', 2024, 'Blue', 'Hybrid', 'Automatic', 1993, 0, 'New', 1299000, 6],
            ['Honda', 'SUV', 'CR-V 1.5 Turbo', 2022, 'Grey', 'Petrol', 'Automatic', 1498, 32000, 'Used', 1050000, 1],
            ['Honda', 'Hatchback', 'Jazz 1.5 RS', 2021, 'Red', 'Petrol', 'Automatic', 1496, 45000, 'Used', 520000, 2],
            ['Mazda', 'Hatchback', 'Mazda3 2.0 SP', 2023, 'Red', 'Petrol', 'Automatic', 1998, 12000, 'Used', 890000, 1],
            ['Mazda', 'SUV', 'CX-5 2.2 XDL', 2024, 'White', 'Diesel', 'Automatic', 2191, 0, 'New', 1479000, 3],
            ['BMW', 'Sedan', '320d M Sport', 2022, 'Black', 'Diesel', 'Automatic', 1995, 28000, 'Used', 1890000, 1],
            ['BMW', 'Coupe', '430i M Sport Coupe', 2023, 'Blue', 'Petrol', 'Automatic', 1998, 9000, 'Used', 2790000, 1],
            ['Mercedes-Benz', 'Sedan', 'C 220 d AMG Dynamic', 2023, 'Silver', 'Diesel', 'Automatic', 1993, 15000, 'Used', 2350000, 2],
            ['Mercedes-Benz', 'SUV', 'GLC 300 e', 2024, 'White', 'Hybrid', 'Automatic', 1991, 0, 'New', 3290000, 2],
            ['Ford', 'Pickup', 'Ranger Wildtrak 2.0', 2024, 'Orange', 'Diesel', 'Automatic', 1996, 0, 'New', 1299000, 5],
            ['Ford', 'Van', 'Transit 2.2 Van', 2020, 'White', 'Diesel', 'Manual', 2198, 78000, 'Used', 650000, 1],
            ['Tesla', 'Electric', 'Model 3 Long Range', 2023, 'White', 'Electric', 'Automatic', 0, 14000, 'Used', 1690000, 2],
            ['Tesla', 'Electric', 'Model Y Performance', 2024, 'Red', 'Electric', 'Automatic', 0, 0, 'New', 2290000, 3],
        ];

        foreach ($cars as [$brand, $category, $model, $year, $color, $fuel, $trans, $cc, $km, $condition, $price, $stock]) {
            Car::firstOrCreate(
                ['model_name' => $model, 'model_year' => $year, 'color' => $color],
                [
                    'fuel_type' => $fuel,
                    'transmission' => $trans,
                    'engine_cc' => $cc,
                    'mileage_km' => $km,
                    'car_condition' => $condition,
                    'price' => $price,
                    'stock_qty' => $stock,
                    'description' => "{$brand} {$model} ปี {$year} สี {$color}",
                    'image_url' => null,
                    'brand_id' => $brands[$brand],
                    'category_id' => $cats[$category],
                ]
            );
        }
    }
}