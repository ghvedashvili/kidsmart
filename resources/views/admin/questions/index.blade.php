@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 860px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    select.fc { cursor: pointer; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .q-list { display: flex; flex-direction: column; gap: 12px; }
    .q-row { padding: 14px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: border-color 0.15s, box-shadow 0.15s; }
    .q-row:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .q-row.trashed-row:hover { border-color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .q-title { font-weight: 700; color: #1e293b; font-size: 0.8rem; margin-bottom: 3px; }
    .q-text { color: #374151; font-size: 0.8rem; line-height: 1.5; margin-bottom: 0; }
    .var-num { color: #2563eb; font-weight: 700; background: #eff6ff; border-radius: 3px; padding: 0 4px; }
    .var-str { color: #7c3aed; }
    .q-meta { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 8px; }
    .q-tag { font-size: 0.65rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 7px; }
    .q-tag.muted { color: #94a3b8; }
    .q-tag.theme { color: #7c3aed; border-color: #e9d5ff; }
    .q-tag.oly { color: #b45309; border-color: #fde68a; }
    .q-seq { width: 40px; height: 28px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #2563eb; font-family: 'Goldman', monospace; font-size: 0.68rem; padding: 2px 3px; text-align: center; outline: none; box-sizing: border-box; }
    .q-seq:focus { border-color: #6366f1; }
    .q-seq.saved { border-color: #86efac; }
    .q-actions { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; }
    .q-actions-left, .q-actions-right { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
    .q-action-btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.76rem; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s; padding: 0; line-height: 1; }
    .q-action-btn:hover { border-color: #94a3b8; color: #1e293b; background: #fff; }
    .q-status-btn { font-family: 'Goldman', monospace; font-size: 0.68rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 11px; cursor: pointer; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 3px; transition: all 0.15s; line-height: 1; }
    .q-status-btn:hover { border-color: #94a3b8; color: #1e293b; }
    .q-status-btn.active { color: #059669; border-color: #bbf7d0; background: #f0fdf4; }
    .q-status-btn.active:hover { border-color: #059669; }
    .q-status-btn.inactive { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .q-status-btn.danger { color: #cbd5e1; border: none; background: none; padding: 6px 7px; }
    .q-status-btn.danger:hover { color: #ef4444; }
    .q-status-btn.restore { color: #059669; border-color: #86efac; background: #f0fdf4; }
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

    /* reassign modal */
    .rs-lbl { font-size:0.6rem; color:#94a3b8; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:5px; }
    .rs-diff-row { display:flex; gap:6px; margin-bottom:4px; }
    .rs-diff-btn { background:#f8fafc; border:1px solid #e2e8f0; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.78rem; padding:7px 14px; border-radius:4px; cursor:pointer; transition:all 0.15s; }
    .rs-diff-btn.sel { border-color:#4f46e5; color:#4f46e5; background:#eef2ff; }
    .rs-err { font-size:0.72rem; color:#ef4444; margin-bottom:8px; display:none; }
    .rs-hint { font-size:0.62rem; color:#94a3b8; margin:-4px 0 10px; display:none; }
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
    .lp-scale-row { display:flex; align-items:flex-start; justify-content:center; gap:8px; flex-wrap:nowrap; margin-bottom:8px; padding:0 4px; }
    .lp-scale-pan { display:flex; flex-wrap:nowrap; gap:2px; align-items:flex-end; justify-content:center; min-width:60px; min-height:34px; overflow:visible; }
    .lp-scale-emoji { font-size:1.15rem; line-height:1; flex-shrink:0; }
    .lp-scale-emoji.lp-scale-blank { border:2px dashed #94a3b8; border-radius:7px; padding:2px 4px; text-align:center; }
    .lp-scale-emoji.lp-scale-blank span { font-size:0.5rem; color:#94a3b8; display:block; line-height:1; }
    .lp-scale-wplate { width:26px; height:26px; border-radius:6px; background:#4f46e5; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.7rem; flex-shrink:0; }
    .lp-scale-wplate.lp-scale-blank { background:#f8fafc; border:2px dashed #94a3b8; color:#94a3b8; }
    .lp-scale-cond { background:#eef2ff; border:1.5px dashed #a5b4fc; border-radius:8px; padding:6px 10px; text-align:center; font-size:0.74rem; font-weight:700; color:#4338ca; margin-bottom:10px; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .filters { gap: 6px; }
        .filters select, .filters input { font-size: 0.7rem; padding: 6px 8px; }
        .q-row { padding: 10px; }
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

    <div class="q-list">
        @forelse($templates as $tpl)
        <div class="q-row {{ $showTrashed ? 'trashed-row' : '' }}" style="display:flex;gap:10px;align-items:flex-start;{{ (!$showTrashed && !$tpl->is_active) ? 'opacity:0.5;' : '' }}">
            @unless($showTrashed)
            <input type="checkbox" class="q-check q-item" value="{{ $tpl->id }}" style="margin-top:3px;" onchange="updateBar()">
            @endunless
            <div style="flex:1;min-width:0;">
            @if($tpl->title)
            <div class="q-title">★ {{ $tpl->title }}</div>
            @endif
            <div class="q-text q-preview"
                data-tpl="{{ $tpl->template_text }}"
                data-nc='@json($tpl->num_config ?? [])'>{{ $tpl->template_text }}</div>

            <div class="q-meta">
                <span class="q-tag muted">#{{ $tpl->id }}</span>
                <span class="q-tag">{{ $tpl->topic->grade->name }}</span>
                <span class="q-tag">{{ $tpl->topic->name }}</span>
                @if($tpl->theme)
                <span class="q-tag theme">{{ $tpl->theme->icon ?? '' }} {{ $tpl->theme->name }}</span>
                @endif
                @if($tpl->is_olympiad)
                <span class="q-tag oly">🏆 ოლიმპიადა</span>
                @else
                <span class="q-tag">დონე {{ $tpl->difficulty }}</span>
                @endif
                <span class="q-tag muted">= {{ $tpl->correct_formula }}</span>
            </div>

            <div class="q-actions">
                <div class="q-actions-left">
                    @unless($showTrashed)
                    <input type="number" class="q-seq" min="1" placeholder="—" value="{{ $tpl->sequence }}"
                        data-tpl-id="{{ $tpl->id }}" onchange="saveQSequence(this)" title="რიგითობა თემაში">
                    @endunless
                    <button type="button" class="q-action-btn" onclick="regenPreview(this)" title="ახალი მაგალითი">↺</button>
                    <button type="button" class="q-action-btn" onclick="openLivePreview({{ $tpl->id }})" title="ლაივ პრევიუ">👁</button>
                    @unless($showTrashed)
                    @php
                        $rsData = [
                            'data-id'         => $tpl->id,
                            'data-grade'      => $tpl->topic->grade_id,
                            'data-topic'      => $tpl->topic_id,
                            'data-theme'      => $tpl->theme_id,
                            'data-difficulty' => $tpl->difficulty,
                            'data-qtype'      => $tpl->question_type,
                            'data-olympiad'   => $tpl->is_olympiad ? 1 : 0,
                        ];
                    @endphp
                    <button type="button" class="q-action-btn" @foreach($rsData as $k => $v) {{ $k }}="{{ $v }}" @endforeach
                        onclick="openReassign(this,'move')" title="კლასის/თემის/თემატიკის/დონის შეცვლა">⇄</button>
                    <button type="button" class="q-action-btn" @foreach($rsData as $k => $v) {{ $k }}="{{ $v }}" @endforeach
                        onclick="openReassign(this,'copy')" title="დუბლირება სხვა კლასში/თემაში">⎘</button>
                    @endunless
                </div>
                <div class="q-actions-right">
                    @if($showTrashed)
                    <form method="POST" action="{{ route('admin.questions.restore', $tpl->id) }}">
                        @csrf
                        <button type="submit" class="q-status-btn restore">↺ აღდგენა</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.questions.toggle-active', $tpl) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="q-status-btn {{ $tpl->is_active ? 'active' : 'inactive' }}" title="{{ $tpl->is_active ? 'გაჩერება' : 'გააქტიურება' }}">{{ $tpl->is_active ? '✓ აქტიური' : '⏸ გაჩერებული' }}</button>
                    </form>
                    <a href="{{ route('admin.questions.edit', $tpl) }}" class="q-status-btn">✎ რედ.</a>
                    <form method="POST" action="{{ route('admin.questions.destroy', $tpl) }}">
                        @csrf @method('DELETE')
                        <button type="button" class="q-status-btn danger" onclick="confirmDeleteQuestion(this)">✕</button>
                    </form>
                    @endif
                </div>
            </div>
            </div>
        </div>
        @empty
        <div class="q-row" style="color:#94a3b8;font-size:0.78rem;">შაბლონი არ არის</div>
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

{{-- Reassign modal: change grade/topic/theme/difficulty without opening the full edit form --}}
<div class="lp-overlay" id="rsOverlay" onclick="rsOverlayClick(event)">
    <div class="lp-box">
        <div class="lp-head">
            <span id="rsTitle">კლასი · თემა · თემატიკა · დონე</span>
            <button type="button" class="lp-close" onclick="closeReassign()">✕</button>
        </div>
        <div class="lp-body">
            <div class="rs-lbl">კლასი</div>
            <select id="rsGrade" class="fc" style="margin-bottom:10px;width:100%;" onchange="onRsGradeChange()">
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" data-max-level="{{ $grade->max_level }}">{{ $grade->name }}</option>
                @endforeach
            </select>
            <div class="rs-lbl">თემა</div>
            <select id="rsTopic" class="fc" style="margin-bottom:10px;width:100%;"></select>
            <div class="rs-lbl" id="rsThemeWrap">თემატიკა</div>
            <select id="rsTheme" class="fc" style="margin-bottom:10px;width:100%;">
                <option value="">— არავითარი —</option>
                @foreach($themes as $theme)
                <option value="{{ $theme->id }}">{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                @endforeach
            </select>
            <div class="rs-lbl">დონე</div>
            <div class="rs-diff-row" id="rsDiffRow">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" class="rs-diff-btn" data-d="{{ $i }}" onclick="setRsDiff({{ $i }})">{{ $i }}</button>
                @endfor
            </div>
            <div class="rs-hint" id="rsMaxHint"></div>
            <div class="rs-err" id="rsErr"></div>
        </div>
        <div class="lp-foot" style="display:flex;gap:8px;">
            <button type="button" class="btn" id="rsSaveBtn" onclick="saveReassign()">შენახვა</button>
            <button type="button" class="btn" onclick="closeReassign()">გაუქმება</button>
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

function confirmDeleteQuestion(btn) {
    const form = btn.closest('form');
    Swal.fire({
        title: 'შაბლონის წაშლა?',
        html: '<span style="font-size:0.9rem;color:#555;">აღდგენა შესაძლებელია „წაშლილების" სიიდან.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'დიახ, წავშალო',
        cancelButtonText: 'გაუქმება',
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
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

// ── Reassign modal — change grade (via topic)/topic/theme/difficulty from the
// list without opening the full edit form. Reuses _topicsByGrade already built
// above for the filter bar's grade→topic cascade.
let rsId = null, rsDiff = 1, rsIsOlympiad = false, rsQType = 'multiple_choice', rsMode = 'move';

function openReassign(btn, mode) {
    rsId         = btn.dataset.id;
    rsMode       = mode || 'move';
    rsDiff       = +btn.dataset.difficulty || 1;
    rsIsOlympiad = btn.dataset.olympiad === '1';
    rsQType      = btn.dataset.qtype;
    const gradeId = btn.dataset.grade;
    const topicId = btn.dataset.topic;
    const themeId = btn.dataset.theme;

    document.getElementById('rsGrade').value = gradeId;
    rebuildRsTopics(gradeId, topicId);
    applyRsMaxLevel(gradeId);
    setRsDiff(rsDiff);

    const themeSel = document.getElementById('rsTheme');
    const isSpecial = ['pyramid', 'code', 'crossword', 'scale'].includes(rsQType);
    themeSel.disabled = isSpecial;
    document.getElementById('rsThemeWrap').style.opacity = isSpecial ? '0.4' : '1';
    themeSel.value = isSpecial ? '' : (themeId || '');

    document.getElementById('rsTitle').textContent = rsMode === 'copy'
        ? '⎘ დუბლირება სხვაგან'
        : '⇄ კლასი · თემა · თემატიკა · დონე';
    document.getElementById('rsSaveBtn').textContent = rsMode === 'copy' ? 'დუბლირება' : 'შენახვა';

    document.getElementById('rsErr').style.display = 'none';
    document.getElementById('rsOverlay').classList.add('open');
}
function closeReassign() {
    document.getElementById('rsOverlay').classList.remove('open');
}
function rsOverlayClick(e) {
    if (e.target === document.getElementById('rsOverlay')) closeReassign();
}
function onRsGradeChange() {
    const gradeId = document.getElementById('rsGrade').value;
    rebuildRsTopics(gradeId, null);
    applyRsMaxLevel(gradeId);
}
function rebuildRsTopics(gradeId, keepTopicId) {
    const sel = document.getElementById('rsTopic');
    sel.innerHTML = '';
    (_topicsByGrade[gradeId] || []).forEach(t => {
        const opt = new Option(t.name, t.id);
        if (keepTopicId && String(t.id) === String(keepTopicId)) opt.selected = true;
        sel.add(opt);
    });
}
function applyRsMaxLevel(gradeId) {
    const opt = document.querySelector('#rsGrade option[value="' + gradeId + '"]');
    const max = rsIsOlympiad ? 5 : (opt ? (+opt.dataset.maxLevel || 5) : 5);
    document.querySelectorAll('#rsDiffRow .rs-diff-btn').forEach(b => {
        b.style.display = (+b.dataset.d) <= max ? '' : 'none';
    });
    const hint = document.getElementById('rsMaxHint');
    if (!rsIsOlympiad && max < 5) {
        hint.textContent = 'ამ კლასს მაქსიმუმ ' + max + ' დონე აქვს';
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
    if (rsDiff > max) setRsDiff(max);
}
function setRsDiff(d) {
    rsDiff = d;
    document.querySelectorAll('#rsDiffRow .rs-diff-btn').forEach(b => {
        b.classList.toggle('sel', +b.dataset.d === d);
    });
}
async function saveReassign() {
    const topicId = document.getElementById('rsTopic').value;
    const themeVal = document.getElementById('rsTheme').value;
    const errEl = document.getElementById('rsErr');
    errEl.style.display = 'none';
    if (!topicId) {
        errEl.textContent = 'აირჩიე თემა';
        errEl.style.display = 'block';
        return;
    }
    try {
        const url = rsMode === 'copy'
            ? `/admin/questions/${rsId}/duplicate`
            : `/admin/questions/${rsId}/reassign`;
        const r = await fetch(url, {
            method: rsMode === 'copy' ? 'POST' : 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ topic_id: topicId, theme_id: themeVal || null, difficulty: rsDiff }),
        });
        const data = await r.json();
        if (!r.ok) {
            errEl.textContent = data.error || 'შეცდომა — არ შეინახა';
            errEl.style.display = 'block';
            return;
        }
        window.location.reload();
    } catch (e) {
        errEl.textContent = 'დაკავშირების შეცდომა';
        errEl.style.display = 'block';
    }
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
    if (qt === 'scale')     return renderLpScale(data);
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

// Mirrors ScaleService::scaleSvg() (PHP) — same static, level triangle+beam frame.
// Mirrors ScaleService::scaleSvg() (PHP) — beam tilts toward the heavier side
// around a fixed triangle stand; no platform/tray.
// Mirrors ScaleService::scaleSvg() (PHP) — straight, level beam resting on a
// fixed triangle stand; items render right above it, sitting on top of the beam.
function scaleSvgHtmlLp() {
    return '<svg viewBox="0 0 280 60" style="display:block;width:100%;height:auto;overflow:visible;">'
        + '<line x1="10" y1="10" x2="270" y2="10" stroke="#1a7a3c" stroke-width="5" stroke-linecap="round"/>'
        + '<circle cx="140" cy="10" r="6" fill="#1a7a3c"/>'
        + '<polygon points="118,58 162,58 140,16" fill="#1a7a3c"/>'
        + '</svg>';
}

// Every occurrence gets its OWN chip (never a single text blob with the symbol
// repeated N times) — lets the nowrap row size each one individually instead of
// risking the browser line-wrapping a multi-emoji string.
function renderLpScalePan(items) {
    let html = '';
    (items || []).forEach(it => {
        const count = Math.max(1, it.count || 1);
        for (let i = 0; i < count; i++) {
            html += it.kind === 'weight'
                ? `<div class="lp-scale-wplate">${escHtmlLp(String(it.value ?? '?'))}</div>`
                : `<div class="lp-scale-emoji">${it.symbol || '❓'}</div>`;
        }
    });
    return html;
}

function renderLpScaleBlank(kind, symbol) {
    // MC mode never names the answer symbol in advance (null symbol) -- same
    // as a weight-kind blank, a single "?" plate is the whole clue. Falling
    // back to the generic "❓" emoji AND still appending the small "how
    // many?" subscript below it prints two question marks.
    if (kind === 'weight' || !symbol) return `<div class="lp-scale-wplate lp-scale-blank">?</div>`;
    return `<div class="lp-scale-emoji lp-scale-blank">${escHtmlLp(symbol)}<span>?</span></div>`;
}

function renderLpScale(data) {
    let q;
    try { q = JSON.parse(data.question_text); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }

    const blankChip = renderLpScaleBlank(q.blank_kind, q.blank_symbol);
    const leftHtml  = renderLpScalePan(q.left)  + (q.blank_side === 'left'  ? blankChip : '');
    const rightHtml = renderLpScalePan(q.right) + (q.blank_side === 'right' ? blankChip : '');

    let html = `<div class="lp-q">⚖️ ${escHtmlLp(q.prompt || '')}</div>`;
    html += `<div style="max-width:260px;margin:0 auto 8px;"><div class="lp-scale-row" style="justify-content:space-between;align-items:flex-end;margin-bottom:2px;">`
        + `<div class="lp-scale-pan" style="flex:1;">${leftHtml}</div>`
        + `<div class="lp-scale-pan" style="flex:1;">${rightHtml}</div>`
        + `</div>${scaleSvgHtmlLp()}</div>`;

    if (Array.isArray(data.options) && data.options.length) {
        html += '<div class="lp-opts">' + data.options.map(o =>
            `<div class="lp-opt ${o === data.correct_answer ? 'correct' : ''}">${escHtmlLp(o)}</div>`
        ).join('') + '</div>';
    } else {
        html += `<div class="lp-answer">✍️ პასუხი: <b>${escHtmlLp(data.correct_answer)}</b></div>`;
    }
    return html;
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
