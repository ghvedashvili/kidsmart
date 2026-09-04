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
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .btn-edit { background: none; border: none; color: #cbd5e1; font-size: 0.82rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; line-height:1; }
    .btn-edit:hover { color: #6366f1; }
    .row { display: flex; flex-direction: column; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #374151; }
    .row:last-child { border-bottom: none; }
    .row-display { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 8px; }
    .row-edit { display: none; gap: 8px; align-items: center; padding: 4px 0 2px; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .err { font-size: 0.72rem; color: #e74c3c; margin-top: 6px; }
    .pill { display:inline-block; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 10px; font-size:0.68rem; margin-right:4px; }
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
        <div class="card-label">ტესტში კითხვების რაოდენობა — წესის დამატება</div>
        <div class="hint">
            ნაგულისხმევად ყველა ტესტს აქვს {{ \App\Models\TestQuestionCount::DEFAULT_COUNT }} კითხვა. აქ შეგიძლია დაადგინო გამონაკლისი კონკრეტული კლასის/დონის/თემატიკისთვის — თემატიკის გარეშე დატოვება ნიშნავს, რომ წესი მოქმედებს ამ კლასის+დონეზე ნებისმიერი თემატიკით.
        </div>
        <form method="POST" action="{{ route('admin.question-counts.store') }}">
            @csrf
            <select name="grade_id" class="fc" required>
                <option value="">კლასი</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
            <select name="difficulty" class="fc" required>
                <option value="">დონე</option>
                @for($i = 1; $i <= 3; $i++)
                <option value="{{ $i }}" {{ old('difficulty') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
                @endfor
            </select>
            <select name="theme_id" class="fc">
                <option value="">ნებისმიერი თემატიკა</option>
                @foreach($themes as $theme)
                <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                @endforeach
            </select>
            <input type="number" name="questions_count" class="fc" placeholder="კითხვების რაოდენობა" min="1" max="100" value="{{ old('questions_count') }}" required>
            <button type="submit" class="btn">+ დამატება</button>
            @error('grade_id')<div class="err">{{ $message }}</div>@enderror
            @error('questions_count')<div class="err">{{ $message }}</div>@enderror
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">არსებული წესები · {{ $rows->count() }}</div>
        @forelse($rows as $row)
        <div class="row">
            <div class="row-display" id="qd{{ $row->id }}">
                <span>
                    <span class="pill">{{ $row->grade->name ?? '—' }}</span>
                    <span class="pill">დონე {{ $row->difficulty }}</span>
                    <span class="pill">{{ $row->theme ? ($row->theme->icon . ' ' . $row->theme->name) : 'ნებისმიერი თემატიკა' }}</span>
                    <b>{{ $row->questions_count }}</b> კითხვა
                </span>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <button type="button" class="btn-edit" onclick="qcEdit({{ $row->id }})" title="ედიტი">✎</button>
                    <form method="POST" action="{{ route('admin.question-counts.destroy', $row) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                    </form>
                </div>
            </div>
            <div class="row-edit" id="qe{{ $row->id }}">
                <form method="POST" action="{{ route('admin.question-counts.update', $row) }}" style="display:flex;gap:8px;align-items:center;width:100%;">
                    @csrf @method('PUT')
                    <input type="number" name="questions_count" class="fc" value="{{ $row->questions_count }}" min="1" max="100" style="flex:1;margin-bottom:0;" required>
                    <button type="submit" class="btn">შენახვა</button>
                    <button type="button" class="btn-del" onclick="qcCancel({{ $row->id }})">✕</button>
                </form>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">ჯერ წესი არ დამატებულა — ყველა ტესტს {{ \App\Models\TestQuestionCount::DEFAULT_COUNT }} კითხვა ექნება</div>
        @endforelse
    </div>
</div>
<script>
function qcEdit(id) {
    document.getElementById('qd' + id).style.display = 'none';
    document.getElementById('qe' + id).style.display = 'flex';
}
function qcCancel(id) {
    document.getElementById('qd' + id).style.display = 'flex';
    document.getElementById('qe' + id).style.display = 'none';
}
</script>
@endsection
