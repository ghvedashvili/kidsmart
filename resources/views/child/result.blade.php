<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>შედეგი</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Nunito', sans-serif; background: #f1f5f9; min-height: 100vh; }

.hero {
    padding: 40px 20px 32px;
    text-align: center;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
}
.score-circle {
    width: 130px; height: 130px;
    border-radius: 50%;
    border: 5px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.15);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    margin: 0 auto 20px;
    backdrop-filter: blur(4px);
}
.score-num {
    font-size: 2.8rem;
    font-weight: 900;
    line-height: 1;
}
.score-total {
    font-size: 0.85rem;
    font-weight: 700;
    opacity: 0.75;
}
.score-label {
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 6px;
    opacity: 0.9;
}
.score-pct {
    font-size: 0.82rem;
    opacity: 0.65;
    margin-bottom: 20px;
}
.stars {
    font-size: 1.8rem;
    letter-spacing: 6px;
    margin-bottom: 6px;
}
.theme-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.15); border-radius: 99px;
    padding: 5px 14px; font-size: 0.78rem; font-weight: 700;
}

/* Review */
.review-wrap { max-width: 640px; margin: 0 auto; padding: 20px 16px 80px; }
.section-title {
    font-size: 0.72rem; font-weight: 800; color: #94a3b8;
    letter-spacing: 0.1em; text-transform: uppercase;
    margin: 20px 0 12px;
}
.q-review {
    background: white; border-radius: 14px; padding: 16px 18px;
    margin-bottom: 10px;
    border-left: 4px solid #e2e8f0;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.q-review.correct { border-left-color: #16a34a; }
.q-review.wrong   { border-left-color: #ef4444; }
.q-review.skipped { border-left-color: #f59e0b; }
.q-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.q-badge {
    font-size: 0.68rem; font-weight: 900; padding: 2px 9px;
    border-radius: 99px; color: white;
}
.q-badge.correct { background: #16a34a; }
.q-badge.wrong   { background: #ef4444; }
.q-badge.skipped { background: #f59e0b; }
.q-text-r { font-size: 0.92rem; font-weight: 700; color: #334155; line-height: 1.5; margin-bottom: 8px; }
.ans-row { font-size: 0.8rem; font-weight: 700; }
.ans-correct { color: #16a34a; }
.ans-wrong   { color: #ef4444; }
.ans-note    { color: #64748b; }

/* Bottom */
.bottom-btns {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: white; border-top: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex; gap: 10px; max-width: 640px; margin: 0 auto;
}
.btn-retry, .btn-home {
    flex: 1; border-radius: 12px; padding: 13px;
    font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 800;
    cursor: pointer; border: none; transition: all 0.18s;
    text-decoration: none; display: flex; align-items: center; justify-content: center;
}
.btn-retry {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    box-shadow: 0 4px 14px rgba(79,70,229,0.3);
}
.btn-retry:hover { transform: translateY(-1px); }
.btn-home { background: #f1f5f9; color: #64748b; }
.btn-home:hover { background: #e2e8f0; }
</style>
</head>
<body>

@php
    $correct = $test->correct_count;
    $total   = $test->total_questions;
    $pct     = $total > 0 ? round($correct / $total * 100) : 0;
    $stars   = $pct >= 90 ? 5 : ($pct >= 70 ? 4 : ($pct >= 50 ? 3 : ($pct >= 30 ? 2 : 1)));
@endphp

<div class="hero">
    <div class="score-circle">
        <div class="score-num">{{ $correct }}</div>
        <div class="score-total">/ {{ $total }}</div>
    </div>
    <div class="stars">{{ str_repeat('⭐', $stars) }}</div>
    <div class="score-label">
        @if($pct >= 90) გამოჩენილია! 🏆
        @elseif($pct >= 70) კარგი სამუშაო! 👏
        @elseif($pct >= 50) გააგრძელე! 💪
        @else მეცადე კიდევ! 📚
        @endif
    </div>
    <div class="score-pct">{{ $pct }}% სწორი</div>
    @if($test->theme)
    <div class="theme-badge">{{ $test->theme->icon }} {{ $test->theme->name }}</div>
    @endif
</div>

<div class="review-wrap">
    <div class="section-title">კითხვების განხილვა</div>

    @foreach($questions as $i => $q)
    @php
        $ans = $answers[$q->id] ?? null;
        $status = $ans === null ? 'skipped' : ($ans->is_correct ? 'correct' : 'wrong');
    @endphp
    <div class="q-review {{ $status }}">
        <div class="q-header">
            <span class="q-badge {{ $status }}">
                @if($status === 'correct') ✓ სწორი
                @elseif($status === 'wrong') ✗ არასწორი
                @else — გამოტოვებული
                @endif
            </span>
            <span style="font-size:0.68rem;color:#94a3b8;font-weight:700;">{{ $i+1 }}</span>
        </div>
        <div class="q-text-r">{{ $q->question_text }}</div>
        <div class="ans-row">
            @if($status === 'correct')
                <span class="ans-correct">✓ {{ $ans->selected_answer }}</span>
            @elseif($status === 'wrong')
                <span class="ans-wrong">✗ {{ $ans->selected_answer }}</span>
                <span class="ans-note"> → სწორი: <strong>{{ $q->correct_answer }}</strong></span>
            @else
                <span class="ans-note">პასუხი არ გასცემია · სწორი: <strong>{{ $q->correct_answer }}</strong></span>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div style="height:80px;"></div>
<div class="bottom-btns">
    <a href="{{ route('test.start') }}" class="btn-retry">ახალი ტესტი →</a>
    <a href="{{ route('dashboard') }}" class="btn-home">🏠 მთავარი</a>
</div>

</body>
</html>
