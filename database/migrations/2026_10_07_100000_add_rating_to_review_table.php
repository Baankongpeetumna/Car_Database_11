<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // คะแนนดาว 1-5 (ตรวจค่าใน ReviewController)
        // nullable เพื่อให้รีวิวเก่าที่มีอยู่แล้ว migrate ผ่าน และค่า NULL ไม่ถูกนับในค่าเฉลี่ย
        Schema::table('REVIEW', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('REVIEW', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
