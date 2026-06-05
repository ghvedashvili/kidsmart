@extends('layouts.app')

@section('content')
@guest
<style>
    body { padding-top: 0 !important; }
</style>
@endguest
@auth

@if(auth()->user()->level == 1)

<style>
    nav.navbar { display: none !important; }
    body {
        margin: 0;
        padding: 0 !important;
        overflow: hidden;
        height: 100dvh;
        height: 100vh;
        background: #f5f5f5;
    }

    .onboard-hero {
        height: 100dvh;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 24px;
    }

    .onboard-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.13) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }

    @keyframes gridMove {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(28px, 28px); }
    }

    .onboard-card {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 480px;
        width: 100%;
    }

    .onboard-title {
        font-family: 'Goldman', monospace;
        font-size: clamp(1.4rem, 5vw, 2.2rem);
        color: #111;
        margin-bottom: 16px;
        letter-spacing: 0.05em;
    }

    .onboard-text {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.75rem, 2.2vw, 0.95rem);
        color: #555;
        line-height: 1.8;
        margin-bottom: 32px;
        letter-spacing: 0.03em;
    }

    .onboard-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 36px;
        font-family: 'Goldman', monospace;
        font-size: clamp(0.8rem, 2.5vw, 1rem);
        letter-spacing: 0.08em;
        color: #f5f5f5;
        background: #111;
        border: none;
        border-radius: 3px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .onboard-btn:hover {
        background: #333;
        color: #fff;
    }
</style>

<div class="onboard-hero">
    <div class="onboard-card">
        <div class="onboard-title">გამარჯობა, {{ auth()->user()->name }}! 👋</div>
        <p class="onboard-text">
            გვიხარია, რომ შემოგვიერთდი.<br>
            თამაშის დასაწყებად აუცილებელი პირობაა რომ <br>
            საკუთარი <strong>nickname</strong> შექმნა.
        </p>
        <a href="{{ route('levels.show', 1) }}" class="onboard-btn" data-loader data-loader-text="Loading...">
            nickname-ის შექმნა →
        </a>
    </div>
</div>

@else

@php
    $user         = auth()->user();
    $totalPlayers = \App\Models\User::count();
    $myLevel      = $user->level;
    $maxLevel     = \App\Models\Question::max('level') ?? $myLevel;
    $playersAhead = \App\Models\User::where('level', '>', $myLevel)->count();
    $sameLevel    = \App\Models\User::where('level', $myLevel)->count();
    $myRank       = $playersAhead + 1;
@endphp

<style>
    nav.navbar { display: none !important; }
    body {
        margin: 0;
        padding: 0 !important;
        overflow: hidden;
        background: #f5f5f5 !important;
        min-height: 100dvh;
        min-height: 100vh;
    }
    body.dot-light::before { display: none; }

    .dash-hero {
        min-height: 100dvh;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 32px 24px;
        gap: 32px;
    }

    .dash-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.1) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }

    @keyframes gridMove {
        0%   { transform: translate(0,0); }
        100% { transform: translate(28px,28px); }
    }

    .dash-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 28px;
        width: 100%;
        max-width: 560px;
    }

    .dash-greeting {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.7rem, 3.5vw, 1.4rem);
        color: #111;
        letter-spacing: 0.06em;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .dash-level-badge {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.68rem, 2vw, 0.78rem);
        color: #888;
        letter-spacing: 0.08em;
        text-align: center;
        margin-top: 6px;
    }

    .dash-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        width: 100%;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 16px 10px;
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .stat-value {
        font-family: 'Goldman', monospace;
        font-size: clamp(1.2rem, 4vw, 1.8rem);
        color: #111;
        letter-spacing: 0.04em;
        line-height: 1;
    }

    .stat-label {
        font-family: 'Goldman', monospace;
        font-size: clamp(0.58rem, 1.8vw, 0.68rem);
        color: #999;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1.5;
    }

    .dash-continue {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 13px 36px;
        font-family: 'Goldman', monospace;
        font-size: clamp(0.75rem, 2.5vw, 0.85rem);
        letter-spacing: 0.1em;
        color: #333;
        background: transparent;
        border: 1px solid #ccc;
        border-radius: 3px;
        text-decoration: none;
        transition: color 0.2s, border-color 0.2s;
    }
    .dash-continue:hover { color: #000; border-color: #888; }
</style>

<div class="dash-hero">
    <div class="dash-inner">
        <div>
            <div class="dash-greeting">გამარჯობა, {{ $user->nickname }}</div>
            <div class="dash-level-badge">{{ $myLevel }}-ე ტური · #{{ $myRank }} ადგილი</div>
        </div>

        <div class="dash-stats">
            <div class="stat-card">
                <div class="stat-value">{{ $totalPlayers }}</div>
                <div class="stat-label">სულ<br>მოთამაშე</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $sameLevel }}</div>
                <div class="stat-label">შენს<br>ტურზე</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $playersAhead }}</div>
                <div class="stat-label">შენზე<br>წინ</div>
            </div>
        </div>

        <a href="{{ route('levels.show', $myLevel) }}" class="dash-continue" data-loader data-loader-text="Loading...">
            {{ $myLevel }}-ე ტური →
        </a>
    </div>
