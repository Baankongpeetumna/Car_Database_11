<?php

use App\Http\Controllers\ProductController; // เพิ่ม
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// เปิดดูได้โดยไม่ต้อง login
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

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

require __DIR__.'/settings.php';