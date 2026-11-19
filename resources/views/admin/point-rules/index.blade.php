@extends('layouts.app')
{{-- redesign-kidsmart: წესები → ქულები (დიზაინი A7) --}}
@section('content')
@include('admin.partials.rules-tabs', ['active' => 'points'])

<div class="ks-note">
    რამდენი ქულა (მონეტა) ერიცხება ბავშვს თითო სწორ პასუხზე — ცალ-ცალკე ტესტზე, ვარჯიშზე და ოლიმპიადაზე.
    „ყველა“ ნიშნავს, რომ წესი ყველა კლასზე/დონეზე ვრცელდება, გარდა კონკრეტული გამონაკლისებისა.
    თუ არაფერია მითითებული, ნაგულისხმევი — <b>{{ \App\Models\PointRule::DEFAULT_POINTS }} ქულაა</b>.
</div>

<form method="POST" action="{{ route('admin.point-rules.store') }}" class="ks-card" style="display:flex;flex-direction:column;gap:12px;">
    @csrf
    <h2 class="ks-h2">ახალი წესი</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div class="ks-field" style="flex:1 1 160px;">
            <label class="ks-label" for="prGrade">კლასი</label>
            <select name="grade_id" id="prGrade" class="ks-select" onchange="prApplyGradeMaxLevel(this.value)">
                <option value="">ყველა კლასი</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" data-max-level="{{ $grade->max_level }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 140px;">
            <label class="ks-label" for="prDifficulty">დონე</label>
            <select name="difficulty" id="prDifficulty" class="ks-select">
                <option value="">ყველა დონე</option>
                @for($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" {{ old('difficulty') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 140px;">
            <label class="ks-label" for="prContext">კონტექსტი</label>
            <select name="context" id="prContext" class="ks-select" required>
                <option value="">— აირჩიე —</option>
                <option value="test" {{ old('context') == 'test' ? 'selected' : '' }}>ტესტი</option>
                <option value="practice" {{ old('context') == 'practice' ? 'selected' : '' }}>ვარჯიში</option>
                <option value="olympiad" {{ old('context') == 'olympiad' ? 'selected' : '' }}>ოლიმპიადა</option>
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="prPoints">ქულა თითო სწორზე</label>
            <input type="number" id="prPoints" name="points_per_correct" class="ks-input" min="0" max="100" step="0.01" value="{{ old('points_per_correct', \App\Models\PointRule::DEFAULT_POINTS) }}" required>
        </div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </div>
    @error('context')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
</form>

<div class="ks-card ks-card-tight">
    <h2 class="ks-h2" style="padding:6px 6px 10px;">არსებული წესები · {{ $rows->count() }}</h2>
    @php $__ctx = ['test' => ['ტესტი', 'ks-chip-orange'], 'practice' => ['ვარჯიში', 'ks-chip-blue'], 'olympiad' => ['ოლიმპიადა', 'ks-chip-yellow']]; @endphp
    <div class="ks-table-wrap">
        <table class="ks-table" style="min-width:560px;">
            <thead><tr><th>კლასი</th><th>დონე</th><th>კონტექსტი</th><th>ქულა</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr id="pd{{ $row->id }}">
                    <td>{{ $row->grade->name ?? 'ყველა კლასი' }}</td>
                    <td>{{ $row->difficulty ? 'დონე ' . $row->difficulty : 'ყველა დონე' }}</td>
                    <td><span class="ks-chip {{ $__ctx[$row->context][1] ?? '' }}">{{ $__ctx[$row->context][0] ?? $row->context }}</span></td>
                    <td style="font-weight:800;">{{ $row->points_per_correct }} ქულა</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" class="ks-btn ks-btn-soft ks-btn-sm" onclick="prEdit({{ $row->id }})">რედ.</button>
                        <form method="POST" action="{{ route('admin.point-rules.destroy', $row) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                        </form>
                    </td>
                </tr>
                <tr id="pe{{ $row->id }}" style="display:none;background:#FFF4EA;">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.point-rules.update', $row) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <label class="ks-label" for="pp{{ $row->id }}">ქულა:</label>
                            <input type="number" id="pp{{ $row->id }}" name="points_per_correct" class="ks-input" value="{{ $row->points_per_correct }}" min="0" max="100" step="0.01" style="width:120px;" required>
                            <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                            <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="prCancel({{ $row->id }})">გაუქმება</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="ks-muted">ჯერ წესი არ დამატებულა — ყველგან ნაგულისხმევი {{ \App\Models\PointRule::DEFAULT_POINTS }} ქულაა</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function prEdit(id) {
    document.getElementById('pd' + id).style.display = 'none';
    document.getElementById('pe' + id).style.display = 'table-row';
}
function prCancel(id) {
    document.getElementById('pd' + id).style.display = 'table-row';
    document.getElementById('pe' + id).style.display = 'none';
}
// დონეების შეზღუდვა არჩეული კლასის max_level-მდე („ყველა დონე“ ყოველთვის ჩანს)
function prApplyGradeMaxLevel(gradeId) {
    const gradeSelect = document.getElementById('prGrade');
    const opt = gradeSelect.querySelector('option[value="' + gradeId + '"]');
    const max = gradeId && opt ? (+opt.dataset.maxLevel || 5) : 5;
    const diffSelect = document.getElementById('prDifficulty');
    Array.from(diffSelect.options).forEach(o => {
        if (o.value === '') { o.hidden = false; return; }
        o.hidden = (+o.value) > max;
    });
    if (diffSelect.value && (+diffSelect.value) > max) diffSelect.value = '';
}
prApplyGradeMaxLevel(document.getElementById('prGrade').value);
</script>
@endsection
