<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateQuestionsAnswerAndAddHints extends Migration
{
    public function up(): void
    {
        // 1️⃣ answer ველის ტიპის ცვლილება text-ად (უსაფრთხოებისთვის)
        Schema::table('questions', function (Blueprint $table) {
            $table->text('answer')->change();
        });

        // 2️⃣ ძველი მონაცემების კონვერტაცია JSON array-ად
        DB::table('questions')->get()->each(function ($q) {
            $answers = array_map('trim', explode(',', $q->answer));
            DB::table('questions')->where('id', $q->id)->update([
                'answer' => json_encode($answers),
            ]);
        });

        // 3️⃣ answer ველის ტიპის ოფიციალურად JSON-ად შეცვლა
        Schema::table('questions', function (Blueprint $table) {
            $table->json('answer')->change();
        });

        // 4️⃣ hints ველის დამატება JSON array-ად
        Schema::table('questions', function (Blueprint $table) {
            $table->json('hints')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        // 1️⃣ hints ველის წაშლა
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('hints');
        });

        // 2️⃣ answer JSON → string ფორმატში დაბრუნება
        DB::table('questions')->get()->each(function ($q) {
            $answers = json_decode($q->answer, true);
            DB::table('questions')->where('id', $q->id)->update([
                'answer' => implode(', ', $answers),
            ]);
        });

        // 3️⃣ answer ველის ტიპის დაბრუნება string-ად
        Schema::table('questions', function (Blueprint $table) {
            $table->string('answer')->change();
        });
    }
}
