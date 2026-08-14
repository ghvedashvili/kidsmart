@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #64748b; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #1e293b; border-color: #94a3b8; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 10px; box-sizing: border-box; }
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
    .row-view { display:flex; align-items:center; flex:1; gap:4px; min-width:0; overflow:hidden; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .atitle { display: none; }
        .anav { gap: 3px; margin-bottom: 14px; }
        .anav a { font-size: 0.6rem; padding: 3px 7px; }
        .card-dark { padding: 14px; }
        .row { font-size: 0.72rem; }
    }
    .row-edit { display:none; flex:1; gap:6px; align-items:center; }
    .row.editing .row-view { display:none; }
    .row.editing .row-edit { display:flex; }
    .btn-edit { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-edit:hover { color:#64748b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .fc-inline { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; color:#374151; font-family:'Goldman',monospace; font-size:0.78rem; padding:5px 9px; outline:none; }
    .fc-inline:focus { border-color:#94a3b8; }
    .btn-save { background:#f8fafc; border:1px solid #e2e8f0; color:#374151; font-family:'Goldman',monospace; font-size:0.72rem; padding:5px 12px; border-radius:4px; cursor:pointer; white-space:nowrap; }
    .btn-save:hover { border-color:#94a3b8; }
    .btn-cancel { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; }
    .btn-cancel:hover { color:#64748b; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემატიკა</a>
        <a href="{{ route('admin.topics.index') }}" class="active">თემები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
        <a href="{{ route('admin.users.index') }}">მომხმარებლები</a>
        <a href="{{ route('admin.permissions.index') }}">ნებართვები</a>
        <a href="{{ route('admin.packages.index') }}">პაკეტები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="card-dark">
        <div class="card-label">თემას დამატება</div>
        <form method="POST" action="{{ route('admin.topics.store') }}">
            @csrf
            <div style="display:flex;gap:10px;">
                <select name="grade_id" class="fc" style="width:180px;margin-bottom:0;" required>
                    <option value="">— კლასი —</option>
                    @foreach($grades as $grade)
                    <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                        {{ $grade->name }}
                    </option>
                    @endforeach
                </select>
                <input type="text" name="name" class="fc" placeholder="თემას სახელი (100-ფარგლებში)" style="flex:1;margin-bottom:0;" required value="{{ old('name') }}">
                <button type="submit" class="btn">+ დამატება</button>
            </div>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">თემები · {{ $topics->count() }}</div>
        @php $lastGrade = null; @endphp
        @forelse($topics as $topic)
            @if($lastGrade !== $topic->grade_id)
                @if($lastGrade !== null) <div style="height:4px;"></div> @endif
                <div class="grade-group">{{ $topic->grade->name }}</div>
                @php $lastGrade = $topic->grade_id; @endphp
            @endif
            <div class="row" id="topic-row-{{ $topic->id }}">
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
                    <button type="button" class="btn-cancel" onclick="cancelTopic({{ $topic->id }})">✕</button>
                </div>
            </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">თემა არ არის</div>
        @endforelse
    </div>
</div>

<script>
function editTopic(id) {
    document.getElementById('topic-row-' + id).classList.add('editing');
}
function cancelTopic(id) {
    document.getElementById('topic-row-' + id).classList.remove('editing');
}
</script>
@endsection
