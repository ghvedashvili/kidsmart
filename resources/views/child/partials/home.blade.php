{{-- redesign-kidsmart: ბავშვის მთავარი გვერდი (dashboard.blade-დან, role=child) --}}
@php
    $activeTest    = auth()->user()->tests()->where('is_olympiad', false)->whereNull('completed_at')->latest()->first();
    $setting       = auth()->user()->childSetting;
    $required      = $setting?->tests_per_week ?? 0;
    $todayCount    = auth()->user()->tests()->where('is_olympiad', false)->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
    $olympiadStatus = (new \App\Services\OlympiadService())->statusFor(auth()->user());
    $doneToday     = $required > 0 && $todayCount >= $required && !$activeTest && !$setting?->unlimited_tests;
    $coins         = $setting?->coins ?? 0;
    $achCount      = auth()->user()->achievements()->count();
    $totalTestsCount = auth()->user()->tests()->whereNotNull('completed_at')->count();
    $practiceTopicsCount = \App\Models\Topic::where('grade_id', $setting?->grade_id)
        ->whereHas('questionTemplates')->count();

    $doneDates = auth()->user()->tests()->whereNotNull('completed_at')
        ->pluck('completed_at')->map(fn($d) => $d->format('Y-m-d'))->unique();

    $streak = 0;
    $cursor = \Carbon\Carbon::today();
    if (!$doneDates->contains($cursor->format('Y-m-d'))) { $cursor = $cursor->copy()->subDay(); }
    while ($doneDates->contains($cursor->format('Y-m-d'))) { $streak++; $cursor = $cursor->copy()->subDay(); }
@endphp

@php
    $__gradeName = $setting?->grade?->name;
    $__level     = (int) ($setting?->difficulty ?? 1);
@endphp

@php
    $__u        = auth()->user();
    $__qCount   = $setting?->grade_id ? \App\Models\TestQuestionCount::resolve($setting->grade_id, (int) $setting->difficulty, null) : null;
    $__todayTest = $__u->tests()->where('is_olympiad', false)->whereNotNull('completed_at')->whereDate('completed_at', today())->latest('completed_at')->first();
    $__done     = $__todayTest && !$activeTest;
    $__canMore  = (bool) $setting?->unlimited_tests;

    // დღის შედეგები
    $__tCoins   = (float) $__u->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->sum('coins_earned');
    $__tMedals  = $__u->achievements()->whereDate('earned_at', today())->get();
    $__tMedalNames = $__tMedals->isEmpty() ? collect() : \App\Models\Achievement::whereIn('slug', $__tMedals->pluck('slug'))->pluck('name');
    $__tPractice = \App\Models\PracticeAnswerLog::where('child_id', $__u->id)->whereDate('created_at', today())
        ->selectRaw('COUNT(*) as n, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as ok')->first();
    $__tPracN   = (int) ($__tPractice->n ?? 0);
    $__tPracOk  = (int) ($__tPractice->ok ?? 0);
    $__tPct     = $__todayTest ? (int) round($__todayTest->correct_count / max(1, $__todayTest->total_questions) * 100) : null;
    $__fmt      = fn($v) => rtrim(rtrim(number_format((float) $v, 1, '.', ''), '0'), '.');
    // ოლიმპიადის ბარათი ჩანს, როცა ოლიმპიადა დანიშნულია (დღეს ან მომავალში) ან დღეს უკვე დაიწყო
    $__olyDate  = $olympiadStatus['rule']['olympiad_date'] ?? null;
    $__olyShow  = $olympiadStatus['already_attempted_today'] || ($__olyDate && ($__olyDate->isToday() || $__olyDate->isFuture()));
@endphp

