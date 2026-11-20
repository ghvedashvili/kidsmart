@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
@include('partials.achievement-toast')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 720px; margin: 0 auto; padding: 4px 0 40px; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #1D1A17; }

    /* ── Header ── */
    .topbar { display:flex; align-items:center; justify-content:space-between; padding:0 16px; }
    .back { font-size:0.86rem; font-weight:800; color:#1D1A17; text-decoration:none; padding:8px 14px; background:white; border-radius:14px; border:1px solid #F1E3D3; }
    .title { margin: 0; font-size: 48px; line-height: .95; font-weight: 900; font-stretch: 66%; text-transform: uppercase; color: #1D1A17; }
    .topic-tag { font-weight:800; font-size:0.82rem; color:#8A3407; background:#FFE3CC; border-radius:99px; padding:5px 14px; margin:10px 16px 0; display:inline-block; }
    .topic-tag:empty { display: none; }

    /* ── Question card ── */
    .q-card { background:white; border-radius:28px; margin:16px; padding:28px 22px 24px; box-shadow:0 2px 0 #F1E3D3; min-height:200px; }
    .q-label { font-weight:800; font-size:0.78rem; color:#6B625A; letter-spacing:0.06em; text-transform:uppercase; margin-bottom:14px; }
    .q-text { font-size:clamp(1.05rem,3.4vw,1.25rem); font-weight:700; color:#1D1A17; line-height:1.6; margin-bottom:6px; white-space: pre-line; }
    .q-hint { font-weight:600; font-size:0.86rem; color:#6B625A; margin-bottom:18px; border-left:3px solid #FFE3CC; padding-left:10px; }

    /* ── MC options ── */
    .opts { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top: 16px; }
    .opt-btn {
        padding:14px 10px; min-height:62px; border-radius:18px; border:2.5px solid #F1E3D3;
        font-family:inherit; font-weight:900; font-size:clamp(1.1rem,4.2vw,1.35rem); color:#1D1A17;
        background:#FFF8F1; cursor:pointer; transition:all 0.15s; text-align:center;
    }
    .opt-btn:hover:not(:disabled) { border-color:#FFC49A; transform: translateY(-2px); }
    .opt-btn.correct  { border-color:#3FAE6E; background:#E8F7EE; color:#14532D; }
    .opt-btn.wrong    { border-color:#E5484D; background:#FDECEA; color:#B42318; }
    .opt-btn.reveal   { border-color:#7FCB9C; background:#E8F7EE; color:#14532D; opacity:0.7; }

    /* ── Pyramid ── */
    .pyramid { display:flex; flex-direction:column; align-items:center; gap:6px; }
    .pyr-row { display:flex; gap:6px; }
    .pyr-cell {
        width:58px; height:58px; border-radius:16px; display:flex; align-items:center; justify-content:center;
        font-family:inherit; font-weight:900; font-size:1.15rem;
    }
    .pyr-cell.given { background:#6D4FE0; color:white; }
    .pyr-cell.hidden input {
        width:100%; height:100%; border:2.5px solid #C9BBFF; border-radius:16px; background:#EEE8FF;
        color:#4B33B8; font-family:inherit; font-weight:900; font-size:1.15rem;
        text-align:center; outline:none; padding:0;
    }
    .pyr-cell.hidden input:focus { border-color:#6D4FE0; }
    .pyr-cell.hidden.correct-cell input { border-color:#3FAE6E !important; background:#E8F7EE !important; color:#14532D !important; }
    .pyr-cell.hidden.wrong-cell input   { border-color:#E5484D !important; background:#FDECEA !important; color:#B42318 !important; }

    /* ── Check button ── */
    .check-btn {
        display:block; width:calc(100% - 32px); margin:0 16px;
        height:58px; border:none; border-radius:29px;
        font-family:inherit; font-weight:800; font-size:1.1rem; color:white;
        background:#1D1A17; cursor:pointer; transition:background 0.15s;
    }
    .check-btn:hover { background:#000; }
    .check-btn:disabled { opacity:0.45; cursor:not-allowed; }

    /* ── Feedback bar ── */
    .feedback { margin:12px 16px 0; padding:14px 18px; border-radius:18px; font-weight:800; font-size:1.05rem; display:none; }
    .feedback.correct { background:#E8F7EE; color:#14532D; }
    .feedback.wrong   { background:#FDECEA; color:#B42318; }

    /* ── Next button ── */
    .next-btn {
        display:none; width:calc(100% - 32px); margin:10px 16px 0;
        height:58px; border:none; border-radius:29px;
        font-family:inherit; font-weight:800; font-size:1.1rem; color:white;
        background:#C24A0A; cursor:pointer; transition:background 0.15s;
    }
    .next-btn:hover { background:#A53E08; }

    /* ── Code question ── */
    .code-eq-box { background:#FFF6DC;border-radius:16px;padding:12px 16px;margin-bottom:14px;border:2px dashed #F2CF6B; }
    .code-eq { font-weight:700; font-size:1.05rem; color:#1D1A17; margin:3px 0; }
    .code-target-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:10px; }
    .code-sym-box { width:52px;height:52px;border-radius:14px;background:#6D4FE0;color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0; }
    .code-inp-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap; }
    .code-inp { width:52px;height:52px;border-radius:14px;border:2.5px solid #C9BBFF;background:#EEE8FF;color:#4B33B8;font-family:inherit;font-weight:900;font-size:1.15rem;text-align:center;outline:none;padding:0;transition:border-color .15s; }
    .code-inp:focus { border-color:#6D4FE0; }
    .code-inp.correct-inp { border-color:#3FAE6E !important;background:#E8F7EE !important;color:#14532D !important; }
    .code-inp.wrong-inp   { border-color:#E5484D !important;background:#FDECEA !important;color:#B42318 !important; }

    /* ── Crossword ── */
    .cw-grid { display:grid;gap:4px;align-items:center;justify-items:center;margin:0 auto; }
    .cw-inp { width:100%;height:100%;border:2.5px solid #C9BBFF;border-radius:14px;background:#EEE8FF;color:#4B33B8;font-family:inherit;font-weight:900;font-size:1.05rem;text-align:center;outline:none;padding:0;transition:border-color .15s; }
    .cw-inp:focus { border-color:#6D4FE0; }
    .cw-inp.correct-inp { border-color:#3FAE6E !important;background:#E8F7EE !important;color:#14532D !important; }
    .cw-inp.wrong-inp   { border-color:#E5484D !important;background:#FDECEA !important;color:#B42318 !important; }
    .cw-given { width:52px;height:52px;border-radius:14px;background:#DFF5E3;border:2.5px solid #9FD9B5;color:#14532D;font-family:inherit;font-weight:900;font-size:1.05rem;display:flex;align-items:center;justify-content:center; }
    .cw-op  { font-family:inherit;font-weight:900;font-size:1.05rem;color:#6B625A;text-align:center; }
    .cw-eq  { font-family:inherit;font-weight:900;font-size:0.9rem;color:#B5AAA0;text-align:center; }
    .cw-res { width:100%;height:100%;border-radius:12px;background:#F5EFE8;display:flex;align-items:center;justify-content:center;font-family:inherit;font-size:1rem;color:#1D1A17;font-weight:900; }

    /* ── Spinner ── */
    .spinner { display:none; text-align:center; padding:40px; font-weight:700; color:#6B625A; font-size:1rem; }
    .spinner.show { display:block; }
    @media (max-width: 900px) { .title { font-size: 40px; } .q-card { margin: 14px 0; } .feedback, .check-btn, .next-btn { margin-left: 0; margin-right: 0; width: 100%; } .topbar { padding: 0; } .topic-tag { margin-left: 0; } }
</style>

<div class="wrap">
    <a href="{{ route('practice.topics') }}" class="back" style="display:inline-flex;align-items:center;gap:6px;margin:0 0 12px;"><i class="bi bi-arrow-left"></i> ვარჯიში</a>
    <div class="topbar">
        <h1 class="title">
            @if($type === 'pyramid') პირამიდა
            @elseif($type === 'auto') ვარჯიში
            @else 📘 {{ $topic->name }}
            @endif
        </h1>
    </div>

    @if($type === 'auto')
    <div class="topic-tag" id="topicTag"></div>
    @endif

    <div class="spinner show" id="spinner">⏳ იტვირთება...</div>

    <div class="q-card" id="qCard" style="display:none;">
        <div class="q-label" id="qLabel">კითხვა</div>
        <div class="q-hint" id="qHint" style="display:none;"></div>

        {{-- MC question --}}
        <div id="mcArea" style="display:none;">
            <div class="q-text" id="qText"></div>
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
            <div style="font-size:0.78rem;font-weight:800;color:#6B625A;text-align:center;margin-bottom:6px;">სამიზნე კოდი</div>
            <div class="code-target-row" id="codeSyms"></div>
            <div class="code-inp-row" id="codeInps"></div>
        </div>

        {{-- Crossword question --}}
        <div id="cwArea" style="display:none;">
            <div class="q-text" style="margin-bottom:16px;">🧮 შეავსე კროსვორდი</div>
            <div id="cwGrid" style="display:grid;gap:4px;align-items:center;justify-items:center;margin:0 auto;max-width:max-content;">
            </div>
        </div>

        {{-- Free-number-input question --}}
        <div id="inputArea" style="display:none;">
            <div class="q-text" id="inputQText"></div>
            <div style="display:flex;justify-content:center;margin-top:16px;">
                <input type="number" id="inputAnswerField" class="code-inp" style="width:110px;height:56px;font-size:1.3rem;">
            </div>
        </div>
    </div>

    <div class="feedback" id="feedback"></div>
    <button class="check-btn" id="checkBtn" style="display:none;" onclick="checkAnswer()">შემოწმება ✨</button>
    <button class="next-btn" id="nextBtn" onclick="loadQuestion()">შემდეგი კითხვა →</button>
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

    const topicTag = document.getElementById('topicTag');
    if (topicTag) topicTag.textContent = q.topic_name ? '📘 ' + q.topic_name : '';

    document.getElementById('mcArea').style.display   = 'none';
    document.getElementById('pyrArea').style.display  = 'none';
    document.getElementById('codeArea').style.display = 'none';
    document.getElementById('cwArea').style.display   = 'none';
    document.getElementById('inputArea').style.display = 'none';

    const hintEl = document.getElementById('qHint');
    if (q.hint) { hintEl.textContent = q.hint; hintEl.style.display = 'block'; }
    else         { hintEl.style.display = 'none'; }

    if (q.type === 'mc') {
        document.getElementById('mcArea').style.display  = 'block';
        document.getElementById('qLabel').textContent = 'კითხვა';
        document.getElementById('qText').textContent  = q.question;

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
    } else if (q.type === 'crossword') {
        document.getElementById('cwArea').style.display   = 'block';
        document.getElementById('qLabel').textContent     = '🧮 კროსვორდი';
        renderCrossword(q);
        document.getElementById('checkBtn').style.display = 'block';
    } else if (q.type === 'input') {
        document.getElementById('inputArea').style.display = 'block';
        document.getElementById('qLabel').textContent      = 'კითხვა';
        document.getElementById('inputQText').textContent  = q.question;
        const field = document.getElementById('inputAnswerField');
        field.value = '';
        field.classList.remove('correct-inp', 'wrong-inp');
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

// ── Crossword render ──────────────────────────────────────────────────────
function renderCrossword(q) {
    const R = q.rows || 2, C = q.cols || 2;
    const rowOps = q.row_ops || Array(R).fill('+');
    const colOps = q.col_ops || Array(C).fill('+');
    const rowResults = q.row_results || [];
    const colResults = q.col_results || [];

    const SZ=52, OP=24, EQ=20, RES=46;
    const colParts=[]; for(let c=0;c<C;c++){colParts.push(SZ+'px');if(c<C-1)colParts.push(OP+'px');} colParts.push(EQ+'px',RES+'px');
    const rowParts=[]; for(let r=0;r<R;r++){rowParts.push(SZ+'px');if(r<R-1)rowParts.push(OP+'px');} rowParts.push(EQ+'px',RES+'px');

    const grid = document.getElementById('cwGrid');
    grid.style.gridTemplateColumns = colParts.join(' ');
    grid.style.gridTemplateRows = rowParts.join(' ');
    grid.innerHTML = '';

    const revealedSet = new Set((q.revealed || []).map(Number));
    const revealedVals = q.revealed_values || {};

    for (let dr=0; dr<=2*R; dr++) {
        for (let dc=0; dc<=2*C; dc++) {
            let el = document.createElement('div');
            if (dr <= 2*R-2) {
                if (dr%2===0) {
                    const r=dr/2;
                    if (dc<=2*C-2) {
                        if (dc%2===0) {
                            const pos = r*C + dc/2;
                            if (revealedSet.has(pos)) {
                                el.className = 'cw-given';
                                el.textContent = revealedVals[pos] ?? '';
                            } else {
                                el = document.createElement('input');
                                el.type='number'; el.className='cw-inp'; el.placeholder='?';
                                el.dataset.pos = pos;
                            }
                        } else { el.className='cw-op'; el.textContent=rowOps[r]||'+'; }
                    } else if(dc===2*C-1) { el.className='cw-eq'; el.textContent='='; }
                    else { el.className='cw-res'; el.textContent=rowResults[r]??'?'; }
                } else {
                    if(dc<=2*C-2&&dc%2===0){el.className='cw-op';el.textContent=colOps[dc/2]||'+';}
                }
            } else if(dr===2*R-1) {
                if(dc<=2*C-2&&dc%2===0){el.className='cw-eq';el.textContent='=';}
            } else {
                if(dc<=2*C-2&&dc%2===0){el.className='cw-res';el.textContent=colResults[dc/2]??'?';}
            }
            grid.appendChild(el);
        }
    }
}

// ── Unified check dispatch ────────────────────────────────────────────────
function checkAnswer() {
    if (currentQ && currentQ.type === 'code') {
        checkCode();
    } else if (currentQ && currentQ.type === 'crossword') {
        checkCrossword();
    } else if (currentQ && currentQ.type === 'input') {
        checkInput();
    } else {
        checkPyramid();
    }
}

// ── Free-number-input check ─────────────────────────────────────────────
async function checkInput() {
    const field = document.getElementById('inputAnswerField');
    document.getElementById('checkBtn').disabled = true;
    const r    = await postAnswer({ answer: field.value });
    const data = await r.json();

    field.classList.add(data.correct ? 'correct-inp' : 'wrong-inp');
    if (!data.correct && data.correct_answer !== undefined) field.value = data.correct_answer;

    showFeedback(data);
    document.getElementById('checkBtn').style.display = 'none';
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

// ── Crossword check ───────────────────────────────────────────────────────
async function checkCrossword() {
    const crossword_answers = {};
    // Include pre-revealed values
    if (currentQ && currentQ.revealed_values) {
        Object.entries(currentQ.revealed_values).forEach(([pos, val]) => {
            crossword_answers[pos] = val;
        });
    }
    document.querySelectorAll('#cwGrid .cw-inp').forEach(inp => {
        crossword_answers[inp.dataset.pos] = inp.value;
    });

    document.getElementById('checkBtn').disabled = true;
    const r    = await postAnswer({ crossword_answers });
    const data = await r.json();

    if (data.results) {
        Object.entries(data.results).forEach(([pos, res]) => {
            const inp = document.querySelector(`#cwGrid .cw-inp[data-pos="${pos}"]`);
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

// ── Show feedback ─────────────────────────────────────────────────────────
function showFeedback(data) {
    const fb = document.getElementById('feedback');
    fb.style.display = 'block';

    if (data.correct) {
        fb.className  = 'feedback correct';
        fb.textContent = '🎉 სწორია!' + (data.coins > 0 ? ' +' + data.coins + ' 🪙' : '');
    } else {
        fb.className   = 'feedback wrong';
        fb.textContent = '❌ არასწორია — სცადე თავიდან!';
    }

    document.getElementById('nextBtn').style.display = 'block';

    showAchievementToasts(data.new_achievements);
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
