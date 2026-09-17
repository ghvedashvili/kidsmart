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
    .pill-up { background:#dcfce7; color:#15803d; }
    .pill-down { background:#fee2e2; color:#b91c1c; }
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
        <div class="card-label">დონის ცვლილება — წესის დამატება</div>
        <div class="hint">
            ბავშვის დონე მოწმდება ყოველ N-ე დასრულებული ტესტიდან, შემდეგ ყოველ ტესტზე, ბოლო N ტესტის საერთო სწორი პასუხების მიხედვით — თუ „ასაწევ" ზღვარზე მეტია, დონე +1 იმატებს (არ გადააჭარბებს კლასის მაქსიმალურ ლეველს — ეს <a href="{{ route('admin.grades.index') }}" style="color:#6366f1;">კლასების გვერდზეა</a> განსაზღვრული); თუ „დასაწევ" ზღვარზე ნაკლებია, დონე −1 დაიწევს (მინ. 1); შუალედში — უცვლელი რჩება, დათვლა არ განულდება.
            ნაგულისხმევად ეს არის {{ \App\Models\LevelUpRule::DEFAULT_TESTS_REQUIRED }} ტესტი, {{ \App\Models\LevelUpRule::DEFAULT_UP_THRESHOLD }}%-ზე მეტი და {{ \App\Models\LevelUpRule::DEFAULT_DOWN_THRESHOLD }}%-ზე ნაკლები. აქ შეგიძლია დაადგინო გამონაკლისი კონკრეტული კლასისთვის.
        </div>
        <form method="POST" action="{{ route('admin.level-rules.store') }}">
            @csrf
            <select name="grade_id" class="fc" required>
                <option value="">კლასი</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
            @error('grade_id')<div class="err">{{ $message }}</div>@enderror

            <div class="fl">რამდენი ტესტის შემდეგ მოწმდეს</div>
            <input type="number" name="tests_required" class="fc" placeholder="ტესტების რაოდენობა" min="1" max="50" value="{{ old('tests_required', \App\Models\LevelUpRule::DEFAULT_TESTS_REQUIRED) }}" required>

            <div class="fl">რომელი % -ის ზემოთ აიწევს დონე</div>
            <input type="number" name="up_threshold" class="fc" placeholder="ასაწევი %" min="1" max="100" value="{{ old('up_threshold', \App\Models\LevelUpRule::DEFAULT_UP_THRESHOLD) }}" required>

            <div class="fl">რომელი % -ის ქვემოთ დაიწევს დონე</div>
            <input type="number" name="down_threshold" class="fc" placeholder="დასაწევი %" min="0" max="99" value="{{ old('down_threshold', \App\Models\LevelUpRule::DEFAULT_DOWN_THRESHOLD) }}" required>
            @error('down_threshold')<div class="err">{{ $message }}</div>@enderror

            <button type="submit" class="btn">+ დამატება</button>
        </form>
    </div>

    <div class="card-dark">
        <div class="card-label">არსებული წესები · {{ $rows->count() }}</div>
        @forelse($rows as $row)
        <div class="row">
            <div class="row-display" id="ld{{ $row->id }}">
                <span>
                    <span class="pill">{{ $row->grade->name ?? '—' }}</span>
                    <span class="pill">{{ $row->tests_required }} ტესტი</span>
                    <span class="pill pill-up">↑ {{ $row->up_threshold }}%+</span>
                    <span class="pill pill-down">↓ &lt;{{ $row->down_threshold }}%</span>
                </span>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                    <button type="button" class="btn-edit" onclick="lrEdit({{ $row->id }})" title="ედიტი">✎</button>
                    <form method="POST" action="{{ route('admin.level-rules.destroy', $row) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                    </form>
                </div>
            </div>
            <div class="row-edit" id="le{{ $row->id }}">
                <form method="POST" action="{{ route('admin.level-rules.update', $row) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;width:100%;">
                    @csrf @method('PUT')
                    <input type="number" name="tests_required" class="fc" value="{{ $row->tests_required }}" min="1" max="50" style="flex:1;min-width:90px;margin-bottom:0;" required>
                    <input type="number" name="up_threshold" class="fc" value="{{ $row->up_threshold }}" min="1" max="100" style="flex:1;min-width:90px;margin-bottom:0;" required>
                    <input type="number" name="down_threshold" class="fc" value="{{ $row->down_threshold }}" min="0" max="99" style="flex:1;min-width:90px;margin-bottom:0;" required>
                    <button type="submit" class="btn">შენახვა</button>
                    <button type="button" class="btn-del" onclick="lrCancel({{ $row->id }})">✕</button>
                </form>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">ჯერ წესი არ დამატებულა — ყველა კლასი ნაგულისხმევ მნიშვნელობებზეა</div>
        @endforelse
    </div>
</div>
<script>
function lrEdit(id) {
    document.getElementById('ld' + id).style.display = 'none';
    document.getElementById('le' + id).style.display = 'flex';
}
function lrCancel(id) {
    document.getElementById('ld' + id).style.display = 'flex';
    document.getElementById('le' + id).style.display = 'none';
}
</script>
@endsection
