@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }

    .back { display:inline-flex; align-items:center; gap:6px; margin:16px 0 24px; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#0891b2; text-decoration:none; padding:8px 16px; background:white; border-radius:99px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    .back:hover { box-shadow:0 4px 12px rgba(0,0,0,0.12); }

    .child-cta {
        display: flex; align-items: center; justify-content: center; gap: 12px;
        width: 100%; padding: 20px; margin-bottom: 20px; box-sizing: border-box;
        color: white; font-family: 'Fredoka One', cursive;
        font-size: 1.1rem; letter-spacing: 0.03em;
        text-decoration: none; border-radius: 20px;
        transition: all 0.2s;
    }
    .child-cta:hover { transform: translateY(-2px); color: white; }
    .child-cta.resume {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 6px 24px rgba(245,158,11,0.35);
    }
    .child-cta.resume:hover { box-shadow: 0 10px 32px rgba(245,158,11,0.45); }

    .stats-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:28px; }
    .stat-card { background:white; border-radius:16px; padding:16px; text-align:center; box-shadow:0 4px 14px rgba(0,0,0,0.06); }
    .stat-val { font-family:'Fredoka One',cursive; font-size:1.8rem; color:#0c4a6e; line-height:1; }
    .stat-lbl { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.65rem; color:#94a3b8; margin-top:4px; }

    .test-link {
        display:flex; align-items:center; gap:14px;
        background:white; border-radius:16px; padding:14px 16px; margin-bottom:10px;
        text-decoration:none; box-shadow:0 3px 12px rgba(0,0,0,0.06);
        transition:box-shadow 0.15s;
    }
    .test-link:hover { box-shadow:0 6px 20px rgba(0,0,0,0.10); }
    .t-icon { font-size:1.8rem; flex-shrink:0; }
    .t-info { flex:1; min-width:0; }
    .t-date { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.65rem; color:#94a3b8; margin-bottom:3px; }
    .t-score { font-family:'Fredoka One',cursive; font-size:0.95rem; color:#0c4a6e; }
    .t-pct { font-family:'Nunito',sans-serif; font-size:0.72rem; font-weight:900; padding:4px 12px; border-radius:99px; flex-shrink:0; }
    .pct-hi  { background:#dcfce7; color:#15803d; }
    .pct-mid { background:#fef9c3; color:#ca8a04; }
    .pct-lo  { background:#fee2e2; color:#dc2626; }
    .t-arrow { font-size:0.85rem; color:#cbd5e1; flex-shrink:0; }

    .empty { text-align:center; padding:60px 20px; }
    .empty-icon { font-size:3rem; margin-bottom:12px; }
    .empty-txt { font-family:'Fredoka One',cursive; font-size:1.05rem; color:#94a3b8; }
</style>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>

    @if($activeTest)
    <a href="{{ route('test.show', $activeTest) }}" class="child-cta resume">
        📝 გააგრძელე ტესტი →
    </a>
    @endif

    @if($totalTests > 0)
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-val">{{ $totalTests }}</div>
            <div class="stat-lbl">ტესტი სულ</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $avgScore }}%</div>
            <div class="stat-lbl">საშ. შედეგი</div>
        </div>
    </div>
    @endif

    @forelse($tests as $test)
    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <a href="{{ route('my.test.show', $test) }}" class="test-link">
        <div class="t-icon">{{ $test->theme?->icon ?? '📝' }}</div>
        <div class="t-info">
            <div class="t-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</div>
            <div class="t-score">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</div>
        </div>
        <div class="t-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">{{ $pct }}%</div>
        <div class="t-arrow">›</div>
    </a>
    @empty
    <div class="empty">
        <div class="empty-icon">📝</div>
        <div class="empty-txt">ჯერ ტესტი არ გაქვს დაწერილი</div>
    </div>
    @endforelse
</div>
@endsection
