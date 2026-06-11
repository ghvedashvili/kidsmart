@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
:root {
    --green:  #059669;
    --lg:     #10b981;
    --yellow: #f59e0b;
    --orange: #f97316;
    --dark:   #020917;
    --ink:    #0f172a;
    --muted:  #64748b;
    --bg2:    #f8fafc;
    --spring: cubic-bezier(0.16,1,0.3,1);
}
body {
    font-family: 'Nunito', sans-serif;
    background-color: #fff;
    background-image:
        linear-gradient(rgba(148,163,184,0.13) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148,163,184,0.13) 1px, transparent 1px);
    background-size: 32px 32px;
    padding: 0 !important;
    overflow-x: clip;
    -webkit-font-smoothing: antialiased;
}

/* ── Hero ── */
.hero-mod {
    min-height: 92vh;
    background: linear-gradient(150deg, #020917 0%, #081221 42%, #0c1e14 72%, #030e08 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: 100px 24px 88px;
    position: relative; overflow: hidden;
}
.hero-mod::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 55% 50% at 8% 70%, rgba(16,185,129,0.22) 0%, transparent 58%),
        radial-gradient(ellipse 45% 40% at 92% 15%, rgba(124,58,237,0.18) 0%, transparent 55%),
        radial-gradient(ellipse 35% 32% at 55% 100%, rgba(245,158,11,0.11) 0%, transparent 50%);
}
/* Floating orbs */
.hero-orb {
    position: absolute; border-radius: 50%; pointer-events: none;
    filter: blur(72px); will-change: transform;
}
.hero-orb-1 {
    width: 480px; height: 480px; left: -6%; top: 22%;
    background: radial-gradient(circle, rgba(16,185,129,0.28) 0%, transparent 70%);
    animation: orbFloat 9s ease-in-out infinite;
}
.hero-orb-2 {
    width: 380px; height: 380px; right: -4%; top: 4%;
    background: radial-gradient(circle, rgba(124,58,237,0.22) 0%, transparent 70%);
    animation: orbFloat 11s ease-in-out infinite reverse;
}
.hero-orb-3 {
    width: 280px; height: 280px; left: 38%; bottom: -4%;
    background: radial-gradient(circle, rgba(245,158,11,0.16) 0%, transparent 70%);
    animation: orbFloat 13s ease-in-out infinite 2s;
}
@keyframes orbFloat {
    0%,100% { transform: translateY(0) scale(1); }
    33%      { transform: translateY(-16px) scale(1.04); }
    66%      { transform: translateY(10px) scale(0.97); }
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 10px;
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.28);
    color: #6ee7b7;
    font-family: 'Fredoka One', cursive;
    font-size: 12px; letter-spacing: 0.15em; text-transform: uppercase;
    padding: 8px 22px; border-radius: 99px;
    margin-bottom: 34px;
    backdrop-filter: blur(16px);
    position: relative; z-index: 1;
    box-shadow: 0 0 28px rgba(16,185,129,0.1), inset 0 1px 0 rgba(255,255,255,0.04);
}
.hero-badge-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    background: #10b981; box-shadow: 0 0 10px #10b981;
    animation: dotPulse 2.2s ease-in-out infinite;
}
@keyframes dotPulse { 0%,100%{opacity:1;box-shadow:0 0 10px #10b981} 50%{opacity:0.4;box-shadow:0 0 4px #10b981} }
.hero-h1 {
    font-family: 'Fredoka One', cursive;
    font-size: clamp(2.6rem, 6.5vw, 4.8rem);
    line-height: 1.1; color: #fff;
    margin-bottom: 22px;
    position: relative; z-index: 1;
}
.hero-h1 em {
    font-style: normal;
    background: linear-gradient(125deg, #fbbf24 0%, #f97316 45%, #ec4899 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-sub {
    font-size: clamp(1rem, 2.4vw, 1.18rem); font-weight: 700;
    color: rgba(255,255,255,0.5);
    max-width: 460px; margin: 0 auto 50px; line-height: 1.8;
    position: relative; z-index: 1;
}
.hero-btns {
    display: flex; gap: 14px; flex-wrap: wrap; justify-content: center;
    position: relative; z-index: 1;
}
.btn-main {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff; border: none; border-radius: 16px;
    font-family: 'Fredoka One', cursive; font-size: 1.1rem;
    padding: 15px 36px; cursor: pointer;
    box-shadow: 0 8px 28px rgba(16,185,129,0.45);
    transition: all 0.25s var(--spring); text-decoration: none; display: inline-block;
}
.btn-main:hover { transform: translateY(-3px); box-shadow: 0 16px 44px rgba(16,185,129,0.55); color: #fff; }
.btn-ghost {
    background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.82);
    border: 1px solid rgba(255,255,255,0.15); border-radius: 16px;
    font-family: 'Fredoka One', cursive; font-size: 1.1rem;
    padding: 15px 36px; cursor: pointer;
    transition: all 0.25s; text-decoration: none; display: inline-block;
    backdrop-filter: blur(12px);
}
.btn-ghost:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.25); color: #fff; }
.scroll-cue {
    position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
    color: rgba(255,255,255,0.18); font-size: 20px; z-index: 1;
    animation: cue 2s ease-in-out infinite;
}
@keyframes cue { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(10px)} }

/* ── Section layout ── */
.sec {
    padding: 96px 24px;
    max-width: 1100px; margin: 0 auto;
}
.sec-bg {
    background-color: #f1f5f9;
    background-image:
        linear-gradient(rgba(148,163,184,0.16) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148,163,184,0.16) 1px, transparent 1px);
    background-size: 32px 32px;
}
.sec-bg .sec { max-width: 100%; padding: 96px max(24px, calc(50% - 530px)); }
.eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'Fredoka One', cursive; font-size: 12px;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--green); margin-bottom: 14px;
}
.eyebrow::before {
    content: '';
    width: 18px; height: 2.5px;
    background: linear-gradient(90deg, var(--lg), transparent);
    border-radius: 2px; flex-shrink: 0;
}
.sec-h2 {
    font-family: 'Fredoka One', cursive;
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    color: var(--ink); margin-bottom: 14px; line-height: 1.2;
}
.sec-sub {
    font-size: 15px; color: var(--muted);
    margin-bottom: 0; line-height: 1.75; max-width: 600px; font-weight: 600;
}

