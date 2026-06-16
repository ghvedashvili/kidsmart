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
    .row { display: flex; align-items: flex-start; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; gap: 12px; }
    .row:last-child { border-bottom: none; }
    .tag { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 3px; padding: 2px 8px; font-size: 0.68rem; color: #64748b; margin: 2px; display: inline-block; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .hint { font-size: 0.68rem; color: #94a3b8; margin-top: -6px; margin-bottom: 10px; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}" class="active">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <span style="font-size:1.6rem;">{{ $theme->icon }}</span>
        <div>
            <div style="color:#aaa;font-size:0.9rem;">{{ $theme->name }}</div>
            <div style="color:#555;font-size:0.68rem;letter-spacing:0.08em;">ცვლადების მართვა</div>
        </div>
    </div>

    <div class="card-dark">
        <div class="card-label">ახალი ცვლადი</div>
        <form method="POST" action="{{ route('admin.themes.variables.store', $theme) }}">
            @csrf
            <input type="text" name="variable_name" class="fc" placeholder="ცვლადის სახელი, მაგ: TEAM" maxlength="50"
                oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" required
                value="{{ old('variable_name') }}">
            <div class="hint">მხოლოდ ლათინური ასოები, ციფრები, _ (TEAM, PLAYER, PLACE)</div>
            <textarea name="values" class="fc" rows="2"
                placeholder="მნიშვნელობები მძიმით: დინამო, რუსთავი, თბილისი" required>{{ old('values') }}</textarea>
            <div class="hint">მძიმით გამოყოფილი სია — შაბლონში გამოიყენება @{{TEAM}}</div>
            @error('variable_name')<div style="color:#e74c3c;font-size:0.72rem;margin-bottom:8px;">{{ $message }}</div>@enderror
            @error('values')<div style="color:#e74c3c;font-size:0.72rem;margin-bottom:8px;">{{ $message }}</div>@enderror
            <button type="submit" class="btn">შენახვა</button>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">ცვლადები · {{ $theme->variables->count() }}</div>
        @forelse($theme->variables as $var)
        <div class="row">
            <div style="flex:1;">
                <div style="color:#888;margin-bottom:6px;font-size:0.75rem;letter-spacing:0.1em;">
                    {{ '{{' . $var->variable_name . '}' . '}' }}
                </div>
                <div>
                    @foreach($var->values as $v)
                    <span class="tag">{{ $v }}</span>
                    @endforeach
                </div>
            </div>
            <form method="POST" action="{{ route('admin.themes.variables.destroy', $var) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">ცვლადი არ არის</div>
        @endforelse
    </div>
</div>
@endsection
