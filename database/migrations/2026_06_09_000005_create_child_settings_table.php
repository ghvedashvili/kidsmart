<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedTinyInteger('tests_per_week')->default(3);
            $table->timestamps();
        });

        Schema::create('child_theme', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'theme_id']);
        });

        Schema::create('child_topic', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_topic');
        Schema::dropIfExists('child_theme');
        Schema::dropIfExists('child_settings');
    }
};
