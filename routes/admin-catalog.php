<?php

use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\TierController;
use Illuminate\Support\Facades\Route;

// Admin จัดการยี่ห้อ ประเภท รถ ระดับสมาชิก รีวิว
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('brands', BrandController::class)
            ->except('show');

        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('cars', CarController::class)
            ->except('show');

        Route::patch('cars/{car}/stock', [CarController::class, 'addStock'])
            ->name('cars.stock');

        Route::resource('tiers', TierController::class)
            ->except('show');

        Route::resource('reviews', ReviewController::class)
            ->only(['index', 'destroy']);

        // ประวัติการแก้ไขของ admin (ดูอย่างเดียว)
        Route::get('logs', [AdminLogController::class, 'index'])
            ->name('logs.index');
    });