</div>

@endif

@else

@php
    $googleUrl   = route('google.login');
@endphp

<style>
    nav.fixed-top, #pull-bar { display: none !important; }

    body {
        margin: 0;
        padding: 0 !important;
        overflow: hidden;
        background: #080808;
        height: 100dvh; height: 100vh;
    }

    .hero {
        height: 100dvh; height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 20s linear infinite;
        pointer-events: none;
        z-index: 1;
    }
    @keyframes gridMove {
        0%   { transform: translate(0,0); }
        100% { transform: translate(28px,28px); }
    }

    .hero-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 32px;
        width: 100%;
    }

    /* Phase 1 — intro subtitle */
    .subtitle-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.05em;
    }
    .subtitle {
        font-family: 'Goldman', monospace;
        font-size: clamp(2rem, 8.5vw, 5rem);
        font-weight: normal;
        color: #444;
        letter-spacing: 0.04em;
        line-height: 1.05;
        opacity: 0;
        transition: opacity 0.9s ease;
        white-space: nowrap;
        overflow: hidden;
        max-width: 98vw;
    }

    /* Phase 2 — main logo */
    .logo-wrap {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: clamp(6px, 1.8vh, 18px);
    }

    .logo-veravart {
        font-family: 'Goldman', monospace;
        font-size: clamp(3rem, 13vw, 8rem);
        font-weight: normal;
        color: #d8d8d8;
        letter-spacing: 0.04em;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        max-width: 98vw;
    }

    .logo-game {
        font-family: 'Goldman', monospace;
        font-size: clamp(3rem, 13vw, 8rem);
        font-weight: normal;
        color: #3a3a3a;
        letter-spacing: 0.04em;
        min-height: 1.1em;
        white-space: nowrap;
        overflow: hidden;
        max-width: 98vw;
    }

    .dud { color: rgba(255, 50, 50, 0.75); }

    /* Phase 3 — sign in */
    .enter-btn {
        margin-top: clamp(24px, 4.5vh, 48px);
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 14px 44px;
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 2.5vw, 1.4rem);
        letter-spacing: 0.15em;
        color: #555;
        border: 1px solid #222;
        border-radius: 2px;
        text-decoration: none;
        text-transform: uppercase;
        transition: color 0.3s ease, border-color 0.3s ease, opacity 0.5s ease;
        opacity: 0;
        pointer-events: none;
    }
    .enter-btn.visible { opacity: 1; pointer-events: auto; }
    .enter-btn:hover   { color: #b8b8b8; border-color: #555; }

    .pwa-fab {
        position: fixed;
        bottom: 28px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        opacity: 0; pointer-events: none;
        transition: opacity 0.4s ease, transform 0.4s ease;
        display: flex; align-items: center; gap: 8px;
        padding: 10px 22px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 100px;
        color: rgba(255,255,255,0.45);
        font-family: 'Goldman', monospace;
        font-size: 0.7rem; letter-spacing: 0.08em;
        cursor: pointer; backdrop-filter: blur(8px);
        white-space: nowrap; z-index: 10;
    }
    .pwa-fab.visible { opacity:1; transform:translateX(-50%) translateY(0); pointer-events:auto; }
    .pwa-fab:hover   { background:rgba(255,255,255,0.09); color:rgba(255,255,255,0.75); }
</style>

<div class="hero">
    <div class="hero-inner">

        <div class="subtitle-wrap" id="subtitleWrap">
            <div class="subtitle" id="subtitleLine1"></div>
            <div class="subtitle" id="subtitleLine2"></div>
        </div>

        <div class="logo-wrap" id="logoWrap">
            <div class="logo-veravart" id="logoVeravart"></div>
            <div class="logo-game"     id="logoGame"></div>
            <a href="{{ $googleUrl }}" class="enter-btn" id="enterBtn" data-loader data-loader-text="შესვლა...">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                sign in
            </a>
        </div>

    </div>
</div>

<button class="pwa-fab" id="pwaFab" onclick="openPwaModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2v13M8 9l4-4 4 4"/><path d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/>
    </svg>
    App-ის დაყენება
</button>

<script>
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (!isAlreadyInstalled) {
        setTimeout(() => { const f=document.getElementById('pwaFab'); if(f) f.classList.add('visible'); }, 9000);
    }
</script>

<script>
/* ── TextScramble ── */
class TextScramble {
    constructor(el) { this.el = el; }
    setText(text, syms) {
        const old = this.el.innerText;
        const len = Math.max(old.length, text.length);
        const p   = new Promise(r => this.resolve = r);
        const now = performance.now();
        this.queue = [];
        for (let i = 0; i < len; i++) {
            const s = Array.isArray(syms) ? syms[i % syms.length] : syms;
            this.queue.push({
                from: old[i] || '', to: text[i] || '',
                start: now + i * 90 + Math.random() * 25,
                end:   now + i * 90 + 680 + Math.random() * 220,
                syms: s, ch: '', t: 0
            });
        }
        cancelAnimationFrame(this.raf);
        this.raf = requestAnimationFrame(t => this._tick(t));
        return p;
    }
    _tick(now) {
        let out = '', done = 0;
        for (const q of this.queue) {
            if (now >= q.end)        { done++; out += q.to; }
            else if (now >= q.start) {
                if (!q.ch || now - q.t > 110) { q.ch = q.syms[Math.floor(Math.random()*q.syms.length)]; q.t = now; }
                out += `<span class="dud">${q.ch}</span>`;
            } else { out += q.from; }
        }
        this.el.innerHTML = out;
        if (done === this.queue.length) this.resolve();
        else this.raf = requestAnimationFrame(t => this._tick(t));
    }
}

const symSets = [
    '⠁⠂⠃⠄⠅⠆⠇⠈⠉⠊⠋⠌⠍⠎⠏⠐⠑⠒⠓⠔⠕⠖⠗⠘⠙⠚⠛⠜⠝⠞⠟⠠⠡⠢⠣⠤⠥⠦⠧⠨⠩⠪⠫⠬⠭⠮⠯',
    '♠♣♥♦♤♧♡♢', '♔♕♖♗♘♙♚♛♜♝♞♟', '•-·−', '±×÷≈≠≤≥∞√∆∂∫∑∏∈∉',
    'ᚠᚢᚦᚨᚱᚲᚷᚹᚺᚾᛁᛃᛇᛈᛉᛋᛏᛒᛖᛗᛚᛜᛞ',
    'あいうえおかきくけこさしすせそアイウエオカキクケコ',
    '←↑→↓↔↕⇐⇑⇒⇓⇔➔➜➤➝',
];
const subSyms = '⠁⠂⠃♠♣♥♔♕•-±×ᚠᚢあいう←↑→'.split('');

const subtitleLine1 = document.getElementById('subtitleLine1');
const subtitleLine2 = document.getElementById('subtitleLine2');
const subtitleWrap  = document.getElementById('subtitleWrap');
const logoWrap      = document.getElementById('logoWrap');
const logoVeravart  = document.getElementById('logoVeravart');
const logoGame      = document.getElementById('logoGame');
const enterBtn      = document.getElementById('enterBtn');

const sLine1    = new TextScramble(subtitleLine1);
const sLine2    = new TextScramble(subtitleLine2);
const sVeravart = new TextScramble(logoVeravart);
const sGame     = new TextScramble(logoGame);

function animate() {
    subtitleLine1.style.opacity = '1';

    sLine1.setText('ghvedashvili', symSets).then(() => {
        subtitleLine2.style.opacity = '1';
        return sLine2.setText('presents', symSets);
    }).then(() => {
        setTimeout(() => {
            subtitleLine1.style.opacity = '0';
            subtitleLine2.style.opacity = '0';
            setTimeout(() => {
                subtitleWrap.style.display = 'none';
                logoWrap.style.display     = 'flex';
                sVeravart.setText('veravart', symSets).then(() => {
                    return sGame.setText('game', subSyms);
                }).then(() => {
                    setTimeout(() => enterBtn.classList.add('visible'), 1000);
                });
            }, 700);
        }, 1800);
    });
}

setTimeout(animate, 900);
</script>

@endauth

<script>
document.querySelectorAll('.swal-loader').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        Swal.fire({ allowOutsideClick:false, allowEscapeKey:false, background:'transparent', showConfirmButton:false, didOpen:()=>Swal.showLoading() });
        setTimeout(() => { window.location.href = this.href; }, 500);
    });
});
</script>
@endsection
