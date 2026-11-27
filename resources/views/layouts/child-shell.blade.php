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
            <img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo">
        </a>
        <div class="ks-cnav">
            @foreach($__cLinks as [$__r, $__p, $__i, $__l, $__m])
            @if(Route::has($__r))
            <a href="{{ route($__r) }}" data-guide="nav-{{ str_replace('.', '-', $__r) }}" class="{{ $__cOn($__p) ? 'on' : '' }}" @if($__cOn($__p)) aria-current="page" @endif><i class="bi {{ $__i }}"></i>{{ $__l }}</a>
            @endif
            @endforeach
        </div>
        <div class="ks-clevel">
            <span class="ks-clevel-t">დონე {{ $__cLevel }} / {{ $__cMax }}</span>
            <div class="ks-clevel-bar"><span style="width: {{ max(6, min(100, round($__cLevel / max(1, $__cMax) * 100))) }}%"></span></div>
            <span class="ks-clevel-s">{{ $__cs?->grade?->name ?? 'კლასი ჯერ არ არის არჩეული' }}</span>
        </div>
        <div class="ks-cside-foot">
            @if(session('ks_impersonator'))
            <div class="ks-cback">
                <span class="ks-cback-l">ბავშვის რეჟიმი · დაბრუნება:</span>
                <form method="POST" action="{{ route('child.as-child.leave') }}">
                    @csrf
                    <button type="submit" name="to" value="parent" class="ks-cback-btn"><i class="bi bi-people"></i>მშობლის რეჟიმი</button>
                    <button type="submit" name="to" value="admin" class="ks-cback-btn dark"><i class="bi bi-shield-lock"></i>ადმინ რეჟიმი</button>
                </form>
            </div>
            @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout"><i class="bi bi-box-arrow-left"></i>გასვლა</button>
            </form>
            @endif
        </div>
    </aside>

    <main class="ks-cmain">
        {{-- ზედა ზოლი (ყველა გვერდზე ერთნაირი, landing-ის მსგავსად მიმაგრებული): მხოლოდ ლოგო და გასვლა --}}
        <header class="ks-ctop ks-ctop-bar{{ request()->routeIs('dashboard') ? ' has-bell' : '' }}{{ View::hasSection('ksBackUrl') ? ' has-back' : '' }}">
            {{-- გვერდს შეუძლია უკან დაბრუნების ღილაკი მოითხოვოს: @section('ksBackUrl') + @section('ksBackLabel') --}}
            @hasSection('ksBackUrl')
            <a href="@yield('ksBackUrl')" class="ks-cback-link"><i class="bi bi-arrow-left"></i> @yield('ksBackLabel', 'უკან')</a>
            @endif
            <a href="{{ route('dashboard') }}" class="ks-cbrand">
                <img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo">
            </a>
            <div class="ks-ctop-acts">
            {{-- შეტყობინებების ზარი — მხოლოდ მთავარ გვერდზე --}}
            @if(request()->routeIs('dashboard'))
            <button type="button" id="notifBtn" class="ks-clogout-top ks-cbell" aria-label="შეტყობინებები" title="შეტყობინებების ჩართვა/გამორთვა" onclick="window._notifToast=true;toggleNotifications()"><i id="notifIcon" class="bi bi-bell"></i></button>
            @endif
            @if(session('ks_impersonator'))
            <form method="POST" action="{{ route('child.as-child.leave') }}" class="ks-ctop-r">
                @csrf
                <button type="submit" name="to" value="parent" class="ks-cback-top"><i class="bi bi-people"></i><span>მშობელი</span></button>
                <button type="submit" name="to" value="admin" class="ks-cback-top dark"><i class="bi bi-shield-lock"></i><span>ადმინი</span></button>
            </form>
            @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout-top"><i class="bi bi-box-arrow-right"></i><span>გასვლა</span></button>
            </form>
            @endif
            </div>
        </header>
        <style>
            .ks-ctop-acts { display: flex; align-items: center; gap: 8px; }
            .ks-ctop-bar.has-back { display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; }
            .ks-ctop-bar.has-back .ks-cbrand { justify-self: center; }
            .ks-ctop-bar.has-back .ks-ctop-acts { justify-self: end; }
            .ks-cback-link { justify-self: start; height: 44px; padding: 0 16px; border-radius: 22px; border: 2px solid #ECE9E4; background: #fff; color: #2B2622; font-family: 'Noto Sans Georgian', system-ui, sans-serif; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; white-space: nowrap; }
            .ks-cback-link:hover { border-color: #2B2622; color: #2B2622; }
            @media (max-width: 420px) { .ks-cback-link { padding: 0 12px; } }
            .ks-ctop-acts .ks-cbell { width: 44px; padding: 0 !important; justify-content: center; font-size: 18px !important; }
            .ks-ctop-acts .ks-cbell.on, .ks-ctop-acts .ks-cbell.active { color: #1E8A4C !important; border-color: #BFE6CD !important; }
            /* ზარი მთავარ გვერდზე დესკტოპზეც ჩანდეს — ზოლი იქ დამალულია, ამიტომ ზარი გვერდით მენიუს ქვემოთ არ გადაგვაქვს: ვაჩვენებთ ზოლს მხოლოდ ზარით */
            @media (min-width: 901px) {
                body.ks-child .ks-ctop.ks-ctop-bar.has-bell { display: flex; justify-content: flex-end; background: transparent; border: 0; backdrop-filter: none; -webkit-backdrop-filter: none; position: static; padding-bottom: 0; }
                body.ks-child .ks-ctop-bar.has-bell .ks-cbrand, body.ks-child .ks-ctop-bar.has-bell form { display: none; }
                /* დესკტოპზე ლოგო და გასვლა გვერდით მენიუშია — ზოლში მხოლოდ „უკან“ */
                body.ks-child .ks-ctop.ks-ctop-bar.has-back { display: grid; }
                body.ks-child .ks-ctop-bar.has-back .ks-cbrand, body.ks-child .ks-ctop-bar.has-back .ks-ctop-acts { visibility: hidden; }
            }
        </style>

        @yield('content')
    </main>

    <div class="ks-ctabs" role="navigation" aria-label="მთავარი მენიუ">
        @foreach($__cLinks as [$__r, $__p, $__i, $__l, $__m])
        @if($__m && Route::has($__r))
        <a href="{{ route($__r) }}" data-guide="nav-{{ str_replace('.', '-', $__r) }}" class="{{ $__cOn($__p) ? 'on' : '' }}"><i class="bi {{ $__i }}"></i>{{ $__l }}</a>
        @endif
        @endforeach
    </div>
</div>
