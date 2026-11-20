{{-- redesign-kidsmart: მშობლის გარსი — დესკტოპზე მუქი გვერდითი მენიუ, მობილურზე ქვედა ტაბები.
     <nav> ელემენტს განზრახ არ ვიყენებთ: navigation.blade-ის სკრიპტი პირველ <nav>-ს ზომავს (--nav-h). --}}
@php
    $__pu   = auth()->user();
    $__pKids = $__pu->children()->withTimestamps()->orderByPivot('created_at', 'asc')->get();
    $__pRoute = request()->route('child');
    $__pKidId = $__pRoute instanceof \Illuminate\Database\Eloquent\Model ? $__pRoute->getKey() : $__pRoute;
    $__pKidId = $__pKidId ?: request('kid');
    $__pKid = ($__pKidId ? $__pKids->firstWhere('id', (int) $__pKidId) : null) ?? $__pKids->first();
    $__pPending = $__pKid ? \App\Models\MarketPurchase::where('child_id', $__pKid->id)->where('status', 'pending')->count() : 0;
    $__pPkg = $__pu->currentPackage();
    $__pCanAdmin = $__pu->isAdmin() || \App\Models\RolePermission::hasAnyAdminPage($__pu->role);
    $__pAv  = fn($k) => $k->avatar === 'boy' ? '👦' : ($k->avatar === 'girl' ? '👧' : '🙂');
    $__pKidLinks = $__pKid ? [
        ['child.stats',           'child.stats|child.test.show', 'bi-bar-chart-line', 'ტესტები და ანალიტიკა'],
        ['child.practice-stats',  'child.practice-stats',  'bi-bullseye',        'ვარჯიში'],
        ['child.olympiad-stats',  'child.olympiad-stats',  'bi-trophy',          'ოლიმპიადა'],
        ['child.achievements',    'child.achievements',    'bi-award',           'მედლები'],
        ['market.index',          'market.index',          'bi-bag',             'ოჯახის მარკეტი'],
    ] : [];
    $__pOn = fn($p) => collect(explode('|', $p))->contains(fn($x) => request()->routeIs($x));
    // სხვა შვილზე გადასვლისას იგივე გვერდი რჩება (თუ შვილის გვერდზე ვართ), თორემ — მიმოხილვა
    $__pSameRoute = request()->route()?->getName();
    $__pSwitchUrl = function ($k) use ($__pSameRoute) {
        $ok = $__pSameRoute && in_array($__pSameRoute, ['child.hub', 'child.stats', 'child.practice-stats', 'child.olympiad-stats', 'child.achievements', 'market.index']);
        return route($ok ? $__pSameRoute : 'child.hub', $k);
    };
@endphp
<div class="ks-child-shell">
    <aside class="ks-pside" role="navigation" aria-label="მშობლის მენიუ">
        <a href="{{ route('dashboard') }}" class="ks-cbrand">
            <span class="ks-pbrand-t"><img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo"><small>მშობლის პანელი</small></span>
        </a>
        <div class="ks-pnav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}"><i class="bi bi-grid-1x2"></i>მთავარი</a>
        </div>
        @if($__pKid)
        <div class="ks-pnav">
            <div class="ks-pnav-lbl">{{ $__pKid->name }}</div>
            @foreach($__pKidLinks as [$__r, $__p, $__i, $__l])
            <a href="{{ route($__r, $__pKid) }}" class="{{ $__pOn($__p) ? 'on' : '' }}" @if($__pOn($__p)) aria-current="page" @endif>
                <i class="bi {{ $__i }}"></i>{{ $__l }}
                @if($__r === 'market.index' && $__pPending)<span class="ks-pnav-badge">{{ $__pPending }}</span>@endif
            </a>
            @endforeach
        </div>
        @endif
        @if($__pCanAdmin)
        <a href="{{ route('admin.panel') }}" class="ks-padmin"><i class="bi bi-shield-lock"></i><span>ადმინ რეჟიმი</span><i class="bi bi-arrow-right"></i></a>
        @endif
        <div class="ks-pplan">
            <small>მიმდინარე გეგმა</small>
            <b>{{ $__pPkg->name }} · {{ $__pKids->count() }}{{ $__pPkg->max_children > 0 ? '/' . $__pPkg->max_children : '' }} შვილი</b>
            <a href="{{ route('dashboard') }}#plans" onclick="var m=document.getElementById('plansModal'); if(m){event.preventDefault(); m.classList.add('open');}">გეგმის შეცვლა →</a>
        </div>
        <div class="ks-cside-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout"><i class="bi bi-box-arrow-left"></i>გასვლა</button>
            </form>
        </div>
    </aside>

    <main class="ks-cmain">
        {{-- ზედა ზოლი (ბავშვის გვერდების მსგავსად): მხოლოდ ლოგო და გასვლა, მიმაგრებული --}}
        <header class="ks-ctop ks-ctop-bar">
            <a href="{{ route('dashboard') }}" class="ks-cbrand">
                <img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo">
            </a>
            <div class="ks-ctop-r">
                @if($__pCanAdmin)
                <a href="{{ route('admin.panel') }}" class="ks-padmin-top" title="ადმინ რეჟიმი"><i class="bi bi-shield-lock"></i><span>ადმინი</span></a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ks-clogout-top"><i class="bi bi-box-arrow-right"></i><span>გასვლა</span></button>
                </form>
            </div>
        </header>

        @yield('content')
    </main>

    <div class="ks-ptabs" role="navigation" aria-label="მშობლის მენიუ">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}"><i class="bi bi-grid-1x2"></i>მთავარი</a>
        @if($__pKid)
        <a href="{{ route('child.stats', $__pKid) }}" class="{{ request()->routeIs('child.stats', 'child.test.show', 'child.practice-stats', 'child.olympiad-stats', 'child.achievements') ? 'on' : '' }}"><i class="bi bi-bar-chart-line"></i>ანალიტიკა</a>
        <a href="{{ route('market.index', $__pKid) }}" class="{{ request()->routeIs('market.index') ? 'on' : '' }}"><i class="bi bi-bag"></i>მარკეტი @if($__pPending)<span class="ks-pnav-badge">{{ $__pPending }}</span>@endif</a>
        @endif
    </div>
</div>
