@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
:root {
    --green:  #1a7a3c;
    --lg:     #25a352;
    --yellow: #f9c913;
    --orange: #ff6b2b;
    --sky:    #e8f7ff;
    --dark:   #0d2818;
    --ink:    #0d1117;
    --muted:  #64748b;
    --bg2:    #f8fafc;
}
body {
    font-family: 'Nunito', sans-serif;
    background: transparent;
    padding: 0 !important;
    overflow-x: clip;
}

/* ── Hero ── */
.hero-mod {
    min-height: 88vh;
    background: linear-gradient(140deg, #0d2818 0%, #173d26 55%, #0b3020 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: 96px 24px 72px;
    position: relative; overflow: hidden;
}
.hero-mod::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse at 15% 65%, rgba(37,163,82,0.18) 0%, transparent 45%),
        radial-gradient(ellipse at 85% 25%, rgba(249,201,19,0.12) 0%, transparent 40%),
        radial-gradient(ellipse at 50% 95%, rgba(255,107,43,0.10) 0%, transparent 35%);
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.18);
    color: rgba(255,255,255,0.85);
    font-family: 'Fredoka One', cursive;
    font-size: 13px; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 6px 18px; border-radius: 99px;
    margin-bottom: 28px;
    backdrop-filter: blur(8px);
    position: relative; z-index: 1;
}
.hero-h1 {
    font-family: 'Fredoka One', cursive;
    font-size: clamp(2.4rem, 6vw, 4.4rem);
    line-height: 1.12; color: #fff;
    margin-bottom: 20px;
    position: relative; z-index: 1;
}
.hero-h1 em {
    font-style: normal;
    background: linear-gradient(130deg, var(--yellow) 0%, var(--orange) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-sub {
    font-size: clamp(1rem, 2.4vw, 1.2rem); font-weight: 600;
    color: rgba(255,255,255,0.65);
    max-width: 460px; margin: 0 auto 40px; line-height: 1.7;
    position: relative; z-index: 1;
}
.hero-btns {
    display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;
    position: relative; z-index: 1;
}
.btn-main {
    background: linear-gradient(135deg, var(--lg) 0%, var(--green) 100%);
    color: #fff; border: none; border-radius: 14px;
    font-family: 'Fredoka One', cursive; font-size: 1.1rem;
    padding: 14px 34px; cursor: pointer;
    box-shadow: 0 8px 24px rgba(26,122,60,0.5);
    transition: all 0.2s; text-decoration: none; display: inline-block;
}
.btn-main:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(26,122,60,0.6); color: #fff; }
.btn-ghost {
    background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.85);
    border: 1.5px solid rgba(255,255,255,0.22); border-radius: 14px;
    font-family: 'Fredoka One', cursive; font-size: 1.1rem;
    padding: 14px 34px; cursor: pointer;
    transition: all 0.2s; text-decoration: none; display: inline-block;
    backdrop-filter: blur(8px);
}
.btn-ghost:hover { background: rgba(255,255,255,0.14); color: #fff; }
.scroll-cue {
    position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
    color: rgba(255,255,255,0.3); font-size: 22px; z-index: 1;
    animation: cue 1.8s ease-in-out infinite;
}
@keyframes cue { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(8px)} }

/* ── Section layout ── */
.sec {
    padding: 80px 24px;
    max-width: 1100px; margin: 0 auto;
}
.sec-bg { background: rgba(248,250,252,0.82); }
.sec-bg .sec { max-width: 100%; padding: 80px max(24px, calc(50% - 530px)); }
.eyebrow {
    font-family: 'Fredoka One', cursive; font-size: 13px;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--green); margin-bottom: 8px;
}
.sec-h2 {
    font-family: 'Fredoka One', cursive;
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    color: var(--ink); margin-bottom: 12px; line-height: 1.2;
}
.sec-sub {
    font-size: 15px; color: var(--muted);
    max-width: 480px; margin-bottom: 36px; line-height: 1.7;
}

/* ── Drag carousel (shared) ── */
.dc {
    display: flex; gap: 16px;
    overflow-x: auto; scroll-snap-type: x mandatory;
    padding: 24px 4px 20px; scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    cursor: grab; user-select: none;
}
.dc::-webkit-scrollbar { display: none; }
.dc.dragging { cursor: grabbing; }
/* ── Question cards ── */
.car-outer { margin: 0 -24px; padding: 0 24px; overflow: hidden; }
.car-track { }

