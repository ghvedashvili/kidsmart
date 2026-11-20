@extends('layouts.app')
{{-- redesign-kidsmart: კლასები (დიზაინი A9) --}}
@section('content')
@include('admin.partials.settings-tabs', ['active' => 'grades'])

@php $__gBg = ['#FFE3CC', '#FFF3EA', '#FFF3EA', '#DFF5E3', '#FFF1C2', '#FFF3EA']; @endphp

<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
    <div style="flex:3 1 420px;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
        @forelse($grades as $grade)
        <div style="border-radius:20px;padding:16px;display:flex;flex-direction:column;gap:8px;background:{{ $__gBg[$loop->index % count($__gBg)] }};{{ $grade->is_active ? '' : 'opacity:.6;' }}">
            {{-- ნახვის რეჟიმი --}}
            <div id="gd{{ $grade->id }}" style="display:flex;flex-direction:column;gap:6px;">
                <span style="font-size:40px;line-height:1;font-weight:900;font-stretch:68%;">{{ $grade->number }}</span>
                <span style="font-size:15px;font-weight:800;">{{ $grade->name }}</span>
                <span style="font-size:13px;color:var(--ks-ink-2);">მაქს. დონე: <b>{{ $grade->max_level }}</b></span>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.grades.toggle', $grade) }}" style="display:flex;align-items:center;gap:8px;">
                        @csrf @method('PATCH')
                        <button type="submit" class="ks-switch" role="switch" aria-checked="{{ $grade->is_active ? 'true' : 'false' }}" aria-label="{{ $grade->name }} — აქტიური"></button>
                        <span style="font-size:12px;font-weight:700;">{{ $grade->is_active ? 'აქტიური' : 'გათიშული' }}</span>
                    </form>
                    <span style="flex-grow:1;"></span>
                    <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="gradeEdit({{ $grade->id }})">რედ.</button>
                    <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                    </form>
                </div>
            </div>
            {{-- რედაქტირების რეჟიმი --}}
            <form id="ge{{ $grade->id }}" method="POST" action="{{ route('admin.grades.update', $grade) }}" style="display:none;flex-direction:column;gap:8px;">
                @csrf @method('PUT')
                <div class="ks-field"><label class="ks-label" for="gn{{ $grade->id }}">№</label><input type="number" id="gn{{ $grade->id }}" name="number" class="ks-input" value="{{ $grade->number }}" min="1" max="12" required></div>
                <div class="ks-field"><label class="ks-label" for="gm{{ $grade->id }}">სახელი</label><input type="text" id="gm{{ $grade->id }}" name="name" class="ks-input" value="{{ $grade->name }}" required></div>
                <div class="ks-field"><label class="ks-label" for="gl{{ $grade->id }}">მაქს. დონე</label><input type="number" id="gl{{ $grade->id }}" name="max_level" class="ks-input" value="{{ $grade->max_level }}" min="1" max="5" required></div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                    <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="gradeCancel({{ $grade->id }})">გაუქმება</button>
                </div>
            </form>
        </div>
        @empty
        <div class="ks-card ks-muted">კლასი ჯერ არ არის დამატებული</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.grades.store') }}" class="ks-card" style="flex:1 1 260px;display:flex;flex-direction:column;gap:10px;">
        @csrf
        <h2 class="ks-h2">კლასის დამატება</h2>
        <div class="ks-field"><label class="ks-label" for="gNum">ნომერი (1–12)</label><input type="number" id="gNum" name="number" class="ks-input" min="1" max="12" value="{{ old('number') }}" required></div>
        <div class="ks-field"><label class="ks-label" for="gName">სახელი</label><input type="text" id="gName" name="name" class="ks-input" placeholder="მაგ: VI კლასი" value="{{ old('name') }}" required></div>
        <div class="ks-field"><label class="ks-label" for="gMax">მაქსიმალური დონე (1–5)</label><input type="number" id="gMax" name="max_level" class="ks-input" min="1" max="5" value="{{ old('max_level', \App\Models\Grade::DEFAULT_MAX_LEVEL) }}" required></div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
        @error('number')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
        @error('max_level')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
        <span class="ks-muted" style="font-size:12px;line-height:1.5;">მაქს. დონე განსაზღვრავს, რამდენ სირთულემდე ავა ბავშვი ამ კლასში და რამდენი დონე გამოჩნდება კითხვის კონსტრუქტორში.</span>
    </form>
</div>

<script>
function gradeEdit(id) {
    document.getElementById('gd' + id).style.display = 'none';
    const f = document.getElementById('ge' + id);
    f.style.display = 'flex';
    f.querySelector('input[name="name"]').focus();
}
function gradeCancel(id) {
    document.getElementById('gd' + id).style.display = 'flex';
    document.getElementById('ge' + id).style.display = 'none';
}
</script>
@endsection
