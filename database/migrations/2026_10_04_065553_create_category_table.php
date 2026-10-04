<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CATEGORY', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_name', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CATEGORY');
    }
};