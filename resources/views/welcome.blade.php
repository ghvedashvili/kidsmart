@extends('layouts.app')

@section('content')
<style>
    nav.fixed-top { display: none !important; }
    body {
        margin: 0; padding: 0 !important; overflow-x: hidden;
        background: #ffffff;
        background-image: radial-gradient(rgba(0,0,0,0.07) 1.5px, transparent 1.5px);
        background-size: 22px 22px;
        min-height: 100dvh;
    }

    .hero {
        min-height: 100dvh;
        display: flex; align-items: center; justify-content: center;
        padding: 32px 20px 48px;
    }

    .hero-inner {
        display: flex; flex-direction: column; align-items: center;
        text-align: center; width: 100%; max-width: 420px; gap: 24px;
    }

    .logo-wrap img {
        width: clamp(200px, 58vw, 300px); height: auto;
        filter: drop-shadow(0 8px 24px rgba(0,0,0,0.10));
    }

    .tagline {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(1rem, 3.5vw, 1.15rem);
        color: #aaa; letter-spacing: 0.04em;
        margin-top: -10px;
    }

    /* Role cards */
    .role-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; width: 100%; }
    .role-card {
        border: none; border-radius: 20px; padding: 32px 14px;
        cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
        display: flex; align-items: center; justify-content: center;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    }
    .role-card:hover  { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.11); }
    .role-card.active { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.13); }
    .role-card-parent { border-top: 5px solid #4a90d9; }
    .role-card-child  { border-top: 5px solid #ff4b7d; }
    .role-card.active.role-card-parent { background: #eef6ff; }
    .role-card.active.role-card-child  { background: #fff0f4; }
    .role-label {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(1rem, 4vw, 1.2rem);
        letter-spacing: 0.04em;
    }
    .role-card-parent .role-label { color: #4a90d9; }
    .role-card-child  .role-label { color: #ff4b7d; }

    /* Panels */
    .auth-panel { width: 100%; display: none; flex-direction: column; align-items: center; gap: 12px; }
    .auth-panel.visible { display: flex; }

    /* Google button */
    .google-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 12px;
        padding: 14px 32px; width: 100%;
        font-family: 'Fredoka One', cursive;
        font-size: clamp(0.9rem, 2.5vw, 1rem); letter-spacing: 0.06em;
        color: #fff; background: #4a90d9; border: none; border-radius: 14px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(74,144,217,0.35);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .google-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(74,144,217,0.45); color: #fff; }

    /* Child form */
    .child-form { display: flex; flex-direction: column; gap: 10px; width: 100%; }
    .child-input {
        background: #fff; border: 2.5px solid #ffc0d0; border-radius: 14px;
        color: #333; font-family: 'Fredoka One', cursive; font-size: 1.3rem;
        padding: 13px 20px; outline: none; width: 100%;
        letter-spacing: 0.18em; text-align: center; box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .child-input:focus { border-color: #ff4b7d; }
    .child-input::placeholder { color: #ffb3c6; letter-spacing: 0.06em; font-size: 1rem; }
    .child-submit {
        background: #ff4b7d; border: none; border-radius: 14px;
        color: #fff; font-family: 'Fredoka One', cursive; font-size: 1rem;
        letter-spacing: 0.06em; padding: 14px; cursor: pointer; width: 100%;
        box-shadow: 0 4px 16px rgba(255,75,125,0.35);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .child-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,75,125,0.45); }
    .err-msg { font-family: 'Fredoka One', cursive; font-size: 0.85rem; color: #ff4b7d; }
</style>

<div class="hero">
    <div class="hero-inner">
        <div class="logo-wrap">
            <img src="/img/logo.png" alt="KidSmart">
        </div>
        <div class="tagline">სახალისო მათემატიკა 🎉</div>

        <div class="role-cards">
            <button class="role-card role-card-parent" id="cardParent" onclick="showPanel('parent')">
                <div class="role-label">მშობელი</div>
            </button>
            <button class="role-card role-card-child" id="cardChild" onclick="showPanel('child')">
                <div class="role-label">ბავშვი</div>
            </button>
        </div>

        {{-- Parent panel --}}
        <div class="auth-panel" id="panelParent">
            <a href="{{ route('google.login') }}" class="google-btn" data-loader data-loader-text="შესვლა...">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#fff"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#fff"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#fff"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#fff"/>
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
@if($errors->any())
showPanel('child');
@endif
</script>
@endsection
