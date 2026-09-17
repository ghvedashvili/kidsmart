<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_up_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('tests_required')->default(7);
            $table->unsignedTinyInteger('up_threshold')->default(85);
            $table->unsignedTinyInteger('down_threshold')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_up_rules');
    }
};
