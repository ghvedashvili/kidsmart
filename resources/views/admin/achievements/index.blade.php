@extends('layouts.app')
{{-- redesign-kidsmart: მიღწევები (დიზაინი A8) --}}
@section('content')
<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">მედლები, რომლებსაც ბავშვი აქტივობით იღებს · {{ $achievements->count() }}</span>
        <h1 class="ks-display ks-h1">მიღწევები</h1>
    </div>
    <a href="{{ route('admin.achievements.create') }}" class="ks-btn ks-btn-primary">+ ახალი მიღწევა</a>
</header>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif

@php
    $__ctxs = \App\Models\Achievement::CONTEXTS;
    $__used = $achievements->pluck('context')->unique();
    $__tierC = ['#D98B4A', '#9AA3B2', '#F5B301', '#F07A2E', '#1E8A4C'];
@endphp

<div role="tablist" aria-label="კატეგორია" style="display:flex;flex-wrap:wrap;gap:6px;">
    <button type="button" role="tab" class="ks-btn ks-btn-dark ks-btn-sm ach-filter" data-ctx="" onclick="achFilter(this)">ყველა</button>
    @foreach($__ctxs as $key => $label)
        @if($__used->contains($key))
        <button type="button" role="tab" class="ks-btn ks-btn-ghost ks-btn-sm ach-filter" data-ctx="{{ $key }}" onclick="achFilter(this)">{{ $label }}</button>
        @endif
    @endforeach
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px;">
    @forelse($achievements as $ach)
    @php $topTier = $ach->tiers->last(); @endphp
    <div class="ks-card ks-card-tight ach-card" data-ctx="{{ $ach->context }}" style="display:flex;flex-direction:column;gap:8px;{{ $ach->is_active ? '' : 'opacity:.6;' }}">
        <div style="display:flex;align-items:center;gap:10px;">
            @if($topTier?->imageUrl())
            <img src="{{ $topTier->imageUrl() }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
            @else
            <span style="display:flex;gap:4px;">
                @foreach($ach->tiers as $t)
                <span title="{{ $t->label }}" style="width:24px;height:24px;border-radius:50%;background:{{ $ach->tiers->count() === 1 ? '#F5B301' : ($__tierC[$loop->index] ?? '#F07A2E') }};"></span>
                @endforeach
            </span>
            @endif
            <span style="flex-grow:1;"></span>
            <form method="POST" action="{{ route('admin.achievements.toggle', $ach) }}">
                @csrf @method('PATCH')
                <button type="submit" class="ks-switch" role="switch" aria-checked="{{ $ach->is_active ? 'true' : 'false' }}" aria-label="{{ $ach->name }} — აქტიური"></button>
            </form>
        </div>
        <span style="font-size:15px;font-weight:800;">{{ $ach->name }}</span>
        @if($ach->description)<span style="font-size:13px;color:var(--ks-ink-2);">{{ $ach->description }}</span>@endif
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            <span class="ks-chip ks-chip-orange">{{ $__ctxs[$ach->context] ?? $ach->context }}</span>
            <span class="ks-chip">{{ \App\Models\Achievement::CONDITION_TYPES[$ach->condition_type] ?? $ach->condition_type }}</span>
            @if($ach->tiers->count() > 1)<span class="ks-chip ks-chip-purple">{{ $ach->tiers->count() }} დონე</span>@endif
            @if($ach->theme)<span class="ks-chip ks-chip-blue">{{ $ach->theme->icon ?? '' }} {{ $ach->theme->name }}</span>@endif
            @if($ach->game)<span class="ks-chip ks-mono">{{ $ach->game }}</span>@endif
        </div>
        <div style="display:flex;gap:8px;margin-top:auto;">
            <a href="{{ route('admin.achievements.edit', $ach) }}" class="ks-btn ks-btn-soft ks-btn-sm" style="flex-grow:1;">რედაქტირება</a>
            <form method="POST" action="{{ route('admin.achievements.destroy', $ach) }}" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს „{{ $ach->name }}“?')" aria-label="წაშლა">×</button>
            </form>
        </div>
    </div>
    @empty
    <div class="ks-card ks-muted">მიღწევა ჯერ არ არის დამატებული</div>
    @endforelse
</div>

<script>
function achFilter(btn) {
    const ctx = btn.dataset.ctx;
    document.querySelectorAll('.ach-filter').forEach(b => {
        const on = b === btn;
        b.classList.toggle('ks-btn-dark', on);
        b.classList.toggle('ks-btn-ghost', !on);
    });
    document.querySelectorAll('.ach-card').forEach(c => {
        c.style.display = !ctx || c.dataset.ctx === ctx ? 'flex' : 'none';
    });
}
</script>
@endsection
