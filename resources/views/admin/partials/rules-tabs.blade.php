{{-- redesign-kidsmart: წესების 4 გვერდის საერთო სათაური და ჩანართები (დიზაინი A7).
     გამოყენება: @include('admin.partials.rules-tabs', ['active' => 'points']) --}}
@php
    $__rU   = auth()->user();
    $__rAdm = $__rU->isAdmin();
    $__rAp  = $__rAdm ? null : (\App\Models\RolePermission::allowedPages($__rU->role) ?? []);
    $__rCan = fn($k) => $__rAdm || in_array($k, (array) $__rAp);
    $__rTabs = [
        'points'   => ['admin.pointrules',    'admin.point-rules.index',    'ქულები',          'მონეტა სწორ პასუხზე'],
        'level'    => ['admin.levelrules',    'admin.level-rules.index',    'დონის ცვლილება', 'ავტომატური ზრდა/კლება'],
        'olympiad' => ['admin.olympiadrules', 'admin.olympiad-rules.index', 'ოლიმპიადა',       'თარიღი და დაშვება'],
        'size'     => ['admin.qcounts',       'admin.question-counts.index','ტესტის ზომა',     'კითხვების რაოდენობა'],
    ];
@endphp
<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">ქულები, დონის ცვლილება, ოლიმპიადა და ტესტის ზომა</span>
        <h1 class="ks-display ks-h1">წესები</h1>
    </div>
</header>

<nav aria-label="წესის ტიპი" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
    @foreach($__rTabs as $__key => $__t)
        @if($__rCan($__t[0]))
        @php $__on = ($active ?? '') === $__key; @endphp
        <a href="{{ route($__t[1]) }}" @if($__on) aria-current="page" @endif
           style="min-height:66px;border-radius:18px;padding:10px 14px;text-decoration:none;display:flex;flex-direction:column;justify-content:center;gap:2px;{{ $__on ? 'background:#C24A0A;color:#fff;' : 'background:#fff;color:#2B2622;border:1px solid #ECE9E4;' }}">
            <span style="font-size:15px;font-weight:800;">{{ $__t[2] }}</span>
            <span style="font-size:12px;opacity:.85;">{{ $__t[3] }}</span>
        </a>
        @endif
    @endforeach
</nav>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif
