{{-- ადმინის გვერდითი მენიუ (redesign-kidsmart).
     ჩანს მხოლოდ admin.* გვერდებზე და ტესტის გადახედვაზე — იხ. layouts/app.blade.php.
     ბმულები იგივე ნებართვებით იფილტრება, რაც ძველ ზედა ზოლში. --}}
@php
    $__su       = auth()->user();
    $__sIsAdmin = $__su->isAdmin();
    $__sAp      = $__sIsAdmin ? null : (\App\Models\RolePermission::allowedPages($__su->role) ?? []);
    $__sCan     = fn($key) => $__sIsAdmin || in_array($key, (array) $__sAp);

    $__ico = [
        'panel'     => 'M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
        'preview'   => 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
        'questions' => 'M9 9a3 3 0 1 1 4 2.8c-.6.3-1 .9-1 1.6V14M12 17.5v.5M4 4h16v16H4z',
        'topics'    => 'M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4zM20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6z',
        'themes'    => 'M12 3a9 9 0 1 0 0 18c1 0 1.5-.8 1.5-1.6 0-1.2-1-1.6-1-2.6 0-.9.7-1.4 1.6-1.4H17a4 4 0 0 0 4-4c0-4.7-4-8.4-9-8.4z',
        'grades'    => 'M22 9L12 4 2 9l10 5 10-5zM6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5',
        'points'    => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM12 7v10M9 9.5c0-1 1.3-1.5 3-1.5s3 .7 3 1.8c0 2.7-6 1.2-6 4 0 1.1 1.3 1.7 3 1.7s3-.5 3-1.5',
        'level'     => 'M4 20V14M10 20V9M16 20V4M2 20h20',
        'olympiad'  => 'M8 4h8v5a4 4 0 0 1-8 0zM16 5h3v2a3 3 0 0 1-3 3M8 5H5v2a3 3 0 0 0 3 3M12 13v4M8 20h8',
        'size'      => 'M4 6h16M4 12h10M4 18h6',
        'ach'       => 'M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM8.5 14L7 22l5-3 5 3-1.5-8',
        'users'     => 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2 21a7 7 0 0 1 14 0M17 4a4 4 0 0 1 0 7.5M22 21a7 7 0 0 0-4-6.3',
        'packages'  => 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8',
        'perms'     => 'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4',
    ];

    // [ნებართვის გასაღები, სახელი, route, აქტიურობის pattern, აიკონი]
    $__groups = [
        'მიმოხილვა' => [
            ['admin.panel', 'პანელი', 'admin.panel', 'admin.panel', 'panel'],
            ['*', 'ტესტის გადახედვა', 'test.preview', 'test.preview', 'preview'],
        ],
        'კონტენტი' => [
            ['admin.questions', 'კითხვები', 'admin.questions.index', 'admin.questions.*', 'questions'],
            ['admin.topics', 'თემები', 'admin.topics.index', 'admin.topics.*', 'topics'],
            ['admin.themes', 'თემატიკა და ცვლადები', 'admin.themes.index', 'admin.themes.*', 'themes'],
            ['admin.grades', 'კლასები', 'admin.grades.index', 'admin.grades.*', 'grades'],
        ],
        'გეიმიფიკაცია' => [
            ['admin.pointrules', 'ქულები', 'admin.point-rules.index', 'admin.point-rules.*', 'points'],
            ['admin.levelrules', 'დონის ცვლილება', 'admin.level-rules.index', 'admin.level-rules.*', 'level'],
            ['admin.olympiadrules', 'ოლიმპიადა', 'admin.olympiad-rules.index', 'admin.olympiad-rules.*', 'olympiad'],
            ['admin.qcounts', 'ტესტის ზომა', 'admin.question-counts.index', 'admin.question-counts.*', 'size'],
            ['admin.achievements', 'მიღწევები', 'admin.achievements.index', 'admin.achievements.*', 'ach'],
        ],
        'მომხმარებლები' => [
            ['admin.users', 'მომხმარებლები', 'admin.users.index', 'admin.users.*', 'users'],
            ['admin.packages', 'პაკეტები', 'admin.packages.index', 'admin.packages.*', 'packages'],
            ['admin.perms', 'წვდომა', 'admin.permissions.index', 'admin.permissions.*', 'perms'],
        ],
    ];
@endphp
<aside class="ks-admin-side" aria-label="ადმინის მენიუ">
    <a href="{{ route('admin.panel') }}" class="ks-side-brand">
        <span class="ks-side-logo">
            <svg width="24" height="24" viewBox="0 0 120 120" aria-hidden="true"><path d="M22 18 L48 40 L26 56 Z" fill="#E2590F"/><path d="M98 18 L72 40 L94 56 Z" fill="#E2590F"/><ellipse cx="60" cy="66" rx="40" ry="36" fill="#E2590F"/><path d="M20 70 Q40 66 60 86 Q80 66 100 70 Q96 100 60 104 Q24 100 20 70 Z" fill="#FFF8F1"/><circle cx="45" cy="62" r="6" fill="#1D1A17"/><circle cx="75" cy="62" r="6" fill="#1D1A17"/><ellipse cx="60" cy="82" rx="6" ry="4.5" fill="#1D1A17"/></svg>
        </span>
        <span class="ks-side-name"><b>KidSmart</b><small>ადმინ პანელი</small></span>
    </a>

    <nav class="ks-side-nav">
        @foreach($__groups as $__gTitle => $__items)
            @php $__visible = array_filter($__items, fn($it) => $it[0] === '*' || $__sCan($it[0])); @endphp
            @if(count($__visible))
            <div class="ks-side-group">
                <span class="ks-side-title">{{ $__gTitle }}</span>
                @foreach($__visible as $__it)
                    @if(\Illuminate\Support\Facades\Route::has($__it[2]))
                    <a href="{{ route($__it[2]) }}" class="ks-side-link{{ request()->routeIs($__it[3]) ? ' active' : '' }}"
                       @if(request()->routeIs($__it[3])) aria-current="page" @endif>
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $__ico[$__it[4]] }}"/></svg>
                        <span>{{ $__it[1] }}</span>
                    </a>
                    @endif
                @endforeach
            </div>
            @endif
        @endforeach
    </nav>

    <div class="ks-side-user">
        <span class="ks-side-avatar">{{ mb_substr($__su->name, 0, 1) }}</span>
        <span style="flex-grow:1;display:flex;flex-direction:column;min-width:0;">
            <span style="font-size:13px;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $__su->name }}</span>
            <span style="font-size:11px;color:#D8CEC3;">{{ $__sIsAdmin ? 'სრული წვდომა' : $__su->role }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ks-side-logout">გასვლა</button>
        </form>
    </div>
</aside>
