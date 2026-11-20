@extends('layouts.app')
@section('content')
@php
    // მშობლის ხედი: იგივე დიზაინი (7. მიღწევები), რაც ბავშვის გვერდზეა
    $__u = $child;
    // მიმდინარე მაჩვენებლები „შემდეგი მიზნის“ ბარათისთვის (მხოლოდ ის ტიპები, რისი დათვლაც ზუსტად შეიძლება)
    $__dates = $__u->tests()->whereNotNull('completed_at')->pluck('completed_at')->map(fn($d) => $d->format('Y-m-d'))
        ->merge(\App\Models\PracticeAnswerLog::where('child_id', $__u->id)->pluck('created_at')->map(fn($d) => $d->format('Y-m-d')))
        ->unique();
    $__streak = 0; $__cur = \Carbon\Carbon::today();
    if (!$__dates->contains($__cur->format('Y-m-d'))) { $__cur = $__cur->copy()->subDay(); }
    while ($__dates->contains($__cur->format('Y-m-d'))) { $__streak++; $__cur = $__cur->copy()->subDay(); }
    $__metric = [
        'test_count'         => $__u->tests()->where('is_olympiad', false)->whereNotNull('completed_at')->count(),
        'day_streak'         => $__streak,
        'practice_count'     => \App\Models\PracticeAnswerLog::where('child_id', $__u->id)->count(),
        'olympiad_completed' => $__u->tests()->where('is_olympiad', true)->whereNotNull('completed_at')->count(),
        'game_wins'          => (int) \App\Models\GameSession::where('child_id', $__u->id)->sum('wins'),
    ];
    $__next = null;
    foreach ($achievements as $__a) {
        if (!array_key_exists($__a->condition_type, $__metric)) continue;
        $__lvl  = $earned->get($__a->slug)->tier_level ?? 0;
        $__tier = $__a->tiers->firstWhere('level', $__lvl + 1);
        if (!$__tier || !$__tier->threshold) continue;
        $__val  = min($__metric[$__a->condition_type], $__tier->threshold);
        $__ratio = $__val / max(1, $__tier->threshold);
        if (!$__next || $__ratio > $__next['ratio']) {
            $__next = ['label' => $__tier->label, 'val' => $__val, 'max' => (int) $__tier->threshold, 'ratio' => $__ratio];
        }
    }

    $__tabs = ['test' => 'ტესტები', 'practice' => 'ვარჯიში', 'olympiad' => 'ოლიმპიადა', 'games' => 'თამაშები'];
    $__metal = [
        'gold'   => ['#F5B301', '#FFF1C2', 'ოქრო',     '#6B4A00'],
        'silver' => ['#9AA3B2', '#EEF0F4', 'ვერცხლი',  '#4A5363'],
        'bronze' => ['#D98B4A', '#FCE6D4', 'ბრინჯაო',  '#7A3F12'],
        'lock'   => ['#D9D4CC', '#FFFFFF', 'დახურული', '#6B625A'],
    ];
    $__total = $achievements->count();
