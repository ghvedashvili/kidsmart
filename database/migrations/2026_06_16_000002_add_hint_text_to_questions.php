<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->text('hint_text')->nullable()->after('template_text');
        });

        Schema::table('test_questions', function (Blueprint $table) {
            $table->text('hint_text')->nullable()->after('question_text');
        });
    }

    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->dropColumn('hint_text');
        });
        Schema::table('test_questions', function (Blueprint $table) {
            $table->dropColumn('hint_text');
        });
    }
};
