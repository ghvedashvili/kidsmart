<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * მასწავლებლის შენიშვნა არსებულ კითხვაზე: ვინ და როდის გაგზავნა განხილვაზე (review_status = flagged).
 * flagged_by რჩება გადაწყვეტის შემდეგაც — მასწავლებელი „ჩემს შენიშვნებში“ ხედავს შედეგს.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->foreignId('flagged_by')->nullable()->after('teacher_data')->constrained('users')->nullOnDelete();
            $table->timestamp('flagged_at')->nullable()->after('flagged_by');
        });
    }

    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flagged_by');
            $table->dropColumn('flagged_at');
        });
    }
};
