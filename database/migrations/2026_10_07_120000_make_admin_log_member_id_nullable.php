<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // คำสั่ง php artisan member:role รันจาก terminal ไม่มี admin login อยู่
        // จึงให้ member_id เป็น NULL ได้ (NULL = ทำโดยระบบ / terminal)
        Schema::table('ADMIN_LOG', function (Blueprint $table) {
            $table->unsignedInteger('member_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ADMIN_LOG', function (Blueprint $table) {
            $table->unsignedInteger('member_id')->nullable(false)->change();
        });
    }
};
