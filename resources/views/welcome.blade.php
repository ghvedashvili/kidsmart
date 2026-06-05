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

@php $googleUrl = route('google.login'); @endphp

<style>
    body {
        margin: 0;
        padding: 0 !important;
        overflow: hidden;
        background: #080808;
        height: 100dvh;
        height: 100vh;
    }

    .hero {
        height: 100dvh;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: clamp(12px, 3vh, 24px);
        position: relative;
        overflow: hidden;
    }

    /* moving dot grid */
    .hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(255,255,255,0.13) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }

    @keyframes gridMove {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(28px, 28px); }
    }


    .hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: clamp(10px, 2.5vh, 20px);
        text-align: center;
        padding: 0 20px;
    }

    .title {
        font-family: 'Goldman', monospace;
        font-size: clamp(1.1rem, 5.2vw, 4rem);
        font-weight: normal;
        letter-spacing: clamp(0.05em, 0.6vw, 0.2em);
        color: #c8c8c8;
        filter: drop-shadow(0 0 0.4em rgba(200,200,200,0.25));
        min-height: 1.3em;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dud {
        color: rgba(255, 50, 50, 0.9);
    }

    .subtitle {
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 3vw, 1.6rem);
        color: #555;
        letter-spacing: 0.15em;
        text-transform: lowercase;
        min-height: 1.4em;
    }

</style>

<style>
    .pwa-fab {
        position: fixed;
        bottom: 28px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s ease, transform 0.4s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 22px;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 100px;
        color: rgba(255,255,255,0.7);
        font-family: 'Goldman', monospace;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        cursor: pointer;
        backdrop-filter: blur(8px);
        white-space: nowrap;
        z-index: 10;
    }
    .pwa-fab.visible {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }
    .pwa-fab:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .pwa-fab svg { flex-shrink: 0; }
</style>

<div class="hero">
    <div class="hero-inner">
        <div class="title" id="titleEl"></div>
        <div class="subtitle" id="subtitleEl"></div>
    </div>
</div>

{{-- PWA Install FAB --}}
<button class="pwa-fab" id="pwaFab" onclick="openPwaModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2v13M8 9l4-4 4 4"/><path d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/>
    </svg>
    App-ის დაყენება
</button>

<script>
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (!isAlreadyInstalled) {
        setTimeout(() => {
            const fab = document.getElementById('pwaFab');
            if (fab) fab.classList.add('visible');
        }, 5000);
    }
</script>

<script>
    class TextScramble {
        constructor(el) {
            this.el = el;
        }
        setText(newText, symbolSets) {
            const oldText = this.el.innerText;
            const length  = Math.max(oldText.length, newText.length);
            const promise = new Promise(resolve => this.resolve = resolve);
            const now = performance.now();
            this.queue = [];
            for (let i = 0; i < length; i++) {
                const syms = Array.isArray(symbolSets)
                    ? symbolSets[i % symbolSets.length]
                    : symbolSets;
                const startAt  = now + i * 70 + Math.random() * 20;
                const settleAt = startAt + 520 + Math.random() * 200;
                this.queue.push({ from: oldText[i] || '', to: newText[i] || '', startAt, settleAt, syms, char: '', lastSwap: 0 });
            }
            cancelAnimationFrame(this.frameRequest);
            this.frameRequest = requestAnimationFrame(t => this.update(t));
            return promise;
        }
        update(now) {
            let output = '', complete = 0;
            for (let i = 0; i < this.queue.length; i++) {
                const q = this.queue[i];
                if (now >= q.settleAt) {
                    complete++;
                    output += q.to;
                } else if (now >= q.startAt) {
                    if (!q.char || now - q.lastSwap > 90) {
                        q.char     = q.syms[Math.floor(Math.random() * q.syms.length)];
                        q.lastSwap = now;
                    }
                    output += `<span class="dud">${q.char}</span>`;
                } else {
                    output += q.from;
                }
            }
            this.el.innerHTML = output;
            if (complete === this.queue.length) {
                this.resolve();
            } else {
                this.frameRequest = requestAnimationFrame(t => this.update(t));
            }
        }
    }

    const symSets = [
        '⠁⠂⠃⠄⠅⠆⠇⠈⠉⠊⠋⠌⠍⠎⠏⠐⠑⠒⠓⠔⠕⠖⠗⠘⠙⠚⠛⠜⠝⠞⠟⠠⠡⠢⠣⠤⠥⠦⠧⠨⠩⠪⠫⠬⠭⠮⠯',
        '♠♣♥♦♤♧♡♢',
        '♔♕♖♗♘♙♚♛♜♝♞♟',
        '•-·−',
        '±×÷≈≠≤≥∞√∆∂∫∑∏∈∉',
        'ᚠᚢᚦᚨᚱᚲᚷᚹᚺᚾᛁᛃᛇᛈᛉᛋᛏᛒᛖᛗᛚᛜᛞ',
        'あいうえおかきくけこさしすせそアイウエオカキクケコ',
        '←↑→↓↔↕⇐⇑⇒⇓⇔➔➜➤➝',
    ];

    const scrambler  = new TextScramble(document.getElementById('titleEl'));
    const scrambler2 = new TextScramble(document.getElementById('subtitleEl'));
    const subSyms    = '⠁⠂⠃♠♣♥♔♕•-±×ᚠᚢあいう←↑→'.split('');

    function animate() {
        scrambler2.setText('Ghvedashvili presents...', subSyms).then(() => {
            setTimeout(() => scrambler.setText('VERAVART GAME', symSets), 2000);
        });
    }

    setTimeout(animate, 400);
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
