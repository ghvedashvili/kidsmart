<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widens the answer_type enum to add "numeric_input" — the child types the
 * number in directly instead of picking from generated multiple-choice options.
 * Raw SQL is used instead of Schema::table()->change() since doctrine/dbal
 * isn't installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE question_templates MODIFY answer_type ENUM('numeric', 'text', 'numeric_input') NOT NULL DEFAULT 'numeric'");
    }

    public function down(): void
    {
        DB::statement("UPDATE question_templates SET answer_type = 'numeric' WHERE answer_type = 'numeric_input'");
        DB::statement("ALTER TABLE question_templates MODIFY answer_type ENUM('numeric', 'text') NOT NULL DEFAULT 'numeric'");
    }
};
