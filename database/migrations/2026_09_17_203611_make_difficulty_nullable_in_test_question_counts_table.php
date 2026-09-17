<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // null difficulty = applies to any level for this grade(+theme), same "null = wildcard"
        // convention theme_id already uses. Raw SQL avoids requiring doctrine/dbal for ->change().
        DB::statement('ALTER TABLE test_question_counts MODIFY difficulty TINYINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('UPDATE test_question_counts SET difficulty = 1 WHERE difficulty IS NULL');
        DB::statement('ALTER TABLE test_question_counts MODIFY difficulty TINYINT UNSIGNED NOT NULL');
    }
};
