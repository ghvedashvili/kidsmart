<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_settings', function (Blueprint $table) {
            // { order: [topic_id,...] (shuffled), cursor: int, last_repeated_topic_id: int|null }
            $table->json('topic_rotation')->nullable()->after('difficulty_streak');
            // running total of completed tests since the last level re-evaluation (batches of 7)
            $table->unsignedInteger('tests_since_level_review')->default(0)->after('topic_rotation');
        });
    }

    public function down(): void
    {
        Schema::table('child_settings', function (Blueprint $table) {
            $table->dropColumn(['topic_rotation', 'tests_since_level_review']);
        });
    }
};
