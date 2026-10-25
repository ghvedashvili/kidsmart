<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `context` scopes an achievement to one activity category (test/practice/
     * olympiad/games) or 'any' (checked after every category's activity, e.g.
     * day_streak/coins_earned). `game` further scopes a 'games'-context
     * achievement to one specific game (null = applies across all games) --
     * mirrors the existing theme_id nullable-scoping pattern.
     *
     * `daily_limit` is dropped: achievements are no longer rate-limited to
     * one new unlock per day.
     */
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->string('context', 20)->default('test')->after('theme_id');
            $table->string('game', 40)->nullable()->after('context');
            $table->dropColumn('daily_limit');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->boolean('daily_limit')->default(false);
            $table->dropColumn(['context', 'game']);
        });
    }
};
