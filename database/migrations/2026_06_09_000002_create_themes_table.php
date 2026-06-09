<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('🎯');
            $table->timestamps();
        });

        Schema::create('theme_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('variable_name');
            $table->json('values');
            $table->timestamps();

            $table->unique(['theme_id', 'variable_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_variables');
        Schema::dropIfExists('themes');
    }
};
