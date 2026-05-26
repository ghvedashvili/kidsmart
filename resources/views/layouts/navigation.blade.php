@php
    use App\Models\Question;
    use App\Models\User;

    $levels = Question::select('level')->distinct()->orderBy('level')->get();
    $activeLevel = auth()->user() ? auth()->user()->level : 1;

    // level of the currently loaded page (e.g. /levels/3 → 3)
    $currentPageLevel = (request()->segment(1) === 'levels' && is_numeric(request()->segment(2)))
        ? (int) request()->segment(2)
        : null;

    if (auth()->check()) {
        $myLevel      = auth()->user()->level;
        $totalPlayers = User::count();
        $myHints      = auth()->user()->hints ?? 0;

        // % of players at or above current level
        $atOrAbove   = User::where('level', '>=', $myLevel)->count();
        $topPercent  = $totalPlayers > 0 ? round($atOrAbove / $totalPlayers * 100) : 100;

        // count users at or above each level (1 query)
        $rawCounts = User::selectRaw('level, count(*) as cnt')->groupBy('level')->pluck('cnt', 'level')->toArray();
        $levelPlayerCounts = [];
        foreach ($levels as $lvl) {
            $c = 0;
            foreach ($rawCounts as $ul => $cnt) {
                if ($ul >= $lvl->level) $c += $cnt;
            }
            $levelPlayerCounts[$lvl->level] = $c;
        }
    }
@endphp

<style>
/* ── navbar 3-column grid ── */
.nav-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    width: 100%;
    padding: 0 16px;
}

.nav-left  { justify-self: start; display: flex; align-items: center; }
.nav-center { justify-self: center; }
.nav-right { justify-self: end;  display: flex; align-items: center; gap: 4px; }

