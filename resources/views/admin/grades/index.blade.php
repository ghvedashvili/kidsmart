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
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; }
    .row:last-child { border-bottom: none; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}" class="active">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="card-dark">
        <div class="card-label">კლასის დამატება</div>
        <form method="POST" action="{{ route('admin.grades.store') }}">
            @csrf
            <div style="display:flex;gap:10px;">
                <input type="number" name="number" class="fc" placeholder="№ (1–12)" min="1" max="12" style="width:100px;margin-bottom:0;" required>
                <input type="text" name="name" class="fc" placeholder="სახელი, მაგ: მე-2 კლასი" style="flex:1;margin-bottom:0;" required>
                <button type="submit" class="btn">+ დამატება</button>
            </div>
            @error('number')<div style="color:#e74c3c;font-size:0.72rem;margin-top:6px;">{{ $message }}</div>@enderror
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">კლასები · {{ $grades->count() }}</div>
        @forelse($grades as $grade)
        <div class="row">
            <span><span style="color:#555;margin-right:10px;">{{ $grade->number }}</span>{{ $grade->name }}</span>
            <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">კლასი არ არის</div>
        @endforelse
    </div>
</div>
@endsection
