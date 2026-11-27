<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * ბავშვის აქტიური დღეები — „დაბრუნების“ მაჩვენებლისთვის (ადმინ პანელი).
 * ერთი ჩანაწერი = ბავშვი შემოვიდა ამ დღეს (TrackChildActivity middleware წერს).
 * ძველი მონაცემი შეივსება დასრულებული ტესტებიდან და ვარჯიშის პასუხებიდან.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_activity_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();
            $table->date('day');
            $table->unique(['child_id', 'day']);
            $table->index('day');
        });

        // ისტორიის შევსება
        $pairs = collect();
        DB::table('tests')->whereNotNull('completed_at')
            ->selectRaw('child_id, DATE(completed_at) as day')->distinct()
            ->orderBy('child_id')->chunk(2000, function ($rows) use (&$pairs) { $pairs = $pairs->concat($rows); });
        if (Schema::hasTable('practice_answer_logs')) {
            DB::table('practice_answer_logs')
                ->selectRaw('child_id, DATE(created_at) as day')->distinct()
                ->orderBy('child_id')->chunk(2000, function ($rows) use (&$pairs) { $pairs = $pairs->concat($rows); });
        }
        $pairs->map(fn ($r) => ['child_id' => $r->child_id, 'day' => $r->day])
            ->unique(fn ($r) => $r['child_id'] . '|' . $r['day'])
            ->chunk(1000)
            ->each(fn ($c) => DB::table('child_activity_days')->insertOrIgnore($c->values()->all()));
    }

    public function down(): void
    {
        Schema::dropIfExists('child_activity_days');
    }
};
