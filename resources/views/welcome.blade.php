@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
    nav.fixed-top { display: none !important; }
    body {
        margin: 0; padding: 0 !important;
        overflow-x: clip;
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
    .role-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%; }
    .role-card {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 13px 10px;
        font-family: 'Goldman', monospace; font-size: 0.82rem; letter-spacing: 0.08em;
        background: transparent; border: 1px solid #ddd; border-radius: 4px;
        cursor: pointer; transition: color 0.2s, border-color 0.2s;
        color: #888;
    }
    .role-card:hover { border-color: #aaa; color: #333; }
    .role-card.active { border-color: #111; color: #111; }

    /* Panels */
    .auth-panel { width: 100%; display: none; flex-direction: column; align-items: center; gap: 12px; }
    .auth-panel.visible { display: flex; }

    /* Google button */
    .google-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        padding: 13px 28px; width: 100%;
        font-family: 'Goldman', monospace; font-size: 0.82rem; letter-spacing: 0.08em;
        color: #fff; background: #111; border: none; border-radius: 4px;
        text-decoration: none; cursor: pointer;
        transition: background 0.2s;
    }
    .google-btn:hover { background: #333; color: #fff; }

    /* Child form */
    .child-form { display: flex; flex-direction: column; gap: 10px; width: 100%; }
    .child-input {
        background: #fff; border: 1px solid #ddd; border-radius: 4px;
        color: #333; font-family: 'Goldman', monospace; font-size: 1rem;
        padding: 13px 16px; outline: none; width: 100%;
        letter-spacing: 0.18em; text-align: center; box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .child-input:focus { border-color: #aaa; }
    .child-input::placeholder { color: #bbb; letter-spacing: 0.06em; font-size: 0.82rem; }
    .child-submit {
        background: #111; border: none; border-radius: 4px;
        color: #fff; font-family: 'Goldman', monospace; font-size: 0.82rem;
        letter-spacing: 0.08em; padding: 13px; cursor: pointer; width: 100%;
        transition: background 0.2s;
    }
    .child-submit:hover { background: #333; }
    .err-msg { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #e74c3c; }

    /* ── Content section ── */
    .content-section {
        width: 100%; max-width: 480px; margin: 0 auto;
        padding: 0 20px 80px;
        display: flex; flex-direction: column; gap: 48px;
    }

    /* Stats row */
    .stats-row {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
        text-align: center;
    }
    .stat-item { display: flex; flex-direction: column; gap: 4px; }
    .stat-num {
        font-family: 'Goldman', monospace; font-size: clamp(1.6rem, 6vw, 2rem);
        color: #111; letter-spacing: -0.02em;
    }
    .stat-lbl {
        font-family: 'Goldman', monospace; font-size: 0.62rem;
        color: #aaa; letter-spacing: 0.1em; text-transform: uppercase;
    }

    /* Feature cards */
    .feat-grid { display: flex; flex-direction: column; gap: 10px; }
    .feat-card {
        display: flex; align-items: flex-start; gap: 14px;
        border: 1px solid #e8e8e8; border-radius: 4px; padding: 16px 18px;
        background: #fff;
    }
    .feat-icon { font-size: 1.3rem; flex-shrink: 0; margin-top: 2px; }
    .feat-title {
        font-family: 'Goldman', monospace; font-size: 0.78rem;
        color: #111; letter-spacing: 0.06em; margin-bottom: 4px;
    }
    .feat-desc {
        font-family: 'Goldman', monospace; font-size: 0.68rem;
        color: #888; letter-spacing: 0.04em; line-height: 1.6;
    }

    /* Operations */
    .ops-title {
        font-family: 'Goldman', monospace; font-size: 0.62rem;
        color: #aaa; letter-spacing: 0.14em; text-transform: uppercase;
        margin-bottom: 10px;
    }
    .ops-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .op-card {
        border: 1px solid #e8e8e8; border-radius: 4px; padding: 14px 16px;
        background: #fff; display: flex; flex-direction: column; gap: 6px;
    }
    .op-sym {
        font-family: 'Goldman', monospace; font-size: 1.4rem; color: #111;
    }
    .op-name {
        font-family: 'Goldman', monospace; font-size: 0.68rem;
        color: #aaa; letter-spacing: 0.1em; text-transform: uppercase;
    }
    .op-ex {
        font-family: 'Goldman', monospace; font-size: 0.72rem;
        color: #555; letter-spacing: 0.06em;
    }

    /* divider */
    .sec-divider {
        width: 32px; height: 1px; background: #ddd; margin: 0 auto;
    }
</style>

<div class="hero">
    <div class="hero-inner">
        <div class="logo-wrap">
            <img src="/img/logo.png" alt="KidSmart">
        </div>
        <div class="tagline">სახალისო მათემატიკა 🎉</div>

        <div class="role-cards">
            <button class="role-card role-card-parent" id="cardParent" onclick="showPanel('parent')">მშობელი</button>
            <button class="role-card role-card-child" id="cardChild" onclick="showPanel('child')">ბავშვი</button>
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

{{-- Content cards section --}}
<div class="content-section">

    <div class="sec-divider"></div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-num">4</div>
            <div class="stat-lbl">ოპერაცია</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">5</div>
            <div class="stat-lbl">სირთულე</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">∞</div>
            <div class="stat-lbl">ამოცანა</div>
        </div>
    </div>

    <div class="sec-divider"></div>

    {{-- Feature cards --}}
    <div class="feat-grid">
        <div class="feat-card">
            <div class="feat-icon">🎯</div>
            <div>
                <div class="feat-title">პერსონალიზებული ტესტები</div>
                <div class="feat-desc">ყოველ ბავშვს საკუთარი სირთულე, თემა და დღიური დავალება. მშობელი თვითონ ირჩევს.</div>
            </div>
        </div>
        <div class="feat-card">
            <div class="feat-icon">📊</div>
            <div>
                <div class="feat-title">სტატისტიკა და პროგრესი</div>
                <div class="feat-desc">მშობელი ხედავს ყველა ტესტის შედეგს, სწორ და არასწორ პასუხებს.</div>
            </div>
        </div>
        <div class="feat-card">
            <div class="feat-icon">🔢</div>
            <div>
                <div class="feat-title">სახალისო ამოცანები</div>
                <div class="feat-desc">რიცხვებიანი ამბები — ფეხბურთი, საქონელი, ბაღი — მათემატიკა ცხოვრებიდან.</div>
            </div>
        </div>
        <div class="feat-card">
            <div class="feat-icon">🏆</div>
            <div>
                <div class="feat-title">ყოველდღიური ჩვევა</div>
                <div class="feat-desc">შეტყობინება შეახსენებს ტესტს. განსაზღვრე რამდენი ამოცანა დღეში.</div>
            </div>
        </div>
    </div>

    <div class="sec-divider"></div>

    {{-- Operations --}}
    <div>
        <div class="ops-title">მოქმედებები</div>
        <div class="ops-grid">
            <div class="op-card">
                <div class="op-sym">+</div>
                <div class="op-name">შეკრება</div>
                <div class="op-ex">347 + 285 = 632</div>
            </div>
            <div class="op-card">
                <div class="op-sym">−</div>
                <div class="op-name">გამოკლება</div>
                <div class="op-ex">523 − 178 = 345</div>
            </div>
            <div class="op-card">
                <div class="op-sym">×</div>
                <div class="op-name">გამრავლება</div>
                <div class="op-ex">47 × 23 = 1081</div>
            </div>
            <div class="op-card">
                <div class="op-sym">÷</div>
                <div class="op-name">გაყოფა</div>
                <div class="op-ex">84 ÷ 7 = 12</div>
            </div>
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
