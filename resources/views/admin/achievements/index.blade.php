@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 900px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .aw-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; text-decoration:none; display:inline-block; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-add { background:#eef2ff; border-color:#c7d2fe; color:#4338ca; }
    .btn-add:hover { border-color:#818cf8; }

    .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:14px; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.04); position:relative; }
    .card.inactive { opacity:0.5; }
    .card-img { width:56px; height:56px; object-fit:contain; margin:0 auto 8px; display:block; }
    .card-emoji { font-size:2.2rem; margin-bottom:8px; }
    .card-name { font-size:0.82rem; color:#1e293b; margin-bottom:4px; }
    .card-meta { font-size:0.62rem; color:#94a3b8; margin-bottom:10px; }
    .card-actions { display:flex; gap:6px; justify-content:center; align-items:center; flex-wrap:wrap; }
    .toggle-btn { font-family:'Goldman',monospace; font-size:0.6rem; letter-spacing:0.05em; border-radius:20px; padding:4px 10px; cursor:pointer; border:none; }
    .toggle-btn.on { background:#dcfce7; color:#15803d; }
    .toggle-btn.off { background:#f1f5f9; color:#94a3b8; }
    .btn-edit-sm { color:#6366f1; font-size:0.7rem; text-decoration:none; padding:4px 8px; }
    .btn-del-sm { color:#cbd5e1; font-size:0.7rem; background:none; border:none; cursor:pointer; padding:4px 8px; }
    .btn-del-sm:hover { color:#ef4444; }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="aw-head">
        <div style="font-size:0.68rem;color:#94a3b8;letter-spacing:0.12em;text-transform:uppercase;">მიღწევები · {{ $achievements->count() }}</div>
        <a href="{{ route('admin.achievements.create') }}" class="btn btn-add">+ ახალი მიღწევა</a>
    </div>

    <div class="grid">
        @forelse($achievements as $ach)
        @php $topTier = $ach->tiers->last(); @endphp
        <div class="card {{ $ach->is_active ? '' : 'inactive' }}">
            @if($topTier?->imageUrl())
            <img src="{{ $topTier->imageUrl() }}" class="card-img" alt="">
            @else
            <div class="card-emoji">🏅</div>
            @endif
            <div class="card-name">{{ $ach->name }}</div>
            @if($ach->theme)
            <div style="font-size:0.6rem;color:#7c3aed;margin-bottom:2px;">{{ $ach->theme->icon ?? '' }} მხოლოდ {{ $ach->theme->name }}</div>
            @endif
            <div class="card-meta">
                {{ \App\Models\Achievement::CONDITION_TYPES[$ach->condition_type] ?? $ach->condition_type }}
                @if($ach->tiers->count() > 1) · {{ $ach->tiers->count() }} დონე @endif
                @if($ach->daily_limit) · დღეში 1-ჯერ @endif
            </div>
            <div class="card-actions">
                <form method="POST" action="{{ route('admin.achievements.toggle', $ach) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="toggle-btn {{ $ach->is_active ? 'on' : 'off' }}">
                        {{ $ach->is_active ? '● აქტიური' : '○ გათიშული' }}
                    </button>
                </form>
                <a href="{{ route('admin.achievements.edit', $ach) }}" class="btn-edit-sm">✎ რედ.</a>
                <form method="POST" action="{{ route('admin.achievements.destroy', $ach) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del-sm" onclick="return confirm('წაიშალოს „{{ $ach->name }}“?')">✕</button>
                </form>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">მიღწევა არ არის დამატებული</div>
        @endforelse
    </div>
</div>
@endsection
