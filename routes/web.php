<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController; // เพิ่ม
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

// หน้าแรกของร้าน (ยี่ห้อ ประเภท รถมาใหม่ ระดับสมาชิก) ใช้ชื่อ route 'home' เดิม
Route::get('/', HomeController::class)->name('home');

// เปิดดูได้โดยไม่ต้อง login
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{car}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')
            ->name('dashboard');

        // เพิ่ม Routes จัดการรถ แบรนด์ และหมวดหมู่ตรงนี้ภายหลัง
    });
require __DIR__.'/commerce.php';
require __DIR__.'/admin-catalog.php';
require __DIR__.'/storefront.php';
require __DIR__.'/settings.php';