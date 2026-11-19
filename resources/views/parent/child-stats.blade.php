@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero dark">
        <span class="ks-cp-hero-ic" aria-hidden="true">📊</span>
        <span class="ks-cp-eyebrow" style="color:#FFC49A;">ტესტების ისტორია და შედეგები</span>
        <h1 class="ks-cp-title">{{ $child->name }} · ანალიტიკა</h1>
    </div>

    <div class="ks-cp-stats">
        <div class="ks-cp-stat"><b>{{ $totalTests }}</b><span>ტესტი სულ</span></div>
        <div class="ks-cp-stat"><b>{{ $avgScore !== null ? $avgScore . '%' : '—' }}</b><span>საშუალო შედეგი</span></div>
        <div class="ks-cp-stat"><b>{{ $todayCount }}@if($required > 0)<span style="font-size:18px;color:#B5AAA0;">/{{ $required }}</span>@endif</b><span>დღეს</span></div>
    </div>

    @if($topicStats->count())
    <div class="ks-cp-acc open" id="secTopics">
        <div class="ks-cp-acc-h" onclick="toggleSection('secTopics')">
            <span class="ks-cp-row-ic" style="background:#EEE8FF;">📚</span>
            <span class="ks-cp-acc-t">თემების მიხედვით</span>
            <span class="ks-cp-chip">{{ $topicStats->count() }}</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            <div class="ks-cp-list">
            @foreach($topicStats as $topicName => $levels)
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;padding:12px 14px;background:#FFF8F1;border-radius:16px;">
                <span style="flex:1 1 180px;font-weight:800;">{{ $topicName }}</span>
                <span style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($levels as $lvl)
                    <span class="ks-cp-pct {{ $lvl->pct >= 80 ? 'hi' : ($lvl->pct >= 50 ? 'mid' : 'lo') }}" style="font-size:12px;">დონე {{ $lvl->difficulty }} · {{ $lvl->pct }}%</span>
                    @endforeach
                </span>
            </div>
            @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="ks-cp-acc open" id="secTests">
        <div class="ks-cp-acc-h" onclick="toggleSection('secTests')">
            <span class="ks-cp-row-ic" style="background:#FFE3CC;">📋</span>
            <span class="ks-cp-acc-t">ტესტების ისტორია</span>
            <span class="ks-cp-chip">{{ $totalTests }}</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            <div class="ks-cp-list">
            @forelse($tests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="ks-cp-row" style="background:#FFF8F1;box-shadow:none;">
                <span class="ks-cp-row-ic" style="background:#fff;">{{ $test->theme?->icon ?? '📝' }}</span>
                <span class="ks-cp-row-b">
                    <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
                    <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
                </span>
                <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
                <i class="bi bi-chevron-right ks-cp-row-end"></i>
            </a>
            @empty
            <div class="ks-cp-row-s">ტესტები ჯერ არ დაწერილა</div>
            @endforelse
            </div>
        </div>
    </div>

    @if($oldGrades->isNotEmpty())
    <h2 class="ks-cp-h2">ძველი კლასები</h2>
    <div class="ks-cp-list">
    @foreach($oldGrades as $grade)
    @php
        $gTests   = $oldGradeTests->get($grade->id, collect());
        $gHist    = $gradeHistory->get($grade->id);
    @endphp
    <div class="ks-cp-acc" id="secOldGrade{{ $grade->id }}">
        <div class="ks-cp-acc-h" onclick="toggleSection('secOldGrade{{ $grade->id }}')">
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
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="ks-cp-row" style="background:#FFF8F1;box-shadow:none;">
                <span class="ks-cp-row-ic" style="background:#fff;">{{ $test->theme?->icon ?? '📝' }}</span>
                <span class="ks-cp-row-b">
                    <span class="ks-cp-row-t">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</span>
                    <span class="ks-cp-row-s">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
                </span>
                <span class="ks-cp-pct {{ $pct >= 80 ? 'hi' : ($pct >= 50 ? 'mid' : 'lo') }}">{{ $pct }}%</span>
                <i class="bi bi-chevron-right ks-cp-row-end"></i>
            </a>
            @empty
            <div class="ks-cp-row-s">ამ კლასში ტესტები არ დაწერილა</div>
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
