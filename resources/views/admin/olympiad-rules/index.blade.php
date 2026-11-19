@extends('layouts.app')
{{-- redesign-kidsmart: წესები → ოლიმპიადა (დიზაინი A7) --}}
@section('content')
@include('admin.partials.rules-tabs', ['active' => 'olympiad'])

<div class="ks-note">
    გლობალური პარამეტრები ვრცელდება ყველა კლასზე, გარდა ქვემოთ დამატებული გამონაკლისებისა.
    ცარიელი თარიღი ნიშნავს, რომ ოლიმპიადა ჯერ არ არის დანიშნული. ბავშვი დაიშვება, თუ ბოლო N დღეში საკმარისი ტესტი დაწერა.
</div>

<form method="POST" action="{{ route('admin.olympiad-rules.global') }}" style="background:#1D1A17;color:#fff;border-radius:22px;padding:18px;display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
    @csrf @method('PUT')
    <span style="flex:1 1 100%;font-size:16px;font-weight:800;">გლობალური ნაგულისხმევი — ყველა კლასზე</span>
    <div class="ks-field" style="flex:1 1 160px;"><label class="ks-label" style="color:#E8DCCF;" for="ogDate">ოლიმპიადის თარიღი</label><input type="date" id="ogDate" name="olympiad_date" class="ks-input" value="{{ old('olympiad_date', optional($global?->olympiad_date)->format('Y-m-d')) }}"></div>
    <div class="ks-field" style="flex:1 1 150px;"><label class="ks-label" style="color:#E8DCCF;" for="ogTests">ტესტი ბოლო N დღეში</label><input type="number" id="ogTests" name="tests_required" class="ks-input" min="1" max="50" value="{{ old('tests_required', $global?->tests_required ?? \App\Models\OlympiadRule::DEFAULT_TESTS_REQUIRED) }}" required></div>
    <div class="ks-field" style="flex:1 1 150px;"><label class="ks-label" style="color:#E8DCCF;" for="ogDays">დღეები (N)</label><input type="number" id="ogDays" name="days_window" class="ks-input" min="1" max="90" value="{{ old('days_window', $global?->days_window ?? \App\Models\OlympiadRule::DEFAULT_DAYS_WINDOW) }}" required></div>
    <div class="ks-field" style="flex:1 1 150px;"><label class="ks-label" style="color:#E8DCCF;" for="ogQ">კითხვა ოლიმპიადაში</label><input type="number" id="ogQ" name="questions_count" class="ks-input" min="1" max="100" value="{{ old('questions_count', $global?->questions_count ?? \App\Models\OlympiadRule::DEFAULT_QUESTIONS_COUNT) }}" required></div>
    <button type="submit" class="ks-btn" style="background:#F7883E;color:#1D1A17;">შენახვა</button>
</form>

<form method="POST" action="{{ route('admin.olympiad-rules.store') }}" class="ks-card" style="display:flex;flex-direction:column;gap:12px;">
    @csrf
    <h2 class="ks-h2">გამონაკლისი კონკრეტული კლასისთვის</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div class="ks-field" style="flex:1 1 150px;">
            <label class="ks-label" for="orGrade">კლასი</label>
            <select name="grade_id" id="orGrade" class="ks-select" required>
                <option value="">— აირჩიე —</option>
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ks-field" style="flex:1 1 150px;"><label class="ks-label" for="orDate">თარიღი</label><input type="date" id="orDate" name="olympiad_date" class="ks-input" value="{{ old('olympiad_date') }}"></div>
        <div class="ks-field" style="flex:1 1 120px;"><label class="ks-label" for="orTests">ტესტი</label><input type="number" id="orTests" name="tests_required" class="ks-input" min="1" max="50" value="{{ old('tests_required', \App\Models\OlympiadRule::DEFAULT_TESTS_REQUIRED) }}" required></div>
        <div class="ks-field" style="flex:1 1 120px;"><label class="ks-label" for="orDays">დღე (N)</label><input type="number" id="orDays" name="days_window" class="ks-input" min="1" max="90" value="{{ old('days_window', \App\Models\OlympiadRule::DEFAULT_DAYS_WINDOW) }}" required></div>
        <div class="ks-field" style="flex:1 1 120px;"><label class="ks-label" for="orQ">კითხვები</label><input type="number" id="orQ" name="questions_count" class="ks-input" min="1" max="100" value="{{ old('questions_count', \App\Models\OlympiadRule::DEFAULT_QUESTIONS_COUNT) }}" required></div>
        <button type="submit" class="ks-btn ks-btn-primary">+ დამატება</button>
    </div>
    @error('grade_id')<span class="ks-chip ks-chip-red" style="align-self:flex-start;">{{ $message }}</span>@enderror
