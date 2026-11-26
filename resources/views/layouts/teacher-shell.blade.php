{{-- მასწავლებლის გარსი — მშობლის გარსის სტილით: მარცხნივ მენიუ (დესკტოპი), ქვემოთ ტაბები (მობილური).
     <nav> განზრახ არ არის: navigation.blade-ის სკრიპტი პირველ <nav>-ს ზომავს. --}}
@php
    $__tu = auth()->user();
    $__tCounts = \App\Models\QuestionTemplate::where('created_by', $__tu->id)
        ->selectRaw('review_status, count(*) c')->groupBy('review_status')->pluck('c', 'review_status');
    $__tTab = request()->routeIs('teacher.questions.index') ? (request('tab') ?: (($__tCounts['rejected'] ?? 0) ? 'rejected' : 'pending')) : null;
    $__tLinks = [
        ['rejected', 'bi-arrow-return-left', 'შესასწორებელი', 'warn'],
        ['pending',  'bi-hourglass-split',   'განხილვაზე',     ''],
        ['approved', 'bi-check2-circle',     'დადასტურებული',   ''],
    ];
@endphp
<div class="ks-child-shell">
    <aside class="ks-pside" role="navigation" aria-label="მასწავლებლის მენიუ">
        <a href="{{ route('teacher.questions.index') }}" class="ks-cbrand">
            <span class="ks-pbrand-t"><img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo"><small>მასწავლებლის გვერდი</small></span>
        </a>
        <a href="{{ route('teacher.questions.create') }}" class="tq-new {{ request()->routeIs('teacher.questions.create') ? 'on' : '' }}"><i class="bi bi-plus-lg"></i>ახალი კითხვა</a>
        <div class="ks-pnav">
            <div class="ks-pnav-lbl">ჩემი კითხვები</div>
            @foreach($__tLinks as [$__k, $__i, $__l, $__cls])
            <a href="{{ route('teacher.questions.index', ['tab' => $__k]) }}" class="{{ $__tTab === $__k ? 'on' : '' }}" @if($__tTab === $__k) aria-current="page" @endif>
                <i class="bi {{ $__i }}"></i>{{ $__l }}
                @if(($__tCounts[$__k] ?? 0) > 0)<span class="ks-pnav-badge{{ $__cls === 'warn' ? '' : ' tq-badge-soft' }}">{{ $__tCounts[$__k] }}</span>@endif
            </a>
            @endforeach
        </div>
        @if($__tu->isAdmin())
        {{-- მხოლოდ ადმინს აქვს რეჟიმებს შორის გადართვა --}}
        <a href="{{ route('admin.panel') }}" class="ks-padmin"><i class="bi bi-shield-lock"></i><span>ადმინ რეჟიმი</span><i class="bi bi-arrow-right"></i></a>
        <a href="{{ route('dashboard') }}" class="ks-padmin"><i class="bi bi-people"></i><span>მშობლის რეჟიმი</span><i class="bi bi-arrow-right"></i></a>
        @endif
        <div class="ks-cside-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ks-clogout"><i class="bi bi-box-arrow-left"></i>გასვლა</button>
            </form>
        </div>
    </aside>

    <main class="ks-cmain">
        <header class="ks-ctop ks-ctop-bar">
            <a href="{{ route('teacher.questions.index') }}" class="ks-cbrand">
                <img src="{{ asset('img/logo.png') }}" alt="KidSmart" class="ks-logo">
            </a>
            <div class="ks-ctop-r">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ks-clogout-top"><i class="bi bi-box-arrow-right"></i><span>გასვლა</span></button>
                </form>
            </div>
        </header>
        @yield('content')
    </main>

    <div class="ks-ptabs" role="navigation" aria-label="მასწავლებლის მენიუ">
        <a href="{{ route('teacher.questions.create') }}" class="{{ request()->routeIs('teacher.questions.create') ? 'on' : '' }}"><i class="bi bi-plus-circle"></i>ახალი</a>
        <a href="{{ route('teacher.questions.index', ['tab' => 'rejected']) }}" class="{{ $__tTab === 'rejected' ? 'on' : '' }}"><i class="bi bi-arrow-return-left"></i>შესასწორებელი @if($__tCounts['rejected'] ?? 0)<span class="ks-pnav-badge">{{ $__tCounts['rejected'] }}</span>@endif</a>
        <a href="{{ route('teacher.questions.index', ['tab' => 'pending']) }}" class="{{ $__tTab === 'pending' ? 'on' : '' }}"><i class="bi bi-hourglass-split"></i>განხილვაზე</a>
        <a href="{{ route('teacher.questions.index', ['tab' => 'approved']) }}" class="{{ $__tTab === 'approved' ? 'on' : '' }}"><i class="bi bi-check2-circle"></i>დადასტურებული</a>
    </div>
</div>
