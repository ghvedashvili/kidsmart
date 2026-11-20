@extends('layouts.app')
{{-- redesign-kidsmart: წესები → ტესტის ზომა (დიზაინი A7) --}}
@section('content')
@include('admin.partials.rules-tabs', ['active' => 'size'])

<div class="ks-note">
    ნაგულისხმევად ყველა ტესტს აქვს <b>{{ \App\Models\TestQuestionCount::DEFAULT_COUNT }} კითხვა</b>. აქ დაადგენ გამონაკლისს კლასის, დონის და თემატიკის მიხედვით —
    „ნებისმიერი“ ნიშნავს, რომ წესი ამ განზომილების ყველა მნიშვნელობაზე მოქმედებს. დონეები კლასის მაქსიმუმამდე იზღუდება.
</div>

<form method="POST" action="{{ route('admin.question-counts.store') }}" class="ks-card" style="display:flex;flex-direction:column;gap:12px;">
    @csrf
    <h2 class="ks-h2">ახალი წესი</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="qcGrade">კლასი</label>
            <select name="grade_id" id="qcGrade" class="ks-select" required onchange="qcApplyGradeMaxLevel(this.value)">
                <option value="">— აირჩიე —</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" data-max-level="{{ $grade->max_level }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="qcDifficulty">დონე</label>
            <select name="difficulty" id="qcDifficulty" class="ks-select">
                <option value="">ნებისმიერი დონე</option>
                @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" {{ old('difficulty') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 170px;">
            <label class="ks-label" for="qcTheme">თემატიკა</label>
            <select name="theme_id" id="qcTheme" class="ks-select">
                <option value="">ნებისმიერი თემატიკა</option>
                @foreach($themes as $theme)
                <option value="{{ $theme->id }}" {{ old('theme_id') == $theme->id ? 'selected' : '' }}>{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 120px;">
            <label class="ks-label" for="qcCount">კითხვა</label>
            <input type="number" id="qcCount" name="questions_count" class="ks-input" min="1" max="100" value="{{ old('questions_count') }}" required>
        </div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </div>
    @error('grade_id')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
    @error('difficulty')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
    @error('questions_count')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
</form>

<div class="ks-card ks-card-tight">
    <h2 class="ks-h2" style="padding:6px 6px 10px;">არსებული წესები · {{ $rows->count() }}</h2>
    <div class="ks-table-wrap">
        <table class="ks-table" style="min-width:560px;">
            <thead><tr><th>კლასი</th><th>დონე</th><th>თემატიკა</th><th>კითხვა</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr id="qd{{ $row->id }}">
                    <td style="font-weight:800;">{{ $row->grade->name ?? '—' }}</td>
                    <td>{{ $row->difficulty ? 'დონე ' . $row->difficulty : 'ნებისმიერი დონე' }}</td>
                    <td>{{ $row->theme ? (($row->theme->icon ?? '') . ' ' . $row->theme->name) : 'ნებისმიერი თემატიკა' }}</td>
                    <td style="font-weight:800;">{{ $row->questions_count }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" class="ks-btn ks-btn-soft ks-btn-sm" onclick="qcEdit({{ $row->id }})">რედ.</button>
                        <form method="POST" action="{{ route('admin.question-counts.destroy', $row) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                        </form>
                    </td>
                </tr>
                <tr id="qe{{ $row->id }}" style="display:none;background:#FFF3EA;">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.question-counts.update', $row) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <label class="ks-label" for="qq{{ $row->id }}">კითხვა:</label>
                            <input type="number" id="qq{{ $row->id }}" name="questions_count" class="ks-input" value="{{ $row->questions_count }}" min="1" max="100" style="width:110px;" required>
                            <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                            <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="qcCancel({{ $row->id }})">გაუქმება</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="ks-muted">ჯერ წესი არ დამატებულა — ყველა ტესტს {{ \App\Models\TestQuestionCount::DEFAULT_COUNT }} კითხვა ექნება</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function qcEdit(id) {
    document.getElementById('qd' + id).style.display = 'none';
    document.getElementById('qe' + id).style.display = 'table-row';
}
function qcCancel(id) {
    document.getElementById('qd' + id).style.display = 'table-row';
    document.getElementById('qe' + id).style.display = 'none';
}
// დონეების შეზღუდვა კლასის max_level-მდე („ნებისმიერი დონე“ ყოველთვის ჩანს)
function qcApplyGradeMaxLevel(gradeId) {
    const gradeSelect = document.getElementById('qcGrade');
    const opt = gradeSelect.querySelector('option[value="' + gradeId + '"]');
    const max = gradeId && opt ? (+opt.dataset.maxLevel || 5) : 5;
    const diffSelect = document.getElementById('qcDifficulty');
    Array.from(diffSelect.options).forEach(o => {
        if (o.value === '') { o.hidden = false; return; }
        o.hidden = (+o.value) > max;
    });
    if (diffSelect.value && (+diffSelect.value) > max) diffSelect.value = '';
}
qcApplyGradeMaxLevel(document.getElementById('qcGrade').value);
</script>
@endsection
