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
        .nc-hdr { grid-template-columns: 56px 1fr 1fr 50px 20px 20px; }
        .nc-row { grid-template-columns: 56px 1fr 1fr 50px 20px 20px; }
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

    .nc-hdr { display: grid; grid-template-columns: 68px 1fr 1fr 64px 22px 22px; gap: 5px; margin-bottom: 4px; }
    .nc-hdr span { font-size: 0.54rem; color: #cbd5e1; letter-spacing: 0.1em; text-transform: uppercase; text-align: center; }
    .nc-hdr span:first-child { text-align: left; padding-left: 2px; }
    .nc-row { display: grid; grid-template-columns: 68px 1fr 1fr 64px 22px 22px; gap: 5px; align-items: center; margin-bottom: 5px; }
    .nc-inp { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 6px 8px; outline: none; box-sizing: border-box; width: 100%; transition: border-color 0.15s; }
    .nc-inp:focus { border-color: #94a3b8; }
    .nc-name { color: #059669; text-transform: uppercase; text-align: center; }
    .nc-del { background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 0; font-size: 0.75rem; text-align: center; transition: color 0.15s; line-height: 1; }
    .nc-del:hover { color: #ef4444; }
    .nc-toggle { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0; font-size: 0.78rem; text-align: center; transition: color 0.15s; line-height: 1; }
    .nc-toggle:hover { color: #2563eb; }
    .nc-toggle.on { color: #2563eb; }
    .nc-add { background: none; border: 1px dashed #cbd5e1; color: #94a3b8; font-family: 'Goldman', monospace; font-size: 0.6rem; letter-spacing: 0.08em; padding: 5px 14px; border-radius: 3px; cursor: pointer; transition: all 0.15s; margin-top: 4px; }
    .nc-add:hover { border-color: #64748b; color: #374151; }
    .nc-add:disabled { opacity: 0.4; cursor: not-allowed; }
    .nc-add:disabled:hover { border-color: #cbd5e1; color: #94a3b8; }

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
    .qt-row { display:flex; gap:6px; margin-bottom:14px; }
    .qt-btn { background:#f8fafc; border:1px solid #e2e8f0; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.7rem; letter-spacing:0.06em; padding:6px 16px; border-radius:4px; cursor:pointer; transition:all 0.15s; }
    .qt-btn.sel { border-color:#4f46e5; color:#4f46e5; background:#eef2ff; }
    .pyr-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }
    .pyr-inp { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.78rem; padding:8px 11px; width:100%; outline:none; box-sizing:border-box; }
    .pyr-inp:focus { border-color:#94a3b8; }
    .code-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:10px; }
    .op-check { display:flex; gap:12px; margin-bottom:6px; align-items:center; }
    .op-check label { font-family:'Goldman',monospace; font-size:0.72rem; color:#374151; display:flex; align-items:center; gap:5px; cursor:pointer; }
    .load-search-wrap { position:relative; }
    .load-search-results { display:none; position:absolute; top:100%; left:0; right:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,0.1); max-height:240px; overflow-y:auto; margin-top:2px; }
    .load-search-item { padding:8px 12px; font-size:0.74rem; color:#374151; cursor:pointer; border-bottom:1px solid #f1f5f9; }
    .load-search-item:last-child { border-bottom:none; }
    .load-search-item:hover, .load-search-item.hi { background:#eef2ff; }
    .load-search-empty { padding:10px 12px; font-size:0.72rem; color:#94a3b8; }
    .load-selected-badge { display:none; font-size:0.68rem; color:#4f46e5; background:#eef2ff; border:1px solid #c7d2fe; border-radius:4px; padding:4px 10px; margin-top:6px; }
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

            @unless($template)
            {{-- Load an existing template as a starting point --}}
            <div class="card" style="background:#fafaff;border-color:#e0e7ff;">
                <div class="sec-title">⚡ შაბლონის ჩატვირთვა <span style="color:#94a3b8;font-size:0.54rem;letter-spacing:0.06em;">(სურვილისამებრ)</span></div>
                <div class="load-search-wrap">
                    <input type="text" id="loadSearchInput" class="fc" style="margin-bottom:0;" autocomplete="off"
                        placeholder="🔍 ძებნა სათაურით ან ID-ით..." oninput="onLoadSearchInput()"
                        onfocus="onLoadSearchInput()" onblur="setTimeout(hideLoadSearchResults, 150)">
                    <div id="loadSearchResults" class="load-search-results"></div>
                </div>
                <div id="loadSelectedBadge" class="load-selected-badge"></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                    <select id="loadGradeSelect" class="fc" style="margin-bottom:0;flex:1;min-width:110px;" onchange="onLoadGradeChange(this.value)">
                        <option value="">— კლასი —</option>
                        @foreach($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                    <select id="loadTopicSelect" class="fc" style="margin-bottom:0;flex:1;min-width:130px;" onchange="onLoadTopicChange(this.value)" disabled>
                        <option value="">— თემა —</option>
                    </select>
                    <select id="loadTplSelect" class="fc" style="margin-bottom:0;flex:2;min-width:200px;" disabled>
                        <option value="">— შაბლონი —</option>
                        @foreach($loadableTemplates as $t)
                        <option value="{{ $t->id }}" data-topic="{{ $t->topic_id }}"
                            data-search="{{ strtolower(($t->title ? $t->title.' ' : '').$t->id.' '.strip_tags($t->template_text ?: $t->question_type)) }}"
                            hidden>{{ $t->title ? '★ '.$t->title : Str::limit($t->template_text ?: '('.$t->question_type.')', 60) }} (#{{ $t->id }})</option>
                        @endforeach
                    </select>
                    <button type="button" class="nc-add" onclick="loadSelectedTemplate()" style="white-space:nowrap;">ჩატვირთვა</button>
                </div>
                <div style="font-size:0.6rem;color:#94a3b8;margin-top:4px;">აკოპირებს არჩეული შაბლონის ყველა პარამეტრს — თემა, ტიპი, ტექსტი, ფორმულა, ცვლადები — თქვენ შეასწორებთ და შეინახავთ როგორც ახალს.</div>
            </div>
            @endunless

            {{-- 1. Context --}}
            <div class="card">
                <div class="sec-title">① კონტექსტი</div>

                @php
                    $existingQType      = old('question_type', $template?->question_type ?? 'multiple_choice');
                    $isExistingPyramid  = $existingQType === 'pyramid';
                    $isExistingCode     = $existingQType === 'code';
                    $isExistingCw       = $existingQType === 'crossword';
                    $isExistingScale    = $existingQType === 'scale';
                    $codeCfg = ($existingQType === 'code') ? ($template->num_config ?? []) : [];
                    $cwCfg   = ($existingQType === 'crossword') ? ($template->num_config ?? []) : [];
                @endphp

                <div class="lbl">სათაური <span style="color:#94a3b8;font-size:0.6rem;">(მხოლოდ ადმინისთვის — ტესტში ბავშვს არ გამოუჩნდება)</span></div>
                <input type="text" name="title" id="titleInput" class="fc" maxlength="150"
                    placeholder="მაგ: ორნიშნა რიცხვის პოვნა — 2 ციფრის წაშლა"
                    value="{{ old('title', $template?->title) }}">

                <div class="lbl">კითხვის ტიპი</div>
                <div class="qt-row">
                    <button type="button" class="qt-btn {{ !$isExistingPyramid && !$isExistingCode && !$isExistingCw && !$isExistingScale ? 'sel' : '' }}" onclick="setQType('multiple_choice')">📝 Multiple Choice</button>
                    <button type="button" class="qt-btn {{ $isExistingPyramid ? 'sel' : '' }}" onclick="setQType('pyramid')">🔺 პირამიდა</button>
                    <button type="button" class="qt-btn {{ $isExistingCode ? 'sel' : '' }}" onclick="setQType('code')">🕵️ კოდი</button>
                    <button type="button" class="qt-btn {{ $isExistingCw ? 'sel' : '' }}" onclick="setQType('crossword')">🧮 კროსვორდი</button>
                    <button type="button" class="qt-btn {{ $isExistingScale ? 'sel' : '' }}" onclick="setQType('scale')">⚖ სასწორი</button>
                </div>
                <input type="hidden" name="question_type" id="qtInput" value="{{ $existingQType }}">

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
                    <div id="themeFieldWrap">
                        <div class="lbl">თემატიკა <span style="color:#94a3b8;font-size:0.55rem;">(სურვილისამებრ)</span></div>
                        <select name="theme_id" id="themeSelect" class="fc" style="margin-bottom:0;"
                            onchange="onThemeChange(this.value)">
                            <option value="">— თემატიკა —</option>
                            @foreach($themes as $theme)
                            <option value="{{ $theme->id }}" {{ old('theme_id', $template?->theme_id ?? $defaultThemeId) == $theme->id ? 'selected' : '' }}>
                                {{ $theme->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('theme_id')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="lbl">სირთულე</div>
                <div class="diff-row" id="diffRow">
                    @for($i=1;$i<=5;$i++)
                    <button type="button" class="diff-btn {{ old('difficulty', $template?->difficulty ?? 1) == $i ? 'sel' : '' }}"
                        onclick="setDiff({{ $i }})">{{ $i }}</button>
                    @endfor
                </div>
                <div class="hint" id="diffMaxHint" style="display:none;"></div>
                <input type="hidden" name="difficulty" id="diffInput"
                    value="{{ old('difficulty', $template?->difficulty ?? 1) }}">

                <div style="margin-top:6px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <button type="button" id="olyBtn" class="diff-btn {{ old('is_olympiad', $template?->is_olympiad) ? 'sel' : '' }}" style="width:auto;padding:7px 14px;"
                        onclick="toggleOlympiad()">🏆 ოლიმპიადა</button>
                    <button type="button" id="activeBtn" class="diff-btn {{ old('is_active', $template?->is_active ?? true) ? 'sel' : '' }}" style="width:auto;padding:7px 14px;"
                        onclick="toggleActive()">✓ აქტიური</button>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:0.68rem;color:#94a3b8;">რიგითობა თემაში</span>
                        <input type="number" name="sequence" class="fc" style="width:70px;padding:7px 8px;"
                            min="1" placeholder="—" value="{{ old('sequence', $template?->sequence) }}">
                    </div>
                </div>
                <input type="hidden" name="is_olympiad" id="isOlympiadInput"
                    value="{{ old('is_olympiad', $template?->is_olympiad ? 1 : 0) }}">
                <input type="hidden" name="is_active" id="isActiveInput"
                    value="{{ old('is_active', ($template?->is_active ?? true) ? 1 : 0) }}">

                {{-- Pyramid config (shown only for pyramid type) --}}
                <div id="pyrFields" style="display:none;margin-top:14px;border-top:1px solid #f1f5f9;padding-top:14px;">
                    <div class="sec-title" style="margin-bottom:10px;">🔺 პირამიდის პარამეტრები</div>
                    @php
                        $pyrCfg = ($template?->question_type === 'pyramid') ? ($template->num_config ?? []) : [];
                    @endphp
                    <div class="pyr-grid">
                        <div>
                            <div class="lbl">ძირის ზომა</div>
                            <select class="pyr-inp" id="pyrHeight" onchange="syncPyrConfig()">
                                <option value="3" {{ ($pyrCfg['height']??3)==3?'selected':'' }}>3 (6 კვანძი)</option>
                                <option value="4" {{ ($pyrCfg['height']??3)==4?'selected':'' }}>4 (10 კვანძი)</option>
                                <option value="5" {{ ($pyrCfg['height']??3)==5?'selected':'' }}>5 (15 კვანძი)</option>
                            </select>
                        </div>
                        <div>
                            <div class="lbl">რიცხვის მაქსიმუმი</div>
                            <input type="number" class="pyr-inp" id="pyrMax" min="2" max="99"
                                value="{{ $pyrCfg['max_base'] ?? 9 }}" onchange="syncPyrConfig()">
                        </div>
                        <div>
                            <div class="lbl">ცარიელი უჯრები</div>
                            <input type="number" class="pyr-inp" id="pyrHide" min="1" max="14"
                                value="{{ $pyrCfg['hidden_count'] ?? 2 }}" onchange="syncPyrConfig()">
                        </div>
                    </div>
                    <div style="font-size:0.58rem;color:#94a3b8;margin-top:8px;" id="pyrHint">
                        3-ძირი → 6 კვანძი; 4-ძირი → 10 კვანძი; 5-ძირი → 15 კვანძი
                    </div>
                </div>{{-- /pyrFields --}}

                {{-- Code config — separate from pyrFields so visibility is independent --}}
                <div id="codeFields" style="display:none;margin-top:14px;border-top:1px solid #f1f5f9;padding-top:14px;">
                    <div class="sec-title" style="margin-bottom:10px;">🕵️ კოდის პარამეტრები</div>

                    {{-- Row 1: symbol count + min + max --}}
                    <div class="code-grid">
                        <div>
                            <div class="lbl">ცვლადები</div>
                            <select class="pyr-inp" id="codeSymCount" onchange="syncCodeConfig()">
                                @foreach([2,3,4,5] as $n)
                                <option value="{{ $n }}" {{ ($codeCfg['symbol_count']??3)==$n?'selected':'' }}>{{ $n }} ცვლადი</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="lbl">min მნიშვნელობა</div>
                            <input type="number" class="pyr-inp" id="codeMinVal" min="1" max="50"
                                value="{{ $codeCfg['min_val'] ?? 1 }}" onchange="syncCodeConfig()">
                        </div>
                        <div>
                            <div class="lbl">max მნიშვნელობა</div>
                            <input type="number" class="pyr-inp" id="codeMaxVal" min="2" max="100"
                                value="{{ $codeCfg['max_val'] ?? 9 }}" onchange="syncCodeConfig()">
                        </div>
                    </div>

                    {{-- Row 2: operators --}}
                    <div class="lbl" style="margin-top:10px;">ოპერაციები</div>
                    <div class="op-check">
                        <label><input type="checkbox" id="codeOpPlus"  onchange="syncCodeConfig()"
                            {{ in_array('+', $codeCfg['operators'] ?? ['+']) ? 'checked' : '' }}> +</label>
                        <label><input type="checkbox" id="codeOpMinus" onchange="syncCodeConfig()"
                            {{ in_array('-', $codeCfg['operators'] ?? []) ? 'checked' : '' }}> −</label>
                        <label><input type="checkbox" id="codeOpMul"   onchange="syncCodeConfig()"
                            {{ in_array('×', $codeCfg['operators'] ?? []) ? 'checked' : '' }}> ×</label>
                        <label><input type="checkbox" id="codeOpDiv"   onchange="syncCodeConfig()"
                            {{ in_array('÷', $codeCfg['operators'] ?? []) ? 'checked' : '' }}> ÷</label>
                    </div>

                    {{-- Row 3: vars per equation + unique values --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
                        <div>
                            <div class="lbl">ცვლადები განტოლებაში</div>
                            <select class="pyr-inp" id="codeVarsPerEq" onchange="syncCodeConfig()">
                                <option value="2" {{ ($codeCfg['vars_per_eq']??2)==2?'selected':'' }}>2-ცვლადიანი (A+B=?)</option>
                                <option value="3" {{ ($codeCfg['vars_per_eq']??2)==3?'selected':'' }}>3-ცვლადიანი (A+B+B=?)</option>
                            </select>
                        </div>
                        <div style="display:flex;align-items:flex-end;padding-bottom:4px;">
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.78rem;font-weight:700;color:#374151;cursor:pointer;">
                                <input type="checkbox" id="codeUniqueVals" onchange="syncCodeConfig()"
                                    {{ !empty($codeCfg['unique_values']) ? 'checked' : '' }}
                                    style="width:16px;height:16px;cursor:pointer;">
                                მნიშვნელობები განსხვავებული
                            </label>
                        </div>
                    </div>
                </div>{{-- /codeFields --}}

                {{-- Crossword config --}}
                <div id="cwFields" style="display:none;margin-top:14px;border-top:1px solid #f1f5f9;padding-top:14px;">
                    <div class="sec-title" style="margin-bottom:10px;">🧮 კროსვორდის პარამეტრები</div>
                    <div class="lbl">ბადის ზომა</div>
                    <div class="op-check" style="margin-bottom:10px;">
                        @php $cwRows=$cwCfg['rows']??2; $cwCols=$cwCfg['cols']??2; @endphp
                        <label><input type="radio" name="_cwGrid" id="cwGrid22" value="22" onchange="syncCwConfig()" {{ ($cwRows==2&&$cwCols==2)?'checked':'' }}> 2×2</label>
                        <label><input type="radio" name="_cwGrid" id="cwGrid23" value="23" onchange="syncCwConfig()" {{ ($cwRows==2&&$cwCols==3)?'checked':'' }}> 2×3</label>
                        <label><input type="radio" name="_cwGrid" id="cwGrid32" value="32" onchange="syncCwConfig()" {{ ($cwRows==3&&$cwCols==2)?'checked':'' }}> 3×2</label>
                        <label><input type="radio" name="_cwGrid" id="cwGrid33" value="33" onchange="syncCwConfig()" {{ ($cwRows==3&&$cwCols==3)?'checked':'' }}> 3×3</label>
                    </div>
                    <div class="code-grid">
                        <div>
                            <div class="lbl">min მნიშვნელობა</div>
                            <input type="number" class="pyr-inp" id="cwMinVal" min="1" max="50"
                                value="{{ $cwCfg['min_val'] ?? 1 }}" onchange="syncCwConfig()">
                        </div>
                        <div>
                            <div class="lbl">max მნიშვნელობა</div>
                            <input type="number" class="pyr-inp" id="cwMaxVal" min="2" max="100"
                                value="{{ $cwCfg['max_val'] ?? 10 }}" onchange="syncCwConfig()">
                        </div>
                    </div>
                    <div class="lbl" style="margin-top:10px;">ოპერაციები</div>
                    <div class="op-check">
                        <label><input type="checkbox" id="cwOpPlus"  onchange="syncCwConfig()"
                            {{ in_array('+', $cwCfg['operators'] ?? ['+']) ? 'checked' : '' }}> +</label>
                        <label><input type="checkbox" id="cwOpMinus" onchange="syncCwConfig()"
                            {{ in_array('-', $cwCfg['operators'] ?? []) ? 'checked' : '' }}> −</label>
                        <label><input type="checkbox" id="cwOpMul"   onchange="syncCwConfig()"
                            {{ in_array('×', $cwCfg['operators'] ?? []) ? 'checked' : '' }}> ×</label>
                        <label><input type="checkbox" id="cwOpDiv"   onchange="syncCwConfig()"
                            {{ in_array('÷', $cwCfg['operators'] ?? []) ? 'checked' : '' }}> ÷</label>
                    </div>
                    <div style="margin-top:12px;">
                        <div class="lbl">წინასწარ გახსნილი უჯრები <span id="cwRevHint" style="color:#94a3b8;font-size:0.7rem;font-weight:600;"></span></div>
                        <input type="number" class="pyr-inp" id="cwRevealedCount" min="0" max="8"
                            value="{{ $cwCfg['revealed_count'] ?? 0 }}" onchange="syncCwConfig()"
                            style="width:80px;">
                        <span style="font-size:0.7rem;color:#64748b;margin-left:8px;">0 = ყველა ცარიელი</span>
                    </div>
                </div>{{-- /cwFields --}}

                {{-- Scale config --}}
                <div id="scaleFields" style="display:none;margin-top:14px;border-top:1px solid #f1f5f9;padding-top:14px;">
                    <div class="sec-title" style="margin-bottom:10px;">⚖ სასწორის პარამეტრები</div>

                    <div class="lbl">კითხვის ტექსტი <span style="color:#94a3b8;font-size:0.6rem;">(არასავალდებულო — ცარიელი დატოვებისას ავტომატურად დაგენერირდება პირობის მიხედვით)</span></div>
                    <textarea id="scaleTemplateText" class="fc" rows="2" placeholder="⚖️ რომელი ვარიანტი დააბალანსებს სასწორს?"
                        oninput="document.getElementById('templateText').value = this.value; previewDebounce();"></textarea>

                    <div class="lbl">სიმბოლოები <span style="color:#94a3b8;font-size:0.6rem;">(წონის ერთეულები — ემოჯი ან რიცხვი)</span></div>
                    <div class="nc-hdr" style="grid-template-columns:34px 1fr 70px 56px 56px 28px;">
                        <span></span><span>ტიპი</span><span>ემოჯი</span><span>min</span><span>max</span><span></span>
                    </div>
                    <div id="scaleSymRows"></div>
                    <button type="button" class="nc-add" onclick="addScaleSymbol()">+ სიმბოლოს დამატება</button>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
                        <div>
                            <div class="lbl" style="display:flex;justify-content:space-between;">⬅ მარცხენა პინა <span id="scaleLeftCap" style="color:#94a3b8;font-weight:600;">0/4</span></div>
                            <div id="scaleLeftRows"></div>
                            <button type="button" class="nc-add" id="scaleLeftAddBtn" onclick="addScalePanItem('left')">+ დამატება</button>
                        </div>
                        <div>
                            <div class="lbl" style="display:flex;justify-content:space-between;">➡ მარჯვენა პინა <span id="scaleRightCap" style="color:#94a3b8;font-weight:600;">0/4</span></div>
                            <div id="scaleRightRows"></div>
                            <button type="button" class="nc-add" id="scaleRightAddBtn" onclick="addScalePanItem('right')">+ დამატება</button>
                        </div>
                    </div>
                    <div style="font-size:0.58rem;color:#94a3b8;margin-top:4px;">თითო მხარეს მაქსიმუმ 4 საგანი ეტევა ვიზუალურად (სულ რაოდენობა — ცალკეული ციფრების/ემოჯების ჯამი)</div>

                    <div style="font-size:0.6rem;color:#94a3b8;margin-top:10px;">პასუხის სიმბოლო ავტომატურად აირჩევა — ნებისმიერი სიმბოლო, რომელიც არცერთ პინაზე არ დევს. ემოჯი-ტიპის სიმბოლო ამ როლისთვის მხოლოდ მაშინ გამოდგება, თუ ზემოთ კითხვის ტექსტიც დაწერეთ (მისი წონა ბავშვს პირდაპირ არასდროს უჩანს).</div>
                    <div style="font-size:0.6rem;margin-top:4px;" id="scaleAtHint"></div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
                        <div>
                            <div class="lbl">რაოდენობა min <span style="color:#94a3b8;font-size:0.55rem;">(ემოჯი-პასუხის რაოდენობის ფარგლები)</span></div>
                            <input type="number" class="pyr-inp" id="scaleCountMin" min="1" value="1" onchange="syncScaleConfig()">
                        </div>
                        <div>
                            <div class="lbl">რაოდენობა max</div>
                            <input type="number" class="pyr-inp" id="scaleCountMax" min="1" value="6" onchange="syncScaleConfig()">
                        </div>
                    </div>

                    <div class="lbl" style="margin-top:12px;">პირობები <span style="color:#94a3b8;font-size:0.6rem;">(სურვილისამებრ — სიმბოლოების წონებს შორის მიმართება, ასო-სახელებით)</span></div>
                    <div class="hint" style="margin-bottom:6px;">ორივე მხარეს შეიძლება გამოთქმა, ფუნქციების ჩათვლით: <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">A &gt; B+1</code> &nbsp;·&nbsp; <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">round(A/2) &gt;= B</code></div>
                    <div id="scaleCondChips" class="cond-chips" style="display:none;"></div>
                    <div id="scaleCondRows"></div>
                    <button type="button" class="nc-add" id="scaleAddCondBtn" onclick="addScaleCond()" style="display:none;">+ პირობის დამატება</button>
                    <span id="scaleCondNoVars" style="font-size:0.6rem;color:#94a3b8;">ჯერ დაამატეთ სიმბოლო</span>

                    <div class="lbl" style="margin-top:12px;">პასუხის ტიპი</div>
                    <div class="at-row">
                        <button type="button" id="scaleAtInput" class="at-btn" onclick="setScaleAnswerMode('numeric_input')">⌨️ ჩაწერა (რიცხვი)</button>
                        <button type="button" id="scaleAtMc" class="at-btn" onclick="setScaleAnswerMode('numeric')">🔢 არჩევანი (4 ვარიანტი)</button>
                    </div>
                    <div style="font-size:0.6rem;color:#94a3b8;margin-top:4px;">⌨️ ჩაწერა — ბავშვი თავად წერს რიცხვს (ემოჯი-პასუხისას კითხვაში ეტყობა რომელი ემოჯი). 🔢 არჩევანი — 4 ვარიანტიდან ირჩევს "{{'რაოდენობა + ემოჯი'}}" ან რიცხვს, ემოჯის ტიპი წინასწარ არ ეტყობა.</div>

                    <div id="scaleDistSection" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;">
                        <div class="lbl">მცდარი პასუხების დიაპაზონი <span style="color:#94a3b8;font-size:0.6rem;">± (წონა-ტიპის პასუხის არჩევანისთვის)</span></div>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <div class="lbl" style="margin:0;white-space:nowrap;">min</div>
                            <input type="number" id="scaleDistMin" class="nc-inp" style="width:76px;" min="1" value="1" onchange="syncScaleConfig()">
                            <div class="lbl" style="margin:0;white-space:nowrap;">max</div>
                            <input type="number" id="scaleDistMax" class="nc-inp" style="width:76px;" min="1" value="5" onchange="syncScaleConfig()">
                        </div>
                    </div>
                </div>{{-- /scaleFields --}}
            </div>

            <div id="mcSections">
            {{-- 3. Template text --}}
            <div class="card">
                <div class="sec-title">③ კითხვის ტექსტი</div>

                <div id="strVarSection"></div>

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
                    oninput="onTemplateInput()">{{ old('template_text', $template?->template_text) }}</textarea>
                @error('template_text')<div class="err">{{ $message }}</div>@enderror
                <div class="hint" id="exprHint" style="display:none;margin-top:2px;">⚡ ავტომატურ კონსტრუქტორში ჩასვით <code>@{{EXPR}}</code> იმ ადგილზე, სადაც გენერირებული გამოსახულება გამოჩნდება (მაგ. "ამოხსენი: @{{EXPR}}") — ცარიელი ველი ან @{{EXPR}}-ის გარეშე ტექსტი ნაგულისხმევ "... = ?" სტილს გამოაჩენს ტექსტის ბოლოში.</div>
            </div>

            {{-- 2+4: Numeric vars & Formula side by side --}}
            <div class="card-row">
                {{-- 2. Numeric vars --}}
                <div class="card">
                    <div class="sec-title">② რიცხვის ცვლადები <span style="color:#94a3b8;font-size:0.54rem;letter-spacing:0.06em;">(სახელი · min · max · ნაბიჯი)</span></div>

                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px;margin-bottom:12px;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.76rem;font-weight:700;color:#166534;cursor:pointer;margin-bottom:0;">
                            <input type="checkbox" id="autoBuildEnabled" onchange="toggleAutoBuild()"
                                {{ !empty(($template?->num_config ?? [])['_auto']['enabled'] ?? null) ? 'checked' : '' }}>
                            ⚡ ავტომატური კონსტრუქტორი <span style="font-weight:600;color:#4b8a63;">(მოქმედება შემთხვევით შეირჩევა ყოველ გენერაციაზე)</span>
                        </label>
                        <div id="autoBuildFields" style="display:none;margin-top:10px;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                                <div>
                                    <div class="lbl">ცვლადების რაოდენობა</div>
                                    <select class="pyr-inp" id="autoVarCount" onchange="applyAutoBuild()">
                                        @php $autoCfg = ($template?->num_config ?? [])['_auto'] ?? []; $autoVarCount = count(array_filter($template?->num_config ?? [], fn($k) => !str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY)) ?: 2; @endphp
                                        @foreach([2,3,4,5] as $n)
                                        <option value="{{ $n }}" {{ $autoVarCount == $n ? 'selected' : '' }}>{{ $n }} ცვლადი</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="display:flex;align-items:flex-end;padding-bottom:4px;">
                                    <label style="display:flex;align-items:center;gap:7px;font-size:0.78rem;font-weight:700;color:#374151;cursor:pointer;">
                                        <input type="checkbox" id="autoParens" onchange="syncAll();previewDebounce();" {{ !empty($autoCfg['parens']) ? 'checked' : '' }}>
                                        ფრჩხილები
                                    </label>
                                </div>
                            </div>
                            <div class="lbl">მოქმედებები</div>
                            <div class="op-check" style="margin-bottom:10px;">
                                <label><input type="checkbox" id="autoOpPlus" onchange="syncAll();previewDebounce();"
                                    {{ in_array('+', $autoCfg['operators'] ?? ['+']) ? 'checked' : '' }}> +</label>
                                <label><input type="checkbox" id="autoOpMinus" onchange="syncAll();previewDebounce();"
                                    {{ in_array('-', $autoCfg['operators'] ?? []) ? 'checked' : '' }}> −</label>
                                <label><input type="checkbox" id="autoOpMul" onchange="syncAll();previewDebounce();"
                                    {{ in_array('×', $autoCfg['operators'] ?? []) ? 'checked' : '' }}> ×</label>
                                <label><input type="checkbox" id="autoOpDiv" onchange="syncAll();previewDebounce();"
                                    {{ in_array('÷', $autoCfg['operators'] ?? []) ? 'checked' : '' }}> ÷</label>
                            </div>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.74rem;font-weight:700;color:#374151;cursor:pointer;margin-bottom:10px;">
                                <input type="checkbox" id="autoNoNegSteps" onchange="syncAll();previewDebounce();"
                                    {{ !array_key_exists('no_negative_steps', $autoCfg) || $autoCfg['no_negative_steps'] ? 'checked' : '' }}>
                                არ დავუშვათ უარყოფითი შუალედური მნიშვნელობა (მიმდევრობით ანგარიშისას)
                            </label>
                            <label style="display:flex;align-items:center;gap:7px;font-size:0.74rem;font-weight:700;color:#374151;cursor:pointer;margin-bottom:10px;">
                                <input type="checkbox" id="autoMixBlank" onchange="syncAll();previewDebounce();"
                                    {{ !array_key_exists('mix_blank', $autoCfg) || $autoCfg['mix_blank'] ? 'checked' : '' }}>
                                ცვლადი დავმალოთ შედეგის მაგივრად — მაგ. 4 + ? = 9 (გამორთული = ყოველთვის "...= ?")
                            </label>
                            <button type="button" class="nc-add" onclick="applyAutoBuild()">⚡ ცვლადების შექმნა</button>
                            <div style="font-size:0.62rem;color:#4b8a63;margin-top:6px;">აწყობს ქვემოთ ცვლადების მწკრივებს (N1, N2, ...) — თქვენ მხოლოდ min/max/ნაბიჯი დააზუსტეთ.</div>
                        </div>
                    </div>

                    <div class="nc-hdr" style="margin-top:4px;">
                        <span>სახელი</span><span>min</span><span>max</span><span>ნაბიჯი</span><span></span><span></span>
                    </div>
                    <div id="ncRows"></div>
                    <button type="button" class="nc-add" onclick="addNcRow()">+ ცვლადის დამატება</button>
                    <div style="font-size:0.62rem;color:#94a3b8;margin-top:4px;">ƒ/± ღილაკი ცვლის ტიპს: შემთხვევითი → „გამოთვლილი" (მაგ. N1+3) → „ოპერატორი" (ით მეტი/ნაკლები, ჯერ მეტი/ნაკლები — ფორმულაში ნიშნად, ტექსტში სიტყვად გამოჩნდება) → „პირობითი" (A&gt;B?X:Y)</div>
                    <div style="font-size:0.62rem;color:#94a3b8;margin-top:2px;">
                        „გამოთვლილი" და „პირობითი" ველებში: ოპერატორები <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">+ − × / %</code>,
                        ფუნქციები <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">round(x) floor(x) ceil(x) abs(x) min(a,b) max(a,b) pow(a,b) sqrt(x)</code>,
                        „პირობითი"-ში ასევე შედარება <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">&gt; &lt; &gt;= &lt;= == !=</code> და <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">?</code>/<code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">:</code> — მაგ. <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">N1&gt;N2?round(N1/2):max(N1,N2)</code>
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:0.75rem;font-weight:700;color:#374151;cursor:pointer;">
                        <input type="checkbox" id="uniqueNumVars" onchange="syncAll();previewDebounce();">
                        ყველა ცვლადი განსხვავებული
                    </label>
                    <input type="hidden" name="num_config" id="numConfigJson">
                    @error('num_config')<div class="err">{{ $message }}</div>@enderror

                    <div class="lbl" style="margin-top:10px;">პირობები <span style="color:#94a3b8;font-size:0.6rem;">(სურვილისამებრ)</span></div>
                    <div class="hint" style="margin-bottom:6px;">ორივე მხარეს შეიძლება გამოთქმა, ფუნქციების ჩათვლით: <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">N1 &gt; N2+N3</code> &nbsp;·&nbsp; <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">round(N1/2) &gt;= N2</code></div>
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
                        <button type="button" id="atBtnNum" class="at-btn" onclick="setAnswerType('numeric')">🔢 რიცხვი (არჩევანი)</button>
                        <button type="button" id="atBtnInput" class="at-btn" onclick="setAnswerType('numeric_input')">⌨️ რიცხვი (ჩაწერა)</button>
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
                            <span class="chip chip-op" onclick="insertFormulaFn('round')" title="უახლოეს მთელამდე">round()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('floor')" title="ქვედა მთელამდე">floor()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('ceil')" title="ზედა მთელამდე">ceil()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('abs')" title="აბსოლუტური მნიშვნელობა (ყოველთვის დადებითი)">abs()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('min')" title="პატარა ორიდან — მაგ. min(N1,N2)">min()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('max')" title="დიდი ორიდან — მაგ. max(N1,N2)">max()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('pow')" title="ხარისხი — მაგ. pow(N1,2) = N1²">pow()</span>
                            <span class="chip chip-op" onclick="insertFormulaFn('sqrt')" title="კვადრატული ფესვი">sqrt()</span>
                        </div>
                        <input type="text" id="correctFormula" class="fc"
                            placeholder="N1+N2"
                            value="{{ old('answer_type', $template?->answer_type) !== 'text' ? old('correct_formula', $template?->correct_formula) : '' }}"
                            oninput="previewDebounce()">
                        <div class="hint">
                            ოპერატორები: <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">+ − × / %</code> და ფრჩხილები &nbsp;·&nbsp;
                            ფუნქციები (ორარგუმენტიანებს მძიმით გამოყავით): <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">round(x) floor(x) ceil(x) abs(x) min(a,b) max(a,b) pow(a,b) sqrt(x)</code>
                            <br>მაგ: N1+N2 &nbsp;·&nbsp; (N1+N2)*N3 &nbsp;·&nbsp; round((N1+N2)/2) &nbsp;·&nbsp; min(N1,N2)*2 &nbsp;·&nbsp; pow(N1,2)
                        </div>
                        @error('correct_formula')<div class="err">{{ $message }}</div>@enderror

                        {{-- Only "numeric" (multiple-choice) needs distractors — "numeric_input"
                             (⌨️ child types the number) has no options to build. --}}
                        <div id="distractorSection">
                            <div class="lbl" style="margin-top:10px;">მცდარი პასუხების დიაპაზონი <span style="color:#94a3b8;font-size:0.6rem;">±</span></div>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <div class="lbl" style="margin:0;white-space:nowrap;">min</div>
                                <input type="number" id="distMin" class="nc-inp" style="width:76px;" min="1"
                                    value="{{ $template?->answer_type !== 'text' ? ($template?->distractors['min'] ?? 1) : 1 }}" oninput="previewDebounce()">
                                <div class="lbl" style="margin:0;white-space:nowrap;">max</div>
                                <input type="number" id="distMax" class="nc-inp" style="width:76px;" min="1"
                                    value="{{ $template?->answer_type !== 'text' ? ($template?->distractors['max'] ?? 10) : 10 }}" oninput="previewDebounce()">
                            </div>
                            <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;">
                                <button type="button" id="noneCorrectBtn" class="at-btn" onclick="toggleNoneCorrect()">☐ არცერთი სწორეა</button>
                            </div>
                        </div>
                    </div>

                    {{-- Text UI --}}
                    <div id="txtAnsUi" style="display:none;">
                        <div class="lbl" style="display:flex;justify-content:space-between;align-items:center;">
                            <span>სწორი პასუხი — ცვლადი</span>
                            <button type="button" id="textCondToggle" class="nc-toggle" style="font-size:0.56rem;" onclick="toggleTextCondMode()" title="გადართვა პირობითი ფორმულაზე">⚡ პირობითი</button>
                        </div>
                        <select id="textCorrectVar" class="fc" onchange="syncAll();previewDebounce();">
                            <option value="">— ცვლადი —</option>
                        </select>
                        <div id="noVarsHint" style="display:none;font-size:0.6rem;color:#94a3b8;margin-top:-6px;margin-bottom:10px;">სტრიქონის ცვლადები არ არის — თემატიკაში დაამატეთ</div>

                        {{-- Conditional formula (hidden unless toggled on) --}}
                        <div id="textCondWrap" style="display:none;">
                            <input type="text" id="textCondFormula" class="fc" placeholder="მაგ: TOTAL1>TOTAL2?CITY1:CITY2"
                                oninput="syncAll();previewDebounce();">
                            <div class="chips" id="textCondChips"></div>
                            <div class="hint">პირობა რიცხვით ცვლადებზე (&gt;, &lt;, &gt;=, &lt;=, ==, !=) — შედარების მხარეს ასევე გამოთქმა/ფუნქციებიც შეიძლება, მაგ. <code style="background:#f1f5f9;padding:1px 5px;border-radius:2px;">round(N1/2)&gt;N2</code> — მერე ? და : შორის — თითო მხარეს ცვლადის სახელი ან ტექსტი. ჯაჭვურადაც შეიძლება: A&gt;B?X:C&gt;D?Y:Z</div>
                        </div>

                        <div class="lbl" style="margin-top:4px;display:flex;align-items:center;gap:6px;justify-content:space-between;">
                            <span>ვარიანტების ცვლადები <span style="color:#94a3b8;font-size:0.6rem;">(კლიკით)</span></span>
                            <span style="font-size:0.62rem;color:#64748b;white-space:nowrap;">
                                რამდენი არასწორი:
                                <input type="number" id="textDistCount" class="fc" value="3" min="1" max="8"
                                    style="width:40px;display:inline-block;margin:0;padding:2px 4px;text-align:center;"
                                    oninput="syncAll();previewDebounce();">
                            </span>
                        </div>
                        <div class="hint" style="margin:-2px 0 4px;">მონიშნეთ ნებისმიერი რაოდენობა — თითო გენერაციაზე ზემოთ მითითებული რაოდენობა შემთხვევით შეირჩევა მათგან (მათ გამორჩევით, რაც ამ რაუნდში სწორ პასუხს დაემთხვა)</div>
                        <div id="textOptChips"></div>
                        <div id="noVarsHint2" style="display:none;font-size:0.6rem;color:#94a3b8;margin-top:4px;">ცვლადები არ არის</div>

                        {{-- Hand-typed distractor templates, e.g. a combined var token
                             string — a combination the chip picker above can't express
                             since each chip is a single existing variable's value, not
                             several combined into one custom string. Also joins the
                             random pool above, not the pinned "ყველა სწორია"-style ones. --}}
                        <div class="lbl" style="margin-top:8px;">სავარაუდო პასუხის ხელით ჩაწერა <span style="color:#94a3b8;font-size:0.6rem;">(მაგ: @{{N1}} და @{{N2}})</span></div>
                        <div class="hint" style="margin:-2px 0 4px;">@{{*}} — ავტომატურად ავირჩევთ შემთხვევით ცვლადს ამ ადგილზე ყოველ გენერაციაზე, ანუ ერთი შაბლონი @{{*}} და @{{*}} თავისთავად დააგენერირებს "რამდენი არასწორი"-ით მითითებულ რაოდენობას სხვადასხვა კომბინაციით — ცალ-ცალკე ხელით ჩაწერა არ გჭირდებათ</div>
                        <div style="display:flex;gap:4px;">
                            <input type="text" id="textOptCustomInput" class="fc" style="margin:0;"
                                placeholder="@{{*}} და @{{*}}"
                                onkeydown="if(event.key==='Enter'){event.preventDefault();addTextOptCustom();}">
                            <button type="button" class="btn-cancel" style="padding:6px 10px;white-space:nowrap;" onclick="addTextOptCustom()">+ დამატება</button>
                        </div>
                        <div class="chips" id="textOptCustomChips" style="margin-top:4px;"></div>
                    </div>

                    <input type="hidden" name="correct_formula" id="correctFormulaHidden"
                        value="{{ old('correct_formula', $template?->correct_formula) }}">
                    <input type="hidden" name="distractors" id="distractorsJson">
                    @error('distractors')<div class="err">{{ $message }}</div>@enderror
                </div>
            </div>
            </div>{{-- /mcSections --}}

            {{-- Hint — shown under the question regardless of type (pyramid/code/crossword too) --}}
            <div class="card">
                <div class="lbl">მინიშნება <span style="color:#94a3b8;font-size:0.6rem;">(გამოჩნდება კითხვის ქვეშ)</span></div>
                <textarea name="hint_text" id="hintText" class="fc" rows="2"
                    placeholder="მინიშნება..."
                    oninput="previewDebounce()">{{ old('hint_text', $template?->hint_text) }}</textarea>
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
    numConfig:          @json($template?->num_config ?? []),
    conditions:         @json($template?->conditions ?? []),
    themeVarMap:        {},
    varGroups:          [],
    topicsByGrade:      @json($topics->groupBy('grade_id')->map(fn($g) => $g->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values())),
    maxLevelByGrade:    @json($grades->pluck('max_level', 'id')),
    selectedTopic:      {{ (int)($template?->topic_id ?? 0) }},
    selectedTheme:      {{ (int)($template?->theme_id ?? $defaultThemeId ?? 0) }},
    distractors:        @json($template?->distractors ?? []),
    answerType:         '{{ old('answer_type', $template?->answer_type ?? 'numeric') }}',
    editCorrectFormula: @json(old('correct_formula', $template?->correct_formula ?? '')),
    allThemesData:      @json($allThemesData),
};
</script>
@verbatim
<script>
const OB = '{' + '{', CB = '}' + '}';
const SPEC = { '__ALL__': 'ყველა სწორეა', '__NONE__': 'არცერთი სწორია', '__EQUAL__': 'თანაბარია' };

function getUsedTemplateVars() {
    const tmpl = document.getElementById('templateText').value;
    const matches = tmpl.match(/\{\{([A-Z0-9_]+)\}\}/g) || [];
    return new Set(matches.map(function(m) { return m.slice(2, -2); }));
}
function onTemplateInput() {
    const themeEl = document.getElementById('themeSelect');
    if (themeEl && themeEl.value) {
        const data = _KS.allThemesData[themeEl.value] || { groups: [], standalone: [], varMap: {} };
        renderTextCorrectVar(data);
        renderTextOptChips(data);
    }
    previewDebounce();
}

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
    applyGradeMaxLevel(gradeId);
}

// ── Grade → difficulty cap (a grade's max_level, set on the Grades admin page)
function applyGradeMaxLevel(gradeId) {
    const max = gradeId ? (+_KS.maxLevelByGrade[gradeId] || 5) : 5;
    const hint = document.getElementById('diffMaxHint');
    document.querySelectorAll('#diffRow .diff-btn').forEach((b, i) => {
        b.style.display = (i + 1) <= max ? '' : 'none';
    });
    if (gradeId && max < 5) {
        hint.style.display = '';
        hint.textContent = 'ამ კლასს მაქსიმუმ ' + max + ' დონე აქვს (იმართება კლასების გვერდზე)';
    } else {
        hint.style.display = 'none';
    }
    const diffInput = document.getElementById('diffInput');
    if (+diffInput.value > max) {
        setDiff(max);
    }
}

// ── Theme → variable chips
// Cached so renderNcRows() can re-render the text-answer correct/distractor
// var pickers (which also list numeric vars) whenever a numeric row is
// added/removed/renamed, without needing a theme change to trigger it.
let lastThemeData = { groups: [], standalone: [], varMap: {} };
function onThemeChange(themeId) {
    const data = (themeId && _KS.allThemesData[themeId])
        ? _KS.allThemesData[themeId]
        : { groups: [], standalone: [], varMap: {} };
    lastThemeData    = data;
    _KS.varGroups    = data.groups;
    _KS.themeVarMap  = data.varMap;
    renderStrVarSection(data);
    renderTextCorrectVar(data);
    renderTextOptChips(data);
    previewDebounce();
}
function renderStrVarSection(data) {
    const el = document.getElementById('strVarSection');
    if (!data.groups.length && !data.standalone.length) { el.innerHTML = ''; return; }
    let html = '<div class="lbl">სტრიქონის ცვლადები</div>';
    data.groups.forEach(function(grp) {
        html += '<div style="margin-bottom:6px;">'
            + '<div style="font-size:0.54rem;color:#7c3aed;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:3px;">' + grp.name + '</div>'
            + '<div class="chips" style="margin-bottom:0;">'
            + grp.slots.map(function(s) {
                return '<span class="chip chip-theme" onclick="insertVar(\'' + s + '\')">' + OB + s + CB + '</span>';
            }).join('')
            + '</div></div>';
    });
    if (data.standalone.length) {
        html += '<div class="chips">'
            + data.standalone.map(function(v) {
                return '<span class="chip chip-theme" onclick="insertVar(\'' + v + '\')">' + OB + v + CB + '</span>';
            }).join('')
            + '</div>';
    }
    el.innerHTML = html;
}
function renderTextCorrectVar(data) {
    const sel = document.getElementById('textCorrectVar');
    const prevVal = sel.value;
    const used = getUsedTemplateVars();
    sel.innerHTML = '<option value="">— ცვლადი —</option>';
    data.groups.forEach(function(grp) {
        const slots = grp.slots.filter(function(s) { return used.has(s); });
        if (!slots.length) return;
        const og = document.createElement('optgroup');
        og.label = grp.name;
        slots.forEach(function(s) { og.appendChild(new Option(s, s)); });
        sel.appendChild(og);
    });
    const sa = data.standalone.filter(function(v) { return used.has(v); });
    if (sa.length) {
        const og = document.createElement('optgroup');
        og.label = 'სხვა';
        sa.forEach(function(v) { og.appendChild(new Option(v, v)); });
        sel.appendChild(og);
    }
    // Numeric (N1, N2, DEL1...) vars -- NOT filtered by template_text usage
    // like theme vars are above, since these typically drive the answer/
    // distractors without necessarily appearing in the displayed question
    // text itself (e.g. #91's DEL1/DEL2, computed but never shown directly).
    const ncNames = ncRows.filter(function(r) { return r.name; }).map(function(r) { return r.name; });
    if (ncNames.length) {
        const ncOg = document.createElement('optgroup');
        ncOg.label = 'რიცხვითი ცვლადები';
        ncNames.forEach(function(n) { ncOg.appendChild(new Option(n, n)); });
        sel.appendChild(ncOg);
    }
    const specOg = document.createElement('optgroup');
    specOg.label = '──────';
    Object.entries(SPEC).forEach(function(e) { specOg.appendChild(new Option(e[1], e[0])); });
    sel.appendChild(specOg);
    if (prevVal) sel.value = prevVal;
    const hasVars = data.groups.some(function(g) { return g.slots.some(function(s) { return used.has(s); }); }) || sa.length || ncNames.length;
    var hint = document.getElementById('noVarsHint');
    if (hint) hint.style.display = (hasVars || !used.size) ? 'none' : '';
    syncAll();
}
function renderTextOptChips(data) {
    const el = document.getElementById('textOptChips');
    const prevSel = new Set(Array.from(document.querySelectorAll('.text-opt-chip.sel')).map(function(c) { return c.dataset.varname; }));
    const used = getUsedTemplateVars();
    let html = '';
    data.groups.forEach(function(grp) {
        const slots = grp.slots.filter(function(s) { return used.has(s); });
        if (!slots.length) return;
        html += '<div style="font-size:0.52rem;color:#7c3aed;letter-spacing:0.1em;text-transform:uppercase;margin:4px 0 2px;">' + grp.name + '</div>'
            + '<div class="chips" style="margin-bottom:4px;">'
            + slots.map(function(s) {
                return '<span class="chip chip-txt text-opt-chip' + (prevSel.has(s) ? ' sel' : '') + '" data-varname="' + s + '" onclick="toggleTextOpt(this)">' + s + '</span>';
            }).join('')
            + '</div>';
    });
    const sa = data.standalone.filter(function(v) { return used.has(v); });
    if (sa.length) {
        html += '<div class="chips">'
            + sa.map(function(v) {
                return '<span class="chip chip-txt text-opt-chip' + (prevSel.has(v) ? ' sel' : '') + '" data-varname="' + v + '" onclick="toggleTextOpt(this)">' + v + '</span>';
            }).join('')
            + '</div>';
    }
    // Numeric (N1, N2, DEL1...) vars -- NOT filtered by template_text usage
    // like theme vars are above (see renderTextCorrectVar()'s matching note).
    // Part of the random pool (below "count" sampling), same as a plain
    // theme-var chip -- only __ALL__/__NONE__/__EQUAL__ are pinned.
    const ncNames = ncRows.filter(function(r) { return r.name; }).map(function(r) { return r.name; });
    if (ncNames.length) {
        html += '<div style="font-size:0.52rem;color:#7c3aed;letter-spacing:0.1em;text-transform:uppercase;margin:4px 0 2px;">რიცხვითი ცვლადები</div>'
            + '<div class="chips" style="margin-bottom:4px;">'
            + ncNames.map(function(n) {
                return '<span class="chip chip-txt text-opt-chip' + (prevSel.has(n) ? ' sel' : '') + '" data-varname="' + n + '" onclick="toggleTextOpt(this)">' + n + '</span>';
            }).join('')
            + '</div>';
    }
    html += '<div style="font-size:0.52rem;color:#94a3b8;margin-top:6px;border-top:1px solid #f1f5f9;padding-top:6px;">ეს ყოველთვის დაემატება, ზემოთ მითითებული რაოდენობის გარდა:</div>'
        + '<div class="chips">'
        + Object.entries(SPEC).map(function(e) {
            return '<span class="chip chip-txt text-opt-chip' + (prevSel.has(e[0]) ? ' sel' : '') + '" data-varname="' + e[0] + '" onclick="toggleTextOpt(this)">' + e[1] + '</span>';
        }).join('')
        + '</div>';
    el.innerHTML = html;
    var hint2 = document.getElementById('noVarsHint2');
    if (hint2) hint2.style.display = 'none';
}

// ── Question type toggle
function setQType(type) {
    document.getElementById('qtInput').value = type;
    document.querySelectorAll('.qt-btn').forEach(b => {
        b.classList.remove('sel');
        if ((type === 'pyramid'         && b.textContent.includes('პირამიდა'))   ||
            (type === 'multiple_choice' && b.textContent.includes('Multiple'))    ||
            (type === 'code'            && b.textContent.includes('კოდი'))        ||
            (type === 'crossword'       && b.textContent.includes('კროსვორდი'))   ||
            (type === 'scale'           && b.textContent.includes('სასწორი'))) {
            b.classList.add('sel');
        }
    });
    applyQType(type);
}
function applyQType(type) {
    const isPyr   = type === 'pyramid';
    const isCode  = type === 'code';
    const isCw    = type === 'crossword';
    const isScale = type === 'scale';
    const isMC    = !isPyr && !isCode && !isCw && !isScale;
    document.getElementById('pyrFields').style.display      = isPyr   ? 'block' : 'none';
    document.getElementById('codeFields').style.display     = isCode  ? 'block' : 'none';
    document.getElementById('cwFields').style.display       = isCw    ? 'block' : 'none';
    document.getElementById('scaleFields').style.display     = isScale ? 'block' : 'none';
    document.getElementById('themeFieldWrap').style.display = isMC    ? 'block' : 'none';
    document.getElementById('mcSections').style.display     = isMC    ? 'block' : 'none';
    if (isPyr)   { syncPyrConfig();   return; }
    if (isCode)  { syncCodeConfig();  return; }
    if (isCw)    { syncCwConfig();    return; }
    if (isScale) { syncScaleConfig(); return; }
}
function syncPyrConfig() {
    const h    = document.getElementById('pyrHeight').value;
    const mx   = document.getElementById('pyrMax').value;
    const hide = document.getElementById('pyrHide').value;
    document.getElementById('numConfigJson').value = JSON.stringify({
        height: parseInt(h), max_base: parseInt(mx), hidden_count: parseInt(hide)
    });
    if (document.getElementById('qtInput').value === 'pyramid') genPreviewPyramid();
}
function syncCodeConfig() {
    const count      = parseInt(document.getElementById('codeSymCount').value)   || 3;
    const minV       = parseInt(document.getElementById('codeMinVal').value)      || 1;
    const maxV       = parseInt(document.getElementById('codeMaxVal').value)      || 9;
    const varsPerEq  = parseInt(document.getElementById('codeVarsPerEq').value)   || 2;
    const uniqueVals = document.getElementById('codeUniqueVals').checked;
    const ops = [];
    if (document.getElementById('codeOpPlus').checked)  ops.push('+');
    if (document.getElementById('codeOpMinus').checked) ops.push('-');
    if (document.getElementById('codeOpMul').checked)   ops.push('×');
    if (document.getElementById('codeOpDiv').checked)   ops.push('÷');
    document.getElementById('numConfigJson').value = JSON.stringify({
        symbol_count:  count,
        min_val:       minV,
        max_val:       maxV,
        operators:     ops.length ? ops : ['+'],
        vars_per_eq:   varsPerEq,
        unique_values: uniqueVals,
    });
    if (document.getElementById('qtInput').value === 'code') genPreviewCode();
}
function syncCwConfig() {
    const minV = parseInt(document.getElementById('cwMinVal').value)  || 1;
    const maxV = parseInt(document.getElementById('cwMaxVal').value)  || 10;
    const ops  = [];
    if (document.getElementById('cwOpPlus').checked)  ops.push('+');
    if (document.getElementById('cwOpMinus').checked) ops.push('-');
    if (document.getElementById('cwOpMul').checked)   ops.push('×');
    if (document.getElementById('cwOpDiv').checked)   ops.push('÷');
    const gridVal = document.querySelector('input[name="_cwGrid"]:checked')?.value || '22';
    const rows = parseInt(gridVal[0]) || 2;
    const cols = parseInt(gridVal[1]) || 2;
    // Max that doesn't fully reveal any row or column
    const maxAllowed = Math.min(rows * (cols - 1), (rows - 1) * cols);
    const revealed_count = Math.max(0, Math.min(maxAllowed, parseInt(document.getElementById('cwRevealedCount').value)||0));
    const minRec = (rows-1)*(cols-1);
    const hint = document.getElementById('cwRevHint');
    if (hint) hint.textContent = `(მინ. ${minRec} რეკ., მაქს. ${maxAllowed})`;
    document.getElementById('numConfigJson').value = JSON.stringify({
        min_val: minV, max_val: maxV, operators: ops.length ? ops : ['+'], rows, cols, revealed_count,
    });
    if (document.getElementById('qtInput').value === 'crossword') genPreviewCw();
}

function genPreviewCw() {
    const minV = parseInt(document.getElementById('cwMinVal').value)  || 1;
    const maxV = parseInt(document.getElementById('cwMaxVal').value)  || 10;
    const ops  = [];
    if (document.getElementById('cwOpPlus').checked)  ops.push('+');
    if (document.getElementById('cwOpMinus').checked) ops.push('-');
    if (document.getElementById('cwOpMul').checked)   ops.push('×');
    if (document.getElementById('cwOpDiv').checked)   ops.push('÷');
    if (!ops.length) ops.push('+');
    const gridVal = document.querySelector('input[name="_cwGrid"]:checked')?.value || '22';
    const R = parseInt(gridVal[0]) || 2;
    const C = parseInt(gridVal[1]) || 2;

    function cwCalcChain(vals, op) {
        if (vals.length > 2 && (op === '÷' || op === '/')) return null;
        let r = vals[0];
        for (let i = 1; i < vals.length; i++) {
            const v = vals[i];
            if (op === '+') r += v;
            else if (op === '-') { if (r <= v) return null; r -= v; }
            else if (op === '×' || op === '*') r *= v;
            else if (op === '÷' || op === '/') { if (v === 0 || r % v !== 0) return null; r = r / v; }
        }
        return r > 0 ? r : null;
    }

    // Operators are picked ONCE per outer attempt, then only the cell VALUES are
    // retried for a batch of inner attempts before giving up on that combo and
    // picking new operators — re-rolling operators on every single value attempt
    // let whichever operator is statistically easier (e.g. '+', always valid for
    // positive numbers) win the "first success" race far more often than a harder
    // one (e.g. '-', which needs the first number to stay bigger). Mirrors
    // CrosswordService::build()'s outer/inner split.
    let cells, rowOps, colOps, rowResults, colResults, valid = false;
    outer: for (let opAttempt = 0; opAttempt < 10; opAttempt++) {
        rowOps = Array.from({length: R}, () => ops[Math.floor(Math.random()*ops.length)]);
        colOps = Array.from({length: C}, () => ops[Math.floor(Math.random()*ops.length)]);
        // If multiple operators were selected but every slot collapsed to the same
        // one by chance, force one slot to differ (mirrors CrosswordService::build()).
        if (ops.length > 1) {
            const combined = rowOps.concat(colOps);
            if (combined.length > 1 && new Set(combined).size < 2) {
                let alt = combined[0];
                while (alt === combined[0]) alt = ops[Math.floor(Math.random()*ops.length)];
                if (rowOps.length && (!colOps.length || Math.random() < 0.5)) {
                    rowOps[Math.floor(Math.random()*rowOps.length)] = alt;
                } else {
                    colOps[Math.floor(Math.random()*colOps.length)] = alt;
                }
            }
        }
        for (let attempt = 0; attempt < 30; attempt++) {
            cells = Array.from({length: R*C}, () => minV + Math.floor(Math.random()*(maxV-minV+1)));
            rowResults = []; colResults = []; valid = true;
            for (let r = 0; r < R && valid; r++) {
                const rc = cells.slice(r*C, r*C+C);
                const res = cwCalcChain(rc, rowOps[r]);
                if (!res) { valid=false; break; }
                rowResults[r] = res;
            }
            if (!valid) continue;
            for (let c = 0; c < C && valid; c++) {
                const cc = Array.from({length: R}, (_,r) => cells[r*C+c]);
                const res = cwCalcChain(cc, colOps[c]);
                if (!res) { valid=false; break; }
                colResults[c] = res;
            }
            if (valid) break outer;
        }
    }
    if (!valid) { rowOps=Array(R).fill('+');colOps=Array(C).fill('+');
        rowResults=[];colResults=[];
        for(let r=0;r<R;r++){let s=0;for(let c=0;c<C;c++)s+=cells[r*C+c];rowResults[r]=s;}
        for(let c=0;c<C;c++){let s=0;for(let r=0;r<R;r++)s+=cells[r*C+c];colResults[c]=s;}
    }

    const maxAllowed = Math.min(R * (C - 1), (R - 1) * C);
    const revCount = Math.max(0, Math.min(maxAllowed, parseInt(document.getElementById('cwRevealedCount').value)||0));

    function noFullLine(revSet) {
        for (let r = 0; r < R; r++) {
            let full = true;
            for (let c2 = 0; c2 < C; c2++) { if (!revSet.has(r*C+c2)) { full=false; break; } }
            if (full) return false;
        }
        for (let c2 = 0; c2 < C; c2++) {
            let full = true;
            for (let r = 0; r < R; r++) { if (!revSet.has(r*C+c2)) { full=false; break; } }
            if (full) return false;
        }
        return true;
    }

    let revealedSet = new Set();
    if (revCount > 0) {
        const allPos = Array.from({length:R*C},(_,i)=>i);
        outer: for (let cnt = revCount; cnt >= 1; cnt--) {
            for (let attempt = 0; attempt < 200; attempt++) {
                const sh = [...allPos].sort(() => Math.random() - 0.5);
                const trial = new Set(sh.slice(0, cnt));
                if (noFullLine(trial)) { revealedSet = trial; break outer; }
            }
        }
    }

    const SZ=40, OP=20, EQ=20, RES=40;
    const cellSt = `width:${SZ}px;height:${SZ}px;border-radius:8px;`;
    const cellHidden = `<div style="${cellSt}border:2px dashed #a5b4fc;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;color:#4f46e5;font-weight:700;">?</div>`;
    const cellGiven = v => `<div style="${cellSt}background:#d1fae5;border:2px solid #6ee7b7;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#065f46;font-weight:700;">${v}</div>`;
    const cellRes   = v => `<div style="${cellSt}background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#374151;font-weight:700;">${v}</div>`;
    const opEl  = v => `<div style="width:${OP}px;text-align:center;font-size:0.9rem;color:#64748b;">${v}</div>`;
    const eqEl  = `<div style="width:${EQ}px;text-align:center;font-size:0.8rem;color:#94a3b8;">=</div>`;
    const resEl = v => `<div style="width:${RES}px;height:${SZ}px;background:#e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#374151;font-weight:700;">${v}</div>`;
    const emp   = `<div></div>`;

    // Build CSS grid columns: C cells + (C-1) ops + eq + result
    const colParts = [];
    for(let c=0;c<C;c++){colParts.push(SZ+'px');if(c<C-1)colParts.push(OP+'px');}
    colParts.push(EQ+'px',RES+'px');
    const rowParts = [];
    for(let r=0;r<R;r++){rowParts.push(SZ+'px');if(r<R-1)rowParts.push(OP+'px');}
    rowParts.push(EQ+'px',RES+'px');

    const items = [];
    for (let dr=0; dr<=2*R; dr++) {
        for (let dc=0; dc<=2*C; dc++) {
            let html = emp;
            if (dr <= 2*R-2) {
                if (dr%2===0) {
                    const r=dr/2;
                    if (dc<=2*C-2) {
                        if(dc%2===0){
                            const cellIdx = r*C + dc/2;
                            const cellHtml = revealedSet.has(cellIdx) ? cellGiven(cells[cellIdx]) : cellHidden;
                            html=`<div style="display:flex;align-items:center;justify-content:center;">${cellHtml}</div>`;
                        } else html=`<div style="display:flex;align-items:center;justify-content:center;">${opEl(rowOps[r])}</div>`;
                    } else if(dc===2*C-1) {
                        html=`<div style="display:flex;align-items:center;justify-content:center;">${eqEl}</div>`;
                    } else {
                        html=`<div style="display:flex;align-items:center;justify-content:center;">${resEl(rowResults[r])}</div>`;
                    }
                } else {
                    if(dc<=2*C-2&&dc%2===0){const c2=dc/2;html=`<div style="display:flex;align-items:center;justify-content:center;">${opEl(colOps[c2])}</div>`;}
                }
            } else if(dr===2*R-1) {
                if(dc<=2*C-2&&dc%2===0) html=`<div style="display:flex;align-items:center;justify-content:center;">${eqEl}</div>`;
            } else {
                if(dc<=2*C-2&&dc%2===0){const c2=dc/2;html=`<div style="display:flex;align-items:center;justify-content:center;">${resEl(colResults[c2])}</div>`;}
            }
            items.push(html);
        }
    }

    document.getElementById('prevQ').innerHTML = '🧮 კროსვორდი';
    document.getElementById('prevHint').innerHTML = '';
    document.getElementById('prevWarn').innerHTML = '';
    document.getElementById('prevOpts').innerHTML =
        `<div style="display:grid;grid-template-columns:${colParts.join(' ')};grid-template-rows:${rowParts.join(' ')};gap:4px;align-items:center;justify-items:center;margin:10px auto;max-width:max-content;">${items.join('')}</div>`;
    document.getElementById('prevFormula').innerHTML =
        `${R}×${C} · ხაზები:[${rowOps.join(',')}] სვეტები:[${colOps.join(',')}]`;
    document.getElementById('prevVars').innerHTML = '';
}

// ── Scale (balance-scale) config ────────────────────────────────────────────
let scaleSymbols = []; // [{id, kind:'emoji'|'weight', symbol, min, max}]
let scaleLeft    = []; // [{key, count}]
let scaleRight   = []; // [{key, count}]

function scaleSymbolLabel(i) { return String.fromCharCode(65 + i); }

// Mirrors ScaleService::scaleSvg() (PHP) — straight, level beam resting on a
// fixed triangle stand; items render in the row right above it (flex-end),
// so they visually sit on top of the beam.
function scaleSvgHtml() {
    return '<svg viewBox="0 0 280 60" style="display:block;width:100%;height:auto;overflow:visible;">'
        + '<line x1="10" y1="10" x2="270" y2="10" stroke="#1a7a3c" stroke-width="5" stroke-linecap="round"/>'
        + '<circle cx="140" cy="10" r="6" fill="#1a7a3c"/>'
        + '<polygon points="118,58 162,58 140,16" fill="#1a7a3c"/>'
        + '</svg>';
}

// Curated emoji pool for the admin's "emoji" symbol picker — same set
// CodeService uses, so a child sees familiar icons across question types.
const SCALE_EMOJI_POOL = [
    '🍎','🍌','🍓','🍊','🍇','🍐','🍍','🍒','🍉','🍋',
    '🍑','🥝','⭐','🌙','💎','🔥','🌊','🎵','🐶','🐱',
    '🐻','🦊','🐼','🌸','🌈','🎯','🏆','🚀','🎸','🦋',
];
function addScaleSymbol(kind, symbol, min, max, id) {
    const sid = id || ('s' + Date.now() + '_' + Math.random().toString(36).slice(2, 6));
    // Defaults to "weight" (a plain number the child sees directly) — switching
    // to "emoji" is an explicit choice, made by picking from SCALE_EMOJI_POOL
    // below rather than typing/pasting a character.
    scaleSymbols.push({ id: sid, kind: kind || 'weight', symbol: symbol || SCALE_EMOJI_POOL[0], min: min ?? 1, max: max ?? 9 });
    renderScaleSymbols();
    syncScaleConfig();
    return sid;
}
function removeScaleSymbol(i) {
    const id = scaleSymbols[i].id;
    scaleSymbols.splice(i, 1);
    scaleLeft  = scaleLeft.filter(it => it.key !== id);
    scaleRight = scaleRight.filter(it => it.key !== id);
    renderScaleSymbols();
    renderScalePan('left');
    renderScalePan('right');
    syncScaleConfig();
}
function renderScaleSymbols() {
    const el = document.getElementById('scaleSymRows');
    el.innerHTML = scaleSymbols.map((s, i) => `
        <div style="display:grid;grid-template-columns:34px 1fr 70px 56px 56px 28px;gap:4px;align-items:center;margin-bottom:4px;">
            <div style="font-weight:800;color:#94a3b8;text-align:center;">${scaleSymbolLabel(i)}</div>
            <select class="nc-inp" onchange="scaleSymbols[${i}].kind=this.value; renderScaleSymbols(); syncScaleConfig();">
                <option value="weight" ${s.kind === 'weight' ? 'selected' : ''}>წონა (რიცხვი)</option>
                <option value="emoji" ${s.kind === 'emoji' ? 'selected' : ''}>ემოჯი</option>
            </select>
            ${s.kind === 'emoji'
                ? `<select class="nc-inp" style="text-align:center;font-size:1.1rem;" onchange="scaleSymbols[${i}].symbol=this.value; renderScaleSymbols(); syncScaleConfig();">
                    ${SCALE_EMOJI_POOL.map(e => `<option value="${e}" ${e === s.symbol ? 'selected' : ''}>${e}</option>`).join('')}
                  </select>`
                : `<div style="font-size:0.58rem;color:#94a3b8;text-align:center;">(ჩანს რიცხვი)</div>`}
            <input type="number" class="nc-inp" value="${s.min}" onchange="scaleSymbols[${i}].min=+this.value; syncScaleConfig();">
            <input type="number" class="nc-inp" value="${s.max}" onchange="scaleSymbols[${i}].max=+this.value; syncScaleConfig();">
            <button type="button" class="nc-del" onclick="removeScaleSymbol(${i})">✕</button>
        </div>
    `).join('');
    updateScaleAtHint();
    renderScaleConds();
}

// ── Conditions (scale) — same engine/UI pattern as the generic MC "პირობები"
// section (#condRows/addCond()), but driven by the symbols' letter labels
// (scaleSymbolLabel()) instead of ncRows names, and writing into the SAME
// #conditionsJson hidden field (the generic sync is bypassed for scale).
let scaleConditions = [];
let scaleCondIdSeq  = 1;
let scaleCondFocus  = null;

function addScaleCond(left = '', op = '>', right = '', silent = false) {
    const names = scaleSymbols.map((s, i) => scaleSymbolLabel(i));
    if (!names.length) return;
    scaleConditions.push({ id: scaleCondIdSeq++, left: left || names[0], op, right: right || (names[1] || names[0]) });
    renderScaleConds();
    if (!silent) previewDebounce();
}
function removeScaleCond(id) {
    scaleConditions = scaleConditions.filter(c => c.id !== id);
    renderScaleConds();
}
function updScaleCond(id, field, val) {
    const c = scaleConditions.find(c => c.id === id);
    if (c) { c[field] = val; syncScaleConfig(); previewDebounce(); }
}
function insertIntoScaleCond(text) {
    if (!scaleCondFocus) return;
    const inp = document.getElementById('scalecond-' + scaleCondFocus.id + '-' + scaleCondFocus.field);
    if (!inp) return;
    const s = inp.selectionStart ?? inp.value.length;
    const e = inp.selectionEnd   ?? inp.value.length;
    inp.value = inp.value.slice(0, s) + text + inp.value.slice(e);
    inp.selectionStart = inp.selectionEnd = s + text.length;
    inp.focus();
    updScaleCond(scaleCondFocus.id, scaleCondFocus.field, inp.value.trim());
}
function renderScaleConds() {
    const container = document.getElementById('scaleCondRows');
    if (!container) return;
    container.innerHTML = '';
    const names   = scaleSymbols.map((s, i) => scaleSymbolLabel(i));
    const hasVars = names.length > 0;

    document.getElementById('scaleAddCondBtn').style.display = hasVars ? '' : 'none';
    document.getElementById('scaleCondNoVars').style.display = hasVars ? 'none' : '';

    const chipBar = document.getElementById('scaleCondChips');
    chipBar.style.display = hasVars && scaleConditions.length ? '' : 'none';
    chipBar.innerHTML = '';
    names.forEach(n => {
        const c = document.createElement('span');
        c.className = 'cond-chip'; c.textContent = n;
        c.onclick = () => insertIntoScaleCond(n); chipBar.appendChild(c);
    });
    OP_SYMS.forEach(s => {
        const c = document.createElement('span');
        c.className = 'cond-chip cond-op-chip'; c.textContent = s;
        c.onclick = () => insertIntoScaleCond(s); chipBar.appendChild(c);
    });

    scaleConditions.forEach(cond => {
        const div = document.createElement('div');
        div.className = 'cond-row';
        const opOpts = Object.entries(OP_LABELS).map(([v, l]) =>
            `<option value="${v}" ${cond.op === v ? 'selected' : ''}>${l}</option>`).join('');
        div.innerHTML = `
            <input id="scalecond-${cond.id}-left" class="nc-inp" placeholder="A ან A+1" value="${cond.left}"
                data-sci="${cond.id}" data-scf="left">
            <select class="cond-sel cond-op" data-sci="${cond.id}" data-scf="op">${opOpts}</select>
            <input id="scalecond-${cond.id}-right" class="nc-inp" placeholder="B ან B+1 ან 5" value="${cond.right}"
                data-sci="${cond.id}" data-scf="right">
            <button type="button" class="nc-del" onclick="removeScaleCond(${cond.id})">✕</button>
        `;
        container.appendChild(div);
    });

    syncScaleConfig();
}
document.getElementById('scaleCondRows').addEventListener('input', function (e) {
    const el = e.target, id = +el.dataset.sci, field = el.dataset.scf;
    if (!id || !field || field === 'op') return;
    updScaleCond(id, field, el.value.trim());
});
document.getElementById('scaleCondRows').addEventListener('change', function (e) {
    const el = e.target, id = +el.dataset.sci, field = el.dataset.scf;
    if (!id || field !== 'op') return;
    updScaleCond(id, 'op', el.value);
});
document.getElementById('scaleCondRows').addEventListener('focusin', function (e) {
    const el = e.target, id = +el.dataset.sci, field = el.dataset.scf;
    if (id && field && field !== 'op') scaleCondFocus = { id, field };
});

// Visual cap: the scale graphic's platforms are sized to comfortably hold 4
// items on a side — both the "+" button and the per-row count input enforce it.
const SCALE_SIDE_MAX = 4;
function scaleSideTotal(arr) {
    return arr.reduce((t, it) => t + Math.max(1, +it.count || 1), 0);
}
function addScalePanItem(side) {
    const arr = side === 'left' ? scaleLeft : scaleRight;
    if (scaleSideTotal(arr) >= SCALE_SIDE_MAX) return;
    // Default each new row to a symbol not already used in THIS pan (falling back
    // to any other symbol) instead of always "A" — otherwise every new row looks
    // identical until the admin notices the dropdown has other options too.
    const usedHere  = new Set(arr.map(it => it.key));
    const candidate = scaleSymbols.find(s => !usedHere.has(s.id)) || scaleSymbols[0] || null;
    arr.push({ key: candidate ? candidate.id : '', count: 1 });
    renderScalePan(side);
    syncScaleConfig();
}
function removeScalePanItem(side, i) {
    (side === 'left' ? scaleLeft : scaleRight).splice(i, 1);
    renderScalePan(side);
    syncScaleConfig();
}
function setScalePanItemCount(side, i, val) {
    const arr = side === 'left' ? scaleLeft : scaleRight;
    const otherTotal = scaleSideTotal(arr) - Math.max(1, +arr[i].count || 1);
    arr[i].count = Math.max(1, Math.min(SCALE_SIDE_MAX - otherTotal, +val || 1));
    renderScalePan(side);
    syncScaleConfig();
}
function renderScalePan(side) {
    const arr = side === 'left' ? scaleLeft : scaleRight;
    const el  = document.getElementById(side === 'left' ? 'scaleLeftRows' : 'scaleRightRows');
    const arrName = side === 'left' ? 'scaleLeft' : 'scaleRight';
    // Every defined symbol is selectable here — including the current answer
    // symbol; updateScaleAtHint() warns instead of hiding it, since silently
    // excluding options is what made it look like "only one symbol is choosable"
    // before.
    el.innerHTML = arr.map((it, i) => `
        <div style="display:flex;gap:4px;align-items:center;margin-bottom:4px;">
            <select class="nc-inp" style="flex:1;" onchange="${arrName}[${i}].key=this.value; syncScaleConfig();">
                ${scaleSymbols.map((s2) => `<option value="${s2.id}" ${s2.id === it.key ? 'selected' : ''}>${scaleSymbolLabel(scaleSymbols.indexOf(s2))} ${s2.kind === 'emoji' ? s2.symbol : '(წონა)'}</option>`).join('')}
            </select>
            <input type="number" class="nc-inp" style="width:56px;" min="1" max="${SCALE_SIDE_MAX}" value="${it.count}"
                onchange="setScalePanItemCount('${side}', ${i}, this.value)">
            <button type="button" class="nc-del" onclick="removeScalePanItem('${side}', ${i})">✕</button>
        </div>
    `).join('');
    const total   = scaleSideTotal(arr);
    const addBtn  = document.getElementById(side === 'left' ? 'scaleLeftAddBtn' : 'scaleRightAddBtn');
    const capNote = document.getElementById(side === 'left' ? 'scaleLeftCap' : 'scaleRightCap');
    if (addBtn)  addBtn.disabled = total >= SCALE_SIDE_MAX;
    if (capNote) capNote.textContent = total >= SCALE_SIDE_MAX ? `მაქს. ${SCALE_SIDE_MAX} მიღწეულია` : `${total}/${SCALE_SIDE_MAX}`;
    updateScaleAtHint();
}
// The answer symbol is no longer admin-picked — it's auto-chosen at generation
// time from ANY defined symbol, on a pan or not (see genPreviewScale() /
// ScaleService::build()). This returns that eligible candidate pool: a
// weight-kind symbol is always eligible; an emoji-kind one only when a custom
// template_text is present (its weight is never shown directly, so without a
// hand-written relationship there'd be no way for a child to solve it).
function scaleEligibleCandidates() {
    const hasCustomPrompt = !!document.getElementById('scaleTemplateText').value.trim();
    return scaleSymbols.filter(s => s.kind === 'weight' || hasCustomPrompt);
}
function updateScaleAtHint() {
    const hint = document.getElementById('scaleAtHint');
    if (!hint) return;
    const eligible = scaleEligibleCandidates();

    if (scaleSymbols.length && !eligible.length) {
        hint.innerHTML = '<span style="color:#ef4444;">⚠ ყველა სიმბოლო ემოჯი-ტიპისაა — დაწერეთ კითხვის ტექსტი ზემოთ, თორემ პასუხისთვის ვერც ერთი ვერ გამოდგება</span>';
    } else if (document.getElementById('answerTypeInput').value !== 'numeric_input') {
        // MC distractors draw from every emoji-kind symbol (pan-placed ones
        // included) — if the template only has this one emoji defined at all,
        // options will all be the same emoji with different counts.
        const emojiCount = scaleSymbols.filter(s => s.kind === 'emoji').length;
        hint.innerHTML = emojiCount === 1
            ? '<span style="color:#b45309;">ℹ️ მხოლოდ ერთი ემოჯი-სიმბოლოა განსაზღვრული — თუ ემოჯი გაიმარჯვებს პასუხად, ვარიანტებში მხოლოდ ამ ერთის სხვადასხვა რაოდენობა გამოჩნდება</span>'
            : '';
    } else {
        hint.innerHTML = '';
    }
}
function setScaleAnswerMode(mode) {
    document.getElementById('answerTypeInput').value = mode === 'numeric_input' ? 'numeric_input' : 'numeric';
    document.getElementById('scaleAtInput').classList.toggle('at-sel', mode === 'numeric_input');
    document.getElementById('scaleAtMc').classList.toggle('at-sel', mode !== 'numeric_input');
    syncScaleConfig();
    previewDebounce();
}
function syncScaleConfig() {
    const symbols = {};
    scaleSymbols.forEach(s => {
        symbols[s.id] = s.kind === 'emoji'
            ? { kind: 'emoji', symbol: s.symbol || '❓', min: +s.min || 1, max: +s.max || 9 }
            : { kind: 'weight', min: +s.min || 1, max: +s.max || 9 };
    });
    const left  = scaleLeft.map(it  => ({ key: it.key, count: Math.max(1, +it.count || 1) }));
    const right = scaleRight.map(it => ({ key: it.key, count: Math.max(1, +it.count || 1) }));
    const countMin = Math.max(1, parseInt(document.getElementById('scaleCountMin').value) || 1);
    const countMax = Math.max(countMin, parseInt(document.getElementById('scaleCountMax').value) || 6);

    document.getElementById('numConfigJson').value = JSON.stringify({
        symbols, left, right,
        count_min: countMin, count_max: countMax,
    });

    document.getElementById('conditionsJson').value = JSON.stringify(
        scaleConditions.map(c => ({ left: c.left, op: c.op, right: c.right }))
    );

    // Distractor range only matters when a weight-kind candidate could win as
    // the answer in MC mode — an emoji-kind MC answer builds its own
    // "{count} {emoji}" wrong options via ScaleService::buildEmojiOptions()
    // instead, which doesn't consult this.
    const isMc = document.getElementById('answerTypeInput').value !== 'numeric_input';
    const showDist = isMc && scaleEligibleCandidates().some(s => s.kind === 'weight');
    document.getElementById('scaleDistSection').style.display = showDist ? '' : 'none';
    if (showDist) {
        const dMin = Math.max(1, parseInt(document.getElementById('scaleDistMin').value) || 1);
        const dMax = Math.max(dMin, parseInt(document.getElementById('scaleDistMax').value) || 5);
        document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax });
    } else {
        document.getElementById('distractorsJson').value = '';
    }

    updateScaleAtHint();
    if (document.getElementById('qtInput').value === 'scale') genPreviewScale();
}
function restoreScaleConfig(numCfg, answerType, distractors, conditionsArr) {
    scaleSymbols = Object.entries(numCfg.symbols || {}).map(([key, conf]) => ({
        id: key, kind: conf.kind || 'weight', symbol: conf.symbol || SCALE_EMOJI_POOL[0], min: conf.min ?? 1, max: conf.max ?? 9,
    }));
    scaleLeft  = (numCfg.left  || []).map(it => ({ key: it.key, count: it.count || 1 }));
    scaleRight = (numCfg.right || []).map(it => ({ key: it.key, count: it.count || 1 }));
    scaleConditions = (Array.isArray(conditionsArr) ? conditionsArr : []).map(c => ({
        id: scaleCondIdSeq++, left: c.left || '', op: c.op || '>', right: c.right || '',
    }));
    document.getElementById('scaleTemplateText').value = document.getElementById('templateText').value || '';
    document.getElementById('scaleCountMin').value = numCfg.count_min || 1;
    document.getElementById('scaleCountMax').value = numCfg.count_max || 6;
    document.getElementById('scaleDistMin').value = distractors?.min || 1;
    document.getElementById('scaleDistMax').value = distractors?.max || 5;
    renderScaleSymbols();
    renderScalePan('left');
    renderScalePan('right');
    renderScaleConds();
    setScaleAnswerMode(answerType === 'numeric_input' ? 'numeric_input' : 'numeric');
}

/**
 * Client-side mirror of ScaleService::build() (PHP) — same retry-until-solvable
 * algorithm, duplicated here so the live preview doesn't need a server round trip.
 * See QuestionTemplate.php's parity convention note for why this duplication is
 * intentional: any change to the PHP generator must be mirrored here too.
 */
function genPreviewScale() {
    let cfg;
    try { cfg = JSON.parse(document.getElementById('numConfigJson').value); } catch (e) { cfg = null; }
    if (!cfg || !cfg.symbols || !Object.keys(cfg.symbols).length) {
        document.getElementById('prevQ').innerHTML = '<span style="color:#94a3b8;font-size:0.72rem;">სიმბოლოები და პინები შეავსეთ preview-სთვის</span>';
        document.getElementById('prevOpts').innerHTML = '';
        document.getElementById('prevHint').innerHTML = '';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        document.getElementById('prevWarn').innerHTML = '';
        return;
    }

    const symbols = cfg.symbols, left = cfg.left || [], right = cfg.right || [];
    const countMin = cfg.count_min || 1, countMax = cfg.count_max || 6;

    // Letters (A, B, C...) in symbol save order — same positional labels
    // scaleSymbolLabel() shows in the admin UI, used both for the conditions
    // check below and for {{A}}-style substitution into the prompt.
    const letterMap = {};
    Object.keys(symbols).forEach((key, i) => { letterMap[String.fromCharCode(65 + i)] = key; });

    // The answer symbol is auto-chosen (mirrors ScaleService::build()), not
    // admin-picked: ANY defined symbol is a candidate, on a pan or not — an
    // emoji-kind one only when a custom template_text is present (its weight
    // is never shown directly, so without a hand-written relationship there'd
    // be no way for a child to determine it).
    const hasCustomPrompt = !!document.getElementById('templateText').value.trim();
    const candidates = Object.keys(symbols).filter(key => {
        const kind = symbols[key].kind || 'emoji';
        return kind === 'weight' || hasCustomPrompt;
    });
    if (!candidates.length) {
        document.getElementById('prevQ').innerHTML = '';
        document.getElementById('prevOpts').innerHTML = '';
        document.getElementById('prevHint').innerHTML = '';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        document.getElementById('prevWarn').innerHTML = '<span style="color:#f87171;">⚠ საჭიროა მინიმუმ 1 წონა-ტიპის სიმბოლო, ან დაწერეთ კითხვის ტექსტი, რომ ემოჯი-ტიპის სიმბოლოც დასაშვები გახდეს</span>';
        return;
    }
    const countOnSidePv = (arr, key) => arr.filter(it => it.key === key).reduce((t, it) => t + Math.max(1, it.count || 1), 0);

    let result = null;
    for (let attempt = 0; attempt < 300 && !result; attempt++) {
        // Every symbol gets a provisional rolled weight — used directly for an
        // emoji-kind symbol (always), and as a weight-kind symbol's own pan
        // value for whenever it ISN'T the candidate being tried below (when it
        // IS, this provisional roll is set aside in favor of solving for its
        // true value).
        const weights = {};
        Object.entries(symbols).forEach(([key, conf]) => {
            const min = conf.min || 1, max = Math.max(min, conf.max || 9);
            weights[key] = min + Math.floor(Math.random() * (max - min + 1));
        });
        const sumSide = items => items.reduce((t, it) => t + (weights[it.key] || 0) * Math.max(1, it.count || 1), 0);
        const totalLeft = sumSide(left), totalRight = sumSide(right);
        const deficit = totalLeft - totalRight;
        if (deficit === 0) continue;
        const blankSide = deficit > 0 ? 'right' : 'left';
        const need = Math.abs(deficit);

        const pool = [...candidates].sort(() => Math.random() - 0.5);
        for (const answerKey of pool) {
            const conf = symbols[answerKey];
            const kind = conf.kind || 'emoji';
            const cLeft = countOnSidePv(left, answerKey), cRight = countOnSidePv(right, answerKey);
            const coef = cLeft - cRight;
            let answerValue, inline = false, thisBlankSide = blankSide;

            if (kind === 'weight' && coef !== 0 && (cLeft === 0 || cRight === 0)) {
                const knownLeft = totalLeft - weights[answerKey] * cLeft;
                const knownRight = totalRight - weights[answerKey] * cRight;
                const diff = knownRight - knownLeft;
                if (diff % coef !== 0) continue;
                const x = diff / coef;
                const min = conf.min || 1, max = Math.max(min, conf.max || 20);
                if (x < min || x > max) continue;
                answerValue = x;
                inline = true;
                thisBlankSide = null;
            } else if (kind === 'weight' && coef !== 0) {
                continue; // on both pans with different counts — "?" would show on both sides, skip
            } else if (kind === 'weight') {
                const min = conf.min || 1, max = Math.max(min, conf.max || 20);
                if (need < min || need > max) continue;
                answerValue = need;
            } else {
                const aw = weights[answerKey] || 0;
                if (aw <= 0 || need % aw !== 0) continue;
                const cnt = Math.floor(need / aw);
                if (cnt < countMin || cnt > countMax) continue;
                answerValue = cnt;
            }

            // Conditions must be checked against each symbol's REAL final value,
            // not the provisional roll — a weight-kind winner's provisional roll
            // was never actually used/shown, so it's swapped out here.
            if (scaleConditions.length) {
                const checkWeights = kind === 'weight' ? { ...weights, [answerKey]: answerValue } : weights;
                const letterWeights = {};
                Object.entries(letterMap).forEach(([letter, key]) => { letterWeights[letter] = checkWeights[key] || 0; });
                if (!evalScaleConditions(letterWeights)) continue;
            }

            result = { weights, blankSide: thisBlankSide, answerKey, answerKind: kind, answerValue, inline };
            break;
        }
    }

    if (!result) {
        document.getElementById('prevQ').innerHTML = '';
        document.getElementById('prevOpts').innerHTML = '';
        document.getElementById('prevHint').innerHTML = '';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        document.getElementById('prevWarn').innerHTML = '<span style="color:#f87171;">⚠ ამ კონფიგურაციით ამოხსნადი კომბინაცია ვერ მოიძებნა — შეამოწმეთ წონების დიაპაზონები</span>';
        return;
    }
    document.getElementById('prevWarn').innerHTML = '';

    const answerKey  = result.answerKey;
    const answerKind = result.answerKind;
    const answerConf = symbols[answerKey];

    const isMc = document.getElementById('answerTypeInput').value !== 'numeric_input';
    let options = null, correctAnswer;
    if (answerKind === 'weight') {
        correctAnswer = String(result.answerValue);
        if (isMc) {
            // Mirrors QuestionTemplate::buildMcOptions() — same admin-configured
            // ± range (#scaleDistMin/#scaleDistMax) the backend uses via `distractors`.
            const dMin = Math.max(1, parseInt(document.getElementById('scaleDistMin').value) || 1);
            const dMax = Math.max(dMin, parseInt(document.getElementById('scaleDistMax').value) || 5);
            const wrong = [];
            let attempts = 0;
            while (wrong.length < 3 && attempts < 100) {
                attempts++;
                const delta = dMin + Math.floor(Math.random() * (dMax - dMin + 1));
                const cand  = result.answerValue + (Math.random() < 0.5 ? -1 : 1) * delta;
                if (cand > 0 && cand !== result.answerValue && !wrong.includes(cand)) wrong.push(cand);
            }
            options = [result.answerValue, ...wrong].map(String).sort(() => Math.random() - 0.5);
        }
    } else {
        // Mirrors ScaleService::buildEmojiOptions() — each option is a symbol
        // repeated N times with no digit (e.g. "⭐⭐", never "2⭐"). Distractors
        // are drawn from every emoji-kind symbol, pan-placed ones included (the
        // rolled weights are already known at this point, no reason not to reuse
        // them), ranked by how close that candidate's TOTAL weight comes to the
        // actual deficit, so wrong answers look like genuine near-misses —
        // different symbols preferred over different counts of the same one, as
        // long as there are enough of them.
        const emojiLabel = (sym, n) => sym.repeat(Math.max(1, n));
        const answerSym = answerConf.symbol || '❓';
        const label = emojiLabel(answerSym, result.answerValue);
        correctAnswer = label;
        if (isMc) {
            const need = result.answerValue * Math.max(1, result.weights[answerKey] || 1);
            let pool = Object.keys(symbols).filter(k => (symbols[k].kind || 'emoji') === 'emoji');
            if (!pool.length) pool = [answerKey];

            const ranked = [];
            pool.forEach(key => {
                const w = Math.max(1, result.weights[key] || 1);
                for (let cnt = countMin; cnt <= countMax; cnt++) {
                    const lbl = emojiLabel(symbols[key].symbol || '❓', cnt);
                    if (lbl === label) continue;
                    const total = cnt * w;
                    // A different symbol can share the same rolled weight as the
                    // answer symbol — if this candidate's TOTAL also equals the
                    // actual deficit, it would genuinely balance the scale too
                    // (a second correct answer in disguise), so exclude it, not
                    // just the one matching the correct LABEL string.
                    if (total === need) continue;
                    ranked.push({ label: lbl, key, dev: Math.abs(total - need) });
                }
            });
            ranked.sort((a, b) => a.dev - b.dev);

            const wrong = [];
            const usedKeys = [];
            for (const cand of ranked) {
                if (wrong.length >= 3) break;
                if (usedKeys.includes(cand.key) || wrong.includes(cand.label)) continue;
                wrong.push(cand.label);
                usedKeys.push(cand.key);
            }
            for (const cand of ranked) {
                if (wrong.length >= 3) break;
                if (wrong.includes(cand.label)) continue;
                wrong.push(cand.label);
            }
            let delta = 1;
            while (wrong.length < 3) {
                const cand = emojiLabel(answerSym, result.answerValue + delta);
                if (cand !== label && !wrong.includes(cand)) wrong.push(cand);
                delta++;
            }
            options = [label, ...wrong].sort(() => Math.random() - 0.5);
        }
    }

    // Every occurrence gets its OWN chip (never a single text blob with the
    // symbol repeated N times) — lets the nowrap row size each one individually
    // instead of risking the browser line-wrapping a multi-emoji string.
    const panHtml = (items, blankHere) => {
        let html = items.map(it => {
            const conf  = symbols[it.key] || {};
            const count = Math.max(1, it.count || 1);
            // Weight-kind answer symbol placed inline on a pan (result.inline,
            // see above) — its value is never shown directly, so render it as
            // the same dashed "?" plate the separately-appended blank uses.
            if (result.inline && it.key === answerKey) {
                return Array.from({ length: count }, () =>
                    `<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.75rem;color:#94a3b8;flex-shrink:0;">?</div>`
                ).join('');
            }
            if ((conf.kind || 'emoji') === 'weight') {
                return Array.from({ length: count }, () =>
                    `<div style="width:26px;height:26px;border-radius:6px;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.7rem;flex-shrink:0;">${result.weights[it.key]}</div>`
                ).join('');
            }
            return Array.from({ length: count }, () =>
                `<div style="font-size:1.15rem;line-height:1;flex-shrink:0;">${conf.symbol || '❓'}</div>`
            ).join('');
        }).join('');
        if (blankHere) {
            // MC mode never names the answer symbol in advance (that's the whole
            // point of offering 4 "{emoji}×N" choices) — show a bare "?" there
            // too, same as weight-kind, instead of leaking which emoji it is.
            const revealSymbol = !isMc;
            html += answerKind === 'weight'
                ? `<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:800;font-size:0.75rem;flex-shrink:0;">?</div>`
                : (revealSymbol
                    ? `<div style="font-size:1.15rem;line-height:1;border:2.5px dashed #94a3b8;border-radius:7px;padding:2px 4px;text-align:center;flex-shrink:0;">${answerConf.symbol || '❓'}<span style="font-size:0.5rem;color:#94a3b8;display:block;line-height:1;">?</span></div>`
                    : `<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:800;font-size:0.95rem;flex-shrink:0;">?</div>`);
        }
        return html;
    };

    const defaultPrompt = isMc
        ? 'რომელი ვარიანტი დააბალანსებს სასწორს? ⚖️'
        : (answerKind === 'weight'
            ? 'რა წონა აკლია, რომ სასწორი დაბალანსდეს? ⚖️'
            : `რამდენი ${answerConf.symbol || ''} დასამატებელია, რომ სასწორი დაბალანსდეს? ⚖️`);
    let prompt = document.getElementById('templateText').value.trim() || defaultPrompt;

    // {{A}}, {{B}}... resolve to that symbol's rolled value — the number for a
    // weight-kind symbol (the just-solved answer value if it won this round —
    // checked FIRST, since every symbol now gets a throwaway provisional roll
    // regardless, so `??` alone would wrongly prefer that over the real solved
    // value — or its own rolled weight otherwise), or the emoji character for
    // an emoji-kind symbol (never its hidden weight). Mirrors generateScale() (PHP).
    // letterDebug collects the same letters' actual NUMERIC values (admin-only,
    // shown below the preview — never substituted into the child-facing prompt
    // for an emoji symbol) so a condition like "A > B" can be checked by eye.
    const letterDebug = [];
    Object.entries(letterMap).forEach(([letter, key]) => {
        const conf = symbols[key] || {};
        const isWeight = (conf.kind || 'emoji') === 'weight';
        // Only a WEIGHT-kind winner's provisional roll is thrown away in favor
        // of result.answerValue (the just-solved number) -- for an EMOJI-kind
        // winner, answerValue is the ADD-ON COUNT (e.g. "2 more 🎵"), a totally
        // different quantity from its hidden weight, so it must never be
        // substituted here even when that symbol happens to be the answer.
        const numericVal = (isWeight && key === answerKey) ? result.answerValue : (result.weights[key] ?? null);
        const val = isWeight ? (numericVal ?? '?') : (conf.symbol || '❓');
        prompt = prompt.split(`{{${letter}}}`).join(String(val));
        letterDebug.push(isWeight ? `${letter}=${numericVal ?? '?'}` : `${letter}=${conf.symbol || '❓'}(${numericVal ?? '?'})`);
    });

    document.getElementById('prevQ').innerHTML = '⚖️ ' + escHtmlFormLp(prompt);
    document.getElementById('prevHint').innerHTML = escHtmlFormLp(document.getElementById('hintText').value || '');

    // The scale graphic + condition hint span both preview-opts grid columns;
    // the actual options (or numeric_input answer) render with the exact same
    // .preview-opt/.c classes every other question type's preview uses.
    let optsHtml = `<div style="grid-column:1/-1;max-width:280px;margin:4px auto 0;">
        <div style="display:flex;justify-content:space-between;gap:6px;padding:0 6px;margin-bottom:2px;">
            <div style="flex:1;display:flex;flex-wrap:nowrap;gap:2px;align-items:flex-end;justify-content:center;min-height:34px;overflow:visible;">${panHtml(left, result.blankSide === 'left')}</div>
            <div style="flex:1;display:flex;flex-wrap:nowrap;gap:2px;align-items:flex-end;justify-content:center;min-height:34px;overflow:visible;">${panHtml(right, result.blankSide === 'right')}</div>
        </div>
        ${scaleSvgHtml()}
    </div>`;
    if (options) {
        optsHtml += options.map(o => `<div class="preview-opt ${o === correctAnswer ? 'c' : ''}">${escHtmlFormLp(o)}</div>`).join('');
    } else {
        optsHtml += `<div class="preview-opt c" style="grid-column:1/-1;cursor:default;">✍️ ${escHtmlFormLp(correctAnswer)}</div>`;
    }
    document.getElementById('prevOpts').innerHTML = optsHtml;
    document.getElementById('prevFormula').innerHTML = '';
    document.getElementById('prevVars').innerHTML = escHtmlFormLp(letterDebug.join(' · '));
}
function escHtmlFormLp(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// init on page load
(function() {
    const qt = document.getElementById('qtInput').value;
    applyQType(qt);
})();

// ── Difficulty
function setDiff(n) {
    document.getElementById('diffInput').value = n;
    document.querySelectorAll('#diffRow .diff-btn').forEach((b, i) => b.classList.toggle('sel', i + 1 === n));
    const oi = document.getElementById('isOlympiadInput');
    if (oi.value === '1') {
        oi.value = '0';
        document.getElementById('olyBtn').classList.remove('sel');
    }
}

// ── Olympiad toggle — mutually exclusive with the normal 1-5 difficulty picker
function toggleOlympiad() {
    const input = document.getElementById('isOlympiadInput');
    const active = input.value !== '1';
    input.value = active ? '1' : '0';
    document.getElementById('olyBtn').classList.toggle('sel', active);
    document.querySelectorAll('#diffRow .diff-btn').forEach(b => {
        b.disabled = active;
        b.style.opacity = active ? '0.35' : '';
        b.style.pointerEvents = active ? 'none' : '';
    });
}

// ── Active/inactive — inactive templates are skipped by test generation entirely
function toggleActive() {
    const input = document.getElementById('isActiveInput');
    const active = input.value !== '1';
    input.value = active ? '1' : '0';
    document.getElementById('activeBtn').classList.toggle('sel', active);
}

// ── Insert at cursor (template text)
function insertVar(name) {
    const ta = document.getElementById('templateText');
    const s = ta.selectionStart, e = ta.selectionEnd;
    const ins = OB + name + CB;
    ta.value = ta.value.slice(0, s) + ins + ta.value.slice(e);
    ta.selectionStart = ta.selectionEnd = s + ins.length;
    ta.focus(); onTemplateInput();
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
    const isTxt   = type === 'text';
    const isInput = type === 'numeric_input';
    document.getElementById('numAnsUi').style.display = isTxt ? 'none' : '';
    document.getElementById('txtAnsUi').style.display = isTxt ? '' : 'none';
    document.getElementById('distractorSection').style.display = isInput ? 'none' : '';
    document.getElementById('atBtnNum').classList.toggle('at-sel', type === 'numeric');
    document.getElementById('atBtnInput').classList.toggle('at-sel', isInput);
    document.getElementById('atBtnTxt').classList.toggle('at-sel', isTxt);
    syncAll(); previewDebounce();
}
function toggleTextOpt(el) {
    el.classList.toggle('sel');
    syncAll(); previewDebounce();
}

// ── Hand-typed distractor templates (e.g. a combined "{{N1}} და {{N2}}"
// string) — kept in their own array rather than as extra .text-opt-chip
// elements, since renderTextOptChips() fully rebuilds that chip list from
// the template's variable set (on theme change etc.) and would otherwise
// wipe out a custom one that isn't among those variables.
let textCustomOpts = [];
function addTextOptCustom() {
    const inp = document.getElementById('textOptCustomInput');
    const val = inp.value.trim();
    if (!val || textCustomOpts.includes(val)) return;
    textCustomOpts.push(val);
    inp.value = '';
    renderTextOptCustomChips();
    syncAll(); previewDebounce();
}
function removeTextOptCustom(idx) {
    textCustomOpts.splice(idx, 1);
    renderTextOptCustomChips();
    syncAll(); previewDebounce();
}
function renderTextOptCustomChips() {
    const el = document.getElementById('textOptCustomChips');
    if (!el) return;
    el.innerHTML = textCustomOpts.map((v, i) =>
        `<span class="chip chip-txt sel">${escHtmlFormLp(v)} <a href="javascript:void(0)" onclick="removeTextOptCustom(${i})" style="color:#ef4444;font-weight:800;margin-left:3px;">×</a></span>`
    ).join('');
}

// ── Text answer: plain-variable dropdown vs conditional formula ("A>B?X:Y").
// The hidden `correct_formula` field carries whichever mode is active — the
// backend's resolveTextFormula() treats a plain name and a conditional formula
// as two forms of the same field, so no separate column/flag is needed.
let textCondMode = false;
function toggleTextCondMode() {
    textCondMode = !textCondMode;
    document.getElementById('textCorrectVar').style.display = textCondMode ? 'none' : '';
    document.getElementById('textCondWrap').style.display = textCondMode ? '' : 'none';
    document.getElementById('textCondToggle').classList.toggle('on', textCondMode);
    document.getElementById('textCondToggle').textContent = textCondMode ? '🔗 ცვლადი' : '⚡ პირობითი';
    renderTextCondChips();
    syncAll(); previewDebounce();
}
function insertIntoTextCond(text) {
    const inp = document.getElementById('textCondFormula');
    const s = inp.selectionStart ?? inp.value.length;
    const e = inp.selectionEnd ?? inp.value.length;
    inp.value = inp.value.slice(0, s) + text + inp.value.slice(e);
    inp.selectionStart = inp.selectionEnd = s + text.length;
    inp.focus();
    syncAll(); previewDebounce();
}
function renderTextCondChips() {
    const bar = document.getElementById('textCondChips');
    if (!bar) return;
    bar.innerHTML = '';
    const numNames = ncRows.filter(r => r.name).map(r => r.name);
    const themeVarEls = document.querySelectorAll('.text-opt-chip');
    const themeNames = [...new Set([...themeVarEls].map(c => c.dataset.varname))];
    [...numNames, ...themeNames].forEach(n => {
        const c = document.createElement('span');
        c.className = 'cond-chip'; c.textContent = n;
        c.onclick = () => insertIntoTextCond(n); bar.appendChild(c);
    });
    ['>', '<', '>=', '<=', '==', '!=', '?', ':'].forEach(s => {
        const c = document.createElement('span');
        c.className = 'cond-chip cond-op-chip'; c.textContent = s;
        c.onclick = () => insertIntoTextCond(s); bar.appendChild(c);
    });
}

// ── Mirrors QuestionTemplate's findTopLevelChar/splitConditionExpr/resolveTextFormula
function stripWrappingParensJs(s) {
    while (s.length >= 2 && s[0] === '(' && s[s.length - 1] === ')') {
        let depth = 0, wrapsWhole = true;
        for (let i = 0; i < s.length; i++) {
            if (s[i] === '(') depth++;
            else if (s[i] === ')') {
                depth--;
                if (depth === 0 && i !== s.length - 1) { wrapsWhole = false; break; }
            }
        }
        if (!wrapsWhole) break;
        s = s.slice(1, -1).trim();
    }
    return s;
}
function findTopLevelCharJs(str, char) {
    let depth = 0;
    for (let i = 0; i < str.length; i++) {
        const c = str[i];
        if (c === '(') depth++;
        else if (c === ')') depth--;
        else if (depth === 0 && c === char) return i;
    }
    return null;
}
function splitConditionExprJs(cond) {
    for (const op of ['>=', '<=', '==', '!=']) {
        const pos = cond.indexOf(op);
        if (pos !== -1) return [cond.slice(0, pos).trim(), op, cond.slice(pos + 2).trim()];
    }
    for (const op of ['>', '<']) {
        const pos = cond.indexOf(op);
        if (pos !== -1) return [cond.slice(0, pos).trim(), op, cond.slice(pos + 1).trim()];
    }
    return null;
}
function condTrueJs(left, op, right, numVars) {
    const l = evalExpr(left, numVars), r = evalExpr(right, numVars);
    switch (op) {
        case '>':  return l > r;
        case '<':  return l < r;
        case '>=': return l >= r;
        case '<=': return l <= r;
        case '==': return l === r;
        case '!=': return l !== r;
        default:   return false;
    }
}
function resolveTextFormulaJs(formula, numVars, themeVars) {
    formula = stripWrappingParensJs((formula || '').trim());
    const vars = { ...numVars, ...themeVars };
    const qPos = findTopLevelCharJs(formula, '?');
    if (qPos === null) {
        if (formula in vars) return vars[formula];
        if (formula.includes('{{')) {
            return formula.replace(/\{\{(\w+)\}\}/g, (m, name) => (name in vars) ? vars[name] : m);
        }
        return formula || '?';
    }

    const condStr = formula.slice(0, qPos).trim();
    const restStr = formula.slice(qPos + 1);
    const cPos = findTopLevelCharJs(restStr, ':');
    if (cPos === null) return '?';
    const ifTrueStr  = restStr.slice(0, cPos).trim();
    const ifFalseStr = restStr.slice(cPos + 1).trim();

    const parts = splitConditionExprJs(condStr);
    if (!parts) return '?';
    const [left, op, right] = parts;
    const chosen = condTrueJs(left, op, right, numVars) ? ifTrueStr : ifFalseStr;
    return resolveTextFormulaJs(chosen, numVars, themeVars);
}

// ── Insert function at cursor — places cursor inside parens
function insertFormulaFn(name) {
    const inp = document.getElementById('correctFormula');
    const s = inp.selectionStart, e = inp.selectionEnd;
    const ins = name + '()';
    inp.value = inp.value.slice(0, s) + ins + inp.value.slice(e);
    // cursor inside the parens
    inp.selectionStart = inp.selectionEnd = s + ins.length - 1;
    inp.focus();
    previewDebounce();
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

// ── None-correct toggle (numeric mode)
let noneCorrect = false;
function toggleNoneCorrect() {
    noneCorrect = !noneCorrect;
    const btn = document.getElementById('noneCorrectBtn');
    if (btn) {
        btn.classList.toggle('at-sel', noneCorrect);
        btn.textContent = (noneCorrect ? '☑' : '☐') + ' არცერთი სწორეა';
    }
    syncAll(); previewDebounce();
}

// ── Auto-build constructor (random operators/parens re-rolled server-side on every generation)
function toggleAutoBuild() {
    const on = document.getElementById('autoBuildEnabled').checked;
    document.getElementById('autoBuildFields').style.display = on ? '' : 'none';
    // template_text stays editable — admins can wrap the auto-generated equation in
    // their own instruction text via the {{EXPR}} placeholder (see exprHint below).
    document.getElementById('correctFormula').readOnly = on;
    document.getElementById('exprHint').style.display = on ? '' : 'none';
    document.getElementById('correctFormula').style.background = on ? '#f8fafc' : '';
    if (!on) previewDebounce();
    syncAll(); previewDebounce();
}
function applyAutoBuild() {
    const count = parseInt(document.getElementById('autoVarCount').value) || 2;
    ncRows = [];
    for (let i = 1; i <= count; i++) addNcRow('N' + i, 1, 9, 1);
    renderNcRows();
    syncAll(); previewDebounce();
}
function applyAutoPreviewText(ncObj, autoCfg) {
    const names = Object.keys(ncObj).filter(k => !k.startsWith('_'));
    if (!names.length) return;
    const op = autoCfg.operators[0];
    const formulaOp = op === '×' ? '*' : (op === '÷' ? '/' : op);
    let text = names.map(n => OB + n + CB).join(' ' + op + ' ') + ' = ?';
    let formula = names.join(formulaOp);
    if (autoCfg.parens && names.length >= 3) {
        text = '(' + OB + names[0] + CB + ' ' + op + ' ' + OB + names[1] + CB + ')' +
            names.slice(2).map(n => ' ' + op + ' ' + OB + n + CB).join('');
        formula = '(' + names[0] + formulaOp + names[1] + ')' + names.slice(2).map(n => formulaOp + n).join('');
    }
    // Only fill template_text when it's empty — admins can type their own
    // instruction text (with an {{EXPR}} placeholder) and it must never be
    // clobbered by this cosmetic default.
    if (!document.getElementById('templateText').value.trim()) {
        document.getElementById('templateText').value = text;
    }
    document.getElementById('correctFormula').value = formula;
}

// ── num_config rows
let ncRows  = [];
let ncIdSeq = 1;

// Georgian labels for the "operator" variable type's underlying math symbol —
// mirrors QuestionTemplate::OPERATOR_LABELS exactly.
const OPERATOR_LABELS = { '+': 'ით მეტი', '-': 'ით ნაკლები', '*': 'ჯერ მეტი', '/': 'ჯერ ნაკლები' };
const NC_MODE_CYCLE = ['random', 'derived', 'operator', 'conditional'];

function addNcRow(name = '', min = 1, max = 9, step = 1, mode = 'random', derived = '', opOptions = [], conditional = '') {
    if (!name) {
        const used = new Set(ncRows.map(r => r.name));
        let i = 1;
        while (used.has('N' + i)) i++;
        name = 'N' + i;
    }
    ncRows.push({ id: ncIdSeq++, name, min, max, step, mode, derived, opOptions: opOptions.slice(), conditional });
    renderNcRows();
}
function removeNcRow(id) {
    ncRows = ncRows.filter(r => r.id !== id);
    renderNcRows();
}
function toggleNcMode(id) {
    const r = ncRows.find(r => r.id === id);
    if (!r) return;
    const idx = NC_MODE_CYCLE.indexOf(r.mode);
    r.mode = NC_MODE_CYCLE[(idx + 1) % NC_MODE_CYCLE.length];
    // No default-checked box on switching to operator mode — admin must pick
    // explicitly (matches the auto-constructor's operator checkboxes, which also
    // start unchecked). Generation still falls back to '+' as a last resort if
    // saved with none checked (same as the auto-constructor's own fallback).
    renderNcRows();
    previewDebounce();
}
function toggleOpOption(id, symbol, checked) {
    const r = ncRows.find(r => r.id === id);
    if (!r) return;
    r.opOptions = r.opOptions || [];
    if (checked && !r.opOptions.includes(symbol)) r.opOptions.push(symbol);
    if (!checked) r.opOptions = r.opOptions.filter(s => s !== symbol);
    syncAll(); previewDebounce();
}
function renderNcRows() {
    const c = document.getElementById('ncRows');
    c.innerHTML = '';
    ncRows.forEach(row => {
        const div = document.createElement('div');
        div.className = 'nc-row';
        if (row.mode === 'derived') {
            div.innerHTML = `
            <input class="nc-inp nc-name" maxlength="6" placeholder="N1" value="${row.name}"
                data-nc="${row.id}" data-nc-f="name">
            <input class="nc-inp" style="grid-column: 2 / 5;" placeholder="მაგ. N1+3" value="${row.derived || ''}"
                data-nc="${row.id}" data-nc-f="derived">
            <button type="button" class="nc-toggle on" title="ოპერატორ ცვლადზე გადართვა" onclick="toggleNcMode(${row.id})">ƒ</button>
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
            `;
        } else if (row.mode === 'conditional') {
            div.innerHTML = `
            <input class="nc-inp nc-name" maxlength="6" placeholder="RELATION" value="${row.name}"
                data-nc="${row.id}" data-nc-f="name">
            <input class="nc-inp" style="grid-column: 2 / 5;" placeholder="მაგ. TOTAL1>TOTAL2?ჯერ მეტი:ჯერ ნაკლები" value="${row.conditional || ''}"
                data-nc="${row.id}" data-nc-f="conditional">
            <button type="button" class="nc-toggle on" title="შემთხვევით მნიშვნელობაზე გადართვა" onclick="toggleNcMode(${row.id})">⚖</button>
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
            `;
        } else if (row.mode === 'operator') {
            const opts = row.opOptions || [];
            const chk = (sym) => opts.includes(sym) ? 'checked' : '';
            div.innerHTML = `
            <input class="nc-inp nc-name" maxlength="6" placeholder="X" value="${row.name}"
                data-nc="${row.id}" data-nc-f="name">
            <div style="grid-column: 2 / 5;display:flex;gap:7px;flex-wrap:wrap;align-items:center;font-size:0.6rem;color:#374151;">
                <label style="display:flex;align-items:center;gap:3px;cursor:pointer;white-space:nowrap;">
                    <input type="checkbox" ${chk('+')} onchange="toggleOpOption(${row.id},'+',this.checked)"> ით მეტი</label>
                <label style="display:flex;align-items:center;gap:3px;cursor:pointer;white-space:nowrap;">
                    <input type="checkbox" ${chk('-')} onchange="toggleOpOption(${row.id},'-',this.checked)"> ით ნაკლები</label>
                <label style="display:flex;align-items:center;gap:3px;cursor:pointer;white-space:nowrap;">
                    <input type="checkbox" ${chk('*')} onchange="toggleOpOption(${row.id},'*',this.checked)"> ჯერ მეტი</label>
                <label style="display:flex;align-items:center;gap:3px;cursor:pointer;white-space:nowrap;">
                    <input type="checkbox" ${chk('/')} onchange="toggleOpOption(${row.id},'/',this.checked)"> ჯერ ნაკლები</label>
            </div>
            <button type="button" class="nc-toggle on" title="პირობით ცვლადზე გადართვა" onclick="toggleNcMode(${row.id})">±</button>
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
            `;
        } else {
            div.innerHTML = `
            <input class="nc-inp nc-name" maxlength="6" placeholder="N1" value="${row.name}"
                data-nc="${row.id}" data-nc-f="name">
            <input type="number" class="nc-inp" min="0" placeholder="1" value="${row.min}"
                data-nc="${row.id}" data-nc-f="min">
            <input type="number" class="nc-inp" min="0" placeholder="9" value="${row.max}"
                data-nc="${row.id}" data-nc-f="max">
            <input type="number" class="nc-inp" min="1" placeholder="1" value="${row.step}"
                title="ნაბიჯი: 1=ნებ, 2=ლუწი, 5=მრ5" data-nc="${row.id}" data-nc-f="step">
            <button type="button" class="nc-toggle" title="გამოთვლილ ცვლადზე გადართვა (ƒ → ოპერატორი → პირობითი)" onclick="toggleNcMode(${row.id})">ƒ</button>
            <button type="button" class="nc-del" onclick="removeNcRow(${row.id})">✕</button>
            `;
        }
        c.appendChild(div);
    });
    syncAll();
    renderConds();
    // Keeps the text-answer correct/distractor var pickers' "რიცხვითი
    // ცვლადები" group in sync whenever a numeric row is added, removed or
    // renamed -- not just on a theme change (same reasoning as renderConds()
    // just above, which already does this for the generic conditions builder).
    renderTextCorrectVar(lastThemeData);
    renderTextOptChips(lastThemeData);
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
const OP_LABELS = {'>':'> მეტია','<':'< ნაკლებია','>=':'≥ მეტი/ტოლი','<=':'≤ ნაკ/ტოლი','==':'= ტოლია','!=':'≠ არ ტოლდება','%0':'÷ იყოფა','!%0':'÷ არ იყოფა'};
const OP_SYMS   = ['+','-','*','/','%','(', ')'];

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
            <input id="cond-${cond.id}-left" class="nc-inp" placeholder="N1 ან N1%10" value="${cond.left}"
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
    const _qt = document.getElementById('qtInput').value;
    if (_qt === 'pyramid')   { syncPyrConfig();  return; }
    if (_qt === 'code')      { syncCodeConfig();  return; }
    if (_qt === 'crossword') { syncCwConfig();    return; }
    if (_qt === 'scale')     { syncScaleConfig(); return; }
    const ncObj = {};
    ncRows.forEach(r => {
        if (!r.name) return;
        if (r.mode === 'derived') {
            ncObj[r.name] = { derived: r.derived || '0' };
        } else if (r.mode === 'operator') {
            ncObj[r.name] = { operator: true, options: (r.opOptions && r.opOptions.length) ? r.opOptions : ['+'] };
        } else if (r.mode === 'conditional') {
            ncObj[r.name] = { conditional: r.conditional || '' };
        } else {
            ncObj[r.name] = { min: +r.min, max: +r.max, step: +r.step || 1 };
        }
    });
    const _uq = document.getElementById('uniqueNumVars');
    if (_uq && _uq.checked) ncObj['_unique'] = true;

    const autoEl = document.getElementById('autoBuildEnabled');
    const autoOn = autoEl && autoEl.checked;
    if (autoOn) {
        const aOps = [];
        if (document.getElementById('autoOpPlus').checked)  aOps.push('+');
        if (document.getElementById('autoOpMinus').checked) aOps.push('-');
        if (document.getElementById('autoOpMul').checked)   aOps.push('×');
        if (document.getElementById('autoOpDiv').checked)   aOps.push('÷');
        ncObj['_auto'] = {
            enabled: true,
            operators: aOps.length ? aOps : ['+'],
            parens: document.getElementById('autoParens').checked,
            no_negative_steps: document.getElementById('autoNoNegSteps').checked,
            mix_blank: document.getElementById('autoMixBlank').checked,
        };
        applyAutoPreviewText(ncObj, ncObj['_auto']);
    }
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
        document.getElementById('correctFormulaHidden').value = textCondMode
            ? document.getElementById('textCondFormula').value.trim()
            : document.getElementById('textCorrectVar').value;
        const optVars = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname).concat(textCustomOpts);
        const wrongCount = Math.max(1, parseInt(document.getElementById('textDistCount')?.value) || 3);
        document.getElementById('distractorsJson').value = JSON.stringify({ vars: optVars, count: wrongCount });
    } else {
        document.getElementById('correctFormulaHidden').value = document.getElementById('correctFormula').value;
        const dMin = +document.getElementById('distMin').value || 1;
        const dMax = +document.getElementById('distMax').value || 10;
        document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax, none_correct: noneCorrect || false });
    }
}

// The only function names evalExpr() (and correct_formula's own inline
// evaluator in genPreviewNumeric()) will call -- mirrors PHP's MATH_FNS
// constant exactly, so the admin's preview and the real backend generation
// accept identical formula syntax.
const MATH_FNS = ['round', 'floor', 'ceil', 'abs', 'min', 'max', 'pow', 'sqrt'];

// ── Condition evaluator (supports expressions like N2+N3, and the whitelisted
// math functions above, e.g. round(N1/2))
function evalExpr(expr, numVars) {
    let e = String(expr);
    Object.entries(numVars).forEach(([k, v]) => { e = e.replaceAll(k, String(v)); });
    // Any bare word surviving substitution must be a whitelisted function --
    // everything else (an unresolved variable, a typo'd/foreign identifier)
    // is dropped before Function() ever sees it. Mirrors PHP's evalExpr().
    e = e.replace(/[a-zA-Z_][a-zA-Z0-9_]*/g, m => MATH_FNS.includes(m) ? m : '');
    e = e.replace(/[^0-9a-zA-Z_+\-*/(),\s%.]/g, '');
    const round = Math.round, floor = Math.floor, ceil = Math.ceil, abs = Math.abs,
          min = Math.min, max = Math.max, pow = Math.pow, sqrt = Math.sqrt;
    try {
        const r = Function('round','floor','ceil','abs','min','max','pow','sqrt',
            'return (' + e + ')')(round, floor, ceil, abs, min, max, pow, sqrt);
        return Number.isFinite(r) ? Math.floor(r) : 0;
    } catch(_) { return 0; }
}
// Scale's own conditions check — same evalExpr() engine, against scaleConditions
// (letter-named: A, B, C...) instead of the generic numeric-vars conditions array.
function evalScaleConditions(letterVars) {
    return scaleConditions.every(c => {
        const l = evalExpr(c.left  ?? '0', letterVars);
        const r = evalExpr(c.right ?? '0', letterVars);
        switch (c.op) {
            case '>':   return l > r;
            case '<':   return l < r;
            case '>=':  return l >= r;
            case '<=':  return l <= r;
            case '==':  return l === r;
            case '!=':  return l !== r;
            case '%0':  return r !== 0 && l % r === 0;
            case '!%0': return r !== 0 && l % r !== 0;
            default:    return true;
        }
    });
}
function evalConditions(numVars) {
    return conditions.every(c => {
        const l = evalExpr(c.left  ?? '0', numVars);
        const r = evalExpr(c.right ?? '0', numVars);
        switch (c.op) {
            case '>':   return l > r;
            case '<':   return l < r;
            case '>=':  return l >= r;
            case '<=':  return l <= r;
            case '==':  return l === r;
            case '!=':  return l !== r;
            case '%0':  return r !== 0 && l % r === 0;
            case '!%0': return r !== 0 && l % r !== 0;
            default:    return true;
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
// $extraCheck(numVars), if given, must also return true for an attempt to be
// accepted — genPreviewNumeric() uses this to fold the formula/clean-division
// check into the SAME retry loop as the conditions check (matching how PHP's
// generateNumeric() retries the whole combination together, not just the vars).
function pickNumVars(extraCheck) {
    const confRows = ncRows.filter(r => r.name);
    const specialModes  = ['derived', 'operator', 'conditional'];
    const randomRows      = confRows.filter(r => !specialModes.includes(r.mode));
    const operatorRows    = confRows.filter(r => r.mode === 'operator');
    const derivedRows     = confRows.filter(r => r.mode === 'derived');
    const conditionalRows = confRows.filter(r => r.mode === 'conditional');
    const unique = document.getElementById('uniqueNumVars')?.checked ?? false;

    // Operator variables are picked ONCE per outer attempt, then only the random
    // values are retried for a batch of inner attempts before giving up on that
    // operator combo and picking new ones. Re-rolling the operator on every single
    // attempt (as before) let whichever option is statistically easier to satisfy
    // the formula/conditions check (passed in via extraCheck — e.g. '+', almost
    // always a valid positive whole result) win the "first success" race far more
    // often than a harder one (e.g. '/', which needs clean division) — skewing the
    // output even when both were equally selected. Mirrors
    // QuestionTemplate::generateNumeric()'s outer/inner split.
    const outerAttempts = 10, innerAttempts = 20;
    let numVars = {}, condOk = false;
    outer: for (let opAttempt = 0; opAttempt < outerAttempts; opAttempt++) {
        // An operator variable's value is one of +-*/ , randomly picked each outer
        // attempt from the admin-checked subset — same spirit as a derived var,
        // except it isn't computed from anything, just rolled like a random one.
        const opVars = {};
        operatorRows.forEach(r => {
            const opts = (r.opOptions && r.opOptions.length) ? r.opOptions : ['+'];
            opVars[r.name] = opts[Math.floor(Math.random() * opts.length)];
        });

        for (let attempt = 0; attempt < innerAttempts; attempt++) {
            numVars = { ...opVars };
            randomRows.forEach(r => {
                const step = Math.max(1, r.step || 1);
                const steps = Math.floor((r.max - r.min) / step);
                numVars[r.name] = r.min + Math.floor(Math.random() * (steps + 1)) * step;
            });
            // Derived vars are computed (not randomly rolled) from whatever vars were
            // already resolved above them, in row order — same evaluator conditions use.
            derivedRows.forEach(r => {
                numVars[r.name] = evalExpr(r.derived || '0', numVars);
            });
            // Conditional vars are a text label picked via a comparison (e.g.
            // "TOTAL1>TOTAL2?ჯერ მეტი:ჯერ ნაკლები") — same ternary engine the
            // text-answer correct_formula uses, with no theme vars in scope.
            conditionalRows.forEach(r => {
                numVars[r.name] = resolveTextFormulaJs(r.conditional || '', numVars, {});
            });
            // Uniqueness only makes sense among actual numbers — an operator symbol or
            // conditional label repeating across slots isn't a "duplicate".
            if (unique) {
                const uniqueVals = randomRows.concat(derivedRows).map(r => numVars[r.name]);
                if (new Set(uniqueVals).size !== uniqueVals.length) continue;
            }
            if (evalConditions(numVars) && (!extraCheck || extraCheck(numVars))) { condOk = true; break outer; }
        }
    }
    return { numVars, condOk };
}
// Builds the display map used for template_text/hint_text substitution — an
// operator variable shows its Georgian label ("ით მეტი") instead of the raw
// symbol ("+") that's used when substituting into correct_formula. Mirrors
// QuestionTemplate::OPERATOR_LABELS / the $displayVars construction in PHP.
function buildDisplayVars(numVars, ncConfRows) {
    const display = { ...numVars };
    (ncConfRows || ncRows.filter(r => r.name)).forEach(r => {
        if (r.mode === 'operator' && display[r.name] !== undefined) {
            display[r.name] = OPERATOR_LABELS[display[r.name]] || display[r.name];
        }
    });
    return display;
}
function clearPreview() {
    document.getElementById('prevQ').innerHTML = '<span style="color:#94a3b8;font-size:0.72rem;line-height:1.8;">③ კითხვის ტექსტი + ④ სწორი პასუხი<br>შეავსეთ preview-სთვის</span>';
    ['prevHint','prevFormula','prevVars','prevWarn'].forEach(id => document.getElementById(id).innerHTML = '');
    document.getElementById('prevOpts').innerHTML = '';
}

function isSolvable(rows, hidden) {
    const H = rows.length;
    // known[r][c] = true if determinable
    const known = rows.map((row, r) => row.map((_, c) => !hidden.has(r + ',' + c)));
    let changed = true;
    while (changed) {
        changed = false;
        for (let r = 0; r < H; r++) {
            const rowLen = rows[r].length;
            for (let c = 0; c < rowLen; c++) {
                if (known[r][c]) continue;
                // bottom-up: both children known
                if (r + 1 < H && known[r+1][c] && known[r+1][c+1]) {
                    known[r][c] = true; changed = true; continue;
                }
                // top-down left child: parent(r-1,c) + right sibling(r,c+1) known
                if (r > 0 && known[r-1][c] && c + 1 < rowLen && known[r][c+1]) {
                    known[r][c] = true; changed = true; continue;
                }
                // top-down right child: parent(r-1,c-1) + left sibling(r,c-1) known
                if (r > 0 && c > 0 && known[r-1][c-1] && known[r][c-1]) {
                    known[r][c] = true; changed = true; continue;
                }
            }
        }
    }
    return known.every(row => row.every(v => v));
}

function buildPyramidData(height, maxBase, hiddenCount) {
    for (let attempt = 0; attempt < 100; attempt++) {
        const base = [];
        for (let i = 0; i < height; i++) base.push(1 + Math.floor(Math.random() * maxBase));
        const rowsFromBottom = [base];
        for (let r = 1; r < height; r++) {
            const prev = rowsFromBottom[r - 1];
            const row = [];
            for (let c = 0; c < prev.length - 1; c++) row.push(prev[c] + prev[c + 1]);
            rowsFromBottom.push(row);
        }
        const rows = rowsFromBottom.slice().reverse(); // top → bottom
        const allCells = [];
        rows.forEach((row, r) => row.forEach((_, c) => allCells.push([r, c])));
        for (let i = allCells.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [allCells[i], allCells[j]] = [allCells[j], allCells[i]];
        }
        const hidden = new Set(allCells.slice(0, Math.min(hiddenCount, allCells.length)).map(([r,c]) => r+','+c));
        if (isSolvable(rows, hidden)) return { rows, hidden };
    }
    // fallback: no hidden cells
    const base = [];
    for (let i = 0; i < height; i++) base.push(1 + Math.floor(Math.random() * maxBase));
    const rowsFromBottom = [base];
    for (let r = 1; r < height; r++) {
        const prev = rowsFromBottom[r - 1];
        const row = [];
        for (let c = 0; c < prev.length - 1; c++) row.push(prev[c] + prev[c + 1]);
        rowsFromBottom.push(row);
    }
    return { rows: rowsFromBottom.slice().reverse(), hidden: new Set() };
}

function genPreviewPyramid() {
    const h    = parseInt(document.getElementById('pyrHeight').value) || 3;
    const mx   = parseInt(document.getElementById('pyrMax').value)    || 9;
    const hide = parseInt(document.getElementById('pyrHide').value)   || 2;
    const { rows, hidden } = buildPyramidData(h, mx, hide);

    let html = '<div style="display:flex;flex-direction:column;align-items:center;gap:5px;margin-top:4px;">';
    rows.forEach((row, r) => {
        html += '<div style="display:flex;gap:5px;">';
        row.forEach((val, c) => {
            const pos = r + ',' + c;
            const isHidden = hidden.has(pos);
            if (isHidden) {
                html += `<div style="width:40px;height:40px;border-radius:8px;border:2px dashed #a5b4fc;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-family:'Goldman',monospace;font-size:0.82rem;color:#a5b4fc;">?</div>`;
            } else {
                html += `<div style="width:40px;height:40px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-family:'Goldman',monospace;font-size:0.82rem;font-weight:700;">${val}</div>`;
            }
        });
        html += '</div>';
    });
    html += '</div>';

    document.getElementById('prevQ').innerHTML = '🔺 პირამიდა — ' + h + '-ძირი';
    document.getElementById('prevHint').innerHTML = '';
    document.getElementById('prevOpts').innerHTML = html;
    const hiddenActual = hidden.size;
    document.getElementById('prevFormula').innerHTML =
        `<span style="color:#4f46e5;">ლურჯი</span> = ცნობილი &nbsp;·&nbsp; <span style="color:#a5b4fc;">?</span> = ცარიელი (${hiddenActual} უჯრა)`;
    document.getElementById('prevVars').innerHTML = `მაქს. ძირი: ${mx} · ნამდვილი ჯამები · ✓ ამოხსნადი`;
    document.getElementById('prevWarn').innerHTML = hiddenActual < hide
        ? `<span style="color:#f87171;">⚠ ${hide} ცარიელი ვერ მოიძებნა — ${hiddenActual} ჩაისვა</span>`
        : '';
}

function genPreviewCode() {
    const count      = parseInt(document.getElementById('codeSymCount').value)   || 3;
    const minV       = parseInt(document.getElementById('codeMinVal').value)      || 1;
    const maxV       = parseInt(document.getElementById('codeMaxVal').value)      || 9;
    const varsPerEq  = parseInt(document.getElementById('codeVarsPerEq').value)   || 2;
    const uniqueVals = document.getElementById('codeUniqueVals').checked;
    const ops = [];
    if (document.getElementById('codeOpPlus').checked)  ops.push('+');
    if (document.getElementById('codeOpMinus').checked) ops.push('−');
    if (document.getElementById('codeOpMul').checked)   ops.push('×');
    if (document.getElementById('codeOpDiv').checked)   ops.push('÷');
    if (!ops.length) ops.push('+');

    const allEmoji = ['🍎','🍌','🍓','🍊','🍇','🍐','🍍','🍒','🍉','🍋','🍑','🥝','⭐','🌙','💎','🔥','🌊','🎵','🐶','🐱','🐻','🦊','🐼','🌸','🌈','🎯','🏆','🚀','🎸','🦋'];
    const shuffled = [...allEmoji].sort(() => Math.random() - 0.5);
    const symbols  = shuffled.slice(0, count);

    // Generate values (unique if needed)
    let values;
    if (uniqueVals && (maxV - minV + 1) >= count) {
        const pool = Array.from({length: maxV - minV + 1}, (_, i) => minV + i)
            .sort(() => Math.random() - 0.5);
        values = pool.slice(0, count);
    } else {
        values = symbols.map(() => minV + Math.floor(Math.random() * (maxV - minV + 1)));
    }

    // Anchor equation (always +, uses only S0)
    const equations = [];
    if (varsPerEq === 3) {
        equations.push(`${symbols[0]} + ${symbols[0]} + ${symbols[0]} = ${3 * values[0]}`);
    } else {
        equations.push(`${symbols[0]} + ${symbols[0]} = ${2 * values[0]}`);
    }

    // Chain equations
    const chainOps = Array.from({length: count - 1}, () => ops[Math.floor(Math.random() * ops.length)]);
    // If multiple operators were selected but every chain link collapsed to the same
    // one by chance, force one link to differ (mirrors CodeService::build()).
    if (ops.length > 1 && chainOps.length > 1 && new Set(chainOps).size < 2) {
        let alt = chainOps[0];
        while (alt === chainOps[0]) alt = ops[Math.floor(Math.random() * ops.length)];
        chainOps[Math.floor(Math.random() * chainOps.length)] = alt;
    }
    for (let i = 1; i < count; i++) {
        let op = chainOps[i - 1];
        let eq;
        if (varsPerEq === 3) {
            // Pattern: S(i-1) op S(i) op S(i) = result
            if (op === '+') {
                eq = `${symbols[i-1]} + ${symbols[i]} + ${symbols[i]} = ${values[i-1] + 2*values[i]}`;
            } else if (op === '−') {
                if (values[i-1] > 2 * values[i]) {
                    eq = `${symbols[i-1]} − ${symbols[i]} − ${symbols[i]} = ${values[i-1] - 2*values[i]}`;
                } else {
                    eq = `${symbols[i-1]} + ${symbols[i]} + ${symbols[i]} = ${values[i-1] + 2*values[i]}`;
                }
            } else if (op === '×') {
                eq = `${symbols[i-1]} × ${symbols[i]} × ${symbols[i]} = ${values[i-1] * values[i] * values[i]}`;
            } else {
                eq = `${symbols[i-1]} + ${symbols[i]} + ${symbols[i]} = ${values[i-1] + 2*values[i]}`;
            }
        } else {
            // Pattern: S(i-1) op S(i) = result
            if (op === '+') {
                eq = `${symbols[i-1]} + ${symbols[i]} = ${values[i-1] + values[i]}`;
            } else if (op === '−') {
                if (values[i-1] >= values[i]) {
                    eq = `${symbols[i-1]} − ${symbols[i]} = ${values[i-1] - values[i]}`;
                } else {
                    eq = `${symbols[i]} − ${symbols[i-1]} = ${values[i] - values[i-1]}`;
                }
            } else if (op === '×') {
                eq = `${symbols[i-1]} × ${symbols[i]} = ${values[i-1] * values[i]}`;
            } else if (op === '÷') {
                // Find divisor of values[i-1] for values[i]
                let divisors = [];
                for (let d = minV; d <= maxV; d++) { if (values[i-1] % d === 0) divisors.push(d); }
                if (divisors.length) {
                    values[i] = divisors[Math.floor(Math.random() * divisors.length)];
                    eq = `${symbols[i-1]} ÷ ${symbols[i]} = ${values[i-1] / values[i]}`;
                } else {
                    eq = `${symbols[i-1]} + ${symbols[i]} = ${values[i-1] + values[i]}`;
                }
            } else {
                eq = `${symbols[i-1]} + ${symbols[i]} = ${values[i-1] + values[i]}`;
            }
        }
        equations.push(eq);
    }

    const target = [...symbols].reverse();
    const answers = target.map((_, pos) => values[count - 1 - pos]);

    document.getElementById('prevQ').innerHTML = '🕵️ კოდის გაშიფვრა';
    document.getElementById('prevHint').innerHTML = '';
    document.getElementById('prevWarn').innerHTML = '';

    let eqHtml = '<div style="background:#fff8e7;border-radius:6px;padding:10px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;">';
    equations.forEach(eq => { eqHtml += `<div style="font-size:1.05rem;font-weight:700;color:#374151;margin:4px 0;">${eq}</div>`; });
    eqHtml += '</div>';

    let targetHtml = '<div style="font-size:0.6rem;color:#94a3b8;margin-bottom:5px;letter-spacing:0.1em;">სამიზნე კოდი:</div>';
    targetHtml += '<div style="display:flex;gap:6px;justify-content:center;margin-bottom:10px;">';
    target.forEach(sym => { targetHtml += `<div style="width:40px;height:40px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">${sym}</div>`; });
    targetHtml += '</div>';

    let ansHtml = '<div style="display:flex;gap:6px;justify-content:center;">';
    answers.forEach(v => { ansHtml += `<div style="width:40px;height:40px;border-radius:8px;background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;">${v}</div>`; });
    ansHtml += '</div>';

    document.getElementById('prevOpts').innerHTML = eqHtml + targetHtml + ansHtml;
    document.getElementById('prevFormula').innerHTML = `სიმბოლო მნიშვნელობები: ${symbols.map((s,i)=>`${s}=${values[i]}`).join(' · ')}`;
    document.getElementById('prevVars').innerHTML = '';
}

function genPreview() {
    // A thrown error anywhere in a genPreview*() function used to abort
    // silently (console-only) — the preview just stayed stale/blank with no
    // clue why. Surfacing it in #prevWarn makes that failure visible instead
    // of looking like "nothing happened".
    try {
        genPreviewInner();
    } catch (e) {
        const warn = document.getElementById('prevWarn');
        if (warn) warn.innerHTML = '<span style="color:#f87171;">⚠ preview შეცდომა: ' + escHtmlFormLp(e.message || String(e)) + '</span>';
        console.error('genPreview failed:', e);
    }
}
function genPreviewInner() {
    const qt = document.getElementById('qtInput').value;
    if (qt === 'pyramid')   { genPreviewPyramid(); return; }
    if (qt === 'code')      { genPreviewCode();     return; }
    if (qt === 'crossword') { genPreviewCw();       return; }
    if (qt === 'scale')     { genPreviewScale();    return; }
    const autoEl = document.getElementById('autoBuildEnabled');
    if (autoEl && autoEl.checked) { genPreviewAuto(); return; }
    const tmpl = document.getElementById('templateText').value;
    if (!tmpl) { clearPreview(); return; }
    const isTxt = document.getElementById('answerTypeInput').value === 'text';
    isTxt ? genPreviewText(tmpl) : genPreviewNumeric(tmpl);
}

// Mirrors QuestionTemplate::evalArithmeticSteps() in PHP: parens first, then × ÷
// left-to-right collapsing each factor chain, then the +/- chain left-to-right —
// tracking whether that final walk (including the first term) ever dips below 0.
function evalArithmeticStepsJs(names, numVars, chosenOps, parenStart) {
    const applyOp = (a, op, b) => {
        if (op === '+') return a + b;
        if (op === '-') return a - b;
        if (op === '×') return a * b;
        if (op === '÷') return (b !== 0 && a % b === 0) ? a / b : null;
        return null;
    };
    let terms = names.map((name, i) => ({ val: numVars[name], op: i === 0 ? null : chosenOps[i - 1] }));

    // Tracks whether ANY sub-step — inside the parens, inside a ×÷ chain, or in the
    // outer +/- walk — ever produced a negative value a child would have had to
    // compute along the way, even if it's later cancelled out by what follows.
    let noNegative = true;

    if (parenStart !== null) {
        const a = terms[parenStart], b = terms[parenStart + 1];
        const combined = applyOp(a.val, b.op, b.val);
        if (combined === null) return null;
        if (combined < 0) noNegative = false;
        terms.splice(parenStart, 2, { val: combined, op: a.op });
    }

    const reduced = [];
    for (const t of terms) {
        if (t.op === '×' || t.op === '÷') {
            const prev = reduced.pop();
            const combined = applyOp(prev.val, t.op, t.val);
            if (combined === null) return null;
            if (combined < 0) noNegative = false;
            reduced.push({ val: combined, op: prev.op });
        } else {
            reduced.push(t);
        }
    }

    let running = reduced[0].val;
    if (running < 0) noNegative = false;
    for (let i = 1; i < reduced.length; i++) {
        running = applyOp(running, reduced[i].op, reduced[i].val);
        if (running === null) return null;
        if (running < 0) noNegative = false;
    }
    return { result: running, noNegative };
}

// Mirrors QuestionTemplate::attemptArithmetic() — picks an operator combo (and paren
// position) once, then retries only the random VALUES (via pickNumVars(), re-rolled
// every inner try — a fixed set of numbers can make '-' impossible no matter how
// operators are ordered, even though a different roll would work) against that fixed
// combo before giving up on it and picking a new one. Operators are deliberately NOT
// re-rolled on every value attempt: doing so would let whichever operator is
// statistically easier (e.g. '+', always a valid positive result) win the "first
// success" race far more often than a harder one (e.g. '-'), skewing the output even
// when both are equally selected. Returns null if nothing qualified within maxAttempts.
function attemptArithmeticJs(operators, useParens, noNegSteps, maxAttempts) {
    const names = ncRows.filter(r => r.name).map(r => r.name);
    const n = names.length;
    if (n < 2) return null;

    const outerAttempts = Math.min(10, maxAttempts);
    const innerAttempts = Math.max(1, Math.floor(maxAttempts / outerAttempts));

    for (let opAttempt = 0; opAttempt < outerAttempts; opAttempt++) {
        const chosenOps = [];
        for (let i = 0; i < n - 1; i++) chosenOps.push(operators[Math.floor(Math.random() * operators.length)]);
        // If multiple operators were selected but every slot collapsed to the same
        // one by chance, force one slot to differ (mirrors QuestionTemplate::attemptArithmetic()).
        if (operators.length > 1 && chosenOps.length > 1 && new Set(chosenOps).size < 2) {
            let alt = chosenOps[0];
            while (alt === chosenOps[0]) alt = operators[Math.floor(Math.random() * operators.length)];
            chosenOps[Math.floor(Math.random() * chosenOps.length)] = alt;
        }
        // Parens are always applied once enabled — only *where* they land varies.
        const parenStart = (useParens && n >= 3) ? Math.floor(Math.random() * (n - 1)) : null;

        for (let valAttempt = 0; valAttempt < innerAttempts; valAttempt++) {
            const { numVars } = pickNumVars();
            const evaluated = evalArithmeticStepsJs(names, numVars, chosenOps, parenStart);
            if (!evaluated) continue;
            if (evaluated.result <= 0 || Math.floor(evaluated.result) !== evaluated.result) continue;
            if (noNegSteps && !evaluated.noNegative) continue;

            return { correct: evaluated.result, chosenOps, parenStart, numVars, names };
        }
    }
    return null;
}

function genPreviewAuto() {
    const ops = [];
    if (document.getElementById('autoOpPlus').checked)  ops.push('+');
    if (document.getElementById('autoOpMinus').checked) ops.push('-');
    if (document.getElementById('autoOpMul').checked)   ops.push('×');
    if (document.getElementById('autoOpDiv').checked)   ops.push('÷');
    const operators = ops.length ? ops : ['+'];
    const useParens = document.getElementById('autoParens').checked;
    const noNegSteps = document.getElementById('autoNoNegSteps').checked;
    if (!ncRows.filter(r => r.name).length) { clearPreview(); return; }

    // mix_blank's "sometimes hide an operand instead of the result" relies on
    // {{EXPR}} to correctly render either "...= ?" or "...= <result>" depending on
    // which was hidden. If the admin wrote their own text without that token (e.g.
    // raw {{N1}}/{{N2}} placeholders with a hand-written "= ?" at the end), force
    // the classic result-hidden style. Mirrors QuestionTemplate::generateAutoArithmetic().
    const customText = (document.getElementById('templateText').value || '').trim();
    const hasExprToken = customText.includes('{{EXPR}}');
    const mixBlank = document.getElementById('autoMixBlank').checked && (!customText || hasExprToken);

    // Same two-tier approach as the PHP generator: try with every constraint as
    // configured first; if the ranges make that impossible (e.g. only '-' with no
    // room to stay non-negative), relax the no-negative-intermediate check before
    // giving up on the chosen operators — only fall back to '+' as a last resort.
    let found = attemptArithmeticJs(operators, useParens, noNegSteps, 400);
    if (!found && noNegSteps) found = attemptArithmeticJs(operators, useParens, false, 400);

    let correct, chosenOps, parenStart, numVars, names;
    if (found) {
        ({ correct, chosenOps, parenStart, numVars, names } = found);
    } else {
        ({ numVars } = pickNumVars());
        names = Object.keys(numVars);
        const n = names.length;
        if (n < 2) { clearPreview(); return; }
        chosenOps = Array(n - 1).fill('+'); parenStart = null;
        correct = names.reduce((s, k) => s + numVars[k], 0);
    }
    const n = names.length;

    // When mixBlank is on, the hidden slot is always one of the operands (never
    // the result) — "4 + ? = 9" instead of "4 + 5 = ?". When it's off, the result
    // is always what's hidden (the classic style). Operand slots inside the paren
    // pair are excluded (an extra algebra step a child hasn't been shown). Mirrors
    // QuestionTemplate::generateAutoArithmetic().
    let blankPos = null;
    if (mixBlank) {
        let blankCandidates = Array.from({ length: n }, (_, i) => i);
        if (parenStart !== null) {
            blankCandidates = blankCandidates.filter(i => i !== parenStart && i !== parenStart + 1);
        }
        if (blankCandidates.length) {
            blankPos = blankCandidates[Math.floor(Math.random() * blankCandidates.length)];
        }
    }
    const answer = blankPos === null ? correct : numVars[names[blankPos]];

    let display = '';
    for (let i = 0; i < n; i++) {
        if (parenStart !== null && i === parenStart) display += '(';
        display += (i === blankPos) ? '?' : numVars[names[i]];
        if (parenStart !== null && i === parenStart + 1) display += ')';
        if (i < n - 1) display += ' ' + chosenOps[i] + ' ';
    }
    const equation = display + (blankPos === null ? ' = ?' : ' = ' + correct);

    // Admin can wrap the generated equation in their own instruction text (e.g.
    // "ამოხსენი: {{EXPR}}"). {{EXPR}} is replaced with the full equation; any
    // {{N1}}, {{N2}}... placeholder is also replaced with that variable's rolled
    // value (shown as "?" if it's the one hidden this round). The equation is
    // appended at the end ONLY if the text references neither {{EXPR}} nor any
    // variable directly — otherwise the admin is already placing the numbers
    // themselves (e.g. "{{N1}} + {{N2}} = ?"), and appending it again would
    // duplicate it. Falls back to the bare equation when the field is empty.
    const hasVarToken = names.some(name => customText.includes(OB + name + CB));
    let text;
    if (!customText) {
        text = equation;
    } else {
        const displayValues = { ...numVars };
        if (blankPos !== null) displayValues[names[blankPos]] = '?';
        text = hasExprToken ? customText.split('{{EXPR}}').join(equation) : customText;
        Object.entries(displayValues).forEach(([k, v]) => { text = text.split(OB + k + CB).join(String(v)); });
        text = text.replace(/\{\{\w+\}\}/g, '?');
        if (!hasExprToken && !hasVarToken) text += ' ' + equation;
    }
    document.getElementById('prevQ').innerHTML = escHtml(text);
    document.getElementById('prevHint').textContent = (document.getElementById('hintText').value || '').trim();
    // A literal operator symbol (e.g. a hand-typed "+") typed next to raw {{N1}}/
    // {{N2}} placeholders can never change — only {{EXPR}} carries the real,
    // per-generation operator/parens. With more than one operator selected, that
    // mismatch would show a wrong-looking sign while the graded answer still uses
    // whichever operator was actually rolled underneath.
    document.getElementById('prevWarn').innerHTML = (hasVarToken && !hasExprToken && operators.length > 1)
        ? '<span>⚠ ტექსტში ოპერატორი ფიქსირებულია — სწორი ასახვისთვის გამოიყენეთ {{EXPR}} {{N1}}/{{N2}}-ის მაგივრად</span>'
        : '';

    const dMin = +document.getElementById('distMin').value || 1;
    const dMax = +document.getElementById('distMax').value || 10;
    const wrong = [];
    let attempts = 0;
    while (wrong.length < 3 && attempts < 100) {
        attempts++;
        const delta = dMin + Math.floor(Math.random() * (dMax - dMin + 1));
        const candidate = answer + (Math.random() < 0.5 ? delta : -delta);
        if (candidate > 0 && candidate !== answer && !wrong.includes(candidate)) wrong.push(candidate);
    }
    const opts = [...wrong, answer];
    for (let i = opts.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [opts[i], opts[j]] = [opts[j], opts[i]]; }
    document.getElementById('prevOpts').innerHTML = opts.map(o => `<div class="preview-opt ${o===answer?'c':''}">${o}</div>`).join('');
    document.getElementById('prevFormula').innerHTML = `სწ. პასუხი: <span style="color:#2a7a2a;">${answer}</span>`;
    document.getElementById('prevVars').innerHTML = names.map(k => `${k}=${numVars[k]}`).join(' · ');
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML.replace(/\n/g, '<br>');
}

function genPreviewText(tmpl) {
    const themeMap = pickThemeVars();
    const { numVars } = pickNumVars();
    const displayVars = buildDisplayVars(numVars);
    const allVars = {...themeMap, ...displayVars};

    let text = tmpl;
    Object.entries(allVars).forEach(([k,v]) => { text = text.replaceAll(OB+k+CB, v); });
    text = text.replace(/\{\{\w+\}\}/g, '?');
    document.getElementById('prevQ').innerHTML = escHtml(text);
    document.getElementById('prevHint').textContent = '';
    document.getElementById('prevWarn').innerHTML = '';

    const formula = textCondMode
        ? document.getElementById('textCondFormula').value.trim()
        : document.getElementById('textCorrectVar').value;
    if (!formula) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#94a3b8;">სწორი პასუხის ცვლადი აირჩიეთ...</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = Object.entries(themeMap).map(([k,v]) => `${k}=${v}`).join(' · ');
        return;
    }

    const themeAndSpecial = { ...themeMap, ...SPEC };
    const correct = resolveTextFormulaJs(formula, numVars, themeAndSpecial);
    const chipVarNames = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname);
    const SPEC_KEYS = Object.keys(SPEC);
    // SPEC chips (__ALL__/__NONE__/__EQUAL__) are PINNED -- always included
    // when selected, on top of the configured count -- everything else
    // (plain var chips + hand-typed templates) is the RANDOM POOL that many
    // get sampled from each generation. Mirrors the PHP generateText() split.
    const pinnedNames = chipVarNames.filter(v => SPEC_KEYS.includes(v));
    const poolNames = chipVarNames.filter(v => !SPEC_KEYS.includes(v)).concat(textCustomOpts);
    const wrongCount = Math.max(1, parseInt(document.getElementById('textDistCount')?.value) || 3);
    // A hand-typed distractor (containing {{VAR}} tokens) is substituted the
    // same way the correct_formula's literal-text form is -- mirrors the PHP
    // generateText() fix for distractor "var names" that are really templates.
    // A "{{*}}" slot is a WILDCARD -- filled with a random base variable's
    // value each time the pattern is expanded, so one typed template
    // auto-generates many distinct wrong answers (mirrors PHP's $resolvePattern).
    const allVarsForOpts = { ...numVars, ...themeAndSpecial };
    const swappablePool = ncRows.filter(r => r.name && !['derived','operator','conditional'].includes(r.mode)).map(r => r.name);
    // Each "{{*}}" slot in a SINGLE rendering must land on a DIFFERENT
    // variable -- two slots independently picking the same one (e.g. both
    // landing on N1) would render as something like "2 და 2", which reads as
    // "delete two separate 2s" when only one position actually holds that
    // value. Shuffled once per call and consumed in order, so a 2-slot
    // pattern can never repeat a position within that rendering. Mirrors PHP.
    const resolvePattern = tpl => {
        const shuffled = [...swappablePool];
        for (let i = shuffled.length-1; i > 0; i--) {
            const j = Math.floor(Math.random()*(i+1)); [shuffled[i],shuffled[j]]=[shuffled[j],shuffled[i]];
        }
        let idx = 0;
        return tpl.replace(/\{\{(\*|\w+)\}\}/g, (m, name) => {
            if (name === '*') {
                if (!shuffled.length) return m;
                const pick = shuffled[idx % shuffled.length];
                idx++;
                return allVarsForOpts[pick] ?? '?';
            }
            return (name in allVarsForOpts) ? allVarsForOpts[name] : m;
        });
    };
    // A plain (non-template) var name must resolve against the SAME merged
    // set resolvePattern() uses (numeric vars included) -- mirrors PHP's
    // generateText(), where $vars already merges displayNumVars + themeVars.
    // SPEC[v]/themeMap[v] alone would leave a bare numeric var name (e.g.
    // "DEL1" picked directly, not inside a {{...}} template) unresolved.
    const resolveOpt = v => v.includes('{{') ? resolvePattern(v) : ((v in allVarsForOpts) ? allVarsForOpts[v] : '?');
    // Two wildcard-filled renderings that pick the SAME numbers in a
    // different order (e.g. "6 და 2" vs "2 და 6") are the same answer in
    // disguise when the slots are interchangeable -- compared by their
    // sorted digit multiset rather than the literal string, so only one
    // ordering of any given pair ever shows up. Mirrors PHP's $digitKey.
    // String(s) first -- a bare numeric var (e.g. DEL1) picked directly as
    // the correct answer resolves to a raw JS number, which has no .match();
    // every other source (theme var, {{...}} template) was already a string,
    // so this went unnoticed until a numeric var was used as the correct answer.
    const digitKey = s => (String(s).match(/\d+/g) || []).map(Number).sort((a,b) => a-b).join(',');

    const seen = new Set([correct]);
    const seenWildcardKeys = new Set([digitKey(correct)]);
    const pinned = [];
    pinnedNames.forEach(v => {
        const val = resolveOpt(v);
        if (seen.has(val)) return;
        seen.add(val); pinned.push(val);
    });
    let pool = [];
    poolNames.forEach(v => {
        if (v.includes('{{*}}')) {
            if (!swappablePool.length) return;
            for (let attempt = 0; attempt < 30 && pool.length < wrongCount * 3; attempt++) {
                const val = resolvePattern(v);
                const key = digitKey(val);
                if (seen.has(val) || seenWildcardKeys.has(key)) continue;
                seen.add(val); seenWildcardKeys.add(key); pool.push(val);
            }
            return;
        }
        const val = resolveOpt(v);
        if (seen.has(val)) return;
        seen.add(val); pool.push(val);
    });
    for (let i = pool.length-1; i > 0; i--) {
        const j = Math.floor(Math.random()*(i+1)); [pool[i],pool[j]]=[pool[j],pool[i]];
    }
    const opts = [...pinned, ...pool.slice(0, wrongCount)].map(v => ({ v, c: false }));
    opts.unshift({ v: correct, c: true });
    for (let i = opts.length-1; i > 0; i--) {
        const j = Math.floor(Math.random()*(i+1)); [opts[i],opts[j]]=[opts[j],opts[i]];
    }

    document.getElementById('prevOpts').innerHTML = opts.map(o =>
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`).join('');
    document.getElementById('prevFormula').innerHTML =
        `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span> <span style="color:#94a3b8;">(${formula})</span>`;
    document.getElementById('prevVars').innerHTML = Object.entries({...themeMap, ...numVars}).map(([k,v]) => `${k}=${v}`).join(' · ');
    // "რამდენი არასწორი" only picks N from the candidates already marked
    // below it -- it can't invent any on its own, so an admin who filled it
    // in without marking/typing a single chip would otherwise see just the
    // correct answer with no clue why.
    document.getElementById('prevWarn').innerHTML = (!pinned.length && !pool.length)
        ? '<span>⚠ არჩეული კანდიდატი არ არის — მონიშნეთ ჩიფი ან დააწერეთ შაბლონი ზემოთ, "რამდენი არასწორი" მხოლოდ მათგან ირჩევს</span>'
        : '';
}

function genPreviewNumeric(tmpl) {
    const formula = document.getElementById('correctFormula').value.trim();
    const dMin    = +document.getElementById('distMin').value || 1;
    const dMax    = +document.getElementById('distMax').value || 10;
    const themeMap = pickThemeVars();

    // The formula/clean-division check runs INSIDE the same retry loop as the
    // conditions check (via pickNumVars()'s extraCheck) — so an operator variable
    // landing on "/" where the numbers don't divide evenly causes that whole
    // attempt to be retried with fresh values, not silently truncated. Mirrors
    // QuestionTemplate::generateNumeric()'s single combined attempt loop.
    let correct = null, substituted = '';
    const { numVars, condOk } = pickNumVars(!formula ? null : (vars) => {
        let f = formula;
        Object.entries(vars).forEach(([k, v]) => { f = f.replaceAll(k, String(v)); });
        // Mirrors evalExpr()/PHP's generateNumeric() -- any bare word
        // surviving substitution must be a whitelisted function, everything
        // else is dropped before Function() ever sees it (a bare identifier
        // here can reach real browser globals, unlike PHP's eval()).
        f = f.replace(/[a-zA-Z_][a-zA-Z0-9_]*/g, m => MATH_FNS.includes(m) ? m : '');
        f = f.replace(/[^0-9a-zA-Z_+\-*/(),\s%.]/g, '');
        try {
            const round = Math.round, floor = Math.floor, ceil = Math.ceil, abs = Math.abs,
                  min = Math.min, max = Math.max, pow = Math.pow, sqrt = Math.sqrt;
            const raw = Function('round','floor','ceil','abs','min','max','pow','sqrt',
                'return (' + f + ')')(round, floor, ceil, abs, min, max, pow, sqrt);
            if (Number.isFinite(raw) && raw > 0 && Math.floor(raw) === raw) {
                correct = raw; substituted = f; return true;
            }
        } catch (e) {}
        correct = null;
        return false;
    });

    const displayVars = buildDisplayVars(numVars);
    let text = tmpl;
    Object.entries({...themeMap, ...displayVars}).forEach(([k,v]) => { text = text.replaceAll(OB+k+CB, v); });
    text = text.replace(/\{\{\w+\}\}/g, '?');

    let hint = (document.getElementById('hintText').value || '').trim();
    Object.entries({...themeMap, ...displayVars}).forEach(([k,v]) => { hint = hint.replaceAll(OB+k+CB, v); });
    hint = hint.replace(/\{\{\w+\}\}/g, '?');

    document.getElementById('prevQ').innerHTML = escHtml(text);
    document.getElementById('prevHint').textContent = hint || '';
    document.getElementById('prevWarn').innerHTML = condOk ? '' : '<span>⚠ პირობები/ფორმულა ვერ შეხვდა 200 მცდელობაში</span>';

    if (!formula) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#94a3b8;">④ ფორმულა ჩაწერეთ პასუხისთვის...</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
        return;
    }

    if (correct === null) {
        document.getElementById('prevOpts').innerHTML = '<span style="font-size:0.64rem;color:#2a2a2a;">ფორმულა ვერ გამოითვალა</span>';
        document.getElementById('prevFormula').innerHTML = '';
        document.getElementById('prevVars').innerHTML = '';
        return;
    }
    correct = Math.floor(correct);

    // "numeric_input" (⌨️ child types the number) has no options to build —
    // show the computed answer directly instead of distractors.
    if (document.getElementById('answerTypeInput').value === 'numeric_input') {
        document.getElementById('prevOpts').innerHTML =
            `<div class="preview-opt c" style="cursor:default;">✍️ ${correct}</div>
             <div style="font-size:0.6rem;color:#94a3b8;margin-top:4px;">(მოსწავლე თავად ჩაწერს რიცხვს — ვარიანტები არ გენერირდება)</div>`;
        document.getElementById('prevFormula').innerHTML =
            `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span>&nbsp;·&nbsp;<span style="color:#1e1e1e;">${formula} = ${substituted} = ${correct}</span>`;
        document.getElementById('prevVars').innerHTML = Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
        return;
    }

    const f = substituted;

    const wrong = new Set();
    let tries = 0;
    const wrongCount = noneCorrect ? 3 : 3;
    while (wrong.size < wrongCount && tries < 80) {
        tries++;
        const delta = dMin + Math.floor(Math.random() * (dMax - dMin + 1));
        const cand  = correct + (Math.random() < 0.5 ? 1 : -1) * delta;
        if (cand > 0 && cand !== correct) wrong.add(cand);
    }

    let opts;
    if (noneCorrect) {
        opts = [...wrong].map(v => ({ v, c: false }));
        opts.push({ v: 'არცერთი სწორი არ არის', c: true });
    } else {
        opts = [{ v: correct, c: true }, ...[...wrong].map(v => ({ v, c: false }))];
    }
    for (let i = opts.length-1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i+1)); [opts[i], opts[j]] = [opts[j], opts[i]];
    }

    document.getElementById('prevOpts').innerHTML = opts.map(o =>
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`).join('');
    document.getElementById('prevFormula').innerHTML = noneCorrect
        ? `სწ. პასუხი: <span style="color:#2a7a2a;">არცერთი სწორი არ არის</span>&nbsp;·&nbsp;<span style="color:#94a3b8;">${formula} = ${correct} (hidden)</span>`
        : `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span>&nbsp;·&nbsp;<span style="color:#1e1e1e;">${formula} = ${f} = ${correct}</span>`;
    document.getElementById('prevVars').innerHTML = Object.entries(numVars).map(([k,v]) => `${k}=${v}`).join(' · ') || '';
}

// ── Event delegation for dynamically rendered inputs (avoids inline-handler issues)
document.getElementById('ncRows').addEventListener('input', function(e) {
    const el = e.target, id = +el.dataset.nc, field = el.dataset.ncF;
    if (!id || !field) return;
    let val;
    if (field === 'name') val = (el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, ''));
    else if (field === 'derived' || field === 'conditional') val = el.value;
    else val = +el.value;
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

// ── "⚡ შაბლონის ჩატვირთვა" picker cascade: grade → topic → template
function onLoadGradeChange(gradeId) {
    const topicSel = document.getElementById('loadTopicSelect');
    topicSel.innerHTML = '<option value="">— თემა —</option>';
    const list = gradeId ? (_KS.topicsByGrade[gradeId] || []) : [];
    list.forEach(t => topicSel.add(new Option(t.name, t.id)));
    topicSel.disabled = !gradeId;
    updateLoadTplVisibility();
}
function onLoadTopicChange() {
    updateLoadTplVisibility();
}
// Browsing via the grade→topic→template selects (no search typed) — unrelated
// to the search dropdown below, just hides options outside the chosen topic.
function updateLoadTplVisibility() {
    const tplSel = document.getElementById('loadTplSelect');
    const topicId = document.getElementById('loadTopicSelect').value;
    tplSel.value = '';
    Array.from(tplSel.options).forEach(o => {
        if (!o.value) return;
        o.hidden = String(o.dataset.topic) !== String(topicId);
    });
    tplSel.disabled = !topicId;
    clearLoadSelectedBadge();
}

// ── Search-as-you-type dropdown — shows which templates actually match
// (by title or id) as a visible, clickable list, since you might not
// remember the exact title or id to pick blindly from a <select>.
function onLoadSearchInput() {
    renderLoadSearchResults();
}
function renderLoadSearchResults() {
    const box = document.getElementById('loadSearchResults');
    const search = (document.getElementById('loadSearchInput').value || '').trim().toLowerCase();
    if (!search) { box.style.display = 'none'; box.innerHTML = ''; return; }

    const tplSel = document.getElementById('loadTplSelect');
    const matches = Array.from(tplSel.options)
        .filter(o => o.value && (o.dataset.search || '').includes(search))
        .slice(0, 40);

    box.innerHTML = matches.length
        ? matches.map(o =>
            `<div class="load-search-item" onclick="pickLoadSearchResult(${o.value})">${escHtml(o.textContent)}</div>`
          ).join('')
        : '<div class="load-search-empty">არაფერი ვერ მოიძებნა</div>';
    box.style.display = 'block';
}
function hideLoadSearchResults() {
    document.getElementById('loadSearchResults').style.display = 'none';
}
function pickLoadSearchResult(id) {
    const tplSel = document.getElementById('loadTplSelect');
    const opt = Array.from(tplSel.options).find(o => String(o.value) === String(id));
    if (!opt) return;
    opt.hidden = false;
    tplSel.value = String(id);
    tplSel.disabled = false;
    document.getElementById('loadSearchInput').value = '';
    hideLoadSearchResults();
    const badge = document.getElementById('loadSelectedBadge');
    badge.textContent = '✓ არჩეულია: ' + opt.textContent.trim();
    badge.style.display = 'block';
}
function clearLoadSelectedBadge() {
    const badge = document.getElementById('loadSelectedBadge');
    badge.style.display = 'none';
    badge.textContent = '';
}

// ── Load an existing template as a starting point (create page only) — fetches
// the full record, then applies it the same way an edit page's own template
// would have been server-rendered, but driven by JS since this is a *different*
// template than the one the page originally loaded with.
function loadSelectedTemplate() {
    const id = document.getElementById('loadTplSelect').value;
    if (!id) return;
    fetch(`/admin/questions/${id}/data`, { headers: { 'Accept': 'application/json' } })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(data => loadIntoForm(data))
        .catch(() => alert('შაბლონის ჩატვირთვა ვერ მოხერხდა'));
}
function loadIntoForm(data) {
    document.getElementById('titleInput').value = data.title || '';

    // Topic (+ keep the grade filter dropdown in sync for display purposes)
    let placed = false;
    for (const [gId, topics] of Object.entries(_KS.topicsByGrade)) {
        if (topics.some(t => t.id === data.topic_id)) {
            document.getElementById('gradeFilter').value = gId;
            const sel = document.getElementById('topicSelect');
            sel.innerHTML = '<option value="">— აირჩიე —</option>';
            topics.forEach(t => {
                const opt = new Option(t.name, t.id);
                if (t.id === data.topic_id) opt.selected = true;
                sel.add(opt);
            });
            applyGradeMaxLevel(gId);
            placed = true;
            break;
        }
    }
    if (!placed) document.getElementById('topicSelect').value = data.topic_id;

    // Difficulty / olympiad
    if (data.is_olympiad) {
        if (document.getElementById('isOlympiadInput').value !== '1') toggleOlympiad();
    } else {
        if (document.getElementById('isOlympiadInput').value === '1') toggleOlympiad();
        setDiff(data.difficulty || 1);
    }

    // Active / sequence
    const wantActive = data.is_active !== false;
    document.getElementById('isActiveInput').value = wantActive ? '1' : '0';
    document.getElementById('activeBtn').classList.toggle('sel', wantActive);
    const seqInput = document.querySelector('input[name="sequence"]');
    if (seqInput) seqInput.value = data.sequence ?? '';

    // Question type (shows/hides pyr/code/cw fields + mcSections)
    setQType(data.question_type || 'multiple_choice');

    // template_text must be set BEFORE onThemeChange() runs — renderTextCorrectVar()/
    // renderTextOptChips() (which onThemeChange triggers) filter the variable list by
    // getUsedTemplateVars(), which reads directly from #templateText's current value.
    // Calling onThemeChange() first would filter against the still-empty field and
    // find nothing to select, silently losing the saved text-answer selection.
    document.getElementById('templateText').value = data.template_text || '';
    document.getElementById('hintText').value = data.hint_text || '';
    document.getElementById('correctFormula').value = (data.answer_type === 'text') ? '' : (data.correct_formula || '');

    // Theme + answer type (MC only)
    if (data.question_type === 'multiple_choice' || !data.question_type) {
        const themeSel = document.getElementById('themeSelect');
        themeSel.value = data.theme_id || '';
        onThemeChange(themeSel.value);
        setAnswerType(data.answer_type || 'numeric');
    }

    const numCfg = data.num_config || {};

    if (data.question_type === 'pyramid') {
        document.getElementById('pyrHeight').value = numCfg.height || 3;
        document.getElementById('pyrMax').value = numCfg.max_base || 9;
        document.getElementById('pyrHide').value = numCfg.hidden_count || 2;
        syncPyrConfig();
    } else if (data.question_type === 'code') {
        document.getElementById('codeSymCount').value = numCfg.symbol_count || 3;
        document.getElementById('codeMinVal').value = numCfg.min_val || 1;
        document.getElementById('codeMaxVal').value = numCfg.max_val || 9;
        const cops = numCfg.operators || ['+'];
        document.getElementById('codeOpPlus').checked  = cops.includes('+');
        document.getElementById('codeOpMinus').checked = cops.includes('-');
        document.getElementById('codeOpMul').checked   = cops.includes('×');
        document.getElementById('codeOpDiv').checked   = cops.includes('÷');
        document.getElementById('codeVarsPerEq').value = numCfg.vars_per_eq || 2;
        document.getElementById('codeUniqueVals').checked = !!numCfg.unique_values;
        syncCodeConfig();
    } else if (data.question_type === 'crossword') {
        const gridEl = document.getElementById('cwGrid' + (numCfg.rows || 2) + (numCfg.cols || 2));
        if (gridEl) gridEl.checked = true;
        document.getElementById('cwMinVal').value = numCfg.min_val || 1;
        document.getElementById('cwMaxVal').value = numCfg.max_val || 10;
        const wops = numCfg.operators || ['+'];
        document.getElementById('cwOpPlus').checked  = wops.includes('+');
        document.getElementById('cwOpMinus').checked = wops.includes('-');
        document.getElementById('cwOpMul').checked   = wops.includes('×');
        document.getElementById('cwOpDiv').checked   = wops.includes('÷');
        document.getElementById('cwRevealedCount').value = numCfg.revealed_count || 0;
        syncCwConfig();
    } else if (data.question_type === 'scale') {
        restoreScaleConfig(numCfg, data.answer_type, data.distractors || null, data.conditions || []);
    } else if (data.answer_type !== 'text') {
        // multiple_choice, numeric — rebuild ncRows from num_config the same way
        // the page-load restore does, then separately restore _unique/_auto.
        ncRows = [];
        const uq = document.getElementById('uniqueNumVars');
        if (uq) uq.checked = !!numCfg['_unique'];
        Object.entries(numCfg).forEach(([name, conf]) => {
            if (name.startsWith('_') || typeof conf !== 'object' || conf === null) return;
            if (conf.operator) {
                addNcRow(name, 1, 9, 1, 'operator', '', Array.isArray(conf.options) && conf.options.length ? conf.options : ['+']);
            } else if ('derived' in conf) {
                addNcRow(name, 1, 9, 1, 'derived', conf.derived ?? '');
            } else if ('conditional' in conf) {
                addNcRow(name, 1, 9, 1, 'conditional', '', [], conf.conditional ?? '');
            } else {
                addNcRow(name, conf.min ?? 1, conf.max ?? 9, conf.step ?? 1);
            }
        });
        if (!Object.keys(numCfg).filter(k => !k.startsWith('_')).length) {
            addNcRow('N1', 1, 9, 1);
            addNcRow('N2', 1, 9, 1);
        }
        const autoCfg = numCfg['_auto'];
        const autoEl = document.getElementById('autoBuildEnabled');
        if (autoCfg && autoCfg.enabled) {
            autoEl.checked = true;
            document.getElementById('autoVarCount').value = Math.min(5, Math.max(2,
                Object.keys(numCfg).filter(k => !k.startsWith('_')).length || 2));
            document.getElementById('autoParens').checked = !!autoCfg.parens;
            const aops = autoCfg.operators || ['+'];
            document.getElementById('autoOpPlus').checked  = aops.includes('+');
            document.getElementById('autoOpMinus').checked = aops.includes('-');
            document.getElementById('autoOpMul').checked   = aops.includes('×');
            document.getElementById('autoOpDiv').checked   = aops.includes('÷');
            document.getElementById('autoNoNegSteps').checked = !array_key_exists_js('no_negative_steps', autoCfg) || !!autoCfg.no_negative_steps;
            document.getElementById('autoMixBlank').checked   = !array_key_exists_js('mix_blank', autoCfg) || !!autoCfg.mix_blank;
        } else {
            autoEl.checked = false;
        }
        toggleAutoBuild();
    }

    // Conditions
    conditions = [];
    if (Array.isArray(data.conditions)) {
        data.conditions.forEach(c => addCond(c.left, c.op, c.right, true));
    } else {
        renderConds();
    }

    // Distractors (numeric) / text correct-var + option chips
    if (data.answer_type === 'text') {
        const cf = data.correct_formula || '';
        if (cf.includes('?') && cf.includes(':')) {
            if (!textCondMode) toggleTextCondMode();
            document.getElementById('textCondFormula').value = cf;
        } else {
            if (textCondMode) toggleTextCondMode();
            if (cf) document.getElementById('textCorrectVar').value = cf;
        }
        document.querySelectorAll('.text-opt-chip.sel').forEach(c => c.classList.remove('sel'));
        if (data.distractors && Array.isArray(data.distractors.vars)) {
            data.distractors.vars.forEach(v => {
                const chip = document.querySelector('.text-opt-chip[data-varname="' + v + '"]');
                if (chip) chip.classList.add('sel');
            });
        }
        renderTextCondChips();
    } else {
        const dist = data.distractors || {};
        const dMinEl = document.getElementById('distMin'), dMaxEl = document.getElementById('distMax');
        if (dMinEl) dMinEl.value = dist.min ?? 1;
        if (dMaxEl) dMaxEl.value = dist.max ?? 10;
        noneCorrect = !!dist.none_correct;
        const btn = document.getElementById('noneCorrectBtn');
        if (btn) { btn.classList.toggle('at-sel', noneCorrect); btn.textContent = noneCorrect ? '☑ არცერთი სწორეა' : '☐ არცერთი სწორეა'; }
    }

    syncAll();
    genPreview();
}
function array_key_exists_js(key, obj) { return obj && Object.prototype.hasOwnProperty.call(obj, key); }

// ── Init
(function init() {
    // Restore olympiad toggle visual state (edit mode or old() restore)
    if (document.getElementById('isOlympiadInput').value === '1') {
        document.getElementById('isOlympiadInput').value = '0'; // toggleOlympiad() flips it back to '1'
        toggleOlympiad();
    }

    // Load vars for the currently selected theme (edit mode or old() restore)
    const themeEl = document.getElementById('themeSelect');
    if (themeEl && themeEl.value) onThemeChange(themeEl.value);

    // Restore none_correct toggle (numeric mode)
    if (_KS.distractors && _KS.distractors.none_correct) {
        noneCorrect = true;
        const btn = document.getElementById('noneCorrectBtn');
        if (btn) { btn.classList.add('at-sel'); btn.textContent = '☑ არცერთი სწორეა'; }
    }

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
                applyGradeMaxLevel(gId);
                break;
            }
        }
    }

    // Restore answer type toggle
    const initType = _KS.answerType || 'numeric';
    document.getElementById('answerTypeInput').value = initType;
    const isTxtInit   = initType === 'text';
    const isInputInit = initType === 'numeric_input';
    document.getElementById('numAnsUi').style.display = isTxtInit ? 'none' : '';
    document.getElementById('txtAnsUi').style.display = isTxtInit ? '' : 'none';
    document.getElementById('distractorSection').style.display = isInputInit ? 'none' : '';
    document.getElementById('atBtnNum').classList.toggle('at-sel', initType === 'numeric');
    document.getElementById('atBtnInput').classList.toggle('at-sel', isInputInit);
    document.getElementById('atBtnTxt').classList.toggle('at-sel', isTxtInit);

    // The numConfig/addNcRow() restore below must run BEFORE the isTxtInit
    // block further down: it's what creates each numeric var's <option> in
    // #textCorrectVar and its <span class="text-opt-chip"> distractor chip
    // (via renderNcRows()'s renderTextCorrectVar()/renderTextOptChips() hook)
    // — restoring a saved selection against those elements before they exist
    // silently does nothing, so a bare numeric var name picked as the correct
    // answer (or as a distractor) would never show up again after a reload.
    const numCfg = _KS.numConfig;
    if (document.getElementById('qtInput').value === 'scale') {
        restoreScaleConfig(numCfg || {}, _KS.answerType, _KS.distractors, _KS.conditions);
    } else if (numCfg && typeof numCfg === 'object' && Object.keys(numCfg).length) {
        if (numCfg['_unique']) {
            const cb = document.getElementById('uniqueNumVars');
            if (cb) cb.checked = true;
        }
        Object.entries(numCfg).forEach(([name, conf]) => {
            if (name.startsWith('_') || typeof conf !== 'object' || conf === null) return;
            if (conf.operator) {
                addNcRow(name, 1, 9, 1, 'operator', '', Array.isArray(conf.options) && conf.options.length ? conf.options : ['+']);
            } else if ('derived' in conf) {
                addNcRow(name, 1, 9, 1, 'derived', conf.derived ?? '');
            } else if ('conditional' in conf) {
                addNcRow(name, 1, 9, 1, 'conditional', '', [], conf.conditional ?? '');
            } else {
                addNcRow(name, conf.min ?? 1, conf.max ?? 9, conf.step ?? 1);
            }
        });
        if (numCfg['_auto'] && numCfg['_auto'].enabled) {
            document.getElementById('autoBuildFields').style.display = '';
            document.getElementById('correctFormula').readOnly = true;
            document.getElementById('exprHint').style.display = '';
            document.getElementById('correctFormula').style.background = '#f8fafc';
        }
    } else if (!isTxtInit) {
        addNcRow('N1', 1, 9, 1);
        addNcRow('N2', 1, 9, 1);
    }

    // For text mode: restore correct var (plain) or conditional formula + selected option vars
    if (isTxtInit) {
        if (_KS.editCorrectFormula) {
            // resolveTextFormula() (PHP) accepts a plain var name, a ternary
            // ("A>B?X:Y"), or literal text with one or more {{VAR}} tokens
            // (e.g. "{{DEL1}} და {{DEL2}}") — only the first form fits the
            // plain dropdown below, so the other two both need the advanced
            // formula textbox, or an existing {{VAR}}-only formula would be
            // silently dropped (dropdown can't hold arbitrary text) and the
            // preview/answer would come up empty on every edit.
            if ((_KS.editCorrectFormula.includes('?') && _KS.editCorrectFormula.includes(':')) || _KS.editCorrectFormula.includes('{{')) {
                textCondMode = true;
                document.getElementById('textCorrectVar').style.display = 'none';
                document.getElementById('textCondWrap').style.display = '';
                document.getElementById('textCondToggle').classList.add('on');
                document.getElementById('textCondToggle').textContent = '🔗 ცვლადი';
                document.getElementById('textCondFormula').value = _KS.editCorrectFormula;
            } else {
                document.getElementById('textCorrectVar').value = _KS.editCorrectFormula;
            }
        }
        if (_KS.distractors && Array.isArray(_KS.distractors.vars)) {
            _KS.distractors.vars.forEach(function(varName) {
                // A saved "{{VAR}}"-style entry is a hand-typed template, not an
                // existing variable's chip -- restore it into textCustomOpts
                // instead (see addTextOptCustom()), since no chip for it exists.
                if (varName.includes('{{')) {
                    if (!textCustomOpts.includes(varName)) textCustomOpts.push(varName);
                    return;
                }
                const chip = document.querySelector('.text-opt-chip[data-varname="' + varName + '"]');
                if (chip) chip.classList.add('sel');
            });
            renderTextOptCustomChips();
        }
        const distCountEl = document.getElementById('textDistCount');
        if (distCountEl) distCountEl.value = (_KS.distractors && _KS.distractors.count) || 3;
    }
    const conds = _KS.conditions;
    if (Array.isArray(conds)) {
        conds.forEach(c => addCond(c.left, c.op, c.right, true));
    }
    if (isTxtInit) renderTextCondChips();
    syncAll();
    setTimeout(genPreview, 120);
})();

document.getElementById('mainForm').addEventListener('submit', function() {
    const _qt = document.getElementById('qtInput').value;
    if (_qt === 'pyramid') { syncPyrConfig(); }
    else if (_qt === 'code') { syncCodeConfig(); }
    else { syncAll(); }
});
</script>
@endverbatim
@endsection
