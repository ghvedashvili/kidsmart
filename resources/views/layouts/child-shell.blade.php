{{-- redesign-kidsmart: ბავშვის გარსი — დესკტოპზე გვერდითი მენიუ, მობილურზე ქვედა ტაბები.
     <nav> ელემენტს განზრახ არ ვიყენებთ: navigation.blade-ის სკრიპტი პირველ <nav>-ს ზომავს (--nav-h). --}}
@php
    $__cu       = auth()->user();
    $__cs       = $__cu->childSetting;
    $__cCoins   = (int) floor($__cs?->coins ?? 0);
    $__cLevel   = (int) ($__cs?->difficulty ?? 1);
    $__cMax     = (int) ($__cs?->grade?->max_level ?? \App\Models\Grade::DEFAULT_MAX_LEVEL);
    $__cDates   = $__cu->tests()->whereNotNull('completed_at')->pluck('completed_at')->map(fn($d) => $d->format('Y-m-d'))->unique();
    $__cStreak  = 0;
    $__cCur     = \Carbon\Carbon::today();
    if (!$__cDates->contains($__cCur->format('Y-m-d'))) { $__cCur = $__cCur->copy()->subDay(); }
    while ($__cDates->contains($__cCur->format('Y-m-d'))) { $__cStreak++; $__cCur = $__cCur->copy()->subDay(); }
    $__cLinks = [
        ['dashboard',       'dashboard',                            'bi-house-door',      'მთავარი',    true],
        ['practice.topics', 'practice.*|videos.library',            'bi-book',            'სწავლა',     true],
        ['my.tests',        'my.tests|my.test.show',                'bi-clipboard-check', 'ტესტები',    false],
        ['olympiad.index',  'olympiad.*',                           'bi-trophy',          'ოლიმპიადა',  false],
        ['games.index',     'games.*',                              'bi-controller',      'თამაშები',   true],
        ['achievements',    'achievements',                         'bi-award',           'მედლები',    true],
        ['market.child',    'market.child',                         'bi-bag',             'მარკეტი',    true],
    ];
    $__cOn = fn($p) => collect(explode('|', $p))->contains(fn($x) => request()->routeIs($x));
@endphp
<div class="ks-child-shell">
    <aside class="ks-cside" role="navigation" aria-label="მთავარი მენიუ">
        <a href="{{ route('dashboard') }}" class="ks-cbrand">
            <span class="ks-cbrand-mark">@include('partials.ks-fox', ['size' => 26])</span>
            <span class="ks-cbrand-name">KidSmart</span>
        </a>
        <div class="ks-cnav">
            @foreach($__cLinks as [$__r, $__p, $__i, $__l, $__m])
            @if(Route::has($__r))
            <a href="{{ route($__r) }}" class="{{ $__cOn($__p) ? 'on' : '' }}" @if($__cOn($__p)) aria-current="page" @endif><i class="bi {{ $__i }}"></i>{{ $__l }}</a>
            @endif
            @endforeach
        </div>
        <div class="ks-clevel">
            <span class="ks-clevel-t">დონე {{ $__cLevel }} / {{ $__cMax }}</span>
            <div class="ks-clevel-bar"><span style="width: {{ max(6, min(100, round($__cLevel / max(1, $__cMax) * 100))) }}%"></span></div>
            <span class="ks-clevel-s">{{ $__cs?->grade?->name ?? 'კლასი ჯერ არ არის არჩეული' }}</span>
        </div>
        <div class="ks-cside-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout"><i class="bi bi-box-arrow-left"></i>გასვლა</button>
            </form>
        </div>
    </aside>

    <main class="ks-cmain">
        {{-- ზედა ზოლი (ყველა გვერდზე ერთნაირი, landing-ის მსგავსად მიმაგრებული): მხოლოდ ლოგო და გასვლა --}}
        <header class="ks-ctop ks-ctop-bar">
            <a href="{{ route('dashboard') }}" class="ks-cbrand">
                <span class="ks-cbrand-mark">@include('partials.ks-fox', ['size' => 26])</span>
                <span class="ks-cbrand-name">KidSmart</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout-top"><i class="bi bi-box-arrow-right"></i><span>გასვლა</span></button>
            </form>
        </header>

        @yield('content')
    </main>

    <div class="ks-ctabs" role="navigation" aria-label="მთავარი მენიუ">
        @foreach($__cLinks as [$__r, $__p, $__i, $__l, $__m])
        @if($__m && Route::has($__r))
        <a href="{{ route($__r) }}" class="{{ $__cOn($__p) ? 'on' : '' }}"><i class="bi {{ $__i }}"></i>{{ $__l }}</a>
        @endif
        @endforeach
    </div>
</div>
