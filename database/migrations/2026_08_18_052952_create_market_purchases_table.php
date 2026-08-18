<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->unsignedInteger('coins_spent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_purchases');
    }
};