/* ── Drag carousel (shared) ── */
.dc {
    display: flex; gap: 16px;
    overflow-x: auto; scroll-snap-type: none;
    padding: 24px 4px 20px; scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    cursor: grab; user-select: none;
}
.dc::-webkit-scrollbar { display: none; }
.dc.dragging { cursor: grabbing; }
/* ── Question cards ── */
.car-outer { margin: 0 -24px; padding: 0 24px; overflow: hidden; }
.ac-outer   { overflow: hidden; }
.mkt-outer  { overflow: hidden; }
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

/* ── Adaptive / Detective drag carousel ── */
.adapt-grid, .detect-grid {
    display: flex; gap: 16px;
    overflow-x: auto; scroll-snap-type: none;
    padding: 8px 4px 20px; scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    cursor: grab; user-select: none;
}
.adapt-grid::-webkit-scrollbar, .detect-grid::-webkit-scrollbar { display: none; }
.adapt-card {
    flex: 0 0 280px; scroll-snap-align: start;
    background: #fff; border-radius: 16px; padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border-left: 4px solid var(--green);
}
.adapt-t { font-family: 'Fredoka One', cursive; font-size: 1.2rem; color: var(--ink); margin-bottom: 8px; }
.adapt-b { font-size: 14px; color: var(--muted); line-height: 1.7; font-weight: 700; }

