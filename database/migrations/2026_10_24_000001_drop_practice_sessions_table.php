<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * practice_sessions tracked a practice-only level/streak, separate from
     * the child's real ChildSetting->difficulty -- removed because practice
     * now reuses TestGeneratorService's own selection/rotation (see
     * practice_template_rotation migration) instead of leveling up on its
     * own. Nothing outside PracticeController/PracticeSession read this
     * table; practice stats are served from practice_answer_logs instead.
     */
    public function up(): void
    {
        Schema::dropIfExists('practice_sessions');
    }

    public function down(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('topic_id')->nullable();
            $table->enum('session_type', ['topic', 'pyramid'])->default('topic');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedTinyInteger('streak')->default(0);
            $table->unsignedSmallInteger('total_answered')->default(0);
            $table->unsignedSmallInteger('total_correct')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }
};
