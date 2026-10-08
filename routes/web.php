<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController; // เพิ่ม
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Models\Car;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Review;

// หน้าแรกของร้าน (ยี่ห้อ ประเภท รถมาใหม่ ระดับสมาชิก) ใช้ชื่อ route 'home' เดิม
Route::get('/', HomeController::class)->name('home');

// เปิดดูได้โดยไม่ต้อง login
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{car}', [ProductController::class, 'show'])->name('products.show');

// หน้า dashboard ของ starter kit ไม่ได้ใช้แล้ว: เก็บชื่อ route ไว้กันลิงก์เก่าพัง แต่ส่งกลับหน้า Home
Route::redirect('/dashboard', '/')->name('dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Route::view('/dashboard', 'admin.dashboard')
        //     ->name('dashboard');

        Route::get('/dashboard', function () {
            return view('admin.dashboard', [
                'carsCount' => Car::count(),
                'brandsCount' => Brand::count(),
                'categoriesCount' => Category::count(),
                'pendingOrdersCount' => Order::where('status', 'pending')->count(),
                'membersCount' => User::where('role', 'member')->count(),
                'reviewsCount' => Review::count(),
                'averageRating' => Review::avg('rating') ?? 0,

                 'latestOrders' => Order::with('member')
                    ->orderByDesc('order_id')
                    ->limit(4)
                    ->get(),

                'lowStockCars' => Car::where('stock_qty', '<', 3)
                    ->orderBy('stock_qty')
                    ->get(),
            ]);
        })->name('dashboard');

        // เพิ่ม Routes จัดการรถ แบรนด์ และหมวดหมู่ตรงนี้ภายหลัง
    });
require __DIR__.'/commerce.php';
require __DIR__.'/admin-catalog.php';
require __DIR__.'/storefront.php';
require __DIR__.'/settings.php';