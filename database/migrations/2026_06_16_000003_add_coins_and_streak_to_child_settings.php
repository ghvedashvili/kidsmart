<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_settings', function (Blueprint $table) {
            $table->unsignedInteger('coins')->default(0)->after('tests_per_week');
            $table->tinyInteger('difficulty_streak')->default(0)->after('coins');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->unsignedSmallInteger('coins_earned')->nullable()->after('total_questions');
        });

        Schema::create('child_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug', 40);
            $table->timestamp('earned_at');
            $table->unique(['child_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_achievements');

        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('coins_earned');
        });

        Schema::table('child_settings', function (Blueprint $table) {
            $table->dropColumn(['coins', 'difficulty_streak']);
        });
    }
};
