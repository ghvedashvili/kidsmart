<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('icon')->default('🎁');
            $table->string('category')->nullable();
            $table->unsignedInteger('coin_cost')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_items');
    }
};
