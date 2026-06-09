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
</style>

<nav class="bg-dark border-bottom fixed-top" style="border-color:#2a2a2a!important;" data-bs-theme="dark">
    <div class="nav-grid" style="min-height:52px;">
        <div class="nav-left">
            <a class="navbar-brand mb-0" href="{{ url('/') }}" style="line-height:1;">
                <img src="/images/logo.png" alt="KidSmart" style="height:34px;width:auto;">
            </a>
        </div>
        <div class="nav-center"></div>
        <div class="nav-right">
            @auth
            <button id="notif-btn-desktop" onclick="toggleNotifications()" title="შეტყობინებები"
                style="display:none;" class="nav-link-item">
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
            <a class="google-btn" href="{{ route('google.login') }}" data-loader data-loader-text="შესვლა…">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="G">
                Sign in
            </a>
            @endauth
        </div>
    </div>
</nav>

<script>
(function() {
    const nav = document.querySelector('nav');
    if (nav) document.documentElement.style.setProperty('--nav-h', nav.offsetHeight + 'px');
})();

document.addEventListener('submit', e => {
    const form = e.target.closest('form[data-loader]');
    if (form) AppLoader.show(form.dataset.loaderText || 'Loading…');
});
</script>
