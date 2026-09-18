@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Goldman&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    @media (min-width: 760px)  { .wrap { max-width: 700px; } }
    @media (min-width: 1040px) { .wrap { max-width: 960px; } }

    .page-hero {
        width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
        min-height: 120px; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden; margin-bottom: 20px;
        background-image:
            linear-gradient(90deg, rgba(239,246,255,0.94) 0%, rgba(239,246,255,0.78) 45%, rgba(239,246,255,0.08) 68%),
            url('/img/practice-hero.jpg');
        background-size: cover; background-position: right center; background-repeat: no-repeat;
        box-shadow: 0 8px 20px rgba(37,99,235,0.18);
    }
    .page-hero-title { font-family:'Goldman', monospace; font-size:1.05rem; color:#1e40af; margin-bottom:4px; }
    .page-hero-sub { font-family:'Nunito', sans-serif; font-weight:800; font-size:0.75rem; color:#2563eb; }

    .pp-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 24px; }
    @media (min-width: 520px) { .pp-stat-grid { grid-template-columns: repeat(4, 1fr); } }
    .pp-stat-box {
        background: #fff; border-radius: 14px; padding: 14px; text-align: center;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1.5px solid #f5f5f5;
    }
    .pp-stat-val { font-family: 'Fredoka One', cursive; font-size: 1.4rem; color: #1a1a2e; line-height: 1; }
    .pp-stat-label { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.6rem; color: #999; text-transform: uppercase; letter-spacing: 0.02em; margin-top: 6px; }
    .pp-stat-box.wrong .pp-stat-val { color: #dc2626; }

    .pp-section-label { font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; }

    .pp-wrong-list { display: flex; flex-direction: column; gap: 10px; }
    .pp-wrong-card {
        background: #fff; border-radius: 14px; padding: 14px 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1.5px solid #fee2e2;
    }
    .pp-wrong-prompt { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.82rem; color: #1a1a2e; margin-bottom: 8px; white-space: pre-line; }
    .pp-wrong-row { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .pp-wrong-chip {
        font-family: 'Goldman', monospace; font-size: 0.62rem; border-radius: 100px; padding: 4px 10px;
    }
    .pp-wrong-chip.given { background: #fee2e2; color: #dc2626; }
    .pp-wrong-chip.correct { background: #dcfce7; color: #15803d; }
    .pp-wrong-meta { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.62rem; color: #aaa; margin-top: 8px; }

    .pp-empty { text-align: center; padding: 40px 16px; color: #aaa; font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.82rem; }

    .pp-pagination { margin-top: 16px; }
</style>

<div class="wrap">
    <div class="page-hero">
        <div class="page-hero-title">🎯 {{ $child->name }}-ის ვარჯიშის სტატისტიკა</div>
        <div class="page-hero-sub">{{ $child->childSetting?->grade?->name ?? 'კლასი —' }} · სულ და დღევანდელი შედეგები</div>
    </div>

    <div class="pp-stat-grid">
        <div class="pp-stat-box">
            <div class="pp-stat-val">{{ $totalAnswered }}</div>
            <div class="pp-stat-label">სულ კითხვა</div>
        </div>
        <div class="pp-stat-box">
            <div class="pp-stat-val">{{ $todayAnswered }}</div>
            <div class="pp-stat-label">დღეს კითხვა</div>
        </div>
        <div class="pp-stat-box wrong">
            <div class="pp-stat-val">{{ $totalWrong }}</div>
            <div class="pp-stat-label">სულ შეცდომა</div>
        </div>
        <div class="pp-stat-box wrong">
            <div class="pp-stat-val">{{ $todayWrong }}</div>
            <div class="pp-stat-label">დღეს შეცდომა</div>
        </div>
    </div>

    <div class="pp-section-label">შეცდომები · {{ $totalWrong }}</div>

    @if($wrongAnswers->isEmpty())
    <div class="pp-empty">ჯერ არ არის შეცდომები 🎉</div>
    @else
    <div class="pp-wrong-list">
        @foreach($wrongAnswers as $log)
        <div class="pp-wrong-card">
            <div class="pp-wrong-prompt">{{ $log->prompt }}</div>
            <div class="pp-wrong-row">
                <span class="pp-wrong-chip given">✎ პასუხი: {{ $log->given }}</span>
                <span class="pp-wrong-chip correct">✓ სწორია: {{ $log->correct }}</span>
            </div>
            <div class="pp-wrong-meta">{{ $log->topic?->name ?? '—' }} · {{ $log->created_at->format('d.m.Y H:i') }}</div>
        </div>
        @endforeach
    </div>
    <div class="pp-pagination">{{ $wrongAnswers->links() }}</div>
    @endif
</div>
@endsection
