<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_questions', function (Blueprint $table) {
            $table->string('question_type', 30)->default('multiple_choice')->after('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_questions', function (Blueprint $table) {
            $table->dropColumn('question_type');
        });
    }
};
