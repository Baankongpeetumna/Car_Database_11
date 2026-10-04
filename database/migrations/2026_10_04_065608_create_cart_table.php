<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CART', function (Blueprint $table) {
            $table->increments('cart_id');
            $table->dateTime('created_at')->useCurrent();
            $table->unsignedInteger('member_id')->unique();

            $table->foreign('member_id')
                ->references('member_id')
                ->on('MEMBER');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CART');
    }
};