<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CAR', function (Blueprint $table) {
            $table->increments('car_id');
            $table->string('model_name', 255);
            $table->unsignedInteger('model_year');
            $table->string('color', 100);
            $table->string('fuel_type', 50);
            $table->string('transmission', 50);
            $table->unsignedInteger('engine_cc');
            $table->unsignedInteger('mileage_km')->default(0);
            $table->string('car_condition', 50);
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->text('description')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->unsignedInteger('brand_id');
            $table->unsignedInteger('category_id');

            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('BRAND');

            $table->foreign('category_id')
                ->references('category_id')
                ->on('CATEGORY');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CAR');
    }
};