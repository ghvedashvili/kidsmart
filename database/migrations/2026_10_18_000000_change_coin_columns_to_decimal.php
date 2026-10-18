<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widens points_per_correct/coins/coins_earned from integer to decimal(.,2)
 * so admins can configure fractional points-per-correct (e.g. 0.5) without
 * MySQL silently rounding the value away. Raw SQL is used instead of
 * Schema::table()->change() since doctrine/dbal isn't installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE point_rules MODIFY points_per_correct DECIMAL(6,2) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE child_settings MODIFY coins DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE tests MODIFY coins_earned DECIMAL(8,2) UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE point_rules MODIFY points_per_correct SMALLINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE child_settings MODIFY coins INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE tests MODIFY coins_earned SMALLINT UNSIGNED NULL');
    }
};
