<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('difficulty')->default(1)->comment('1-5');
            $table->text('template_text');
            $table->string('correct_formula');
            $table->json('num_config')->comment('{"N1":{"min":1,"max":9},"N2":{"min":1,"max":9}}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_templates');
    }
};
