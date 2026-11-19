@extends('layouts.app')
@section('content')
@php $pct = $achievements->count() > 0 ? round($earned->count() / $achievements->count() * 100) : 0; @endphp
<div class="ks-cp">
    <div class="ks-cp-hero purple">
        <span class="ks-cp-hero-ic" aria-hidden="true">🏅</span>
        <span class="ks-cp-eyebrow">{{ $earned->count() }}/{{ $achievements->count() }} მედალი მოგებული — გააგრძელე!</span>
        <h1 class="ks-cp-title">ჩემი მედლები</h1>
    </div>

    <div class="ks-cp-stats">
        <div class="ks-cp-stat"><b>{{ (int) floor($setting?->coins ?? 0) }}</b><span>მონეტა</span></div>
        <div class="ks-cp-stat"><b>{{ $totalTests }}</b><span>ტესტი</span></div>
        <div class="ks-cp-stat"><b>{{ $earned->count() }}/{{ $achievements->count() }}</b><span>მიღწეული</span></div>
    </div>

    <div class="ks-cp-card ks-cp-progress">
        <div class="ks-cp-progress-top"><span>პროგრესი</span><span>{{ $pct }}%</span></div>
        <div class="ks-cp-progress-bar"><span style="width: {{ $pct }}%; background:#6D4FE0;"></span></div>
    </div>

    @if($marketRewards->count())
    <h2 class="ks-cp-h2">მარკეტის ჯილდოები</h2>
    <div class="ks-cp-grid">
        @foreach($marketRewards as $reward)
        <div class="ks-cp-item hl">
            <span class="ks-cp-item-badge">✓</span>
            <div class="ks-cp-item-ic">{{ $reward->item->icon }}</div>
            <div class="ks-cp-item-t">{{ $reward->item->title }}</div>
            <div class="ks-cp-item-s">{{ $reward->coins_spent }} მონეტა · {{ $reward->updated_at->format('d.m.Y') }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <h2 class="ks-cp-h2">მიღწევები</h2>
    <div class="ks-cp-grid">
        @foreach($achievements as $ach)
        @php
            $earnedRow   = $earned->get($ach->slug);
            $currentTier = $earnedRow->tier_level ?? 0;
            $isEarned    = $currentTier > 0;
            $displayTier = $isEarned ? $ach->tiers->firstWhere('level', $currentTier) : $ach->tiers->first();
        @endphp
        @if($displayTier)
        <div class="ks-cp-item {{ $isEarned ? '' : 'dim' }}">
            @if($isEarned)<span class="ks-cp-item-badge">✓</span>@endif
            @if($displayTier->imageUrl())
            <img src="{{ $displayTier->imageUrl() }}" alt="">
            @else
            <div class="ks-cp-item-ic">🏅</div>
            @endif
            <div class="ks-cp-item-t">{{ $displayTier->label }}</div>
            <div class="ks-cp-item-s">
                {{ $displayTier->threshold !== null ? str_replace('{n}', $displayTier->threshold, $ach->description ?? '') : $ach->description }}
            </div>
            @if($ach->tiers->count() > 1)
            <span class="ks-cp-chip" style="background:#EEE8FF;color:#4B33B8;">{{ $currentTier }}/{{ $ach->tiers->count() }} დონე</span>
            @endif
            @if($isEarned)
            <div class="ks-cp-item-s">{{ $earnedRow->earned_at->format('d.m.Y') }}</div>
            @endif
        </div>
        @endif
        @endforeach
    </div>

    @if($oldGrades->isNotEmpty())
    <h2 class="ks-cp-h2">ძველი კლასები</h2>
    <div class="ks-cp-list">
    @foreach($oldGrades as $grade)
    @php $gEarned = $oldGradeAchievements->get($grade->id, collect()); @endphp
    <div class="ks-cp-acc" id="secOldGrade{{ $grade->id }}">
        <div class="ks-cp-acc-h" onclick="toggleOldGrade('secOldGrade{{ $grade->id }}')">
            <span class="ks-cp-acc-t">{{ $grade->name }}</span>
            <span class="ks-cp-chip">{{ $gEarned->count() }} მედალი</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            @if($gEarned->isEmpty())
            <div class="ks-cp-row-s">ამ კლასში მედლები არ არის</div>
            @else
            <div class="ks-cp-grid">
                @foreach($achievements as $ach)
                @php
                    $row = $gEarned->get($ach->slug);
                    if (! $row) continue;
                    $tier = $ach->tiers->firstWhere('level', $row->tier_level);
                    if (! $tier) continue;
                @endphp
                <div class="ks-cp-item" style="background:#FFF8F1;box-shadow:none;">
                    <span class="ks-cp-item-badge">✓</span>
                    @if($tier->imageUrl())
                    <img src="{{ $tier->imageUrl() }}" alt="">
                    @else
                    <div class="ks-cp-item-ic">🏅</div>
                    @endif
                    <div class="ks-cp-item-t">{{ $tier->label }}</div>
                    <div class="ks-cp-item-s">{{ $row->earned_at->format('d.m.Y') }}</div>
                </div>
                @endforeach
            </div>
            @endif
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
