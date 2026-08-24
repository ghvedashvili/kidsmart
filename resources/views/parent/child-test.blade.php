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
    .q-text { font-size: clamp(0.95rem, 3vw, 1.05rem); font-weight: 800; color: var(--dark); line-height: 1.7; margin-bottom: 6px; }
    .q-hint { font-size: 0.78rem; color: #64748b; font-style: italic; margin-bottom: 14px; line-height: 1.5; }

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

    .vid-btn { display:inline-flex; align-items:center; gap:6px; margin-top:12px; padding:7px 16px; background:#ede9fe; border-radius:99px; font-family:'Nunito',sans-serif; font-weight:800; font-size:0.75rem; color:#6d28d9; text-decoration:none; border:none; cursor:pointer; }
    .vid-btn:hover { background:#ddd6fe; }
    .vid-panel-q { margin-top:10px; display:none; }
    .vid-panel-q iframe { width:100%; aspect-ratio:16/9; border:none; border-radius:12px; }
    .vid-title-q { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.75rem; color:#64748b; margin-top:6px; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">

<div class="wrap">
    <div class="topbar">
        @if(!empty($isChild))
        <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>
        @else
        <a href="{{ route('child.stats', $child) }}" class="back">← {{ $child->name }}</a>
        @endif
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
        $qType = $q->question_type ?? 'multiple_choice';
        if ($qType === 'multiple_choice' && str_starts_with((string)$correct, '{')) {
            $caKeys = array_keys(json_decode($correct, true) ?? []);
            $qType  = (count($caKeys) && str_contains((string)($caKeys[0] ?? ''), ',')) ? 'pyramid' : 'code';
        }
    @endphp
    <div class="q-card">
        <div class="q-badge">⚽ {{ $i + 1 }}</div>
        <span class="q-icon">{{ $icons[$i % count($icons)] }}</span>

        @if($qType === 'pyramid')
        @php
            $pyrRows   = json_decode($q->question_text, true) ?? [];
            $solutions = json_decode($correct, true) ?? [];
            $userCells = $selected ? (json_decode($selected, true) ?? []) : [];
            $pyrOk     = $answer?->is_correct;
        @endphp
        <div class="q-text" style="margin-bottom:12px;">🔺 მათემატიკური პირამიდა</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:5px;margin-bottom:12px;">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:5px;">
                @foreach($row as $c => $val)
                @php
                    $pos    = "$r,$c";
                    $sol    = $solutions[$pos] ?? null;
                    $uv     = ($sol !== null && isset($userCells[$pos])) ? $userCells[$pos] : null;
                    $cellOk = $sol !== null && (int)($uv ?? PHP_INT_MIN) === $sol;
                @endphp
                @if($val !== null)
                <div style="width:44px;height:44px;border-radius:10px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $val }}</div>
                @elseif($cellOk)
                <div style="width:44px;height:44px;border-radius:10px;background:#16a34a;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $sol }}</div>
                @elseif($uv !== null)
                <div style="width:54px;min-height:44px;border-radius:10px;background:#fee2e2;border:2px solid #ef4444;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2px 3px;gap:1px;">
                    <span style="color:#ef4444;font-size:0.78rem;font-weight:800;line-height:1;">{{ $uv }}</span>
                    <span style="color:#94a3b8;font-size:0.6rem;line-height:1;">/</span>
                    <span style="color:#16a34a;font-size:0.78rem;font-weight:800;line-height:1;">{{ $sol }}</span>
                </div>
                @else
                <div style="width:44px;height:44px;border-radius:10px;border:2px dashed #fca5a5;background:#fff1f2;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;">
                    <span style="color:#94a3b8;font-size:0.62rem;line-height:1;">—</span>
                    <span style="color:#16a34a;font-size:0.72rem;font-weight:800;line-height:1;">{{ $sol }}</span>
                </div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
        @if($pyrOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;">❌ არასწორი — წითელი = შეცდომა</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @elseif($qType === 'code')
        @php
            $codeQ    = json_decode($q->question_text, true) ?? [];
            $codeAns  = json_decode($correct, true) ?? [];
            $userInps = $selected ? (json_decode($selected, true) ?? []) : [];
            $codeOk   = $answer?->is_correct;
        @endphp
        <div class="q-text" style="margin-bottom:10px;">🕵️ კოდის გაშიფვრა</div>
        <div style="background:#fff8e7;border-radius:8px;padding:8px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;font-size:0.88rem;font-weight:700;color:#374151;">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div>{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.6rem;color:#94a3b8;margin-bottom:6px;letter-spacing:0.08em;">სამიზნე კოდი</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            @php
                $cv   = $codeAns[$pos] ?? null;
                $uv   = $userInps[$pos] ?? null;
                $ok   = $cv !== null && (int)($uv ?? PHP_INT_MIN) === (int)$cv;
            @endphp
            <div style="text-align:center;">
                <div style="width:44px;height:44px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:3px;">{{ $sym }}</div>
                @if($selected !== null)
                    @if($ok)
                    <div style="font-size:0.78rem;font-weight:900;color:#16a34a;">{{ $cv }}</div>
                    @elseif($uv !== null)
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0;">
                        <span style="color:#ef4444;font-size:0.75rem;font-weight:800;line-height:1.2;">{{ $uv }}</span>
                        <span style="color:#94a3b8;font-size:0.55rem;line-height:1;">/</span>
                        <span style="color:#16a34a;font-size:0.75rem;font-weight:800;line-height:1.2;">{{ $cv }}</span>
                    </div>
                    @else
                    <div style="color:#f59e0b;font-size:0.75rem;font-weight:800;">— / {{ $cv }}</div>
                    @endif
                @else
                <div style="font-size:0.72rem;color:#16a34a;font-weight:800;">{{ $cv }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @if($codeOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;">❌ არასწორი</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @else
        <div class="q-text">{{ $q->question_text }}</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
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
        @endif

        @if(!empty($isChild) && $selected !== $correct)
        @php $topicVids = $q->template?->topic?->videos ?? collect(); @endphp
        @if($topicVids->isNotEmpty())
        <button class="vid-btn" onclick="toggleVidQ('vq{{ $q->id }}', this)">📹 ახსნა-ვიდეო</button>
        <div class="vid-panel-q" id="vq{{ $q->id }}">
            @foreach($topicVids as $vid)
            <div style="margin-bottom:10px;">
                <iframe src="{{ $vid->embedUrl() }}"
                    title="{{ $vid->title ?: $q->template?->topic?->name }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe>
                @if($vid->title)<div class="vid-title-q">{{ $vid->title }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>
    @endforeach
</div>
<script>
function toggleVidQ(id, btn) {
    const el = document.getElementById(id);
    const open = el.style.display === 'block';
    el.style.display = open ? 'none' : 'block';
    btn.textContent = open ? '📹 ახსნა-ვიდეო' : '📹 დახურვა';
}
</script>
@endsection
