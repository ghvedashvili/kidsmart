<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_question_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('difficulty');
            // null theme_id = applies to any theme for this grade+difficulty (uniqueness for this case is enforced in the controller, not the DB, since MySQL treats each NULL as distinct)
            $table->foreignId('theme_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('questions_count');
            $table->timestamps();

            $table->unique(['grade_id', 'difficulty', 'theme_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_question_counts');
    }
};
