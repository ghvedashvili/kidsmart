@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=Patrick+Hand&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
:root {
    --nb-paper:  #fdf8f0;
    --nb-line:   #c8d8e8;
    --nb-margin: rgba(220,80,80,0.35);
    --nb-ink:    #1a3a6e;
    --nb-accent: #e85d04;
    --nb-green:  #2a7a2a;
}

body {
    background-color: var(--nb-paper);
    font-family: 'Patrick Hand', cursive;
    padding-left: 0 !important;
    padding-right: 0 !important;
    overflow-x: clip;
}

/* ruled lines */
.notebook-bg {
    background-image: repeating-linear-gradient(
        transparent, transparent 31px,
        var(--nb-line) 31px, var(--nb-line) 32px
    );
    position: relative;
}
/* red margin */
.notebook-bg::before {
    content: '';
    position: fixed;
    top: 0; left: 68px; bottom: 0;
    width: 2px;
    background: var(--nb-margin);
    pointer-events: none;
    z-index: 1;
}

/* ── Typography helpers ── */
.f-caveat   { font-family: 'Caveat', cursive; }
.f-patrick  { font-family: 'Patrick Hand', cursive; }
.f-fredoka  { font-family: 'Fredoka One', cursive; }

/* ── Doodle layer ── */
.doodle-layer {
    position: fixed; inset: 0; z-index: 0;
    pointer-events: none; overflow: hidden;
    opacity: 0.13;
}

/* ── Hero ── */
.hero {
    position: relative; z-index: 2;
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 40px;
    align-items: start;
    max-width: 1100px;
    margin: 0 auto;
    padding: 48px 80px 48px;
}
@media(max-width:900px) {
    .hero { grid-template-columns: 1fr; padding: 32px 24px 32px 84px; }
    .doodle-layer .d-right { display: none; }
}