</form>

<div class="ks-card ks-card-tight">
    <h2 class="ks-h2" style="padding:6px 6px 10px;">კლასების გამონაკლისები · {{ $rows->count() }}</h2>
    <div class="ks-table-wrap">
        <table class="ks-table" style="min-width:600px;">
            <thead><tr><th>კლასი</th><th>თარიღი</th><th>დაშვება</th><th>კითხვები</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr id="od{{ $row->id }}">
                    <td style="font-weight:800;">{{ $row->grade->name ?? '—' }}</td>
                    <td>@if($row->olympiad_date)<span class="ks-chip ks-chip-yellow">{{ $row->olympiad_date->format('d.m.Y') }}</span>@else<span class="ks-chip">არ არის დანიშნული</span>@endif</td>
                    <td>{{ $row->tests_required }} ტესტი / {{ $row->days_window }} დღე</td>
                    <td style="font-weight:800;">{{ $row->questions_count }} კითხვა</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button type="button" class="ks-btn ks-btn-soft ks-btn-sm" onclick="olrEdit({{ $row->id }})">რედ.</button>
                        <form method="POST" action="{{ route('admin.olympiad-rules.destroy', $row) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="წაშლა">×</button>
                        </form>
                    </td>
                </tr>
                <tr id="oe{{ $row->id }}" style="display:none;background:#FFF4EA;">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.olympiad-rules.update', $row) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <div class="ks-field"><label class="ks-label" for="oed{{ $row->id }}">თარიღი</label><input type="date" id="oed{{ $row->id }}" name="olympiad_date" class="ks-input" value="{{ optional($row->olympiad_date)->format('Y-m-d') }}" style="width:160px;"></div>
                            <div class="ks-field"><label class="ks-label" for="oet{{ $row->id }}">ტესტი</label><input type="number" id="oet{{ $row->id }}" name="tests_required" class="ks-input" value="{{ $row->tests_required }}" min="1" max="50" style="width:90px;" required></div>
                            <div class="ks-field"><label class="ks-label" for="oew{{ $row->id }}">დღე</label><input type="number" id="oew{{ $row->id }}" name="days_window" class="ks-input" value="{{ $row->days_window }}" min="1" max="90" style="width:90px;" required></div>
                            <div class="ks-field"><label class="ks-label" for="oeq{{ $row->id }}">კითხვა</label><input type="number" id="oeq{{ $row->id }}" name="questions_count" class="ks-input" value="{{ $row->questions_count }}" min="1" max="100" style="width:90px;" required></div>
                            <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                            <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="olrCancel({{ $row->id }})">გაუქმება</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="ks-muted">ჯერ გამონაკლისი არ დამატებულა — ყველა კლასი გლობალურ პარამეტრებზეა</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function olrEdit(id) {
    document.getElementById('od' + id).style.display = 'none';
    document.getElementById('oe' + id).style.display = 'table-row';
}
function olrCancel(id) {
    document.getElementById('od' + id).style.display = 'table-row';
    document.getElementById('oe' + id).style.display = 'none';
}
</script>
@endsection
