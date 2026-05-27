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

        // count users at or above each level (1 query)
        $rawCounts = User::selectRaw('level, count(*) as cnt')->groupBy('level')->pluck('cnt', 'level')->toArray();
        $levelPlayerCounts = [];
        foreach ($levels as $lvl) {
            $levelPlayerCounts[$lvl->level] = $rawCounts[$lvl->level] ?? 0;
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
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    max-height: 0;
    transition: max-height 0.3s ease;
    display: flex;
    align-items: center;
    cursor: grab;
}
.stepper-panel::-webkit-scrollbar { display: none; }
.stepper-panel.open {
    max-height: 62px;
    height: 62px;
}

.stepper-track {
    display: flex;
    align-items: flex-start;
    gap: 0;
    padding: 6px 0;
}

.stepper-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
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

/* ── dot player count label ── */
.stepper-count {
    font-size: 15px;
    color: #666;
    white-space: nowrap;
    line-height: 1;
    cursor: default;
}

.stepper-line {
    flex: none;
    width: 24px;
    height: 2px;
    background: #2a2a2a;
    margin-top: 6px;
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

                <div class="stepper-item">
                    <a @if(!$isLocked) href="{{ route('levels.show', $lvl->level) }}" data-loader data-loader-text="Loading…" @else href="#" @endif
                       class="stepper-dot {{ $isCompleted ? 'dot-done' : ($isCurrent ? 'dot-current' : 'dot-locked') }}">
                        @if($currentPageLevel === $lvl->level)<span class="dot-v"></span>@endif
                    </a>
                    <span class="stepper-count" data-tip="ამ ლეველზე იმყოფება {{ $cnt }} მოთამაშე">👤{{ $cnt }}</span>
                </div>

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

    document.body.style.transition = 'padding-top 0.3s ease';

    if (!isOpen) {
        // setup BEFORE animation — panel is still max-height:0 but offsetWidth is correct
        panel.style.overflow = '';
        setupStepper();
        scrollToCurrentLevel(true);

        panel.classList.add('open');
        toggle.classList.add('active');

        setTimeout(() => {
            const h = nav.offsetHeight + 'px';
            document.body.style.paddingTop = h;
            document.documentElement.style.setProperty('--nav-h', h);
        }, 310);
    } else {
        panel.style.overflow = 'hidden';
        panel.classList.remove('open');
        toggle.classList.remove('active');

        const h = (navH - panelH) + 'px';
        document.body.style.paddingTop = h;
        document.documentElement.style.setProperty('--nav-h', h);
    }
}

let _infScrollHandler = null;
let _contentWidth     = 0;

function setupStepper() {
    const panel = document.getElementById('stepperPanel');
    const track = panel && panel.querySelector('.stepper-track');
    if (!panel || !track) return;

    // clean up previous state
    if (_infScrollHandler) { panel.removeEventListener('scroll', _infScrollHandler); _infScrollHandler = null; }
    track.querySelectorAll('[data-clone],[data-spacer]').forEach(el => el.remove());
    track.style.justifyContent = '';
    track.style.width = '';
    panel.style.overflowX = '';
    panel.style.cursor = '';

    const origItems       = Array.from(track.children);
    const rawContentWidth = origItems.reduce((s, el) => s + el.offsetWidth, 0);
    const panelWidth      = panel.offsetWidth;

    if (rawContentWidth <= panelWidth) {
        // ყველა ეტევა – ცენტრში, სქროლი გამორთულია
        track.style.justifyContent = 'center';
        track.style.width          = '100%';
        panel.style.overflowX      = 'hidden';
        panel.style.cursor         = 'default';
        return;
    }

    // არ ეტევა – infinite carousel
    // ბოლო ლეველის შემდეგ invisible spacer (=ხაზის სიგანე) → loop-junction-ზე თანაბარი მანძილი, ხაზი არ ჩანს
    const spacer = document.createElement('div');
    spacer.dataset.spacer = '1';
    spacer.style.cssText  = 'flex:none;width:24px;';
    track.appendChild(spacer);

    const allItems     = Array.from(track.children);
    const contentWidth = allItems.reduce((s, el) => s + el.offsetWidth, 0);

    panel.style.overflowX = 'auto';
    panel.style.cursor    = 'grab';

    const cloneBefore = allItems.map(el => { const c = el.cloneNode(true); c.dataset.clone = 'b'; return c; });
    const cloneAfter  = allItems.map(el => { const c = el.cloneNode(true); c.dataset.clone = 'a'; return c; });
    cloneBefore.reverse().forEach(el => track.insertBefore(el, track.firstChild));
    cloneAfter.forEach(el => track.appendChild(el));

    _contentWidth = contentWidth;

    // middle set-ზე დასეტვა (instant)
    panel.scrollLeft = contentWidth;

    _infScrollHandler = () => {
        const s = panel.scrollLeft;
        if (s < contentWidth * 0.5)  panel.scrollLeft = s + contentWidth;
        else if (s > contentWidth * 1.5) panel.scrollLeft = s - contentWidth;
    };
    panel.addEventListener('scroll', _infScrollHandler, { passive: true });
}

