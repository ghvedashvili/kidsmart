<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('child_achievements', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('child_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('tier_level')->default(1)->after('slug');
        });

        // Backfill: every existing achievement was earned under whatever grade the child
        // is currently set to, since no grade-change feature existed before this migration
        // (same reasoning already used for tests.grade_id's backfill).
        DB::statement('
            UPDATE child_achievements
            INNER JOIN child_settings ON child_settings.user_id = child_achievements.child_id
            SET child_achievements.grade_id = child_settings.grade_id
            WHERE child_achievements.grade_id IS NULL
        ');

        Schema::table('child_achievements', function (Blueprint $table) {
            // add the new unique index before dropping the old one — the old (child_id, slug)
            // index is the only thing supporting the child_id foreign key, and MySQL refuses
            // to drop it while nothing else covers that column
            $table->unique(['child_id', 'slug', 'grade_id']);
            $table->dropUnique(['child_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('child_achievements', function (Blueprint $table) {
            // same ordering constraint as up(): add the old index before dropping the
            // one currently supporting the child_id foreign key
            $table->unique(['child_id', 'slug']);
            $table->dropUnique(['child_id', 'slug', 'grade_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['grade_id', 'tier_level']);
        });
    }
};