/* ── levels toggle button ── */
.levels-toggle {
    background: none;
    border: none;
    color: rgba(255,255,255,0.75);
    font-size: 1.1rem;
    padding: 6px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    border-radius: 4px;
    transition: color 0.15s, background 0.15s;
    letter-spacing: 0.03em;
}
.levels-toggle:hover { color: #fff; background: rgba(255,255,255,0.08); }
.levels-toggle.active { color: #fff; }

.levels-caret {
    font-size: 0.6rem;
    transition: transform 0.2s;
    display: inline-block;
}
.levels-toggle.active .levels-caret { transform: rotate(180deg); }

/* ── full-width stepper panel ── */
.stepper-panel {
    width: 100%;
    background: #111;
    border-top: 1px solid #2a2a2a;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease;
    display: flex;
    align-items: center;
    padding: 0 24px;
}
.stepper-panel.open {
    max-height: 52px;
    height: 52px;
}

.stepper-track {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    width: 100%;
}

/* ── dots ── */
.stepper-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: transform 0.15s, box-shadow 0.15s;
    display: block;
    text-decoration: none;
    position: relative;
}
.stepper-dot:hover { transform: scale(1.6) !important; }

.dot-done    { background: #2ecc71; box-shadow: 0 0 0 3px rgba(46,204,113,0.2); }
.dot-current { background: #f39c12; box-shadow: 0 0 0 4px rgba(243,156,18,0.3); transform: scale(1.3); }
.dot-locked  { background: transparent; border: 2px solid #3a3a3a; cursor: not-allowed; }
.dot-locked:hover { transform: none !important; }

.dot-v {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.dot-v::after {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fff;
}

/* ── dot hover tooltip ── */
.stepper-dot {
    position: relative;
}
.stepper-dot::after {
    content: attr(data-count) " 👥";
    position: absolute;
    bottom: calc(100% + 7px);
    left: 50%;
    transform: translateX(-50%);
    background: #1a1a1a;
    color: #ccc;
    font-size: 10px;
    white-space: nowrap;
    padding: 3px 7px;
    border-radius: 4px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s;
}
.stepper-dot:hover::after {
    opacity: 1;
}

.stepper-line {
    flex: 1;
    height: 2px;
    background: #2a2a2a;
    max-width: 32px;
    min-width: 6px;
}
.line-done { background: #2ecc71; }

/* ── right-side nav items ── */
.nav-link-item {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    font-size: 1.05rem;
    padding: 6px 8px;
    border-radius: 4px;
    transition: color 0.15s, background 0.15s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}
.nav-link-item:hover { color: #fff; background: rgba(255,255,255,0.08); }
.nav-link-item.text-danger { color: #e74c3c; }
.nav-link-item.text-danger:hover { color: #ff6b6b; background: rgba(231,76,60,0.1); }

.nav-nickname {
    color: rgba(255,255,255,0.75);
    font-size: 1.05rem;
    padding: 6px 8px;
    border-radius: 4px;
    cursor: default;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    transition: color 0.15s, background 0.15s;
}
.nav-nickname:hover { color: #fff; background: rgba(255,255,255,0.08); }
.nick-full { display: none; }
.nav-nickname:hover .nick-short { display: none; }
.nav-nickname:hover .nick-full  { display: inline; }

.nav-hints {
    color: #f39c12;
    font-size: 1rem;
    padding: 4px 8px;
    cursor: default;
}

.nav-stat {
    color: rgba(255,255,255,0.4);
    font-size: 0.95rem;
    padding: 4px 6px;
    cursor: default;
    letter-spacing: 0.02em;
    position: relative;
}
.nav-stat::after {
    content: attr(data-tooltip);
    position: absolute;
    top: calc(100% + 7px);
    right: 0;
    background: #1a1a1a;
    color: #ccc;
    font-size: 11px;
    white-space: nowrap;
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #333;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s;
    z-index: 9999;
}
.nav-stat:hover { color: rgba(255,255,255,0.75); }
.nav-stat:hover::after { opacity: 1; }

/* ── mobile toggler ── */
.nav-toggler {
    background: none;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    color: rgba(255,255,255,0.7);
    font-size: 1.5rem;
    line-height: 1;
}

/* ── mobile collapse ── */
.nav-collapse {
    display: none;
    flex-direction: column;
    width: 100%;
    background: #111;
    border-top: 1px solid #222;
    padding: 8px 0;
}
.nav-collapse.open { display: flex; }

.nav-collapse-item {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    font-size: 1.1rem;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s;
}
.nav-collapse-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
.nav-collapse-item.text-danger { color: #e74c3c; }

.google-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
    padding: 6px 10px;
    border-radius: 4px;
    text-decoration: none;
    transition: color 0.15s, background 0.15s;
}
.google-btn:hover { color: #fff; background: rgba(255,255,255,0.08); }
.google-btn img { width: 16px; }
</style>

<nav class="bg-dark border-bottom fixed-top" style="border-color:#2a2a2a!important;" data-bs-theme="dark">

    {{-- ── top row ── --}}
    <div class="nav-grid" style="min-height:52px;">

        {{-- Left: brand --}}
        <div class="nav-left">
            <a class="navbar-brand text-white mb-0" href="{{ url('/') }}" style="font-size:1rem;letter-spacing:.02em;">
                GameVeravart
            </a>
        </div>

        {{-- Center: Levels toggle (auth only) --}}
        <div class="nav-center">
            @auth
            <button class="levels-toggle" id="levelsToggle" onclick="toggleStepper()">
                Levels <span class="levels-caret">▾</span>
            </button>
            @endauth
        </div>

        {{-- Right: desktop items + mobile toggler --}}
        <div class="nav-right">
            @auth
            {{-- Desktop only --}}
            <div class="d-none d-md-flex align-items-center gap-1">
                <span class="nav-nickname" title="{{ auth()->user()->nickname }}">
                    👤 <span class="nick-short">{{ \Illuminate\Support\Str::limit(auth()->user()->nickname, 5, '…') }}</span><span class="nick-full">{{ auth()->user()->nickname }}</span>
                </span>

                <span class="nav-hints">💡 {{ $myHints }}</span>

                <span class="nav-stat"
                      data-tooltip="ამ ლეველს {{ $topPercent }}%-მა შეძლო">🏆 top {{ $topPercent }}%</span>

                @if(auth()->user()->isAdmin())
                <a class="nav-link-item text-danger fw-bold" href="{{ route('admin.panel') }}">
                    <i class="bi bi-shield-lock-fill"></i> Admin
                </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" data-loader data-loader-text="Signing out…" class="m-0">
                    @csrf
                    <button type="submit" class="nav-link-item text-danger" style="background:none;border:none;cursor:pointer;">
                        Logout
                    </button>
                </form>
            </div>

            {{-- Mobile toggler --}}
            <button class="nav-toggler d-md-none" onclick="toggleMobileNav(this)" aria-label="Menu">
                ☰
            </button>
            @else
            <a class="google-btn" href="{{ route('google.login') }}" data-loader data-loader-text="Signing in…">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="G">
                Login with Google
            </a>
            @endauth
        </div>
    </div>

    {{-- ── stepper panel (auth only) ── --}}
    @auth
    <div class="stepper-panel" id="stepperPanel">
        <div class="stepper-track">
            @foreach($levels as $idx => $lvl)
                @php
                    $isCompleted = $lvl->level < $activeLevel;
                    $isCurrent   = $lvl->level == $activeLevel;
                    $isLocked    = $lvl->level > auth()->user()->level;
                    $isLast      = $idx === count($levels) - 1;
                    $cnt         = $levelPlayerCounts[$lvl->level] ?? 0;
                @endphp

                <a @if(!$isLocked) href="{{ route('levels.show', $lvl->level) }}" data-loader data-loader-text="Loading…" @else href="#" @endif
                   class="stepper-dot {{ $isCompleted ? 'dot-done' : ($isCurrent ? 'dot-current' : 'dot-locked') }}"
                   data-count="{{ $cnt }}">@if($currentPageLevel === $lvl->level)<span class="dot-v"></span>@endif</a>

                @if(!$isLast)
                    <div class="stepper-line {{ $isCompleted ? 'line-done' : '' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── mobile collapse menu ── --}}
    <div class="nav-collapse d-md-none" id="mobileNav">
        <span class="nav-collapse-item nav-nickname" style="color:#aaa;">
            👤 <span class="nick-short">{{ \Illuminate\Support\Str::limit(auth()->user()->nickname, 5, '…') }}</span><span class="nick-full">{{ auth()->user()->nickname }}</span>
        </span>
        <span class="nav-collapse-item" style="cursor:default;color:#666;padding-top:0;">
            💡 {{ $myHints }} hints
        </span>
        <span class="nav-collapse-item" style="cursor:default;color:#666;padding-top:0;" title="ამ ლეველს {{ $topPercent }}%-მა შეძლო">
            🏆 top {{ $topPercent }}%
        </span>

        @if(auth()->user()->isAdmin())
        <a class="nav-collapse-item text-danger" href="{{ route('admin.panel') }}">
            <i class="bi bi-shield-lock-fill"></i> Admin Panel
        </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" data-loader data-loader-text="Signing out…" class="m-0">
            @csrf
            <button type="submit" class="nav-collapse-item text-danger w-100" style="background:none;border:none;text-align:left;cursor:pointer;">
                Logout
            </button>
        </form>
    </div>
    @endauth

</nav>

<script>
function toggleStepper() {
    const panel  = document.getElementById('stepperPanel');
    const toggle = document.getElementById('levelsToggle');
    const nav    = document.querySelector('nav');
    const isOpen = panel.classList.contains('open');

    // გავზომოთ ᲧᲕᲔᲚᲐᲤᲔᲠᲘ class-ის შეცვლამდე
    const navH   = nav.offsetHeight;
    const panelH = isOpen ? panel.offsetHeight : 0;

    if (isOpen) panel.style.overflow = 'hidden';

    panel.classList.toggle('open', !isOpen);
    toggle.classList.toggle('active', !isOpen);

    document.body.style.transition = 'padding-top 0.3s ease';

    if (!isOpen) {
        setTimeout(() => {
            const h = nav.offsetHeight + 'px';
            document.body.style.paddingTop = h;
            document.documentElement.style.setProperty('--nav-h', h);
            panel.style.overflow = 'visible';
        }, 310);
    } else {
        const h = (navH - panelH) + 'px';
        document.body.style.paddingTop = h;
        document.documentElement.style.setProperty('--nav-h', h);
    }
}

function toggleMobileNav(btn, forceClose = false) {
    const mobileNav = document.getElementById('mobileNav');
    const navEl     = document.querySelector('nav');
    const isOpen    = mobileNav.classList.contains('open');

    if (forceClose) {
        mobileNav.classList.remove('open');
        if (btn) btn.textContent = '☰';
    } else {
        mobileNav.classList.toggle('open', !isOpen);
        if (btn) btn.textContent = (!isOpen) ? '✕' : '☰';
    }

    document.body.style.transition = 'padding-top 0.3s ease';
    requestAnimationFrame(() => {
        const h = navEl.offsetHeight + 'px';
        document.body.style.paddingTop = h;
        document.documentElement.style.setProperty('--nav-h', h);
    });
}

// stepper panel დაიხუროს გარე კლიქზე
document.addEventListener('click', e => {
    const panel  = document.getElementById('stepperPanel');
    const toggle = document.getElementById('levelsToggle');
    if (!panel || !toggle) return;
    if (!panel.contains(e.target) && !toggle.contains(e.target)) {
        if (!panel.classList.contains('open')) return;
        const nav    = document.querySelector('nav');
        const navH   = nav.offsetHeight;
        const panelH = panel.offsetHeight;
        panel.style.overflow = 'hidden';
        panel.classList.remove('open');
        toggle.classList.remove('active');
        const h = (navH - panelH) + 'px';
        document.body.style.transition = 'padding-top 0.3s ease';
        document.body.style.paddingTop = h;
        document.documentElement.style.setProperty('--nav-h', h);
    }
});

document.addEventListener('submit', e => {
    const form = e.target.closest('form[data-loader]');
    if (form) AppLoader.show(form.dataset.loaderText || 'Loading…');
});

// page load-ზე --nav-h დასეტვა
(function() {
    const nav = document.querySelector('nav');
    if (nav) document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
})();
</script>
