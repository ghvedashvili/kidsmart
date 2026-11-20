<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * redesign-kidsmart:
 *  - users.photo_url — მშობლის Google პროფილის სურათი (მთავარ გვერდზე ჩანს)
 *  - child_settings.parent_notify — რა შემთხვევებზე მიიღოს მშობელმა push შეტყობინება
 *    (null = ყველა ჩართულია, ძველი ქცევის მსგავსად)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo_url', 500)->nullable()->after('avatar');
        });
        Schema::table('child_settings', function (Blueprint $table) {
            $table->json('parent_notify')->nullable()->after('unlimited_tests');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
        Schema::table('child_settings', function (Blueprint $table) {
            $table->dropColumn('parent_notify');
        });
    }
};
