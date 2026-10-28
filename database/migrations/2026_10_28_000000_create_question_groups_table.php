<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->timestamps();
        });

        Schema::table('question_templates', function (Blueprint $table) {
            $table->foreignId('question_group_id')
                  ->nullable()
                  ->after('sequence')
                  ->constrained('question_groups')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->dropForeign(['question_group_id']);
            $table->dropColumn('question_group_id');
        });
        Schema::dropIfExists('question_groups');
    }
};
