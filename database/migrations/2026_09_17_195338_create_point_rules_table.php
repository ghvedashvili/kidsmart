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
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('difficulty')->nullable();
            $table->enum('context', ['test', 'practice', 'olympiad']);
            $table->unsignedSmallInteger('points_per_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