.qc {
    flex: 0 0 296px; scroll-snap-align: start;
    background: #fff; border-radius: 20px;
    padding: 28px 18px 18px; position: relative;
    box-shadow: 0 6px 24px rgba(26,122,60,0.12);
    border-top: 6px solid var(--green);
    display: flex; flex-direction: column; gap: 10px;
    min-height: 290px;
}
.qc.qc-y { border-top-color: #c89800; }
.qc.qc-o { border-top-color: var(--orange); }
.qc-badge {
    position: absolute; top: -14px; left: 18px;
    background: var(--green); color: #fff;
    font-family: 'Fredoka One', cursive; font-size: 0.88rem;
    padding: 3px 14px; border-radius: 99px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.qc.qc-y .qc-badge { background: #c89800; }
.qc.qc-o .qc-badge { background: var(--orange); }
.qc-icon { font-size: 1.8rem; display: block; }
.qc-text {
    font-family: 'Nunito', sans-serif; font-size: 1rem;
    font-weight: 800; color: var(--ink); line-height: 1.6; flex: 1;
}
.qc-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.qc-opt {
    display: flex; align-items: center; justify-content: center;
    background: #f0faf4; border: 2.5px solid #c5e8d0;
    border-radius: 12px; padding: 11px 8px;
    font-family: 'Fredoka One', cursive; font-size: 1rem;
    color: #3a7a50; cursor: pointer;
    transition: all 0.15s; text-align: center;
    border-style: solid;
}
.qc.qc-y .qc-opt { background: #fffbea; border-color: #f0d960; color: #7a6000; }
.qc.qc-o .qc-opt { background: #fff5f0; border-color: #ffc4a0; color: #7a3010; }
.qc-opt:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.qc-opt.correct { border-color: var(--green) !important; background: var(--green) !important; color: #fff !important; }
.qc-opt.wrong   { border-color: #ef4444 !important; background: #fee2e2 !important; color: #991b1b !important; }
.qc-opt.reveal  { border-color: var(--green) !important; background: var(--green) !important; color: #fff !important; }
.qc-fb { font-family: 'Fredoka One', cursive; font-size: 13px; min-height: 18px; color: var(--muted); }

/* ── Adaptive cards ── */
.adapt-grid { padding: 8px 4px 20px; }
.adapt-card {
    flex: 0 0 300px; scroll-snap-align: start;
    background: #fff; border-radius: 16px; padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border-left: 4px solid var(--green);
}
.adapt-t { font-family: 'Fredoka One', cursive; font-size: 1.2rem; color: var(--ink); margin-bottom: 8px; }
.adapt-b { font-size: 14px; color: var(--muted); line-height: 1.7; font-weight: 700; }

/* ── Detective ── */
.detect-grid { padding: 8px 4px 20px; }
.detect-card {
    flex: 0 0 340px; scroll-snap-align: start;
    background: #fff; border-radius: 16px; padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.hint-row {
    display: flex; gap: 8px; align-items: flex-start;
    padding: 8px 12px; border-radius: 10px; margin-bottom: 6px;
    font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 800;
}
.hint-vis  { background: #f0faf4; color: var(--ink); }
.hint-lock { background: #f8f8f8; color: #bbb; font-weight: 600; font-size: 13px; }

/* ── Market carousel ── */
.mkt-outer { overflow: hidden; }
.mkt-track {
    display: flex; gap: 16px;
    animation: mkt 22s linear infinite;
    width: max-content; padding: 4px 0 16px;
}
.mkt-outer:hover .mkt-track { animation-play-state: paused; }
@keyframes mkt { from{transform:translateX(0)} to{transform:translateX(-50%)} }
.mkt-card {
    flex: 0 0 162px;
    background: #fff; border-radius: 16px; padding: 20px 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07); text-align: center;
    transition: transform 0.15s;
}
.mkt-card:hover { transform: translateY(-4px); }
.mkt-ico   { font-size: 2.2rem; margin-bottom: 8px; }
.mkt-name  { font-family: 'Fredoka One', cursive; font-size: 14px; color: var(--ink); margin-bottom: 6px; line-height: 1.3; }
.mkt-price { font-family: 'Fredoka One', cursive; font-size: 20px; color: var(--green); }

/* ── Scroll reveal ── */
.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.in { opacity: 1; transform: translateY(0); }
.reveal:nth-child(2) { transition-delay: 0.1s; }
.reveal:nth-child(3) { transition-delay: 0.2s; }
.reveal:nth-child(4) { transition-delay: 0.3s; }

/* ── CTA ── */
.cta-wrap {
    background: linear-gradient(140deg, #0d2818 0%, #1a4a2e 100%);
    padding: 80px 24px; text-align: center;
}
.cta-h2 { font-family: 'Fredoka One', cursive; font-size: clamp(2rem,4vw,3rem); color: #fff; margin-bottom: 12px; line-height: 1.2; }
.cta-sub { font-size: 16px; color: rgba(255,255,255,0.65); margin-bottom: 32px; font-weight: 700; }

/* ── Footer ── */
.nb-footer {
    background: #0d1117;
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 32px;
}
.footer-logo { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #fff; }
.footer-copy { font-size: 12px; color: #444; }
</style>

{{-- ── Hero ── --}}
<section class="hero-mod">
    <div class="hero-badge">✏️ AI · ქართული · 6–14 წელი</div>
    <h1 class="hero-h1">
        ბავშვი სწავლობს იმ ენაზე,<br>
        <em>რომელიც მას უყვარს.</em>
    </h1>
    <p class="hero-sub">ჩვენ ვქმნით ამოცანებს ბავშვის სამყაროდან...</p>
    <div class="hero-btns">
        <button class="btn-main" onclick="document.getElementById('loginNavBtn')?.click()">
            დაიწყე ახლა ✏️
        </button>
        <a class="btn-ghost" href="#questions">სცადე ამოცანა ↓</a>
    </div>
    <div class="scroll-cue">↓</div>
</section>

{{-- ── Question cards carousel ── --}}
<div id="questions">
<div class="sec" style="padding-bottom:48px;">
    <div class="eyebrow reveal">სცადე ახლავე</div>
    <h2 class="sec-h2 reveal">ბავშვის სამყაროდან ამოცანები</h2>
    <p class="sec-sub reveal">ფეხბურთი, სუპერგმირები, კოსმოსი — ბავშვი ირჩევს, ჩვენ ვქმნით.</p>
    <div class="car-outer">
        <div class="car-track dc" id="qCar"></div>
    </div>
</div>
</div>

{{-- ── Adaptive ── --}}
<div class="sec-bg" id="adaptive">
<div class="sec">
    <div class="eyebrow reveal">ადაპტური სწავლება</div>
    <h2 class="sec-h2 reveal">ამოცანები ვიტარდებიან ბავშვთან ერთად.</h2>
    <p class="sec-sub reveal">ამოცანების 5 დონე, რომლებიც ავტომატურად ერგებიან ბავშვის დონეს.</p>
    <div class="adapt-grid dc">
        <div class="adapt-card reveal">
            <div class="adapt-t">3 ტესტი 95% და მეტი?</div>
            <p class="adapt-b">ავტომატურად შეიცვლება დონე — ბავშვს რომ არ მობეზრდეს.</p>
        </div>
        <div class="adapt-card reveal">
            <div class="adapt-t">ხშირად უშვებს შეცდომებს?</div>
            <p class="adapt-b">ამ თემაში შესაბამის დონის ამოცანებს ვთავაზობთ და ვავითარებთ ბავშვის უნარს.</p>
        </div>
    </div>
</div>
</div>

{{-- ── Math Detective ── --}}
<div class="sec" id="detective">
    <div class="eyebrow reveal">5 ტესტი · 5 მინიშნება</div>
    <h2 class="sec-h2 reveal">მათემატიკური დეტექტივი</h2>
    <p class="sec-sub reveal">გაიარე ტესტები და გაიგე საიდუმლო:</p>
    <div class="detect-grid dc">
        <div class="detect-card reveal">
            <div style="font-family:'Fredoka One',cursive;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#aaa;margin-bottom:10px;">🔍 გამოძიება #1</div>
            <div style="font-family:'Fredoka One',cursive;font-size:1.25rem;color:var(--ink);margin-bottom:14px;">ვინ მოიგო ოქროს ბურთი?</div>
            <div class="hint-row hint-vis"><span style="min-width:20px;color:var(--green);">1.</span><span>#7 ნომრით თამაშობს</span></div>
            <div class="hint-row hint-vis"><span style="min-width:20px;color:var(--green);">2.</span><span>ქართველია</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">3.</span><span>🔒 ჩაიტვირთება ტესტი 3-ის შემდეგ</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">4.</span><span>🔒 ჩაიტვირთება ტესტი 4-ის შემდეგ</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">5.</span><span>🔒 ჩაიტვირთება ტესტი 5-ის შემდეგ</span></div>
        </div>
        <div class="detect-card reveal">
            <div style="font-family:'Fredoka One',cursive;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#aaa;margin-bottom:10px;">🔍 გამოძიება #2</div>
            <div style="font-family:'Fredoka One',cursive;font-size:1.25rem;color:var(--ink);margin-bottom:10px;">რა საიდუმლო შეტყობინება მიიღეს სუპერგმირებმა?</div>
            <div style="font-family:'Fredoka One',cursive;font-size:1.1rem;color:var(--orange);letter-spacing:.04em;background:#fff5f0;border-left:4px solid var(--orange);padding:10px 14px;border-radius:0 10px 10px 0;margin-bottom:12px;line-height:1.6;">
                8-15-2-9-20-9-22-9<br>3-9-20-1-4-5-12-9-16-15
            </div>
            <div class="hint-row hint-vis"><span style="min-width:20px;color:var(--green);">1.</span><span>A=1, B=2, C=3 ...</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">2.</span><span>🔒 ჩაიტვირთება ტესტი 2-ის შემდეგ</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">3.</span><span>🔒 ჩაიტვირთება ტესტი 3-ის შემდეგ</span></div>
        </div>
    </div>
</div>

{{-- ── Parent Market ── --}}
<div class="sec-bg" id="market">
<div class="sec">
    <div class="eyebrow reveal">ჯილდოების სისტემა</div>
    <h2 class="sec-h2 reveal">მშობელი ადგენს მიზანს · ბავშვი ირჩევს გზას</h2>
    <p class="sec-sub reveal">მშობელი ქმნის საკუთარ მარკეტს სადაც აწესებს ჯილდოებს · ყველას თავისი ფასი აქვს.</p>
    <div class="mkt-outer">
        <div class="mkt-track">
            <div class="mkt-card"><div class="mkt-ico">🍕</div><div class="mkt-name">პიცა სახლში</div><div class="mkt-price">10 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎬</div><div class="mkt-name">კინოში წასვლა</div><div class="mkt-price">20 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎁</div><div class="mkt-name">სიურპრიზი</div><div class="mkt-price">50 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎮</div><div class="mkt-name">თამაში (2 სთ)</div><div class="mkt-price">15 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🍦</div><div class="mkt-name">ნაყინი</div><div class="mkt-price">5 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🏆</div><div class="mkt-name">სპეც. გასეირნება</div><div class="mkt-price">30 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">📚</div><div class="mkt-name">წიგნი საჩუქრად</div><div class="mkt-price">25 🪙</div></div>
            {{-- duplicate for seamless loop --}}
            <div class="mkt-card"><div class="mkt-ico">🍕</div><div class="mkt-name">პიცა სახლში</div><div class="mkt-price">10 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎬</div><div class="mkt-name">კინოში წასვლა</div><div class="mkt-price">20 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎁</div><div class="mkt-name">სიურპრიზი</div><div class="mkt-price">50 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🎮</div><div class="mkt-name">თამაში (2 სთ)</div><div class="mkt-price">15 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🍦</div><div class="mkt-name">ნაყინი</div><div class="mkt-price">5 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">🏆</div><div class="mkt-name">სპეც. გასეირნება</div><div class="mkt-price">30 🪙</div></div>
            <div class="mkt-card"><div class="mkt-ico">📚</div><div class="mkt-name">წიგნი საჩუქრად</div><div class="mkt-price">25 🪙</div></div>
        </div>
    </div>
</div>
</div>

{{-- ── CTA ── --}}
<div class="cta-wrap">
    <h2 class="cta-h2">დაიწყე დღეს.<br>პირველი ამოცანა — 2 წუთში.</h2>
    <p class="cta-sub">Google-ით შესვლა · ბარათი არ სჭირდება.</p>
    <button class="btn-main" style="font-size:1.15rem;padding:16px 44px;"
        onclick="document.getElementById('loginNavBtn')?.click()">
        შექმენი პროფილი — უფასოდ ✏️
    </button>
</div>

{{-- ── Footer ── --}}
<footer class="nb-footer">
    <div class="footer-logo">KidSmart 📓</div>
    <p class="footer-copy">© 2025 KidSmart · საქართველო</p>
</footer>

<script>
var QDATA = [
    { theme:'⚽ ფეხბურთი', icon:'⚽', clr:'',
      q:'კვარამ-მ პირველ თამაშში გაიტანა 3 გოლი, მეორე თამაშში 2-ით მეტი. სულ რამდენი გოლი ორივე თამაშში?',
      opts:['6','8','10','5'], ans:'8' },
    { theme:'🦸 სუპ-გმირი', icon:'🦸', clr:'qc-y',
      q:'სუპ.მენს 5 ბოროტმოქმედი დაამარცხა, ბეთმენს — 3-ით ნაკლები. ბეთმენმა რამდენი?',
      opts:['2','3','8','4'], ans:'2' },
    { theme:'🚀 კოსმოსი', icon:'🚀', clr:'qc-o',
      q:'რაკეტა 5 წუთში 600 კმ გაივლის. 1 წუთში რამდენ კმ-ს გაივლის?',
      opts:['100','120','150','60'], ans:'120' },
    { theme:'⚽ ფეხბურთი', icon:'🥅', clr:'',
      q:'ორ გუნდს ჯამში 9 გოლი. ერთმა 6 გაიტანა. მეორემ რამდენი გაიტანა?',
      opts:['2','3','4','5'], ans:'3' },
    { theme:'🦸 სუპ-გმირი', icon:'💥', clr:'qc-y',
      q:'გმირს 4 კოსტუმი. ყოველ კოსტუმს 3 ნიღბი. სულ რამდენი ნიღბი?',
      opts:['7','10','12','8'], ans:'12' },
    { theme:'🚀 კოსმოსი', icon:'🌍', clr:'qc-o',
      q:'კოსმოსური სადგური 92 წუთში ბრუნავს. 3 ბრუნვა — რამდენ წუთს?',
      opts:['184','276','366','280'], ans:'276' },
];

(function() {
    var track = document.getElementById('qCar');
    if (!track) return;

    function buildSet(prefix) {
        QDATA.forEach(function(c, i) {
            var uid = prefix + i;
            var div = document.createElement('div');
            div.className = 'qc' + (c.clr ? ' ' + c.clr : '');
            div.innerHTML =
                '<div class="qc-badge">' + c.theme + '</div>' +
                '<span class="qc-icon">' + c.icon + '</span>' +
                '<div class="qc-text">' + c.q + '</div>' +
                '<div class="qc-opts" id="qo' + uid + '"></div>' +
                '<div class="qc-fb" id="qf' + uid + '"></div>';
            track.appendChild(div);

            var el = div.querySelector('#qo' + uid);
            c.opts.forEach(function(opt) {
                var b = document.createElement('button');
                b.className = 'qc-opt';
                b.textContent = opt;
                b.onclick = function() {
                    if (el.dataset.done) return;
                    el.dataset.done = '1';
                    el.querySelectorAll('.qc-opt').forEach(function(btn) {
                        btn.disabled = true;
                        if (btn.textContent === c.ans) btn.classList.add('correct');
                        else if (btn.textContent === opt) btn.classList.add('wrong');
                    });
                    div.querySelector('#qf' + uid).innerHTML = opt === c.ans
                        ? '<span style="color:var(--green);">✓ სწორია! +10 🪙</span>'
                        : '<span style="color:#ef4444;">✗ სწ: ' + c.ans + '</span>';
                };
                el.appendChild(b);
            });
        });
    }

    buildSet('a');
})();

(function() {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.reveal').forEach(function(el) { el.classList.add('in'); });
        return;
    }
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { e.target.classList.add('in'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(function(el) { obs.observe(el); });
})();

// Mouse drag-to-scroll for all .dc carousels
(function() {
    document.querySelectorAll('.dc').forEach(function(el) {
        var down = false, startX, scrollLeft;
        el.addEventListener('mousedown', function(e) {
            down = true; el.classList.add('dragging');
            startX = e.pageX - el.getBoundingClientRect().left;
            scrollLeft = el.scrollLeft;
            e.preventDefault();
        });
        el.addEventListener('mouseleave', function() { down = false; el.classList.remove('dragging'); });
        el.addEventListener('mouseup',    function() { down = false; el.classList.remove('dragging'); });
        el.addEventListener('mousemove', function(e) {
            if (!down) return;
            var x = e.pageX - el.getBoundingClientRect().left;
            el.scrollLeft = scrollLeft - (x - startX) * 1.4;
        });
    });
})();
</script>
@endsection
