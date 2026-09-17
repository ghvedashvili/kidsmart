@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .hint { font-size: 0.7rem; color: #94a3b8; margin: -10px 0 16px; line-height: 1.6; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 10px; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    .fl { font-size: 0.64rem; color: #94a3b8; letter-spacing: 0.06em; margin: -4px 0 6px; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .btn-edit { background: none; border: none; color: #cbd5e1; font-size: 0.82rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; line-height:1; }
    .btn-edit:hover { color: #6366f1; }
    .row { display: flex; flex-direction: column; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; }
    .row:last-child { border-bottom: none; }
    .row-display { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 8px; flex-wrap: wrap; }
    .row-edit { display: none; gap: 8px; align-items: center; padding: 4px 0 2px; flex-wrap: wrap; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .err { font-size: 0.72rem; color: #e74c3c; margin-top: 6px; }
    .pill { display:inline-block; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 10px; font-size:0.68rem; margin-right:4px; }
    .pill-date { background:#fef3c7; color:#92400e; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .card-dark { padding: 14px; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="card-dark">
        <div class="card-label">🏆 ოლიმპიადა — გლობალური ნაგულისხმევი</div>
        <div class="hint">
            ეს პარამეტრები ვრცელდება ყველა კლასზე, გარდა იმ კლასებისა, რომლებსაც ქვემოთ გამონაკლისი წესი აქვთ დაწესებული.
            თარიღის დატოვება ცარიელი ნიშნავს — ამ დროისთვის ოლიმპიადა არ არის დანიშნული.
        </div>
        <form method="POST" action="{{ route('admin.olympiad-rules.global') }}">
            @csrf @method('PUT')
            <div class="fl">ოლიმპიადის თარიღი</div>
            <input type="date" name="olympiad_date" class="fc" value="{{ old('olympiad_date', optional($global?->olympiad_date)->format('Y-m-d')) }}">

            <div class="fl">რამდენი ტესტი ბოლო N დღეში</div>
            <input type="number" name="tests_required" class="fc" min="1" max="50" value="{{ old('tests_required', $global?->tests_required ?? \App\Models\OlympiadRule::DEFAULT_TESTS_REQUIRED) }}" required>

            <div class="fl">რამდენი დღის განმავლობაში (N)</div>
            <input type="number" name="days_window" class="fc" min="1" max="90" value="{{ old('days_window', $global?->days_window ?? \App\Models\OlympiadRule::DEFAULT_DAYS_WINDOW) }}" required>

            <div class="fl">კითხვების რაოდენობა ოლიმპიადის ტესტში</div>
            <input type="number" name="questions_count" class="fc" min="1" max="100" value="{{ old('questions_count', $global?->questions_count ?? \App\Models\OlympiadRule::DEFAULT_QUESTIONS_COUNT) }}" required>

            <button type="submit" class="btn">შენახვა</button>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">გამონაკლისი კონკრეტული კლასისთვის</div>
        <form method="POST" action="{{ route('admin.olympiad-rules.store') }}">
            @csrf
            <select name="grade_id" class="fc" required>
                <option value="">კლასი</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
            @error('grade_id')<div class="err">{{ $message }}</div>@enderror

            <div class="fl">ოლიმპიადის თარიღი</div>
            <input type="date" name="olympiad_date" class="fc" value="{{ old('olympiad_date') }}">

            <div class="fl">რამდენი ტესტი ბოლო N დღეში</div>
            <input type="number" name="tests_required" class="fc" min="1" max="50" value="{{ old('tests_required', \App\Models\OlympiadRule::DEFAULT_TESTS_REQUIRED) }}" required>

            <div class="fl">რამდენი დღის განმავლობაში (N)</div>
            <input type="number" name="days_window" class="fc" min="1" max="90" value="{{ old('days_window', \App\Models\OlympiadRule::DEFAULT_DAYS_WINDOW) }}" required>

            <div class="fl">კითხვების რაოდენობა ოლიმპიადის ტესტში</div>
            <input type="number" name="questions_count" class="fc" min="1" max="100" value="{{ old('questions_count', \App\Models\OlympiadRule::DEFAULT_QUESTIONS_COUNT) }}" required>

            <button type="submit" class="btn">+ დამატება</button>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">კლასების გამონაკლისები · {{ $rows->count() }}</div>
        @forelse($rows as $row)
        <div class="row">
            <div class="row-display" id="od{{ $row->id }}">
                <span>
                    <span class="pill">{{ $row->grade->name ?? '—' }}</span>
                    <span class="pill pill-date">{{ $row->olympiad_date ? $row->olympiad_date->format('d.m.Y') : 'არ არის დანიშნული' }}</span>
                    <span class="pill">{{ $row->tests_required }} ტესტი / {{ $row->days_window }} დღე</span>
                    <span class="pill">{{ $row->questions_count }} კითხვა</span>
                </span>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <button type="button" class="btn-edit" onclick="olrEdit({{ $row->id }})" title="ედიტი">✎</button>
                    <form method="POST" action="{{ route('admin.olympiad-rules.destroy', $row) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                    </form>
                </div>
            </div>
            <div class="row-edit" id="oe{{ $row->id }}">
                <form method="POST" action="{{ route('admin.olympiad-rules.update', $row) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;width:100%;">
                    @csrf @method('PUT')
                    <input type="date" name="olympiad_date" class="fc" value="{{ optional($row->olympiad_date)->format('Y-m-d') }}" style="flex:1;min-width:140px;margin-bottom:0;">
                    <input type="number" name="tests_required" class="fc" value="{{ $row->tests_required }}" min="1" max="50" style="flex:1;min-width:80px;margin-bottom:0;" required>
                    <input type="number" name="days_window" class="fc" value="{{ $row->days_window }}" min="1" max="90" style="flex:1;min-width:80px;margin-bottom:0;" required>
                    <input type="number" name="questions_count" class="fc" value="{{ $row->questions_count }}" min="1" max="100" style="flex:1;min-width:80px;margin-bottom:0;" required>
                    <button type="submit" class="btn">შენახვა</button>
                    <button type="button" class="btn-del" onclick="olrCancel({{ $row->id }})">✕</button>
                </form>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">ჯერ გამონაკლისი არ დამატებულა — ყველა კლასი გლობალურ პარამეტრებზეა</div>
        @endforelse
    </div>
</div>
<script>
function olrEdit(id) {
    document.getElementById('od' + id).style.display = 'none';
    document.getElementById('oe' + id).style.display = 'flex';
}
function olrCancel(id) {
    document.getElementById('od' + id).style.display = 'flex';
    document.getElementById('oe' + id).style.display = 'none';
}
</script>
@endsection
