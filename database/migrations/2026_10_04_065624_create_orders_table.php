<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORDERS', function (Blueprint $table) {
            $table->increments('order_id');
            $table->dateTime('order_date')->useCurrent();
            $table->string('status', 50)->default('pending');
            $table->string('payment_method', 50);
            $table->text('shipping_address');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedInteger('member_id');

            $table->foreign('member_id')
                ->references('member_id')
                ->on('MEMBER');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORDERS');
    }
};