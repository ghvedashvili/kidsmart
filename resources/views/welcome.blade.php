@extends('layouts.app')

@section('content')
@php
    // ფასები პირდაპირ ბაზიდან (ადმინის „პაკეტები“ გვერდიდან იმართება)
    $__plans = \App\Models\Package::where('is_active', true)->orderBy('sort_order')->orderBy('price_monthly')->get();
    $__featuredId = $__plans->first(fn($p) => !$p->is_free)?->id;
    $__money = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
@endphp
<style>
    body.ks-landing { padding-top: 0 !important; background: #FFF8F1 !important; }
    html:has(body.ks-landing) { background: #FFF8F1 !important; background-image: none !important; scroll-padding-top: 80px; }
    body.ks-landing > nav.fixed-top, body.ks-landing #mobileNav { display: none !important; }
    .ksl { font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #1D1A17; overflow-x: clip; }
    .ksl a { color: inherit; }
    .ksl-in { max-width: 1200px; margin: 0 auto; padding: clamp(32px, 6vw, 80px) clamp(16px, 4vw, 40px); box-sizing: border-box; }
    .ksl-head { position: sticky; top: 0; z-index: 50; background: rgba(255,248,241,.94); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-bottom: 1px solid #F1E3D3; }
    .ksl-head-in { max-width: 1200px; margin: 0 auto; padding: 12px clamp(16px, 4vw, 40px); display: flex; align-items: center; gap: 12px 20px; }
    .ksl-brand { text-decoration: none; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .ksl-brand-mark { width: 40px; height: 40px; border-radius: 12px; background: #1D1A17; display: flex; align-items: center; justify-content: center; }
    .ksl-brand b { font-weight: 800; font-size: 20px; }
    .ksl-nav { flex: 1; min-width: 0; display: flex; gap: 2px; overflow-x: auto; white-space: nowrap; scrollbar-width: none; }
    .ksl-nav::-webkit-scrollbar { display: none; }
    .ksl-nav a { text-decoration: none; color: #4F473F; font-size: 14px; font-weight: 700; padding: 10px 12px; border-radius: 12px; }
    .ksl-nav a:hover { background: #FFE3CC; color: #8A3407; }
    .ksl-head-act { display: flex; gap: 8px; flex-shrink: 0; margin-left: auto; }
    .ksl-btn { height: 44px; padding: 0 18px; border-radius: 22px; border: none; font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; white-space: nowrap; }
    .ksl-btn.ghost { background: #fff; border: 2px solid #F1E3D3; color: #1D1A17; }
    .ksl-btn.ghost:hover { border-color: #1D1A17; }
    .ksl-btn.primary { background: #C24A0A; color: #fff; }
    .ksl-btn.primary:hover { background: #A53E08; color: #fff; }
    .ksl-btn.dark { background: #1D1A17; color: #fff; }
    .ksl-btn.dark:hover { background: #000; color: #fff; }
    .ksl-btn.big { height: 58px; padding: 0 28px; border-radius: 29px; font-size: 18px; }
    .ksl-eyebrow { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #B6460A; }
    .ksl-h1 { margin: 0; font-size: clamp(54px, 9vw, 104px); line-height: .9; font-weight: 900; font-stretch: 66%; text-transform: uppercase; }
    .ksl-h2 { margin: 0; font-size: clamp(40px, 5.5vw, 64px); line-height: .92; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .ksl-p { margin: 0; font-size: 17px; line-height: 1.55; color: #4F473F; }
    .ksl-sec-head { display: flex; flex-direction: column; gap: 12px; max-width: 760px; margin-bottom: 28px; }
    .ksl-hero { display: flex; flex-wrap: wrap; align-items: center; gap: 32px; }
    .ksl-hero-txt { flex: 1 1 420px; display: flex; flex-direction: column; gap: 20px; }
    .ksl-pill { align-self: flex-start; font-size: 13px; font-weight: 700; color: #8A3407; background: #FFE3CC; border-radius: 999px; padding: 6px 14px; }
    .ksl-hero-art { flex: 1 1 340px; position: relative; min-height: 340px; display: flex; align-items: center; justify-content: center; }
    .ksl-blob1 { position: absolute; width: min(420px, 90%); aspect-ratio: 1; border-radius: 48% 52% 40% 60% / 45% 40% 60% 55%; background: #FFB27A; }
    .ksl-blob2 { position: absolute; width: min(300px, 66%); aspect-ratio: 1; border-radius: 60% 40% 55% 45% / 50% 55% 45% 50%; background: #F7883E; }
    .ksl-hero-art svg { position: relative; width: min(280px, 70%); height: auto; }
    .ksl-float { position: absolute; background: #fff; border-radius: 18px; padding: 10px 14px; box-shadow: 0 8px 20px rgba(150,80,20,.15); font-size: 14px; font-weight: 800; }
    .ksl-float.a { left: 4%; top: 12%; color: #14612F; transform: rotate(-6deg); }
    .ksl-float.b { right: 4%; bottom: 12%; transform: rotate(4deg); }
    .ksl-band { background: #fff; border-top: 1px solid #F1E3D3; border-bottom: 1px solid #F1E3D3; }
    .ksl-split { display: flex; flex-wrap: wrap; gap: 32px; align-items: center; }
    .ksl-split > * { flex: 1 1 380px; min-width: 0; }
    .ksl-try { background: #FFE3CC; border-radius: 32px; padding: clamp(18px, 3vw, 28px); display: flex; flex-direction: column; gap: 16px; }
    .ksl-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .ksl-tab { height: 42px; padding: 0 16px; border-radius: 999px; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; background: #fff; color: #1D1A17; border: 2px solid #fff; }
    .ksl-tab[aria-selected="true"] { background: #1D1A17; color: #fff; border-color: #1D1A17; }
    .ksl-q { margin: 0; font-size: clamp(19px, 2vw, 24px); line-height: 1.4; font-weight: 700; }
    .ksl-opts { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
    .ksl-opt { height: 68px; border-radius: 20px; font-family: inherit; font-size: 26px; font-weight: 800; cursor: pointer; background: #fff; color: #1D1A17; border: 3px solid #fff; }
    .ksl-opt:hover { border-color: #FFC49A; }
    .ksl-opt.ok { background: #DFF5E3; color: #14612F; border-color: #1E8A4C; }
    .ksl-opt.bad { background: #FDE2E2; color: #A12626; border-color: #D93B3B; }
    .ksl-fb { border-radius: 16px; padding: 12px 16px; font-size: 15px; font-weight: 800; display: none; }
    .ksl-fb.ok { display: block; background: #fff; color: #14612F; }
    .ksl-fb.bad { display: block; background: #fff; color: #A12626; }
    .ksl-levels { flex: 1 1 320px; background: #fff; border-radius: 28px; padding: 24px; display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 10px; align-items: end; box-shadow: 0 2px 0 #F1E3D3; }
    .ksl-levels div { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .ksl-levels i { display: block; width: 100%; border-radius: 12px; background: #EEE8FF; }
    .ksl-levels .on i { background: #7B5CF0; }
    .ksl-levels span { font-size: 12px; font-weight: 700; }
    .ksl-cards2 { flex: 1 1 280px; display: flex; flex-direction: column; gap: 16px; }
    .ksl-note { border-radius: 24px; padding: 22px; display: flex; flex-direction: column; gap: 6px; }
    .ksl-note b { font-size: 20px; font-weight: 800; }
    .ksl-note span { font-size: 15px; line-height: 1.5; }
    .ksl-dark { background: #1D1A17; color: #fff; }
    .ksl-dark .ksl-p { color: #E8DCCF; }
    .ksl-dark .ksl-eyebrow { color: #FFC49A; }
    .ksl-grid4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; }
    .ksl-game { border-radius: 24px; padding: 18px; display: flex; flex-direction: column; gap: 8px; color: #1D1A17; }
    .ksl-game-ic { width: 48px; height: 48px; border-radius: 14px; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .ksl-game b { font-size: 17px; font-weight: 800; line-height: 1.25; }
    .ksl-game span { font-size: 13px; line-height: 1.45; color: #3A332D; }
    .ksl-grade { border-radius: 24px; padding: 20px; display: flex; flex-direction: column; gap: 6px; }
    .ksl-grade-n { font-size: 44px; line-height: 1; font-weight: 900; font-stretch: 68%; }
    .ksl-grade-k { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; }
    .ksl-grade b { font-size: 17px; font-weight: 800; }
    .ksl-grade span:last-child { font-size: 13px; line-height: 1.5; color: #3A332D; }
    .ksl-peach { background: #FFE3CC; }
    .ksl-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ksl-chips span { background: #fff; border-radius: 999px; padding: 8px 14px; font-size: 13px; font-weight: 700; }
    .ksl-rewards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
    .ksl-reward { background: #fff; border-radius: 20px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 14px; font-weight: 700; }
    .ksl-reward b { display: flex; align-items: center; gap: 4px; font-weight: 800; white-space: nowrap; }
    .ksl-feats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .ksl-feat { background: #fff; border-radius: 20px; padding: 16px; display: flex; flex-direction: column; gap: 4px; box-shadow: 0 2px 0 #F1E3D3; }
    .ksl-feat b { font-size: 16px; font-weight: 800; }
    .ksl-feat span { font-size: 14px; line-height: 1.5; color: #4F473F; }
    .ksl-mock { background: #fff; border-radius: 28px; padding: 22px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 12px 30px rgba(150,80,20,.12); }
    .ksl-mock-bars { height: 130px; display: grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 8px; align-items: end; }
    .ksl-mock-bars i { display: block; border-radius: 8px; background: #B9A8F5; }
    .ksl-mock-bars i.z { background: #EFE3D6; }
    .ksl-mock-bars i.t { background: #E2590F; }
    .ksl-mock-row { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; }
    .ksl-mock-bar { height: 8px; border-radius: 4px; background: #F4ECE3; overflow: hidden; }
    .ksl-mock-bar span { display: block; height: 8px; border-radius: 4px; }
    .ksl-plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; align-items: stretch; }
    .ksl-plan { border-radius: 28px; padding: 26px; display: flex; flex-direction: column; gap: 16px; background: #FFF8F1; border: 2px solid #F1E3D3; }
    .ksl-plan.feat { background: #1D1A17; color: #fff; border-color: #1D1A17; }
    .ksl-plan-h { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .ksl-plan-h b { font-size: 22px; font-weight: 800; }
    .ksl-plan-badge { font-size: 12px; font-weight: 800; border-radius: 999px; padding: 6px 10px; background: #F7883E; color: #1D1A17; }
    .ksl-price { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
    .ksl-price b { font-size: 44px; font-weight: 900; font-stretch: 70%; }
    .ksl-price span { font-size: 14px; font-weight: 700; color: #6B625A; }
    .ksl-plan.feat .ksl-price span { color: #E8DCCF; }
    .ksl-plan ul { margin: 0; padding: 0 0 0 18px; display: flex; flex-direction: column; gap: 8px; font-size: 14px; line-height: 1.45; }
    .ksl-plan .ksl-btn { margin-top: auto; height: 54px; border-radius: 27px; justify-content: center; font-size: 16px; }
    .ksl-plan.feat .ksl-btn { background: #F7883E; color: #1D1A17; }
    .ksl-plan.feat .ksl-btn:hover { background: #FFA766; }
    .ksl-cta { position: relative; overflow: hidden; background: #F7883E; border-radius: 36px; padding: clamp(28px, 5vw, 56px); display: flex; flex-wrap: wrap; align-items: center; gap: 24px; }
    .ksl-cta::before { content: ''; position: absolute; right: -60px; top: -60px; width: 300px; height: 300px; border-radius: 50%; background: #FFA766; }
    .ksl-cta > * { position: relative; }
    .ksl-foot { border-top: 1px solid #F1E3D3; }
    .ksl-foot-in { max-width: 1200px; margin: 0 auto; padding: 24px clamp(16px, 4vw, 40px); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; font-size: 13px; color: #6B625A; }
    .ksl-foot a { font-weight: 700; color: #B6460A; }
    @media (max-width: 900px) {
        .ksl-nav { display: none; }
        .ksl-head-act .ghost { display: none; }
        .ksl-hero-art { min-height: 260px; }
        .ksl-opt { height: 60px; font-size: 22px; }
    }
    @media (prefers-reduced-motion: no-preference) { html:has(body.ks-landing) { scroll-behavior: smooth; } }
</style>

<div class="ksl" id="top">
    <header class="ksl-head">
        <div class="ksl-head-in">
            <a href="#top" class="ksl-brand">
                <span class="ksl-brand-mark">@include('partials.ks-fox', ['size' => 26])</span>
                <b>KidSmart</b>
            </a>
            <nav class="ksl-nav" aria-label="გვერდის ნავიგაცია">
                <a href="#try">ამოცანები</a>
                <a href="#adaptive">სწავლება</a>
                <a href="#games">თამაშები</a>
                <a href="#audience">აუდიტორია</a>
                <a href="#rewards">ჯილდოები</a>
                <a href="#parent">მშობელი</a>
                <a href="#pricing">ფასები</a>
            </nav>
            <div class="ksl-head-act">
                <button type="button" class="ksl-btn ghost" onclick="toggleLoginModal(event); lmSwitch('child')">ბავშვის შესვლა</button>
                <button type="button" class="ksl-btn primary" onclick="toggleLoginModal(event)">დაიწყე</button>
            </div>
        </div>
    </header>

    {{-- ── Hero ── --}}
    <section class="ksl-in ksl-hero">
        <div class="ksl-hero-txt">
            <span class="ksl-pill">II–V კლასის მოსწავლეებისთვის</span>
            <h1 class="ksl-h1">მათემატიკა ბავშვის ენაზე</h1>
            <p class="ksl-p" style="font-size:clamp(16px,1.6vw,19px);max-width:520px;">ბავშვი სწავლობს იმ ენაზე, რომელიც მას უყვარს — ამოცანები ფეხბურთიდან, კოსმოსიდან და სუპერგმირების სამყაროდან.</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">
                <button type="button" class="ksl-btn big" style="background:#E2590F;color:#fff;" onclick="toggleLoginModal(event)">დაიწყე უფასოდ <i class="bi bi-arrow-right"></i></button>
                <a href="#try" class="ksl-btn big ghost" style="font-size:16px;">სცადე ამოცანა <i class="bi bi-arrow-down"></i></a>
            </div>
            <span style="font-size:13px;color:#6B625A;">Google-ით შესვლა · ბარათი არ სჭირდება</span>
        </div>
        <div class="ksl-hero-art" aria-hidden="true">
            <div class="ksl-blob1"></div>
            <div class="ksl-blob2"></div>
            @include('partials.ks-fox', ['size' => 280, 'color' => '#E2590F', 'smile' => true])
            <span class="ksl-float a">✓ 5 სწორი ზედიზედ!</span>
            <span class="ksl-float b">🪙 +30 მონეტა</span>
        </div>
    </section>

    {{-- ── სცადე ამოცანა ── --}}
    <section id="try" class="ksl-band">
        <div class="ksl-in ksl-split">
            <div style="display:flex;flex-direction:column;gap:14px;">
                <span class="ksl-eyebrow">სცადე ამოცანა</span>
                <h2 class="ksl-h2">ფეხბურთი, კოსმოსი თუ სუპერგმირები?</h2>
                <p class="ksl-p">როცა ამოცანები ბავშვის სამყაროდანაა, სწავლა თავგადასავალია. აირჩიე თემა და უპასუხე.</p>
            </div>
            <div class="ksl-try">
                <div class="ksl-tabs" role="tablist" aria-label="თემატიკა" id="kslTabs"></div>
                <p class="ksl-q" id="kslQ"></p>
                <div class="ksl-opts" role="radiogroup" aria-label="პასუხები" id="kslOpts"></div>
                <div class="ksl-fb" id="kslFb" aria-live="polite"></div>
            </div>
        </div>
    </section>

    {{-- ── ადაპტირებული სწავლება ── --}}
    <section id="adaptive" class="ksl-in">
        <div class="ksl-sec-head">
            <span class="ksl-eyebrow" style="color:#5B3FD0;">ადაპტირებული სწავლება</span>
            <h2 class="ksl-h2">სისტემა, რომელიც ბავშვთან ერთად იზრდება</h2>
            <p class="ksl-p">არანაირი გადაწვა ან მოწყენილობა. დონე ავტომატურად ერგება ბავშვის შედეგებს.</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:20px;">
            <div class="ksl-levels" role="img" aria-label="სირთულის 5 დონე">
                @foreach([50, 80, 110, 140, 170] as $__i => $__h)
                <div class="{{ $__i === 2 ? 'on' : '' }}"><i style="height:{{ $__h }}px"></i><span>დონე {{ $__i + 1 }}</span></div>
                @endforeach
            </div>
            <div class="ksl-cards2">
                <div class="ksl-note" style="background:#DFF5E3;"><b style="color:#14612F;">კარგად წერს ტესტებს?</b><span style="color:#14612F;">დონე ავტომატურად იწევა, რომ ბავშვს არ მოსწყინდეს.</span></div>
                <div class="ksl-note" style="background:#FFF1C2;"><b style="color:#6B4A00;">ხშირად უშვებს შეცდომებს?</b><span style="color:#6B4A00;">ვთავაზობთ შესაბამისი დონის ამოცანებს და ვავითარებთ ნაბიჯ-ნაბიჯ.</span></div>
            </div>
        </div>
    </section>

    {{-- ── თამაშები ── --}}
    <section id="games" class="ksl-dark">
        <div class="ksl-in">
            <div class="ksl-sec-head">
                <span class="ksl-eyebrow">KidSmart თამაშები</span>
                <h2 class="ksl-h2">ისწავლე თამაშით — არა მხოლოდ ტესტებით</h2>
                <p class="ksl-p">ტესტებს შორის ბავშვი შესვენებას თამაშით იღებს და KidSmart-ს ეჯიბრება.</p>
            </div>
            <div class="ksl-grid4">
                @foreach([
                    ['✊', 'ქვა-ქაღალდი-მაკრატელი', 'კლასიკური თამაში KidSmart-ის წინააღმდეგ — პირადი რეკორდით.', '#FFE3CC'],
                    ['🐰', 'ბაჭია და მგლები', 'ლოგიკური სამაგიდო თამაში: მართე მგლები და დაიჭირე ბაჭია.', '#DCEBFF'],
                    ['🧠', 'მეხსიერება', 'იპოვე წყვილები რაც შეიძლება სწრაფად და მოხვდი ტაბლოზე.', '#EEE8FF'],
                    ['🧱', 'კვორიდორი', 'ააშენე კედლები და პირველმა მიაღწიე მეორე მხარეს.', '#DFF5E3'],
                ] as [$__gi, $__gn, $__gt, $__gb])
                <div class="ksl-game" style="background:{{ $__gb }}">
                    <span class="ksl-game-ic" aria-hidden="true">{{ $__gi }}</span>
                    <b>{{ $__gn }}</b>
                    <span>{{ $__gt }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── აუდიტორია ── --}}
    <section id="audience" class="ksl-in">
        <div class="ksl-sec-head">
            <span class="ksl-eyebrow" style="color:#1F4E9C;">სამიზნე აუდიტორია</span>
            <h2 class="ksl-h2">იმ ასაკისთვის, როცა ლოგიკა იბადება</h2>
        </div>
        <div class="ksl-grid4">
            @foreach([
                ['II', 'პირველი ნაბიჯები', 'საბაზისო არითმეტიკა, სივრცითი აღქმა და მარტივი ლოგიკა.', '#FFE3CC'],
                ['III', 'ლოგიკის გამყარება', 'შაბლონები, კრებები და მცირე დეტექტიური ამოცანები.', '#EEE8FF'],
                ['IV', 'ნამდვილი გამოწვევები', 'საიდუმლო კოდები, პირამიდები და ლოგიკური ჯაჭვები.', '#DCEBFF'],
                ['V', 'ელიტური დეტექტივი', 'მრავალეტაპიანი ამოცანები და სტრატეგიული აზროვნება.', '#DFF5E3'],
            ] as [$__n, $__t, $__x, $__b])
            <div class="ksl-grade" style="background:{{ $__b }}">
                <span class="ksl-grade-n">{{ $__n }}</span>
                <span class="ksl-grade-k">კლასი</span>
                <b>{{ $__t }}</b>
                <span>{{ $__x }}</span>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── ჯილდოები ── --}}
    <section id="rewards" class="ksl-peach">
        <div class="ksl-in ksl-split">
            <div style="display:flex;flex-direction:column;gap:14px;">
                <span class="ksl-eyebrow" style="color:#8A3407;">ჯილდოების სისტემა</span>
                <h2 class="ksl-h2">მშობელი ადგენს მიზანს, ბავშვი ირჩევს გზას</h2>
                <p class="ksl-p" style="color:#4A2A12;">შექმენით ოჯახის მარკეტი და თავად დააწესეთ ჯილდოები. სწორი პასუხებით ბავშვი მონეტებს აგროვებს და ჯილდოს თავად ირჩევს.</p>
                <div class="ksl-chips">
                    <span>მედლები და დონეები</span><span>მონეტები</span><span>ზედიზედ დღეები</span><span>ოლიმპიადა</span>
                </div>
            </div>
            <div class="ksl-rewards">
                @foreach([['🍕', 'პიცა სახლში', 10], ['🎬', 'კინოში წასვლა', 20], ['🎁', 'სიურპრიზი', 50], ['🎮', 'თამაში (2 სთ)', 15], ['🍦', 'ნაყინი', 5], ['📚', 'წიგნი საჩუქრად', 25]] as [$__ri, $__rn, $__rp])
                <div class="ksl-reward">
                    <span>{{ $__ri }} {{ $__rn }}</span>
                    <b><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>{{ $__rp }}</b>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── მშობლის პანელი ── --}}
    <section id="parent" class="ksl-in ksl-split">
        <div style="display:flex;flex-direction:column;gap:18px;">
            <span class="ksl-eyebrow" style="color:#5B3FD0;">მშობლის პანელი</span>
            <h2 class="ksl-h2">შენ აწესებ წესებს, ჩვენ ვაკეთებთ ანალიზს</h2>
            <div class="ksl-feats">
                <div class="ksl-feat"><b>მოქნილი გრაფიკი</b><span>შენ განსაზღვრავ, დღეში რამდენი ტესტი უნდა დაწეროს.</span></div>
                <div class="ksl-feat"><b>სრული კონტროლი</b><span>ნახე ყველა ტესტი, სად შეცდა და რა იყო სწორი პასუხი.</span></div>
                <div class="ksl-feat"><b>ზრდის დინამიკა</b><span>შედეგები თემებისა და დონეების მიხედვით.</span></div>
                <div class="ksl-feat"><b>შეტყობინებები</b><span>გაუგზავნე შეხსენება შვილს ერთი ღილაკით.</span></div>
            </div>
        </div>
        <div class="ksl-mock" aria-hidden="true">
            <div style="display:flex;align-items:center;gap:10px;"><span style="width:40px;height:40px;border-radius:50%;background:#DCEBFF;display:flex;align-items:center;justify-content:center;font-size:20px;">👦</span><b style="font-size:16px;">ნიკა · ბოლო 7 დღე</b></div>
            <div class="ksl-mock-bars">
                <i style="height:66px"></i><i style="height:78px"></i><i class="z" style="height:6px"></i><i style="height:52px"></i><i style="height:96px"></i><i style="height:40px"></i><i class="t" style="height:84px"></i>
            </div>
            <div class="ksl-mock-row"><span>შეკრება</span><span>92%</span></div><div class="ksl-mock-bar"><span style="width:92%;background:#1E8A4C;"></span></div>
            <div class="ksl-mock-row"><span>ლოგიკა</span><span>58%</span></div><div class="ksl-mock-bar"><span style="width:58%;background:#F5B301;"></span></div>
        </div>
    </section>

    {{-- ── ფასები ── --}}
    <section id="pricing" class="ksl-band">
        <div class="ksl-in">
            <div class="ksl-sec-head">
                <span class="ksl-eyebrow">ტარიფები</span>
                <h2 class="ksl-h2">აირჩიე შენზე მორგებული პაკეტი</h2>
                <p class="ksl-p">დაიწყე უფასოდ, გადადი უფრო დიდ პაკეტზე, როცა მზად იქნები.</p>
            </div>
            <div class="ksl-plans">
                @foreach($__plans as $__pl)
                @php $__feat = $__pl->id === $__featuredId; @endphp
                <div class="ksl-plan {{ $__feat ? 'feat' : '' }}">
                    <div class="ksl-plan-h"><b>{{ $__pl->name }}</b>@if($__feat)<span class="ksl-plan-badge">ყველაზე პოპულარული</span>@endif</div>
                    <div class="ksl-price">
                        @if($__pl->is_free)
                            <b>0 ₾</b><span>მუდამ უფასო</span>
                        @elseif($__pl->price_monthly > 0)
                            <b>{{ $__money($__pl->price_monthly) }} ₾</b><span>/ თვეში</span>
                        @elseif($__pl->price_yearly > 0)
                            <b>{{ $__money($__pl->price_yearly) }} ₾</b><span>/ წელიწადში</span>
                        @endif
                    </div>
                    <ul>
                        <li>{{ $__pl->max_children === 0 ? 'შეუზღუდავი რაოდენობის შვილი' : $__pl->max_children . ' შვილის პროფილი' }}</li>
                        @if($__pl->max_difficulty)<li>სირთულის {{ $__pl->max_difficulty }} დონემდე</li>@endif
                        @if(!$__pl->is_free && $__pl->price_monthly > 0 && $__pl->price_yearly > 0)<li>ან {{ $__money($__pl->price_yearly) }} ₾ წელიწადში</li>@endif
                        @foreach(preg_split('/\r\n|\r|\n/', trim((string) $__pl->description)) as $__line)
                            @if(trim($__line) !== '')<li>{{ trim($__line) }}</li>@endif
                        @endforeach
                    </ul>
                    <button type="button" class="ksl-btn {{ $__feat ? '' : 'dark' }}" onclick="toggleLoginModal(event)">{{ $__pl->is_free ? 'დაიწყე უფასოდ' : 'აირჩიე ' . $__pl->name }}</button>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA ── --}}
    <section class="ksl-in">
        <div class="ksl-cta">
            <div style="flex:1 1 360px;display:flex;flex-direction:column;gap:10px;">
                <h2 class="ksl-h1" style="font-size:clamp(44px,6vw,76px);color:#fff;">დაიწყე დღეს</h2>
                <span style="font-size:18px;font-weight:700;color:#3D1A04;">პირველი ამოცანა — 2 წუთში.</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button type="button" class="ksl-btn big dark" onclick="toggleLoginModal(event)">შექმენი პროფილი — უფასოდ</button>
                <span style="font-size:13px;font-weight:700;color:#3D1A04;text-align:center;">Google-ით შესვლა · ბარათი არ სჭირდება</span>
            </div>
        </div>
    </section>

    <footer class="ksl-foot">
        <div class="ksl-foot-in">
            <span>© {{ date('Y') }} KidSmart · საქართველო · Developed by Ghvedashvili</span>
            <a href="#top">ზემოთ დაბრუნება ↑</a>
        </div>
    </footer>
</div>

<script>
(function () {
    var Q = {
        football: { label: '⚽ ფეხბურთი', q: 'ორმა გუნდმა ჯამში 9 გოლი გაიტანა. ერთმა — 6. მეორემ რამდენი?', opts: ['2', '3', '4'], a: '3' },
        hero:     { label: '🦸 სუპერგმირები', q: 'გმირს 4 კოსტიუმი აქვს, თითოეულს — 3 ნიღაბი. სულ რამდენი ნიღაბია?', opts: ['7', '12', '14'], a: '12' },
        space:    { label: '🚀 კოსმოსი', q: 'რაკეტა 5 წუთში 600 კმ-ს გადის. 1 წუთში რამდენ კმ-ს გაივლის?', opts: ['100', '120', '150'], a: '120' }
    };
    var cur = 'football';
    var tabs = document.getElementById('kslTabs'), qEl = document.getElementById('kslQ'),
        opts = document.getElementById('kslOpts'), fb = document.getElementById('kslFb');
    if (!tabs) return;
    function render() {
        tabs.innerHTML = '';
        Object.keys(Q).forEach(function (id) {
            var b = document.createElement('button');
            b.type = 'button'; b.className = 'ksl-tab'; b.setAttribute('role', 'tab');
            b.setAttribute('aria-selected', id === cur ? 'true' : 'false');
            b.textContent = Q[id].label;
            b.onclick = function () { cur = id; render(); };
            tabs.appendChild(b);
        });
        var item = Q[cur];
        qEl.textContent = item.q;
        opts.innerHTML = ''; fb.className = 'ksl-fb'; fb.textContent = '';
        item.opts.forEach(function (v) {
            var o = document.createElement('button');
            o.type = 'button'; o.className = 'ksl-opt'; o.setAttribute('role', 'radio'); o.setAttribute('aria-checked', 'false');
            o.textContent = v;
            o.onclick = function () {
                Array.prototype.forEach.call(opts.children, function (c) {
                    c.classList.remove('ok', 'bad'); c.setAttribute('aria-checked', 'false');
                    if (c.textContent === item.a) c.classList.add('ok');
                });
                o.setAttribute('aria-checked', 'true');
                if (v === item.a) { fb.className = 'ksl-fb ok'; fb.textContent = '✓ სწორია! KidSmart-ში ასეთ პასუხებზე მონეტებს მოიგებ.'; }
                else { o.classList.add('bad'); fb.className = 'ksl-fb bad'; fb.textContent = '✗ თითქმის! სწორი პასუხია ' + item.a + '.'; }
            };
            opts.appendChild(o);
        });
    }
    render();
})();
</script>
@endsection
