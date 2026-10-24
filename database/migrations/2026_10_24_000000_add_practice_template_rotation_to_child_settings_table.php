<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_settings', function (Blueprint $table) {
            $table->json('practice_template_rotation')->nullable()->after('template_rotation');
        });
    }

    public function down(): void
    {
        Schema::table('child_settings', function (Blueprint $table) {
            $table->dropColumn('practice_template_rotation');
        });
    }
};
