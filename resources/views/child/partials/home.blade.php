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
    $__maxLevel  = (int) ($setting?->grade?->max_level ?? \App\Models\Grade::DEFAULT_MAX_LEVEL);
    $__level     = (int) ($setting?->difficulty ?? 1);
    $__done      = auth()->user()->tests()->where('is_olympiad', false)->whereNotNull('completed_at')
                      ->selectRaw('COALESCE(SUM(correct_count),0) as c, COALESCE(SUM(total_questions),0) as t')->first();
    $__avg       = ($__done && $__done->t > 0) ? (int) round($__done->c / $__done->t * 100) : null;

    $__topics = $setting?->grade_id
        ? \App\Models\Topic::where('grade_id', $setting->grade_id)->where('is_active', true)
            ->whereHas('questionTemplates')->orderBy('sequence')->orderBy('name')->take(6)->get()
        : collect();
    $__tStats = $__topics->isEmpty() ? collect() : \App\Models\PracticeAnswerLog::where('child_id', auth()->id())
        ->whereIn('topic_id', $__topics->pluck('id'))
        ->selectRaw('topic_id, COUNT(*) as n, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as ok')
        ->groupBy('topic_id')->get()->keyBy('topic_id');
    $__palette = [
        ['#E2590F', '#FFE3CC', '+'], ['#7B5CF0', '#EEE8FF', '×'], ['#2F6FD6', '#DCEBFF', '?'],
        ['#1E8A4C', '#DFF5E3', '#'], ['#C2185B', '#FCE4EC', '÷'], ['#8A5A00', '#FFF1C9', '='],
    ];

    $__recent = auth()->user()->achievements()->orderByDesc('earned_at')->take(3)->get();
    $__achNames = $__recent->isEmpty() ? collect() : \App\Models\Achievement::whereIn('slug', $__recent->pluck('slug'))->pluck('name', 'slug');
    $__medalBg = ['#F5B301', '#9AA3B2', '#D98B4A'];

    $__games = [
        ['games.memory',      '🧠', 'მეხსიერება',            'იპოვე წყვილები',     '#FFE3CC'],
        ['games.rps',         '✊', 'ქვა-ქაღალდი-მაკრატელი', 'აჯობე KidSmart-ს',   '#EEE8FF'],
        ['games.wolves-hare', '🐰', 'კურდღელი და მგლები',     'სტრატეგიული თამაში', '#DCEBFF'],
        ['games.quoridor',    '🧱', 'კვორიდორი',             'ააშენე კედლები',     '#DFF5E3'],
    ];

    if ($activeTest) {
        $__mState = 'resume'; $__mTitle = 'გააგრძელე';
        $__mEyebrow = 'დღევანდელი მისია · დაწყებულია';
        $__mSub = 'ტესტი დაწყებული გაქვს — დაასრულე და აიღე მონეტები.';
    } elseif (!$setting || !$setting->grade_id) {
        $__mState = 'wait'; $__mTitle = 'მალე';
        $__mEyebrow = 'მისია მოლოდინშია';
        $__mSub = 'მშობელს ჯერ დავალება არ დაუყენებია — მალე მიიღებ შენს პირველ მისიას!';
    } elseif ($doneToday) {
        $__mState = 'done'; $__mTitle = 'ყოჩაღ!';
        $__mEyebrow = 'დღევანდელი მისია შესრულებულია';
        $__mSub = $todayCount . ' ტესტი გააკეთე დღეს. ხვალ ახალი მისია გელოდება!';
    } else {
        $__mState = 'go'; $__mTitle = 'ტესტი დღეს';
        $__mEyebrow = 'დღევანდელი მისია';
        $__mSub = 'სწორ პასუხებზე მოიგე მონეტები და აიწიე დონე.';
    }
@endphp

