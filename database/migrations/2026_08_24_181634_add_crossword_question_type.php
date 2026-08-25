<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // question_templates
        DB::statement("ALTER TABLE question_templates MODIFY question_type VARCHAR(30) NOT NULL DEFAULT 'multiple_choice'");

        // test_questions
        DB::statement("ALTER TABLE test_questions MODIFY question_type VARCHAR(30) NOT NULL DEFAULT 'multiple_choice'");
    }

    public function down(): void {}
};
