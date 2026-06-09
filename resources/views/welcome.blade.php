@extends('layouts.app')

@section('content')
<style>
    nav.fixed-top { display: none !important; }
    body {
        margin: 0; padding: 0 !important; overflow: hidden;
        background: #080808;
        height: 100dvh; height: 100vh;
    }
    .hero {
        height: 100dvh; height: 100vh;
        display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute; inset: -100%;
        background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 20s linear infinite;
        pointer-events: none; z-index: 1;
    }
    @keyframes gridMove {
        0%   { transform: translate(0,0); }
        100% { transform: translate(28px,28px); }
    }
    .hero-inner {
        position: relative; z-index: 2;
        display: flex; flex-direction: column; align-items: center;
        text-align: center; padding: 0 24px; width: 100%; max-width: 480px;
        gap: 28px;
    }
    .hero-title {
        font-family: 'Goldman', monospace;
        font-size: clamp(1.6rem, 7vw, 3rem);
        color: #d0d0d0; letter-spacing: 0.08em;
    }
    .hero-sub {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.7rem, 2vw, 0.85rem);
        color: #444; letter-spacing: 0.1em;
    }

    /* Role cards */
    .role-cards {
        display: grid; grid-template-columns: 1fr 1fr; gap: 14px; width: 100%;
    }
    .role-card {
        background: none; border: 1px solid #1e1e1e; border-radius: 10px;
        padding: 28px 16px; cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
        display: flex; flex-direction: column; align-items: center; gap: 12px;
    }
    .role-card:hover { border-color: #444; background: rgba(255,255,255,0.03); }
    .role-card.active { border-color: #555; background: rgba(255,255,255,0.05); }
    .role-icon { font-size: 2.2rem; }
    .role-label {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.72rem, 2vw, 0.85rem);
        color: #555; letter-spacing: 0.1em; text-transform: uppercase;
        transition: color 0.2s;
    }
    .role-card:hover .role-label, .role-card.active .role-label { color: #bbb; }

    /* Panels */
    .auth-panel { width: 100%; display: none; flex-direction: column; align-items: center; gap: 14px; }
    .auth-panel.visible { display: flex; }

    /* Google button */
    .google-btn {
        display: inline-flex; align-items: center; gap: 12px;
        padding: 13px 40px;
        font-family: 'Goldman', monospace;
        font-size: clamp(0.8rem, 2.2vw, 0.95rem);
        letter-spacing: 0.12em; text-transform: uppercase;
        color: #888; border: 1px solid #2a2a2a; border-radius: 4px;
        text-decoration: none;
        transition: color 0.2s, border-color 0.2s;
    }
    .google-btn:hover { color: #ccc; border-color: #555; }

    /* Child form */
    .child-form { display: flex; flex-direction: column; gap: 10px; width: 100%; }
    .child-input {
        background: rgba(255,255,255,0.04); border: 1px solid #2a2a2a; border-radius: 5px;
        color: #ccc; font-family: 'Goldman', monospace; font-size: 0.85rem;
        padding: 11px 16px; outline: none; width: 100%;
        transition: border-color 0.2s; text-align: center; letter-spacing: 0.05em;
        box-sizing: border-box;
    }
    .child-input:focus { border-color: #444; }
    .child-input::placeholder { color: #3a3a3a; }
    .child-submit {
        background: none; border: 1px solid #2a2a2a; border-radius: 5px;
        color: #777; font-family: 'Goldman', monospace; font-size: 0.82rem;
        letter-spacing: 0.1em; padding: 11px; cursor: pointer;
        transition: color 0.2s, border-color 0.2s;
    }
    .child-submit:hover { color: #ccc; border-color: #555; }
    .err-msg { color: #e74c3c; font-family: 'Goldman', monospace; font-size: 0.72rem; letter-spacing: 0.05em; }
</style>

<div class="hero">
    <div class="hero-inner">
        <div>
            <img src="/img/logo.png" alt="KidSmart"
                 style="width:clamp(180px,55vw,280px);height:auto;display:block;margin:0 auto 4px;">
            <div class="hero-sub">ვინ ხარ?</div>
        </div>

        <div class="role-cards">
            <button class="role-card" id="cardParent" onclick="showPanel('parent')">
                <div class="role-icon">👨‍👩‍👧</div>
                <div class="role-label">მშობელი</div>
            </button>
            <button class="role-card" id="cardChild" onclick="showPanel('child')">
                <div class="role-icon">🧒</div>
                <div class="role-label">ბავშვი</div>
            </button>
        </div>

        {{-- Parent panel --}}
        <div class="auth-panel" id="panelParent">
            <a href="{{ route('google.login') }}" class="google-btn" data-loader data-loader-text="შესვლა...">
                <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google-ით შესვლა
            </a>
        </div>

        {{-- Child panel --}}
        <div class="auth-panel" id="panelChild">
            <form method="POST" action="{{ route('child-login') }}" class="child-form">
                @csrf
                @error('child_code')
                    <div class="err-msg">{{ $message }}</div>
                @enderror
                <input type="text" name="child_code" class="child-input"
                    placeholder="შენი კოდი"
                    value="{{ old('child_code') }}"
                    maxlength="8" autocomplete="off"
                    style="font-size:1.3rem;letter-spacing:0.2em;text-align:center;"
                    oninput="this.value = this.value.toUpperCase()">
                <button type="submit" class="child-submit">შესვლა →</button>
            </form>
        </div>
    </div>
</div>

<script>
function showPanel(role) {
    ['parent','child'].forEach(r => {
        document.getElementById('card' + r.charAt(0).toUpperCase() + r.slice(1)).classList.toggle('active', r === role);
        document.getElementById('panel' + r.charAt(0).toUpperCase() + r.slice(1)).classList.toggle('visible', r === role);
    });
}

// თუ validation error მოვიდა → ბავშვის პანელი გავხსნათ
@if($errors->any())
showPanel('child');
@endif
</script>
@endsection
