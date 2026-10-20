@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 860px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    select.fc { cursor: pointer; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .q-row { padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .q-row:last-child { border-bottom: none; }
    .q-text { color: #374151; font-size: 0.8rem; line-height: 1.5; margin-bottom: 6px; }
    .var-num { color: #2563eb; font-weight: 700; background: #eff6ff; border-radius: 3px; padding: 0 4px; }
    .var-str { color: #7c3aed; }
    .q-regen { background: none; border: none; color: #cbd5e1; font-size: 0.7rem; cursor: pointer; padding: 0; margin-left: 4px; transition: color 0.15s; }
    .q-regen:hover { color: #64748b; }
    .q-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .q-tag { font-size: 0.65rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 7px; }
    .q-seq { width: 34px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 2px; color: #2563eb; font-family: 'Goldman', monospace; font-size: 0.65rem; padding: 2px 3px; text-align: center; outline: none; }
    .q-seq:focus { border-color: #6366f1; }
    .q-seq.saved { border-color: #86efac; }
    .diff { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .pagination-wrap { margin-top: 20px; display: flex; gap: 6px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { font-family: 'Goldman', monospace; font-size: 0.68rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 3px; padding: 4px 10px; text-decoration: none; }
    .pagination-wrap a:hover { color: #1e293b; border-color: #94a3b8; }
    .pagination-wrap span.active-page { color: #1e293b; border-color: #94a3b8; }
    .q-check { width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; accent-color: #374151; }
    .pdf-bar { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1e293b; border-radius: 10px; padding: 12px 20px; display: none; align-items: center; gap: 14px; box-shadow: 0 8px 32px rgba(0,0,0,0.25); z-index: 100; white-space: nowrap; }
    .pdf-bar.show { display: flex; }
    .pdf-count { font-family: 'Goldman', monospace; font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.06em; }
    .pdf-btn { background: #fff; border: none; border-radius: 6px; color: #1e293b; font-family: 'Goldman', monospace; font-size: 0.75rem; letter-spacing: 0.06em; padding: 8px 18px; cursor: pointer; }
    .pdf-btn:hover { background: #f1f5f9; }
    .pdf-clear { background: none; border: none; color: #64748b; font-size: 0.8rem; cursor: pointer; padding: 0; }
    .pdf-clear:hover { color: #fff; }

    /* live preview modal */
    .lp-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.4); display:flex; align-items:center; justify-content:center; z-index:100000; opacity:0; pointer-events:none; transition:opacity 0.15s; }
    .lp-overlay.open { opacity:1; pointer-events:all; }
    .lp-box { background:#fff; border-radius:10px; width:100%; max-width:420px; margin:0 16px; box-shadow:0 8px 32px rgba(0,0,0,0.16); transform:translateY(6px); transition:transform 0.15s; display:flex; flex-direction:column; max-height:84vh; }
    .lp-overlay.open .lp-box { transform:translateY(0); }
    .lp-head { display:flex; justify-content:space-between; align-items:center; padding:16px 18px 10px; font-size:0.7rem; color:#94a3b8; letter-spacing:0.14em; text-transform:uppercase; flex-shrink:0; }
    .lp-close { background:none; border:none; color:#94a3b8; font-size:1rem; cursor:pointer; padding:0 4px; }
    .lp-close:hover { color:#1e293b; }
    .lp-body { padding:0 18px 16px; overflow-y:auto; font-family:'Goldman',monospace; }
    .lp-foot { padding:12px 18px 18px; border-top:1px solid #f1f5f9; flex-shrink:0; }
    .lp-loading, .lp-err { font-size:0.78rem; color:#94a3b8; padding:20px 0; text-align:center; }
    .lp-err { color:#ef4444; }
    .lp-q { font-size:0.88rem; color:#1e293b; line-height:1.55; margin-bottom:12px; }
    .lp-hint { font-size:0.72rem; color:#64748b; border-left:2px solid #e2e8f0; padding-left:9px; margin-bottom:12px; }
    .lp-opts { display:flex; flex-wrap:wrap; gap:7px; }
    .lp-opt { font-size:0.78rem; padding:6px 14px; border-radius:4px; border:1px solid #e2e8f0; color:#374151; background:#fff; }
    .lp-opt.correct { background:#ecfdf5; border-color:#6ee7b7; color:#065f46; font-weight:700; }
    .lp-answer { font-size:0.82rem; color:#065f46; }
    .lp-pyr-row { display:flex; gap:6px; justify-content:center; margin-bottom:6px; }
    .lp-pyr-cell { width:40px; height:40px; border-radius:8px; background:#4f46e5; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700; }
    .lp-pyr-cell.empty { background:#eef2ff; color:#4f46e5; border:2px dashed #a5b4fc; }
    .lp-code-eqs { background:#fff8e7; border-radius:8px; padding:8px 12px; margin-bottom:10px; border:1.5px dashed #ffe194; font-size:0.8rem; color:#374151; }
    .lp-code-target { display:flex; gap:8px; flex-wrap:wrap; }
    .lp-code-sym { text-align:center; font-size:1.1rem; }
    .lp-code-sym b { font-size:0.78rem; color:#15803d; }
    .lp-cw { font-size:0.78rem; color:#374151; line-height:1.7; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .filters { gap: 6px; }
        .filters select, .filters input { font-size: 0.7rem; padding: 6px 8px; }
        .card-dark { padding: 14px; }
        .q-text { font-size: 0.75rem; }
        .pdf-bar { width: calc(100% - 24px); left: 12px; transform: none; bottom: 70px; flex-wrap: wrap; gap: 8px; padding: 10px 14px; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div style="display:flex;align-items:center;gap:10px;">
            @unless($showTrashed)
            <input type="checkbox" id="checkAll" class="q-check" title="ყველა მონიშვნა">
            @endunless
            <div style="color:#555;font-size:0.78rem;">სულ: {{ $templates->total() }} შაბლონი</div>
        </div>
        <div style="display:flex;gap:8px;">
            @if($showTrashed)
            <a href="{{ route('admin.questions.index', request()->except('trashed', 'page')) }}" class="btn">← აქტიურები</a>
            @else
            <a href="{{ route('admin.questions.index', array_merge(request()->except('page'), ['trashed' => 1])) }}" class="btn">🗑 წაშლილები</a>
            <a href="{{ route('admin.questions.create') }}" class="btn">+ ახალი შაბლონი</a>
            @endif
        </div>
    </div>

    <form method="GET" class="filters" id="filterForm">
        <select name="grade_id" class="fc" id="gradeFilter" onchange="onGradeChange(this.value)">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                {{ $grade->name }}
            </option>
            @endforeach
        </select>
        <select name="topic_id" class="fc" id="topicFilter" onchange="this.form.submit()">
            <option value="">ყველა თემა</option>
            @foreach($topics as $topic)
            <option value="{{ $topic->id }}" {{ ($filters['topic_id'] ?? '') == $topic->id ? 'selected' : '' }}>
                {{ $topic->grade->name }} / {{ $topic->name }}
            </option>
            @endforeach
        </select>
        <select name="theme_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა თემატიკა</option>
            @foreach($themes as $theme)
            <option value="{{ $theme->id }}" {{ ($filters['theme_id'] ?? '') == $theme->id ? 'selected' : '' }}>
                {{ $theme->icon ?? '' }} {{ $theme->name }}
            </option>
            @endforeach
        </select>
        <select name="difficulty" class="fc" onchange="this.form.submit()">
            <option value="">ყველა დონე</option>
            @for($i=1;$i<=5;$i++)
            <option value="{{ $i }}" {{ ($filters['difficulty'] ?? '') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
            @endfor
            <option value="olympiad" {{ ($filters['difficulty'] ?? '') === 'olympiad' ? 'selected' : '' }}>🏆 ოლიმპიადა</option>
        </select>
        @if(array_filter($filters))
        <a href="{{ route('admin.questions.index') }}" style="color:#555;font-size:0.72rem;text-decoration:none;padding:9px 0;">× გასუფთავება</a>
        @endif
    </form>

    <div class="card-dark">
        @forelse($templates as $tpl)
        <div class="q-row" style="display:flex;gap:10px;align-items:flex-start;{{ (!$showTrashed && !$tpl->is_active) ? 'opacity:0.45;' : '' }}">
            @unless($showTrashed)
            <input type="checkbox" class="q-check q-item" value="{{ $tpl->id }}" style="margin-top:3px;" onchange="updateBar()">
            @endunless
            <div style="flex:1;">
            @if($tpl->title)
            <div style="font-weight:700;color:#1e293b;font-size:0.8rem;margin-bottom:3px;">★ {{ $tpl->title }}</div>
            @endif
            <div class="q-text q-preview"
                data-tpl="{{ $tpl->template_text }}"
                data-nc='@json($tpl->num_config ?? [])'>{{ $tpl->template_text }}</div>
            <div class="q-meta">
                <span class="q-tag" style="color:#94a3b8;">#{{ $tpl->id }}</span>
                <span class="q-tag">{{ $tpl->topic->grade->name }}</span>
                <span class="q-tag">{{ $tpl->topic->name }}</span>
                @unless($showTrashed)
                <input type="number" class="q-seq" min="1" placeholder="—" value="{{ $tpl->sequence }}"
                    data-tpl-id="{{ $tpl->id }}" onchange="saveQSequence(this)" title="რიგითობა თემაში">
                @endunless
                @if($tpl->theme)
                <span class="q-tag" style="color:#7c3aed;border-color:#e9d5ff;">{{ $tpl->theme->icon ?? '' }} {{ $tpl->theme->name }}</span>
                @endif
                @if($tpl->is_olympiad)
                <span class="q-tag" style="color:#b45309;border-color:#fde68a;">🏆 ოლიმპიადა</span>
                @else
                <span class="q-tag">დონე {{ $tpl->difficulty }}</span>
                @endif
                <span class="q-tag" style="color:#888;">= {{ $tpl->correct_formula }}</span>
                <button type="button" class="q-regen" onclick="regenPreview(this)" title="ახალი მაგალითი">↺</button>
                <button type="button" class="q-regen" onclick="openLivePreview({{ $tpl->id }})" title="ლაივ პრევიუ">👁</button>
                <div style="flex:1;"></div>
                @if($showTrashed)
                <form method="POST" action="{{ route('admin.questions.restore', $tpl->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="color:#059669;font-size:0.7rem;background:none;border:1px solid #059669;border-radius:2px;padding:2px 8px;cursor:pointer;">↺ აღდგენა</button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.questions.toggle-active', $tpl) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" style="color:{{ $tpl->is_active ? '#059669' : '#b91c1c' }};font-size:0.7rem;background:none;border:none;cursor:pointer;padding:2px 4px;" title="{{ $tpl->is_active ? 'გაჩერება' : 'გააქტიურება' }}">{{ $tpl->is_active ? '✓ აქტიური' : '⏸ გაჩერებული' }}</button>
                </form>
                <a href="{{ route('admin.questions.edit', $tpl) }}" style="color:#555;font-size:0.7rem;text-decoration:none;padding:2px 8px;border:1px solid #222;border-radius:2px;">რედ.</a>
                <form method="POST" action="{{ route('admin.questions.destroy', $tpl) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                </form>
                @endif
            </div>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">შაბლონი არ არის</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $templates->withQueryString()->links('admin.pagination') }}
    </div>
</div>

{{-- Floating PDF bar --}}
<div class="pdf-bar" id="pdfBar">
    <span class="pdf-count" id="pdfCount">0 მონიშნული</span>
    <form method="POST" action="{{ route('admin.questions.pdf') }}" target="_blank" id="pdfForm">
        @csrf
        <div id="pdfInputs"></div>
        <button type="submit" class="pdf-btn">📄 PDF-ში</button>
    </form>
    <button class="pdf-clear" onclick="clearAll()" title="გასუფთავება">✕</button>
</div>

{{-- Live preview modal --}}
<div class="lp-overlay" id="lpOverlay" onclick="lpOverlayClick(event)">
    <div class="lp-box">
        <div class="lp-head">
            <span>ლაივ პრევიუ</span>
            <button type="button" class="lp-close" onclick="closeLivePreview()">✕</button>
        </div>
        <div class="lp-body" id="lpBody"></div>
        <div class="lp-foot">
            <button type="button" class="btn" onclick="regenLivePreview()">↺ ახალი თაობა</button>
        </div>
    </div>
</div>

<script>
// grade → topic filtering
const _allTopics = @json($topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'grade_id' => $t->grade_id]));
const _topicsByGrade = {};
_allTopics.forEach(t => { (_topicsByGrade[t.grade_id] = _topicsByGrade[t.grade_id] || []).push(t); });
const _selectedTopic = {{ (int)($filters['topic_id'] ?? 0) }};

function onGradeChange(gradeId) {
    const sel = document.getElementById('topicFilter');
    const prev = sel.value;
    sel.innerHTML = '<option value="">ყველა თემა</option>';
    const list = gradeId ? (_topicsByGrade[gradeId] || []) : _allTopics;
    list.forEach(t => {
        const opt = new Option(t.name, t.id);
        if (String(t.id) === String(prev) || t.id === _selectedTopic) opt.selected = true;
        sel.add(opt);
    });
    document.getElementById('filterForm').submit();
}

(function () {
    const gradeId = document.getElementById('gradeFilter').value;
    if (!gradeId) return;
    const sel = document.getElementById('topicFilter');
    sel.innerHTML = '<option value="">ყველა თემა</option>';
    (_topicsByGrade[gradeId] || []).forEach(t => {
        const opt = new Option(t.name, t.id);
        if (t.id === _selectedTopic) opt.selected = true;
        sel.add(opt);
    });
})();

const THEME_VARS = @json($themeVarMap);
@verbatim
// Mirrors OPERATOR_LABELS / resolveTextFormula / findTopLevelChar in
// QuestionTemplate.php and the admin form's JS twins — this listing page's
// "↺" preview is a separate, lighter re-implementation (no access to
// `conditions`, so no retry-until-satisfied loop), but still needs to handle
// all 4 num_config variable modes or derived/operator/conditional vars render
// as NaN/undefined.
const OPERATOR_LABELS_LITE = { '+': 'ით მეტი', '-': 'ით ნაკლები', '*': 'ჯერ მეტი', '/': 'ჯერ ნაკლები' };

function evalExprLite(expr, vars) {
    let e = String(expr);
    Object.entries(vars).forEach(([k, v]) => { e = e.replaceAll(k, String(v)); });
    e = e.replace(/[^0-9+\-*/()\s%]/g, '');
    try {
        const r = Function('return (' + e + ')')();
        return Number.isFinite(r) ? Math.floor(r) : 0;
    } catch (err) { return 0; }
}
function stripWrappingParensLite(s) {
    while (s.length >= 2 && s[0] === '(' && s[s.length - 1] === ')') {
        let depth = 0, wrapsWhole = true;
        for (let i = 0; i < s.length; i++) {
            if (s[i] === '(') depth++;
            else if (s[i] === ')') { depth--; if (depth === 0 && i !== s.length - 1) { wrapsWhole = false; break; } }
        }
        if (!wrapsWhole) break;
        s = s.slice(1, -1).trim();
    }
    return s;
}
function findTopLevelCharLite(str, char) {
    let depth = 0;
    for (let i = 0; i < str.length; i++) {
        if (str[i] === '(') depth++;
        else if (str[i] === ')') depth--;
        else if (depth === 0 && str[i] === char) return i;
    }
    return null;
}
function splitConditionExprLite(cond) {
    for (const op of ['>=', '<=', '==', '!=']) { const p = cond.indexOf(op); if (p !== -1) return [cond.slice(0, p).trim(), op, cond.slice(p + 2).trim()]; }
    for (const op of ['>', '<']) { const p = cond.indexOf(op); if (p !== -1) return [cond.slice(0, p).trim(), op, cond.slice(p + 1).trim()]; }
    return null;
}
function resolveConditionalLite(formula, vars) {
    formula = stripWrappingParensLite((formula || '').trim());
    const qPos = findTopLevelCharLite(formula, '?');
    if (qPos === null) {
        if (formula in vars) return vars[formula];
        if (formula.includes('{{')) return formula.replace(/\{\{(\w+)\}\}/g, (m, n) => (n in vars) ? vars[n] : m);
        return formula || '?';
    }
    const condStr = formula.slice(0, qPos).trim();
    const rest = formula.slice(qPos + 1);
    const cPos = findTopLevelCharLite(rest, ':');
    if (cPos === null) return '?';
    const ifTrue = rest.slice(0, cPos).trim(), ifFalse = rest.slice(cPos + 1).trim();
    const parts = splitConditionExprLite(condStr);
    if (!parts) return '?';
    const [l, op, r] = parts;
    const lv = evalExprLite(l, vars), rv = evalExprLite(r, vars);
    let ok;
    switch (op) {
        case '>': ok = lv > rv; break;
        case '<': ok = lv < rv; break;
        case '>=': ok = lv >= rv; break;
        case '<=': ok = lv <= rv; break;
        case '==': ok = lv === rv; break;
        default: ok = lv !== rv;
    }
    return resolveConditionalLite(ok ? ifTrue : ifFalse, vars);
}

function genVars(nc) {
    const vars = {};
    const randomEntries = [], operatorEntries = [], derivedEntries = [], conditionalEntries = [];
    for (const [name, conf] of Object.entries(nc)) {
        if (name.startsWith('_') || typeof conf !== 'object' || conf === null) continue;
        if (conf.operator) operatorEntries.push([name, conf]);
        else if ('derived' in conf) derivedEntries.push([name, conf]);
        else if ('conditional' in conf) conditionalEntries.push([name, conf]);
        else randomEntries.push([name, conf]);
    }
    randomEntries.forEach(([name, conf]) => {
        const step = Math.max(1, conf.step || 1);
        const steps = Math.floor((conf.max - conf.min) / step);
        vars[name] = conf.min + Math.floor(Math.random() * (steps + 1)) * step;
    });
    operatorEntries.forEach(([name, conf]) => {
        const opts = (conf.options && conf.options.length) ? conf.options : ['+'];
        vars[name] = opts[Math.floor(Math.random() * opts.length)];
    });
    derivedEntries.forEach(([name, conf]) => {
        vars[name] = evalExprLite(conf.derived || '0', vars);
    });
    conditionalEntries.forEach(([name, conf]) => {
        vars[name] = resolveConditionalLite(conf.conditional || '', vars);
    });

    // Display values for the rendered text — an operator variable shows its
    // Georgian label ("ით მეტი") here instead of the raw symbol ("+") that
    // was used above for any derived/conditional formula that referenced it.
    const displayVars = { ...vars };
    operatorEntries.forEach(([name]) => {
        displayVars[name] = OPERATOR_LABELS_LITE[displayVars[name]] ?? displayVars[name];
    });
    return displayVars;
}

function renderPreview(el) {
    const nc = JSON.parse(el.dataset.nc || '{}');
    const vars = genVars(nc);
    el.innerHTML = el.dataset.tpl.replace(/\{\{(\w+)\}\}/g, (_, name) => {
        if (vars[name] !== undefined) return `<span class="var-num">${vars[name]}</span>`;
        const list = THEME_VARS[name];
        if (list && list.length) return `<span class="var-str">${list[Math.floor(Math.random() * list.length)]}</span>`;
        return `<span class="var-str">${name}</span>`;
    });
}

function regenPreview(btn) {
    const el = btn.closest('.q-row').querySelector('.q-preview');
    if (el) renderPreview(el);
}

function saveQSequence(input) {
    const id = input.dataset.tplId;
    const val = input.value.trim();
    input.classList.remove('saved');
    fetch(`/admin/questions/${id}/sequence`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ sequence: val === '' ? null : val }),
    })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(() => { input.classList.add('saved'); setTimeout(() => input.classList.remove('saved'), 1200); })
        .catch(() => { alert('შეცდომა — რიგითობა არ შეინახა'); });
}

// ── Live preview modal — fetches the REAL server-side generate() result for
// this exact template id, so it's never out of sync with what a child gets.
let lpCurrentId = null;

function openLivePreview(id) {
    lpCurrentId = id;
    document.getElementById('lpOverlay').classList.add('open');
    regenLivePreview();
}
function closeLivePreview() {
    document.getElementById('lpOverlay').classList.remove('open');
}
function lpOverlayClick(e) {
    if (e.target === document.getElementById('lpOverlay')) closeLivePreview();
}

async function regenLivePreview() {
    if (!lpCurrentId) return;
    const body = document.getElementById('lpBody');
    body.innerHTML = '<div class="lp-loading">იტვირთება...</div>';
    try {
        const r = await fetch(`/admin/questions/${lpCurrentId}/preview`, { headers: { 'Accept': 'application/json' } });
        const data = await r.json();
        if (!data.ok) {
            body.innerHTML = `<div class="lp-err">⚠ ${escHtmlLp(data.error || 'შეცდომა')}</div>`;
            return;
        }
        body.innerHTML = renderLivePreview(data);
    } catch (e) {
        body.innerHTML = '<div class="lp-err">⚠ დაკავშირების შეცდომა</div>';
    }
}

function escHtmlLp(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function renderLivePreview(data) {
    const qt = data.question_type || 'multiple_choice';
    if (qt === 'pyramid')   return renderLpPyramid(data);
    if (qt === 'code')      return renderLpCode(data);
    if (qt === 'crossword') return renderLpCrossword(data);
    return renderLpMc(data);
}

function renderLpMc(data) {
    let html = `<div class="lp-q">${escHtmlLp(data.question_text)}</div>`;
    if (data.hint_text) html += `<div class="lp-hint">${escHtmlLp(data.hint_text)}</div>`;
    if (Array.isArray(data.options) && data.options.length) {
        html += '<div class="lp-opts">' + data.options.map(o =>
            `<div class="lp-opt ${o === data.correct_answer ? 'correct' : ''}">${escHtmlLp(o)}</div>`
        ).join('') + '</div>';
    } else {
        html += `<div class="lp-answer">✍️ პასუხი: <b>${escHtmlLp(data.correct_answer)}</b></div>`;
    }
    return html;
}

function renderLpPyramid(data) {
    let rows, sols;
    try { rows = JSON.parse(data.question_text); sols = JSON.parse(data.correct_answer); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    let html = '<div class="lp-q">🔺 პირამიდა</div>';
    rows.forEach((row, r) => {
        html += '<div class="lp-pyr-row">';
        row.forEach((val, c) => {
            if (val === null) {
                const sol = sols[`${r},${c}`];
                html += `<div class="lp-pyr-cell empty">${sol ?? '?'}</div>`;
            } else {
                html += `<div class="lp-pyr-cell">${val}</div>`;
            }
        });
        html += '</div>';
    });
    return html;
}

function renderLpCode(data) {
    let q, ans;
    try { q = JSON.parse(data.question_text); ans = JSON.parse(data.correct_answer); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    let html = '<div class="lp-q">🕵️ კოდი</div>';
    html += '<div class="lp-code-eqs">' + (q.equations || []).map(eq => `<div>${escHtmlLp(eq)}</div>`).join('') + '</div>';
    html += '<div class="lp-code-target">' + (q.target || []).map((sym, i) =>
        `<div class="lp-code-sym">${sym}<br><b>${ans[i] ?? '?'}</b></div>`
    ).join('') + '</div>';
    return html;
}

function renderLpCrossword(data) {
    let q;
    try { q = JSON.parse(data.question_text); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    return `<div class="lp-q">🧮 კროსვორდი</div><div class="lp-cw">` +
        `რიგები (${q.rows}×${q.cols})<br>` +
        `სტრიქონების ოპერატორები: ${(q.row_ops || []).join(', ')} · შედეგები: ${(q.row_results || []).join(', ')}<br>` +
        `სვეტების ოპერატორები: ${(q.col_ops || []).join(', ')} · შედეგები: ${(q.col_results || []).join(', ')}` +
        `</div>`;
}

@endverbatim

const bar      = document.getElementById('pdfBar');
const countEl  = document.getElementById('pdfCount');
const inputs   = document.getElementById('pdfInputs');
const checkAll = document.getElementById('checkAll');

function updateBar() {
    const checked = [...document.querySelectorAll('.q-item:checked')];
    bar.classList.toggle('show', checked.length > 0);
    countEl.textContent = checked.length + ' მონიშნული';
    inputs.innerHTML = checked.map(c => `<input type="hidden" name="ids[]" value="${c.value}">`).join('');
}

function clearAll() {
    document.querySelectorAll('.q-item').forEach(c => c.checked = false);
    checkAll.checked = false;
    updateBar();
}

checkAll.addEventListener('change', function () {
    document.querySelectorAll('.q-item').forEach(c => { c.checked = this.checked; });
    updateBar();
});
</script>
@endsection
