@extends('layouts.app')
{{-- redesign-kidsmart: თემატიკა (დიზაინი A6) --}}
@section('content')
<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">თემატიკის სიტყვები ავტომატურად ჩაჯდება კითხვებში — {NAME}, {TEAM}, {CITY}</span>
        <h1 class="ks-display ks-h1">თემატიკა და ცვლადები</h1>
    </div>
</header>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif

@php $__thBg = ['#DFF5E3', '#DCEBFF', '#FDE2EC', '#FFF1C2', '#EEE8FF', '#FFE3CC']; @endphp

<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
    <div style="flex:3 1 440px;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
        @forelse($themes as $theme)
        <div class="ks-card ks-card-tight" style="display:flex;flex-direction:column;gap:12px;{{ $theme->is_active ? '' : 'opacity:.65;' }}">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:26px;background:{{ $__thBg[$loop->index % count($__thBg)] }};">{{ $theme->icon }}</span>
                <span style="flex-grow:1;min-width:0;display:flex;flex-direction:column;">
                    <span style="font-size:16px;font-weight:800;">{{ $theme->name }}</span>
                    <span class="ks-muted" style="font-size:12px;">{{ $theme->variables_count }} ცვლადი</span>
                </span>
                <form method="POST" action="{{ route('admin.themes.toggle', $theme) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="ks-switch" role="switch" aria-checked="{{ $theme->is_active ? 'true' : 'false' }}" aria-label="{{ $theme->name }} — აქტიური"></button>
                </form>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.themes.variables', $theme) }}" class="ks-btn ks-btn-dark ks-btn-sm" style="flex-grow:1;">ცვლადების მართვა</a>
                <form method="POST" action="{{ route('admin.themes.destroy', $theme) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                </form>
            </div>
        </div>
        @empty
        <div class="ks-card ks-muted">თემატიკა ჯერ არ არის დამატებული</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.themes.store') }}" class="ks-card" style="flex:1 1 260px;display:flex;flex-direction:column;gap:10px;">
        @csrf
        <h2 class="ks-h2">ახალი თემატიკა</h2>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <div class="ks-field" style="width:80px;"><label class="ks-label" for="thIcon">აიკონი</label><input type="text" id="thIcon" name="icon" class="ks-input" placeholder="⚽" maxlength="5" style="text-align:center;font-size:20px;" required></div>
            <div class="ks-field" style="flex-grow:1;"><label class="ks-label" for="thName">სახელი</label><input type="text" id="thName" name="name" class="ks-input" placeholder="მაგ: ფეხბურთი" required></div>
        </div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </form>
</div>
@endsection
