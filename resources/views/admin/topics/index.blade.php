@extends('layouts.app')
{{-- redesign-kidsmart: თემები (დიზაინი A4) --}}
@section('content')
<style>
    /* redesign-kidsmart: თემები (დიზაინი A4) — იგივე კლასები, რასაც გვერდის JS იყენებს */
    .topics-list { display: flex; flex-direction: column; gap: 8px; }
    .grade-group { margin: 10px 0 2px; font-size: 13px; font-weight: 800; color: var(--ks-muted); text-transform: uppercase; letter-spacing: .5px; }
    .grade-group:first-child { margin-top: 0; }
    .row { margin: 0 !important; display: flex; background: #fff; border: 1px solid var(--ks-line); border-radius: 18px; padding: 12px 14px; }
    .row-view { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 15px; font-weight: 800; }
    .row-edit { display: none; align-items: center; gap: 6px; }
    .row.editing { border-color: var(--ks-orange); }
    .row.editing .row-view { display: none; }
    .row.editing .row-edit { display: flex; }
    .badge { display: inline-flex; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 4px 10px; background: #F1EFEB; color: var(--ks-ink-2); border: none; }
    .badge-btn { display: inline-flex; font-family: var(--ks-font); font-size: 12px; font-weight: 800; border-radius: 999px; padding: 4px 10px; background: var(--ks-purple-soft); color: var(--ks-purple-dark); border: none; cursor: pointer; }
    .badge-btn:hover { background: #FFF3EA; }
    .btn-vid { min-height: 34px; padding: 0 10px; border-radius: 10px; border: none; background: #F1EFEB; color: var(--ks-ink-2); font-family: var(--ks-font); font-size: 12px; font-weight: 800; cursor: pointer; }
    .btn-vid.has { background: var(--ks-blue-soft); color: var(--ks-blue-dark); }
    .btn-edit { min-width: 34px; min-height: 34px; border-radius: 10px; border: none; background: transparent; color: var(--ks-ink-2); cursor: pointer; font-size: 14px; }
    .btn-edit:hover { background: #F1EFEB; color: var(--ks-ink); }
    .btn-del { min-width: 34px; min-height: 34px; border-radius: 10px; border: none; background: transparent; color: var(--ks-muted); cursor: pointer; font-size: 13px; font-weight: 800; }
    .btn-del:hover { background: var(--ks-red-soft); color: var(--ks-red-dark); }
    .fc-inline { min-height: 38px; border-radius: 10px; border: 1px solid var(--ks-line); background: var(--ks-bg-admin); padding: 0 10px; font-family: var(--ks-font); font-size: 14px; color: var(--ks-ink); outline: none; }
    .fc-inline:focus { border-color: var(--ks-orange); }
    .btn-save { min-height: 38px; padding: 0 14px; border-radius: 10px; border: none; background: var(--ks-orange-strong); color: #fff; font-family: var(--ks-font); font-size: 13px; font-weight: 800; cursor: pointer; white-space: nowrap; }
    .btn-save:hover { background: #A63E07; }
    .btn-cancel-row { min-width: 38px; min-height: 38px; border-radius: 10px; border: 1px solid var(--ks-line); background: #fff; cursor: pointer; font-weight: 800; color: var(--ks-muted); }
    .vid-panel, .tpl-panel { display: none; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--ks-line-soft); }
    .vid-item { display: flex; align-items: center; gap: 10px; background: var(--ks-bg-admin); border-radius: 12px; padding: 8px 10px; margin-bottom: 6px; }
    .vid-thumb { width: 64px; height: 36px; border-radius: 6px; object-fit: cover; }
    .vid-title { font-size: 13px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .vid-id { font-size: 11px; color: var(--ks-muted); font-family: var(--ks-mono); }
    .vid-add-form { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .vid-err { margin-top: 6px; font-size: 12px; font-weight: 700; color: var(--ks-red-dark); }
    .tpl-hint { font-size: 12px; color: var(--ks-muted); margin-bottom: 8px; }
    .tpl-item { display: flex; align-items: center; gap: 8px; background: var(--ks-bg-admin); border-radius: 12px; padding: 8px 10px; margin-bottom: 6px; flex-wrap: wrap; }
    .tpl-seq { width: 52px; min-height: 34px; border-radius: 8px; border: 1px solid var(--ks-line); background: #fff; padding: 0 6px; font-family: var(--ks-font); font-size: 13px; font-weight: 800; }
    .tpl-seq.saved { border-color: var(--ks-green); background: var(--ks-green-soft); }
    .tpl-text { flex: 1 1 200px; min-width: 0; font-size: 14px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tpl-tag { font-size: 11px; font-weight: 800; border-radius: 8px; padding: 3px 8px; background: #fff; border: 1px solid var(--ks-line); color: var(--ks-ink-2); }
    .tpl-edit { font-size: 12px; font-weight: 800; color: var(--ks-orange-dark); text-decoration: none; padding: 6px; }
    /* მოდალები */
    .tp-overlay, .mv-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(29, 26, 23, .45); display: none; align-items: center; justify-content: center; padding: 16px; }
    .tp-overlay.open, .mv-overlay.open { display: flex; }
    .tp-box, .mv-box { width: 100%; max-width: 460px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 22px; padding: 20px; display: flex; flex-direction: column; gap: 8px; font-family: var(--ks-font); }
    .tp-title, .mv-head { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
    .tp-lbl, .mv-lbl { font-size: 13px; font-weight: 700; color: var(--ks-ink-2); }
    .tp-fc, .mv-fc { width: 100%; min-height: 44px; box-sizing: border-box; border-radius: 12px; border: 1px solid var(--ks-line); background: var(--ks-bg-admin); padding: 0 12px; font-family: var(--ks-font); font-size: 14px; margin-bottom: 6px; }
    .tp-fc:focus, .mv-fc:focus { border-color: var(--ks-orange); outline: none; }
    .tp-actions, .mv-foot { display: flex; gap: 8px; margin-top: 6px; }
    .tp-btn-add, .mv-btn-go { min-height: 44px; padding: 0 18px; border-radius: 12px; border: none; background: var(--ks-orange-strong); color: #fff; font-family: var(--ks-font); font-size: 14px; font-weight: 800; cursor: pointer; }
    .mv-btn-go:disabled { opacity: .5; cursor: not-allowed; }
    .tp-btn-cancel, .mv-btn-x { min-height: 44px; padding: 0 16px; border-radius: 12px; border: 1px solid var(--ks-line); background: #fff; font-family: var(--ks-font); font-size: 14px; font-weight: 700; cursor: pointer; }
    .mv-topics-hdr { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
    .mv-select-all { border: none; background: none; color: var(--ks-orange-dark); font-family: var(--ks-font); font-size: 12px; font-weight: 800; cursor: pointer; padding: 6px; }
    .mv-list { display: flex; flex-direction: column; gap: 4px; max-height: 280px; overflow-y: auto; }
    .mv-row { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; background: var(--ks-bg-admin); }
    .mv-row input { width: 18px; height: 18px; accent-color: #C24A0A; }
    .mv-row.inactive { opacity: .55; }
    .mv-name { flex-grow: 1; font-size: 14px; font-weight: 700; }
    .mv-cnt { font-size: 12px; color: var(--ks-muted); }
    .mv-reason { font-size: 11px; font-weight: 800; color: var(--ks-orange-dark); }
</style>

<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">რიგი განსაზღვრავს, რა თანმიმდევრობით ხვდება თემები ბავშვს</span>
        <h1 class="ks-display ks-h1">თემები</h1>
    </div>
    <button type="button" class="ks-btn ks-btn-primary" onclick="openAddModal()">+ ახალი თემა</button>
</header>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif

<div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
    <div class="ks-field" style="width:220px;">
        <label class="ks-label" for="gradeFilter">კლასი</label>
        <select id="gradeFilter" class="ks-select" onchange="filterGrade(this.value)">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" class="ks-btn ks-btn-ghost" id="moveBtn" style="display:none;" onclick="openMoveModal()">სხვა კლასში კოპირება</button>
    <span class="ks-muted" style="margin-left:auto;font-size:13px;">ნაჩვენებია: <b id="topicCount">{{ $topics->count() }}</b> თემა</span>
</div>

<div class="topics-list">
        @php $lastGrade = null; @endphp
        @forelse($topics as $topic)
            @if($lastGrade !== $topic->grade_id)
                @if($lastGrade !== null)<div style="height:4px;"></div>@endif
                <div class="grade-group grade-hdr" data-grade="{{ $topic->grade_id }}">{{ $topic->grade->name }}</div>
                @php $lastGrade = $topic->grade_id; @endphp
            @endif
            <div class="row topic-row" id="topic-row-{{ $topic->id }}" data-grade="{{ $topic->grade_id }}" style="flex-direction:column;align-items:stretch;gap:0;{{ $topic->is_active ? '' : 'opacity:0.5;' }}">
                <div style="display:flex;align-items:center;gap:4px;">
                    <div class="row-view" style="flex:1;">
                        <span>{{ $topic->name }}</span>
                        @if($topic->sequence)
                        <span class="badge" style="color:#C24A0A;border-color:#FFE3CC;">#{{ $topic->sequence }}</span>
                        @endif
                        @unless($topic->is_active)
                        <span class="badge" style="color:#b91c1c;border-color:#fecaca;">⏸ გაჩერებული</span>
                        @endunless
                        @if($topic->question_templates_count)
                        <button type="button" class="badge-btn" onclick="toggleTplPanel({{ $topic->id }})">{{ $topic->question_templates_count }} შაბლონი</button>
                        @else
                        <span class="badge">0 შაბლონი</span>
                        @endif
                    </div>
                    <button type="button" class="btn-vid {{ $topic->videos->count() ? 'has' : '' }}"
                        onclick="toggleVidPanel({{ $topic->id }})">
                        📹 {{ $topic->videos->count() ?: '' }}
                    </button>
                    <div style="display:flex;gap:2px;flex-shrink:0;">
                        <form method="POST" action="{{ route('admin.topics.toggle-active', $topic) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-edit" title="{{ $topic->is_active ? 'გაჩერება — ბავშვები ახალ კითხვებს ვეღარ მიიღებენ' : 'გააქტიურება' }}">{{ $topic->is_active ? '⏸' : '▶' }}</button>
                        </form>
                        <button type="button" class="btn-edit" onclick="editTopic({{ $topic->id }})">✎</button>
                        <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                        </form>
                    </div>
                    <div class="row-edit" style="flex:1;">
                        <form method="POST" action="{{ route('admin.topics.update', $topic) }}" style="display:flex;gap:6px;flex:1;align-items:center;">
                            @csrf @method('PUT')
                            <select name="grade_id" class="fc-inline" required>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ $topic->grade_id == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="name" class="fc-inline" style="flex:1;" value="{{ $topic->name }}" required maxlength="100">
                            <input type="number" name="sequence" class="fc-inline" style="width:56px;" min="1" placeholder="—" value="{{ $topic->sequence }}" title="მიმდევრობა">
                            <button type="submit" class="btn-save">შენახვა</button>
                        </form>
                        <button type="button" class="btn-cancel-row" onclick="cancelTopic({{ $topic->id }})">✕</button>
                    </div>
                </div>
                {{-- Video panel --}}
                <div class="vid-panel" id="vp{{ $topic->id }}">
                    @foreach($topic->videos as $vid)
                    <div class="vid-item">
                        <img src="{{ $vid->thumbnailUrl() }}" class="vid-thumb" alt="">
                        <div style="flex:1;min-width:0;">
                            <div class="vid-title">{{ $vid->title ?: $vid->youtube_id }}</div>
                            <div class="vid-id">{{ $vid->youtube_id }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.topic.videos.destroy', $vid) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                        </form>
                    </div>
                    @endforeach
                    <form method="POST" action="{{ route('admin.topic.videos.store', $topic) }}" class="vid-add-form">
                        @csrf
                        <input type="text" name="youtube_url" class="fc-inline" placeholder="YouTube URL ან ID" style="flex:1;min-width:160px;" required>
                        <input type="text" name="title" class="fc-inline" placeholder="სათაური (სურვილისამებრ)" style="flex:1;min-width:140px;">
                        <button type="submit" class="btn-save">+ ვიდეო</button>
                    </form>
                    @error('youtube_url')<div class="vid-err">{{ $message }}</div>@enderror
                </div>

                {{-- Templates panel --}}
                <div class="tpl-panel" id="tp{{ $topic->id }}">
                    @if($topic->questionTemplates->isEmpty())
                    <div class="ks-muted" style="font-size:13px;">შაბლონი არ არის</div>
                    @else
                    <div class="tpl-hint">რიგითობა — თემის ჯერზე, ეს შაბლონები ჯერ გამოჩნდება (პატარადან დიდზე); ცარიელი = შემთხვევითი</div>
                    @foreach($topic->questionTemplates as $tpl)
                    <div class="tpl-item">
                        <input type="number" class="tpl-seq" min="1" placeholder="—" value="{{ $tpl->sequence }}"
                            data-tpl-id="{{ $tpl->id }}" onchange="saveTplSequence(this)">
                        <div class="tpl-text" title="{{ $tpl->template_text }}">{{ $tpl->title ? '★ '.$tpl->title : Str::limit($tpl->template_text ?: '('.$tpl->question_type.')', 70) }}</div>
                        <span class="tpl-tag" style="color:#8A8078;">#{{ $tpl->id }}</span>
                        @if($tpl->theme)
                        <span class="tpl-tag" style="color:#C24A0A;border-color:#FFD9BF;">{{ $tpl->theme->icon ?? '' }} {{ $tpl->theme->name }}</span>
                        @endif
                        <span class="tpl-tag">დ{{ $tpl->difficulty }}</span>
                        @if(!$tpl->is_active)<span class="tpl-tag" style="color:#b91c1c;border-color:#fecaca;">გაჩერ.</span>@endif
                        <a href="{{ route('admin.questions.edit', $tpl) }}" class="tpl-edit">✎</a>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        @empty
        <div class="ks-card ks-muted">თემა ჯერ არ არის დამატებული</div>
        @endforelse
</div>

{{-- Add modal --}}
<div class="tp-overlay" id="tpOverlay" onclick="tpOverlayClick(event)">
    <div class="tp-box">
        <div class="tp-title">ახალი თემა</div>
        <form method="POST" action="{{ route('admin.topics.store') }}">
            @csrf
            <div class="tp-lbl">კლასი</div>
            <select name="grade_id" class="tp-fc" required>
                <option value="">— კლასი —</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
            <div class="tp-lbl">თემის სახელი</div>
            <input type="text" name="name" id="tpNameInput" class="tp-fc" placeholder="მაგ: გამრავლება" maxlength="100" required value="{{ old('name') }}">
            <div class="tp-lbl">მიმდევრობა <span style="text-transform:none;letter-spacing:0;color:#8A8078;">(სურვილისამებრ — ტესტში რიგითობის პრიორიტეტი)</span></div>
            <input type="number" name="sequence" class="tp-fc" min="1" placeholder="— შემთხვევითი რიგი —" value="{{ old('sequence') }}">
            <div class="tp-actions">
                <button type="submit" class="tp-btn-add">+ დამატება</button>
                <button type="button" class="tp-btn-cancel" onclick="closeAddModal()">გაუქმება</button>
            </div>
        </form>
    </div>
</div>

{{-- Move modal --}}
<div class="mv-overlay" id="mvOverlay" onclick="mvOverlayClick(event)">
    <div class="mv-box">
        <div class="mv-head">კოპირება — <span id="mvGradeName"></span></div>

        <form method="POST" action="{{ route('admin.topics.move') }}" id="mvForm">
            @csrf
            <input type="hidden" name="target_grade_id" id="mvTargetInput">

            <div class="mv-lbl">სამიზნე კლასი</div>
            <select id="mvTargetGrade" class="mv-fc" onchange="onTargetGradeChange(this.value)">
                <option value="">— კლასი —</option>
            </select>

            <div class="mv-topics-hdr">
                <div class="mv-lbl" style="margin-bottom:0;">თემები</div>
                <button type="button" class="mv-select-all" onclick="selectAllActive()">ყველა აქტიური ✓</button>
            </div>
            <div class="mv-list" id="mvList"></div>

            <div class="mv-foot">
                <button type="submit" class="mv-btn-go" id="mvSubmitBtn" disabled>⎘ კოპირება</button>
                <button type="button" class="mv-btn-x" onclick="closeMoveModal()">გაუქმება</button>
            </div>
        </form>
    </div>
</div>

<script>
const _topics = @json($topicsJson);
const _grades = @json($gradesJson);
const _namesByGrade = @json($namesByGrade);

let _sourceGradeId = null;

// ── Grade filter
function filterGrade(gradeId) {
    const rows    = document.querySelectorAll('.topic-row');
    const headers = document.querySelectorAll('.grade-hdr');
    let visible   = 0;
    rows.forEach(r => {
        const show = !gradeId || r.dataset.grade === gradeId;
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    headers.forEach(h => {
        h.style.display = (!gradeId || h.dataset.grade === gradeId) ? '' : 'none';
    });
    document.getElementById('topicCount').textContent = visible;

    const moveBtn = document.getElementById('moveBtn');
    moveBtn.style.display = gradeId ? '' : 'none';
    _sourceGradeId = gradeId || null;
}

// ── Inline edit
function editTopic(id) { document.getElementById('topic-row-' + id).classList.add('editing'); }
function cancelTopic(id) { document.getElementById('topic-row-' + id).classList.remove('editing'); }

// ── Video panel toggle
function toggleVidPanel(id) {
    const el = document.getElementById('vp' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

// ── Templates panel (per-topic priority)
function toggleTplPanel(id) {
    const el = document.getElementById('tp' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
function saveTplSequence(input) {
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

// ── Add modal
function openAddModal() {
    document.getElementById('tpOverlay').classList.add('open');
    setTimeout(() => document.getElementById('tpNameInput').focus(), 120);
}
function closeAddModal() { document.getElementById('tpOverlay').classList.remove('open'); }
function tpOverlayClick(e) { if (e.target === document.getElementById('tpOverlay')) closeAddModal(); }

// ── Move modal
function openMoveModal() {
    if (!_sourceGradeId) return;
    const grade = _grades.find(g => String(g.id) === String(_sourceGradeId));
    document.getElementById('mvGradeName').textContent = grade ? grade.name : '';

    // populate target grade dropdown (exclude source)
    const sel = document.getElementById('mvTargetGrade');
    sel.innerHTML = '<option value="">— კლასი —</option>';
    _grades.forEach(g => {
        if (String(g.id) !== String(_sourceGradeId)) {
            sel.add(new Option(g.name, g.id));
        }
    });
    document.getElementById('mvTargetInput').value = '';

    buildMvList('');
    document.getElementById('mvOverlay').classList.add('open');
}
function closeMoveModal() { document.getElementById('mvOverlay').classList.remove('open'); }
function mvOverlayClick(e) { if (e.target === document.getElementById('mvOverlay')) closeMoveModal(); }

function onTargetGradeChange(val) {
    document.getElementById('mvTargetInput').value = val;
    buildMvList(val);
}

function buildMvList(targetGradeId) {
    const list = document.getElementById('mvList');
    const sourceTopics = _topics.filter(t => String(t.grade_id) === String(_sourceGradeId));

    const targetNames = targetGradeId
        ? (_namesByGrade[targetGradeId] || []).map(n => n.toLowerCase().trim())
        : [];

    list.innerHTML = '';
    sourceTopics.forEach(t => {
        const noTemplates  = t.count === 0;
        const nameConflict = targetGradeId && targetNames.includes(t.name.toLowerCase().trim());
        const inactive     = noTemplates || nameConflict;
        const reason       = noTemplates ? 'შაბლონი არ არის' : nameConflict ? 'სახელი არსებობს' : '';

        const row = document.createElement('div');
        row.className = 'mv-row' + (inactive ? ' inactive' : '');
        row.innerHTML =
            '<input type="checkbox" name="topic_ids[]" value="' + t.id + '"' +
            (inactive ? ' disabled' : '') + ' class="mv-chk" onchange="updateMvBtn()">' +
            '<span class="mv-name">' + escHtml(t.name) + '</span>' +
            '<span class="mv-cnt">' + t.count + ' შაბლ.</span>' +
            (reason ? '<span class="mv-reason">' + escHtml(reason) + '</span>' : '');
        list.appendChild(row);
    });

    updateMvBtn();
}

function selectAllActive() {
    document.querySelectorAll('.mv-chk:not(:disabled)').forEach(c => c.checked = true);
    updateMvBtn();
}

function updateMvBtn() {
    const anyChecked = document.querySelectorAll('.mv-chk:checked').length > 0;
    const hasTarget  = document.getElementById('mvTargetInput').value !== '';
    document.getElementById('mvSubmitBtn').disabled = !(anyChecked && hasTarget);
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeAddModal(); closeMoveModal(); }
});

@if($errors->any()) openAddModal(); @endif
</script>
@endsection
