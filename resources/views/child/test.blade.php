@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
:root {
    --green: #1a7a3c;
    --lg:    #25a352;
    --yellow: #f9c913;
    --orange: #ff6b2b;
    --sky:   #e8f7ff;
    --dark:  #0d2818;
}

body {
    font-family: 'Nunito', sans-serif !important;
    background: var(--sky) !important;
    background-image:
        radial-gradient(circle at 20% 50%, rgba(37,163,82,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(249,201,19,0.10) 0%, transparent 40%) !important;
    padding-bottom: 60px !important;
}

/* Sticky header sits below the fixed nav (56 px) */
.hdr {
    background: linear-gradient(135deg, var(--green), #0f5c2a);
    padding: 18px 20px 14px;
    position: sticky;
    top: 56px;
    z-index: 50;
    box-shadow: 0 4px 20px rgba(10,60,25,0.35);
}
.hdr-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.hdr-name { font-family: 'Fredoka One', cursive; font-size: 1.05rem; color: rgba(255,255,255,0.9); letter-spacing: 0.04em; }
.hdr-theme { font-size: 1.6rem; }
.prog-bar-wrap { background: rgba(255,255,255,0.25); border-radius: 99px; height: 8px; overflow: hidden; }
.prog-bar-fill { background: var(--yellow); height: 100%; border-radius: 99px; transition: width 0.4s ease; }
.prog-text { font-family: 'Fredoka One', cursive; font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 5px; text-align: right; letter-spacing: 0.06em; }
.pitch-strip {
    height: 6px; margin: 12px 0 0;
    background: repeating-linear-gradient(90deg, var(--lg) 0px, var(--lg) 24px, var(--green) 24px, var(--green) 48px);
    border-radius: 3px;
}

.wrap { max-width: 640px; margin: 0 auto; padding: 24px 16px 20px; }

.q-card {
    background: white; border-radius: 20px; padding: 28px 20px 20px;
    box-shadow: 0 6px 24px rgba(26,122,60,0.12);
    border-top: 6px solid var(--green); position: relative;
    margin-bottom: 20px; transition: box-shadow 0.2s;
}
.q-card:nth-child(3n+2) { border-top-color: var(--yellow); }
.q-card:nth-child(3n+3) { border-top-color: var(--orange); }
.q-card.answered { box-shadow: 0 6px 24px rgba(26,122,60,0.22); }

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
.q-text { font-size: clamp(0.95rem, 3.2vw, 1.08rem); font-weight: 800; color: var(--dark); line-height: 1.7; margin-bottom: 18px; }

.opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.opt-lbl { position: relative; cursor: pointer; display: block; }
.opt-lbl input { position: absolute; opacity: 0; width: 0; height: 0; }
.opt-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    background: #f0faf4; border: 2.5px solid #c5e8d0; border-radius: 14px;
    padding: 13px 10px; font-family: 'Fredoka One', cursive;
    font-size: clamp(1rem, 4vw, 1.15rem); color: #3a7a50; text-align: center;
    transition: all 0.15s; min-height: 56px; user-select: none;
}
.q-card:nth-child(3n+2) .opt-btn { background: #fffbea; border-color: #f0d960; color: #7a6000; }
.q-card:nth-child(3n+3) .opt-btn { background: #fff5f0; border-color: #ffc4a0; color: #7a3010; }
.opt-lbl:hover .opt-btn { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.opt-lbl input:checked + .opt-btn {
    border-color: var(--green); background: var(--green); color: white;
    box-shadow: 0 4px 16px rgba(26,122,60,0.35); transform: translateY(-2px);
}
.q-card:nth-child(3n+2) .opt-lbl input:checked + .opt-btn { border-color: #c89800; background: #c89800; }
.q-card:nth-child(3n+3) .opt-lbl input:checked + .opt-btn { border-color: var(--orange); background: var(--orange); }

.check-mark {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--green); color: white; font-size: 0.9rem;
    display: none; align-items: center; justify-content: center;
    position: absolute; top: -10px; right: -10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.q-card.answered .check-mark { display: flex; }
.q-card:nth-child(3n+2).answered .check-mark { background: #c89800; }
.q-card:nth-child(3n+3).answered .check-mark { background: var(--orange); }

.submit-wrap { max-width: 640px; margin: 0 auto; padding: 0 16px 40px; }
.submit-btn {
    width: 100%; background: linear-gradient(135deg, var(--green), #0f5c2a);
    border: none; border-radius: 18px; color: white;
    font-family: 'Fredoka One', cursive; font-size: 1.2rem; letter-spacing: 0.04em;
    padding: 18px; cursor: pointer; transition: all 0.2s;
    box-shadow: 0 6px 24px rgba(26,122,60,0.4); display: none;
}
.submit-btn.vis { display: block; animation: popIn 0.4s cubic-bezier(0.175,0.885,0.32,1.275); }
.submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 32px rgba(26,122,60,0.5); }
@keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.warn { font-family: 'Nunito', sans-serif; font-size: 0.8rem; color: #e74c3c; font-weight: 800; text-align: center; margin-top: 10px; display: none; }
</style>

<form method="POST" action="{{ route('test.submit', $test) }}" id="testForm">
@csrf

<div class="hdr">
    <div class="hdr-top">
        <div class="hdr-name">{{ auth()->user()->name }} · ტესტი</div>
        <div class="hdr-theme">{{ $test->theme?->icon ?? '📝' }}</div>
    </div>
    <div class="prog-bar-wrap">
        <div class="prog-bar-fill" id="progFill" style="width:0%"></div>
    </div>
    <div class="prog-text" id="progText">0 / {{ count($questions) }} პასუხი</div>
    <div class="pitch-strip"></div>
</div>

<div class="wrap">
    @php $icons = ['⚽','🏆','🥅','🧤','🎽','🏟️','⭐','🥇','🎯','🏅','🔥','💪']; @endphp
    @foreach($questions as $i => $q)
    <div class="q-card" id="card-{{ $i }}">
        <div class="q-badge">⚽ {{ $i + 1 }}</div>
        <div class="check-mark">✓</div>
        <span class="q-icon">{{ $icons[$i % count($icons)] }}</span>
        <div class="q-text">{{ $q->question_text }}</div>
        <div class="opts">
            @foreach($q->options as $opt)
            <label class="opt-lbl">
                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}"
                    data-qid="{{ $q->id }}" data-idx="{{ $i }}"
                    onchange="onAnswer({{ $i }}, {{ $q->id }}, this.value)">
                <div class="opt-btn">{{ $opt }}</div>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="submit-wrap">
    <button type="submit" class="submit-btn" id="submitBtn">
        ✓ პასუხების გაგზავნა
    </button>
    <div class="warn" id="warnMsg"></div>
</div>

</form>

<script>
const totalQ = {{ count($questions) }};
const CACHE_KEY = 'test_{{ $test->id }}';
let answeredCount = 0;
const answeredSet = new Set();

function onAnswer(i, qid, val) {
    const cache = JSON.parse(localStorage.getItem(CACHE_KEY) || '{}');
    cache[qid] = val;
    localStorage.setItem(CACHE_KEY, JSON.stringify(cache));

    if (!answeredSet.has(i)) {
        answeredSet.add(i);
        answeredCount++;
        document.getElementById('card-' + i).classList.add('answered');
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width = pct + '%';
        document.getElementById('progText').textContent = answeredCount + ' / ' + totalQ + ' პასუხი';
    }
    if (answeredCount === totalQ) {
        document.getElementById('submitBtn').classList.add('vis');
    }
}

(function restoreCache() {
    const cache = JSON.parse(localStorage.getItem(CACHE_KEY) || '{}');
    Object.entries(cache).forEach(([qid, val]) => {
        const radio = document.querySelector(
            'input[type=radio][data-qid="' + qid + '"][value="' + CSS.escape(val) + '"]'
        );
        if (radio) {
            radio.checked = true;
            const idx = parseInt(radio.dataset.idx);
            if (!answeredSet.has(idx)) {
                answeredSet.add(idx);
                answeredCount++;
                document.getElementById('card-' + idx).classList.add('answered');
            }
        }
    });
    if (answeredCount > 0) {
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width = pct + '%';
        document.getElementById('progText').textContent = answeredCount + ' / ' + totalQ + ' პასუხი';
    }
    if (answeredCount === totalQ) {
        document.getElementById('submitBtn').classList.add('vis');
    }
})();

document.getElementById('testForm').addEventListener('submit', function() {
    localStorage.removeItem(CACHE_KEY);
});
</script>
@endsection
