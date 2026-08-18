@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    select.fc { cursor: pointer; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .grade-group { color: #94a3b8; font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; padding: 12px 0 4px; }
    .row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; gap: 8px; }
    .row:last-child { border-bottom: none; }
    .badge { font-size: 0.65rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 6px; margin-left: 8px; }
    .row-view { display:flex; align-items:center; flex:1; gap:4px; min-width:0; }
    .row-edit { display:none; flex:1; gap:6px; align-items:center; }
    .row.editing .row-view { display:none; }
    .row.editing .row-edit { display:flex; }
    .btn-edit { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-edit:hover { color:#64748b; }
    .btn-del { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-del:hover { color:#ef4444; }
    .fc-inline { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.78rem; padding:5px 9px; outline:none; }
    .fc-inline:focus { border-color:#94a3b8; }
    .btn-save { background:#f8fafc; border:1px solid #e2e8f0; color:#374151; font-family:'Goldman',monospace; font-size:0.72rem; padding:5px 12px; border-radius:4px; cursor:pointer; white-space:nowrap; }
    .btn-save:hover { border-color:#94a3b8; }
    .btn-cancel-row { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; }
    .btn-cancel-row:hover { color:#64748b; }
    .btn-vid { background:none; border:1px solid #e2e8f0; border-radius:3px; color:#64748b; font-family:'Goldman',monospace; font-size:0.6rem; cursor:pointer; padding:2px 7px; transition:all 0.15s; white-space:nowrap; }
    .btn-vid:hover { border-color:#6366f1; color:#6366f1; }
    .btn-vid.has { border-color:#c7d2fe; background:#eef2ff; color:#4f46e5; }
    .vid-panel { display:none; padding:10px 0 4px; border-top:1px solid #f1f5f9; }
    .vid-item { display:flex; align-items:center; gap:8px; padding:5px 0; font-size:0.75rem; color:#374151; }
    .vid-thumb { width:56px; height:36px; object-fit:cover; border-radius:3px; flex-shrink:0; }
    .vid-title { flex:1; font-size:0.72rem; color:#374151; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .vid-id { font-size:0.6rem; color:#94a3b8; }
    .vid-add-form { display:flex; gap:6px; align-items:center; margin-top:8px; flex-wrap:wrap; }
    .vid-err { font-size:0.62rem; color:#e74c3c; margin-top:4px; }

    /* add modal */
    .tp-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.35); display:flex; align-items:center; justify-content:center; z-index:100000; opacity:0; pointer-events:none; transition:opacity 0.15s; }
    .tp-overlay.open { opacity:1; pointer-events:all; }
    .tp-box { background:#fff; border-radius:10px; padding:28px 28px 24px; width:100%; max-width:400px; box-shadow:0 8px 32px rgba(0,0,0,0.14); transform:translateY(6px); transition:transform 0.15s; }
    .tp-overlay.open .tp-box { transform:translateY(0); }
    .tp-title { font-size:0.7rem; color:#94a3b8; letter-spacing:0.18em; text-transform:uppercase; margin-bottom:20px; }
    .tp-lbl { font-size:0.6rem; color:#64748b; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:5px; }
    .tp-fc { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.82rem; padding:9px 13px; width:100%; outline:none; box-sizing:border-box; margin-bottom:14px; }
    .tp-fc:focus { border-color:#94a3b8; }
    select.tp-fc { cursor:pointer; }
    .tp-actions { display:flex; gap:10px; align-items:center; margin-top:4px; }
    .tp-btn-add { background:#f0fdf4; border:1px solid #bbf7d0; color:#059669; font-family:'Goldman',monospace; font-size:0.76rem; letter-spacing:0.06em; padding:10px 22px; border-radius:4px; cursor:pointer; transition:all 0.15s; }
    .tp-btn-add:hover { border-color:#059669; }
    .tp-btn-cancel { background:none; border:none; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.68rem; cursor:pointer; }
    .tp-btn-cancel:hover { color:#374151; }

    /* move modal */
    .mv-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.4); display:flex; align-items:center; justify-content:center; z-index:100000; opacity:0; pointer-events:none; transition:opacity 0.15s; }
    .mv-overlay.open { opacity:1; pointer-events:all; }
    .mv-box { background:#fff; border-radius:10px; padding:24px 24px 20px; width:100%; max-width:460px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 8px 32px rgba(0,0,0,0.16); transform:translateY(6px); transition:transform 0.15s; }
    .mv-overlay.open .mv-box { transform:translateY(0); }
    .mv-head { font-size:0.7rem; color:#94a3b8; letter-spacing:0.18em; text-transform:uppercase; margin-bottom:16px; flex-shrink:0; }
    .mv-lbl { font-size:0.58rem; color:#64748b; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:5px; }
    .mv-fc { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.8rem; padding:8px 11px; width:100%; outline:none; box-sizing:border-box; margin-bottom:14px; cursor:pointer; }
    .mv-fc:focus { border-color:#94a3b8; }
    .mv-topics-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; flex-shrink:0; }
    .mv-select-all { background:none; border:none; color:#3b82f6; font-family:'Goldman',monospace; font-size:0.6rem; cursor:pointer; padding:0; letter-spacing:0.04em; }
    .mv-select-all:hover { color:#1d4ed8; }
    .mv-list { overflow-y:auto; flex:1; border:1px solid #f1f5f9; border-radius:6px; padding:4px 0; min-height:80px; }
    .mv-row { display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid #f8fafc; font-size:0.78rem; color:#374151; }
    .mv-row:last-child { border-bottom:none; }
    .mv-row.inactive { opacity:0.4; }
    .mv-row input[type=checkbox] { width:15px; height:15px; accent-color:#374151; flex-shrink:0; cursor:pointer; }
    .mv-row.inactive input[type=checkbox] { cursor:not-allowed; }
    .mv-name { flex:1; }
    .mv-cnt { font-size:0.62rem; color:#64748b; border:1px solid #e2e8f0; border-radius:2px; padding:1px 6px; white-space:nowrap; }
    .mv-reason { font-size:0.58rem; color:#f59e0b; white-space:nowrap; }
    .mv-foot { flex-shrink:0; display:flex; gap:10px; align-items:center; padding-top:14px; margin-top:4px; border-top:1px solid #f1f5f9; }
    .mv-btn-go { background:#f0fdf4; border:1px solid #bbf7d0; color:#059669; font-family:'Goldman',monospace; font-size:0.76rem; letter-spacing:0.06em; padding:10px 22px; border-radius:4px; cursor:pointer; transition:all 0.15s; }
    .mv-btn-go:hover:not(:disabled) { border-color:#059669; }
    .mv-btn-go:disabled { opacity:0.45; cursor:not-allowed; }
    .mv-btn-x { background:none; border:none; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.68rem; cursor:pointer; }
    .mv-btn-x:hover { color:#374151; }

    @media (max-width:640px) {
        .aw { padding:14px 10px 48px; }
        .card-dark { padding:14px; }
        .row { font-size:0.72rem; }
        .tp-box, .mv-box { margin:0 12px; padding:18px; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    {{-- Toolbar --}}
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
        <select id="gradeFilter" class="fc" style="width:170px;" onchange="filterGrade(this.value)">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn" id="moveBtn" style="display:none;" onclick="openMoveModal()">⎘ სხვა კლასში კოპირება</button>
        <div style="flex:1;"></div>
        <button type="button" class="btn" onclick="openAddModal()">+ ახალი თემა</button>
    </div>

    <div class="card-dark">
        <div class="card-label">თემები · <span id="topicCount">{{ $topics->count() }}</span></div>
        @php $lastGrade = null; @endphp
        @forelse($topics as $topic)
            @if($lastGrade !== $topic->grade_id)
                @if($lastGrade !== null)<div style="height:4px;"></div>@endif
                <div class="grade-group grade-hdr" data-grade="{{ $topic->grade_id }}">{{ $topic->grade->name }}</div>
                @php $lastGrade = $topic->grade_id; @endphp
            @endif
            <div class="row topic-row" id="topic-row-{{ $topic->id }}" data-grade="{{ $topic->grade_id }}" style="flex-direction:column;align-items:stretch;gap:0;">
                <div style="display:flex;align-items:center;gap:4px;">
                    <div class="row-view" style="flex:1;">
                        <span>{{ $topic->name }}</span>
                        <span class="badge">{{ $topic->question_templates_count }} შაბლონი</span>
                    </div>
                    <button type="button" class="btn-vid {{ $topic->videos->count() ? 'has' : '' }}"
                        onclick="toggleVidPanel({{ $topic->id }})">
                        📹 {{ $topic->videos->count() ?: '' }}
                    </button>
                    <div style="display:flex;gap:2px;flex-shrink:0;">
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
            </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">თემა არ არის</div>
        @endforelse
    </div>
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
