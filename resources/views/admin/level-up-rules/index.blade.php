@extends('layouts.app')
{{-- redesign-kidsmart: წესები → დონის ცვლილება (დიზაინი A7) --}}
@section('content')
@include('admin.partials.rules-tabs', ['active' => 'level'])

<div class="ks-note">
    დონე მოწმდება ყოველ N-ე დასრულებული ტესტიდან, ბოლო N ტესტის საერთო შედეგით: ზედა ზღვარზე მეტი — დონე <b>+1</b>
    (არაუმეტეს კლასის მაქსიმუმისა, იხ. <a href="{{ route('admin.grades.index') }}" style="color:inherit;font-weight:800;">კლასები</a>),
    ქვედა ზღვარზე ნაკლები — დონე <b>−1</b> (მინ. 1), შუალედში — უცვლელი.
    ნაგულისხმევი: {{ \App\Models\LevelUpRule::DEFAULT_TESTS_REQUIRED }} ტესტი, ≥ {{ \App\Models\LevelUpRule::DEFAULT_UP_THRESHOLD }}% აწევა, &lt; {{ \App\Models\LevelUpRule::DEFAULT_DOWN_THRESHOLD }}% დაწევა.
</div>

<form method="POST" action="{{ route('admin.level-rules.store') }}" class="ks-card" style="display:flex;flex-direction:column;gap:12px;">
    @csrf
    <h2 class="ks-h2">გამონაკლისი კლასისთვის</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div class="ks-field" style="flex:1 1 160px;">
            <label class="ks-label" for="lrGrade">კლასი</label>
            <select name="grade_id" id="lrGrade" class="ks-select" required>
                <option value="">— აირჩიე —</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="lrTests">რამდენი ტესტის შემდეგ</label>
            <input type="number" id="lrTests" name="tests_required" class="ks-input" min="1" max="50" value="{{ old('tests_required', \App\Models\LevelUpRule::DEFAULT_TESTS_REQUIRED) }}" required>
        </div>
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="lrUp">აწევა, თუ % მეტია</label>
            <input type="number" id="lrUp" name="up_threshold" class="ks-input" min="1" max="100" value="{{ old('up_threshold', \App\Models\LevelUpRule::DEFAULT_UP_THRESHOLD) }}" required>
        </div>
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="lrDown">დაწევა, თუ % ნაკლებია</label>
            <input type="number" id="lrDown" name="down_threshold" class="ks-input" min="0" max="99" value="{{ old('down_threshold', \App\Models\LevelUpRule::DEFAULT_DOWN_THRESHOLD) }}" required>
        </div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </div>
    @error('grade_id')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
    @error('down_threshold')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
</form>

<div class="ks-card ks-card-tight">
    <h2 class="ks-h2" style="padding:6px 6px 10px;">არსებული წესები · {{ $rows->count() }}</h2>
    <div class="ks-table-wrap">
        <table class="ks-table" style="min-width:560px;">
            <thead><tr><th>კლასი</th><th>ტესტი</th><th>აწევა</th><th>დაწევა</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr id="ld{{ $row->id }}">
                    <td style="font-weight:800;">{{ $row->grade->name ?? '—' }}</td>
                    <td>{{ $row->tests_required }} ტესტი</td>
                    <td><span class="ks-chip ks-chip-green">≥ {{ $row->up_threshold }}%</span></td>
                    <td><span class="ks-chip ks-chip-red">&lt; {{ $row->down_threshold }}%</span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" class="ks-btn ks-btn-soft ks-btn-sm" onclick="lrEdit({{ $row->id }})">რედ.</button>
                        <form method="POST" action="{{ route('admin.level-rules.destroy', $row) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                        </form>
                    </td>
                </tr>
                <tr id="le{{ $row->id }}" style="display:none;background:#FFF3EA;">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.level-rules.update', $row) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <div class="ks-field"><label class="ks-label" for="lt{{ $row->id }}">ტესტი</label><input type="number" id="lt{{ $row->id }}" name="tests_required" class="ks-input" value="{{ $row->tests_required }}" min="1" max="50" style="width:100px;" required></div>
                            <div class="ks-field"><label class="ks-label" for="lu{{ $row->id }}">აწევა %</label><input type="number" id="lu{{ $row->id }}" name="up_threshold" class="ks-input" value="{{ $row->up_threshold }}" min="1" max="100" style="width:100px;" required></div>
                            <div class="ks-field"><label class="ks-label" for="lw{{ $row->id }}">დაწევა %</label><input type="number" id="lw{{ $row->id }}" name="down_threshold" class="ks-input" value="{{ $row->down_threshold }}" min="0" max="99" style="width:100px;" required></div>
                            <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                            <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="lrCancel({{ $row->id }})">გაუქმება</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="ks-muted">ჯერ წესი არ დამატებულა — ყველა კლასი ნაგულისხმევ მნიშვნელობებზეა</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function lrEdit(id) {
    document.getElementById('ld' + id).style.display = 'none';
    document.getElementById('le' + id).style.display = 'table-row';
}
function lrCancel(id) {
    document.getElementById('ld' + id).style.display = 'table-row';
    document.getElementById('le' + id).style.display = 'none';
}
</script>
@endsection
