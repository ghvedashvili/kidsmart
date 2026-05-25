@extends('layouts.app')

@section('content')
@auth

@if(auth()->user()->level == 1)

<style>
    @import url('https://fonts.googleapis.com/css2?family=Goldman&display=swap');
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
            სათამაშოდ პირველ რიგში<br>
            საჭიროა <strong>ნიქნეიმის</strong> შექმნა.
        </p>
        <a href="{{ route('levels.show', 1) }}" class="onboard-btn" data-loader data-loader-text="Loading...">
            ნიქნეიმის შექმნა →
        </a>
    </div>
</div>

@else

<div class="container text-center mt-5">
    <h3>გამარჯობა, {{ auth()->user()->nickname }} 👋</h3>
    <a href="{{ route('levels.show', auth()->user()->level) }}"
       class="btn btn-primary mt-3 swal-loader">
        ▶ გაგრძელება
    </a>
</div>

@endif

@else

@php $googleUrl = route('google.login'); @endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Goldman&display=swap');

    nav.navbar { display: none !important; }

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

    .enter-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: clamp(10px, 2vh, 14px) clamp(22px, 5vw, 36px);
        color: #aaa;
        font-family: 'Goldman', monospace;
        font-size: clamp(0.75rem, 2.2vw, 0.9rem);
        letter-spacing: 0.1em;
        text-decoration: none;
        border: 1px solid #333;
        border-radius: 3px;
        background: transparent;
        transition: color 0.2s, border-color 0.2s, opacity 0.3s;
        margin-top: clamp(4px, 1vh, 10px);
        opacity: 0;
        pointer-events: none;
    }
    .enter-btn.visible {
        opacity: 1;
        pointer-events: auto;
    }
    .enter-btn:hover {
        color: #ddd;
        border-color: #666;
    }
    .enter-btn img { width: 17px; opacity: 0.7; }
</style>

<div class="hero">
    <div class="hero-inner">
        <div class="title" id="titleEl"></div>
        <div class="subtitle" id="subtitleEl"></div>
        <a href="{{ $googleUrl }}"
           class="enter-btn"
           id="enterBtn"
           data-loader
           data-loader-text="შესვლა...">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="G">
            Sign in with Google
        </a>
    </div>
</div>

<script>
    class TextScramble {
        constructor(el) {
            this.el = el;
            this.update = this.update.bind(this);
        }
        setText(newText, symbolSets) {
            const oldText = this.el.innerText;
            const length  = Math.max(oldText.length, newText.length);
            const promise = new Promise(resolve => this.resolve = resolve);
            this.queue = [];
            for (let i = 0; i < length; i++) {
                const syms = Array.isArray(symbolSets)
                    ? symbolSets[i % symbolSets.length]
                    : symbolSets;
                this.queue.push({
                    from:  oldText[i] || '',
                    to:    newText[i] || '',
                    start: Math.floor(Math.random() * 90),
                    end:   Math.floor(Math.random() * 90) + Math.floor(Math.random() * 90) + 40,
                    syms
                });
            }
            cancelAnimationFrame(this.frameRequest);
            this.frame = 0;
            this.update();
            return promise;
        }
        update() {
            let output = '', complete = 0;
            for (let i = 0; i < this.queue.length; i++) {
                let { from, to, start, end, char, syms } = this.queue[i];
                if (this.frame >= end) {
                    complete++;
                    output += to;
                } else if (this.frame >= start) {
                    if (!char || Math.random() < 0.28) {
                        char = syms[Math.floor(Math.random() * syms.length)];
                        this.queue[i].char = char;
                    }
                    output += `<span class="dud">${char}</span>`;
                } else {
                    output += from;
                }
            }
            this.el.innerHTML = output;
            if (complete === this.queue.length) {
                this.resolve();
            } else {
                this.frameRequest = requestAnimationFrame(this.update);
                this.frame++;
            }
        }
    }

    const symSets = [
        '⠁⠂⠃⠄⠅⠆⠇⠈⠉⠊⠋⠌⠍⠎⠏⠐⠑⠒⠓⠔⠕⠖⠗⠘⠙⠚⠛⠜⠝⠞⠟⠠⠡⠢⠣⠤⠥⠦⠧⠨⠩⠪⠫⠬⠭⠮⠯',  // V — braille
        '♠♣♥♦♤♧♡♢',                                                             // E — cards
        '♔♕♖♗♘♙♚♛♜♝♞♟',                                                         // R — chess
        '•-·−',                                                                  // A — morse
        '±×÷≈≠≤≥∞√∆∂∫∑∏∈∉',                                                     // V — math
        'ᚠᚢᚦᚨᚱᚲᚷᚹᚺᚾᛁᛃᛇᛈᛉᛋᛏᛒᛖᛗᛚᛜᛞ',                                             // A — runes
        'あいうえおかきくけこさしすせそアイウエオカキクケコ',                        // R — asian
        '←↑→↓↔↕⇐⇑⇒⇓⇔➔➜➤➝',                                                     // T — arrows
    ];

    const scrambler  = new TextScramble(document.getElementById('titleEl'));
    const scrambler2 = new TextScramble(document.getElementById('subtitleEl'));
    const subSyms    = '⠁⠂⠃♠♣♥♔♕•-±×ᚠᚢあいう←↑→'.split('');
    const enterBtn   = document.getElementById('enterBtn');

    function animate() {
        enterBtn.classList.remove('visible');
        scrambler2.setText('', subSyms);
        scrambler.setText('VERAVART GAME', symSets).then(() => {
            scrambler2.setText('by ghvedashvili', subSyms).then(() => {
                setTimeout(() => enterBtn.classList.add('visible'), 2500);
                setTimeout(animate, 15000);
            });
        });
    }

    setTimeout(animate, 600);
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
