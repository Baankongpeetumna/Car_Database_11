<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MEMBERSHIP_TIER', function (Blueprint $table) {
            $table->increments('tier_id');
            $table->string('tier_name', 255);
            $table->unsignedInteger('min_points')->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MEMBERSHIP_TIER');
    }
};