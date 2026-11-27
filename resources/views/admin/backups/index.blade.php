@extends('layouts.app')
{{-- სარეზერვო ასლები: ავტომატური (ყოველ ღამე 03:15) + ხელით შექმნა, ჩამოტვირთვა, წაშლა --}}
@section('content')
@php
    $__fmt = fn ($b) => $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, round($b / 1024)) . ' KB';
    $__stale = !$lastAt || $lastAt->lt(now()->subDays(2));
@endphp
<style>
    .bk-wrap { display: flex; flex-direction: column; gap: 14px; max-width: 900px; }
    .bk-state { display: flex; align-items: center; gap: 14px; border-radius: 20px; padding: 16px 18px; }
    .bk-state.ok { background: #DFF5E3; color: #14612F; }
    .bk-state.warn { background: #FFF1C2; color: #5A3E00; }
    .bk-state i { font-size: 26px; }
    .bk-state b { display: block; font-size: 16px; font-weight: 900; }
    .bk-state span { font-size: 13px; }
    .bk-list { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; overflow: hidden; }
    .bk-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-top: 1px solid #F1EFEB; flex-wrap: wrap; }
    .bk-row:first-child { border-top: none; }
    .bk-ic { width: 40px; height: 40px; border-radius: 12px; background: #FFF3EA; color: #C24A0A; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .bk-name { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 2px; }
    .bk-name b { font-size: 14px; font-weight: 800; color: #2B2622; word-break: break-all; }
    .bk-name small { font-size: 12px; color: #6B625A; }
    .bk-new { font-size: 11px; font-weight: 800; background: #E2590F; color: #fff; border-radius: 99px; padding: 2px 8px; margin-left: 6px; }
    .bk-acts { display: flex; gap: 6px; }
    .bk-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 14px; border-radius: 12px; border: 1.5px solid #ECE9E4; background: #fff; color: #4F473F; font-family: inherit; font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: none; }
    .bk-btn:hover { border-color: #FFC49A; color: #C24A0A; }
    .bk-btn.del { color: #A12626; border-color: #F5C2C2; }
    .bk-btn.del:hover { background: #FDE2E2; }
    .bk-help { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; padding: 16px 18px; font-size: 14px; color: #4F473F; line-height: 1.6; }
    .bk-help h2 { margin: 0 0 6px; font-size: 15px; font-weight: 900; color: #2B2622; }
    .bk-help code { background: #F1EFEB; border-radius: 6px; padding: 1px 6px; font-size: 13px; }
    .bk-help ol { margin: 0; padding-left: 20px; }
</style>

<div class="bk-wrap">
    <header class="ks-page-head">
        <div class="ks-page-title">
            <span class="ks-eyebrow">სისტემა</span>
            <h1 class="ks-display ks-h1">სარეზერვო ასლები</h1>
        </div>
        <form method="POST" action="{{ route('admin.backups.store') }}" style="margin:0;" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<i class=&quot;bi bi-hourglass-split&quot;></i> იქმნება…';">
            @csrf
            <button type="submit" class="ks-btn ks-btn-primary"><i class="bi bi-database-add"></i> ახლავე შექმნა</button>
        </form>
    </header>

    @if(session('success'))<div class="ks-note" role="status" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;white-space:pre-line;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="ks-note" role="alert" style="background:#FDE2E2;color:#A12626;font-weight:700;white-space:pre-line;">{{ session('error') }}</div>@endif

    <div class="bk-state {{ $__stale ? 'warn' : 'ok' }}">
        <i class="bi {{ $__stale ? 'bi-exclamation-triangle' : 'bi-shield-check' }}" aria-hidden="true"></i>
        <div>
            @if($lastAt)
                <b>ბოლო ასლი: {{ $lastAt->format('d.m.Y H:i') }} ({{ $lastAt->locale('ka')->diffForHumans() }})</b>
            @else
                <b>ჯერ არცერთი ასლი არ არის</b>
            @endif
            <span>{{ $__stale ? 'ავტომატური ასლი 2 დღეზე მეტია არ შექმნილა — შეამოწმე, გაშვებულია თუ არა დაგეგმილი ამოცანა (იხ. ქვემოთ).' : 'ავტომატური ასლი იქმნება ყოველ ღამე 03:15-ზე. ინახება ბოლო 14.' }}</span>
        </div>
    </div>

    <div class="bk-list">
        @forelse($files as $__i => $__f)
        <div class="bk-row">
            <span class="bk-ic" aria-hidden="true"><i class="bi bi-file-earmark-zip"></i></span>
            <div class="bk-name">
                <b>{{ $__f['name'] }}@if($__i === 0)<span class="bk-new">ბოლო</span>@endif</b>
                <small>{{ $__f['at']->format('d.m.Y H:i') }} · {{ $__fmt($__f['size']) }}</small>
            </div>
            <div class="bk-acts">
                <a href="{{ route('admin.backups.download', $__f['name']) }}" class="bk-btn"><i class="bi bi-download"></i> ჩამოტვირთვა</a>
                <form method="POST" action="{{ route('admin.backups.destroy', $__f['name']) }}" style="margin:0;" onsubmit="return confirm('წაიშალოს {{ $__f['name'] }}?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bk-btn del" aria-label="წაშლა — {{ $__f['name'] }}"><i class="bi bi-trash3"></i></button>
                </form>
            </div>
        </div>
        @empty
        <div class="bk-row" style="justify-content:center;color:#6B625A;padding:28px;">ასლები ჯერ არ არის — დააჭირე „ახლავე შექმნა“.</div>
        @endforelse
    </div>

    <section class="bk-help">
        <h2>როგორ მუშაობს</h2>
        <ol>
            <li>ასლი ინახება სერვერზე: <code>storage/app/backups</code>. <b>ჩამოტვირთე დროდადრო სხვაგანაც</b> (კომპიუტერზე ან Google Drive-ზე) — თუ სერვერი დაზიანდება, მასზე შენახული ასლიც დაიკარგება.</li>
            <li>ავტომატური ასლისთვის სერვერზე უნდა იყოს გაშვებული Laravel-ის განრიგი: Linux-ზე cron — <code>* * * * * php /გზა/artisan schedule:run</code>; Windows-ზე Task Scheduler იგივე ბრძანებით ყოველ წუთს.</li>
            <li>აღდგენა: გახსენი .gz ფაილი (7-Zip, WinRAR) → მიიღებ .sql-ს → phpMyAdmin-ში „Import“ ან <code>mysql ბაზის_სახელი &lt; ფაილი.sql</code>. ეს ბაზას მთლიანად ჩაანაცვლებს ასლის მომენტით.</li>
            <li>ატვირთული სურათები (<code>storage/app/public</code>) ამ ასლში არ შედის — ისინი ცალკე დააკოპირე.</li>
        </ol>
    </section>
</div>
@endsection
