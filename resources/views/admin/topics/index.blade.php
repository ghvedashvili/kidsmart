@extends('layouts.app')
@section('content')
<style>
    body { background: #0d0d0d !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #555; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #555; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #222; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #bbb; border-color: #444; }
    .card-dark { background: #111; border: 1px solid #1e1e1e; border-radius: 8px; padding: 24px; margin-bottom: 20px; }
    .card-label { font-size: 0.68rem; color: #444; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #ccc; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 10px; box-sizing: border-box; }
    .fc:focus { border-color: #444; }
    .fc::placeholder { color: #444; }
    select.fc { cursor: pointer; }
    .btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #bbb; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #444; color: #fff; }
    .btn-del { background: none; border: none; color: #333; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #e74c3c; }
    .row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #1a1a1a; font-size: 0.8rem; color: #777; }
    .row:last-child { border-bottom: none; }
    .badge { font-size: 0.65rem; color: #444; border: 1px solid #222; border-radius: 2px; padding: 1px 6px; margin-left: 8px; }
    .msg { font-size: 0.75rem; color: #2ecc71; margin-bottom: 16px; }
    .grade-group { color: #555; font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; padding: 12px 0 4px; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}" class="active">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="card-dark">
        <div class="card-label">თოპიქის დამატება</div>
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
                <input type="text" name="name" class="fc" placeholder="თოპიქის სახელი (100-ფარგლებში)" style="flex:1;margin-bottom:0;" required value="{{ old('name') }}">
                <button type="submit" class="btn">+ დამატება</button>
            </div>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">თოპიქები · {{ $topics->count() }}</div>
        @php $lastGrade = null; @endphp
        @forelse($topics as $topic)
            @if($lastGrade !== $topic->grade_id)
                @if($lastGrade !== null) <div style="height:4px;"></div> @endif
                <div class="grade-group">{{ $topic->grade->name }}</div>
                @php $lastGrade = $topic->grade_id; @endphp
            @endif
            <div class="row">
                <span>
                    {{ $topic->name }}
                    <span class="badge">{{ $topic->question_templates_count }} შაბლონი</span>
                </span>
                <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                </form>
            </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">თოპიქი არ არის</div>
        @endforelse
    </div>
</div>
@endsection