/* ── Detective ── */
.detect-card {
    flex: 0 0 300px; scroll-snap-align: start;
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

/* ── Market drag carousel ── */
.mkt-track {
    display: flex; gap: 16px;
    overflow-x: auto; scroll-snap-type: none;
    padding: 4px 0 16px; scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    cursor: grab; user-select: none;
}
.mkt-track::-webkit-scrollbar { display: none; }
.mkt-card {
    flex: 0 0 calc(50% - 23px); scroll-snap-align: start;
    background: #fff; border-radius: 16px; padding: 20px 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07); text-align: center;
    transition: transform 0.15s;
}
.mkt-card:hover { transform: translateY(-4px); }
@media (min-width: 641px) { .mkt-card { flex: 0 0 190px; } }

@keyframes cardSlide {
    from { opacity: 0; transform: translateX(40px) scale(0.96); }
    to   { opacity: 1; transform: translateX(0)   scale(1);    }
}
.qc, .adapt-card, .detect-card, .mkt-card { opacity: 0; }
.track-in > .qc,
.track-in > .adapt-card,
.track-in > .detect-card,
.track-in > .mkt-card {
    animation: cardSlide 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.track-in > :nth-child(1) { animation-delay: 0s; }
.track-in > :nth-child(2) { animation-delay: 0.07s; }
.track-in > :nth-child(3) { animation-delay: 0.14s; }
.track-in > :nth-child(4) { animation-delay: 0.21s; }
.track-in > :nth-child(5) { animation-delay: 0.28s; }
.track-in > :nth-child(6) { animation-delay: 0.35s; }
.track-in > :nth-child(7) { animation-delay: 0.42s; }
.mkt-ico   { font-size: 2.2rem; margin-bottom: 8px; }
.mkt-name  { font-family: 'Fredoka One', cursive; font-size: 14px; color: var(--ink); margin-bottom: 6px; line-height: 1.3; }
.mkt-price { font-family: 'Fredoka One', cursive; font-size: 20px; color: var(--green); }

/* ── Scroll reveal ── */
.reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.reveal.in { opacity: 1; transform: translateY(0); }
.reveal:nth-child(2) { transition-delay: 0.08s; }
.reveal:nth-child(3) { transition-delay: 0.16s; }
.reveal:nth-child(4) { transition-delay: 0.24s; }

/* ── Info blocks ── */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-top: 44px; }
@media (max-width: 640px) { .info-grid { grid-template-columns: 1fr; } }
.info-card {
    background: #fff; border-radius: 22px; padding: 28px 24px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.055), 0 0 0 1px rgba(0,0,0,0.04);
    transition: transform 0.25s var(--spring), box-shadow 0.25s;
    position: relative; overflow: hidden;
}
.info-card::after {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--lg), #34d399);
    border-radius: 22px 22px 0 0;
}
.info-card:hover { transform: translateY(-5px); box-shadow: 0 14px 44px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.04); }
.info-card-icon {
    font-size: 1.8rem;
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}
.info-card-grade {
    display: inline-block;
    background: #ecfdf5; color: #059669;
    font-family: 'Fredoka One', cursive;
    font-size: 11px; letter-spacing: 0.08em;
    padding: 4px 12px; border-radius: 99px; margin-bottom: 12px;
    border: 1px solid rgba(5,150,105,0.18);
}
.info-card-h { font-family: 'Fredoka One', cursive; font-size: 1.15rem; color: var(--ink); margin-bottom: 10px; }
.info-card-p { font-size: 14px; color: var(--muted); line-height: 1.75; font-weight: 600; }

/* ── Feature grid (2×2) ── */
.feat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px; margin-top: 44px; }
@media (max-width: 640px) { .feat-grid { grid-template-columns: 1fr; } }
.feat-card {
    background: #fff; border-radius: 22px; padding: 28px 22px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.055), 0 0 0 1px rgba(0,0,0,0.04);
    transition: transform 0.25s var(--spring), box-shadow 0.25s;
    position: relative; overflow: hidden;
}
.feat-card:hover { transform: translateY(-5px); box-shadow: 0 14px 44px rgba(0,0,0,0.1); }
.feat-icon {
    font-size: 1.7rem;
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}
.feat-h { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: var(--ink); margin-bottom: 8px; }
.feat-tag {
    display: inline-block; font-size: 10px; font-weight: 800;
    letter-spacing: 0.1em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 99px; margin-bottom: 12px;
    background: #ecfdf5; color: #059669;
    border: 1px solid rgba(5,150,105,0.18);
}
.feat-p { font-size: 14px; color: var(--muted); line-height: 1.75; font-weight: 600; }

