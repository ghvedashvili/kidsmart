@extends('layouts.app')
@section('content')
<div class="ks-cp">
    @if(session('test_error'))
    <div class="ks-cp-flash err">{{ session('test_error') }}</div>
    @endif

    <div class="ks-cp-hero yellow {{ $eligible_today || $already_attempted_today ? '' : 'locked' }}">
        <span class="ks-cp-hero-ic" aria-hidden="true">🏆</span>
        <span class="ks-cp-eyebrow" style="color:#3D2A00;">KidSmart ოლიმპიადა</span>
        <h1 class="ks-cp-title">ოლიმპიადა</h1>
        <span class="ks-cp-sub" style="color:#3D2A00;">
            @if($rule['olympiad_date'])
                თარიღი: {{ $rule['olympiad_date']->format('d.m.Y') }}
            @else
                თარიღი ჯერ არ არის დანიშნული
            @endif
        </span>
    </div>

    <div class="ks-row">
        <div class="ks-wide ks-cp-card ks-cp-big">
            @if($already_attempted_today)
                <div class="ks-cp-big-ic">🎉</div>
                @if($todays_test->completed_at)
                    <div class="ks-cp-big-t">დღევანდელი ოლიმპიადა დასრულებულია!</div>
                    <a href="{{ route('test.result', $todays_test) }}" class="ks-cp-btn">შედეგის ნახვა <i class="bi bi-arrow-right"></i></a>
                @else
                    <div class="ks-cp-big-t">ოლიმპიადა დაწყებულია — გააგრძელე!</div>
                    <a href="{{ route('test.show', $todays_test) }}" class="ks-cp-btn orange">გაგრძელება <i class="bi bi-arrow-right"></i></a>
                @endif
            @elseif($eligible_today)
                <div class="ks-cp-big-ic">🏆</div>
                <div class="ks-cp-big-t">დღეს შეგიძლია ოლიმპიადაზე დაწერო!</div>
                <form method="POST" action="{{ route('olympiad.start') }}">
                    @csrf
                    <button type="submit" class="ks-cp-btn orange">დაწყება <i class="bi bi-arrow-right"></i></button>
                </form>
            @else
                <div class="ks-cp-big-ic">🔒</div>
                <div class="ks-cp-big-t">{{ $reason }}</div>
            @endif
        </div>

        <div class="ks-narrow ks-cp-card" style="display:flex;flex-direction:column;gap:14px;">
            <h2 class="ks-cp-h2" style="margin:0;">პირობები</h2>
            <div class="ks-cp-req">
                <span class="ks-cp-req-ic {{ $level_met ? 'ok' : '' }}">{{ $level_met ? '✓' : '○' }}</span>
                <span class="ks-cp-req-t">მაქსიმალური დონე შენს კლასში</span>
            </div>
            <div class="ks-cp-req">
                <span class="ks-cp-req-ic {{ $tests_met ? 'ok' : '' }}">{{ $tests_met ? '✓' : '○' }}</span>
                <span class="ks-cp-req-t">ბოლო {{ $rule['days_window'] }} დღეში {{ $rule['tests_required'] }} ტესტი ({{ $recent_count }}/{{ $rule['tests_required'] }})</span>
            </div>
        </div>
    </div>
</div>
@endsection
