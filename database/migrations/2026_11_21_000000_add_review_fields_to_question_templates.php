<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * მასწავლებლის კითხვების განხილვა: მასწავლებელი ქმნის → ადმინი ადასტურებს ან აბრუნებს კომენტარით.
 * review_status: null — ძველი/ადმინის კითხვა (დადასტურებულად ითვლება), pending, rejected, approved.
 * teacher_data — მასწავლებლის მიერ შეყვანილი ორიგინალი (ტექსტი, პასუხი, არასწორი პასუხები…) რედაქტირებისთვის.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('review_status', 20)->nullable()->after('is_active')->index();
            $table->text('review_comment')->nullable()->after('review_status');
            $table->foreignId('reviewed_by')->nullable()->after('review_comment')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->timestamp('submitted_at')->nullable()->after('reviewed_at');
            $table->json('teacher_data')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_status', 'review_comment', 'reviewed_at', 'submitted_at', 'teacher_data']);
        });
    }
};
