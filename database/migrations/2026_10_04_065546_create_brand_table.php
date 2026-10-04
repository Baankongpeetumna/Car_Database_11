<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('BRAND', function (Blueprint $table) {
            $table->increments('brand_id');
            $table->string('brand_name', 255);
            $table->string('country', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('BRAND');
    }
};