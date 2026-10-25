<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `wins`/`losses` are cumulative totals only -- there was no record of
     * consecutive round outcomes, so a "win streak" achievement had nothing
     * to check against. `current_streak` tracks just that: incremented on a
     * win, reset to 0 on a loss (see GameController's finish/round methods).
     */
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->unsignedInteger('current_streak')->default(0)->after('losses');
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn('current_streak');
        });
    }
};
