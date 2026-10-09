<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('MEMBERSHIP_TIER', 'color')) {
            Schema::table('MEMBERSHIP_TIER', function (Blueprint $table) {
                $table->string('color', 7)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('MEMBERSHIP_TIER', 'color')) {
            Schema::table('MEMBERSHIP_TIER', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }
};