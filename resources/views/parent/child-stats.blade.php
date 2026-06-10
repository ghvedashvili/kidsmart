@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; padding: 0 !important; }
    .wrap {
        max-width: 520px; margin: 0 auto;
        padding: 36px 20px 80px;
        font-family: 'Goldman', monospace;
    }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
    .back { font-size: 0.68rem; color: #bbb; letter-spacing: 0.08em; text-decoration: none; transition: color 0.2s; }
    .back:hover { color: #555; }
    .settings-link { font-size: 0.68rem; color: #bbb; letter-spacing: 0.08em; text-decoration: none; transition: color 0.2s; }
    .settings-link:hover { color: #555; }

    .child-title { font-size: clamp(1rem, 4vw, 1.3rem); color: #111; letter-spacing: 0.06em; margin-bottom: 4px; }
    .child-sub { font-size: 0.65rem; color: #bbb; letter-spacing: 0.1em; margin-bottom: 28px; }

    .stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 32px; }
    .stat-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 16px 12px; text-align: center;
    }
    .stat-val { font-size: clamp(1.4rem, 5vw, 1.9rem); color: #111; letter-spacing: 0.04em; line-height: 1; }
    .stat-label { font-size: 0.58rem; color: #bbb; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 6px; }

    .section-label { font-size: 0.62rem; color: #aaa; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; }

    .test-row {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 14px 16px; margin-bottom: 8px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; transition: border-color 0.2s;
    }
    .test-row:hover { border-color: #bbb; }
    .test-icon { font-size: 1.4rem; flex-shrink: 0; }
    .test-info { flex: 1; min-width: 0; }
    .test-date { font-size: 0.62rem; color: #bbb; letter-spacing: 0.06em; margin-bottom: 3px; }
    .test-score { font-size: 0.88rem; color: #111; letter-spacing: 0.04em; }
    .test-pct {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;
        padding: 3px 10px; border-radius: 99px; flex-shrink: 0;
    }
    .pct-hi { background: #dcfce7; color: #16a34a; }
    .pct-mid { background: #fef9c3; color: #ca8a04; }
    .pct-lo { background: #fee2e2; color: #dc2626; }

    .empty { text-align: center; padding: 40px 20px; color: #ccc; font-size: 0.72rem; letter-spacing: 0.08em; }
</style>

<div class="wrap">
    <div class="topbar">
        <a href="{{ route('dashboard') }}" class="back">← დაბრუნება</a>
        <a href="{{ route('child.settings.edit', $child) }}" class="settings-link">პარამეტრები →</a>
    </div>

    <div class="child-title">{{ $child->name }}</div>
    <div class="child-sub">სტატისტიკა</div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val">{{ $totalTests }}</div>
            <div class="stat-label">ტესტი სულ</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $avgScore !== null ? $avgScore . '%' : '—' }}</div>
            <div class="stat-label">საშ. შედეგი</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $todayCount }}@if($required > 0)<span style="font-size:0.9rem;color:#bbb;">/{{ $required }}</span>@endif</div>
            <div class="stat-label">დღეს</div>
        </div>
    </div>

    <div class="section-label">ტესტების ისტორია</div>

    @forelse($tests as $test)
    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <a href="{{ route('child.test.show', [$child, $test]) }}" class="test-row">
        <div class="test-icon">{{ $test->theme?->icon ?? '📝' }}</div>
        <div class="test-info">
            <div class="test-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</div>
            <div class="test-score">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</div>
        </div>
        <div class="test-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">
            {{ $pct }}%
        </div>
    </a>
    @empty
    <div class="empty">ტესტები ჯერ არ დაწერილა</div>
    @endforelse
</div>
@endsection
