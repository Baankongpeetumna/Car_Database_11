<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// ตัวช่วยสร้างข้อมูลทดสอบที่ใช้ร่วมกันในหลาย test ของ admin
trait CreatesCatalogData
{
    // User ยังไม่มี factory จึงสร้างตรงๆ ให้ตรงกับคอลัมน์ของตาราง MEMBER
    private function makeUser(string $role): User
    {
        $tier = MembershipTier::firstOrCreate(
            ['min_points' => 0],
            ['tier_name' => 'Silver', 'discount_percent' => 0],
        );

        return User::forceCreate([
            'first_name' => 'Test',
            'last_name' => ucfirst($role),
            'email' => $role.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'tier_id' => $tier->tier_id,
        ]);
    }

    // สร้างออเดอร์ที่มีรถคันนี้ตามสถานะที่ต้องการ (ไม่ผ่าน checkout เพื่อให้ test สั้น)
    private function makeOrder(User $member, Car $car, string $status = 'completed'): int
    {
        $orderId = DB::table('ORDERS')->insertGetId([
            'status' => $status,
            'payment_method' => 'cash',
            'shipping_address' => 'Bangkok',
            'subtotal' => $car->price,
            'total_amount' => $car->price,
            'member_id' => $member->member_id,
        ], 'order_id');

        DB::table('ORDER_ITEM')->insert([
            'order_id' => $orderId,
            'car_id' => $car->car_id,
            'quantity' => 1,
            'unit_price' => $car->price,
        ]);

        return $orderId;
    }

    // ไม่ส่ง brand/category มา จะสร้างให้อัตโนมัติ
    private function makeCar(?Brand $brand = null, ?Category $category = null): Car
    {
        $brand ??= Brand::create(['brand_name' => 'Toyota '.uniqid(), 'country' => 'Japan']);
        $category ??= Category::create(['category_name' => 'SUV '.uniqid()]);

        return Car::create([
            'model_name' => 'Fortuner',
            'model_year' => 2024,
            'color' => 'White',
            'fuel_type' => 'Diesel',
            'transmission' => 'Automatic',
            'engine_cc' => 2800,
            'mileage_km' => 0,
            'car_condition' => 'New',
            'price' => 1500000,
            'stock_qty' => 1,
            'brand_id' => $brand->brand_id,
            'category_id' => $category->category_id,
        ]);
    }
}
