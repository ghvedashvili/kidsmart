@extends('layouts.app')
@section('content')
<style>
    body { background: #0d0d0d !important; }
    .aw { max-width: 700px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #555; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #555; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #222; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #bbb; border-color: #444; }
    .card-dark { background: #111; border: 1px solid #1e1e1e; border-radius: 8px; padding: 28px; margin-bottom: 20px; }
    .lbl { font-size: 0.68rem; color: #555; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px; }
    .hint { font-size: 0.67rem; color: #444; margin-top: -4px; margin-bottom: 12px; line-height: 1.5; }
    .fc { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #ccc; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 12px; box-sizing: border-box; }
    .fc:focus { border-color: #444; }
    .fc::placeholder { color: #3a3a3a; }
    textarea.fc { resize: vertical; min-height: 90px; line-height: 1.6; }
    select.fc { cursor: pointer; }
    .btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #bbb; font-family: 'Goldman', monospace; font-size: 0.8rem; letter-spacing: 0.08em; padding: 10px 28px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #444; color: #fff; }
    .err { color: #e74c3c; font-size: 0.72rem; margin-top: -8px; margin-bottom: 10px; }
    .diff-btns { display: flex; gap: 6px; margin-bottom: 12px; }
    .diff-btn { background: none; border: 1px solid #222; color: #444; font-family: 'Goldman', monospace; font-size: 0.72rem; padding: 6px 14px; border-radius: 3px; cursor: pointer; transition: all 0.2s; }
    .diff-btn.sel { border-color: #555; color: #bbb; background: #1a1a1a; }

    /* Variable chips */
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .chip {
        font-family: 'Goldman', monospace; font-size: 0.68rem; letter-spacing: 0.06em;
        padding: 4px 10px; border-radius: 3px; cursor: pointer; transition: all 0.15s;
        user-select: none;
    }
    .chip-theme { background: #1a1a1a; border: 1px solid #2a2a2a; color: #888; }
    .chip-theme:hover { border-color: #555; color: #ccc; }
    .chip-num { background: #0d1f0d; border: 1px solid #1a3a1a; color: #4a9a4a; }
    .chip-num:hover { border-color: #2a5a2a; color: #6aba6a; }

    /* num_config builder */
    .nc-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
    .nc-name { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #6aba6a; font-family: 'Goldman', monospace; font-size: 0.78rem; padding: 7px 10px; width: 80px; outline: none; box-sizing: border-box; text-transform: uppercase; }
    .nc-name:focus { border-color: #2a5a2a; }
    .nc-num { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #ccc; font-family: 'Goldman', monospace; font-size: 0.78rem; padding: 7px 10px; width: 70px; outline: none; box-sizing: border-box; }
    .nc-num:focus { border-color: #444; }
    .nc-sep { color: #333; font-size: 0.7rem; }
    .nc-del { background: none; border: none; color: #333; font-size: 0.8rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .nc-del:hover { color: #e74c3c; }
    .nc-add { background: none; border: 1px dashed #222; color: #444; font-family: 'Goldman', monospace; font-size: 0.68rem; padding: 5px 14px; border-radius: 3px; cursor: pointer; transition: all 0.2s; margin-top: 4px; }
    .nc-add:hover { border-color: #444; color: #888; }

    /* distractors builder */
    .dist-row { display: flex; gap: 8px; align-items: center; }
    .dist-lbl { font-size: 0.65rem; color: #444; letter-spacing: 0.08em; white-space: nowrap; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}" class="active">კითხვები</a>
    </nav>

    <form method="POST" action="{{ $template ? route('admin.questions.update', $template) : route('admin.questions.store') }}" id="mainForm">
        @csrf
        @if($template) @method('PUT') @endif

        <div class="card-dark">
            <div class="lbl">თოპიქი</div>
            <select name="topic_id" class="fc" required>
                <option value="">— აირჩიე —</option>
                @foreach($topics as $topic)
                <option value="{{ $topic->id }}"
                    {{ old('topic_id', $template?->topic_id) == $topic->id ? 'selected' : '' }}>
                    {{ $topic->grade->name }} / {{ $topic->name }}
                </option>
                @endforeach
            </select>
            @error('topic_id')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">სირთულის დონე</div>
            <div class="diff-btns">
                @for($i=1;$i<=5;$i++)
                <button type="button" class="diff-btn {{ old('difficulty', $template?->difficulty ?? 1) == $i ? 'sel' : '' }}"
                    onclick="setDiff({{ $i }})">{{ $i }}</button>
                @endfor
            </div>
            <input type="hidden" name="difficulty" id="diffInput" value="{{ old('difficulty', $template?->difficulty ?? 1) }}">

            {{-- Variable chips --}}
            <div class="lbl">შაბლონის ტექსტი</div>
            <div class="chips" id="chipBar">
                <span class="chip chip-theme" onclick="insertVar('TEAM')">@{{TEAM}}</span>
                <span class="chip chip-theme" onclick="insertVar('PLAYER')">@{{PLAYER}}</span>
                <span class="chip chip-theme" onclick="insertVar('PLACE')">@{{PLACE}}</span>
            </div>
            <textarea name="template_text" id="templateText" class="fc"
                placeholder="@{{TEAM}}-მა გაიტანა @{{N1}} გოლი პირველ ტაიმში და @{{N2}} მეორეში. სულ რამდენი გოლი?"
                required>{{ old('template_text', $template?->template_text) }}</textarea>
            @error('template_text')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">სწორი პასუხის ფორმულა</div>
            <div class="chips" id="formulaChipBar"></div>
            <input type="text" name="correct_formula" id="correctFormula" class="fc"
                placeholder="N1+N2"
                value="{{ old('correct_formula', $template?->correct_formula) }}" required>
            <div class="hint">ოპერატორები: + − * / — (N1+N2, N1-N2, N1*N2)</div>
            @error('correct_formula')<div class="err">{{ $message }}</div>@enderror

            {{-- num_config builder --}}
            <div class="lbl">რიცხვების კონფიგი</div>
            <div id="ncRows"></div>
            <button type="button" class="nc-add" onclick="addNcRow()">+ ცვლადის დამატება</button>
            <textarea name="num_config" id="numConfigJson" class="fc" rows="1"
                style="margin-top:10px;font-size:0.7rem;color:#333;resize:none;"
                readonly></textarea>
            @error('num_config')<div class="err">{{ $message }}</div>@enderror

            {{-- distractors builder --}}
            <div class="lbl" style="margin-top:16px;">სავარაუდო პასუხების დიაპაზონი</div>
            <div class="dist-row" style="margin-bottom:12px;">
                <span class="dist-lbl">min</span>
                <input type="number" id="distMin" class="nc-num" min="1" placeholder="1" value="{{ $template?->distractors['min'] ?? 1 }}">
                <span class="dist-lbl">max</span>
                <input type="number" id="distMax" class="nc-num" min="1" placeholder="10" value="{{ $template?->distractors['max'] ?? 10 }}">
                <span class="dist-lbl" style="color:#333;">→ სწორი პასუხი ±min…±max</span>
            </div>
            <input type="hidden" name="distractors" id="distractorsJson">
            @error('distractors')<div class="err">{{ $message }}</div>@enderror

            <div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
                <button type="submit" class="btn">{{ $template ? 'განახლება' : 'შენახვა' }}</button>
                <a href="{{ route('admin.questions.index') }}"
                    style="color:#444;font-size:0.72rem;text-decoration:none;">გაუქმება</a>
            </div>
        </div>
    </form>
</div>

<script>
// ── difficulty ──────────────────────────────────────────
function setDiff(n) {
    document.getElementById('diffInput').value = n;
    document.querySelectorAll('.diff-btn').forEach((b, i) => b.classList.toggle('sel', i + 1 === n));
}

// ── insert variable at cursor ────────────────────────────
const OB = '{' + '{', CB = '}' + '}';

function insertVar(name) {
    const ta = document.getElementById('templateText');
    const start = ta.selectionStart, end = ta.selectionEnd;
    const insert = OB + name + CB;
    ta.value = ta.value.slice(0, start) + insert + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + insert.length;
    ta.focus();
}

function insertFormula(name) {
    const inp = document.getElementById('correctFormula');
    const start = inp.selectionStart, end = inp.selectionEnd;
    inp.value = inp.value.slice(0, start) + name + inp.value.slice(end);
    inp.selectionStart = inp.selectionEnd = start + name.length;
    inp.focus();
}

// ── num_config rows ──────────────────────────────────────
let ncRows = [];

function addNcRow(name = '', min = 1, max = 9) {
    const id = Date.now();
    ncRows.push({ id, name, min, max });
    renderNcRows();
}

function removeNcRow(id) {
    ncRows = ncRows.filter(r => r.id !== id);
    renderNcRows();
}

function renderNcRows() {
    const container = document.getElementById('ncRows');
    container.innerHTML = '';
    ncRows.forEach(row => {
        const div = document.createElement('div');
        div.className = 'nc-row';
        div.innerHTML = `
            <input class="nc-name" maxlength="10" placeholder="N1" value="${row.name}"
                oninput="updateNcRow(${row.id},'name',this.value.toUpperCase().replace(/[^A-Z0-9]/g,''))"
                onchange="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">
            <span class="nc-sep">min</span>
            <input type="number" class="nc-num" min="0" placeholder="1" value="${row.min}"
                oninput="updateNcRow(${row.id},'min',+this.value)">
            <span class="nc-sep">max</span>
            <input type="number" class="nc-num" min="0" placeholder="9" value="${row.max}"
                oninput="updateNcRow(${row.id},'max',+this.value)">
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
        `;
        container.appendChild(div);
    });
    syncJson();
}

function updateNcRow(id, field, val) {
    const row = ncRows.find(r => r.id === id);
    if (row) { row[field] = val; syncJson(); }
}

function syncJson() {
    // build num_config JSON
    const obj = {};
    ncRows.forEach(r => {
        if (r.name) obj[r.name] = { min: r.min, max: r.max };
    });
    document.getElementById('numConfigJson').value = JSON.stringify(obj, null, 2);

    // update numeric chips in template and formula bars
    const chipBar = document.getElementById('chipBar');
    const formulaBar = document.getElementById('formulaChipBar');

    // remove old num chips
    chipBar.querySelectorAll('.chip-num').forEach(c => c.remove());
    formulaBar.innerHTML = '';

    Object.keys(obj).forEach(name => {
        const c1 = document.createElement('span');
        c1.className = 'chip chip-num';
        c1.textContent = OB + name + CB;
        c1.onclick = () => insertVar(name);
        chipBar.appendChild(c1);

        const c2 = document.createElement('span');
        c2.className = 'chip chip-num';
        c2.textContent = name;
        c2.onclick = () => insertFormula(name);
        formulaBar.appendChild(c2);
    });

    // distractors JSON
    const dMin = +document.getElementById('distMin').value || 1;
    const dMax = +document.getElementById('distMax').value || 10;
    document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax });
}

document.getElementById('distMin').addEventListener('input', syncJson);
document.getElementById('distMax').addEventListener('input', syncJson);

document.getElementById('mainForm').addEventListener('submit', function() {
    // ensure latest distractors are serialized
    syncJson();
    // if num_config is empty object but form has old textarea value, keep it
    const jsonVal = document.getElementById('numConfigJson').value;
    if (jsonVal === '{}' || jsonVal === '') {
        // don't override if ncRows empty and there's nothing — controller will reject
    }
});

// ── init from existing data ──────────────────────────────
(function init() {
    const existing = @json($template?->num_config ?? []);
    if (existing && typeof existing === 'object') {
        Object.entries(existing).forEach(([name, conf]) => {
            addNcRow(name, conf.min ?? 1, conf.max ?? 9);
        });
    }
    if (!ncRows.length) {
        addNcRow('N1', 1, 9);
        addNcRow('N2', 1, 9);
        addNcRow('N3', 1, 9);
        addNcRow('N4', 1, 9);
        addNcRow('N5', 1, 9);
    }
    syncJson();
})();
</script>
@endsection
