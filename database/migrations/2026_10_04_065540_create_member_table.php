<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MEMBER', function (Blueprint $table) {
            $table->increments('member_id');
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('role', 50)->default('member');
            $table->unsignedInteger('points')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->unsignedInteger('tier_id');

            $table->foreign('tier_id')
                ->references('tier_id')
                ->on('MEMBERSHIP_TIER');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MEMBER');
    }
};