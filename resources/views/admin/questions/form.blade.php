@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }

    .aw { max-width: 1200px; margin: 0 auto; padding: 28px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.16em; text-transform: uppercase; margin-bottom: 6px; }
    .anav { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 28px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.67rem; color: #64748b; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 3px; transition: all 0.18s; }
    .anav a:hover, .anav a.active { color: #1e293b; border-color: #94a3b8; }

    .pg { display: grid; grid-template-columns: 1fr 320px; gap: 16px; align-items: start; }
    @media (max-width: 860px) { .pg { grid-template-columns: 1fr; } }

    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 22px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .sec-title { font-size: 0.58rem; color: #94a3b8; letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
    .lbl { font-size: 0.6rem; color: #64748b; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 5px; }
    .hint { font-size: 0.6rem; color: #94a3b8; margin-top: -4px; margin-bottom: 10px; line-height: 1.5; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.78rem; padding: 8px 11px; width: 100%; outline: none; margin-bottom: 10px; box-sizing: border-box; transition: border-color 0.15s; }
    .fc:focus { border-color: #94a3b8; color: #1e293b; }
    .fc::placeholder { color: #cbd5e1; }
    textarea.fc { resize: vertical; min-height: 76px; line-height: 1.65; }
    select.fc { cursor: pointer; }
    .err { color: #ef4444; font-size: 0.66rem; margin-top: -7px; margin-bottom: 10px; }

    .diff-row { display: flex; gap: 5px; margin-bottom: 10px; }
    .diff-btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-family: 'Goldman', monospace; font-size: 0.68rem; padding: 7px 0; width: 42px; text-align: center; border-radius: 3px; cursor: pointer; transition: all 0.15s; }
    .diff-btn.sel { border-color: #059669; color: #059669; background: #f0fdf4; }

    .chips { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 9px; }
    .chip { font-family: 'Goldman', monospace; font-size: 0.6rem; letter-spacing: 0.04em; padding: 4px 8px; border-radius: 3px; cursor: pointer; transition: all 0.12s; user-select: none; white-space: nowrap; }
    .chip-theme { background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b; }
    .chip-theme:hover { border-color: #94a3b8; color: #1e293b; }
    .chip-num { background: #f0fdf4; border: 1px solid #bbf7d0; color: #059669; }
    .chip-num:hover { border-color: #34d399; color: #065f46; }
    .chip-op { background: #eff6ff; border: 1px solid #bfdbfe; color: #3b82f6; min-width: 28px; text-align: center; }
    .chip-op:hover { border-color: #93c5fd; color: #1d4ed8; }

    .starters { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 12px; }
    .starter { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.6rem; letter-spacing: 0.06em; padding: 5px 11px; border-radius: 3px; cursor: pointer; transition: all 0.15s; }
    .starter:hover { border-color: #94a3b8; color: #1e293b; background: #f1f5f9; }

    .nc-hdr { display: grid; grid-template-columns: 68px 1fr 1fr 64px 22px; gap: 5px; margin-bottom: 4px; }
    .nc-hdr span { font-size: 0.54rem; color: #cbd5e1; letter-spacing: 0.1em; text-transform: uppercase; text-align: center; }
    .nc-hdr span:first-child { text-align: left; padding-left: 2px; }
    .nc-row { display: grid; grid-template-columns: 68px 1fr 1fr 64px 22px; gap: 5px; align-items: center; margin-bottom: 5px; }
    .nc-inp { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 6px 8px; outline: none; box-sizing: border-box; width: 100%; transition: border-color 0.15s; }
    .nc-inp:focus { border-color: #94a3b8; }
    .nc-name { color: #059669; text-transform: uppercase; text-align: center; }
    .nc-del { background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 0; font-size: 0.75rem; text-align: center; transition: color 0.15s; line-height: 1; }
    .nc-del:hover { color: #ef4444; }
    .nc-add { background: none; border: 1px dashed #cbd5e1; color: #94a3b8; font-family: 'Goldman', monospace; font-size: 0.6rem; letter-spacing: 0.08em; padding: 5px 14px; border-radius: 3px; cursor: pointer; transition: all 0.15s; margin-top: 4px; }
    .nc-add:hover { border-color: #64748b; color: #374151; }

    .cond-row { display: grid; grid-template-columns: 1fr 100px 1fr 22px; gap: 5px; align-items: center; margin-bottom: 5px; }
    .cond-sel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.72rem; padding: 6px 8px; outline: none; box-sizing: border-box; width: 100%; cursor: pointer; }
    .cond-sel:focus { border-color: #94a3b8; }
    .cond-op { color: #3b82f6; border-color: #bfdbfe; background: #eff6ff; }

    .form-actions { display: flex; gap: 12px; align-items: center; margin-top: 4px; }
    .btn-save { background: #f0fdf4; border: 1px solid #bbf7d0; color: #059669; font-family: 'Goldman', monospace; font-size: 0.76rem; letter-spacing: 0.08em; padding: 11px 26px; border-radius: 4px; cursor: pointer; transition: all 0.18s; }
    .btn-save:hover { border-color: #059669; color: #065f46; background: #dcfce7; }
    .btn-cancel { color: #94a3b8; font-size: 0.66rem; text-decoration: none; transition: color 0.15s; }
    .btn-cancel:hover { color: #374151; }

    .preview-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; position: sticky; top: 68px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .preview-label { font-size: 0.56rem; color: #94a3b8; letter-spacing: 0.2em; text-transform: uppercase; }
    .preview-regen { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.58rem; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 3px; cursor: pointer; transition: all 0.15s; }
    .preview-regen:hover { border-color: #94a3b8; color: #1e293b; }
    .preview-q { color: #374151; font-size: 0.8rem; line-height: 1.7; margin-bottom: 14px; min-height: 48px; }
    .preview-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .preview-opt { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 9px 8px; font-family: 'Goldman', monospace; font-size: 0.78rem; color: #64748b; text-align: center; }
    .preview-opt.c { border-color: #059669; color: #059669; background: #f0fdf4; }
    .preview-formula { margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.05em; }
    .preview-vars { margin-top: 8px; font-size: 0.58rem; color: #cbd5e1; line-height: 1.7; }
    .preview-warn { margin-top: 8px; font-size: 0.6rem; color: #f87171; }
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

    <div class="pg">

        {{-- ──── FORM ──── --}}
        <form method="POST"
              action="{{ $template ? route('admin.questions.update', $template) : route('admin.questions.store') }}"
              id="mainForm">
            @csrf
            @if($template) @method('PUT') @endif

            {{-- 1. Context --}}
            <div class="card">
                <div class="sec-title">① კონტექსტი</div>
                <div class="lbl">თოპიქი</div>
                <select name="topic_id" class="fc" required>
                    <option value="">— აირჩიე —</option>
                    @foreach($topics as $topic)
                    <option value="{{ $topic->id }}" {{ old('topic_id', $template?->topic_id) == $topic->id ? 'selected' : '' }}>
                        {{ $topic->grade->name }} / {{ $topic->name }}
                    </option>
                    @endforeach
                </select>
                @error('topic_id')<div class="err">{{ $message }}</div>@enderror

                <div class="lbl">სირთულე</div>
                <div class="diff-row">
                    @for($i=1;$i<=5;$i++)
                    <button type="button" class="diff-btn {{ old('difficulty', $template?->difficulty ?? 1) == $i ? 'sel' : '' }}"
                        onclick="setDiff({{ $i }})">{{ $i }}</button>
                    @endfor
                </div>
                <input type="hidden" name="difficulty" id="diffInput"
                    value="{{ old('difficulty', $template?->difficulty ?? 1) }}">
            </div>

            {{-- 2. Template text --}}
            <div class="card">
                <div class="sec-title">② კითხვის ტექსტი</div>

                <div class="lbl">სწრაფი შაბლონები</div>
                <div class="starters">
                    <button type="button" class="starter" onclick="applyStarter('add')">✚ შეკრება</button>
                    <button type="button" class="starter" onclick="applyStarter('sub')">― გამოკლება</button>
                    <button type="button" class="starter" onclick="applyStarter('mul')">× გამრავლება</button>
                    <button type="button" class="starter" onclick="applyStarter('div')">÷ გაყოფა</button>
                    <button type="button" class="starter" onclick="applyStarter('comb')">⊕ კომბო</button>
                    <button type="button" class="starter" onclick="applyStarter('diff')">Δ სხვაობა</button>
                </div>

                @if(count($themeVarNames))
                <div class="lbl">სტრიქონის ცვლადები (კლიკით ჩასმა)</div>
                <div class="chips">
                    @foreach($themeVarNames as $varName)
                    <span class="chip chip-theme" onclick="insertVar('{{ $varName }}')">&#123;&#123;{{ $varName }}&#125;&#125;</span>
                    @endforeach
                </div>
                @endif

                <div class="lbl">რიცხვის ცვლადები</div>
                <div class="chips" id="numTextChipBar">
                    <span style="font-size:0.56rem;color:#1e1e1e;padding:4px 2px;">ცვლადების დამატებისას გამოჩნდება</span>
                </div>

                <textarea name="template_text" id="templateText" class="fc" rows="4"
                    placeholder="@{{PLAYER}}-მ @{{N1}} გოლი გაიტანა პირველ ტაიმში, @{{N2}} — მეორეში. სულ?"
                    oninput="previewDebounce()" required>{{ old('template_text', $template?->template_text) }}</textarea>
                @error('template_text')<div class="err">{{ $message }}</div>@enderror

                <div class="lbl" style="margin-top:14px;">მინიშნების ტექსტი <span style="color:#94a3b8;font-size:0.6rem;">(არასავალდებულო — გამოჩნდება კითხვის ქვეშ)</span></div>
                <textarea name="hint_text" id="hintText" class="fc" rows="2"
                    placeholder="@{{PLAYER}}-მ პირველ ტაიმში @{{N1}} გოლი, მეორეში @{{N2}} გოლი გაიტანა."
                    oninput="previewDebounce()">{{ old('hint_text', $template?->hint_text) }}</textarea>
            </div>

            {{-- 3. Numeric vars --}}
            <div class="card">
                <div class="sec-title">③ რიცხვის ცვლადები <span style="color:#1e1e1e;font-size:0.54rem;letter-spacing:0.06em;">(სახელი · min · max · ნაბიჯი)</span></div>
                <div class="nc-hdr">
                    <span>სახელი</span><span>min</span><span>max</span><span>ნაბიჯი</span><span></span>
                </div>
                <div id="ncRows"></div>
                <button type="button" class="nc-add" onclick="addNcRow()">+ ცვლადის დამატება</button>
                <textarea name="num_config" id="numConfigJson" class="fc"
                    style="margin-top:10px;font-size:0.58rem;color:#1e1e1e;resize:none;" rows="1" readonly></textarea>
                @error('num_config')<div class="err">{{ $message }}</div>@enderror
            </div>

            {{-- 4. Formula --}}
            <div class="card">
                <div class="sec-title">④ სწორი პასუხის ფორმულა</div>
                <div class="lbl">ცვლადები</div>
                <div class="chips" id="formulaChipBar">
                    <span style="font-size:0.56rem;color:#1e1e1e;padding:4px 2px;">ცვლადების დამატებისას გამოჩნდება</span>
                </div>
                <div class="lbl">ოპერატორები</div>
                <div class="chips">
                    <span class="chip chip-op" onclick="insertFormula('+')">+</span>
                    <span class="chip chip-op" onclick="insertFormula('-')">−</span>
                    <span class="chip chip-op" onclick="insertFormula('*')">×</span>
                    <span class="chip chip-op" onclick="insertFormula('/')">/</span>
                    <span class="chip chip-op" onclick="insertFormula('%')">%</span>
                    <span class="chip chip-op" onclick="insertFormula('(')">(</span>
                    <span class="chip chip-op" onclick="insertFormula(')')">)</span>
                </div>
                <input type="text" name="correct_formula" id="correctFormula" class="fc"
                    placeholder="N1+N2"
                    value="{{ old('correct_formula', $template?->correct_formula) }}"
                    oninput="previewDebounce()" required>
                <div class="hint">მაგ: N1+N2 &nbsp;·&nbsp; (N1+N2)*N3 &nbsp;·&nbsp; N1*N2-N3 &nbsp;·&nbsp; N1%N2</div>
                @error('correct_formula')<div class="err">{{ $message }}</div>@enderror
            </div>

            {{-- 5. Conditions --}}
            <div class="card">
                <div class="sec-title">⑤ პირობები <span style="color:#1e1e1e;font-size:0.54rem;letter-spacing:0.06em;">(სურვილისამებრ)</span></div>
                <div class="hint" style="margin-bottom:12px;">ცვლადების შეზღუდვები — generate() ამოწმებს ყოველ გენერაციას (max 40 მცდელობა)</div>
                <div id="condRows"></div>
                <button type="button" class="nc-add" id="addCondBtn" onclick="addCond()" style="display:none;">+ პირობის დამატება</button>
                <span id="condNoVars" style="font-size:0.6rem;color:#1e1e1e;">ჯერ დაამატეთ ცვლადები ③-ში</span>
                <input type="hidden" name="conditions" id="conditionsJson">
            </div>

            {{-- 6. Distractors --}}
            <div class="card">
                <div class="sec-title">⑥ მცდარი პასუხების დიაპაზონი</div>
                <div class="hint" style="margin-bottom:10px;">სწ. პასუხი ± random(min, max) → 3 მცდარი ვარიანტი</div>
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="lbl" style="margin:0;white-space:nowrap;">min</div>
                    <input type="number" id="distMin" class="nc-inp" style="width:76px;" min="1"
                        value="{{ $template?->distractors['min'] ?? 1 }}" oninput="previewDebounce()">
                    <div class="lbl" style="margin:0;white-space:nowrap;">max</div>
                    <input type="number" id="distMax" class="nc-inp" style="width:76px;" min="1"
                        value="{{ $template?->distractors['max'] ?? 10 }}" oninput="previewDebounce()">
                    <span style="font-size:0.58rem;color:#1e1e1e;">→ ± შემთხვევითი</span>
                </div>
                <input type="hidden" name="distractors" id="distractorsJson">
                @error('distractors')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">{{ $template ? '↺ განახლება' : '✓ შენახვა' }}</button>
                <a href="{{ route('admin.questions.index') }}" class="btn-cancel">გაუქმება</a>
            </div>
        </form>

        {{-- ──── PREVIEW ──── --}}
        <div class="preview-panel">
            <div class="preview-header">
                <span class="preview-label">Live Preview</span>
                <button type="button" class="preview-regen" onclick="genPreview()">↺ ახალი</button>
            </div>
            <div class="preview-q" id="prevQ"><span style="color:#1c1c1c;">შაბლონი არ არის...</span></div>
            <div id="prevHint" style="font-size:0.7rem;color:#64748b;margin:4px 0 8px;font-style:italic;min-height:0;"></div>
            <div class="preview-opts" id="prevOpts"></div>
            <div class="preview-formula" id="prevFormula"></div>
            <div class="preview-vars" id="prevVars"></div>
            <div class="preview-warn" id="prevWarn"></div>
        </div>

    </div>
</div>

<script>
const _KS = {
    numConfig:  @json($template?->num_config ?? []),
    conditions: @json($template?->conditions ?? []),
    themeVarMap: @json($themeVarMap)
};
</script>
@verbatim
<script>
const OB = '{' + '{', CB = '}' + '}';

// ── Difficulty
function setDiff(n) {
    document.getElementById('diffInput').value = n;
    document.querySelectorAll('.diff-btn').forEach((b, i) => b.classList.toggle('sel', i + 1 === n));
}

// ── Insert at cursor (template text)
function insertVar(name) {
    const ta = document.getElementById('templateText');
    const s = ta.selectionStart, e = ta.selectionEnd;
    const ins = OB + name + CB;
    ta.value = ta.value.slice(0, s) + ins + ta.value.slice(e);
    ta.selectionStart = ta.selectionEnd = s + ins.length;
    ta.focus(); previewDebounce();
}

// ── Insert at cursor (formula)
function insertFormula(sym) {
    const inp = document.getElementById('correctFormula');
    const s = inp.selectionStart, e = inp.selectionEnd;
    const real = {'−':'-','×':'*','÷':'/'}[sym] || sym;
    inp.value = inp.value.slice(0, s) + real + inp.value.slice(e);
    inp.selectionStart = inp.selectionEnd = s + real.length;
    inp.focus(); previewDebounce();
}

// ── Quick starters
const STARTERS = {
    add:  { text: '{{PLAYER}}-მ პირველ ტაიმში {{N1}} გოლი გაიტანა, მეორეში — {{N2}}. სულ რამდენი?', formula: 'N1+N2',     vars: [{n:'N1',min:1,max:9,step:1},{n:'N2',min:1,max:9,step:1}], conds: [] },
    sub:  { text: '{{TEAM}}-ს {{N1}} ქულა ჰქონდა. {{N2}} ქულა დახარჯა. რამდენი დარჩა?',             formula: 'N1-N2',     vars: [{n:'N1',min:10,max:30,step:1},{n:'N2',min:1,max:9,step:1}], conds: [{left:'N1',op:'>',right:'N2'}] },
    mul:  { text: '{{PLAYER}}-მ {{N1}} მატჩი ითამაშა, ყოველ მატჩში {{N2}} ქულა. სულ?',              formula: 'N1*N2',     vars: [{n:'N1',min:2,max:8,step:1},{n:'N2',min:2,max:9,step:1}], conds: [] },
    div:  { text: '{{TEAM}}-ს {{N1}} ქულა აქვს, {{N2}} მოთამაშეზე თანაბრად. თითოს?',                formula: 'N1/N2',     vars: [{n:'N1',min:2,max:5,step:1},{n:'N2',min:1,max:4,step:1}], conds: [{left:'N1',op:'%0',right:'N2'}] },
    comb: { text: '{{PLAYER}}-მ {{N1}} გოლი გაიტანა, {{N2}} გაუსწორდა. ყოველ გოლზე {{N3}} ქულა. სულ?', formula: '(N1-N2)*N3', vars: [{n:'N1',min:4,max:10,step:1},{n:'N2',min:1,max:3,step:1},{n:'N3',min:2,max:5,step:1}], conds: [{left:'N1',op:'>',right:'N2'}] },
    diff: { text: '{{PLAYER}}-მ {{N1}} ქულა დააგროვა, {{PLAYER}}-მ — {{N2}}. სხვაობა?',              formula: 'N1-N2',     vars: [{n:'N1',min:15,max:50,step:5},{n:'N2',min:5,max:14,step:5}], conds: [{left:'N1',op:'>',right:'N2'}] },
};
function applyStarter(key) {
    const s = STARTERS[key]; if (!s) return;
    document.getElementById('templateText').value = s.text;
    document.getElementById('correctFormula').value = s.formula;
    ncRows = []; conditions = [];
    s.vars.forEach(v => addNcRow(v.n, v.min, v.max, v.step || 1));
    s.conds.forEach(c => addCond(c.left, c.op, c.right));
    previewDebounce();
}

// ── num_config rows
let ncRows = [];

function addNcRow(name = '', min = 1, max = 9, step = 1) {
    ncRows.push({ id: Date.now() + Math.random(), name, min, max, step });
    renderNcRows();
}
function removeNcRow(id) {
    ncRows = ncRows.filter(r => r.id !== id);
    renderNcRows();
}
function renderNcRows() {
    const c = document.getElementById('ncRows');
    c.innerHTML = '';
    ncRows.forEach(row => {
        const div = document.createElement('div');
        div.className = 'nc-row';
        div.innerHTML = `
            <input class="nc-inp nc-name" maxlength="6" placeholder="N1" value="${row.name}"
                oninput="updNc(${row.id},'name',this.value.toUpperCase().replace(/[^A-Z0-9]/g,''));this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">
            <input type="number" class="nc-inp" min="0" placeholder="1" value="${row.min}"
                oninput="updNc(${row.id},'min',+this.value)">
            <input type="number" class="nc-inp" min="0" placeholder="9" value="${row.max}"
                oninput="updNc(${row.id},'max',+this.value)">
            <input type="number" class="nc-inp" min="1" placeholder="1" value="${row.step}"
                title="ნაბიჯი: 1=ნებ, 2=ლუწი, 5=მრ5" oninput="updNc(${row.id},'step',+this.value)">
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
        `;
        c.appendChild(div);
    });
    syncAll();
    renderConds();
}
function updNc(id, field, val) {
    const r = ncRows.find(r => r.id === id);
    if (r) {
        r[field] = val;
        if (field === 'name') renderConds();
        syncAll(); previewDebounce();
    }
}

// ── Conditions
let conditions = [];
const OP_LABELS = {'>':'> მეტია','<':'< ნაკლებია','>=':'≥ მეტი/ტოლი','<=':'≤ ნაკ/ტოლი','!=':'≠ არ ტოლდება','%0':'÷ იყოფა'};

function addCond(left = '', op = '>', right = '') {
    const names = ncRows.filter(r => r.name).map(r => r.name);
    if (!names.length) return;
    conditions.push({ id: Date.now() + Math.random(), left: left || names[0], op, right: right || (names[1] || names[0]) });
    renderConds();
}
function removeCond(id) {
    conditions = conditions.filter(c => c.id !== id);
    renderConds();
}
function updCond(id, field, val) {
    const c = conditions.find(c => c.id === id);
    if (c) { c[field] = val; syncAll(); previewDebounce(); }
}
function renderConds() {
    const container = document.getElementById('condRows');
    container.innerHTML = '';
    const names = ncRows.filter(r => r.name).map(r => r.name);
    const hasVars = names.length > 0;

    document.getElementById('addCondBtn').style.display = hasVars ? '' : 'none';
    document.getElementById('condNoVars').style.display = hasVars ? 'none' : '';

    conditions.forEach(cond => {
        const div = document.createElement('div');
        div.className = 'cond-row';

        const leftOpts = names.map(n => `<option value="${n}" ${cond.left===n?'selected':''}>${n}</option>`).join('');
        const opOpts   = Object.entries(OP_LABELS).map(([v,l]) => `<option value="${v}" ${cond.op===v?'selected':''}>${l}</option>`).join('');

        div.innerHTML = `
            <select class="cond-sel" onchange="updCond(${cond.id},'left',this.value)">${leftOpts}</select>
            <select class="cond-sel cond-op" onchange="updCond(${cond.id},'op',this.value)">${opOpts}</select>
            <input class="nc-inp" placeholder="N2 ან 5" value="${cond.right}"
                oninput="updCond(${cond.id},'right',this.value.trim())">
            <button type="button" class="nc-del" onclick="removeCond(${cond.id})">✕</button>
        `;
        container.appendChild(div);
    });

    syncAll();
}

// ── Sync all hidden inputs
function syncAll() {
    const ncObj = {};
    ncRows.forEach(r => {
        if (r.name) ncObj[r.name] = { min: +r.min, max: +r.max, step: +r.step || 1 };
    });
    document.getElementById('numConfigJson').value = JSON.stringify(ncObj);

    const names = Object.keys(ncObj);
    // num chips for text
    const tBar = document.getElementById('numTextChipBar');
    tBar.innerHTML = '';
    if (!names.length) {
        tBar.innerHTML = '<span style="font-size:0.56rem;color:#1e1e1e;padding:4px 2px;">ცვლადების დამატებისას გამოჩნდება</span>';
    }
    names.forEach(name => {
        const c1 = document.createElement('span');
        c1.className = 'chip chip-num'; c1.textContent = OB+name+CB;
        c1.onclick = () => insertVar(name); tBar.appendChild(c1);
    });
    // num chips for formula
    const fBar = document.getElementById('formulaChipBar');
    fBar.innerHTML = '';
    if (!names.length) {
        fBar.innerHTML = '<span style="font-size:0.56rem;color:#1e1e1e;padding:4px 2px;">ცვლადების დამატებისას გამოჩნდება</span>';
    }
    names.forEach(name => {
        const c2 = document.createElement('span');
        c2.className = 'chip chip-num'; c2.textContent = name;
        c2.onclick = () => insertFormula(name); fBar.appendChild(c2);
    });

    // conditions JSON
    document.getElementById('conditionsJson').value = JSON.stringify(
        conditions.map(c => ({ left: c.left, op: c.op, right: c.right }))
    );

    // distractors JSON
    const dMin = +document.getElementById('distMin').value || 1;
    const dMax = +document.getElementById('distMax').value || 10;
    document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax });
}

// ── Condition evaluator
function evalConditions(numVars) {
    return conditions.every(c => {
        const l = /^\d+$/.test(String(c.left))  ? +c.left  : (numVars[c.left]  ?? 0);
        const r = /^\d+$/.test(String(c.right)) ? +c.right : (numVars[c.right] ?? 0);
        switch (c.op) {
            case '>':  return l > r;
            case '<':  return l < r;
            case '>=': return l >= r;
            case '<=': return l <= r;
            case '!=': return l !== r;
            case '%0': return r !== 0 && l % r === 0;
            default:   return true;
        }
    });
}

// ── Live preview
let prevTimer = null;
function previewDebounce() { clearTimeout(prevTimer); prevTimer = setTimeout(genPreview, 320); }

function genPreview() {
    const tmpl    = document.getElementById('templateText').value;
    const formula = document.getElementById('correctFormula').value.trim();
    const dMin    = +document.getElementById('distMin').value || 1;
    const dMax    = +document.getElementById('distMax').value || 10;

    if (!tmpl || !formula) {
        document.getElementById('prevQ').innerHTML = '<span style="color:#1c1c1c;">შაბლონი არ არის...</span>';
        document.getElementById('prevHint').textContent = '';
        document.getElementById('prevOpts').innerHTML = '';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        document.getElementById('prevWarn').innerHTML = '';
        return;
    }

    // Retry loop: generate num vars until conditions are met
    const confRows = ncRows.filter(r => r.name);
    let numVars = {};
    let condOk  = false;
    for (let attempt = 0; attempt < 40; attempt++) {
        numVars = {};
        confRows.forEach(r => {
            const step  = Math.max(1, r.step || 1);
            const steps = Math.floor((r.max - r.min) / step);
            numVars[r.name] = r.min + Math.floor(Math.random() * (steps + 1)) * step;
        });
        if (evalConditions(numVars)) { condOk = true; break; }
    }

    // Pick random value from each theme variable's actual values list
    const themeMap = {};
    Object.entries(_KS.themeVarMap || {}).forEach(([name, vals]) => {
        themeMap[name] = vals.length
            ? vals[Math.floor(Math.random() * vals.length)]
            : '[' + name + ']';
    });

    let text = tmpl;
    Object.entries({...themeMap, ...numVars}).forEach(([k,v]) => {
        text = text.replaceAll(OB+k+CB, v);
    });
    text = text.replace(/\{\{\w+\}\}/g, '?');

    let hint = (document.getElementById('hintText').value || '').trim();
    Object.entries({...themeMap, ...numVars}).forEach(([k,v]) => {
        hint = hint.replaceAll(OB+k+CB, v);
    });
    hint = hint.replace(/\{\{\w+\}\}/g, '?');

    document.getElementById('prevQ').textContent = text;
    document.getElementById('prevHint').textContent = hint || '';
    document.getElementById('prevWarn').innerHTML = condOk ? '' : '<span>⚠ პირობები ვერ შეხვდა 40 მცდელობაში</span>';

    // Evaluate formula
    let f = formula;
    Object.entries(numVars).forEach(([k,v]) => { f = f.replaceAll(k, String(v)); });
    f = f.replace(/[^0-9+\-*/()\s%.]/g, '');
    let correct = null;
    try {
        const raw = Function('return (' + f + ')')();
        if (Number.isFinite(raw) && raw > 0) correct = Math.floor(raw);
    } catch(e) {}

    if (correct === null) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#2a2a2a;">ფორმულა ვერ გამოითვალა</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        return;
    }

    // Distractors
    const wrong = new Set();
    let tries = 0;
    while (wrong.size < 3 && tries < 80) {
        tries++;
        const delta = dMin + Math.floor(Math.random() * (dMax - dMin + 1));
        const cand  = correct + (Math.random() < 0.5 ? 1 : -1) * delta;
        if (cand > 0 && cand !== correct) wrong.add(cand);
    }
    const opts = [{ v: correct, c: true }, ...[...wrong].map(v => ({ v, c: false }))];
    for (let i = opts.length-1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i+1)); [opts[i], opts[j]] = [opts[j], opts[i]];
    }

    document.getElementById('prevOpts').innerHTML = opts.map(o =>
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`
    ).join('');

    document.getElementById('prevFormula').innerHTML =
        `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span>&nbsp;·&nbsp;<span style="color:#1e1e1e;">${formula} = ${f} = ${correct}</span>`;

    document.getElementById('prevVars').innerHTML =
        Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
}

// ── Init
(function init() {
    const numCfg = _KS.numConfig;
    if (numCfg && typeof numCfg === 'object' && Object.keys(numCfg).length) {
        Object.entries(numCfg).forEach(([name, conf]) => {
            addNcRow(name, conf.min ?? 1, conf.max ?? 9, conf.step ?? 1);
        });
    } else {
        addNcRow('N1', 1, 9, 1);
        addNcRow('N2', 1, 9, 1);
    }
    const conds = _KS.conditions;
    if (Array.isArray(conds)) {
        conds.forEach(c => addCond(c.left, c.op, c.right));
    }
    syncAll();
    setTimeout(genPreview, 120);
})();

document.getElementById('mainForm').addEventListener('submit', syncAll);
</script>
@endverbatim
@endsection
