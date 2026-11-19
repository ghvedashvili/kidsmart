@extends('layouts.app')
{{-- redesign-kidsmart: პაკეტები (დიზაინი A9) --}}
@section('content')
@include('admin.partials.settings-tabs', ['active' => 'packages'])

@if(session('error'))<div class="ks-note" style="background:var(--ks-red-soft);color:var(--ks-red-dark);font-weight:700;">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="ks-note" style="background:var(--ks-red-soft);color:var(--ks-red-dark);">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

@php $__pBg = ['#FFFFFF', '#FFE3CC', '#EEE8FF', '#DCEBFF', '#DFF5E3']; @endphp

<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
    <div style="flex:3 1 420px;display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">
        @forelse($packages as $pkg)
        <div style="border-radius:20px;padding:16px;display:flex;flex-direction:column;gap:8px;border:1px solid var(--ks-line);background:{{ $__pBg[$loop->index % count($__pBg)] }};{{ $pkg->is_active ? '' : 'opacity:.65;' }}">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:20px;font-weight:800;flex-grow:1;">{{ $pkg->name }}</span>
                @if($pkg->is_free)<span class="ks-chip ks-chip-green">უფასო</span>@else<span class="ks-chip ks-chip-dark">ფასიანი</span>@endif
                @unless($pkg->is_active)<span class="ks-chip">გათიშული</span>@endunless
            </div>
            @if($pkg->description)<span style="font-size:13px;color:var(--ks-ink-2);">{{ $pkg->description }}</span>@endif
            <span style="font-size:28px;font-weight:900;font-stretch:72%;">{{ $pkg->price_monthly > 0 ? number_format($pkg->price_monthly, 2).'₾ / თვე' : 'უფასო' }}</span>
            @if($pkg->price_yearly > 0)<span style="font-size:13px;color:var(--ks-ink-2);">{{ number_format($pkg->price_yearly, 2) }}₾ / წელი</span>@endif
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                <span class="ks-chip" style="background:#fff;">{{ $pkg->maxChildrenLabel() }} ბავშვი</span>
                <span class="ks-chip" style="background:#fff;">სირთულე ≤ {{ $pkg->max_difficulty }}</span>
                <span class="ks-chip ks-mono" style="background:#fff;">{{ $pkg->slug }}</span>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" class="ks-btn ks-btn-dark ks-btn-sm" onclick="toggleEdit({{ $pkg->id }})">რედაქტირება</button>
                <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                </form>
            </div>

            <form id="edit-{{ $pkg->id }}" method="POST" action="{{ route('admin.packages.update', $pkg) }}" style="display:none;flex-direction:column;gap:8px;border-top:1px solid var(--ks-line);padding-top:10px;">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
                    <div class="ks-field"><label class="ks-label" for="pn{{ $pkg->id }}">სახელი</label><input id="pn{{ $pkg->id }}" name="name" class="ks-input" value="{{ old('name', $pkg->name) }}" required></div>
                    <div class="ks-field"><label class="ks-label" for="ps{{ $pkg->id }}">Slug</label><input id="ps{{ $pkg->id }}" name="slug" class="ks-input ks-mono" value="{{ old('slug', $pkg->slug) }}" required></div>
                    <div class="ks-field"><label class="ks-label" for="pm{{ $pkg->id }}">ფასი / თვე (₾)</label><input id="pm{{ $pkg->id }}" type="number" step="0.01" min="0" name="price_monthly" class="ks-input" value="{{ old('price_monthly', $pkg->price_monthly) }}"></div>
                    <div class="ks-field"><label class="ks-label" for="py{{ $pkg->id }}">ფასი / წელი (₾)</label><input id="py{{ $pkg->id }}" type="number" step="0.01" min="0" name="price_yearly" class="ks-input" value="{{ old('price_yearly', $pkg->price_yearly) }}"></div>
                    <div class="ks-field"><label class="ks-label" for="pk{{ $pkg->id }}">მაქს. ბავშვი (0 = ∞)</label><input id="pk{{ $pkg->id }}" type="number" min="0" name="max_children" class="ks-input" value="{{ old('max_children', $pkg->max_children) }}"></div>
                    <div class="ks-field"><label class="ks-label" for="pd{{ $pkg->id }}">მაქს. სირთულე</label><input id="pd{{ $pkg->id }}" type="number" min="1" max="5" name="max_difficulty" class="ks-input" value="{{ old('max_difficulty', $pkg->max_difficulty) }}"></div>
                    <div class="ks-field"><label class="ks-label" for="po{{ $pkg->id }}">რიგი</label><input id="po{{ $pkg->id }}" type="number" min="0" name="sort_order" class="ks-input" value="{{ old('sort_order', $pkg->sort_order) }}"></div>
                </div>
                <div class="ks-field"><label class="ks-label" for="pds{{ $pkg->id }}">აღწერა</label><input id="pds{{ $pkg->id }}" name="description" class="ks-input" value="{{ old('description', $pkg->description) }}"></div>
                <div style="display:flex;gap:14px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;"><input type="checkbox" name="is_free" value="1" {{ $pkg->is_free ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#C24A0A;">უფასო</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;"><input type="checkbox" name="is_active" value="1" {{ $pkg->is_active ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#C24A0A;">აქტიური</label>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                    <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="toggleEdit({{ $pkg->id }})">გაუქმება</button>
                </div>
            </form>
        </div>
        @empty
        <div class="ks-card ks-muted">პაკეტი ჯერ არ არის დამატებული.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.packages.store') }}" class="ks-card" style="flex:1 1 300px;display:flex;flex-direction:column;gap:8px;">
        @csrf
        <h2 class="ks-h2" style="margin-bottom:4px;">ახალი პაკეტი</h2>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
            <div class="ks-field"><label class="ks-label" for="nName">სახელი *</label><input id="nName" name="name" class="ks-input" placeholder="Standard" value="{{ old('name') }}" required></div>
            <div class="ks-field"><label class="ks-label" for="nSlug">Slug *</label><input id="nSlug" name="slug" class="ks-input ks-mono" placeholder="standard" value="{{ old('slug') }}" required></div>
            <div class="ks-field"><label class="ks-label" for="nPm">ფასი / თვე (₾)</label><input id="nPm" type="number" step="0.01" min="0" name="price_monthly" class="ks-input" value="{{ old('price_monthly', '0') }}"></div>
            <div class="ks-field"><label class="ks-label" for="nPy">ფასი / წელი (₾)</label><input id="nPy" type="number" step="0.01" min="0" name="price_yearly" class="ks-input" value="{{ old('price_yearly', '0') }}"></div>
            <div class="ks-field"><label class="ks-label" for="nKids">მაქს. ბავშვი (0 = ∞)</label><input id="nKids" type="number" min="0" name="max_children" class="ks-input" value="{{ old('max_children', '1') }}"></div>
            <div class="ks-field"><label class="ks-label" for="nDiff">მაქს. სირთულე</label><input id="nDiff" type="number" min="1" max="5" name="max_difficulty" class="ks-input" value="{{ old('max_difficulty', '5') }}"></div>
            <div class="ks-field"><label class="ks-label" for="nSort">რიგი</label><input id="nSort" type="number" min="0" name="sort_order" class="ks-input" value="{{ old('sort_order', $packages->count()) }}"></div>
        </div>
        <div class="ks-field"><label class="ks-label" for="nDesc">აღწერა</label><input id="nDesc" name="description" class="ks-input" placeholder="მოკლე აღწერა" value="{{ old('description') }}"></div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;"><input type="checkbox" name="is_free" value="1" {{ old('is_free') ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#C24A0A;">უფასო</label>
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;"><input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#C24A0A;">აქტიური</label>
        </div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </form>
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'flex' ? 'none' : 'flex';
}
</script>
@endsection
