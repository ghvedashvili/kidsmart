<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::create('questions', function (Blueprint $table) {
    $table->id();
    $table->integer('level'); // აღარ გვაქვს unique()
    $table->text('question');
    $table->text('rules')->nullable();
    $table->string('answer');
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
{
    Schema::dropIfExists('questions');
}
}
