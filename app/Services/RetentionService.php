<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * „დაბრუნების“ მაჩვენებლები ადმინ პანელისთვის — child_activity_days ცხრილიდან.
 * მთავარი კითხვა: რამდენ დღეს ბრუნდება ბავშვი კვირაში?
 */
class RetentionService
{
    public function summary(): ?array
    {
        if (! Schema::hasTable('child_activity_days')) return null;

        $today = Carbon::today();
        $d7    = $today->copy()->subDays(6);    // ბოლო 7 დღე, დღევანდელის ჩათვლით
        $p7    = $today->copy()->subDays(13);   // წინა 7 დღე

        // ბოლო 7 დღე: თითო ბავშვს რამდენი აქტიური დღე ჰქონდა
        $perChild = DB::table('child_activity_days')
            ->where('day', '>=', $d7->toDateString())
            ->selectRaw('child_id, COUNT(*) as days')
            ->groupBy('child_id')
            ->pluck('days', 'child_id');

        $active7 = $perChild->count();
        $dist = array_fill(1, 7, 0);
        foreach ($perChild as $n) $dist[min(7, max(1, (int) $n))]++;

        $activeToday = DB::table('child_activity_days')->where('day', $today->toDateString())->count();
        $active30    = DB::table('child_activity_days')->where('day', '>=', $today->copy()->subDays(29)->toDateString())->distinct()->count('child_id');
        $children    = User::where('role', 'child')->count();

        // კვირიდან კვირაზე დაბრუნება: წინა კვირის აქტიურებიდან რამდენი მოვიდა ამ კვირასაც
        $prevIds = DB::table('child_activity_days')
            ->whereBetween('day', [$p7->toDateString(), $d7->copy()->subDay()->toDateString()])
            ->distinct()->pluck('child_id');
        $returned = $prevIds->isEmpty() ? 0 : $perChild->keys()->intersect($prevIds)->count();

        // ახლები: დარეგისტრირდა 8–35 დღის წინ → დაბრუნდა თუ არა რეგისტრაციიდან მე-7 დღის შემდეგ
        $newKids = User::where('role', 'child')
            ->whereBetween('created_at', [$today->copy()->subDays(35), $today->copy()->subDays(8)->endOfDay()])
            ->get(['id', 'created_at']);
        $newBack = 0;
        if ($newKids->isNotEmpty()) {
            $lastDay = DB::table('child_activity_days')->whereIn('child_id', $newKids->pluck('id'))
                ->selectRaw('child_id, MAX(day) as last_day')->groupBy('child_id')->pluck('last_day', 'child_id');
            foreach ($newKids as $k) {
                $ld = $lastDay[$k->id] ?? null;
                if ($ld && Carbon::parse($ld)->gte($k->created_at->copy()->startOfDay()->addDays(7))) $newBack++;
            }
        }

        // 8 კვირის ტენდენცია: აქტიური ბავშვები და საშუალო დღეები კვირაში
        $weeks = [];
        for ($w = 7; $w >= 0; $w--) {
            $from = $today->copy()->subDays($w * 7 + 6);
            $to   = $today->copy()->subDays($w * 7);
            $row  = DB::table('child_activity_days')
                ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('COUNT(DISTINCT child_id) as kids, COUNT(*) as days')
                ->first();
            $kids = (int) ($row->kids ?? 0);
            $weeks[] = [
                'label' => $to->format('d.m'),
                'kids'  => $kids,
                'avg'   => $kids ? round(($row->days ?? 0) / $kids, 1) : 0,
            ];
        }

        return [
            'children'     => $children,
            'active_today' => $activeToday,
            'active7'      => $active7,
            'active30'     => $active30,
            'avg_days'     => $active7 ? round($perChild->sum() / $active7, 1) : 0,
            'dist'         => $dist,
            'wow'          => ['prev' => $prevIds->count(), 'back' => $returned, 'pct' => $prevIds->count() ? (int) round($returned * 100 / $prevIds->count()) : null],
            'new'          => ['total' => $newKids->count(), 'back' => $newBack, 'pct' => $newKids->count() ? (int) round($newBack * 100 / $newKids->count()) : null],
            'weeks'        => $weeks,
        ];
    }
}
