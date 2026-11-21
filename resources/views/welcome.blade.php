@extends('layouts.app')

@section('content')
@php
    // ფასები პირდაპირ ბაზიდან (ადმინის „პაკეტები“ გვერდიდან იმართება)
    $__plans = \App\Models\Package::where('is_active', true)->orderBy('sort_order')->orderBy('price_monthly')->get();
    $__featuredId = $__plans->first(fn($p) => !$p->is_free)?->id;
    $__money = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $__av = fn($k) => asset('avatars/' . (str_starts_with($k, 'g') ? 'girls/' : 'boys/') . $k . '.webp');
@endphp
<style>
    /* redesign-kidsmart: ლენდინგი v2 — სამი ფერის ზოლები (ლურჯი · ყვითელი · სტაფილოსფერი) */
    body.ks-landing { padding-top: 0 !important; background: #fff !important; }
    html:has(body.ks-landing) { background: #fff !important; background-image: none !important; scroll-padding-top: 76px; }
    body.ks-landing > nav.fixed-top, body.ks-landing #mobileNav { display: none !important; }
    .ksv { --blue: #1F5A6B; --blue-b: #2B7387; --yel: #F5C431; --yel-b: #F9D86A; --ora: #E2590F; --ora-b: #F07A2E; --ora-s: #C24A0A;
        font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; overflow-x: clip; background: #fff; }
    .ksv a { color: inherit; }
    .ksv-blue { background: var(--blue); color: #fff; }
    .ksv-yel { background: var(--yel); color: #2B2622; }
    .ksv-ora { background: var(--ora); color: #fff; }
    .ksv-in { max-width: 1200px; margin: 0 auto; padding: 72px 40px; box-sizing: border-box; }
    .ksv-band { display: flex; flex-wrap: wrap; align-items: center; gap: 48px; }
    .ksv-col { flex: 1 1 420px; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
    .ksv-h1 { margin: 0; font-size: clamp(36px, 4.4vw, 60px); line-height: .95; font-weight: 900; font-stretch: 66%; text-transform: uppercase; }
    .ksv-h2 { margin: 0; font-size: clamp(28px, 3.2vw, 42px); line-height: 1; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .ksv-lead { margin: 0; font-size: 18px; line-height: 1.5; font-weight: 700; }
    .ksv-yel .ksv-lead { font-weight: 600; }
    .ksv-pill { align-self: flex-start; background: #fff; color: #1F5A6B; border-radius: 999px; padding: 7px 14px; font-size: 14px; font-weight: 800; }
    .ksv-btn { height: 56px; padding: 0 26px; border-radius: 28px; border: none; font-family: inherit; font-size: 17px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; cursor: pointer; box-sizing: border-box; white-space: nowrap; }
    .ksv-btn.yel { background: var(--yel); color: #2B2622; }
    .ksv-btn.yel:hover { background: #FFD24D; color: #2B2622; }
    .ksv-btn.line { border: 3px solid #fff; background: transparent; color: #fff; }
    .ksv-btn.line:hover { background: rgba(255,255,255,.12); color: #fff; }
    .ksv-btn.ora { background: var(--ora-s); color: #fff; }
    .ksv-btn.ora:hover { background: #A33E08; color: #fff; }
    .ksv-note { font-size: 14px; font-weight: 700; }

    /* ზედა მენიუ */
    .ksv-head { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,.96); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-bottom: 1px solid #ECE9E4; }
    .ksv-head-in { max-width: 1200px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; gap: 16px; }
    .ksv-brand { text-decoration: none; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .ksv-brand-mark { width: 42px; height: 42px; border-radius: 13px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; }
    .ksv-brand b { font-size: 20px; font-weight: 800; }
    .ksv-nav { flex: 1; display: flex; justify-content: center; gap: 2px; }
    .ksv-nav a { text-decoration: none; color: #4F473F; font-size: 14px; font-weight: 700; padding: 8px 10px; border-radius: 12px; white-space: nowrap; }
    .ksv-nav a:hover { background: #FFF3EA; color: #8A3407; }
    .ksv-head .ksv-btn { margin-left: auto; height: 40px; padding: 0 14px; border-radius: 20px; font-size: 13px; gap: 6px; }

    /* ილუსტრაცია: ლაქა + ბავშვების სახეები + დუდლები */
    .ksv-art { flex: 1 1 340px; position: relative; min-height: 340px; display: flex; align-items: center; justify-content: center; }
    .ksv-blob { position: absolute; width: min(400px, 88%); aspect-ratio: 1.15; border-radius: 52% 48% 44% 56% / 48% 42% 58% 52%; }
    .ksv-stage { position: relative; width: 340px; height: 310px; }
    .ksv-av { position: absolute; border-radius: 50%; object-fit: cover; border: 5px solid #fff; box-shadow: 0 8px 20px rgba(43,38,34,.18); background: #fff; }
    .ksv-chip { position: absolute; background: #fff; color: #2B2622; border-radius: 16px; padding: 9px 13px; font-size: 14px; font-weight: 800; box-shadow: 0 8px 18px rgba(43,38,34,.15); white-space: nowrap; }
    .ksv-doodle { position: absolute; }

    /* სცადე ამოცანა */
    .ksv-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .ksv-tab { height: 44px; padding: 0 16px; border-radius: 999px; font-family: inherit; font-size: 14px; font-weight: 800; cursor: pointer; background: #fff; color: #2B2622; border: 2px solid #fff; }
    .ksv-tab[aria-selected="true"] { background: var(--ora-s); border-color: var(--ora-s); color: #fff; }
    .ksv-quiz { background: #fff; border-radius: 26px; padding: 20px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 10px 24px rgba(43,38,34,.12); }
    .ksv-quiz-top { display: flex; justify-content: space-between; align-items: center; }
    .ksv-qnum { font-size: 12px; font-weight: 800; color: var(--ora-s); text-transform: uppercase; letter-spacing: .5px; }
    .ksv-arrows { display: flex; gap: 8px; }
    .ksv-arrow { width: 44px; height: 44px; border-radius: 50%; border: 2px solid #ECE9E4; background: #fff; color: #2B2622; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
    .ksv-arrow.go { border: none; background: var(--ora-s); color: #fff; }
    .ksv-arrow:disabled { opacity: .35; cursor: default; }
    .ksv-q { margin: 0; font-size: 20px; line-height: 1.4; font-weight: 700; }
    .ksv-opts { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
    .ksv-opt { height: 64px; border-radius: 20px; font-family: inherit; font-size: 24px; font-weight: 800; cursor: pointer; background: #F7F6F3; color: #2B2622; border: 3px solid #F7F6F3; }
    .ksv-opt:hover { border-color: #FFC49A; }
    .ksv-opt.ok { background: #DFF5E3; color: #14612F; border-color: #1E8A4C; }
    .ksv-opt.bad { background: #FDE2E2; color: #A12626; border-color: #D93B3B; }
    .ksv-fb { display: none; border-radius: 14px; padding: 10px 14px; font-size: 15px; font-weight: 800; }
    .ksv-fb.ok { display: block; background: #DFF5E3; color: #14612F; }
    .ksv-fb.bad { display: block; background: #FDE2E2; color: #A12626; }
    .ksv-dots { display: flex; justify-content: center; gap: 6px; }
    .ksv-qtrack { display: flex; gap: 14px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none; overscroll-behavior-x: contain; padding: 4px 2px 10px; }
    .ksv-qtrack::-webkit-scrollbar { display: none; }
    .ksv-qtrack > .ksv-quiz { flex: 0 0 100%; box-sizing: border-box; scroll-snap-align: center; }
    .ksv-qnav { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .ksv-qnav .ksv-arrow { border: none; background: #fff; color: #2B2622; }
    .ksv-qnav .ksv-arrow.go { background: #2B2622; color: #fff; }
    .ksv-ora .ksv-dots i { background: rgba(255,255,255,.45); }
    .ksv-ora .ksv-dots i.on { background: #fff; }
    @media (max-width: 640px) { .ksv-qtrack > .ksv-quiz { flex-basis: 88%; } }
    .ksv-dots i { height: 8px; width: 8px; border-radius: 4px; background: #FFC49A; transition: width .2s, background .2s; }
    .ksv-dots i.on { width: 22px; background: var(--ora-s); }

    /* დონეები */
    .ksv-levels { flex: 1 1 360px; background: #fff; color: #2B2622; border-radius: 30px; padding: 26px; display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 12px; align-items: end; box-sizing: border-box; }
    .ksv-levels > div { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .ksv-levels i { display: block; width: 100%; border-radius: 14px; background: #FFE3CC; }
    .ksv-levels .on i { background: var(--ora); }
    .ksv-levels span { font-size: 12px; font-weight: 700; }
    .ksv-levels .on span { color: var(--ora-s); font-weight: 800; }
    .ksv-levels img { width: 54px; height: 54px; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 6px 14px rgba(43,38,34,.18); margin-bottom: -4px; object-fit: cover; background: #fff; }
    .ksv-tags { display: flex; flex-wrap: wrap; gap: 10px; }
    .ksv-tags span { background: #fff; border-radius: 16px; padding: 12px 14px; font-size: 14px; font-weight: 800; }

    /* კარუსელი (თამაშები) */
    .ksv-car-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; }
    .ksv-track { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none; padding: 2px 2px 6px; }
    .ksv-track::-webkit-scrollbar { display: none; }
    .ksv-track > * { scroll-snap-align: start; flex-shrink: 0; }
    .ksv-game { width: 270px; background: #fff; color: #2B2622; border-radius: 26px; padding: 20px; display: flex; flex-direction: column; gap: 10px; box-sizing: border-box; }
    .ksv-game-ic { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; }
    .ksv-game b { font-size: 18px; font-weight: 800; }
    .ksv-game span:last-child { font-size: 14px; line-height: 1.5; color: #4F473F; }
    .ksv-car-head .ksv-arrow { border: none; background: #fff; color: #2B2622; width: 48px; height: 48px; }
    .ksv-car-head .ksv-arrow.go { background: var(--ora-s); color: #fff; }
    /* თეთრ ბარათებზე ტექსტი ყოველთვის მუქია, რა ფერის ზოლშიც არ უნდა იყოს */
    .ksv-quiz, .ksv-reward, .ksv-levels, .ksv-mock, .ksv-game, .ksv-chip, .ksv-tags span, .ksv-plan { color: #2B2622; }
    .ksv-yel .ksv-plan.feat { color: #fff; }
    /* ფერის მიხედვით: ყვითელზე — მუქი ელემენტები, სტაფილოსფერზე — თეთრი */
    .ksv-yel .ksv-pill { color: #8A3407; }
    .ksv-yel .ksv-btn.line { border-color: #2B2622; color: #2B2622; }
    .ksv-yel .ksv-btn.line:hover { background: rgba(43,38,34,.08); color: #2B2622; }
    .ksv-ora .ksv-tab { background: rgba(255,255,255,.2); border-color: transparent; color: #fff; }
    .ksv-ora .ksv-tab[aria-selected="true"] { background: #fff; border-color: #fff; color: var(--ora-s); }
    .ksv-ora .ksv-btn.ora { background: #fff; color: var(--ora-s); }
    .ksv-ora .ksv-btn.ora:hover { background: #FFF3EA; color: #A33E08; }
    .ksv-yel .ksv-plan.feat { background: var(--blue); color: #fff; }
    .ksv-yel .ksv-plan.feat .ksv-price span { color: #E1F0F2; }

    /* ჯილდოები */
    .ksv-rewards { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
    .ksv-reward { background: #fff; border-radius: 18px; padding: 13px 14px; display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; }
    .ksv-reward b { display: flex; align-items: center; gap: 4px; font-weight: 800; white-space: nowrap; }

    /* მშობელი */
    .ksv-mock { flex: 1 1 380px; background: #fff; color: #2B2622; border-radius: 30px; padding: 20px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 12px 28px rgba(43,38,34,.18); box-sizing: border-box; }
    .ksv-mock-h { display: flex; align-items: center; gap: 12px; }
    .ksv-mock-h img { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 3px solid #FFF3EA; }
    .ksv-mock-h span { flex: 1; display: flex; flex-direction: column; }
    .ksv-mock-h small { font-size: 13px; color: #6B625A; }
    .ksv-mock-ok { background: #DFF5E3; color: #14612F; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 800; flex: none !important; }
    .ksv-mock-bars { background: #F7F6F3; border-radius: 20px; padding: 14px; height: 140px; box-sizing: border-box; display: grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 8px; align-items: end; }
    .ksv-mock-bars i { display: block; border-radius: 8px; background: #FFC49A; }
    .ksv-mock-bars i.hi { background: var(--ora); } .ksv-mock-bars i.z { background: #ECE9E4; }
    .ksv-feats { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
    .ksv-feats span { background: rgba(255,255,255,.16); border-radius: 16px; padding: 12px 14px; font-size: 14px; font-weight: 700; line-height: 1.35; }

    /* ფასები */
    .ksv-plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; color: #2B2622; }
    .ksv-plan { background: #fff; border-radius: 28px; padding: 24px; display: flex; flex-direction: column; gap: 12px; box-sizing: border-box; }
    .ksv-plan.feat { background: var(--yel); }
    .ksv-plan-h { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .ksv-plan-h b { font-size: 22px; font-weight: 800; }
    .ksv-plan-badge { background: #fff; color: #2B2622; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 800; }
    .ksv-price { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
    .ksv-price b { font-size: 36px; font-weight: 900; font-stretch: 70%; }
    .ksv-price span { font-size: 14px; font-weight: 700; color: #6B625A; }
    .ksv-plan.feat .ksv-price span { color: #4F473F; }
    .ksv-plan ul { margin: 0; padding: 0 0 0 18px; display: flex; flex-direction: column; gap: 6px; font-size: 14px; line-height: 1.45; }
    .ksv-plan .ksv-btn { margin-top: auto; height: 52px; font-size: 16px; }
    .ksv-plan .ksv-btn.ghost { background: #fff; border: 2px solid #ECE9E4; color: #2B2622; }
    .ksv-plan .ksv-btn.ghost:hover { border-color: #2B2622; }

    /* CTA + ფუტერი */
    .ksv-cta-avs { display: flex; align-items: center; }
    .ksv-cta-avs img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 5px solid #fff; box-shadow: 0 8px 20px rgba(43,38,34,.18); background: #fff; }
    .ksv-cta-avs img + img { margin-left: -18px; }
    .ksv-foot-in { max-width: 1200px; margin: 0 auto; padding: 20px 24px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; font-size: 13px; color: #6B625A; }
    .ksv-foot-in a { font-weight: 700; color: var(--ora-s); }

    @media (max-width: 1023px) { .ksv-nav { display: none; } }
    /* მობილური: თითო თემა — ერთ ეკრანზე */
    @media (max-width: 640px) {
        .ksv-in { padding: 30px 18px; }
        .ksv-band { gap: 18px; }
        .ksv-col { flex-basis: 100%; gap: 12px; }
        .ksv-art { min-height: 220px; flex-basis: 100%; }
        .ksv-stage { transform: scale(.68); margin: -50px 0; }
        .ksv-hide-sm { display: none !important; }
        .ksv-lead { font-size: 16px; }
        .ksv-btn { height: 50px; padding: 0 20px; font-size: 15px; }
        .ksv-quiz { padding: 16px; gap: 12px; }
        .ksv-q { font-size: 17px; }
        .ksv-opt { height: 54px; font-size: 20px; }
        .ksv-levels { padding: 16px; gap: 8px; flex-basis: 100%; }
        .ksv-game { width: 78vw; }
        .ksv-feats { grid-template-columns: 1fr 1fr; gap: 8px; }
        .ksv-feats span { padding: 10px 12px; font-size: 13px; }
        .ksv-mock-bars { height: 110px; }
        /* ფასები მობილურზე: კარუსელის გარეშე, კომპაქტურად ერთმანეთის ქვეშ */
        .ksv-plans { grid-template-columns: 1fr; gap: 10px; }
        .ksv-plan { padding: 16px 18px; gap: 8px; border-radius: 22px; }
        .ksv-plan-h b { font-size: 18px; }
        .ksv-price b { font-size: 28px; }
        .ksv-plan ul { display: none; }
        .ksv-plan .ksv-btn { height: 46px; }
        .ksv-cta-avs { display: none; }
    }

    /* ── შესვლის მოდალი — საიტის დიზაინში ── */
    body .auth-modal-box { background: #F7F6F3 !important; max-width: 420px !important; padding: 22px !important; box-sizing: border-box; }
    body .auth-modal-head { padding: 0 !important; gap: 10px; }
    body .auth-modal-title { font-size: clamp(24px, calc(1rem + 1.2vw), 30px) !important; line-height: 1 !important; font-stretch: 68% !important; }
    body .auth-modal-close { width: 40px !important; height: 40px !important; border-radius: 14px !important; background: #fff !important; border: 1px solid #ECE9E4 !important; flex-shrink: 0; }
    body .auth-modal-tabs { margin: 16px 0 12px !important; background: #F1EFEB !important; gap: 4px !important; }
    body .auth-modal-tab { height: 42px; font-size: 14px !important; padding: 0 10px !important; }
    body .auth-modal-tab.active { background: #fff !important; color: #2B2622 !important; box-shadow: 0 2px 0 #ECE9E4 !important; }
    body .auth-modal-panel { background: #fff; border-radius: 22px; padding: 18px !important; box-shadow: 0 2px 0 #ECE9E4; }
    .auth-modal-note { margin: 0 0 14px; font-size: 14px; line-height: 1.5; color: #4F473F; }
    body .auth-google-btn, body .auth-code-btn { height: 52px; padding: 0 16px !important; border-radius: 26px !important; background: #C24A0A !important; box-shadow: none !important; font-family: inherit !important; font-size: 15px !important; font-weight: 800; color: #fff !important; transform: none !important; display: flex; align-items: center; justify-content: center; gap: 10px; }
    body .auth-google-btn:hover, body .auth-code-btn:hover { background: #A33E08 !important; }
    body .auth-code-input { height: 56px; border-radius: 16px !important; border: 2px solid #ECE9E4 !important; background: #F7F6F3 !important; font-family: inherit !important; font-size: 22px !important; font-weight: 800; letter-spacing: .25em !important; margin-bottom: 12px !important; }
    body .auth-code-input:focus { border-color: #E2590F !important; background: #fff !important; box-shadow: 0 0 0 3px rgba(226,89,15,.12); }
    .auth-modal-small { display: block; margin-top: 10px; font-size: 12px; color: #6B625A; text-align: center; }
    @media (prefers-reduced-motion: no-preference) { html:has(body.ks-landing) { scroll-behavior: smooth; } }
</style>

@php
    $__star  = fn($s, $c, $st) => '<svg class="ksv-doodle" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="'.$c.'" stroke-width="2" stroke-linejoin="round" aria-hidden="true" style="'.$st.'"><path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.5 6.7 19.4l1.2-6L3.4 9.3l6-.7z"/></svg>';
    $__heart = fn($s, $c, $st) => '<svg class="ksv-doodle" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="'.$c.'" aria-hidden="true" style="'.$st.'"><path d="M12 21s-7.5-4.6-9.3-9.2C1.4 8.4 3.6 5 7 5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8C19.5 16.4 12 21 12 21z"/></svg>';
    $__spark = fn($s, $c, $st) => '<svg class="ksv-doodle" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="'.$c.'" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" style="'.$st.'"><path d="M12 2v5M12 17v5M2 12h5M17 12h5M5 5l3 3M16 16l3 3M19 5l-3 3M8 16l-3 3"/></svg>';
    $__img   = fn($k, $s, $st) => '<img class="ksv-av" src="'.e($__av($k)).'" alt="" style="width:'.$s.'px;height:'.$s.'px;'.$st.'">';
@endphp

<div class="ksv" id="top">
    <header class="ksv-head">
        <div class="ksv-head-in">
            <a href="#top" class="ksv-brand">
                <img src="{{ asset('img/logo.png') }}" alt="KidSmart" style="height:32px;width:auto;display:block;">
            </a>
            <nav class="ksv-nav" aria-label="გვერდის ნავიგაცია">
                <a href="#try">ამოცანები</a>
                <a href="#levels">სწავლება</a>
                <a href="#games">თამაშები</a>
                <a href="#rewards">ჯილდოები</a>
                <a href="#parent">მშობელი</a>
                <a href="#pricing">ფასები</a>
            </nav>
            <button type="button" class="ksv-btn ora" onclick="toggleLoginModal(event)">დაიწყე</button>
        </div>
    </header>

    {{-- ── Hero (ყვითელი) ── --}}
    <section class="ksv-yel">
        <div class="ksv-in ksv-band">
            <div class="ksv-col">
                <span class="ksv-pill">II–V კლასის მოსწავლეებისთვის</span>
                <h1 class="ksv-h1">მათემატიკა ბავშვის ენაზე</h1>
                <p class="ksv-lead" style="max-width:500px;">ამოცანები ფეხბურთიდან, კოსმოსიდან და სუპერგმირების სამყაროდან — დონე თავად ერგება ბავშვს.</p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <button type="button" class="ksv-btn ora" onclick="toggleLoginModal(event)">დაიწყე უფასოდ <i class="bi bi-arrow-right"></i></button>
                    <a href="#try" class="ksv-btn line">სცადე ამოცანა</a>
                </div>
                <span class="ksv-note">Google-ით შესვლა · ბარათი არ სჭირდება</span>
            </div>
            <div class="ksv-art" aria-hidden="true">
                <div class="ksv-blob" style="background:var(--yel-b);"></div>
                <div class="ksv-stage">
                    {!! $__img('g07', 150, 'left:10px;top:10px;') !!}
                    {!! $__img('b05', 130, 'right:10px;top:40px;') !!}
                    {!! $__img('b12', 120, 'left:70px;bottom:0;') !!}
                    {!! $__img('g02', 110, 'right:40px;bottom:10px;') !!}
                    <span class="ksv-chip" style="left:-20px;bottom:120px;color:#14612F;transform:rotate(-6deg);">✓ 5 სწორი ზედიზედ!</span>
                    <span class="ksv-chip" style="right:-20px;top:0;background:var(--ora-s);color:#fff;transform:rotate(5deg);">+30 მონეტა</span>
                    {!! $__star(32, '#2B2622', 'left:-40px;top:-10px;') !!}
                    {!! $__heart(24, '#E2590F', 'right:0;bottom:-10px;') !!}
                    {!! $__spark(28, '#2B2622', 'right:-40px;top:150px;') !!}
                </div>
            </div>
        </div>
    </section>

    {{-- ── სცადე ამოცანა (სტაფილოსფერი) ── --}}
    <section id="try" class="ksv-ora">
        <div class="ksv-in ksv-band">
            <div class="ksv-art ksv-hide-sm" aria-hidden="true" style="min-height:320px;">
                <div class="ksv-blob" style="background:var(--ora-b);"></div>
                <div class="ksv-stage">
                    {!! $__img('b01', 140, 'left:20px;top:20px;') !!}
                    {!! $__img('g10', 150, 'right:10px;bottom:0;') !!}
                    <span class="ksv-chip" style="left:30px;bottom:30px;width:90px;height:90px;padding:0;border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:900;color:#C24A0A;transform:rotate(-8deg);">7+5</span>
                    {!! $__star(30, '#fff', 'right:0;top:0;') !!}
                    {!! $__heart(24, '#fff', 'left:-20px;top:130px;') !!}
                </div>
            </div>
            <div class="ksv-col">
                <h2 class="ksv-h2">სცადე ამოცანა</h2>
                <p class="ksv-lead">აირჩიე თემა და უპასუხე — KidSmart-ში ასეთ პასუხებზე ბავშვი მონეტებს აგროვებს.</p>
                <div class="ksv-tabs" role="tablist" aria-label="თემატიკა" id="ksvTabs"></div>
                <div class="ksv-qtrack" id="ksvTrack" aria-label="ამოცანები" tabindex="0"></div>
                <div class="ksv-qnav">
                    <button type="button" class="ksv-arrow" id="ksvPrev" aria-label="წინა ამოცანა"><i class="bi bi-chevron-left"></i></button>
                    <div class="ksv-dots" id="ksvDots" aria-hidden="true"></div>
                    <button type="button" class="ksv-arrow go" id="ksvNext" aria-label="შემდეგი ამოცანა"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </section>

    {{-- ── ადაპტირებული სწავლება (ზღვის ტალღა) ── --}}
    <section id="levels" class="ksv-blue">
        <div class="ksv-in ksv-band">
            <div class="ksv-col">
                <h2 class="ksv-h2">სწავლა, რომელიც ბავშვთან ერთად იზრდება</h2>
                <p class="ksv-lead">კარგად წერს? დონე იწევა. უჭირს? ვთავაზობთ შესაბამის ამოცანებს და ვავითარებთ ნაბიჯ-ნაბიჯ.</p>
                <div class="ksv-tags">
                    <span style="color:#14612F;">კარგად წერს → დონე ↑</span>
                    <span style="color:#8A3407;">უჭირს → ნაბიჯ-ნაბიჯ</span>
                </div>
            </div>
            <div class="ksv-levels" role="img" aria-label="სირთულის 5 დონე, ბავშვი მესამე დონეზეა">
                @foreach([50, 80, 110, 140, 170] as $__i => $__h)
                <div class="{{ $__i === 2 ? 'on' : '' }}">
                    @if($__i === 2)<img src="{{ $__av('g04') }}" alt="">@endif
                    <i style="height:{{ $__h }}px"></i><span>დონე {{ $__i + 1 }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── თამაშები (ყვითელი) ── --}}
    <section id="games" class="ksv-yel">
        <div class="ksv-in" style="display:flex;flex-direction:column;gap:22px;">
            <div class="ksv-car-head">
                <div style="display:flex;flex-direction:column;gap:10px;max-width:640px;">
                    <h2 class="ksv-h2">ისწავლე თამაშით</h2>
                    <p class="ksv-lead">ტესტებს შორის შესვენება — ბავშვი KidSmart-ს ეჯიბრება.</p>
                </div>
                <div class="ksv-arrows">
                    <button type="button" class="ksv-arrow" id="ksvGPrev" aria-label="წინა თამაში"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="ksv-arrow go" id="ksvGNext" aria-label="შემდეგი თამაში"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="ksv-track" id="ksvGames" aria-label="თამაშები" tabindex="0">
                @foreach([
                    ['✊', 'ქვა-ქაღალდი-მაკრატელი', 'კლასიკა KidSmart-ის წინააღმდეგ — პირადი რეკორდით.', '#FFF3EA'],
                    ['🐰', 'ბაჭია და მგლები', 'ლოგიკური თამაში: მართე მგლები და დაიჭირე ბაჭია.', '#E1F0F2'],
                    ['🧠', 'მეხსიერება', 'იპოვე წყვილები რაც შეიძლება სწრაფად და მოხვდი ტაბლოზე.', '#FFF1C2'],
                    ['🧱', 'კვორიდორი', 'ააშენე კედლები და პირველმა მიაღწიე მეორე მხარეს.', '#E1F0F2'],
                ] as [$__gi, $__gn, $__gt, $__gb])
                <div class="ksv-game">
                    <span class="ksv-game-ic" style="background:{{ $__gb }}" aria-hidden="true">{{ $__gi }}</span>
                    <b>{{ $__gn }}</b>
                    <span>{{ $__gt }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── ჯილდოები (სტაფილოსფერი) ── --}}
    <section id="rewards" class="ksv-ora">
        <div class="ksv-in ksv-band">
            <div class="ksv-col">
                <h2 class="ksv-h2">მონეტები, მედლები, ჯილდოები</h2>
                <p class="ksv-lead">სწორ პასუხებზე ბავშვი მონეტებს აგროვებს და ოჯახის მარკეტში ცვლის — ჯილდოებს მშობელი ამატებს და ადასტურებს.</p>
                <div class="ksv-rewards">
                    @foreach([['🍦', 'ნაყინი', 5], ['🍕', 'პიცა სახლში', 10], ['🎮', 'თამაში (2 სთ)', 15], ['🎬', 'კინოში წასვლა', 20]] as [$__ri, $__rn, $__rp])
                    <div class="ksv-reward">
                        <span>{{ $__ri }} {{ $__rn }}</span>
                        <b><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>{{ $__rp }}</b>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="ksv-art" aria-hidden="true">
                <div class="ksv-blob" style="background:var(--ora-b);"></div>
                <div class="ksv-stage">
                    {!! $__img('g14', 150, 'right:20px;top:0;') !!}
                    {!! $__img('b08', 130, 'left:10px;bottom:10px;') !!}
                    <span class="ksv-chip" style="left:40px;top:30px;width:80px;height:80px;padding:0;border-radius:50%;border:6px solid #E2590F;box-sizing:border-box;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#C24A0A;">★</span>
                    <span class="ksv-chip" style="right:10px;bottom:30px;transform:rotate(4deg);">🏅 ახალი მედალი!</span>
                    {!! $__spark(30, '#fff', 'left:-30px;bottom:60px;') !!}
                    {!! $__heart(24, '#fff', 'right:-20px;top:150px;') !!}
                </div>
            </div>
        </div>
    </section>

    {{-- ── მშობელი (ზღვის ტალღა) ── --}}
    <section id="parent" class="ksv-blue">
        <div class="ksv-in ksv-band">
            <div class="ksv-col">
                <h2 class="ksv-h2">მშობლისთვის — ყველაფერი ერთ ეკრანზე</h2>
                <p class="ksv-lead">დღეს დაწერა თუ არა, კვირის შედეგები, რომელი თემა უჭირს — და ერთი ღილაკით შეხსენება.</p>
                <div class="ksv-feats">
                    <span>შენ ადგენ, დღეში რამდენი ტესტი</span>
                    <span>ყველა შეცდომა და სწორი პასუხი</span>
                    <span>შედეგები თემების მიხედვით</span>
                    <span>შეხსენება ერთი ღილაკით</span>
                </div>
            </div>
            <div class="ksv-mock" aria-hidden="true">
                <div class="ksv-mock-h">
                    <img src="{{ $__av('b15') }}" alt="">
                    <span><b>ნიკა</b><small>3 კლასი · დონე 3/5</small></span>
                    <span class="ksv-mock-ok">✓ დღეს დაწერა</span>
                </div>
                <div class="ksv-mock-bars">
                    <i style="height:60%"></i><i class="hi" style="height:80%"></i><i class="z" style="height:6%"></i><i class="hi" style="height:90%"></i><i style="height:70%"></i><i class="hi" style="height:85%"></i><i class="z" style="height:6%"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- ── ფასები (ყვითელი) ── --}}
    <section id="pricing" class="ksv-yel">
        <div class="ksv-in" style="display:flex;flex-direction:column;gap:22px;">
            <h2 class="ksv-h2" style="text-align:center;">აირჩიე პაკეტი</h2>
            <div class="ksv-plans">
                @foreach($__plans as $__pl)
                @php $__feat = $__pl->id === $__featuredId; @endphp
                <div class="ksv-plan {{ $__feat ? 'feat' : '' }}">
                    <div class="ksv-plan-h"><b>{{ $__pl->name }}</b>@if($__feat)<span class="ksv-plan-badge">პოპულარული</span>@endif</div>
                    <div class="ksv-price">
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
                    <button type="button" class="ksv-btn {{ $__feat || !$__pl->is_free ? 'ora' : 'ghost' }}" onclick="toggleLoginModal(event)">{{ $__pl->is_free ? 'დაიწყე უფასოდ' : 'აირჩიე ' . $__pl->name }}</button>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA (სტაფილოსფერი) ── --}}
    <section class="ksv-ora">
        <div class="ksv-in ksv-band" style="justify-content:space-between;">
            <div style="display:flex;flex-direction:column;gap:10px;flex:1 1 380px;">
                <h2 class="ksv-h2">დაიწყე დღეს</h2>
                <p class="ksv-lead">პირველი ტესტი 2 წუთში — უფასოდ.</p>
            </div>
            <div class="ksv-cta-avs" aria-hidden="true">
                @foreach(['g07', 'b01', 'g10', 'b05'] as $__k)<img src="{{ $__av($__k) }}" alt="">@endforeach
            </div>
            <button type="button" class="ksv-btn ora" onclick="toggleLoginModal(event)">შექმენი პროფილი</button>
        </div>
    </section>

    <footer>
        <div class="ksv-foot-in">
            <span>© {{ date('Y') }} KidSmart · საქართველო · Developed by Ghvedashvili</span>
            <a href="#top">ზემოთ დაბრუნება ↑</a>
        </div>
    </footer>
</div>

<script>
(function () {
    // ── სცადე ამოცანა: თითო თემატიკაში 4 ამოცანა ──
    var Q = {
        football: { label: '⚽ ფეხბურთი', items: [
            { q: 'ორმა გუნდმა ჯამში 9 გოლი გაიტანა. ერთმა — 6. მეორემ რამდენი?', opts: ['2', '3', '4'], a: '3' },
            { q: 'სტადიონზე 8 რიგია, თითოეულში — 10 სკამი. სულ რამდენი სკამია?', opts: ['18', '80', '88'], a: '80' },
            { q: 'მატჩი 90 წუთს გრძელდება და 2 ტაიმისგან შედგება. ერთი ტაიმი რამდენი წუთია?', opts: ['40', '45', '50'], a: '45' },
            { q: 'ნიკამ 3 მატჩში 2-2 გოლი გაიტანა. სულ რამდენი გოლი გაიტანა?', opts: ['5', '6', '8'], a: '6' }
        ] },
        hero: { label: '🦸 სუპერგმირები', items: [
            { q: 'გმირს 4 კოსტიუმი აქვს, თითოეულს — 3 ნიღაბი. სულ რამდენი ნიღაბია?', opts: ['7', '12', '14'], a: '12' },
            { q: 'ქალაქში 20 შენობაა. გმირმა 7 უკვე დაიცვა. კიდევ რამდენი დარჩა?', opts: ['12', '13', '27'], a: '13' },
            { q: '3 გმირს თითოეულს 5 სუპერძალა აქვს. სულ რამდენი სუპერძალაა?', opts: ['8', '15', '35'], a: '15' },
            { q: 'გმირი 24 საფეხურს 4 თანაბარ ნახტომში ავიდა. ერთ ნახტომში რამდენი საფეხურია?', opts: ['4', '6', '8'], a: '6' }
        ] },
        space: { label: '🚀 კოსმოსი', items: [
            { q: 'რაკეტა 5 წუთში 600 კმ-ს გადის. 1 წუთში რამდენ კმ-ს გაივლის?', opts: ['100', '120', '150'], a: '120' },
            { q: 'მთვარეზე 6 ასტრონავტი გაფრინდა, 4 დედამიწაზე დაბრუნდა. რამდენი დარჩა მთვარეზე?', opts: ['1', '2', '10'], a: '2' },
            { q: 'რაკეტას 3 ძრავა აქვს, თითოეულს — 4 საქშენი. სულ რამდენი საქშენია?', opts: ['7', '12', '16'], a: '12' },
            { q: 'ტელესკოპით 35 ვარსკვლავი დავთვალეთ, მერე — კიდევ 25. სულ რამდენი?', opts: ['50', '60', '70'], a: '60' }
        ] }
    };
    var theme = 'football';
    var tabs = document.getElementById('ksvTabs'), track = document.getElementById('ksvTrack'), dots = document.getElementById('ksvDots'),
        prev = document.getElementById('ksvPrev'), next = document.getElementById('ksvNext');
    if (tabs && track) {
        function card(item, n, total) {
            var c = document.createElement('div'); c.className = 'ksv-quiz';
            c.setAttribute('role', 'group'); c.setAttribute('aria-label', 'ამოცანა ' + n + ' / ' + total);
            var num = document.createElement('span'); num.className = 'ksv-qnum'; num.textContent = 'ამოცანა ' + n + ' / ' + total;
            var q = document.createElement('p'); q.className = 'ksv-q'; q.textContent = item.q;
            var opts = document.createElement('div'); opts.className = 'ksv-opts'; opts.setAttribute('role', 'radiogroup'); opts.setAttribute('aria-label', 'პასუხები');
            var fb = document.createElement('div'); fb.className = 'ksv-fb'; fb.setAttribute('aria-live', 'polite');
            item.opts.forEach(function (v) {
                var o = document.createElement('button');
                o.type = 'button'; o.className = 'ksv-opt'; o.setAttribute('role', 'radio'); o.setAttribute('aria-checked', 'false');
                o.textContent = v;
                o.onclick = function () {
                    Array.prototype.forEach.call(opts.children, function (x) {
                        x.classList.remove('ok', 'bad'); x.setAttribute('aria-checked', 'false');
                        if (x.textContent === item.a) x.classList.add('ok');
                    });
                    o.setAttribute('aria-checked', 'true');
                    if (v === item.a) { fb.className = 'ksv-fb ok'; fb.textContent = '✓ სწორია! KidSmart-ში ასეთ პასუხებზე მონეტებს მოიგებ.'; }
                    else { o.classList.add('bad'); fb.className = 'ksv-fb bad'; fb.textContent = '✗ თითქმის! სწორი პასუხია ' + item.a + '.'; }
                };
                opts.appendChild(o);
            });
            c.appendChild(num); c.appendChild(q); c.appendChild(opts); c.appendChild(fb);
            return c;
        }
        // ბარათები გვერდიგვერდ დგას — თითით გასრიალებისას ერთი გადის და მეორე შემოდის
        function step() { var c = track.children[0]; return c ? c.getBoundingClientRect().width + 14 : track.clientWidth; }
        function cur() { return Math.round(track.scrollLeft / Math.max(1, step())); }
        function upd() {
            var n = track.children.length, i = Math.min(n - 1, cur());
            prev.disabled = i <= 0; next.disabled = i >= n - 1;
            Array.prototype.forEach.call(dots.children, function (d, k) { d.classList.toggle('on', k === i); });
        }
        prev.onclick = function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); };
        next.onclick = function () { track.scrollBy({ left: step(), behavior: 'smooth' }); };
        track.addEventListener('scroll', function () { window.requestAnimationFrame(upd); }, { passive: true });
        window.addEventListener('resize', upd);
        function render() {
            tabs.innerHTML = '';
            Object.keys(Q).forEach(function (id) {
                var b = document.createElement('button');
                b.type = 'button'; b.className = 'ksv-tab'; b.setAttribute('role', 'tab');
                b.setAttribute('aria-selected', id === theme ? 'true' : 'false');
                b.textContent = Q[id].label;
                b.onclick = function () { theme = id; render(); };
                tabs.appendChild(b);
            });
            var items = Q[theme].items;
            track.innerHTML = ''; dots.innerHTML = '';
            items.forEach(function (it, i) { track.appendChild(card(it, i + 1, items.length)); dots.appendChild(document.createElement('i')); });
            track.style.scrollBehavior = 'auto'; track.scrollLeft = 0; track.style.scrollBehavior = '';
            upd();
        }
        render();
    }

    // ── თამაშების კარუსელი ──
    var gTrack = document.getElementById('ksvGames'), gp = document.getElementById('ksvGPrev'), gn = document.getElementById('ksvGNext');
    if (gTrack) {
        function gStep() { var c = gTrack.children[0]; return c ? c.getBoundingClientRect().width + 16 : gTrack.clientWidth; }
        function gUpd() {
            var max = gTrack.scrollWidth - gTrack.clientWidth - 2;
            gp.disabled = gTrack.scrollLeft <= 2; gn.disabled = gTrack.scrollLeft >= max;
        }
        gp.onclick = function () { gTrack.scrollBy({ left: -gStep(), behavior: 'smooth' }); };
        gn.onclick = function () { gTrack.scrollBy({ left: gStep(), behavior: 'smooth' }); };
        gTrack.addEventListener('scroll', function () { window.requestAnimationFrame(gUpd); }, { passive: true });
        window.addEventListener('resize', gUpd);
        gUpd();
    }
})();
</script>
@endsection
