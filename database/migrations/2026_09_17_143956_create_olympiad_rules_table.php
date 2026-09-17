<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olympiad_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unique('grade_id');
            $table->date('olympiad_date')->nullable();
            $table->unsignedTinyInteger('tests_required')->default(5);
            $table->unsignedTinyInteger('days_window')->default(14);
            $table->unsignedTinyInteger('questions_count')->default(20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olympiad_rules');
    }
};
