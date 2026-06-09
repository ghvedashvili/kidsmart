@extends('layouts.app')
@section('content')
<style>
    body { background: #f5f5f5 !important; padding: 0 !important; }
    .wrap { max-width: 560px; margin: 0 auto; padding: 36px 20px 80px; font-family: 'Goldman', monospace; }

    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
    .back { font-size: 0.68rem; color: #bbb; letter-spacing: 0.08em; text-decoration: none; transition: color 0.2s; }
    .back:hover { color: #555; }

    .test-title { font-size: clamp(1rem, 4vw, 1.3rem); color: #111; letter-spacing: 0.06em; margin-bottom: 4px; }
    .test-meta { font-size: 0.65rem; color: #bbb; letter-spacing: 0.08em; margin-bottom: 28px; }

    .summary {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 16px 20px; margin-bottom: 28px;
        display: flex; align-items: center; gap: 20px;
    }
    .sum-icon { font-size: 2rem; flex-shrink: 0; }
    .sum-score { font-size: 1.6rem; color: #111; letter-spacing: 0.04em; line-height: 1; }
    .sum-pct {
        font-size: 0.78rem; font-weight: 700; padding: 3px 12px;
        border-radius: 99px; margin-top: 6px; display: inline-block;
    }
    .pct-hi { background: #dcfce7; color: #16a34a; }
    .pct-mid { background: #fef9c3; color: #ca8a04; }
    .pct-lo { background: #fee2e2; color: #dc2626; }

    .section-label { font-size: 0.62rem; color: #aaa; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; }

    .q-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 18px 18px 14px; margin-bottom: 10px;
    }
    .q-num { font-size: 0.6rem; color: #bbb; letter-spacing: 0.1em; margin-bottom: 8px; }
    .q-text { font-size: 0.9rem; color: #111; letter-spacing: 0.02em; line-height: 1.6; margin-bottom: 14px; }

    .options-list { display: flex; flex-direction: column; gap: 6px; }
    .opt-row {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 8px; border: 1px solid #f0f0f0;
        font-size: 0.82rem; color: #555; letter-spacing: 0.02em;
    }
    .opt-row.correct { background: #dcfce7; border-color: #86efac; color: #15803d; }
    .opt-row.wrong   { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }
    .opt-row.missed  { background: #dcfce7; border-color: #86efac; color: #15803d; opacity: 0.6; }
    .opt-icon { font-size: 0.75rem; flex-shrink: 0; min-width: 14px; }
    .opt-badge {
        margin-left: auto; font-size: 0.58rem; letter-spacing: 0.08em;
        padding: 2px 8px; border-radius: 99px; flex-shrink: 0;
    }
    .badge-correct { background: #16a34a; color: #fff; }
    .badge-wrong   { background: #dc2626; color: #fff; }
    .badge-answer  { background: #e0e7ff; color: #4f46e5; }

    .no-answer { font-size: 0.68rem; color: #f59e0b; letter-spacing: 0.06em; margin-top: 8px; }
</style>

<div class="wrap">
    <div class="topbar">
        <a href="{{ route('child.stats', $child) }}" class="back">← {{ $child->name }}</a>
        <span style="font-size:0.65rem;color:#ccc;letter-spacing:0.06em;">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
    </div>

    <div class="test-title">{{ $test->theme?->icon ?? '📝' }} ტესტი #{{ $test->id }}</div>
    <div class="test-meta">{{ $test->completed_at->format('d MMMM Y') }}</div>

    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <div class="summary">
        <div class="sum-icon">{{ $test->theme?->icon ?? '📝' }}</div>
        <div>
            <div class="sum-score">{{ $test->correct_count }} / {{ $test->total_questions }}</div>
            <div class="sum-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">{{ $pct }}%</div>
        </div>
    </div>

    <div class="section-label">კითხვები და პასუხები</div>

    @foreach($questions as $i => $q)
    @php
        $answer   = $answers->get($q->id);
        $selected = $answer?->selected_answer;
        $correct  = $q->correct_answer;
    @endphp
    <div class="q-card">
        <div class="q-num">კითხვა {{ $i + 1 }}</div>
        <div class="q-text">{{ $q->question_text }}</div>
        <div class="options-list">
            @foreach($q->options as $opt)
            @php
                $isCorrect  = $opt === $correct;
                $isSelected = $opt === $selected;
                $cls = '';
                if ($isSelected && $isCorrect)  $cls = 'correct';
                elseif ($isSelected && !$isCorrect) $cls = 'wrong';
                elseif (!$isSelected && $isCorrect) $cls = 'missed';
            @endphp
            <div class="opt-row {{ $cls }}">
                <span class="opt-icon">
                    @if($isSelected && $isCorrect) ✓
                    @elseif($isSelected && !$isCorrect) ✗
                    @elseif(!$isSelected && $isCorrect) ○
                    @else ·
                    @endif
                </span>
                <span>{{ $opt }}</span>
                @if($isSelected && $isCorrect)
                    <span class="opt-badge badge-correct">სწორი</span>
                @elseif($isSelected && !$isCorrect)
                    <span class="opt-badge badge-wrong">მისი პასუხი</span>
                @elseif(!$isSelected && $isCorrect && $selected !== null)
                    <span class="opt-badge badge-answer">სწორი პასუხი</span>
                @endif
            </div>
            @endforeach
        </div>
        @if($selected === null)
        <div class="no-answer">⚠ პასუხი არ გაუცია</div>
        @endif
    </div>
    @endforeach
</div>
@endsection