.hero-badge {
    display: inline-block;
    font-family: 'Caveat', cursive;
    font-size: 13px; font-weight: 700;
    letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--nb-accent);
    border: 2px solid var(--nb-accent);
    padding: 4px 12px; border-radius: 4px;
    transform: rotate(-2deg);
    margin-bottom: 20px;
}
.hero-title {
    font-family: 'Caveat', cursive;
    font-size: clamp(2.4rem, 5vw, 3.4rem);
    font-weight: 700;
    line-height: 1.15;
    color: var(--nb-ink);
    margin-bottom: 16px;
}
.hero-title em {
    font-style: normal;
    color: var(--nb-accent);
    text-decoration: underline;
    text-decoration-style: wavy;
    text-decoration-color: var(--nb-accent);
}
.hero-sub {
    font-size: 17px;
    color: #555;
    line-height: 1.7;
    max-width: 420px;
    margin-bottom: 32px;
}
.btn-primary {
    font-family: 'Caveat', cursive;
    font-size: 17px; font-weight: 700;
    background: var(--nb-ink); color: #fff;
    border: none; border-radius: 6px;
    padding: 12px 28px; cursor: pointer;
    transition: background 0.2s;
    text-decoration: none; display: inline-block;
}
.btn-primary:hover { background: #2a4f9e; color: #fff; }
.btn-outline {
    font-family: 'Caveat', cursive;
    font-size: 17px;
    background: transparent; color: var(--nb-ink);
    border: 2px dashed var(--nb-ink); border-radius: 6px;
    padding: 12px 28px; cursor: pointer;
    transition: background 0.15s;
    text-decoration: none; display: inline-block;
}
.btn-outline:hover { background: rgba(26,58,110,0.06); color: var(--nb-ink); }

/* ── Live Demo card ── */
.demo-card {
    background: #fffef9;
    border: 2px solid var(--nb-line);
    border-radius: 2px;
    padding: 24px;
    box-shadow: 3px 3px 0 var(--nb-line);
    position: relative;
}
.demo-scissors {
    position: absolute; top: -20px; left: 0; right: 0;
    text-align: center; font-size: 11px; color: #ccc;
    font-family: 'Caveat', cursive;
}
.demo-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 12px;
}
.demo-label { font-family: 'Caveat',cursive; font-size:13px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#aaa; }
.demo-coins { font-family: 'Caveat',cursive; font-size:15px; font-weight:700; color:var(--nb-accent); }
.interest-btns { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.int-btn {
    font-family: 'Caveat',cursive; font-size:13px;
    padding: 4px 12px; border-radius: 20px;
    border: 2px dashed #ccc; color: #888;
    background: transparent; cursor: pointer;
    transition: all 0.15s;
}
.int-btn.active { border-style:solid; border-color:var(--nb-ink); color:var(--nb-ink); background:#eef3fb; }
.demo-q {
    font-family: 'Caveat',cursive; font-size:18px; font-weight:600;
    color:var(--nb-ink); line-height:1.4; min-height:52px; margin-bottom:16px;
}
.opts-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px; }
.opt-btn {
    font-family: 'Caveat',cursive; font-size:15px;
    padding: 8px 12px; border-radius:2px;
    border: 2px solid #e0e0e0;
    background: #fffef9; color:#555;
    cursor: pointer; text-align:left;
    transition: all 0.15s;
}
.opt-btn:hover:not(:disabled) { border-color:var(--nb-ink); }
.opt-btn.correct  { border-color:#22c55e; background:#f0fdf4; color:#166534; }
.opt-btn.wrong    { border-color:#ef4444; background:#fef2f2; color:#991b1b; }
.opt-btn.reveal   { border-color:#22c55e; background:#f0fdf4; color:#166534; }
.demo-feedback { font-family:'Caveat',cursive; font-size:14px; color:#555; min-height:20px; margin-bottom:8px; }
.demo-next { font-family:'Caveat',cursive; font-size:13px; color:#aaa; background:none; border:none; cursor:pointer; }
.demo-next:hover { color:var(--nb-ink); }

/* ── Sections ── */
.nb-section { position:relative; z-index:2; padding:56px 80px; }
@media(max-width:900px) { .nb-section { padding:40px 24px 40px 84px; } }
.nb-section-inner { max-width:1100px; margin:0 auto; }
.section-eyebrow {
    font-family:'Caveat',cursive; font-size:11px; font-weight:700;
    letter-spacing:.18em; text-transform:uppercase;
    color:var(--nb-accent); margin-bottom:8px;
}
.section-title {
    font-family:'Caveat',cursive; font-size:clamp(1.8rem,3.5vw,2.2rem); font-weight:700;
    color:var(--nb-ink);
    border-bottom: 3px solid var(--nb-accent);
    display:inline-block; padding-bottom:4px; margin-bottom:32px;
}
.nb-alt { background:rgba(200,216,232,0.1); }

/* feature cards */
.feat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.feat-card {
    background:#fffef9; border:2px solid var(--nb-line);
    border-radius:2px; padding:20px;
    box-shadow:2px 2px 0 #dde8f0;
    transition:transform 0.15s;
}
.feat-card:hover { transform:translateY(-3px) rotate(-0.4deg); }
.feat-ico  { font-size:1.6rem; margin-bottom:8px; }
.feat-title { font-family:'Caveat',cursive; font-size:18px; font-weight:700; color:var(--nb-ink); margin-bottom:4px; }
.feat-body  { font-size:13px; color:#666; line-height:1.6; }

/* steps */
.steps-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; }
.step-card {
    background:#fffef9; border:2px solid var(--nb-line);
    border-radius:2px; padding:20px; position:relative;
}
.step-num { font-family:'Caveat',cursive; font-size:48px; font-weight:700; color:rgba(26,58,110,0.1); line-height:1; margin-bottom:8px; }
.step-title { font-family:'Caveat',cursive; font-size:16px; font-weight:700; color:var(--nb-ink); margin-bottom:4px; }
.step-body  { font-size:13px; color:#666; line-height:1.6; }

/* ops */
.ops-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
.op-card {
    background:#fffef9; border:2px solid var(--nb-line);
    border-radius:2px; padding:20px 22px;
    display:flex; flex-direction:column; gap:6px;
}
.op-sym  { font-family:'Caveat',cursive; font-size:2rem; color:var(--nb-ink); line-height:1; }
.op-name { font-family:'Caveat',cursive; font-size:.65rem; color:#aaa; letter-spacing:.12em; text-transform:uppercase; }
.op-ex   { font-family:'Caveat',cursive; font-size:.9rem; color:#444; }

/* CTA */
.cta-section { position:relative; z-index:2; padding:64px 80px; text-align:center; }
.cta-title { font-family:'Caveat',cursive; font-size:clamp(2rem,4vw,2.8rem); font-weight:700; color:var(--nb-ink); margin-bottom:12px; line-height:1.2; }
.cta-sub   { font-size:16px; color:#777; margin-bottom:32px; }

/* footer */
.nb-footer {
    position:relative; z-index:2;
    display:flex; justify-content:space-between; align-items:center;
    padding:20px 80px;
    border-top:2px dashed var(--nb-line);
}
@media(max-width:600px) { .nb-footer { padding:16px 24px; } }
.footer-logo { font-family:'Fredoka One',cursive; font-size:1.1rem; color:var(--nb-ink); }
.footer-copy { font-size:12px; color:#aaa; }
</style>

{{-- ── Doodle layer ── --}}
<div class="doodle-layer">
    {{-- football top right --}}
    <svg class="d-right" style="position:absolute;top:80px;right:80px;width:160px;height:160px;" viewBox="0 0 180 180">
        <circle cx="90" cy="90" r="55" stroke="#1a3a6e" stroke-width="3" fill="none"/>
        <polygon points="90,45 105,60 100,80 80,80 75,60" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <polygon points="130,75 145,90 135,110 115,105 110,85" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <polygon points="50,75 35,90 45,110 65,105 70,85" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <text x="148" y="42" font-family="Caveat" font-size="14" fill="#e85d04" transform="rotate(-15 148 42)">გოლი!</text>
    </svg>
    {{-- stick footballer left --}}
    <svg style="position:absolute;top:220px;left:4px;width:52px;height:130px;" viewBox="0 0 55 140">
        <circle cx="27" cy="14" r="10" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <line x1="27" y1="24" x2="27" y2="70" stroke="#1a3a6e" stroke-width="2.5"/>
        <line x1="27" y1="38" x2="8"  y2="55" stroke="#1a3a6e" stroke-width="2.5"/>
        <line x1="27" y1="38" x2="46" y2="55" stroke="#1a3a6e" stroke-width="2.5"/>
        <line x1="27" y1="70" x2="10" y2="100" stroke="#1a3a6e" stroke-width="2.5"/>
        <line x1="27" y1="70" x2="44" y2="100" stroke="#1a3a6e" stroke-width="2.5"/>
        <circle cx="8"  cy="112" r="8" stroke="#1a3a6e" stroke-width="2" fill="none"/>
    </svg>
    {{-- rocket bottom right --}}
    <svg class="d-right" style="position:absolute;bottom:200px;right:60px;width:110px;height:190px;" viewBox="0 0 120 200">
        <path d="M60 10 Q75 30 78 80 L42 80 Q45 30 60 10Z" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <rect x="42" y="80" width="36" height="50" stroke="#1a3a6e" stroke-width="2.5" fill="none" rx="2"/>
        <path d="M42 130 Q30 145 32 165 L48 155 L42 130Z" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <path d="M78 130 Q90 145 88 165 L72 155 L78 130Z" stroke="#1a3a6e" stroke-width="2.5" fill="none"/>
        <circle cx="60" cy="55" r="10" stroke="#1a3a6e" stroke-width="2" fill="none"/>
        <path d="M50 165 Q55 180 60 190 Q65 180 70 165" stroke="#e85d04" stroke-width="2.5" fill="none"/>
        <text x="4" y="196" font-family="Caveat" font-size="13" fill="#1a3a6e" transform="rotate(-10 4 196)">3, 2, 1...</text>
    </svg>
    {{-- math scribbles left mid --}}
    <svg style="position:absolute;top:520px;left:76px;width:200px;height:80px;" viewBox="0 0 200 80">
        <text x="0"  y="22" font-family="Caveat" font-size="22" fill="#e85d04">17 + 9 = ?</text>
        <text x="10" y="50" font-family="Caveat" font-size="18" fill="#1a3a6e">x × 3 = 24</text>
        <text x="0"  y="74" font-family="Caveat" font-size="16" fill="#888">∴ x = 8 ✓</text>
    </svg>
    {{-- check circle --}}
    <svg style="position:absolute;top:140px;left:80px;width:140px;height:50px;" viewBox="0 0 140 50">
        <text x="0" y="22" font-family="Caveat" font-size="20" fill="#1a3a6e">5 × 6 = 30</text>
        <circle cx="126" cy="14" r="10" stroke="#2a9d2a" stroke-width="2.5" fill="none"/>
        <path d="M119 14 L124 19 L133 9" stroke="#2a9d2a" stroke-width="2.5" fill="none"/>
    </svg>
    {{-- fraction --}}
    <svg style="position:absolute;bottom:260px;left:80px;width:160px;height:55px;" viewBox="0 0 160 55">
        <text x="0"  y="22" font-family="Caveat" font-size="20" fill="#2a7a2a">½ + ¼ = ¾</text>
        <text x="10" y="50" font-family="Caveat" font-size="15" fill="#888">ფრაქციები ✓</text>
    </svg>
</div>

{{-- ── Hero ── --}}
<div class="notebook-bg">
<section class="hero">

    {{-- Left: text --}}
    <div>
        <div class="hero-badge">AI · ქართული · 6–14 წელი</div>
        <h1 class="hero-title">
            მათემატიკა,<br>
            რომელიც <em>მოსწონთ!</em>
        </h1>
        <p class="hero-sub">
            ბავშვი ირჩევს ინტერესს — KidSmart წერს ამოცანებს.
            ფეხბურთი, კოსმოსი, სუპერგმირები. სწავლა ისე,
            რომ ვერ ჩერდები.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button class="btn-primary" onclick="document.getElementById('loginNavBtn')?.click()">
                დაიწყე ახლა ✏️
            </button>
            <a href="#features" class="btn-outline">ნახე ფუნქციები ↓</a>
        </div>
    </div>

    {{-- Right: Live Demo --}}
    <div class="demo-card" style="position:relative;z-index:3;">
        <div class="demo-scissors">✂ - - - - - - - - - - - - - - -</div>
        <div class="demo-header">
            <span class="demo-label">📝 სცადე ახლავე</span>
            <span class="demo-coins" id="demoCoins">🪙 0 ქოინი</span>
        </div>
        <div class="interest-btns">
            <button class="int-btn active" data-id="football" onclick="demoSwitch('football',this)">⚽ ფეხბურთი</button>
            <button class="int-btn"        data-id="space"    onclick="demoSwitch('space',this)">🚀 კოსმოსი</button>
            <button class="int-btn"        data-id="hero"     onclick="demoSwitch('hero',this)">🦸 სუპ-გმირი</button>
            <button class="int-btn"        data-id="mc"       onclick="demoSwitch('mc',this)">⛏️ Minecraft</button>
        </div>
        <p class="demo-q" id="demoQ"></p>
        <div class="opts-grid" id="demoOpts"></div>
        <div class="demo-feedback" id="demoFeedback"></div>
        <button class="demo-next" id="demoNext" style="display:none;" onclick="demoNext()">↻ შემდეგი ამოცანა</button>
    </div>

</section>

{{-- ── Features ── --}}
<section class="nb-section nb-alt" id="features">
    <div class="nb-section-inner">
        <div class="section-eyebrow">შესაძლებლობები</div>
        <h2 class="section-title">ყველაფერი ერთ რვეულში</h2>
        <div class="feat-grid">
            <div class="feat-card"><div class="feat-ico">🎯</div><div class="feat-title">პერსონალიზებული ტესტები</div><p class="feat-body">ყოველ ბავშვს საკუთარი სირთულე, თემა და დღიური დავალება. მშობელი ირჩევს.</p></div>
            <div class="feat-card"><div class="feat-ico">📊</div><div class="feat-title">სტატისტიკა და პროგრესი</div><p class="feat-body">მშობელი ხედავს ყველა ტესტის შედეგს — სწორ, არასწორ, გამოტოვებულ.</p></div>
            <div class="feat-card"><div class="feat-ico">🔢</div><div class="feat-title">სახალისო ამოცანები</div><p class="feat-body">ფეხბურთი, ბაღი, კოსმოსი — ნაცნობი სამყაროდან ამოცანები.</p></div>
            <div class="feat-card"><div class="feat-ico">🏆</div><div class="feat-title">ყოველდღიური ჩვევა</div><p class="feat-body">შეტყობინება შეახსენებს. განსაზღვრე რამდენი ამოცანა — დღეში.</p></div>
            <div class="feat-card"><div class="feat-ico">👨‍👩‍👧</div><div class="feat-title">მშობლის კონტროლი</div><p class="feat-body">სიძნელე, თემა, კვირის განრიგი — ყველაფერი მშობლის dashboard-იდან.</p></div>
            <div class="feat-card"><div class="feat-ico">📱</div><div class="feat-title">ნებისმიერ მოწყობილობაზე</div><p class="feat-body">ტელეფონი, ტაბლეტი, კომპიუტერი — მუშაობს ყველგან.</p></div>
        </div>
    </div>
</section>

{{-- ── How It Works ── --}}
<section class="nb-section">
    <div class="nb-section-inner">
        <div class="section-eyebrow">პროცესი</div>
        <h2 class="section-title">4 ნაბიჯი დასაწყებად</h2>
        <div class="steps-grid">
            <div class="step-card"><div class="step-num">01</div><div class="step-title">მშობელი რეგისტრირდება</div><p class="step-body">Google-ით შესვლა — 30 წამი.</p></div>
            <div class="step-card"><div class="step-num">02</div><div class="step-title">ბავშვის პროფილი</div><p class="step-body">სახელი, კლასი, სირთულე, საყვარელი თემა.</p></div>
            <div class="step-card"><div class="step-num">03</div><div class="step-title">ბავშვი შედის კოდით</div><p class="step-body">საკუთარი 8-ნიშნა კოდი — ანგარიში არ სჭირდება.</p></div>
            <div class="step-card"><div class="step-num">04</div><div class="step-title">ამოცანები! ყოველდღე.</div><p class="step-body">სწავლა სახალისოდ. მშობელი ხედავს პროგრესს.</p></div>
        </div>
    </div>
</section>

{{-- ── Operations ── --}}
<section class="nb-section nb-alt">
    <div class="nb-section-inner">
        <div class="section-eyebrow">მოქმედებები</div>
        <h2 class="section-title">4 არითმეტიკული ოპერაცია</h2>
        <div class="ops-grid">
            <div class="op-card"><div class="op-sym">+</div><div class="op-name">შეკრება</div><div class="op-ex">347 + 285 = 632</div></div>
            <div class="op-card"><div class="op-sym">−</div><div class="op-name">გამოკლება</div><div class="op-ex">523 − 178 = 345</div></div>
            <div class="op-card"><div class="op-sym">×</div><div class="op-name">გამრავლება</div><div class="op-ex">47 × 23 = 1081</div></div>
            <div class="op-card"><div class="op-sym">÷</div><div class="op-name">გაყოფა</div><div class="op-ex">84 ÷ 7 = 12</div></div>
        </div>
    </div>
</section>

{{-- ── CTA ── --}}
<section class="cta-section">
    <h2 class="cta-title">დაიწყე დღეს.<br>პირველი ამოცანა — 2 წუთში.</h2>
    <p class="cta-sub">Google-ით შესვლა. ბარათი არ სჭირდება.</p>
    <button class="btn-primary" style="font-size:18px;padding:14px 40px;"
        onclick="document.getElementById('loginNavBtn')?.click()">
        შექმენი პროფილი — უფასოდ ✏️
    </button>
</section>

{{-- ── Footer ── --}}
<footer class="nb-footer">
    <div class="footer-logo">KidSmart 📓</div>
    <p class="footer-copy">© 2025 KidSmart · საქართველო</p>
</footer>

</div>{{-- /notebook-bg --}}

<script>
const DEMO_TASKS = {
    football: [
        { q:'კვარამ გაიტანა 17 გოლი, მიქამ — 9. კვარას რამდენით მეტი?', opts:['6','8','26','7'], ans:'8', ok:'სწორია! 17 − 9 = 8 🎉' },
        { q:'გუნდს 3 მატჩი კვირაში. 4 კვირაში სულ?', opts:['10','12','7','14'], ans:'12', ok:'ბრავო! 3 × 4 = 12 🎉' },
    ],
    space: [
        { q:'რაკეტა 120 კმ/წთ. 7 წუთში რამდენ კმ-ს გაივლის?', opts:['840 კმ','720 კმ','127 კმ','980 კმ'], ans:'840 კმ', ok:'ბრავო! 120 × 7 = 840 🚀' },
        { q:'კოსმოსური სადგური ყოველ 92 წუთში ბრუნავს. 5 ბრუნვა?', opts:['460 წთ','400 წთ','442 წთ','500 წთ'], ans:'460 წთ', ok:'ზუსტად! 92 × 5 = 460 🌍' },
    ],
    hero: [
        { q:'სუპ. 340 კმ/სთ, ბეთ. — 180 კმ/სთ. სუპ. რამდენით სწრაფია?', opts:['520','160','200','140'], ans:'160', ok:'ზუსტად! 340 − 180 = 160 🦸' },
        { q:'გმირმა 8 ბოროტმოქმედი დაამარცხა. ყოველ დღე 2-ი. რამდენ დღეში?', opts:['4','6','3','5'], ans:'4', ok:'პერფექტი! 8 ÷ 2 = 4 💪' },
    ],
    mc: [
        { q:'სტივს 64 ბლ., 48 დახ. დარჩ. 4-ად გაყო. თითოში?', opts:['4','3','5','2'], ans:'4', ok:'პერფ! (64−48) ÷ 4 = 4 ⛏️' },
        { q:'ერთ სახლზე 36 ბლოკი. 5 სახლზე?', opts:['180','150','216','175'], ans:'180', ok:'ბრავო! 36 × 5 = 180 🏠' },
    ],
};

let demoInterest = 'football';
let demoIdx      = 0;
let demoCoins    = 0;
let demoPicked   = false;

function demoRender() {
    const pool = DEMO_TASKS[demoInterest];
    const task = pool[demoIdx % pool.length];
    demoPicked = false;

    document.getElementById('demoQ').textContent = task.q;
    document.getElementById('demoFeedback').textContent = '';
    document.getElementById('demoNext').style.display = 'none';

    const grid = document.getElementById('demoOpts');
    grid.innerHTML = '';
    task.opts.forEach(opt => {
        const b = document.createElement('button');
        b.className = 'opt-btn';
        b.textContent = opt;
        b.onclick = () => demoPick(opt, task);
        grid.appendChild(b);
    });
}

function demoPick(opt, task) {
    if (demoPicked) return;
    demoPicked = true;

    const btns = document.querySelectorAll('.opt-btn');
    btns.forEach(b => {
        b.disabled = true;
        if (b.textContent === task.ans) b.classList.add('correct');
        else if (b.textContent === opt)  b.classList.add('wrong');
    });

    const fb = document.getElementById('demoFeedback');
    if (opt === task.ans) {
        demoCoins += 10;
        document.getElementById('demoCoins').textContent = '🪙 ' + demoCoins + ' ქოინი';
        fb.innerHTML = '<span style="color:#166534;">✅ ' + task.ok + ' +10 🪙</span>';
    } else {
        fb.innerHTML = '<span style="color:#991b1b;">❌ ცადე კიდევ! სწ: ' + task.ans + '</span>';
    }
    document.getElementById('demoNext').style.display = 'inline';
}

function demoNext() {
    demoIdx++;
    demoRender();
}

function demoSwitch(id, btn) {
    demoInterest = id;
    demoIdx = 0;
    document.querySelectorAll('.int-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    demoRender();
}

demoRender();
</script>
@endsection
