@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero">
        <span class="ks-cp-hero-ic" aria-hidden="true">📋</span>
        <span class="ks-cp-eyebrow">ყველა შესრულებული ტესტი ერთ ადგილას</span>
        <h1 class="ks-cp-title">ჩემი ტესტები</h1>
    </div>

    @if($activeTest)
    <a href="{{ route('test.show', $activeTest) }}" class="ks-market" style="background:#C24A0A;">
        <i class="bi bi-pencil-fill" style="color:#fff;"></i>
        <span class="ks-market-t"><b>გააგრძელე ტესტი</b><span style="color:#FFE3CC;">დაწყებული ტესტი გელოდება</span></span>
        <i class="bi bi-arrow-right" style="font-size:20px;color:#fff;"></i>
    </a>
    @endif

    @if($totalTests > 0)
    <div class="ks-cp-stats">
        <div class="ks-cp-stat"><b>{{ $totalTests }}</b><span>ტესტი სულ</span></div>
        <div class="ks-cp-stat"><b>{{ $avgScore }}%</b><span>საშუალო შედეგი</span></div>
    </div>
    @endif

    <div class="ks-cp-list">
    @forelse($tests as $test)
    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <a href="{{ route('my.test.show', $test) }}" class="ks-cp-row">
        <span class="ks-cp-row-ic">{{ $test->theme?->icon ?? '📝' }}</span>
        <span class="ks-cp-row-b">
            <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
            <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
        </span>
        <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
        <i class="bi bi-chevron-right ks-cp-row-end"></i>
    </a>
    @empty
    <div class="ks-cp-empty">
        <div class="ks-cp-empty-ic">📝</div>
        <div class="ks-cp-empty-t">ჯერ ტესტი არ გაქვს დაწერილი</div>
        <div class="ks-cp-empty-s">დაიწყე დღევანდელი მისია მთავარ გვერდზე</div>
    </div>
    @endforelse
    </div>

    @if($oldGrades->isNotEmpty())
    <h2 class="ks-cp-h2">ძველი კლასები</h2>
    <div class="ks-cp-list">
    @foreach($oldGrades as $grade)
    @php
        $gTests = $oldGradeTests->get($grade->id, collect());
        $gHist  = $gradeHistory->get($grade->id);
    @endphp
    <div class="ks-cp-acc" id="secOldGrade{{ $grade->id }}">
        <div class="ks-cp-acc-h" onclick="toggleOldGrade('secOldGrade{{ $grade->id }}')">
            <span class="ks-cp-acc-t">{{ $grade->name }}</span>
            <span class="ks-cp-chip">{{ $gTests->count() }} ტესტი</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            @if($gHist)
            <div class="ks-cp-row-s" style="margin-bottom:10px;">საბოლოო დონე: {{ $gHist->difficulty }} · სულ {{ $gHist->tests_completed }} ტესტი</div>
            @endif
            <div class="ks-cp-list">
            @forelse($gTests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('my.test.show', $test) }}" class="ks-cp-row" style="background:#F7F6F3;box-shadow:none;">
                <span class="ks-cp-row-ic" style="background:#fff;">{{ $test->theme?->icon ?? '📝' }}</span>
                <span class="ks-cp-row-b">
                    <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
                    <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
                </span>
                <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
                <i class="bi bi-chevron-right ks-cp-row-end"></i>
            </a>
            @empty
            <div class="ks-cp-row-s">ტესტები არ დაწერილა</div>
            @endforelse
            </div>
        </div>
    </div>
    @endforeach
    </div>
    @endif
</div>
<script>
function toggleOldGrade(id) {
    document.getElementById(id).classList.toggle('open');
}
</script>
@endsection
