{{-- redesign-kidsmart: კლასები / პაკეტები / წვდომა — საერთო სათაური და ჩანართები (დიზაინი A9).
     გამოყენება: @include('admin.partials.settings-tabs', ['active' => 'grades']) --}}
@php
    $__tU   = auth()->user();
    $__tAdm = $__tU->isAdmin();
    $__tAp  = $__tAdm ? null : (\App\Models\RolePermission::allowedPages($__tU->role) ?? []);
    $__tCan = fn($k) => $__tAdm || in_array($k, (array) $__tAp);
    $__tTabs = [
        'grades'   => ['admin.grades',   'admin.grades.index',      'კლასები'],
        'packages' => ['admin.packages', 'admin.packages.index',    'პაკეტები'],
        'perms'    => ['admin.perms',    'admin.permissions.index', 'წვდომა'],
    ];
@endphp
<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">სისტემის საბაზისო პარამეტრები</span>
        <h1 class="ks-display ks-h1">{{ $__tTabs[$active][2] ?? 'პარამეტრები' }}</h1>
    </div>
    <nav aria-label="განყოფილება" style="display:flex;gap:4px;background:#F1E8DE;border-radius:14px;padding:4px;">
        @foreach($__tTabs as $__key => $__t)
            @if($__tCan($__t[0]))
            @php $__on = ($active ?? '') === $__key; @endphp
            <a href="{{ route($__t[1]) }}" @if($__on) aria-current="page" @endif
               style="min-height:40px;padding:0 16px;border-radius:10px;display:flex;align-items:center;text-decoration:none;font-size:14px;font-weight:800;{{ $__on ? 'background:#fff;color:#1D1A17;' : 'color:#4F473F;' }}">{{ $__t[2] }}</a>
            @endif
        @endforeach
    </nav>
</header>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif
