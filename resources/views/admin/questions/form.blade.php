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
                    $codeCfg = ($existingQType === 'code') ? ($template->num_config ?? []) : [];
                    $cwCfg   = ($existingQType === 'crossword') ? ($template->num_config ?? []) : [];
                @endphp

                <div class="lbl">სათაური <span style="color:#94a3b8;font-size:0.6rem;">(მხოლოდ ადმინისთვის — ტესტში ბავშვს არ გამოუჩნდება)</span></div>
                <input type="text" name="title" id="titleInput" class="fc" maxlength="150"
                    placeholder="მაგ: ორნიშნა რიცხვის პოვნა — 2 ციფრის წაშლა"
                    value="{{ old('title', $template?->title) }}">

                <div class="lbl">კითხვის ტიპი</div>
                <div class="qt-row">
                    <button type="button" class="qt-btn {{ !$isExistingPyramid && !$isExistingCode && !$isExistingCw ? 'sel' : '' }}" onclick="setQType('multiple_choice')">📝 Multiple Choice</button>
                    <button type="button" class="qt-btn {{ $isExistingPyramid ? 'sel' : '' }}" onclick="setQType('pyramid')">🔺 პირამიდა</button>
                    <button type="button" class="qt-btn {{ $isExistingCode ? 'sel' : '' }}" onclick="setQType('code')">🕵️ კოდი</button>
                    <button type="button" class="qt-btn {{ $isExistingCw ? 'sel' : '' }}" onclick="setQType('crossword')">🧮 კროსვორდი</button>
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
                    <div style="font-size:0.62rem;color:#94a3b8;margin-top:4px;">ƒ/± ღილაკი ცვლის ტიპს: შემთხვევითი → „გამოთვლილი" (მაგ. N1+3) → „ოპერატორი" (ით მეტი/ნაკლები, ჯერ მეტი/ნაკლები — ფორმულაში ნიშნად, ტექსტში სიტყვად გამოჩნდება)</div>
                    <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:0.75rem;font-weight:700;color:#374151;cursor:pointer;">
                        <input type="checkbox" id="uniqueNumVars" onchange="syncAll();previewDebounce();">
                        ყველა ცვლადი განსხვავებული
                    </label>
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
                        </div>
                        <input type="text" id="correctFormula" class="fc"
                            placeholder="N1+N2"
                            value="{{ old('answer_type', $template?->answer_type) !== 'text' ? old('correct_formula', $template?->correct_formula) : '' }}"
                            oninput="previewDebounce()">
                        <div class="hint">მაგ: N1+N2 &nbsp;·&nbsp; (N1+N2)*N3 &nbsp;·&nbsp; N1*N2-N3 &nbsp;·&nbsp; N1%N2</div>
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
                            <div class="hint">პირობა რიცხვით ცვლადებზე (&gt;, &lt;, &gt;=, &lt;=, ==, !=), მერე ? და : შორის — თითო მხარეს ცვლადის სახელი ან ტექსტი. ჯაჭვურადაც შეიძლება: A&gt;B?X:C&gt;D?Y:Z</div>
                        </div>

                        <div class="lbl" style="margin-top:4px;">ვარიანტების ცვლადები <span style="color:#94a3b8;font-size:0.6rem;">(კლიკით)</span></div>
                        <div id="textOptChips"></div>
                        <div id="noVarsHint2" style="display:none;font-size:0.6rem;color:#94a3b8;margin-top:4px;">ცვლადები არ არის</div>
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
function onThemeChange(themeId) {
    const data = (themeId && _KS.allThemesData[themeId])
        ? _KS.allThemesData[themeId]
        : { groups: [], standalone: [], varMap: {} };
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
    const specOg = document.createElement('optgroup');
    specOg.label = '──────';
    Object.entries(SPEC).forEach(function(e) { specOg.appendChild(new Option(e[1], e[0])); });
    sel.appendChild(specOg);
    if (prevVal) sel.value = prevVal;
    const hasVars = data.groups.some(function(g) { return g.slots.some(function(s) { return used.has(s); }); }) || sa.length;
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
    html += '<div class="chips" style="margin-top:6px;border-top:1px solid #f1f5f9;padding-top:6px;">'
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
            (type === 'crossword'       && b.textContent.includes('კროსვორდი'))) {
            b.classList.add('sel');
        }
    });
    applyQType(type);
}
function applyQType(type) {
    const isPyr = type === 'pyramid';
    const isCode = type === 'code';
    const isCw  = type === 'crossword';
    const isMC  = !isPyr && !isCode && !isCw;
    document.getElementById('pyrFields').style.display      = isPyr  ? 'block' : 'none';
    document.getElementById('codeFields').style.display     = isCode ? 'block' : 'none';
    document.getElementById('cwFields').style.display       = isCw   ? 'block' : 'none';
    document.getElementById('themeFieldWrap').style.display = isMC   ? 'block' : 'none';
    document.getElementById('mcSections').style.display     = isMC   ? 'block' : 'none';
    if (isPyr)  { syncPyrConfig();  return; }
    if (isCode) { syncCodeConfig(); return; }
    if (isCw)   { syncCwConfig();   return; }
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
        const optVars = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname);
        document.getElementById('distractorsJson').value = JSON.stringify({ vars: optVars });
    } else {
        document.getElementById('correctFormulaHidden').value = document.getElementById('correctFormula').value;
        const dMin = +document.getElementById('distMin').value || 1;
        const dMax = +document.getElementById('distMax').value || 10;
        document.getElementById('distractorsJson').value = JSON.stringify({ min: dMin, max: dMax, none_correct: noneCorrect || false });
    }
}

// ── Condition evaluator (supports expressions like N2+N3)
function evalExpr(expr, numVars) {
    let e = String(expr);
    Object.entries(numVars).forEach(([k, v]) => { e = e.replaceAll(k, String(v)); });
    e = e.replace(/[^0-9+\-*/()\s%]/g, '');
    try { const r = Function('return (' + e + ')')(); return Number.isFinite(r) ? Math.floor(r) : 0; }
    catch(_) { return 0; }
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
    const qt = document.getElementById('qtInput').value;
    if (qt === 'pyramid')   { genPreviewPyramid(); return; }
    if (qt === 'code')      { genPreviewCode();     return; }
    if (qt === 'crossword') { genPreviewCw();       return; }
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
    const optVarNames = [...document.querySelectorAll('.text-opt-chip.sel')].map(c => c.dataset.varname);
    const seen = new Set();
    const opts = [];
    optVarNames.forEach(v => {
        const val = SPEC[v] || themeMap[v] || '?';
        if (!seen.has(val)) { seen.add(val); opts.push({ v: val, c: val === correct }); }
    });
    if (!seen.has(correct)) opts.unshift({ v: correct, c: true });
    for (let i = opts.length-1; i > 0; i--) {
        const j = Math.floor(Math.random()*(i+1)); [opts[i],opts[j]]=[opts[j],opts[i]];
    }

    document.getElementById('prevOpts').innerHTML = opts.map(o =>
        `<div class="preview-opt ${o.c?'c':''}">${o.v}</div>`).join('');
    document.getElementById('prevFormula').innerHTML =
        `სწ. პასუხი: <span style="color:#2a7a2a;">${correct}</span> <span style="color:#94a3b8;">(${formula})</span>`;
    document.getElementById('prevVars').innerHTML = Object.entries({...themeMap, ...numVars}).map(([k,v]) => `${k}=${v}`).join(' · ');
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
        f = f.replace(/[^0-9a-z+\-*/()\s%.]/gi, '');
        try {
            const round = Math.round, floor = Math.floor, ceil = Math.ceil, abs = Math.abs;
            const raw = Function('round', 'floor', 'ceil', 'abs', 'return (' + f + ')')(round, floor, ceil, abs);
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

    // For text mode: restore correct var (plain) or conditional formula + selected option vars
    if (isTxtInit) {
        if (_KS.editCorrectFormula) {
            if (_KS.editCorrectFormula.includes('?') && _KS.editCorrectFormula.includes(':')) {
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
                const chip = document.querySelector('.text-opt-chip[data-varname="' + varName + '"]');
                if (chip) chip.classList.add('sel');
            });
        }
    }

    const numCfg = _KS.numConfig;
    if (numCfg && typeof numCfg === 'object' && Object.keys(numCfg).length) {
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
