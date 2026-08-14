<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 30)->unique();
            $table->text('description')->nullable();
            $table->unsignedDecimal('price_monthly', 8, 2)->default(0);
            $table->unsignedDecimal('price_yearly', 8, 2)->default(0);
            $table->unsignedTinyInteger('max_children')->default(0)->comment('0 = unlimited');
            $table->unsignedTinyInteger('max_difficulty')->default(5)->comment('1-5');
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
