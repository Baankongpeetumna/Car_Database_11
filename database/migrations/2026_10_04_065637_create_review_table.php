<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('REVIEW', function (Blueprint $table) {
            $table->increments('review_id');
            $table->text('comment');
            $table->dateTime('created_at')->useCurrent();
            $table->unsignedInteger('member_id');
            $table->unsignedInteger('car_id');

            $table->foreign('member_id')
                ->references('member_id')
                ->on('MEMBER');

            $table->foreign('car_id')
                ->references('car_id')
                ->on('CAR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('REVIEW');
    }
};