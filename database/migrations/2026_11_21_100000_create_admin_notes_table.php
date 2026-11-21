<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* ადმინის პირადი ჩანაწერები (ერთი ჩანაწერი თითო ადმინზე) + პანელის გაშლილი/ჩაკეცილი მდგომარეობა */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('body')->nullable();
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notes');
    }
};
