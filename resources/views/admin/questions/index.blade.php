@extends('layouts.app')
@section('content')
<style>
    body { background: #0d0d0d !important; }
    .aw { max-width: 860px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #555; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #555; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #222; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #bbb; border-color: #444; }
    .card-dark { background: #111; border: 1px solid #1e1e1e; border-radius: 8px; padding: 24px; margin-bottom: 20px; }
    .card-label { font-size: 0.68rem; color: #444; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #ccc; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #444; }
    select.fc { cursor: pointer; }
    .btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #bbb; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn:hover { border-color: #444; color: #fff; }
    .btn-del { background: none; border: none; color: #333; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #e74c3c; }
    .q-row { padding: 12px 0; border-bottom: 1px solid #1a1a1a; }
    .q-row:last-child { border-bottom: none; }
    .q-text { color: #888; font-size: 0.8rem; line-height: 1.5; margin-bottom: 6px; }
    .q-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .q-tag { font-size: 0.65rem; color: #555; border: 1px solid #222; border-radius: 2px; padding: 1px 7px; }
    .diff { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    .msg { font-size: 0.75rem; color: #2ecc71; margin-bottom: 16px; }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .pagination-wrap { margin-top: 20px; display: flex; gap: 6px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { font-family: 'Goldman', monospace; font-size: 0.68rem; color: #555; border: 1px solid #222; border-radius: 3px; padding: 4px 10px; text-decoration: none; }
    .pagination-wrap a:hover { color: #bbb; border-color: #444; }
    .pagination-wrap span.active-page { color: #aaa; border-color: #444; }
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

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="color:#555;font-size:0.78rem;">სულ: {{ $templates->total() }} შაბლონი</div>
        <a href="{{ route('admin.questions.create') }}" class="btn">+ ახალი შაბლონი</a>
    </div>

    <form method="GET" class="filters">
        <select name="grade_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                {{ $grade->name }}
            </option>
            @endforeach
        </select>
        <select name="topic_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა თოპიქი</option>
            @foreach($topics as $topic)
            <option value="{{ $topic->id }}" {{ ($filters['topic_id'] ?? '') == $topic->id ? 'selected' : '' }}>
                {{ $topic->grade->name }} / {{ $topic->name }}
            </option>
            @endforeach
        </select>
        <select name="difficulty" class="fc" onchange="this.form.submit()">
            <option value="">ყველა დონე</option>
            @for($i=1;$i<=5;$i++)
            <option value="{{ $i }}" {{ ($filters['difficulty'] ?? '') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
            @endfor
        </select>
        @if(array_filter($filters))
        <a href="{{ route('admin.questions.index') }}" style="color:#555;font-size:0.72rem;text-decoration:none;padding:9px 0;">× გასუფთავება</a>
        @endif
    </form>

    <div class="card-dark">
        @forelse($templates as $tpl)
        <div class="q-row">
            <div class="q-text">{{ $tpl->template_text }}</div>
            <div class="q-meta">
                <span class="q-tag">{{ $tpl->topic->grade->name }}</span>
                <span class="q-tag">{{ $tpl->topic->name }}</span>
                <span class="q-tag">დონე {{ $tpl->difficulty }}</span>
                <span class="q-tag" style="color:#888;">= {{ $tpl->correct_formula }}</span>
                <div style="flex:1;"></div>
                <a href="{{ route('admin.questions.edit', $tpl) }}" style="color:#555;font-size:0.7rem;text-decoration:none;padding:2px 8px;border:1px solid #222;border-radius:2px;">რედ.</a>
                <form method="POST" action="{{ route('admin.questions.destroy', $tpl) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                </form>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">შაბლონი არ არის</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $templates->withQueryString()->links('admin.pagination') }}
    </div>
</div>
@endsection
