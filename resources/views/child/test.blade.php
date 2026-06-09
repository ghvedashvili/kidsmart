<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>ტესტი</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --accent: #4f46e5;
    --accent2: #7c3aed;
    --green: #16a34a;
    --bg: #f1f5f9;
    --card: #ffffff;
    --radius: 16px;
}

body {
    font-family: 'Nunito', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    padding-bottom: 40px;
}

/* Header */
.test-header {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    padding: 20px 20px 28px;
    color: white;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 4px 20px rgba(79,70,229,0.3);
}
.test-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.test-title {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    opacity: 0.9;
}
.test-theme {
    font-size: 1.4rem;
}
.progress-bar-wrap {
    background: rgba(255,255,255,0.25);
    border-radius: 99px;
    height: 8px;
    overflow: hidden;
}
.progress-bar-fill {
    background: white;
    height: 100%;
    border-radius: 99px;
    transition: width 0.4s ease;
}
.progress-text {
    font-size: 0.72rem;
    opacity: 0.8;
    margin-top: 5px;
    text-align: right;
    font-weight: 700;
}

/* Questions */
.questions-wrap {
    padding: 20px 16px;
    max-width: 640px;
    margin: 0 auto;
}

/* One-at-a-time display */
.q-page { display: none; }
.q-page.active { display: block; }

.q-card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 24px 20px 20px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    margin-bottom: 12px;
}
.q-num {
    display: inline-block;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    color: white;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 3px 12px;
    border-radius: 99px;
    margin-bottom: 14px;
    letter-spacing: 0.06em;
}
.q-text {
    font-size: clamp(1rem, 3.5vw, 1.15rem);
    font-weight: 700;
    color: #1e293b;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Options */
.options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.option-label {
    position: relative;
    cursor: pointer;
    display: block;
}
.option-label input {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
}
.option-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 10px;
    font-family: 'Nunito', sans-serif;
    font-size: clamp(1rem, 4vw, 1.2rem);
    font-weight: 800;
    color: #475569;
    transition: all 0.15s;
    text-align: center;
    user-select: none;
    min-height: 58px;
}
.option-btn .opt-letter {
    font-size: 0.7rem;
    font-weight: 900;
    color: #94a3b8;
    min-width: 16px;
}
.option-label:hover .option-btn {
    border-color: var(--accent);
    background: #eef2ff;
    color: #4f46e5;
    transform: translateY(-1px);
}
.option-label input:checked + .option-btn {
    border-color: var(--accent);
    background: linear-gradient(135deg, #eef2ff, #ede9fe);
    color: var(--accent);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
    transform: translateY(-1px);
}

/* Nav buttons */
.q-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
    gap: 10px;
}
.nav-btn {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 0.85rem;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.15s;
}
.nav-btn:hover { border-color: var(--accent); color: var(--accent); }
.nav-btn:disabled { opacity: 0.3; cursor: default; }
.nav-btn.next {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-color: transparent;
    color: white;
    flex: 1;
}
.nav-btn.next:hover { opacity: 0.9; transform: translateY(-1px); }

/* Answer dots */
.answer-dots {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin: 16px auto 0;
    max-width: 360px;
}
.a-dot {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    border: 2px solid rgba(255,255,255,0.4);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.6rem;
    font-weight: 900;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}
.a-dot.answered {
    background: rgba(255,255,255,0.9);
    border-color: white;
    color: var(--accent);
}
.a-dot.current {
    border-color: white;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.4);
    transform: scale(1.15);
}

