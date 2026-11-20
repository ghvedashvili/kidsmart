@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero blue">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎯</span>
        <span class="ks-cp-eyebrow">{{ $child->childSetting?->grade?->name ?? 'კლასი —' }} · სულ და დღევანდელი შედეგები</span>
        <h1 class="ks-cp-title">{{ $child->name }} · ვარჯიში</h1>
    </div>
    @include('parent.partials.kid-switcher')

    <div class="ks-cp-stats">
        <div class="ks-cp-stat"><b>{{ $totalAnswered }}</b><span>სულ კითხვა</span></div>
        <div class="ks-cp-stat"><b>{{ $todayAnswered }}</b><span>დღეს კითხვა</span></div>
        <div class="ks-cp-stat" style="background:#FDECEA;box-shadow:none;"><b style="color:#B42318;">{{ $totalWrong }}</b><span>სულ შეცდომა</span></div>
        <div class="ks-cp-stat" style="background:#FDECEA;box-shadow:none;"><b style="color:#B42318;">{{ $todayWrong }}</b><span>დღეს შეცდომა</span></div>
    </div>

    <h2 class="ks-cp-h2">შეცდომები · {{ $totalWrong }}</h2>

    @if($wrongAnswers->isEmpty())
    <div class="ks-cp-empty">
        <div class="ks-cp-empty-ic">🎉</div>
        <div class="ks-cp-empty-t">ჯერ არ არის შეცდომები</div>
    </div>
    @else
    <div class="ks-cp-list">
        @foreach($wrongAnswers as $log)
        <div class="ks-cp-card" style="display:flex;flex-direction:column;gap:10px;">
            <div style="font-size:16px;font-weight:700;white-space:pre-line;">{{ $log->prompt }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <span class="ks-cp-pct lo">✎ პასუხი: {{ $log->given }}</span>
                <span class="ks-cp-pct hi">✓ სწორია: {{ $log->correct }}</span>
            </div>
            <div class="ks-cp-row-s">{{ $log->topic?->name ?? '—' }} · {{ $log->created_at->format('d.m.Y H:i') }}</div>
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap" style="display:flex;flex-wrap:wrap;gap:6px;">{{ $wrongAnswers->links('admin.pagination') }}</div>
    @endif
</div>
@endsection
