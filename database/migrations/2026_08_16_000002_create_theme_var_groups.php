<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_var_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->json('values');
            $table->timestamps();
            $table->unique(['theme_id', 'name']);
        });

        Schema::table('theme_variables', function (Blueprint $table) {
            $table->foreignId('group_id')
                  ->nullable()
                  ->after('theme_id')
                  ->constrained('theme_var_groups')
                  ->nullOnDelete();
        });

        DB::statement('ALTER TABLE theme_variables MODIFY `values` JSON NULL');
    }

    public function down(): void
    {
        Schema::table('theme_variables', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
        Schema::dropIfExists('theme_var_groups');
    }
};