/* ── Pricing ── */
.price-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 48px; }
@media (max-width: 768px) { .price-grid { grid-template-columns: 1fr; } }
.price-card {
    background: #fff; border-radius: 28px; padding: 36px 28px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.055), 0 0 0 1px rgba(0,0,0,0.04);
    display: flex; flex-direction: column;
    position: relative; transition: transform 0.25s var(--spring), box-shadow 0.25s;
}
.price-card:hover { transform: translateY(-6px); box-shadow: 0 20px 60px rgba(0,0,0,0.1); }
.price-card.featured {
    background: linear-gradient(165deg, #f0fdf8 0%, #fff 65%);
    box-shadow: 0 4px 28px rgba(5,150,105,0.18), 0 0 0 2px #059669;
}
.price-card.featured:hover { box-shadow: 0 20px 60px rgba(5,150,105,0.25), 0 0 0 2px #059669; }
.price-badge {
    position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(135deg, var(--lg), var(--green));
    color: #fff; font-family: 'Fredoka One', cursive;
    font-size: 12px; letter-spacing: 0.06em;
    padding: 5px 20px; border-radius: 99px;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(5,150,105,0.4);
}
.price-name { font-family: 'Fredoka One', cursive; font-size: 1.35rem; color: var(--ink); margin-bottom: 4px; }
.price-sub  { font-size: 13px; color: var(--muted); margin-bottom: 20px; font-weight: 700; }
.price-val  { font-family: 'Fredoka One', cursive; font-size: 2.5rem; color: var(--ink); line-height: 1; }
.price-val span { font-size: 1rem; color: var(--muted); font-family: 'Nunito', sans-serif; font-weight: 700; }
.price-divider { height: 1px; background: #f1f5f9; margin: 24px 0; }
.price-features { list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 12px; }
.price-features li { font-size: 14px; color: #475569; display: flex; gap: 9px; align-items: flex-start; font-weight: 700; }
.price-features li::before { content: '✓'; color: var(--green); font-weight: 900; flex-shrink: 0; margin-top: 1px; }
.price-btn {
    display: block; text-align: center; margin-top: auto;
    padding: 14px 0; border-radius: 14px; font-family: 'Fredoka One', cursive;
    font-size: 1rem; cursor: pointer; text-decoration: none; border: none;
    transition: all 0.22s var(--spring);
}
.price-btn-free { background: #f1f5f9; color: #475569; }
.price-btn-free:hover { background: #e2e8f0; color: #1e293b; }
.price-btn-main { background: linear-gradient(135deg, var(--lg) 0%, var(--green) 100%); color: #fff; box-shadow: 0 6px 24px rgba(5,150,105,0.35); }
.price-btn-main:hover { box-shadow: 0 10px 36px rgba(5,150,105,0.5); transform: translateY(-2px); }
.price-btn-year { background: var(--ink); color: #fff; }
.price-btn-year:hover { background: #1e293b; transform: translateY(-2px); }
.save-pill { display: inline-block; background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 99px; margin-left: 8px; vertical-align: middle; }


/* ── CTA ── */
.cta-wrap {
    background: linear-gradient(150deg, #020917 0%, #081221 42%, #0c1e14 72%, #030e08 100%);
    padding: 100px 24px; text-align: center;
    position: relative; overflow: hidden;
}
.cta-wrap::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 55% 50% at 15% 65%, rgba(16,185,129,0.18) 0%, transparent 58%),
        radial-gradient(ellipse 45% 40% at 85% 25%, rgba(124,58,237,0.14) 0%, transparent 55%);
}
.cta-h2 {
    font-family: 'Fredoka One', cursive;
    font-size: clamp(2rem,4.5vw,3.2rem); color: #fff; margin-bottom: 14px; line-height: 1.2;
    position: relative; z-index: 1;
}
.cta-sub { font-size: 16px; color: rgba(255,255,255,0.45); margin-bottom: 40px; font-weight: 700; position: relative; z-index: 1; }

/* ── Footer ── */
.nb-footer {
    background: #020917;
    border-top: 1px solid rgba(255,255,255,0.07);
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 40px;
    gap: 12px;
}
.footer-left { display: flex; align-items: center; gap: 16px; }
.footer-logo { font-family: 'Fredoka One', cursive; font-size: 1rem; color: rgba(255,255,255,0.65); }
.footer-divider { width: 1px; height: 14px; background: rgba(255,255,255,0.12); }
.footer-copy { font-size: 11px; color: rgba(255,255,255,0.22); letter-spacing: 0.04em; margin: 0; }
.footer-right { font-size: 11px; color: rgba(255,255,255,0.18); letter-spacing: 0.04em; }
</style>

{{-- ── Hero ── --}}
<section class="hero-mod">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-orb hero-orb-3"></div>
   
    <h1 class="hero-h1">
        ბავშვი სწავლობს იმ ენაზე,<br>
        <em>რომელიც მას უყვარს.</em>
    </h1>
    <p class="hero-sub">ჩვენ ვქმნით ამოცანებს ბავშვის სამყაროდან — ფეხბურთიდან, კოსმოსიდან, სუპერგმირებიდან.</p>
    <div class="hero-btns">
        <button class="btn-main" onclick="document.getElementById('loginNavBtn')?.click()">
            დაიწყე ახლა ✏️
        </button>
        <a class="btn-ghost" href="#questions" onclick="secScroll(event,'questions')">სცადე ამოცანა ↓</a>
    </div>
    <div class="scroll-cue">↓</div>
</section>

{{-- ── Question cards carousel ── --}}
<div id="questions">
<div class="sec" style="padding-bottom:48px;">
    <div class="eyebrow reveal">მათემატიკა ბავშვის ენაზე</div>
    <h2 class="sec-h2 reveal">ფეხბურთი, კოსმოსი თუ სუპერგმირები?</h2>
    <p class="sec-sub reveal">როცა ამოცანები ბავშვის სამყაროდანაა, სწავლა თავგადასავალია.</p>
    <div class="car-outer">
        <div class="car-track dc" id="qCar"></div>
    </div>
</div>
</div>

{{-- ── Adaptive ── --}}
<div class="sec-bg" id="adaptive">
<div class="sec">
    <div class="eyebrow reveal">ადაპტირებული სწავლება</div>
    <h2 class="sec-h2 reveal">ჭკვიანი სისტემა, რომელიც ბავშვთან ერთად იზრდება</h2>
    <p class="sec-sub reveal">არანაირი გადაწვა ან მოწყენილობა. KidSmart-ის ადაპტური ალგორითმი ავტომატურად აფასებს ბავშვის ცოდნას და სთავაზობს სირთულის 5 დონეს. სისტემა ფეხდაფეხ მიჰყვება მის პროგრესს — ამოცანები რთულდება და ვითარდება ბავშვთან ერთად.</p>
    <div class="ac-outer">
    <div class="adapt-grid" id="adaptTrack">
        <div class="adapt-card">
            <div class="adapt-t">3 ტესტი 95% და მეტი?</div>
            <p class="adapt-b">ავტომატურად შეიცვლება დონე — ბავშვს რომ არ მობეზრდეს.</p>
        </div>
        <div class="adapt-card">
            <div class="adapt-t">ხშირად უშვებს შეცდომებს?</div>
            <p class="adapt-b">ამ თემაში შესაბამის დონის ამოცანებს ვთავაზობთ და ვავითარებთ ბავშვის უნარს.</p>
        </div>
    </div>
    </div>
</div>
</div>

{{-- ── Math Detective ── --}}
<div class="sec" id="detective">
    <div class="eyebrow reveal">მათემატიკური დეტექტივი</div>
    <h2 class="sec-h2 reveal">🕵️‍♂️ გახდი მათემატიკური დეტექტივი!</h2>
    <p class="sec-sub reveal">ეს არ არის უბრალო ტესტები, ეს ნამდვილი გამოძიებაა! ყოველი სწორად ამოხსნილი ამოცანა ბავშვს აახლოებს დიდი საიდუმლოს გახსნასთან. მიეცით თქვენს შვილს შანსი, იგრძნოს თავი ნამდვილ დეტექტივად, სადაც ლოგიკა მისი მთავარი იარაღია.</p>
    <div class="ac-outer">
    <div class="detect-grid" id="detectTrack">
        <div class="detect-card">
            <div style="font-family:'Fredoka One',cursive;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#aaa;margin-bottom:10px;">🔍 გამოძიება #1</div>
            <div style="font-family:'Fredoka One',cursive;font-size:1.25rem;color:var(--ink);margin-bottom:14px;">ვინ მოიგო ოქროს ბურთი?</div>
            <div class="hint-row hint-vis"><span style="min-width:20px;color:var(--green);">1.</span><span>#7 ნომრით თამაშობს</span></div>
            <div class="hint-row hint-vis"><span style="min-width:20px;color:var(--green);">2.</span><span>ქართველია</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">3.</span><span>🔒 ჩაიტვირთება ტესტი 3-ის შემდეგ</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">4.</span><span>🔒 ჩაიტვირთება ტესტი 4-ის შემდეგ</span></div>
            <div class="hint-row hint-lock"><span style="min-width:20px;">5.</span><span>🔒 ჩაიტვირთება ტესტი 5-ის შემდეგ</span></div>
        </div>
        <div class="detect-card">
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
</div>

{{-- ── Block 1: Target audience ── --}}
<div class="sec-bg" id="audience">
<div class="sec">
    <div class="eyebrow reveal">სამიზნე აუდიტორია</div>
    <h2 class="sec-h2 reveal">🎒 ზუსტად იმ ასაკისთვის,<br>როცა ლოგიკა იბადება</h2>
    <p class="sec-sub reveal">KidSmart შექმნილია II, III, IV და V კლასის მოსწავლეებისთვის.</p>
    <div class="info-grid">
        <div class="info-card reveal">
            <div class="info-card-icon">🌱</div>
            <div class="info-card-grade">II კლასი</div>
            <div class="info-card-h">პირველი ნაბიჯები</div>
            <p class="info-card-p">საბაზისო არითმეტიკა, სივრცითი აღქმა და მარტივი ლოგიკური ამოცანები. ბავშვი მათემატიკას შიშის გარეშე სწავლობს.</p>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon">🔍</div>
            <div class="info-card-grade">III კლასი</div>
            <div class="info-card-h">ლოგიკის გამყარება</div>
            <p class="info-card-p">შაბლონები, კრებები და მცირე დეტექტიური ამოცანები. ეს კლასი ლოგიკური აზროვნების საძირკველია.</p>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon">🚀</div>
            <div class="info-card-grade">IV კლასი</div>
            <div class="info-card-h">ნამდვილი გამოწვევები</div>
            <p class="info-card-p">კოორდინატები, საიდუმლო კოდები და ლოგიკური ჯაჭვები. ამოცანები სკოლის პროგრამას სცდება.</p>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon">🏆</div>
            <div class="info-card-grade">V კლასი</div>
            <div class="info-card-h">ელიტური დეტექტივი</div>
            <p class="info-card-p">მულტი-ეტაპური გამოძიებები, სტრატეგიული აზროვნება და კოდები. ყველაზე ამბიციური ამოცანების დონე.</p>
        </div>
    </div>
</div>
</div>

{{-- ── Parent Market ── --}}
<div id="market">
<div class="sec">
    <div class="eyebrow reveal">ჯილდოების სისტემა</div>
    <h2 class="sec-h2 reveal">მშობელი ადგენს მიზანს · ბავშვი ირჩევს გზას</h2>
    <p class="sec-sub reveal">შექმენით თქვენი ოჯახური „საკუთარი მარკეტი“, სადაც თავად დააწესებთ ჯილდოებს (მაგალითად: ველოსიპედით გასეირნება, დამატებითი საათი თამაშისთვის ან საყვარელი ტკბილეული). ყოველ ამოხსნილ ამოცანაში ბავშვი აგროვებს ქულებს და სწავლობს, რომ შრომას ყოველთვის მოაქვს დამსახურებული აღიარება.</p>
    <div class="mkt-outer">
        <div class="mkt-track">
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

{{-- ── Block 3: Gamification ── --}}
<div class="sec-bg" id="gamification">
<div class="sec">
    <div class="eyebrow reveal">გეიმიფიკაცია</div>
    <h2 class="sec-h2 reveal">🏠 ჩემი ვირტუალური ოთახი —<br>ჩემი სამყარო</h2>
    <p class="sec-sub reveal">ყოველი ამოხსნილი ამოცანა შენი ოთახის ახალი აქსესუარია!</p>
    <div class="feat-grid">
        <div class="feat-card reveal">
            <div class="feat-icon">🏅</div>
            <div class="feat-tag">Medals & Trophies</div>
            <div class="feat-h">დიდების კედელი</div>
            <p class="feat-p">ყველა წარმატებული დეტექტიური გამოძიებისთვის ბავშვი იღებს უნიკალურ მედლებსა და თასებს, რომლებსაც თავის ვირტუალურ კედელზე ანთავსებს.</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">🎨</div>
            <div class="feat-tag">Customization</div>
            <div class="feat-h">პერსონალიზაცია სტიკერებით</div>
            <p class="feat-p">დააგროვე ქულები და გახსენი სტიკერები, პოსტერები და ავეჯი შენი საყვარელი თემატიკიდან (ფეხბურთი, კოსმოსი). მოაწყვე ოთახი ისე, როგორც შენ გინდა!</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">🔓</div>
            <div class="feat-tag">Mystery Boxes</div>
            <div class="feat-h">საიდუმლო ყუთები</div>
            <p class="feat-p">გარკვეული დონის მიღწევისას ოთახში ჩნდება საიდუმლო ყუთი, რომლის გახსნაც მხოლოდ ახალი მათემატიკური კოდის გატეხვითაა შესაძლებელი.</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">⭐</div>
            <div class="feat-tag">XP & Levels</div>
            <div class="feat-h">დონეებისა და XP სისტემა</div>
            <p class="feat-p">ყოველი სწორი პასუხი XP ქულებს მატებს. დააგროვე XP, ამაღლდი დონეში და გახსენი ახალი ფუნქციები, რომლებიც მეგობრებს ჯერ არ აქვთ.</p>
        </div>
    </div>
</div>
</div>

{{-- ── Block 2: Parent dashboard ── --}}
<div id="parentdash">
<div class="sec">
    <div class="eyebrow reveal">მშობლის პანელი</div>
    <h2 class="sec-h2 reveal">📊 მართე პროცესი და ადევნე<br>თვალი პროგრესს</h2>
    <p class="sec-sub reveal">შენ აწესებ წესებს, KidSmart აკეთებს ანალიზს.</p>
    <div class="feat-grid">
        <div class="feat-card reveal">
            <div class="feat-icon">🕒</div>
            <div class="feat-tag">Task Manager</div>
            <div class="feat-h">მოქნილი გრაფიკი</div>
            <p class="feat-p">შენ განსაზღვრავ, კვირაში რამდენჯერ ან დღეში რამდენი ტესტი უნდა შეასრულოს ბავშვმა. არანაირი გადატვირთვა — მხოლოდ ჯანსაღი რუტინა.</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">👁️</div>
            <div class="feat-tag">Live Feed</div>
            <div class="feat-h">სრული კონტროლი</div>
            <p class="feat-p">მიიღე მყისიერი წვდომა ყველა შესრულებულ დავალებაზე. ნახე, სად გაუჭირდა, სად გამოიყენა მინიშნება და რა ამოხსნა მარტივად.</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">📈</div>
            <div class="feat-tag">Smart Analytics</div>
            <div class="feat-h">ზრდის დინამიკა</div>
            <p class="feat-p">დეტალური რეპორტები მშობლისთვის. სისტემა გაჩვენებს: „რა სირთულით დაიწყო, სად აქვს პროგრესი და რა დონეზეა ახლა".</p>
        </div>
        <div class="feat-card reveal">
            <div class="feat-icon">🔔</div>
            <div class="feat-tag">Instant Alerts</div>
            <div class="feat-h">მყისიერი შეტყობინება</div>
            <p class="feat-p">მიიღე Push-შეტყობინება, როგორც კი ბავშვი ტესტს დაასრულებს. ყოველთვის იცი, რა ხდება — სადაც არ უნდა იყო.</p>
        </div>
    </div>
</div>
</div>



{{-- ── Block 4: Pricing ── --}}
<div class="sec-bg" id="pricing">
<div class="sec">
    <div class="eyebrow reveal">ტარიფები</div>
    <h2 class="sec-h2 reveal">💎 აირჩიე შენზე მორგებული პაკეტი</h2>
    <p class="sec-sub reveal">დაიწყე უფასოდ, გადადი პრემიუმზე, როცა მზად იქნები.</p>
    <div class="price-grid">
        <div class="price-card reveal">
            <div class="price-name">სცადე</div>
            <div class="price-sub">Free</div>
            <div class="price-val">0 ₾ <span>/ მუდამ უფასო</span></div>
            <div class="price-divider"></div>
            <ul class="price-features">
                <li>1 საბაზისო თემატიკა</li>
                <li>დღეში 1 ტესტი</li>
                <li>მარტივი სტატისტიკა</li>
            </ul>
            <button class="price-btn price-btn-free" onclick="document.getElementById('loginNavBtn')?.click()">დაიწყე უფასოდ</button>
        </div>
        <div class="price-card featured reveal">
            <div class="price-badge">✨ ყველაზე პოპულარული</div>
            <div class="price-name">დეტექტივი</div>
            <div class="price-sub">Premium</div>
            <div class="price-val">XX ₾ <span>/ თვეში</span></div>
            <div class="price-divider"></div>
            <ul class="price-features">
                <li>ყველა თემატიკა (ფეხბურთი, კოსმოსი…)</li>
                <li>ლიმიტის გარეშე ტესტები და მინიშნებები</li>
                <li>მშობლის სრული ანალიტიკა და გრაფიკები</li>
                <li>ვირტუალური ოთახის სრული წვდომა</li>
            </ul>
            <button class="price-btn price-btn-main" onclick="document.getElementById('loginNavBtn')?.click()">გახდი პრემიუმი</button>
        </div>
        <div class="price-card reveal">
            <div class="price-name">ჩემპიონი</div>
            <div class="price-sub">Yearly <span class="save-pill">დაზოგე 30%</span></div>
            <div class="price-val">XX ₾ <span>/ წელიწადში</span></div>
            <div class="price-divider"></div>
            <ul class="price-features">
                <li>პრემიუმის ყველა ფუნქცია მთელი წლის განმავლობაში</li>
                <li>პერსონალური სერტიფიკატი წლის ბოლოს</li>
                <li>პრიორიტეტული მხარდაჭერა</li>
            </ul>
            <button class="price-btn price-btn-year" onclick="document.getElementById('loginNavBtn')?.click()">დაზოგე ახლავე</button>
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
    <div class="footer-left">
        <span class="footer-logo">KidSmart</span>
        <!-- <span class="footer-divider"></span>
        <p class="footer-copy">© 2025 KidSmart · საქართველო</p> -->
    </div>
    <span class="footer-right">Developed by Ghvedashvili</span>
</footer>

<script>
if (history.scrollRestoration) history.scrollRestoration = 'manual';
window.addEventListener('load', function() { window.scrollTo(0, 0); });

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

(function() {
    var tracks = document.querySelectorAll('#qCar, #adaptTrack, #detectTrack, .mkt-track');
    if (!('IntersectionObserver' in window)) {
        tracks.forEach(function(t) { t.classList.add('track-in'); });
        return;
    }
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (!e.isIntersecting) return;
            e.target.classList.add('track-in');
            obs.unobserve(e.target);
        });
    }, { threshold: 0.08 });
    tracks.forEach(function(t) { obs.observe(t); });
})();

// Drag-to-scroll with momentum for all carousels
// Drag-to-scroll with desktop momentum, click prevention, and CSS Snap handling
(function() {
    var sel = '.dc, .adapt-grid, .detect-grid, .mkt-track';
    document.querySelectorAll(sel).forEach(function(el) {
        var raf, vel = 0;
        var isDragging = false;

        function momentum() {
            if (Math.abs(vel) < 0.3) return;
            el.scrollLeft += vel;
            vel *= 0.94;
            raf = requestAnimationFrame(momentum);
        }

        // ── Mouse Events ──
        var down = false, mX, mSL, mLastX, mLastT;
        
        el.addEventListener('mousedown', function(e) {
            cancelAnimationFrame(raf); 
            vel = 0;
            down = true; 
            isDragging = false;
            el.classList.add('dragging');
            
            mX = mLastX = e.pageX; 
            mSL = el.scrollLeft; 
            mLastT = Date.now();
        });

        document.addEventListener('mouseup', function(e) {
            if (!down) return;
            down = false; 
            el.classList.remove('dragging');
            
            if (isDragging) requestAnimationFrame(momentum);
        });

        el.addEventListener('mousemove', function(e) {
            if (!down) return;
            
            var deltaX = Math.abs(e.pageX - mX);
            if (deltaX > 4) {
                isDragging = true;
            }

            var now = Date.now();
            var dt = Math.max(now - mLastT, 1);
            
            // ვანგარიშობთ სიჩქარეს
            vel = -(e.pageX - mLastX) / dt * 15; 
            
            el.scrollLeft = mSL - (e.pageX - mX);
            mLastX = e.pageX; 
            mLastT = now;
            e.preventDefault(); 
        });

        // კლიკის ბლოკირება, თუ მომხმარებელი რეალურად სქროლავდა
        el.addEventListener('click', function(e) {
            if (isDragging) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // ── Touch Events ──
        var tX, tY, tSL, tLastX, tLastT, tDir;
        el.addEventListener('touchstart', function(e) {
            cancelAnimationFrame(raf); vel = 0;
            tX = tLastX = e.touches[0].clientX;
            tY = e.touches[0].clientY;
            tSL = el.scrollLeft;
            tLastT = Date.now();
            tDir = null;
        }, { passive: true });

        el.addEventListener('touchmove', function(e) {
            var x = e.touches[0].clientX;
            var y = e.touches[0].clientY;
            if (tDir === null) {
                var dx = Math.abs(x - tX), dy = Math.abs(y - tY);
                if (dx < 4 && dy < 4) return;
                tDir = dx >= dy ? 'h' : 'v';
            }
            if (tDir !== 'h') return;
            e.preventDefault();
            var now = Date.now(), dt = Math.max(now - tLastT, 1);
            vel = -(x - tLastX) / dt * 15;
            el.scrollLeft = tSL - (x - tX);
            tLastX = x; tLastT = now;
        }, { passive: false });

        el.addEventListener('touchend', function() {
            if (tDir === 'h') requestAnimationFrame(momentum);
        }, { passive: true });
    });
})();
</script>
@endsection