<div class="ks-home">
    {{-- მონაცემები: ზედიზედ დღეები · მონეტები (→ მარკეტი) · მედლები (→ მედლები) --}}
    <div class="kh-stats">
        <div class="kh-stat">
            <svg width="26" height="26" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5c1 4 5.5 5.5 5.5 11a5.5 5.5 0 0 1-11 0c0-3 2-4.5 2-7.5 1.5 1 2.6 2.2 3 3.4.6-2.2.5-4.6.5-6.9z" fill="{{ $streak > 0 ? '#F26B1D' : '#D8CEC3' }}"/></svg>
            <b>{{ $streak }}</b><span>დღე ზედიზედ</span>
        </div>
        <a href="{{ route('market.child') }}" class="kh-stat">
            <svg width="26" height="26" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>
            <b>{{ (int) floor($coins) }}</b><span>მონეტა</span>
        </a>
        <a href="{{ route('achievements') }}" class="kh-stat">
            <span style="font-size:24px;line-height:26px;">🏅</span>
            <b>{{ $achCount }}</b><span>მედალი</span>
        </a>
    </div>

    {{-- დღევანდელი მისია: დღეში 1 ტესტი --}}
    <div class="kh-mission {{ $__done ? 'done' : '' }}">
        <div class="kh-mission-blob" aria-hidden="true"></div>
        <div class="kh-mission-fox" aria-hidden="true">@include('partials.ks-fox', ['size' => 130, 'color' => '#E2590F', 'smile' => true])</div>
        <div class="kh-mission-txt">
            <span class="kh-mission-hi">
                <span class="kh-av kh-av-sm {{ $__u->avatar }}" aria-hidden="true">
                    @if($__u->avatar === 'boy') 👦 @elseif($__u->avatar === 'girl') 👧 @else @include('partials.ks-fox', ['size' => 22]) @endif
                </span>
                გამარჯობა, {{ $__u->name }}!
            </span>
            <span class="kh-mission-eyebrow">დღევანდელი მისია</span>
            @if(!$setting || !$setting->grade_id)
                <span class="kh-mission-title">მალე</span>
                <span class="kh-mission-sub">მშობელმა ჯერ კლასი არ აგირჩია</span>
            @elseif($activeTest)
                <span class="kh-mission-title">ტესტი დღეს</span>
                <span class="kh-mission-sub">დაწყებულია — დაასრულე!</span>
            @elseif($__done)
                <span class="kh-mission-title">ტესტი დაწერილია</span>
                <span class="kh-mission-sub">{{ $__todayTest->correct_count }}/{{ $__todayTest->total_questions }} სწორი · ხვალ ახალი მისია</span>
            @else
                <span class="kh-mission-title">ტესტი დღეს</span>
                <span class="kh-mission-sub">{{ $__gradeName }}{{ $__qCount ? ' · ' . $__qCount . ' კითხვა' : '' }} · დონე {{ $__level }}</span>
            @endif
        </div>
        @if($setting && $setting->grade_id)
        <div class="kh-mission-act">
            @if($activeTest)
                <a href="{{ route('test.show', $activeTest) }}" class="kh-go" aria-label="ტესტის გაგრძელება"><i class="bi bi-arrow-right"></i></a>
            @elseif($__done)
                <a href="{{ $__canMore ? route('test.start') : route('my.test.show', $__todayTest) }}" class="kh-go" aria-label="{{ $__canMore ? 'ახალი ტესტი' : 'ტესტის ნახვა' }}"><i class="bi {{ $__canMore ? 'bi-arrow-right' : 'bi-check-lg' }}"></i></a>
            @else
                <a href="{{ route('test.start') }}" class="kh-go" aria-label="მისიის დაწყება"><i class="bi bi-arrow-right"></i></a>
            @endif
            <div class="kh-bar"><span style="width: {{ $__done ? 100 : ($activeTest ? 50 : 0) }}%"></span></div>
            <span class="kh-bar-lbl">{{ $__done ? 1 : 0 }}/1 ტესტი</span>
        </div>
        @endif
    </div>

    {{-- 4 სწრაფი ბმული --}}
    <div class="kh-quick">
        <a href="{{ route('practice.topics') }}" style="background:#FFE9DA;">
            <span class="kh-quick-ic" style="background:#E2590F;"><i class="bi bi-bullseye"></i></span>
            <b>ვარჯიში</b><small>{{ $practiceTopicsCount }} თემა</small>
        </a>
        <a href="{{ route('games.index') }}" style="background:#DCEBFF;">
            <span class="kh-quick-ic" style="background:#2F6FD6;"><i class="bi bi-controller"></i></span>
            <b>თამაშები</b><small>4 თამაში</small>
        </a>
        <a href="{{ Route::has('videos.library') ? route('videos.library') : route('practice.topics') . '#secVideos' }}" style="background:#FFF1C2;">
            <span class="kh-quick-ic" style="background:#C98A00;"><i class="bi bi-play-fill"></i></span>
            <b>ვიდეოები</b><small>გაკვეთილები</small>
        </a>
        <a href="{{ route('market.child') }}" style="background:#EEE8FF;">
            <span class="kh-quick-ic" style="background:#7B5CF0;"><i class="bi bi-bag"></i></span>
            <b>მარკეტი</b><small>ჯილდოები</small>
        </a>
    </div>

    {{-- დღეს: რა მოიგო დღეს --}}
    <div class="kh-today">
        <span class="kh-h">დღეს</span>
        <div class="kh-today-row">
            <span class="kh-today-ic" style="background:#FFF1C2;">
                <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>
            </span>
            <span class="kh-today-t"><b>მოგებული მონეტები</b><small>დღევანდელი ტესტებიდან</small></span>
            <span class="kh-today-v">+{{ $__fmt($__tCoins) }}</span>
        </div>
        <div class="kh-today-row">
            <span class="kh-today-ic" style="background:#EEE8FF;">🏅</span>
            <span class="kh-today-t"><b>ახალი მედლები</b><small>{{ $__tMedalNames->isNotEmpty() ? $__tMedalNames->implode(', ') : 'დღეს ჯერ არ მოგიგია' }}</small></span>
            <span class="kh-today-v">{{ $__tMedals->count() }}</span>
        </div>
        <div class="kh-today-row">
            <span class="kh-today-ic" style="background:#DFF5E3;">📝</span>
            <span class="kh-today-t"><b>ტესტი</b><small>{{ $__todayTest ? $__todayTest->correct_count . '/' . $__todayTest->total_questions . ' სწორი' : 'ჯერ არ დაგიწერია' }}</small></span>
            <span class="kh-today-v">{{ $__tPct !== null ? $__tPct . '%' : '—' }}</span>
        </div>
        <div class="kh-today-row">
            <span class="kh-today-ic" style="background:#FFE3CC;">🎯</span>
            <span class="kh-today-t"><b>ვარჯიში</b><small>{{ $__tPracN > 0 ? $__tPracOk . ' სწორი ' . $__tPracN . '-დან' : 'დღეს ჯერ არ გივარჯიშია' }}</small></span>
            <span class="kh-today-v">{{ $__tPracN }}</span>
        </div>
    </div>
    @if($__olyShow)
    {{-- ოლიმპიადა — ცალკე ბარათი, ჩანს მხოლოდ როცა დანიშნულია --}}
    <a href="{{ route('olympiad.index') }}" class="kh-oly {{ $olympiadStatus['eligible_today'] || $olympiadStatus['already_attempted_today'] ? 'open' : '' }}">
        <div class="kh-oly-top">
            <span class="kh-oly-ic" aria-hidden="true">🏆</span>
            <span class="kh-oly-t">
                <small>{{ $__olyDate ? ($__olyDate->isToday() ? 'დღეს' : $__olyDate->format('d.m.Y')) : 'დღეს' }}</small>
                <b>ოლიმპიადა</b>
            </span>
            <i class="bi bi-chevron-right"></i>
        </div>
        <span class="kh-oly-s">
            @if($olympiadStatus['already_attempted_today'])
                {{ $olympiadStatus['todays_test']?->completed_at ? 'დღეს უკვე დაწერე — ნახე შედეგი' : 'დაწყებულია — გააგრძელე!' }}
            @elseif($olympiadStatus['eligible_today'])
                დღეს ღიაა — დაიწყე!
            @else
                {{ $olympiadStatus['reason'] }}
            @endif
        </span>
        @unless($olympiadStatus['already_attempted_today'] || $olympiadStatus['eligible_today'])
        <span class="kh-oly-req">
            <span class="{{ $olympiadStatus['level_met'] ? 'ok' : '' }}">{{ $olympiadStatus['level_met'] ? '✓' : '○' }} მაქსიმალური დონე</span>
            <span class="{{ $olympiadStatus['tests_met'] ? 'ok' : '' }}">{{ $olympiadStatus['tests_met'] ? '✓' : '○' }} {{ $olympiadStatus['recent_count'] }}/{{ $olympiadStatus['rule']['tests_required'] }} ტესტი</span>
        </span>
        @endunless
    </a>
    @endif
</div>
