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
        Schema::table('tests', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->after('total_questions')->constrained()->nullOnDelete();
        });

        // Backfill: every existing test was taken under whatever grade the child is
        // currently set to, since no grade-change feature existed before this migration.
        DB::statement('
            UPDATE tests
            INNER JOIN child_settings ON child_settings.user_id = tests.child_id
            SET tests.grade_id = child_settings.grade_id
            WHERE tests.grade_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropColumn('grade_id');
        });
    }
};
