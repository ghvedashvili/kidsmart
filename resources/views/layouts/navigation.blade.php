<style>
.nav-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    width: 100%;
    padding: 0 16px;
}
.nav-left  { justify-self: start; display: flex; align-items: center; }
.nav-center { justify-self: center; }
.nav-right { justify-self: end; display: flex; align-items: center; gap: 4px; }
.nav-link-item {
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.95rem;
    padding: 6px 8px;
    border-radius: 4px;
    transition: color 0.15s, background 0.15s;
    display: flex;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
}
.nav-link-item:hover { color: #fff; background: rgba(255,255,255,0.08); }
.google-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.8);
    font-size: 0.75rem;
    padding: 6px 10px;
    border-radius: 4px;
    text-decoration: none;
    transition: color 0.15s, background 0.15s;
    font-family: 'Goldman', monospace;
    letter-spacing: 0.06em;
}
.google-btn:hover { color: #fff; background: rgba(255,255,255,0.08); }
.google-btn img { width: 16px; }
@media (max-width: 640px) { #secNav { display: none !important; } }
</style>

<nav class="bg-dark border-bottom fixed-top" style="border-color:#2a2a2a!important;" data-bs-theme="dark">
    <div class="nav-grid" style="min-height:52px;">
        <div class="nav-left">
            <a class="navbar-brand mb-0" href="{{ url('/') }}">
                <img src="/img/logo.png" alt="KidSmart" style="height:32px;width:auto;">
            </a>
        </div>
        <div class="nav-center" id="secNav" style="display:flex;gap:2px;">
            <a class="nav-link-item" href="/#questions" onclick="secScroll(event,'questions')" style="font-size:0.8rem;">ამოცანები</a>
            <a class="nav-link-item" href="/#adaptive"  onclick="secScroll(event,'adaptive')"  style="font-size:0.8rem;">სწავლება</a>
            <a class="nav-link-item" href="/#detective" onclick="secScroll(event,'detective')" style="font-size:0.8rem;">დეტექტივი</a>
            <a class="nav-link-item" href="/#market"    onclick="secScroll(event,'market')"    style="font-size:0.8rem;">ჯილდოები</a>
        </div>
        <div class="nav-right">
            @auth
            <button id="notif-btn-desktop" onclick="toggleNotifications()" title="შეტყობინებები"
                class="nav-link-item">
                <i id="notif-icon-desktop" class="bi bi-bell"></i>
            </button>

            @if(auth()->user()->isAdmin())
            <a class="nav-link-item" href="{{ route('admin.panel') }}" title="Admin" style="color:rgba(231,76,60,0.8);">
                <i class="bi bi-shield-lock-fill"></i>
            </a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="nav-link-item" title="გასვლა">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
            @else
            <button class="nav-link-item" onclick="toggleLoginModal(event)"
                style="font-family:'Goldman',monospace;font-size:0.75rem;letter-spacing:0.06em;color:rgba(255,255,255,0.8);gap:6px;"
                id="loginNavBtn">
                <i class="bi bi-person"></i> შესვლა
            </button>
            @endauth
        </div>
    </div>
</nav>

{{-- Login mini modal --}}
@guest
<div id="loginModal" style="
    display:none; position:fixed; top:58px; right:12px; z-index:99999;
    background:#fff; border:1px solid #e0e0e0; border-radius:6px;
    box-shadow:0 8px 32px rgba(0,0,0,0.13); width:260px;
    font-family:'Goldman',monospace; overflow:hidden;
">
    {{-- Tabs --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #eee;">
        <button id="lmTabParent" onclick="lmSwitch('parent')" style="
            font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.06em;
            padding:11px;border:none;cursor:pointer;transition:all 0.15s;
            background:#111;color:#fff;
        ">მშობელი</button>
        <button id="lmTabChild" onclick="lmSwitch('child')" style="
            font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.06em;
            padding:11px;border:none;cursor:pointer;transition:all 0.15s;
            background:transparent;color:#888;
        ">ბავშვი</button>
    </div>

    {{-- Parent panel --}}
    <div id="lmPanelParent" style="padding:16px;">
        <a href="{{ route('google.login') }}" data-loader data-loader-text="შესვლა…"
            style="
                display:flex;align-items:center;justify-content:center;gap:10px;
                padding:11px;width:100%;box-sizing:border-box;
                font-family:'Goldman',monospace;font-size:0.75rem;letter-spacing:0.06em;
                color:#fff;background:#111;border:none;border-radius:4px;
                text-decoration:none;cursor:pointer;transition:background 0.2s;
            "
            onmouseover="this.style.background='#333'"
            onmouseout="this.style.background='#111'">
            <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#fff"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#fff"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#fff"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#fff"/>
            </svg>
            Google-ით შესვლა
        </a>
    </div>

    {{-- Child panel --}}
    <div id="lmPanelChild" style="padding:16px;display:none;">
        <form method="POST" action="{{ route('child-login') }}">
            @csrf
            <input type="text" name="child_code"
                placeholder="შენი კოდი"
                maxlength="8" autocomplete="off"
                oninput="this.value=this.value.toUpperCase()"
                style="
                    width:100%;box-sizing:border-box;padding:10px 12px;
                    font-family:'Goldman',monospace;font-size:0.88rem;
                    letter-spacing:0.2em;text-align:center;
                    border:1px solid #ddd;border-radius:4px;outline:none;
                    color:#333;margin-bottom:10px;
                    transition:border-color 0.2s;
                "
                onfocus="this.style.borderColor='#aaa'"
                onblur="this.style.borderColor='#ddd'">
            <button type="submit" style="
                width:100%;padding:11px;
                font-family:'Goldman',monospace;font-size:0.75rem;letter-spacing:0.06em;
                background:#111;color:#fff;border:none;border-radius:4px;
                cursor:pointer;transition:background 0.2s;
            "
            onmouseover="this.style.background='#333'"
            onmouseout="this.style.background='#111'">შესვლა →</button>
        </form>
    </div>
</div>
@endguest

<script>
(function() {
    const nav = document.querySelector('nav');
    if (nav) document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
})();

document.addEventListener('submit', e => {
    const form = e.target.closest('form[data-loader]');
    if (form) AppLoader.show(form.dataset.loaderText || 'Loading…');
});

function toggleLoginModal(e) {
    e.stopPropagation();
    var m = document.getElementById('loginModal');
    if (!m) return;
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}

function lmSwitch(tab) {
    var isParent = tab === 'parent';
    document.getElementById('lmPanelParent').style.display = isParent ? 'block' : 'none';
    document.getElementById('lmPanelChild').style.display  = isParent ? 'none'  : 'block';
    var tP = document.getElementById('lmTabParent');
    var tC = document.getElementById('lmTabChild');
    tP.style.background = isParent ? '#111' : 'transparent';
    tP.style.color       = isParent ? '#fff' : '#888';
    tC.style.background  = isParent ? 'transparent' : '#111';
    tC.style.color        = isParent ? '#888' : '#fff';
}

function secScroll(e, id) {
    var el = document.getElementById(id);
    if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
}

document.addEventListener('click', function(e) {
    var m = document.getElementById('loginModal');
    var btn = document.getElementById('loginNavBtn');
    if (m && btn && !m.contains(e.target) && !btn.contains(e.target)) {
        m.style.display = 'none';
    }
});
</script>
