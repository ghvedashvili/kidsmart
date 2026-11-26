<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* თამაშების მართვა: რომელი თამაში ჩართულია და რომელ კლასებს უჩანს (grade_ids = null → ყველას) */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->boolean('is_active')->default(true);
            $table->json('grade_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};