<div class="ks-home">
    <div class="ks-hello">
        <span class="ks-hello-t">გამარჯობა, {{ auth()->user()->name }}!</span>
        <span class="ks-hello-s">მზად ხარ დღეს, ჩემპიონო?</span>
    </div>

    <section class="ks-row">
        <div class="ks-wide ks-mission {{ $__mState === 'done' ? 'done' : ($__mState === 'wait' ? 'wait' : '') }}">
            <div class="ks-mission-blob" aria-hidden="true"></div>
            <div class="ks-mission-fox" aria-hidden="true">@include('partials.ks-fox', ['size' => 230, 'color' => '#E2590F', 'smile' => true])</div>
            <div class="ks-mission-txt">
                <span class="ks-mission-eyebrow">{{ $__mEyebrow }}</span>
                <h1 class="ks-mission-title">{{ $__mTitle }}</h1>
                <span class="ks-mission-sub">{{ $__mSub }}</span>
            </div>
            <div class="ks-mission-act">
                @if($__mState === 'resume')
                <a href="{{ route('test.show', $activeTest) }}" class="ks-mission-cta">გააგრძელე მისია <i class="bi bi-arrow-right"></i></a>
                @elseif($__mState === 'go')
                <a href="{{ route('test.start') }}" class="ks-mission-cta">მისიის დაწყება <i class="bi bi-arrow-right"></i></a>
                @elseif($__mState === 'done')
                <a href="{{ route('practice.show', 'auto') }}" class="ks-mission-cta">ივარჯიშე კიდევ <i class="bi bi-arrow-right"></i></a>
                @endif
                @if($required > 0 && $__mState !== 'wait')
                <div class="ks-mission-prog">
                    <div class="ks-mission-prog-bar"><span style="width: {{ min(100, round($todayCount / $required * 100)) }}%"></span></div>
                    <b>{{ min($todayCount, $required) }}/{{ $required }} ტესტი</b>
                </div>
                @endif
            </div>
        </div>

        <div class="ks-narrow ks-tiles">
            <div class="ks-tile">
                <span class="ks-tile-ic">📶</span>
                <span><span class="ks-tile-v">{{ $__level }}/{{ $__maxLevel }}</span><br><span class="ks-tile-l">დონე{{ $__gradeName ? ' · ' . $__gradeName : '' }}</span></span>
            </div>
            <a href="{{ route('achievements') }}" class="ks-tile">
                <span class="ks-tile-ic">🏅</span>
                <span><span class="ks-tile-v">{{ $achCount }}</span><br><span class="ks-tile-l">მედალი</span></span>
            </a>
            <a href="{{ route('my.tests') }}" class="ks-tile">
                <span class="ks-tile-ic">✅</span>
                <span><span class="ks-tile-v">{{ $__avg !== null ? $__avg . '%' : '—' }}</span><br><span class="ks-tile-l">საშუალო შედეგი · {{ $totalTestsCount }} ტესტი</span></span>
            </a>
            <a href="{{ route('olympiad.index') }}" class="ks-tile purple {{ $olympiadStatus['eligible_today'] || $olympiadStatus['already_attempted_today'] ? '' : 'locked' }}">
                <span class="ks-tile-ic">🏆</span>
                <span><span class="ks-tile-v">ოლიმპიადა</span><br>
                <span class="ks-tile-l">
                    @if($olympiadStatus['already_attempted_today'])
                        {{ $olympiadStatus['todays_test']?->completed_at ? 'დღეს უკვე დაწერე!' : 'დაწყებულია — გააგრძელე!' }}
                    @elseif($olympiadStatus['eligible_today'])
                        დღეს ღიაა — დაიწყე!
                    @else
                        {{ \Illuminate\Support\Str::limit($olympiadStatus['reason'] ?? 'ჯერ დახურულია', 60) }}
                    @endif
                </span></span>
            </a>
        </div>
    </section>

    <section class="ks-row">
        <div class="ks-wide ks-panel">
            <div class="ks-panel-head">
                <h2>ვარჯიში თემების მიხედვით</h2>
                <a href="{{ route('practice.show', 'auto') }}" class="ks-tag">ვარჯიშის დაწყება →</a>
            </div>
            @if($__topics->isEmpty())
            <div class="ks-medals-empty">თემები მალე გამოჩნდება — მშობელმა ჯერ კლასი უნდა აგირჩიოს.</div>
            @else
            <div class="ks-topics">
                @foreach($__topics as $__i => $__t)
                @php
                    [$__c, $__bg, $__sym] = $__palette[$__i % count($__palette)];
                    $__st  = $__tStats->get($__t->id);
                    $__pct = ($__st && $__st->n > 0) ? (int) round($__st->ok / $__st->n * 100) : null;
                @endphp
                <a href="{{ route('practice.topics') }}" class="ks-topic">
                    <span class="ks-topic-sym" style="background: {{ $__bg }}; color: {{ $__c }}">{{ $__sym }}</span>
                    <span class="ks-topic-body">
                        <span class="ks-topic-top"><span class="ks-topic-name">{{ $__t->name }}</span><span class="ks-topic-pct">{{ $__pct !== null ? $__pct . '%' : 'ახალი' }}</span></span>
                        <span class="ks-topic-bar"><span style="width: {{ max(3, $__pct ?? 0) }}%; background: {{ $__c }}"></span></span>
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        <div class="ks-narrow ks-panel">
            <h2>ვიდეო გაკვეთილები</h2>
            <span class="ks-tile-l" style="font-size:14px;">უყურე მოკლე ვიდეოებს და მერე სცადე სავარჯიშოებში.</span>
            <a href="{{ route('practice.topics') }}#secVideos" class="ks-market" style="background:#FFE3CC;color:#8A3407;">
                <i class="bi bi-play-circle-fill" style="color:#E2590F;"></i>
                <span class="ks-market-t"><b>ვიდეოების ნახვა</b><span style="color:#8A3407;">{{ $practiceTopicsCount }} თემა</span></span>
                <i class="bi bi-chevron-right" style="font-size:18px;color:#8A3407;"></i>
            </a>
        </div>
    </section>

    <section class="ks-row">
        <div class="ks-wide" style="display:flex;flex-direction:column;gap:12px;">
            <div class="ks-panel-head"><h2>თამაშები</h2><a href="{{ route('games.index') }}" class="ks-tag">ყველა →</a></div>
            <div class="ks-games">
                @foreach($__games as [$__gr, $__gi, $__gn, $__gs, $__gbg])
                @if(Route::has($__gr))
                <a href="{{ route($__gr) }}" class="ks-game" style="background: {{ $__gbg }}">
                    <span class="ks-game-ic" aria-hidden="true">{{ $__gi }}</span>
                    <span class="ks-game-n">{{ $__gn }}</span>
                    <span class="ks-game-s">{{ $__gs }}</span>
                </a>
                @endif
                @endforeach
            </div>
        </div>
        <div class="ks-narrow" style="display:flex;flex-direction:column;gap:12px;">
            <div class="ks-panel-head"><h2>ახალი მედლები</h2><a href="{{ route('achievements') }}" class="ks-tag">ყველა →</a></div>
            <div class="ks-medals">
                @forelse($__recent as $__k => $__a)
                <a href="{{ route('achievements') }}" class="ks-medal">
                    <span class="ks-medal-c" style="background: {{ $__medalBg[$__k % 3] }}">🏅</span>
                    <span class="ks-medal-n">{{ $__achNames[$__a->slug] ?? 'მედალი' }}</span>
                </a>
                @empty
                <div class="ks-medals-empty">ჯერ მედალი არ გაქვს — დაწერე ტესტი და მოიგე პირველი!</div>
                @endforelse
            </div>
            <a href="{{ route('market.child') }}" class="ks-market">
                <i class="bi bi-bag"></i>
                <span class="ks-market-t"><b>მარკეტი</b><span>{{ (int) floor($coins) }} მონეტა — ნახე ჯილდოები</span></span>
                <i class="bi bi-chevron-right" style="font-size:18px;color:#fff;"></i>
            </a>
        </div>
    </section>
</div>
