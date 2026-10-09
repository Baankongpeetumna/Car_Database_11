<?php

use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// หน้าร้านฝั่งลูกค้า

// ระดับสมาชิกเปิดดูได้โดยไม่ต้อง login
Route::get('/membership', [MembershipController::class, 'index'])
    ->name('membership.index');

// เขียน แก้ไข ลบรีวิวของตัวเอง
Route::middleware(['auth', 'role:member'])->group(function () {
    Route::post('/products/{car}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');

    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');
});
