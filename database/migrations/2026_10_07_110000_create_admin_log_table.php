<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // บันทึกว่า admin คนไหน ทำอะไร กับข้อมูลไหน เมื่อไร
        Schema::create('ADMIN_LOG', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('member_id');           // admin ที่ทำรายการ
            $table->string('action', 50);                   // created / updated / deleted / restocked / status_changed
            $table->string('subject_type', 50);             // ชื่อตารางที่ถูกแก้ เช่น CAR, BRAND
            $table->unsignedInteger('subject_id')->nullable(); // แถวที่ถูกแก้ (ไม่ใช่ FK เพื่อให้ log อยู่ได้แม้ข้อมูลถูกลบ)
            $table->string('description', 255);             // ชื่อสิ่งที่ถูกแก้ ณ เวลานั้น
            $table->json('changes')->nullable();            // {"คอลัมน์": [ค่าเดิม, ค่าใหม่]}
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('member_id')
                ->references('member_id')
                ->on('MEMBER');

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ADMIN_LOG');
    }
};
