@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    .fc::placeholder { color: #cbd5e1; }
    select.fc { cursor: pointer; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; gap: 8px; }
    .row:last-child { border-bottom: none; }
    .badge { font-size: 0.65rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 6px; margin-left: 8px; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .grade-group { color: #94a3b8; font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; padding: 12px 0 4px; }
    .grade-group:first-child { padding-top: 0; }
    .row-view { display:flex; align-items:center; flex:1; gap:4px; min-width:0; overflow:hidden; }
    .row-edit { display:none; flex:1; gap:6px; align-items:center; }
    .row.editing .row-view { display:none; }
    .row.editing .row-edit { display:flex; }
    .btn-edit { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-edit:hover { color:#64748b; }
    .fc-inline { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.78rem; padding:5px 9px; outline:none; }
    .fc-inline:focus { border-color:#94a3b8; }
    .btn-save { background:#f8fafc; border:1px solid #e2e8f0; color:#374151; font-family:'Goldman',monospace; font-size:0.72rem; padding:5px 12px; border-radius:4px; cursor:pointer; white-space:nowrap; }
    .btn-save:hover { border-color:#94a3b8; }
    .btn-cancel-row { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; }
    .btn-cancel-row:hover { color:#64748b; }

    /* topic modal — avoid Bootstrap .modal conflicts */
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

    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .card-dark { padding: 14px; }
        .row { font-size: 0.72rem; }
        .modal { margin: 0 12px; padding: 20px; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    {{-- Toolbar: filter + add button --}}
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;">
        <select id="gradeFilter" class="fc" style="width:180px;" onchange="filterGrade(this.value)">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
            @endforeach
        </select>
        <div style="flex:1;"></div>
        <button type="button" class="btn" onclick="openModal()">+ ახალი თემა</button>
    </div>

    <div class="card-dark">
        <div class="card-label">თემები · <span id="topicCount">{{ $topics->count() }}</span></div>
        @php $lastGrade = null; @endphp
        @forelse($topics as $topic)
            @if($lastGrade !== $topic->grade_id)
                @if($lastGrade !== null) <div class="grade-sep" data-grade="{{ $topic->grade_id }}" style="height:4px;"></div> @endif
                <div class="grade-group grade-hdr" data-grade="{{ $topic->grade_id }}">{{ $topic->grade->name }}</div>
                @php $lastGrade = $topic->grade_id; @endphp
            @endif
            <div class="row topic-row" id="topic-row-{{ $topic->id }}" data-grade="{{ $topic->grade_id }}">
                {{-- View mode --}}
                <div class="row-view">
                    <span>{{ $topic->name }}</span>
                    <span class="badge">{{ $topic->question_templates_count }} შაბლონი</span>
                </div>
                <div style="display:flex;gap:2px;flex-shrink:0;">
                    <button type="button" class="btn-edit" onclick="editTopic({{ $topic->id }})">✎</button>
                    <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                    </form>
                </div>

                {{-- Edit mode --}}
                <div class="row-edit">
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
        @empty
        <div style="color:#444;font-size:0.78rem;">თემა არ არის</div>
        @endforelse
    </div>
</div>

{{-- Modal --}}
<div class="tp-overlay" id="tpOverlay" onclick="overlayClick(event)">
    <div class="tp-box">
        <div class="tp-title">ახალი თემა</div>
        <form method="POST" action="{{ route('admin.topics.store') }}">
            @csrf
            <div class="tp-lbl">კლასი</div>
            <select name="grade_id" class="tp-fc" required>
                <option value="">— კლასი —</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                    {{ $grade->name }}
                </option>
                @endforeach
            </select>

            <div class="tp-lbl">თემის სახელი</div>
            <input type="text" name="name" id="tpNameInput" class="tp-fc" placeholder="მაგ: გამრავლება" maxlength="100" required value="{{ old('name') }}">

            <div class="tp-actions">
                <button type="submit" class="tp-btn-add">+ დამატება</button>
                <button type="button" class="tp-btn-cancel" onclick="closeModal()">გაუქმება</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('tpOverlay').classList.add('open');
    setTimeout(() => document.getElementById('tpNameInput').focus(), 120);
}
function closeModal() {
    document.getElementById('tpOverlay').classList.remove('open');
}
function overlayClick(e) {
    if (e.target === document.getElementById('tpOverlay')) closeModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

function editTopic(id) {
    document.getElementById('topic-row-' + id).classList.add('editing');
}
function cancelTopic(id) {
    document.getElementById('topic-row-' + id).classList.remove('editing');
}

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
        const show = !gradeId || h.dataset.grade === gradeId;
        h.style.display = show ? '' : 'none';
    });
    document.getElementById('topicCount').textContent = visible;
}

@if($errors->any())
openModal();
@endif
</script>
@endsection
