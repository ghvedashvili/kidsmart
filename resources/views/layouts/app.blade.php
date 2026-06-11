<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KidSmart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#080808">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Goldman&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @stack('head')
</head>
<body class="@yield('bodyClass')">

<div id="page-loader">
    <div class="spinner"></div>
</div>

{{-- PWA Modal --}}
<style>
.pwa-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);z-index:99999;display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.3s ease;}
.pwa-overlay.open{opacity:1;pointer-events:auto;}
.pwa-card{width:100%;max-width:480px;background:#111;border:1px solid #222;border-radius:20px 20px 0 0;padding:28px 24px 40px;transform:translateY(30px);transition:transform 0.35s cubic-bezier(0.34,1.56,0.64,1);}
.pwa-overlay.open .pwa-card{transform:translateY(0);}
.pwa-handle{width:40px;height:4px;background:#333;border-radius:2px;margin:0 auto 24px;}
.pwa-title{font-family:'Goldman',monospace;font-size:1rem;color:#ddd;text-align:center;letter-spacing:0.06em;margin-bottom:6px;}
.pwa-subtitle{font-family:'Goldman',monospace;font-size:0.7rem;color:#555;text-align:center;letter-spacing:0.05em;margin-bottom:28px;}
.pwa-tabs{display:flex;gap:8px;margin-bottom:24px;}
.pwa-tab{flex:1;padding:8px;border:1px solid #2a2a2a;border-radius:8px;background:none;color:#555;font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.05em;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;gap:6px;}
.pwa-tab.active{border-color:#444;color:#ddd;background:#1a1a1a;}
.pwa-steps{display:flex;flex-direction:column;gap:16px;}
.pwa-step{display:flex;align-items:center;gap:14px;}
.pwa-step-num{width:32px;height:32px;border-radius:50%;border:1px solid #2a2a2a;display:flex;align-items:center;justify-content:center;font-family:'Goldman',monospace;font-size:0.7rem;color:#555;flex-shrink:0;}
.pwa-step-icon{width:40px;height:40px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.pwa-step-text{font-family:'Goldman',monospace;font-size:0.72rem;color:#888;letter-spacing:0.03em;line-height:1.6;}
.pwa-step-text strong{color:#bbb;font-weight:normal;}
.pwa-install-native-btn{width:100%;margin-top:24px;padding:13px;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:8px;color:#ccc;font-family:'Goldman',monospace;font-size:0.8rem;letter-spacing:0.08em;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;justify-content:center;gap:8px;}
.pwa-install-native-btn:hover{border-color:#444;color:#fff;}
</style>

<div class="pwa-overlay" id="pwaOverlay" onclick="if(event.target===this)closePwaModal()">
    <div class="pwa-card">
        <div class="pwa-handle"></div>
        <div class="pwa-title">KIDSMART APP</div>
        <div class="pwa-subtitle">დააყენე უფასოდ · ინტერნეტის გარეშეც</div>
        <div class="pwa-tabs">
            <button class="pwa-tab active" id="tabIos" onclick="switchTab('ios')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11"/></svg>
                iOS
            </button>
            <button class="pwa-tab" id="tabAndroid" onclick="switchTab('android')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.341A7 7 0 0117 12.5a7 7 0 01-7-7 7 7 0 01-.523 2.659L7.5 6.182A8.944 8.944 0 007 9.5c0 4.971 4.029 9 9 9a8.944 8.944 0 003.318-.5l-1.795-2.659zM6.341 6.477A7 7 0 006.5 12.5a7 7 0 007 7 7 7 0 00.659-.341l-7.818-12.682zM15.5 2a1 1 0 110 2 1 1 0 010-2zM8.5 2a1 1 0 110 2 1 1 0 010-2z"/></svg>
                Android
            </button>
        </div>
        <div id="stepsIos" class="pwa-steps">
            <div class="pwa-step"><div class="pwa-step-num">1</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg></div><div class="pwa-step-text">გახსენი <strong>Safari</strong> ბრაუზერი და გადადი ამ გვერდზე</div></div>
            <div class="pwa-step"><div class="pwa-step-num">2</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg></div><div class="pwa-step-text">ეკრანის ბოლოში დააჭირე <strong>Share ↑</strong> ღილაკს</div></div>
            <div class="pwa-step"><div class="pwa-step-num">3</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="12" y1="17" x2="12" y2="21"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="10" x2="12" y2="14"/><line x1="10" y1="12" x2="14" y2="12"/></svg></div><div class="pwa-step-text">სიაში იპოვე <strong>„Add to Home Screen"</strong></div></div>
            <div class="pwa-step"><div class="pwa-step-num">4</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><polyline points="20 6 9 17 4 12"/></svg></div><div class="pwa-step-text">ზედა მარჯვნივ დააჭირე <strong>„Add"</strong></div></div>
        </div>
        <div id="stepsAndroid" class="pwa-steps" style="display:none;">
            <div class="pwa-step"><div class="pwa-step-num">1</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg></div><div class="pwa-step-text">გახსენი <strong>Chrome</strong> ბრაუზერი</div></div>
            <div class="pwa-step"><div class="pwa-step-num">2</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="#888"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg></div><div class="pwa-step-text">მენიუ <strong>⋮</strong> → <strong>„Add to Home screen"</strong></div></div>
            <div class="pwa-step"><div class="pwa-step-num">3</div><div class="pwa-step-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.8"><polyline points="20 6 9 17 4 12"/></svg></div><div class="pwa-step-text">დააჭირე <strong>„Install"</strong> ან <strong>„Add"</strong></div></div>
            <button class="pwa-install-native-btn" id="nativeInstallBtn" style="display:none;" onclick="triggerNativeInstall()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v13M8 9l4-4 4 4"/><path d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/></svg>
                დაყენება
            </button>
        </div>
    </div>
</div>

@include('layouts.navigation')

<style>
    html {
        background-color: #fdf8f0;
        background-image:
            repeating-linear-gradient(transparent, transparent 31px, rgba(173,200,222,0.55) 31px, rgba(173,200,222,0.55) 32px),
            repeating-linear-gradient(90deg, transparent, transparent 31px, rgba(173,200,222,0.55) 31px, rgba(173,200,222,0.55) 32px);
        overscroll-behavior: none;
        scroll-behavior: smooth;
    }
    body { background: transparent; overscroll-behavior: none; padding-top: 56px; }

    #page-loader {
        position: fixed; inset: 0; background: #080808;
        display: flex; align-items: center; justify-content: center;
        z-index: 999999; transition: opacity 0.3s ease;
    }
    #page-loader.fade-out { opacity: 0; pointer-events: none; }

    .app-loader {
        position: fixed; inset: 0; background: rgba(0,0,0,0.65);
        backdrop-filter: blur(4px); display: flex; flex-direction: column;
        align-items: center; justify-content: center; z-index: 99999;
    }
    .app-loader.hidden { display: none; }

    .spinner {
        width: 52px; height: 52px;
        border: 4px solid rgba(255,255,255,0.25);
        border-top-color: #fff; border-radius: 50%;
        animation: spin 0.9s linear infinite;
    }
    .loader-text { margin-top: 14px; color: #fff; font-size: 14px; opacity: 0.85; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="container-fluid px-0" style="position:relative;z-index:1;">
    @yield('content')
</div>

<div id="app-loader" class="app-loader hidden">
    <div class="spinner"></div>
    <div class="loader-text">Loading…</div>
</div>

{{-- Cookie consent --}}
<div id="cookieBanner" style="
    display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999;
    background:#fff; border-top:1px solid #e8e8e8;
    padding:16px 20px; font-family:'Goldman',monospace;
    box-shadow:0 -4px 24px rgba(0,0,0,0.07);
">
    <div style="max-width:640px; margin:0 auto;">
        <div style="font-size:0.78rem; color:#111; letter-spacing:0.06em; margin-bottom:6px;">
            ჩვენ ვიყენებთ Cookies-ს
        </div>
        <div style="font-size:0.67rem; color:#888; letter-spacing:0.04em; line-height:1.6; margin-bottom:14px;">
            Google Analytics გვეხმარება გავიგოთ, როგორ იყენებენ ვიზიტორები ჩვენს საიტს.
            მონაცემები ანონიმურია და მხოლოდ საიტის გაუმჯობესებისთვის გამოიყენება.
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="cookieChoice(false)" style="
                font-family:'Goldman',monospace; font-size:0.72rem; letter-spacing:0.08em;
                background:transparent; border:1px solid #ddd; border-radius:4px;
                color:#888; padding:9px 20px; cursor:pointer; transition:border-color 0.2s,color 0.2s;
            " onmouseover="this.style.borderColor='#aaa';this.style.color='#333'"
               onmouseout="this.style.borderColor='#ddd';this.style.color='#888'">
                უარყოფა
            </button>
            <button onclick="cookieChoice(true)" style="
                font-family:'Goldman',monospace; font-size:0.72rem; letter-spacing:0.08em;
                background:#111; border:none; border-radius:4px;
                color:#fff; padding:9px 20px; cursor:pointer; transition:background 0.2s;
            " onmouseover="this.style.background='#333'"
               onmouseout="this.style.background='#111'">
                თანხმობა
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Cookie consent ──
(function() {
    var consent = localStorage.getItem('ks_cookie_consent');
    if (consent === 'true') {
        loadGA();
    } else if (consent === null) {
        document.getElementById('cookieBanner').style.display = 'block';
    }
})();

function cookieChoice(accepted) {
    localStorage.setItem('ks_cookie_consent', accepted ? 'true' : 'false');
    document.getElementById('cookieBanner').style.display = 'none';
    if (accepted) loadGA();
}

function loadGA() {
    var GA_ID = 'G-4KBZMW9QQ3';
    if (!GA_ID) return;
    var s = document.createElement('script');
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_ID;
    s.async = true;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', GA_ID, { anonymize_ip: true });
}

// ── PWA install prompt ──
let _pwaPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    _pwaPrompt = e;
    const btn = document.getElementById('nativeInstallBtn');
    if (btn) btn.style.display = 'flex';
});
function openPwaModal() {
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    switchTab(isIos ? 'ios' : 'android');
    document.getElementById('pwaOverlay').classList.add('open');
}
function closePwaModal() {
    document.getElementById('pwaOverlay').classList.remove('open');
}
function switchTab(tab) {
    document.getElementById('stepsIos').style.display     = tab === 'ios'     ? 'flex' : 'none';
    document.getElementById('stepsAndroid').style.display = tab === 'android' ? 'flex' : 'none';
    document.getElementById('tabIos').classList.toggle('active',     tab === 'ios');
    document.getElementById('tabAndroid').classList.toggle('active', tab === 'android');
}
function triggerNativeInstall() {
    if (!_pwaPrompt) return;
    _pwaPrompt.prompt();
    _pwaPrompt.userChoice.then(() => { _pwaPrompt = null; closePwaModal(); });
}

// ── Page loader ──
window.addEventListener('load', () => {
    const pl = document.getElementById('page-loader');
    if (!pl) return;
    pl.classList.add('fade-out');
    setTimeout(() => pl.remove(), 300);
});

// ── App loader ──
window.AppLoader = {
    show(text = 'Loading…') {
        const loader = document.getElementById('app-loader');
        if (!loader) return;
        loader.querySelector('.loader-text').innerText = text;
        loader.classList.remove('hidden');
    },
    hide() {
        const loader = document.getElementById('app-loader');
        if (loader) loader.classList.add('hidden');
    }
};

document.addEventListener('click', e => {
    const link = e.target.closest('a[data-loader]');
    if (link) AppLoader.show(link.dataset.loaderText || 'Loading…');
});
</script>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(reg => {
        @auth
        const vapidKey = '{{ config("services.vapid.public_key") }}';

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw = atob(base64);
            return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
        }

        window._swReg = reg;

        function updateNotifUI(granted) {
            const iconD = document.getElementById('notif-icon-desktop');
            if (iconD) { iconD.className = granted ? 'bi bi-bell-fill' : 'bi bi-bell'; iconD.style.color = granted ? '#4ade80' : ''; }
            const iconM = document.getElementById('notif-icon-mobile');
            if (iconM) { iconM.className = granted ? 'bi bi-bell-fill' : 'bi bi-bell'; iconM.style.color = granted ? '#4ade80' : ''; }
            const dashBtn  = document.getElementById('notifBtn');
            const dashIcon = document.getElementById('notifIcon');
            const dashText = document.getElementById('notifText');
            if (dashIcon) dashIcon.className = granted ? 'bi bi-bell-fill' : 'bi bi-bell';
            if (dashText) dashText.textContent = granted ? 'შეტყობინებები: ჩართულია' : 'შეტყობინებების ჩართვა';
            if (dashBtn)  dashBtn.classList.toggle('on', granted);
            if (window._notifToast) {
                window._notifToast = false;
                var t = document.getElementById('ks-notif-toast');
                if (!t) {
                    t = document.createElement('div');
                    t.id = 'ks-notif-toast';
                    t.style.cssText = 'position:fixed;bottom:88px;left:50%;transform:translateX(-50%) translateY(8px);background:#1a1a1a;color:#fff;padding:9px 20px;border-radius:10px;font-family:"Nunito",sans-serif;font-size:0.85rem;font-weight:700;z-index:99999;transition:opacity 0.3s,transform 0.3s;pointer-events:none;white-space:nowrap;opacity:0;';
                    document.body.appendChild(t);
                }
                t.textContent = granted ? '🔔 შეტყობინებები ჩართულია' : '🔕 შეტყობინებები გამორთულია';
                t.style.opacity = '1';
                t.style.transform = 'translateX(-50%) translateY(0)';
                clearTimeout(t._tid);
                t._tid = setTimeout(function() { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(8px)'; }, 2500);
            }
        }

        const notifSupported = (typeof Notification !== 'undefined') && ('pushManager' in reg);

        if (notifSupported) {
            reg.pushManager.getSubscription().then(sub => {
                updateNotifUI(!!sub && Notification.permission === 'granted');
            });
        }

        window.toggleNotifications = function() {
            console.log('[KS] toggleNotifications called. notifSupported=', notifSupported, 'permission=', typeof Notification !== 'undefined' ? Notification.permission : 'N/A', '_swReg=', window._swReg);
            if (!notifSupported) {
                const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
                if (isIos && !isStandalone && typeof openPwaModal === 'function') openPwaModal();
                return;
            }
            if (Notification.permission === 'granted') {
                window._swReg.pushManager.getSubscription().then(sub => {
                    if (sub) {
                        sub.unsubscribe().then(() => {
                            fetch('{{ route("push.unsubscribe") }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ endpoint: sub.endpoint }),
                            });
                            updateNotifUI(false);
                        });
                    } else {
                        window.enablePushNotifications();
                    }
                });
            } else {
                window.enablePushNotifications();
            }
        };

        window.enablePushNotifications = function() {
            console.log('[KS] enablePush. _swReg=', window._swReg, 'notifSupported=', notifSupported);
            if (!window._swReg || !notifSupported) return;
            Notification.requestPermission().then(perm => {
                console.log('[KS] permission result:', perm);
                if (perm === 'denied') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'შეტყობინებები დაბლოკილია', text: 'ბრაუზერის მისამართ ბარში 🔒 → Notifications → Allow', confirmButtonText: 'გასაგებია' });
                    }
                    return;
                }
                if (perm !== 'granted') return;
                window._swReg.pushManager.getSubscription().then(existing => {
                    if (existing) { updateNotifUI(true); return; }
                    window._swReg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey),
                    }).then(sub => {
                        fetch('{{ route("push.subscribe") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(sub),
                        });
                        updateNotifUI(true);
                    }).catch(err => console.warn('Push subscribe failed:', err));
                });
            });
        };
        @endauth
    }).catch(err => console.error('SW error:', err));
}
</script>
@yield('scripts')
</body>
</html>
