<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORDER_ITEM', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);

            $table->primary(['order_id', 'car_id']);

            $table->foreign('order_id')
                ->references('order_id')
                ->on('ORDERS');

            $table->foreign('car_id')
                ->references('car_id')
                ->on('CAR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORDER_ITEM');
    }
};