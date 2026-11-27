<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* თამაში იხსნება მხოლოდ დღის ტესტის შემდეგ? (ნაგულისხმევად — კი) */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_settings', function (Blueprint $table) {
            $table->boolean('needs_test')->default(true)->after('grade_ids');
        });
    }

    public function down(): void
    {
        Schema::table('game_settings', function (Blueprint $table) {
            $table->dropColumn('needs_test');
        });
    }
};
