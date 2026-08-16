@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }

    .aw { max-width: 1200px; margin: 0 auto; padding: 28px 16px 64px; font-family: 'Goldman', monospace; }

    .pg { display: grid; grid-template-columns: 1fr 320px; gap: 16px; align-items: start; }
    .card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .card-row .card { margin-bottom: 0; }
    @media (max-width: 860px) { .pg { grid-template-columns: 1fr; } .card-row { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .aw { padding: 12px 8px 48px; }
        .card { padding: 14px; }
        .nc-hdr { grid-template-columns: 56px 1fr 1fr 50px 20px; }
        .nc-row { grid-template-columns: 56px 1fr 1fr 50px 20px; }
        .cond-row { grid-template-columns: 1fr 80px 1fr 20px; }
        .diff-btn { width: 34px; font-size: 0.62rem; padding: 6px 0; }
        .chips { gap: 3px; }
        .chip { font-size: 0.58rem; padding: 3px 6px; }
    }

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

    .cond-row { display: grid; grid-template-columns: 1fr 110px 1fr 22px; gap: 5px; align-items: center; margin-bottom: 5px; }
    .cond-sel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.72rem; padding: 6px 8px; outline: none; box-sizing: border-box; width: 100%; cursor: pointer; }
    .cond-sel:focus { border-color: #94a3b8; }
    .cond-op { color: #3b82f6; border-color: #bfdbfe; background: #eff6ff; }
    .cond-chips { display: flex; flex-wrap: wrap; gap: 3px; margin-bottom: 8px; }
    .cond-chip { font-family: 'Goldman', monospace; font-size: 0.56rem; padding: 3px 7px; border-radius: 3px; cursor: pointer; background: #f0fdf4; border: 1px solid #bbf7d0; color: #059669; user-select: none; }
    .cond-chip:hover { border-color: #34d399; }
    .cond-op-chip { background: #eff6ff; border: 1px solid #bfdbfe; color: #3b82f6; min-width: 22px; text-align: center; }
    .cond-op-chip:hover { border-color: #93c5fd; }

    .form-actions { display: flex; gap: 12px; align-items: center; margin-top: 4px; }
    .btn-save { background: #f0fdf4; border: 1px solid #bbf7d0; color: #059669; font-family: 'Goldman', monospace; font-size: 0.76rem; letter-spacing: 0.08em; padding: 11px 26px; border-radius: 4px; cursor: pointer; transition: all 0.18s; }
    .btn-save:hover { border-color: #059669; color: #065f46; background: #dcfce7; }
    .btn-cancel { color: #94a3b8; font-size: 0.66rem; text-decoration: none; transition: color 0.15s; }
    .btn-cancel:hover { color: #374151; }

    .preview-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; position: sticky; top: 68px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); max-height: calc(100vh - 100px); overflow-y: auto; }
    .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
    .preview-label { font-size: 0.56rem; color: #94a3b8; letter-spacing: 0.2em; text-transform: uppercase; }
    .preview-regen { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.58rem; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 3px; cursor: pointer; transition: all 0.15s; }
    .preview-regen:hover { border-color: #94a3b8; color: #1e293b; }
    .preview-q { color: #1e293b; font-size: 0.84rem; line-height: 1.75; margin-bottom: 16px; min-height: 48px; font-weight: 500; }
    .preview-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .preview-opt { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 9px 8px; font-family: 'Goldman', monospace; font-size: 0.82rem; color: #64748b; text-align: center; }
    .preview-opt.c { border-color: #059669; color: #059669; background: #f0fdf4; font-weight: 600; }
    .preview-formula { margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.05em; }
    .preview-vars { margin-top: 8px; font-size: 0.58rem; color: #cbd5e1; line-height: 1.7; }
    .preview-warn { margin-top: 8px; font-size: 0.6rem; color: #f87171; }
    .tog { margin-left: 6px; transition: transform 0.2s; }
    .at-row { display:flex; gap:6px; margin-bottom:14px; }
    .at-btn { background:#f8fafc; border:1px solid #e2e8f0; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.7rem; letter-spacing:0.06em; padding:6px 16px; border-radius:4px; cursor:pointer; transition:all 0.15s; }
    .at-btn.at-sel { border-color:#374151; color:#1e293b; background:#f1f5f9; }
    .chip-txt { background:#faf5ff; border:1px solid #e9d5ff; color:#7c3aed; }
    .chip-txt:hover { border-color:#c4b5fd; }
    .chip-txt.sel { background:#7c3aed; color:#fff; border-color:#7c3aed; }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

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

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px;">
                    <div>
                        <div class="lbl">კლასი</div>
                        <select id="gradeFilter" class="fc" style="margin-bottom:0;" onchange="onGradeChange(this.value)">
                            <option value="">— კლასი —</option>
                            @foreach($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="lbl">თემა</div>
                        <select name="topic_id" id="topicSelect" class="fc" style="margin-bottom:0;" required>
                            <option value="">— თემა —</option>
                            @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" {{ old('topic_id', $template?->topic_id) == $topic->id ? 'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('topic_id')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <div class="lbl">თემატიკა <span style="color:#94a3b8;font-size:0.55rem;">(სურვილისამებრ)</span></div>
                        <select name="theme_id" class="fc" style="margin-bottom:0;">
                            <option value="">— თემატიკა —</option>
                            @foreach($themes as $theme)
                            <option value="{{ $theme->id }}" {{ old('theme_id', $template?->theme_id) == $theme->id ? 'selected' : '' }}>
                                {{ $theme->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

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

            {{-- 3. Template text --}}
            <div class="card">
                <div class="sec-title">③ კითხვის ტექსტი</div>

                @if(count($themeVarGroups) || count($themeVarStandalone))
                <div class="lbl">სტრიქონის ცვლადები</div>
                @foreach($themeVarGroups as $grp)
                <div style="margin-bottom:6px;">
                    <div style="font-size:0.54rem;color:#7c3aed;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:3px;">{{ $grp['name'] }}</div>
                    <div class="chips" style="margin-bottom:0;">
                        @foreach($grp['slots'] as $slot)
                        <span class="chip chip-theme" onclick="insertVar('{{ $slot }}')">&#123;&#123;{{ $slot }}&#125;&#125;</span>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @if(count($themeVarStandalone))
                <div class="chips">
                    @foreach($themeVarStandalone as $varName)
                    <span class="chip chip-theme" onclick="insertVar('{{ $varName }}')">&#123;&#123;{{ $varName }}&#125;&#125;</span>
                    @endforeach
                </div>
                @endif
                @endif

                <div class="lbl">რიცხვის ცვლადები (კლიკით ჩასმა)</div>
                <div style="display:flex;align-items:flex-start;gap:6px;">
                    <div class="chips" id="numTextChipBar" style="flex:1;min-width:0;margin-bottom:0;"></div>
                    <button type="button" onclick="addNcRow()" style="flex-shrink:0;background:#f0fdf4;border:1px solid #bbf7d0;color:#059669;font-family:'Goldman',monospace;font-size:0.68rem;padding:3px 8px;border-radius:3px;cursor:pointer;line-height:1.4;white-space:nowrap;" title="ახალი ცვლადი">+</button>
                </div>

                <div class="lbl" style="margin-top:8px;">ოპერატორები</div>
                <div class="chips">
                    <span class="chip chip-op" onclick="insertTextOp('+')">+</span>
                    <span class="chip chip-op" onclick="insertTextOp('−')">−</span>
                    <span class="chip chip-op" onclick="insertTextOp('×')">×</span>
                    <span class="chip chip-op" onclick="insertTextOp('÷')">÷</span>
                    <span class="chip chip-op" onclick="insertTextOp('=')"> = </span>
                    <span class="chip chip-op" onclick="insertTextOp('(')">( </span>
                    <span class="chip chip-op" onclick="insertTextOp(')')"> )</span>
                    <span class="chip chip-op" onclick="insertTextOp('?')">?</span>
                </div>

                <textarea name="template_text" id="templateText" class="fc" rows="4"
                    placeholder="@{{PLAYER}}-მ @{{N1}} გოლი გაიტანა პირველ ტაიმში, @{{N2}} — მეორეში. სულ?"
                    oninput="previewDebounce()" required>{{ old('template_text', $template?->template_text) }}</textarea>
                @error('template_text')<div class="err">{{ $message }}</div>@enderror
            </div>

            {{-- 2+4: Numeric vars & Formula side by side --}}
            <div class="card-row">
                {{-- 2. Numeric vars --}}
                <div class="card">
                    <div class="sec-title">② რიცხვის ცვლადები <span style="color:#94a3b8;font-size:0.54rem;letter-spacing:0.06em;">(სახელი · min · max · ნაბიჯი)</span></div>
                    <div class="nc-hdr" style="margin-top:4px;">
                        <span>სახელი</span><span>min</span><span>max</span><span>ნაბიჯი</span><span></span>
                    </div>
                    <div id="ncRows"></div>
                    <button type="button" class="nc-add" onclick="addNcRow()">+ ცვლადის დამატება</button>
                    <input type="hidden" name="num_config" id="numConfigJson">
                    @error('num_config')<div class="err">{{ $message }}</div>@enderror

                    <div class="lbl" style="margin-top:10px;">პირობები <span style="color:#94a3b8;font-size:0.6rem;">(სურვილისამებრ)</span></div>
                    <div class="hint" style="margin-bottom:6px;">ორივე მხარეს შეიძლება გამოთქმა: <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">N1 &gt; N2+N3</code></div>
                    <div id="condChips" class="cond-chips" style="display:none;"></div>
                    <div id="condRows"></div>
                    <button type="button" class="nc-add" id="addCondBtn" onclick="addCond()" style="display:none;">+ პირობის დამატება</button>
                    <span id="condNoVars" style="font-size:0.6rem;color:#94a3b8;">ჯერ დაამატეთ ცვლადები</span>
                    <input type="hidden" name="conditions" id="conditionsJson">
                </div>

                {{-- 4. Formula --}}
                <div class="card">
                    <div class="sec-title">④ სწორი პასუხი</div>

                    <div class="at-row">
                        <button type="button" id="atBtnNum" class="at-btn" onclick="setAnswerType('numeric')">🔢 რიცხვი</button>
                        <button type="button" id="atBtnTxt" class="at-btn" onclick="setAnswerType('text')">🔤 ტექსტი</button>
                    </div>
                    <input type="hidden" name="answer_type" id="answerTypeInput"
                        value="{{ old('answer_type', $template?->answer_type ?? 'numeric') }}">

                    {{-- Numeric UI --}}
                    <div id="numAnsUi">
                        <div class="lbl">ცვლადები</div>
                        <div class="chips" id="formulaChipBar">
                            <span style="font-size:0.56rem;color:#94a3b8;padding:4px 2px;">② ცვლადების შემდეგ გამოჩნდება</span>
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
                        <input type="text" id="correctFormula" class="fc"
                            placeholder="N1+N2"
                            value="{{ old('answer_type', $template?->answer_type) !== 'text' ? old('correct_formula', $template?->correct_formula) : '' }}"
                            oninput="previewDebounce()">
                        <div class="hint">მაგ: N1+N2 &nbsp;·&nbsp; (N1+N2)*N3 &nbsp;·&nbsp; N1*N2-N3 &nbsp;·&nbsp; N1%N2</div>
                        @error('correct_formula')<div class="err">{{ $message }}</div>@enderror

                        <div class="lbl" style="margin-top:10px;">მცდარი პასუხების დიაპაზონი <span style="color:#94a3b8;font-size:0.6rem;">±</span></div>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <div class="lbl" style="margin:0;white-space:nowrap;">min</div>
                            <input type="number" id="distMin" class="nc-inp" style="width:76px;" min="1"
                                value="{{ $template?->answer_type !== 'text' ? ($template?->distractors['min'] ?? 1) : 1 }}" oninput="previewDebounce()">
                            <div class="lbl" style="margin:0;white-space:nowrap;">max</div>
                            <input type="number" id="distMax" class="nc-inp" style="width:76px;" min="1"
                                value="{{ $template?->answer_type !== 'text' ? ($template?->distractors['max'] ?? 10) : 10 }}" oninput="previewDebounce()">
                        </div>
                    </div>

                    {{-- Text UI --}}
                    <div id="txtAnsUi" style="display:none;">
                        @php
                            $isTextEdit = old('answer_type', $template?->answer_type) === 'text';
                            $editFormula = old('correct_formula', $template?->correct_formula);
                        @endphp
                        <div class="lbl">სწორი პასუხი — ცვლადი</div>
                        <select id="textCorrectVar" class="fc" onchange="syncAll();previewDebounce();">
                            <option value="">— ცვლადი —</option>
                            @foreach($themeVarGroups as $grp)
                            <optgroup label="{{ $grp['name'] }}">
                                @foreach($grp['slots'] as $slot)
                                <option value="{{ $slot }}" {{ $isTextEdit && $editFormula === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                            @if(count($themeVarStandalone))
                            <optgroup label="სხვა">
                                @foreach($themeVarStandalone as $v)
                                <option value="{{ $v }}" {{ $isTextEdit && $editFormula === $v ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        @if(!count($themeVarNames))
                        <div style="font-size:0.6rem;color:#94a3b8;margin-top:-6px;margin-bottom:10px;">სტრიქონის ცვლადები არ არის — თემატიკაში დაამატეთ</div>
                        @endif

                        <div class="lbl" style="margin-top:4px;">ვარიანტების ცვლადები <span style="color:#94a3b8;font-size:0.6rem;">(კლიკით)</span></div>
                        <div id="textOptChips">
                            @foreach($themeVarGroups as $grp)
                            <div style="font-size:0.52rem;color:#7c3aed;letter-spacing:0.1em;text-transform:uppercase;margin:4px 0 2px;">{{ $grp['name'] }}</div>
                            <div class="chips" style="margin-bottom:4px;">
                                @foreach($grp['slots'] as $slot)
                                <span class="chip chip-txt text-opt-chip" data-varname="{{ $slot }}" onclick="toggleTextOpt(this)">{{ $slot }}</span>
                                @endforeach
                            </div>
                            @endforeach
                            @if(count($themeVarStandalone))
                            <div class="chips">
                                @foreach($themeVarStandalone as $v)
                                <span class="chip chip-txt text-opt-chip" data-varname="{{ $v }}" onclick="toggleTextOpt(this)">{{ $v }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @if(!count($themeVarNames))
                        <div style="font-size:0.6rem;color:#94a3b8;margin-top:4px;">ცვლადები არ არის</div>
                        @endif
                    </div>

                    <input type="hidden" name="correct_formula" id="correctFormulaHidden"
                        value="{{ old('correct_formula', $template?->correct_formula) }}">
                    <input type="hidden" name="distractors" id="distractorsJson">
                    @error('distractors')<div class="err">{{ $message }}</div>@enderror

                    <div class="lbl" style="margin-top:10px;">მინიშნება <span style="color:#94a3b8;font-size:0.6rem;">(გამოჩნდება კითხვის ქვეშ)</span></div>
                    <textarea name="hint_text" id="hintText" class="fc" rows="2"
                        placeholder="მინიშნება..."
                        oninput="previewDebounce()">{{ old('hint_text', $template?->hint_text) }}</textarea>
                </div>
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
            <div class="preview-q" id="prevQ"><span style="color:#94a3b8;font-size:0.72rem;line-height:1.8;">② სწრაფი შაბლონი ან<br>③ კითხვის ტექსტი + ④ ფორმულა<br>შეავსეთ preview-სთვის</span></div>
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
    numConfig:     @json($template?->num_config ?? []),
    conditions:    @json($template?->conditions ?? []),
    themeVarMap:   @json($themeVarMap),
    topicsByGrade: @json($topics->groupBy('grade_id')->map(fn($g) => $g->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values())),
    selectedTopic: {{ (int)($template?->topic_id ?? 0) }},
    distractors:   @json($template?->distractors ?? []),
    answerType:    '{{ old('answer_type', $template?->answer_type ?? 'numeric') }}',
    varGroups:     @json($themeVarGroups),
};
</script>
@verbatim
<script>
const OB = '{' + '{', CB = '}' + '}';

// ── Grade → Topic cascade
function onGradeChange(gradeId) {
    const sel = document.getElementById('topicSelect');
    const prev = +sel.value;
    sel.innerHTML = '<option value="">— აირჩიე —</option>';
    const list = gradeId
        ? (_KS.topicsByGrade[gradeId] || [])
        : Object.values(_KS.topicsByGrade).flat();
    list.forEach(t => {
        const opt = new Option(t.name, t.id);
        if (t.id === prev || t.id === _KS.selectedTopic) opt.selected = true;
        sel.add(opt);
    });
}

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
function insertTextOp(str) {
    const ta = document.getElementById('templateText');
    const s = ta.selectionStart, e = ta.selectionEnd;
    ta.value = ta.value.slice(0, s) + str + ta.value.slice(e);
    ta.selectionStart = ta.selectionEnd = s + str.length;
    ta.focus(); previewDebounce();
}

// ── Answer type toggle
function setAnswerType(type) {
    document.getElementById('answerTypeInput').value = type;
    const isTxt = type === 'text';
    document.getElementById('numAnsUi').style.display = isTxt ? 'none' : '';
    document.getElementById('txtAnsUi').style.display = isTxt ? '' : 'none';
    document.getElementById('atBtnNum').classList.toggle('at-sel', !isTxt);
    document.getElementById('atBtnTxt').classList.toggle('at-sel', isTxt);
    syncAll(); previewDebounce();
}
function toggleTextOpt(el) {
    el.classList.toggle('sel');
    syncAll(); previewDebounce();
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

// ── num_config rows
let ncRows  = [];
let ncIdSeq = 1;

function addNcRow(name = '', min = 1, max = 9, step = 1) {
    if (!name) {
        const used = new Set(ncRows.map(r => r.name));
        let i = 1;
        while (used.has('N' + i)) i++;
        name = 'N' + i;
    }
    ncRows.push({ id: ncIdSeq++, name, min, max, step });
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
                data-nc="${row.id}" data-nc-f="name">
            <input type="number" class="nc-inp" min="0" placeholder="1" value="${row.min}"
                data-nc="${row.id}" data-nc-f="min">
            <input type="number" class="nc-inp" min="0" placeholder="9" value="${row.max}"
                data-nc="${row.id}" data-nc-f="max">
            <input type="number" class="nc-inp" min="1" placeholder="1" value="${row.step}"
                title="ნაბიჯი: 1=ნებ, 2=ლუწი, 5=მრ5" data-nc="${row.id}" data-nc-f="step">
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
let condIdSeq  = 1; // integer counter — avoids float ID precision bugs
let condFocus  = null; // { id, field } — which input is focused for chip insertion
const OP_LABELS = {'>':'> მეტია','<':'< ნაკლებია','>=':'≥ მეტი/ტოლი','<=':'≤ ნაკ/ტოლი','!=':'≠ არ ტოლდება','%0':'÷ იყოფა'};
const OP_SYMS   = ['+','-','*','/','(', ')'];

function addCond(left = '', op = '>', right = '', silent = false) {
    const names = ncRows.filter(r => r.name).map(r => r.name);
    if (!names.length) return;
    conditions.push({ id: condIdSeq++, left: left || names[0], op, right: right || (names[1] || names[0]) });
    renderConds();
    if (!silent) previewDebounce();
}
function removeCond(id) {
    conditions = conditions.filter(c => c.id !== id);
    renderConds();
}
function updCond(id, field, val) {
    const c = conditions.find(c => c.id === id);
    if (c) { c[field] = val; syncAll(); previewDebounce(); }
}
function insertIntoCond(text) {
    if (!condFocus) return;
    const inp = document.getElementById('cond-' + condFocus.id + '-' + condFocus.field);
    if (!inp) return;
    const s = inp.selectionStart ?? inp.value.length;
    const e = inp.selectionEnd   ?? inp.value.length;
    inp.value = inp.value.slice(0, s) + text + inp.value.slice(e);
    inp.selectionStart = inp.selectionEnd = s + text.length;
    inp.focus();
    updCond(condFocus.id, condFocus.field, inp.value.trim());
}
function renderConds() {
    const container = document.getElementById('condRows');
    container.innerHTML = '';
    const names   = ncRows.filter(r => r.name).map(r => r.name);
    const hasVars = names.length > 0;

    document.getElementById('addCondBtn').style.display = hasVars ? '' : 'none';
    document.getElementById('condNoVars').style.display = hasVars ? 'none' : '';

    // chips above conditions
    const chipBar = document.getElementById('condChips');
    chipBar.style.display = hasVars && conditions.length ? '' : 'none';
    chipBar.innerHTML = '';
    names.forEach(n => {
        const c = document.createElement('span');
        c.className = 'cond-chip'; c.textContent = n;
        c.onclick = () => insertIntoCond(n); chipBar.appendChild(c);
    });
    OP_SYMS.forEach(s => {
        const c = document.createElement('span');
        c.className = 'cond-chip cond-op-chip'; c.textContent = s;
        c.onclick = () => insertIntoCond(s); chipBar.appendChild(c);
    });

    conditions.forEach(cond => {
        const div = document.createElement('div');
        div.className = 'cond-row';
        const opOpts = Object.entries(OP_LABELS).map(([v,l]) =>
            `<option value="${v}" ${cond.op===v?'selected':''}>${l}</option>`).join('');
        div.innerHTML = `
            <input id="cond-${cond.id}-left" class="nc-inp" placeholder="N1 ან N2+N3" value="${cond.left}"
                data-ci="${cond.id}" data-cf="left">
            <select class="cond-sel cond-op" data-ci="${cond.id}" data-cf="op">${opOpts}</select>
            <input id="cond-${cond.id}-right" class="nc-inp" placeholder="N2 ან N2+N3 ან 5" value="${cond.right}"
                data-ci="${cond.id}" data-cf="right">
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

    const isTxt = document.getElementById('answerTypeInput').value === 'text';
    if (isTxt) {
        const selVar = document.getElementById('textCorrectVar').value;
        document.getElementById('correctFormulaHidden').value = selVar;
        const optVars = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname);
        document.getElementById('distractorsJson').value = JSON.stringify({ vars: optVars });
    } else {
        document.getElementById('correctFormulaHidden').value = document.getElementById('correctFormula').value;
        const dMin = +document.getElementById('distMin').value || 1;
        const dMax = +document.getElementById('distMax').value || 10;
        document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax });
    }
}

// ── Condition evaluator (supports expressions like N2+N3)
function evalExpr(expr, numVars) {
    let e = String(expr);
    Object.entries(numVars).forEach(([k, v]) => { e = e.replaceAll(k, String(v)); });
    e = e.replace(/[^0-9+\-*/()\s]/g, '');
    try { const r = Function('return (' + e + ')')(); return Number.isFinite(r) ? Math.floor(r) : 0; }
    catch(_) { return 0; }
}
function evalConditions(numVars) {
    return conditions.every(c => {
        const l = evalExpr(c.left  ?? '0', numVars);
        const r = evalExpr(c.right ?? '0', numVars);
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

function pickThemeVars() {
    const result = {};
    const grouped = new Set();
    // Grouped vars — unique within each group's pool
    (_KS.varGroups || []).forEach(g => {
        const pool = [...(g.pool || [])];
        for (let i = pool.length-1; i > 0; i--) {
            const j = Math.floor(Math.random()*(i+1)); [pool[i],pool[j]]=[pool[j],pool[i]];
        }
        (g.slots || []).forEach((slot, idx) => {
            result[slot] = pool[idx] ?? ('?' + slot);
            grouped.add(slot);
        });
    });
    // Standalone vars
    Object.entries(_KS.themeVarMap || {}).forEach(([name, vals]) => {
        if (!grouped.has(name)) {
            result[name] = vals.length ? vals[Math.floor(Math.random()*vals.length)] : '['+name+']';
        }
    });
    return result;
}
function pickNumVars() {
    const confRows = ncRows.filter(r => r.name);
    let numVars = {}, condOk = false;
    for (let attempt = 0; attempt < 40; attempt++) {
        numVars = {};
        confRows.forEach(r => {
            const step = Math.max(1, r.step || 1);
            const steps = Math.floor((r.max - r.min) / step);
            numVars[r.name] = r.min + Math.floor(Math.random() * (steps + 1)) * step;
        });
        if (evalConditions(numVars)) { condOk = true; break; }
    }
    return { numVars, condOk };
}
function clearPreview() {
    document.getElementById('prevQ').innerHTML = '<span style="color:#94a3b8;font-size:0.72rem;line-height:1.8;">③ კითხვის ტექსტი + ④ სწორი პასუხი<br>შეავსეთ preview-სთვის</span>';
    ['prevHint','prevFormula','prevVars','prevWarn'].forEach(id => document.getElementById(id).innerHTML = '');
    document.getElementById('prevOpts').innerHTML = '';
}

function genPreview() {
    const tmpl = document.getElementById('templateText').value;
    if (!tmpl) { clearPreview(); return; }
    const isTxt = document.getElementById('answerTypeInput').value === 'text';
    isTxt ? genPreviewText(tmpl) : genPreviewNumeric(tmpl);
}

function genPreviewText(tmpl) {
    const themeMap = pickThemeVars();
    const { numVars } = pickNumVars();
    const allVars = {...themeMap, ...numVars};

    let text = tmpl;
    Object.entries(allVars).forEach(([k,v]) => { text = text.replaceAll(OB+k+CB, v); });
    text = text.replace(/\{\{\w+\}\}/g, '?');
    document.getElementById('prevQ').textContent = text;
    document.getElementById('prevHint').textContent = '';
    document.getElementById('prevWarn').innerHTML = '';

    const correctVar = document.getElementById('textCorrectVar').value;
    if (!correctVar) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#94a3b8;">სწორი პასუხის ცვლადი აირჩიეთ...</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = Object.entries(themeMap).map(([k,v]) => `${k}=${v}`).join(' · ');
        return;
    }

    const correct = themeMap[correctVar] ?? '?';
    const optVarNames = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname);
    const seen = new Set();
    const opts = [];
    optVarNames.forEach(v => {
        const val = themeMap[v] ?? '?';
        if (!seen.has(val)) { seen.add(val); opts.push({ v: val, c: val === correct }); }
    });
    if (!seen.has(correct)) opts.unshift({ v: correct, c: true });
    for (let i = opts.length-1; i > 0; i--) {
        const j = Math.floor(Math.random()*(i+1)); [opts[i],opts[j]]=[opts[j],opts[i]];
    }

    document.getElementById('prevOpts').innerHTML = opts.map(o =>
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`).join('');
    document.getElementById('prevFormula').innerHTML =
        `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span> <span style="color:#94a3b8;">(${correctVar})</span>`;
    document.getElementById('prevVars').innerHTML = Object.entries(themeMap).map(([k,v]) => `${k}=${v}`).join(' · ');
}

function genPreviewNumeric(tmpl) {
    const formula = document.getElementById('correctFormula').value.trim();
    const dMin    = +document.getElementById('distMin').value || 1;
    const dMax    = +document.getElementById('distMax').value || 10;
    const { numVars, condOk } = pickNumVars();
    const themeMap = pickThemeVars();

    let text = tmpl;
    Object.entries({...themeMap, ...numVars}).forEach(([k,v]) => { text = text.replaceAll(OB+k+CB, v); });
    text = text.replace(/\{\{\w+\}\}/g, '?');

    let hint = (document.getElementById('hintText').value || '').trim();
    Object.entries({...themeMap, ...numVars}).forEach(([k,v]) => { hint = hint.replaceAll(OB+k+CB, v); });
    hint = hint.replace(/\{\{\w+\}\}/g, '?');

    document.getElementById('prevQ').textContent = text;
    document.getElementById('prevHint').textContent = hint || '';
    document.getElementById('prevWarn').innerHTML = condOk ? '' : '<span>⚠ პირობები ვერ შეხვდა 40 მცდელობაში</span>';

    if (!formula) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#94a3b8;">④ ფორმულა ჩაწერეთ პასუხისთვის...</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
        return;
    }

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
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`).join('');
    document.getElementById('prevFormula').innerHTML =
        `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span>&nbsp;·&nbsp;<span style="color:#1e1e1e;">${formula} = ${f} = ${correct}</span>`;
    document.getElementById('prevVars').innerHTML = Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
}

// ── Event delegation for dynamically rendered inputs (avoids inline-handler issues)
document.getElementById('ncRows').addEventListener('input', function(e) {
    const el = e.target, id = +el.dataset.nc, field = el.dataset.ncF;
    if (!id || !field) return;
    let val = field === 'name'
        ? (el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, ''))
        : +el.value;
    updNc(id, field, val);
});

document.getElementById('condRows').addEventListener('input', function(e) {
    const el = e.target, id = +el.dataset.ci, field = el.dataset.cf;
    if (!id || !field || field === 'op') return;
    updCond(id, field, el.value.trim());
});
document.getElementById('condRows').addEventListener('change', function(e) {
    const el = e.target, id = +el.dataset.ci, field = el.dataset.cf;
    if (!id || field !== 'op') return;
    updCond(id, 'op', el.value);
});
document.getElementById('condRows').addEventListener('focusin', function(e) {
    const el = e.target, id = +el.dataset.ci, field = el.dataset.cf;
    if (id && field && field !== 'op') condFocus = { id, field };
});

// ── Init
(function init() {
    // Restore grade filter based on selected topic (edit / validation failure)
    const curTopicId = +document.getElementById('topicSelect').value;
    if (curTopicId) {
        for (const [gId, topics] of Object.entries(_KS.topicsByGrade)) {
            if (topics.some(t => t.id === curTopicId)) {
                document.getElementById('gradeFilter').value = gId;
                const sel = document.getElementById('topicSelect');
                sel.innerHTML = '<option value="">— აირჩიე —</option>';
                topics.forEach(t => {
                    const opt = new Option(t.name, t.id);
                    if (t.id === curTopicId) opt.selected = true;
                    sel.add(opt);
                });
                break;
            }
        }
    }

    // Restore answer type toggle
    const initType = _KS.answerType || 'numeric';
    document.getElementById('answerTypeInput').value = initType;
    const isTxtInit = initType === 'text';
    document.getElementById('numAnsUi').style.display = isTxtInit ? 'none' : '';
    document.getElementById('txtAnsUi').style.display = isTxtInit ? '' : 'none';
    document.getElementById('atBtnNum').classList.toggle('at-sel', !isTxtInit);
    document.getElementById('atBtnTxt').classList.toggle('at-sel', isTxtInit);

    // For text mode: restore selected option vars from distractors
    if (isTxtInit && _KS.distractors && Array.isArray(_KS.distractors.vars)) {
        _KS.distractors.vars.forEach(varName => {
            const chip = document.querySelector('.text-opt-chip[data-varname="' + varName + '"]');
            if (chip) chip.classList.add('sel');
        });
    }

    const numCfg = _KS.numConfig;
    if (numCfg && typeof numCfg === 'object' && Object.keys(numCfg).length) {
        Object.entries(numCfg).forEach(([name, conf]) => {
            addNcRow(name, conf.min ?? 1, conf.max ?? 9, conf.step ?? 1);
        });
    } else if (!isTxtInit) {
        addNcRow('N1', 1, 9, 1);
        addNcRow('N2', 1, 9, 1);
    }
    const conds = _KS.conditions;
    if (Array.isArray(conds)) {
        conds.forEach(c => addCond(c.left, c.op, c.right, true));
    }
    syncAll();
    setTimeout(genPreview, 120);
})();

document.getElementById('mainForm').addEventListener('submit', syncAll);
</script>
@endverbatim
@endsection
