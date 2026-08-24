<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
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

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