function scrollToCurrentLevel(instant = false) {
    const panel = document.getElementById('stepperPanel');
    if (!panel || panel.style.overflowX === 'hidden') return;
    const track = panel.querySelector('.stepper-track');
    const currentItem = Array.from(track.querySelectorAll('.stepper-item')).find(
        el => !el.dataset.clone && el.querySelector('.dot-current')
    );
    if (!currentItem) return;
    const panelRect = panel.getBoundingClientRect();
    const itemRect  = currentItem.getBoundingClientRect();
    const delta = (itemRect.left + itemRect.width / 2) - (panelRect.left + panelRect.width / 2);

    let target = panel.scrollLeft + delta;

    // clamp: არ ვაჩვენოთ clone-ები საწყის პოზიციაზე
    // [_contentWidth … 2×_contentWidth − panelWidth] = original set-ის ბუნებრივი საზღვრები
    if (_contentWidth > 0) {
        const minS = _contentWidth - 20;
        const maxS = _contentWidth * 2 - panel.offsetWidth;
        if (maxS > minS) target = Math.max(minS, Math.min(maxS, target));
    }

    if (instant) {
        panel.scrollLeft = target;
    } else {
        panel.scrollTo({ left: target, behavior: 'smooth' });
    }
}

// drag-to-scroll (desktop)
(function() {
    const panel = document.getElementById('stepperPanel');
    if (!panel) return;
    let isDown = false, startX, scrollLeft;
    panel.addEventListener('mousedown', e => {
        if (panel.style.overflowX === 'hidden') return;
        isDown = true; startX = e.pageX - panel.offsetLeft; scrollLeft = panel.scrollLeft;
        panel.style.cursor = 'grabbing';
    });
    panel.addEventListener('mouseleave', () => { isDown = false; if (panel.style.overflowX !== 'hidden') panel.style.cursor = 'grab'; });
    panel.addEventListener('mouseup',    () => { isDown = false; if (panel.style.overflowX !== 'hidden') panel.style.cursor = 'grab'; });
    panel.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        panel.scrollLeft = scrollLeft - (e.pageX - panel.offsetLeft - startX) * 1.5;
    });
})();

window.addEventListener('resize', () => {
    const panel = document.getElementById('stepperPanel');
    if (panel && panel.classList.contains('open')) { setupStepper(); scrollToCurrentLevel(true); }
});

// ── stepper count tooltip ──
(function() {
    const tip = document.createElement('div');
    tip.style.cssText = 'position:fixed;background:#1a1a1a;color:#ccc;font-size:11px;padding:4px 10px;border-radius:5px;border:1px solid #333;pointer-events:none;opacity:0;transition:opacity .15s;z-index:99999;white-space:nowrap;';
    document.body.appendChild(tip);

    let hideTimer = null;

    function show(el) {
        if (!el.dataset.tip) return;
        clearTimeout(hideTimer);
        tip.textContent = el.dataset.tip;
        const r = el.getBoundingClientRect();
        tip.style.opacity = '1';
        tip.style.left = (r.left + r.width / 2) + 'px';
        tip.style.top  = (r.top - 34) + 'px';
        tip.style.transform = 'translateX(-50%)';
    }
    function hide(delay = 0) {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => { tip.style.opacity = '0'; }, delay);
    }

    // desktop hover
    document.addEventListener('mouseover', e => {
        const c = e.target.closest('.stepper-count');
        if (c) show(c);
    });
    document.addEventListener('mouseout', e => {
        if (e.target.closest('.stepper-count')) hide();
    });

    // mobile tap toggle
    document.addEventListener('click', e => {
        const c = e.target.closest('.stepper-count');
        if (!c) return;
        if (tip.style.opacity === '1') { hide(); }
        else { show(c); hide(2400); }
    });
})();

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
