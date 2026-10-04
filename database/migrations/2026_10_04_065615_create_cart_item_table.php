<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CART_ITEM', function (Blueprint $table) {
            $table->unsignedInteger('cart_id');
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('quantity')->default(1);

            $table->primary(['cart_id', 'car_id']);

            $table->foreign('cart_id')
                ->references('cart_id')
                ->on('CART');

            $table->foreign('car_id')
                ->references('car_id')
                ->on('CAR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CART_ITEM');
    }
};