@endphp
<style>
    .am { display: flex; flex-direction: column; gap: 14px; max-width: 760px; width: 100%; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; }
    .am-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
    .am-head small { font-size: 13px; color: #6B625A; }
    .am-title { margin: 4px 0 0; font-size: 46px; line-height: .95; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .am-count { display: flex; flex-direction: column; align-items: flex-end; flex-shrink: 0; }
    .am-count b { font-size: 24px; font-weight: 900; }
    .am-count b span { font-size: 15px; color: #6B625A; }
    .am-count small { font-size: 11px; }
    .am-next { background: #fff; border-radius: 22px; padding: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 0 #ECE9E4; }
    .am-next-ic { width: 48px; height: 48px; border-radius: 16px; background: #FFE3CC; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 24px; }
    .am-next-b { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
    .am-next-top { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; font-weight: 700; }
    .am-next-top span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .am-next-top span:last-child { color: #6B625A; flex-shrink: 0; }
    .am-bar { height: 8px; border-radius: 4px; background: #F1EFEB; overflow: hidden; }
    .am-bar span { display: block; height: 8px; border-radius: 4px; background: #E2590F; }
    .am-tabs { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 6px; background: #F1EFEB; border-radius: 999px; padding: 4px; }
    .am-tab { height: 40px; border: none; border-radius: 999px; font-family: inherit; font-size: 12px; font-weight: 700; cursor: pointer; background: transparent; color: #6B625A; padding: 0 4px; }
    .am-tab[aria-selected="true"] { background: #fff; color: #2B2622; box-shadow: 0 2px 6px rgba(150,80,20,.15); }
    .am-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
    .am-grid[hidden] { display: none; }
    .am-medal { border-radius: 20px; padding: 12px 6px; display: flex; flex-direction: column; align-items: center; gap: 6px; border: 2px solid transparent; text-align: center; }
    .am-medal.lock { border: 2px dashed #E6E2DC; }
    .am-ring { width: 54px; height: 54px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 4px rgba(255,255,255,.45); overflow: hidden; }
    .am-ring img { width: 40px; height: 40px; object-fit: contain; }
    .am-medal.lock .am-ring img { filter: grayscale(1); opacity: .5; }
    .am-name { font-size: 12px; font-weight: 700; line-height: 1.3; }
    .am-tier { font-size: 10px; font-weight: 700; }
    .am-empty { grid-column: 1 / -1; text-align: center; font-size: 14px; color: #6B625A; padding: 24px 8px; background: #fff; border-radius: 20px; }
    .am-old { margin-top: 8px; }
    .am-sub { margin: 6px 0 -4px 2px; font-size: var(--fs-h3, 17px); font-weight: 800; }
    @media (min-width: 700px) { .am-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); } }
</style>

<div class="am">
    <div class="ks-cp-hero purple">
        <span class="ks-cp-hero-ic" aria-hidden="true">🏅</span>
        <span class="ks-cp-eyebrow">{{ $child->name }} · დიდების კედელი</span>
        <h1 class="ks-cp-title">მედლები</h1>
        <span class="ks-cp-sub">{{ $earned->filter(fn($e) => ($e->tier_level ?? 0) > 0)->count() }}/{{ $__total }} მოპოვებული</span>
    </div>
    @include('parent.partials.kid-switcher')

    @if($__next)
    <div class="am-next">
        <span class="am-next-ic" aria-hidden="true">🎯</span>
        <div class="am-next-b">
            <div class="am-next-top"><span>შემდეგი: {{ $__next['label'] }}</span><span>{{ $__next['val'] }}/{{ $__next['max'] }}</span></div>
            <div class="am-bar"><span style="width: {{ max(3, round($__next['ratio'] * 100)) }}%"></span></div>
        </div>
    </div>
    @endif

    <div class="am-tabs" role="tablist" aria-label="კატეგორია">
        @foreach($__tabs as $__k => $__l)
        <button type="button" role="tab" class="am-tab" id="amTab-{{ $__k }}" aria-controls="amPanel-{{ $__k }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" onclick="amTab('{{ $__k }}')">{{ $__l }}</button>
        @endforeach
    </div>

    @foreach($__tabs as $__k => $__l)
    @php $__list = $achievements->filter(fn($a) => ($a->context ?? 'any') === $__k || ($__k === 'test' && ($a->context ?? 'any') === 'any')); @endphp
    <div class="am-grid" role="tabpanel" id="amPanel-{{ $__k }}" aria-labelledby="amTab-{{ $__k }}" @unless($loop->first) hidden @endunless>
        @forelse($__list as $ach)
        @php
            $__row  = $earned->get($ach->slug);
            $__lv   = $__row->tier_level ?? 0;
            $__n    = max(1, $ach->tiers->count());
            $__key  = $__lv <= 0 ? 'lock' : ($__lv >= $__n ? 'gold' : ($__lv / $__n >= 0.5 ? 'silver' : 'bronze'));
            [$__c, $__bg, $__tt, $__tc] = $__metal[$__key];
            $__tier = $__lv > 0 ? $ach->tiers->firstWhere('level', $__lv) : $ach->tiers->first();
            $__img  = $__tier?->imageUrl();
            $__desc = $__tier && $__tier->threshold !== null ? str_replace('{n}', $__tier->threshold, $ach->description ?? '') : $ach->description;
        @endphp
        <div class="am-medal {{ $__key === 'lock' ? 'lock' : '' }}" style="background: {{ $__bg }}" title="{{ $__desc }}">
            <span class="am-ring" style="background: {{ $__c }}">
                @if($__img)
                <img src="{{ $__img }}" alt="">
                @else
                <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2h8l-2 6h-4z" fill="#fff" opacity=".85"/><circle cx="12" cy="15" r="6" fill="#fff"/><circle cx="12" cy="15" r="3.4" fill="{{ $__c }}"/></svg>
                @endif
            </span>
            <span class="am-name">{{ $__tier?->label ?? $ach->name }}</span>
            <span class="am-tier" style="color: {{ $__tc }}">{{ $__tt }}{{ $__n > 1 ? ' · ' . $__lv . '/' . $__n : '' }}</span>
        </div>
        @empty
        <div class="am-empty">ამ კატეგორიაში მედლები ჯერ არ არის</div>
        @endforelse
    </div>
    @endforeach

    @if(isset($marketRewards) && $marketRewards->count())
    <h2 class="am-sub">მარკეტის ჯილდოები</h2>
    <div class="am-grid">
        @foreach($marketRewards as $reward)
        <div class="am-medal" style="background:#DFF5E3;">
            <span class="am-ring" style="background:#2E9E57;"><span style="font-size:26px;">{{ $reward->item?->icon }}</span></span>
            <span class="am-name">{{ $reward->item?->title }}</span>
            <span class="am-tier" style="color:#14612F;">{{ $reward->coins_spent }} მონეტა · {{ $reward->updated_at->format('d.m') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @if($oldGrades->isNotEmpty())
    <div class="am-old">
        @foreach($oldGrades as $grade)
        @php $gEarned = $oldGradeAchievements->get($grade->id, collect()); @endphp
        <div class="ks-cp-acc" id="secOldGrade{{ $grade->id }}" style="margin-bottom:10px;">
            <div class="ks-cp-acc-h" onclick="document.getElementById('secOldGrade{{ $grade->id }}').classList.toggle('open')">
                <span class="ks-cp-acc-t">{{ $grade->name }} · მედლები</span>
                <span class="ks-cp-chip">{{ $gEarned->count() }}</span>
                <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
            </div>
            <div class="ks-cp-acc-b">
                <div class="am-grid">
                @forelse($achievements as $ach)
                    @php
                        $row = $gEarned->get($ach->slug);
                        $tier = $row ? $ach->tiers->firstWhere('level', $row->tier_level) : null;
                    @endphp
                    @if($tier)
                    <div class="am-medal" style="background:#FFF1C2;">
                        <span class="am-ring" style="background:#F5B301;">
                            @if($tier->imageUrl())<img src="{{ $tier->imageUrl() }}" alt="">@else<span style="font-size:24px;">🏅</span>@endif
                        </span>
                        <span class="am-name">{{ $tier->label }}</span>
                        <span class="am-tier" style="color:#6B4A00;">{{ $row->earned_at->format('d.m.Y') }}</span>
                    </div>
                    @endif
                @empty
                @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function amTab(key) {
    document.querySelectorAll('.am-tab').forEach(function (b) { b.setAttribute('aria-selected', b.id === 'amTab-' + key ? 'true' : 'false'); });
    document.querySelectorAll('.am-grid[role="tabpanel"]').forEach(function (p) { p.hidden = p.id !== 'amPanel-' + key; });
}
</script>
@endsection
