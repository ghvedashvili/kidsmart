@extends('layouts.app')
@section('content')
<style>
    body { background: #e8f7ff !important; padding: 0 !important;
        background-image: radial-gradient(circle at 20% 50%, rgba(37,163,82,0.08) 0%, transparent 50%),
                          radial-gradient(circle at 80% 20%, rgba(249,201,19,0.10) 0%, transparent 40%);
    }

    :root { --green:#1a7a3c; --lg:#25a352; --yellow:#f9c913; --orange:#ff6b2b; --dark:#0d2818; }

    .wrap { max-width: 600px; margin: 0 auto; padding: 28px 16px 80px; font-family: 'Nunito', sans-serif; }

    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .back { font-family: 'Nunito', sans-serif; font-size: 0.8rem; font-weight: 800; color: var(--green); text-decoration: none; padding: 6px 14px; background: white; border-radius: 99px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .back:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
    .test-date { font-size: 0.72rem; font-weight: 700; color: #888; }

    /* Summary card */
    .summary-card {
        background: linear-gradient(135deg, var(--green), #0f5c2a);
        border-radius: 20px; padding: 24px 22px;
        color: white; margin-bottom: 28px;
        box-shadow: 0 8px 28px rgba(26,122,60,0.35);
        display: flex; align-items: center; gap: 20px;
    }
    .sum-icon { font-size: 3rem; }
    .sum-name { font-family: 'Fredoka One', cursive; font-size: 1.1rem; opacity: 0.85; margin-bottom: 4px; }
    .sum-score { font-family: 'Fredoka One', cursive; font-size: 2rem; line-height: 1; }
    .sum-pct {
        display: inline-block; margin-top: 6px; font-family: 'Fredoka One', cursive;
        font-size: 0.9rem; padding: 3px 14px; border-radius: 99px;
    }
    .pct-hi { background: #dcfce7; color: #15803d; }
    .pct-mid { background: #fef9c3; color: #ca8a04; }
    .pct-lo { background: #fee2e2; color: #dc2626; }
    .pitch-strip {
        height: 6px; border-radius: 3px; margin-bottom: 24px;
        background: repeating-linear-gradient(90deg, var(--lg) 0px, var(--lg) 24px, var(--green) 24px, var(--green) 48px);
    }

    /* Question cards */
    .q-card {
        background: white; border-radius: 20px;
        padding: 28px 20px 20px; margin-bottom: 18px;
        box-shadow: 0 6px 24px rgba(26,122,60,0.10);
        border-top: 6px solid var(--green);
        position: relative;
    }
    .q-card:nth-child(3n+2) { border-top-color: var(--yellow); }
    .q-card:nth-child(3n+3) { border-top-color: var(--orange); }

    .q-badge {
        position: absolute; top: -14px; left: 18px;
        background: var(--green); color: white;
        font-family: 'Fredoka One', cursive; font-size: 0.9rem;
        padding: 3px 14px; border-radius: 99px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .q-card:nth-child(3n+2) .q-badge { background: #c89800; }
    .q-card:nth-child(3n+3) .q-badge { background: var(--orange); }

    .q-icon { font-size: 1.8rem; margin-bottom: 8px; display: block; }
    .q-text { font-size: clamp(0.95rem, 3vw, 1.05rem); font-weight: 800; color: var(--dark); line-height: 1.7; margin-bottom: 16px; }

    .opts { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
    .opt-row {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 12px 10px; border-radius: 14px; border: 2.5px solid #e2e8f0;
        font-family: 'Fredoka One', cursive; font-size: clamp(0.95rem, 3.5vw, 1.1rem);
        color: #888; text-align: center; min-height: 52px; position: relative;
    }
    .opt-row.correct   { background: #dcfce7; border-color: #86efac; color: #15803d; }
    .opt-row.wrong     { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }
    .opt-row.missed    { background: #dcfce7; border-color: #86efac; color: #15803d; opacity: 0.55; }

    .opt-icon { font-size: 1rem; }
    .opt-tag {
        position: absolute; top: -9px; right: 8px;
        font-family: 'Nunito', sans-serif; font-size: 0.6rem; font-weight: 900;
        padding: 1px 8px; border-radius: 99px;
    }
    .tag-correct { background: #15803d; color: white; }
    .tag-wrong   { background: #dc2626; color: white; }
    .tag-answer  { background: #4f46e5; color: white; }

    .no-ans { font-family: 'Nunito', sans-serif; font-size: 0.75rem; font-weight: 800; color: #f59e0b; margin-top: 10px; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

<div class="wrap">
    <div class="topbar">
        <a href="{{ route('child.stats', $child) }}" class="back">← {{ $child->name }}</a>
        <span class="test-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
    </div>

    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <div class="summary-card">
        <div class="sum-icon">{{ $test->theme?->icon ?? '📝' }}</div>
        <div>
            <div class="sum-name">{{ $child->name }} · ტესტი #{{ $test->id }}</div>
            <div class="sum-score">{{ $test->correct_count }} / {{ $test->total_questions }}</div>
            <span class="sum-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">{{ $pct }}%</span>
        </div>
    </div>

    <div class="pitch-strip"></div>

    @php $icons = ['⚽','🏆','🥅','🧤','🎽','🏟️','⭐','🥇','🎯','🏅','🔥','💪']; @endphp
    @foreach($questions as $i => $q)
    @php
        $answer   = $answers->get($q->id);
        $selected = $answer?->selected_answer;
        $correct  = $q->correct_answer;
    @endphp
    <div class="q-card">
        <div class="q-badge">⚽ {{ $i + 1 }}</div>
        <span class="q-icon">{{ $icons[$i % count($icons)] }}</span>
        <div class="q-text">{{ $q->question_text }}</div>
        <div class="opts">
            @foreach($q->options as $opt)
            @php
                $isCor = $opt === $correct;
                $isSel = $opt === $selected;
                $cls   = $isSel && $isCor ? 'correct' : ($isSel && !$isCor ? 'wrong' : (!$isSel && $isCor ? 'missed' : ''));
            @endphp
            <div class="opt-row {{ $cls }}">
                <span class="opt-icon">
                    @if($isSel && $isCor) ✅
                    @elseif($isSel && !$isCor) ❌
                    @elseif(!$isSel && $isCor && $selected !== null) ⭕
                    @else &nbsp;
                    @endif
                </span>
                {{ $opt }}
                @if($isSel && $isCor)
                    <span class="opt-tag tag-correct">სწორი ✓</span>
                @elseif($isSel && !$isCor)
                    <span class="opt-tag tag-wrong">მისი პასუხი</span>
                @elseif(!$isSel && $isCor && $selected !== null)
                    <span class="opt-tag tag-answer">სწორი პასუხი</span>
                @endif
            </div>
            @endforeach
        </div>
        @if($selected === null)
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif
    </div>
    @endforeach
</div>
@endsection