/* Submit */
.submit-wrap {
    max-width: 640px;
    margin: 0 auto;
    padding: 0 16px;
}
.submit-btn {
    width: 100%;
    background: linear-gradient(135deg, var(--green), #15803d);
    border: none;
    border-radius: var(--radius);
    color: white;
    font-family: 'Nunito', sans-serif;
    font-size: 1.05rem;
    font-weight: 900;
    padding: 18px;
    cursor: pointer;
    letter-spacing: 0.04em;
    transition: all 0.2s;
    box-shadow: 0 4px 20px rgba(22,163,74,0.35);
    display: none;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(22,163,74,0.4); }
.submit-btn.visible { display: block; }

.unanswered-warn {
    font-family: 'Nunito', sans-serif;
    font-size: 0.8rem;
    color: #ef4444;
    font-weight: 700;
    text-align: center;
    margin-top: 10px;
    display: none;
}
</style>
</head>
<body>

<form method="POST" action="{{ route('test.submit', $test) }}" id="testForm">
@csrf

<div class="test-header">
    <div class="test-header-top">
        <div class="test-title">{{ auth()->user()->name }} · ტესტი</div>
        <div class="test-theme">{{ $test->theme?->icon ?? '📝' }}</div>
    </div>
    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressFill" style="width: {{ (1/count($questions))*100 }}%"></div>
    </div>
    <div class="progress-text" id="progressText">1 / {{ count($questions) }}</div>

    <div class="answer-dots" id="answerDots">
        @foreach($questions as $i => $q)
        <div class="a-dot {{ $i === 0 ? 'current' : '' }}" id="dot-{{ $i }}"
            onclick="goTo({{ $i }})">{{ $i+1 }}</div>
        @endforeach
    </div>
</div>

<div class="questions-wrap">
    @foreach($questions as $i => $q)
    <div class="q-page {{ $i === 0 ? 'active' : '' }}" id="page-{{ $i }}">
        <div class="q-card">
            <div class="q-num">კითხვა {{ $i+1 }}</div>
            <div class="q-text">{{ $q->question_text }}</div>
            <div class="options-grid">
                @php $letters = ['A','B','C','D','E']; @endphp
                @foreach($q->options as $j => $opt)
                <label class="option-label">
                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}"
                        onchange="onAnswer({{ $i }})">
                    <div class="option-btn">
                        <span class="opt-letter">{{ $letters[$j] }}</span>
                        {{ $opt }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="q-nav">
            <button type="button" class="nav-btn" onclick="goTo({{ $i-1 }})"
                {{ $i === 0 ? 'disabled' : '' }}>← უკან</button>
            @if($i < count($questions) - 1)
            <button type="button" class="nav-btn next" id="next-{{ $i }}"
                onclick="goTo({{ $i+1 }})">შემდეგი →</button>
            @else
            <button type="button" class="nav-btn next" onclick="trySubmit()">დასრულება ✓</button>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="submit-wrap">
    <button type="submit" class="submit-btn" id="submitBtn">
        ✓ პასუხების გაგზავნა
    </button>
    <div class="unanswered-warn" id="warnMsg"></div>
</div>

</form>

<script>
let current = 0;
const totalQ = {{ count($questions) }};
const answered = new Array(totalQ).fill(false);

function goTo(n) {
    if (n < 0 || n >= totalQ) return;
    document.getElementById('page-' + current).classList.remove('active');
    document.getElementById('dot-' + current).classList.remove('current');
    current = n;
    document.getElementById('page-' + current).classList.add('active');
    document.getElementById('dot-' + current).classList.add('current');
    const pct = ((current + 1) / totalQ * 100).toFixed(0);
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressText').textContent = (current + 1) + ' / ' + totalQ;
    window.scrollTo({ top: 0, behavior: 'smooth' });

    const allAnswered = answered.every(Boolean);
    document.getElementById('submitBtn').classList.toggle('visible', allAnswered);
}

function onAnswer(i) {
    answered[i] = true;
    document.getElementById('dot-' + i).classList.add('answered');
    const allAnswered = answered.every(Boolean);
    document.getElementById('submitBtn').classList.toggle('visible', allAnswered);
    if (i < totalQ - 1) {
        setTimeout(() => goTo(i + 1), 300);
    }
}

function trySubmit() {
    const missing = answered.filter(Boolean).length;
    if (missing < totalQ) {
        const unanswered = answered.map((a, i) => a ? null : i + 1).filter(Boolean);
        document.getElementById('warnMsg').style.display = 'block';
        document.getElementById('warnMsg').textContent =
            'გამოტოვებული კითხვები: ' + unanswered.join(', ');
        const firstMissing = unanswered[0] - 1;
        goTo(firstMissing);
        return;
    }
    document.getElementById('testForm').submit();
}
</script>
</body>
</html>
