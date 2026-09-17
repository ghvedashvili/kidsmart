@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
body { font-family: 'Nunito', sans-serif; background: transparent !important; }
.wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
@media (min-width: 760px)  { .wrap { max-width: 700px; } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } }

.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem; font-weight: 800;
    color: #b45309; text-decoration: none;
    background: white; border-radius: 99px;
    padding: 8px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.page-hero {
    width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
    min-height: 140px; display: flex; flex-direction: column; justify-content: center;
    margin: 16px 0 0;
    background: linear-gradient(135deg, #fffbeb, #fde68a);
    box-shadow: 0 8px 20px rgba(217,119,6,0.18);
}
.page-hero.locked { opacity: 0.6; filter: grayscale(0.4); }
.page-hero-title { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.2rem; color: #78350f; margin-bottom: 4px; }
.page-hero-sub { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.8rem; color: #92400e; }

.flash-err { background:#fee2e2; color:#b91c1c; border-radius:12px; padding:10px 14px; font-size:0.78rem; font-weight:700; margin-top:16px; }

.section { padding: 20px 0 0; }
.sec-title {
    font-size: 0.72rem; font-weight: 800; letter-spacing: 0.12em;
    text-transform: uppercase; color: #64748b;
    margin-bottom: 12px; padding-left: 2px;
}

.req-card { background: white; border-radius: 14px; padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); display: flex; align-items: center; gap: 10px; }
.req-icon { font-size: 1.2rem; width: 26px; text-align: center; }
.req-icon.ok  { color: #16a34a; }
.req-icon.bad { color: #cbd5e1; }
.req-text { font-size: 0.78rem; font-weight: 700; color: #374151; }

.action-card { background: white; border-radius: 16px; padding: 22px 18px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
.action-card .big { font-size: 2rem; margin-bottom: 8px; }
.action-card .msg { font-size: 0.86rem; font-weight: 800; color: #1e293b; margin-bottom: 14px; }

.oly-btn {
    display: inline-block; border: none; border-radius: 99px; padding: 12px 28px;
    font-family: 'Nunito', sans-serif; font-size: 0.86rem; font-weight: 900;
    color: white; background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 4px 14px rgba(217,119,6,0.35); cursor: pointer; text-decoration: none;
}
.oly-btn:hover { filter: brightness(1.05); }
</style>

<div class="wrap">
<a href="{{ route('dashboard') }}" class="back-btn">← დაბრუნება</a>

@if(session('test_error'))
<div class="flash-err">{{ session('test_error') }}</div>
@endif

<div class="page-hero {{ $eligible_today || $already_attempted_today ? '' : 'locked' }}">
    <div class="page-hero-title">🏆 KidSmart ოლიმპიადა</div>
    <div class="page-hero-sub">
        @if($rule['olympiad_date'])
            ოლიმპიადის თარიღი: {{ $rule['olympiad_date']->format('d.m.Y') }}
        @else
            ოლიმპიადის თარიღი ჯერ არ არის დანიშნული
        @endif
    </div>
</div>

<div class="section">
    @if($already_attempted_today)
        <div class="action-card">
            <div class="big">🎉</div>
            @if($todays_test->completed_at)
                <div class="msg">დღევანდელი ოლიმპიადა დასრულებულია!</div>
                <a href="{{ route('test.result', $todays_test) }}" class="oly-btn">შედეგის ნახვა</a>
            @else
                <div class="msg">ოლიმპიადა დაწყებულია — გააგრძელე!</div>
                <a href="{{ route('test.show', $todays_test) }}" class="oly-btn">გაგრძელება</a>
            @endif
        </div>
    @elseif($eligible_today)
        <div class="action-card">
            <div class="big">🏆</div>
            <div class="msg">დღეს შეგიძლია ოლიმპიადაზე დაწერო!</div>
            <form method="POST" action="{{ route('olympiad.start') }}">
                @csrf
                <button type="submit" class="oly-btn">დაწყება</button>
            </form>
        </div>
    @else
        <div class="action-card">
            <div class="big">🔒</div>
            <div class="msg">{{ $reason }}</div>
        </div>
    @endif
</div>

<div class="section">
    <div class="sec-title">პირობები</div>
    <div class="req-card">
        <div class="req-icon {{ $level_met ? 'ok' : 'bad' }}">{{ $level_met ? '✓' : '○' }}</div>
        <div class="req-text">მაქსიმალური დონე შენს კლასში</div>
    </div>
    <div class="req-card">
        <div class="req-icon {{ $tests_met ? 'ok' : 'bad' }}">{{ $tests_met ? '✓' : '○' }}</div>
        <div class="req-text">ბოლო {{ $rule['days_window'] }} დღეში {{ $rule['tests_required'] }} ტესტი ({{ $recent_count }}/{{ $rule['tests_required'] }})</div>
    </div>
</div>

</div>
@endsection
