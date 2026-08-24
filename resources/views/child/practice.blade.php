@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: #f0f9ff !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 0 0 80px; }

    /* ── Header ── */
    .topbar { display:flex; align-items:center; justify-content:space-between; padding:16px 16px 0; }
    .back { font-family:'Nunito',sans-serif; font-size:0.78rem; font-weight:800; color:#0284c7; text-decoration:none; padding:5px 14px; background:white; border-radius:99px; box-shadow:0 2px 8px rgba(0,0,0,0.07); }
    .title { font-family:'Fredoka One',cursive; font-size:1rem; color:#0c4a6e; }

    /* ── Progress bar ── */
    .progress-wrap { padding:12px 16px 0; }
    .prog-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
    .level-badge { font-family:'Fredoka One',cursive; font-size:0.85rem; color:#4f46e5; }
    .streak-dots { display:flex; gap:5px; }
    .sdot { width:12px; height:12px; border-radius:50%; background:#e2e8f0; transition:background 0.2s; }
    .sdot.filled { background:#f59e0b; }
    .prog-bar { height:6px; background:#e0f2fe; border-radius:99px; overflow:hidden; }
    .prog-fill { height:100%; background:linear-gradient(90deg,#38bdf8,#0284c7); border-radius:99px; transition:width 0.5s; }

    /* ── Question card ── */
    .q-card { background:white; border-radius:24px; margin:16px; padding:28px 20px 24px; box-shadow:0 8px 28px rgba(0,0,0,0.08); min-height:200px; }
    .q-label { font-family:'Nunito',sans-serif; font-weight:900; font-size:0.62rem; color:#94a3b8; letter-spacing:0.12em; text-transform:uppercase; margin-bottom:16px; }
    .q-text { font-family:'Fredoka One',cursive; font-size:1.15rem; color:#0c4a6e; line-height:1.5; margin-bottom:6px; }
    .q-hint { font-family:'Nunito',sans-serif; font-weight:700; font-size:0.75rem; color:#94a3b8; font-style:italic; margin-bottom:18px; }

    /* ── MC options ── */
    .opts { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .opt-btn {
        padding:14px 10px; border-radius:16px; border:2.5px solid #e2e8f0;
        font-family:'Fredoka One',cursive; font-size:1rem; color:#334155;
        background:white; cursor:pointer; transition:all 0.15s; text-align:center;
    }
    .opt-btn:hover:not(:disabled) { border-color:#38bdf8; background:#f0f9ff; }
    .opt-btn.correct  { border-color:#86efac; background:#dcfce7; color:#15803d; }
    .opt-btn.wrong    { border-color:#fca5a5; background:#fee2e2; color:#dc2626; }
    .opt-btn.reveal   { border-color:#86efac; background:#dcfce7; color:#15803d; opacity:0.6; }

    /* ── Pyramid ── */
    .pyramid { display:flex; flex-direction:column; align-items:center; gap:6px; }
    .pyr-row { display:flex; gap:6px; }
    .pyr-cell {
        width:58px; height:58px; border-radius:14px; display:flex; align-items:center; justify-content:center;
        font-family:'Fredoka One',cursive; font-size:1.1rem;
    }
    .pyr-cell.given { background:#4f46e5; color:white; box-shadow:0 4px 10px rgba(79,70,229,0.3); }
    .pyr-cell.hidden input {
        width:100%; height:100%; border:2.5px solid #a5b4fc; border-radius:12px; background:#eef2ff;
        color:#4f46e5; font-family:'Fredoka One',cursive; font-size:1.1rem;
        text-align:center; outline:none; padding:0;
    }
    .pyr-cell.hidden input:focus { border-color:#4f46e5; background:#e0e7ff; }
    .pyr-cell.hidden.correct-cell input { border-color:#86efac !important; background:#dcfce7 !important; color:#15803d !important; }
    .pyr-cell.hidden.wrong-cell input   { border-color:#fca5a5 !important; background:#fee2e2 !important; color:#dc2626 !important; }

    /* ── Check button ── */
    .check-btn {
        display:block; width:calc(100% - 32px); margin:0 16px;
        padding:15px; border:none; border-radius:18px;
        font-family:'Fredoka One',cursive; font-size:1.05rem; color:white;
        background:linear-gradient(135deg,#0ea5e9,#0284c7);
        box-shadow:0 6px 20px rgba(2,132,199,0.3); cursor:pointer;
        transition:all 0.15s;
    }
    .check-btn:hover { transform:translateY(-1px); }
    .check-btn:disabled { opacity:0.5; cursor:not-allowed; transform:none; }

    /* ── Feedback bar ── */
    .feedback { margin:12px 16px 0; padding:14px 18px; border-radius:16px; font-family:'Fredoka One',cursive; font-size:1rem; display:none; }
    .feedback.correct { background:#dcfce7; color:#15803d; }
    .feedback.wrong   { background:#fee2e2; color:#dc2626; }

    /* ── Next button ── */
    .next-btn {
        display:none; width:calc(100% - 32px); margin:10px 16px 0;
        padding:14px; border:none; border-radius:18px;
        font-family:'Fredoka One',cursive; font-size:1rem; color:white;
        background:linear-gradient(135deg,#22c55e,#16a34a);
        box-shadow:0 6px 20px rgba(22,163,74,0.3); cursor:pointer; transition:all 0.15s;
    }
    .next-btn:hover { transform:translateY(-1px); }

    /* ── Level-up overlay ── */
    .levelup-overlay {
        display:none; position:fixed; inset:0; background:rgba(79,70,229,0.92);
        z-index:100; flex-direction:column; align-items:center; justify-content:center; text-align:center;
    }
    .levelup-overlay.show { display:flex; }
    .lu-emoji { font-size:4rem; margin-bottom:12px; }
    .lu-title { font-family:'Fredoka One',cursive; font-size:2rem; color:white; margin-bottom:6px; }
    .lu-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.9rem; color:rgba(255,255,255,0.7); margin-bottom:28px; }
    .lu-btn { padding:14px 36px; background:white; border:none; border-radius:18px; font-family:'Fredoka One',cursive; font-size:1.05rem; color:#4f46e5; cursor:pointer; }

    /* ── Code question ── */
    .code-eq-box { background:#fff8e7;border-radius:12px;padding:12px 14px;margin-bottom:14px;border:1.5px dashed #ffe194; }
    .code-eq { font-family:'Fredoka One',cursive; font-size:1rem; color:#374151; margin:3px 0; }
    .code-target-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:10px; }
    .code-sym-box { width:52px;height:52px;border-radius:12px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0; }
    .code-inp-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap; }
    .code-inp { width:52px;height:52px;border-radius:12px;border:2.5px solid #a5b4fc;background:#eef2ff;color:#4f46e5;font-family:'Fredoka One',cursive;font-size:1.1rem;text-align:center;outline:none;padding:0;transition:border-color 0.2s,background 0.2s; }
    .code-inp:focus { border-color:#4f46e5;background:#e0e7ff; }
    .code-inp.correct-inp { border-color:#86efac !important;background:#dcfce7 !important;color:#15803d !important; }
    .code-inp.wrong-inp   { border-color:#fca5a5 !important;background:#fee2e2 !important;color:#dc2626 !important; }

    /* ── Spinner ── */
    .spinner { display:none; text-align:center; padding:40px; font-family:'Fredoka One',cursive; color:#94a3b8; font-size:1rem; }
    .spinner.show { display:block; }
</style>

<div class="wrap">
    <div class="topbar">
        <a href="{{ route('practice.topics') }}" class="back">← სავარჯიშოები</a>
        <div class="title">{{ $type === 'pyramid' ? '🔺 პირამიდა' : '📘 ' . $topic->name }}</div>
    </div>

    <div class="progress-wrap">
        <div class="prog-row">
            <span class="level-badge" id="levelBadge">დონე {{ $session->level }}</span>
            <div class="streak-dots" id="streakDots">
                <div class="sdot {{ $session->streak >= 1 ? 'filled' : '' }}"></div>
                <div class="sdot {{ $session->streak >= 2 ? 'filled' : '' }}"></div>
                <div class="sdot {{ $session->streak >= 3 ? 'filled' : '' }}"></div>
            </div>
        </div>
        <div class="prog-bar"><div class="prog-fill" id="progFill" style="width:{{ ($session->level - 1) * 25 }}%"></div></div>
    </div>

    <div class="spinner show" id="spinner">⏳ იტვირთება...</div>

    <div class="q-card" id="qCard" style="display:none;">
        <div class="q-label" id="qLabel">კითხვა</div>

        {{-- MC question --}}
        <div id="mcArea" style="display:none;">
            <div class="q-text" id="qText"></div>
            <div class="q-hint" id="qHint" style="display:none;"></div>
            <div class="opts" id="optsGrid"></div>
        </div>

        {{-- Pyramid question --}}
        <div id="pyrArea" style="display:none;">
            <div class="q-text" style="margin-bottom:18px;">შეავსე ცარიელი უჯრები</div>
            <div class="pyramid" id="pyramid"></div>
        </div>

        {{-- Code question --}}
        <div id="codeArea" style="display:none;">
            <div class="q-text" style="margin-bottom:14px;">🕵️ გაშიფრე კოდი</div>
            <div class="code-eq-box" id="codeEqs"></div>
            <div style="font-size:0.6rem;color:#94a3b8;text-align:center;margin-bottom:6px;letter-spacing:0.08em;">სამიზნე კოდი</div>
            <div class="code-target-row" id="codeSyms"></div>
            <div class="code-inp-row" id="codeInps"></div>
        </div>
    </div>

    <div class="feedback" id="feedback"></div>
    <button class="check-btn" id="checkBtn" style="display:none;" onclick="checkAnswer()">შემოწმება ✨</button>
    <button class="next-btn" id="nextBtn" onclick="loadQuestion()">შემდეგი კითხვა →</button>
</div>

<div class="levelup-overlay" id="levelupOverlay">
    <div class="lu-emoji">🎉</div>
    <div class="lu-title" id="luTitle">შემდეგი დონე!</div>
    <div class="lu-sub" id="luSub"></div>
    <button class="lu-btn" onclick="closeLevelUp()">გავაგრძელო →</button>
</div>

<script>
const SLUG  = @json($slug);
const CSRF  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let currentQ = null;

// ── Load question ────────────────────────────────────────────────────────
async function loadQuestion() {
    showSpinner();
    document.getElementById('feedback').style.display  = 'none';
    document.getElementById('nextBtn').style.display   = 'none';
    document.getElementById('checkBtn').style.display  = 'none';
    document.getElementById('checkBtn').disabled       = false;

    try {
        const r = await fetch(`/practice/${SLUG}/question`);
        const q = await r.json();
        if (q.error) { showSpinner(q.error); return; }
        currentQ = q;
        renderQuestion(q);
    } catch(e) {
        showSpinner('შეცდომა — სცადე თავიდან');
    }
}

// ── Render ───────────────────────────────────────────────────────────────
function renderQuestion(q) {
    document.getElementById('spinner').classList.remove('show');
    document.getElementById('qCard').style.display = 'block';

    document.getElementById('mcArea').style.display   = 'none';
    document.getElementById('pyrArea').style.display  = 'none';
    document.getElementById('codeArea').style.display = 'none';

    if (q.type === 'mc') {
        document.getElementById('mcArea').style.display  = 'block';
        document.getElementById('qLabel').textContent = 'კითხვა';
        document.getElementById('qText').textContent  = q.question;

        const hintEl = document.getElementById('qHint');
        if (q.hint) { hintEl.textContent = q.hint; hintEl.style.display = 'block'; }
        else         { hintEl.style.display = 'none'; }

        const grid = document.getElementById('optsGrid');
        grid.innerHTML = '';
        q.options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className     = 'opt-btn';
            btn.textContent   = opt;
            btn.onclick       = () => pickMC(opt, btn);
            grid.appendChild(btn);
        });
    } else if (q.type === 'pyramid') {
        document.getElementById('pyrArea').style.display = 'block';
        document.getElementById('qLabel').textContent    = 'პირამიდა';
        renderPyramid(q.rows);
        document.getElementById('checkBtn').style.display = 'block';
    } else if (q.type === 'code') {
        document.getElementById('codeArea').style.display = 'block';
        document.getElementById('qLabel').textContent     = '🕵️ კოდი';
        renderCode(q);
        document.getElementById('checkBtn').style.display = 'block';
    }
}

// ── MC answer ────────────────────────────────────────────────────────────
async function pickMC(answer, btn) {
    const btns = document.querySelectorAll('.opt-btn');
    btns.forEach(b => b.disabled = true);

    const r    = await postAnswer({ answer });
    const data = await r.json();

    const correct = data.correct_answer;
    btns.forEach(b => {
        if (b.textContent === correct) b.classList.add(b.textContent === answer ? 'correct' : 'reveal');
    });
    if (!data.correct) btn.classList.add('wrong');

    showFeedback(data);
}

// ── Pyramid render ───────────────────────────────────────────────────────
function renderPyramid(rows) {
    const container = document.getElementById('pyramid');
    container.innerHTML = '';
    rows.forEach((row, r) => {
        const rowEl = document.createElement('div');
        rowEl.className = 'pyr-row';
        row.forEach((val, c) => {
            const cell = document.createElement('div');
            cell.className = 'pyr-cell ' + (val === null ? 'hidden' : 'given');
            cell.dataset.r = r;
            cell.dataset.c = c;
            if (val === null) {
                const inp = document.createElement('input');
                inp.type = 'number';
                inp.min  = 1;
                cell.appendChild(inp);
            } else {
                cell.textContent = val;
            }
            rowEl.appendChild(cell);
        });
        container.appendChild(rowEl);
    });
}

// ── Code render ──────────────────────────────────────────────────────────
function renderCode(q) {
    const eqBox = document.getElementById('codeEqs');
    eqBox.innerHTML = '';
    (q.equations || []).forEach(eq => {
        const d = document.createElement('div');
        d.className   = 'code-eq';
        d.textContent = eq;
        eqBox.appendChild(d);
    });

    const symRow = document.getElementById('codeSyms');
    symRow.innerHTML = '';
    (q.target || []).forEach(sym => {
        const d = document.createElement('div');
        d.className   = 'code-sym-box';
        d.textContent = sym;
        symRow.appendChild(d);
    });

    const inpRow = document.getElementById('codeInps');
    inpRow.innerHTML = '';
    (q.target || []).forEach((_, pos) => {
        const inp = document.createElement('input');
        inp.type         = 'number';
        inp.className    = 'code-inp';
        inp.placeholder  = '?';
        inp.dataset.pos  = pos;
        inpRow.appendChild(inp);
    });
}

// ── Unified check dispatch ────────────────────────────────────────────────
function checkAnswer() {
    if (currentQ && currentQ.type === 'code') {
        checkCode();
    } else {
        checkPyramid();
    }
}

// ── Pyramid check ────────────────────────────────────────────────────────
async function checkPyramid() {
    const hidden = document.querySelectorAll('.pyr-cell.hidden');
    const answers = {};
    hidden.forEach(cell => {
        answers[`${cell.dataset.r},${cell.dataset.c}`] = cell.querySelector('input').value;
    });

    document.getElementById('checkBtn').disabled = true;
    const r    = await postAnswer({ answers });
    const data = await r.json();

    if (data.results) {
        Object.entries(data.results).forEach(([pos, res]) => {
            const [rr, cc] = pos.split(',');
            const cell = document.querySelector(`.pyr-cell[data-r="${rr}"][data-c="${cc}"]`);
            if (cell) {
                cell.classList.add(res.correct ? 'correct-cell' : 'wrong-cell');
                if (!res.correct) cell.querySelector('input').value = res.value;
            }
        });
    }

    showFeedback(data);
    document.getElementById('checkBtn').style.display = 'none';
}

// ── Code check ───────────────────────────────────────────────────────────
async function checkCode() {
    const inps = document.querySelectorAll('.code-inp');
    const code_answers = {};
    inps.forEach(inp => { code_answers[inp.dataset.pos] = inp.value; });

    document.getElementById('checkBtn').disabled = true;
    const r    = await postAnswer({ code_answers });
    const data = await r.json();

    if (data.results) {
        Object.entries(data.results).forEach(([pos, res]) => {
            const inp = document.querySelector(`.code-inp[data-pos="${pos}"]`);
            if (inp) {
                inp.classList.add(res.correct ? 'correct-inp' : 'wrong-inp');
                if (!res.correct) inp.value = res.value;
            }
        });
    }

    showFeedback(data);
    document.getElementById('checkBtn').style.display = 'none';
}

// ── Post answer ──────────────────────────────────────────────────────────
function postAnswer(body) {
    return fetch(`/practice/${SLUG}/answer`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ key: currentQ.key, ...body }),
    });
}

// ── Show feedback + level-up ─────────────────────────────────────────────
function showFeedback(data) {
    const fb = document.getElementById('feedback');
    fb.style.display = 'block';

    if (data.correct) {
        fb.className  = 'feedback correct';
        fb.textContent = '🎉 სწორია! ' + '⭐'.repeat(Math.min(data.streak, 3));
    } else {
        fb.className   = 'feedback wrong';
        fb.textContent = '❌ არასწორია — სცადე თავიდან!';
    }

    updateProgress(data.level, data.streak);

    if (data.leveled_up) {
        setTimeout(() => showLevelUp(data.level), 900);
    } else {
        document.getElementById('nextBtn').style.display = 'block';
    }
}

// ── Progress UI ──────────────────────────────────────────────────────────
function updateProgress(level, streak) {
    document.getElementById('levelBadge').textContent = 'დონე ' + level;
    document.getElementById('progFill').style.width   = ((level - 1) * 25) + '%';
    const dots = document.querySelectorAll('.sdot');
    dots.forEach((d, i) => d.classList.toggle('filled', i < streak));
}

// ── Level-up overlay ─────────────────────────────────────────────────────
function showLevelUp(level) {
    document.getElementById('luTitle').textContent = 'დონე ' + level + '-ზე გადახველ! 🚀';
    document.getElementById('luSub').textContent   = '3 სწორი პასუხი — ახალ დონეზე ხარ!';
    document.getElementById('levelupOverlay').classList.add('show');
}

function closeLevelUp() {
    document.getElementById('levelupOverlay').classList.remove('show');
    loadQuestion();
}

// ── Spinner ───────────────────────────────────────────────────────────────
function showSpinner(msg) {
    document.getElementById('qCard').style.display   = 'none';
    const s = document.getElementById('spinner');
    s.textContent = msg ?? '⏳ იტვირთება...';
    s.classList.add('show');
}

// ── Init ──────────────────────────────────────────────────────────────────
loadQuestion();
</script>
@endsection
