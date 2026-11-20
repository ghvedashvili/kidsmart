@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero yellow">
        <span class="ks-cp-hero-ic" aria-hidden="true">🏆</span>
        <span class="ks-cp-eyebrow" style="color:#3D2A00;">ოლიმპიადის ტესტების ისტორია და შედეგები</span>
        <h1 class="ks-cp-title">{{ $child->name }} · ოლიმპიადა</h1>
    </div>
    @include('parent.partials.kid-switcher')

    <div class="ks-cp-stats">
        <div class="ks-cp-stat"><b>{{ $totalOlympiad }}</b><span>ოლიმპიადა სულ</span></div>
        <div class="ks-cp-stat"><b>{{ $avgScore !== null ? $avgScore . '%' : '—' }}</b><span>საშუალო შედეგი</span></div>
    </div>

    <div class="ks-cp-acc open" id="secOlympiad">
        <div class="ks-cp-acc-h" onclick="toggleSection('secOlympiad')">
            <span class="ks-cp-row-ic" style="background:#FFF1C9;">🏆</span>
            <span class="ks-cp-acc-t">ოლიმპიადის ისტორია</span>
            <span class="ks-cp-chip">{{ $totalOlympiad }}</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            <div class="ks-cp-list">
            @forelse($olympiadTests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="ks-cp-row" style="background:#FFF8F1;box-shadow:none;">
                <span class="ks-cp-row-ic" style="background:#fff;">🏆</span>
                <span class="ks-cp-row-b">
                    <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
                    <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
                </span>
                <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
                <i class="bi bi-chevron-right ks-cp-row-end"></i>
            </a>
            @empty
            <div class="ks-cp-row-s">ოლიმპიადაზე ჯერ არ დაწერილა</div>
            @endforelse
            </div>
        </div>
    </div>

    @if($oldGrades->isNotEmpty())
    <h2 class="ks-cp-h2">ძველი კლასები</h2>
    <div class="ks-cp-list">
    @foreach($oldGrades as $grade)
    @php $gOlyTests = $oldGradeOlympiadTests->get($grade->id, collect()); @endphp
    <div class="ks-cp-acc" id="secOldGrade{{ $grade->id }}">
        <div class="ks-cp-acc-h" onclick="toggleSection('secOldGrade{{ $grade->id }}')">
            <span class="ks-cp-acc-t">{{ $grade->name }}</span>
            <span class="ks-cp-chip">{{ $gOlyTests->count() }}</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            <div class="ks-cp-list">
            @forelse($gOlyTests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="ks-cp-row" style="background:#FFF8F1;box-shadow:none;">
                <span class="ks-cp-row-ic" style="background:#fff;">🏆</span>
                <span class="ks-cp-row-b">
                    <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
                    <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
                </span>
                <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
                <i class="bi bi-chevron-right ks-cp-row-end"></i>
            </a>
            @empty
            <div class="ks-cp-row-s">ამ კლასში ოლიმპიადაზე არ დაწერილა</div>
            @endforelse
            </div>
        </div>
    </div>
    @endforeach
    </div>
    @endif
</div>

<script>
function toggleSection(id) {
    document.getElementById(id).classList.toggle('open');
}
</script>
@endsection
