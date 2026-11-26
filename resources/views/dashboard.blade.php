@extends('layouts.app')

@push('head')
@if(auth()->user()->role === 'child')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endif
@endpush

@section('content')
<style>
    body {
        background: transparent !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        overflow-x: hidden;
        min-height: 100dvh;
    }
    .dash-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        position: relative;
        overflow: hidden;
        padding: 28px 24px 60px;
        gap: 20px;
        --primary: #F07A2E;
        --primary-light: #FFE3CC;
        --primary-soft: #FFF3EA;
        --accent-yellow: #fdcb6e;
        --accent-pink: #ff7675;
        --accent-green: #55efc4;
        --accent-blue: #FFC49A;
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 12px;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.07) 1px, transparent 1px);
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
        gap: 20px;
        width: 100%;
        max-width: 480px;
        text-align: center;
    }
    /* ── hero banners ── */
    .parent-hero-banner {
        width: 100%; box-sizing: border-box; border-radius: var(--radius-lg); padding: 22px 24px;
        display: flex; flex-direction: column; align-items: flex-start; gap: 14px; text-align: left;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        box-shadow: 0 10px 24px rgba(108,92,231,0.25);
    }
    .parent-hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; width: 100%; }
    .parent-hero-text { flex: 1; min-width: 0; }
    .parent-hero-text h2 { font-family: 'Goldman', monospace; font-size: 0.95rem; color: rgba(255,255,255,0.85); margin: 0; letter-spacing: 0.02em; }
    .parent-hero-name {
        font-family: 'Goldman', monospace; font-size: 1.3rem; color: #fff; margin-top: 4px; letter-spacing: 0.02em;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .plan-pill {
        display: flex; align-items: center; gap: 7px; width: 100%; box-sizing: border-box;
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);
        border-radius: 100px; padding: 8px 14px; font-family: 'Goldman', monospace;
        font-size: 0.66rem; color: #fff; cursor: pointer; letter-spacing: 0.03em; transition: background 0.2s;
    }
    .plan-pill:hover { background: rgba(255,255,255,0.28); }


    .children-section { width: 100%; }
    .section-label {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; text-align: left;
    }
    .add-mini-btn {
        display: flex; align-items: center; gap: 6px; flex-shrink: 0; align-self: flex-start;
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); color: #fff;
        border-radius: 100px; padding: 7px 14px; font-family: 'Goldman', monospace; font-size: 0.62rem; letter-spacing: 0.03em;
        cursor: pointer; transition: background 0.2s;
    }
    .add-mini-btn:hover { background: rgba(255,255,255,0.28); }
    .add-card {
        background: var(--primary-soft); border: 2px dashed #FFE3CC; border-radius: var(--radius-lg);
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 140px; padding: 20px 10px; cursor: pointer;
        font-family: inherit; color: var(--primary); transition: all 0.2s;
    }
    .add-card:hover { border-color: var(--primary-light); background: #FFF3EA; }
    .add-card-plus {
        width: 38px; height: 38px; border-radius: 50%; border: 2px dashed var(--primary-light);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; line-height: 1; color: var(--primary); transition: all 0.2s;
    }
    .add-card:hover .add-card-plus { border-color: var(--primary); }
    .add-card-label { font-family: 'Goldman', monospace; font-size: 0.62rem; letter-spacing: 0.04em; text-align: center; }

    /* ── child card (parent view) — profile-card design ── */
    .children-grid { display: grid; grid-template-columns: 1fr; gap: 16px; width: 100%; }
    .child-card {
        background: #fff; border-radius: 24px;
        display: flex; flex-direction: column; gap: 14px;
        padding: 20px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
        border: 1px solid #F1EFEB;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .child-card:hover { box-shadow: 0 10px 28px rgba(0,0,0,0.1); transform: translateY(-2px); }

    /* ── mobile: tinted header band (photo+name+ID) + light stat row + two equal buttons ── */
    .cc-mcard {
        display: flex; flex-direction: column; gap: 14px; position: relative; cursor: pointer;
        width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;
    }
    @media (min-width: 700px) { .cc-mcard { display: none; } }

    .cc-mheader {
        display: flex; align-items: center; gap: 14px; position: relative;
        border-radius: 18px; padding: 14px 48px 14px 14px; box-sizing: border-box;
        background: linear-gradient(135deg, var(--primary-soft), #FFF3EA);
    }
    .cc-mheader.boy  { background: linear-gradient(135deg, #FFF7F0, #FFF3EA); }
    .cc-mheader.girl { background: linear-gradient(135deg, #FFF3EA, #FFF3EA); }
    .cc-mavatar {
        width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0; position: relative;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        padding: 2px; border: none; cursor: pointer;
    }
    .cc-mheader.boy  .cc-mavatar { background: linear-gradient(135deg, #E2590F, #FFC49A); }
    .cc-mheader.girl .cc-mavatar { background: linear-gradient(135deg, #F07A2E, #FFE3CC); }
    .cc-mavatar-inner {
        width: 100%; height: 100%; border-radius: 50%; background: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
    }
    .cc-mheader-info { flex: 1; min-width: 0; }
    .cc-mheader-name {
        font-family: 'Goldman', monospace; font-size: 1.05rem; color: #2B2622;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cc-mheader-code {
        display: inline-flex; margin-top: 6px;
        font-family: 'Goldman', monospace; font-size: 0.62rem; font-weight: 700; color: #E2590F;
        background: rgba(255,255,255,0.8); border-radius: 8px; padding: 3px 9px; letter-spacing: 0.04em; cursor: pointer;
    }
    .cc-mheader-edit {
        position: absolute; top: 12px; right: 12px; z-index: 1;
        width: 30px; height: 30px; border-radius: 10px; border: none;
        background: rgba(255,255,255,0.65); color: #E2590F;
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem; cursor: pointer;
    }

    .cc-mstat-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .cc-mstat {
        display: flex; align-items: center; gap: 7px; min-width: 0;
        background: #F7F6F3; border-radius: 12px; padding: 9px 11px;
    }
    .cc-mstat-icon { font-size: 0.95rem; flex-shrink: 0; }
    .cc-mstat-val {
        font-family: 'Goldman', monospace; font-size: 0.7rem; font-weight: 700; color: #4F473F;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
    }
    .cc-mstat.purple  { background: #FFF3EA; } .cc-mstat.purple  .cc-mstat-val { color: #E2590F; }
    .cc-mstat.blue    { background: #FFF7F0; } .cc-mstat.blue    .cc-mstat-val { color: #E2590F; }
    .cc-mstat.amber   { background: #fffbeb; } .cc-mstat.amber   .cc-mstat-val { color: #b45309; }
    .cc-mstat.emerald { background: #ecfdf5; } .cc-mstat.emerald .cc-mstat-val { color: #047857; }

    .cc-mfull-row { display: flex; justify-content: flex-end; }
    @media (min-width: 700px) { .cc-mfull-row { display: none; } }
    .cc-mfull-link {
        display: inline-flex; align-items: center; gap: 5px;
        font-family: 'Goldman', monospace; font-size: 0.68rem; font-weight: 700; color: var(--primary);
        text-decoration: none; padding: 2px 0;
    }

    /* ── desktop: full-height photo panel on the left ── */
    .cc-layout { display: flex; gap: 16px; align-items: stretch; }

    .cc-photo {
        display: none;
        flex-shrink: 0; width: 76px; border-radius: 18px; position: relative;
        align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--primary-soft), #FFF3EA);
        border: none; cursor: pointer; padding: 0; transition: transform 0.15s;
    }
    .cc-photo:hover { transform: scale(1.02); }
    .cc-photo.boy  { background: linear-gradient(135deg, #FFF7F0, #FFF3EA); }
    .cc-photo.girl { background: linear-gradient(135deg, #FFF3EA, #FFF3EA); }
    @media (min-width: 700px) { .cc-photo { display: flex; width: 150px; } }

    .cc-avatar-ring {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        padding: 2px; display: flex; align-items: center; justify-content: center;
    }
    .cc-photo.boy  .cc-avatar-ring { background: linear-gradient(135deg, #E2590F, #FFC49A); }
    .cc-photo.girl .cc-avatar-ring { background: linear-gradient(135deg, #F07A2E, #FFE3CC); }
    @media (min-width: 700px) { .cc-avatar-ring { width: 88px; height: 88px; } }

    .cc-avatar-inner {
        width: 100%; height: 100%; border-radius: 50%; background: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.7rem;
    }
    @media (min-width: 700px) { .cc-avatar-inner { font-size: 2.6rem; } }
    .cc-avatar-picker-edit {
        position: absolute; bottom: 8px; right: 8px; width: 24px; height: 24px; border-radius: 50%;
        background: #fff; color: #4F473F; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 1px 4px rgba(0,0,0,0.25); border: 1px solid #ECE9E4;
    }

    .cc-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: 14px; }
    .cc-header-row { display: none; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    @media (min-width: 700px) { .cc-header-row { display: flex; } }
    /* the desktop body's own mini-grid/buttons — mobile shows the .cc-mcard version instead */
    .cc-body .cc-mini-grid,
    .cc-body .cc-btn-row,
    .cc-body .cc-full-btn-secondary { display: none; }
    @media (min-width: 700px) {
        .cc-body .cc-mini-grid { display: grid; }
        .cc-body .cc-btn-row { display: flex; }
        .cc-body .cc-full-btn-secondary { display: flex; }
    }
    .cc-name { font-family: 'Goldman', monospace; font-size: 1.2rem; color: #2B2622; letter-spacing: 0.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .cc-code {
        display: inline-flex; flex-shrink: 0;
        font-family: 'Goldman', monospace; font-size: 0.64rem; font-weight: 700; color: #6B625A;
        background: #F1EFEB; border: 1px solid #ECE9E4; border-radius: 10px; padding: 5px 10px;
        letter-spacing: 0.05em; cursor: pointer; white-space: nowrap; transition: background 0.15s, color 0.15s;
    }
    .cc-code:hover { background: #ECE9E4; color: #4F473F; }

    .cc-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media (min-width: 700px) { .cc-mini-grid { grid-template-columns: repeat(4, 1fr); } }
    .cc-mini {
        display: flex; align-items: center; gap: 10px; min-width: 0;
        border-radius: 16px; padding: 10px 12px; border: 1px solid; transition: transform 0.15s;
    }
    .cc-mini:hover { transform: translateY(-2px); }
    .cc-mini-icon { width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .cc-mini-text { min-width: 0; overflow: hidden; }
    .cc-mini-label { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.56rem; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; }
    .cc-mini-value { font-family: 'Goldman', monospace; font-size: 0.78rem; font-weight: 700; margin: 1px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cc-mini.purple  { background: #FFF3EA; border-color: #FFF3EA; } .cc-mini.purple  .cc-mini-icon { background: #FFF3EA; color: #F07A2E; } .cc-mini.purple  .cc-mini-label { color: #FFC49A; } .cc-mini.purple  .cc-mini-value { color: #C24A0A; }
    .cc-mini.blue    { background: #FFF7F0; border-color: #FFF3EA; } .cc-mini.blue    .cc-mini-icon { background: #FFF3EA; color: #C24A0A; } .cc-mini.blue    .cc-mini-label { color: #F07A2E; } .cc-mini.blue    .cc-mini-value { color: #C24A0A; }
    .cc-mini.amber   { background: #fffbeb; border-color: #fef3c7; } .cc-mini.amber   .cc-mini-icon { background: #fef3c7; color: #d97706; } .cc-mini.amber   .cc-mini-label { color: #f59e0b; } .cc-mini.amber   .cc-mini-value { color: #78350f; }
    .cc-mini.emerald { background: #ecfdf5; border-color: #d1fae5; } .cc-mini.emerald .cc-mini-icon { background: #d1fae5; color: #059669; } .cc-mini.emerald .cc-mini-label { color: #34d399; } .cc-mini.emerald .cc-mini-value { color: #064e3b; }

    .cc-btn-row { display: flex; gap: 8px; }
    .cc-full-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px; flex: 1; box-sizing: border-box;
        background: var(--primary); color: #fff; border: none; border-radius: 16px;
        padding: 13px 14px; font-family: 'Goldman', monospace; font-size: 0.7rem; letter-spacing: 0.02em;
        text-decoration: none; cursor: pointer; transition: background 0.15s, transform 0.1s; position: relative;
        box-shadow: 0 2px 8px rgba(108,92,231,0.25);
    }
    .cc-full-btn:hover { background: var(--primary-light); color: #fff; }
    .cc-full-btn:active { transform: scale(0.98); }
    .cc-full-btn-badge {
        background: #dc2626; color: #fff; font-family: 'Goldman', monospace; font-size: 0.55rem; font-weight: 700;
        border-radius: 100px; padding: 2px 7px; min-width: 16px; text-align: center;
    }
    .cc-mini-btn {
        width: 44px; height: 44px; border-radius: 16px; flex-shrink: 0; border: 1px solid #ECE9E4;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; background: #F1EFEB; color: #4F473F; cursor: pointer; transition: background 0.15s;
    }
    .cc-mini-btn:hover { background: #ECE9E4; }
    .cc-mini-btn:active { transform: scale(0.98); }

    .cc-full-btn-secondary {
        display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box;
        background: #F1EFEB; color: #4F473F; border: 1px solid #ECE9E4; border-radius: 16px;
        padding: 13px 14px; font-family: 'Goldman', monospace; font-size: 0.7rem; letter-spacing: 0.02em;
        cursor: pointer; transition: background 0.15s;
    }
    .cc-full-btn-secondary:hover { background: #ECE9E4; }
    .cc-full-btn-secondary:active { transform: scale(0.98); }

    .cc-share-panel {
        display: none; flex-direction: column; gap: 8px;
        background: #F7F6F3; border-radius: 14px; padding: 12px;
        margin-top: -8px;
    }
    .cc-share-panel.open { display: flex; }
    .cc-share-link-box {
        background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 8px 10px; overflow: hidden;
    }
    .cc-share-link-text {
        display: block; font-family: 'Goldman', monospace; font-size: 0.6rem; color: #666;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cc-share-actions { display: flex; gap: 8px; }
    .cc-share-btn {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
        background: var(--primary-soft); color: var(--primary); border: none; border-radius: 100px;
        padding: 9px 8px; font-family: 'Goldman', monospace; font-size: 0.62rem;
        cursor: pointer; transition: background 0.15s; letter-spacing: 0.02em;
    }
    .cc-share-btn:hover { background: #FFF3EA; }
    .cc-share-btn.copied { background: #dcfce7; color: #15803d; }



    /* ── quick-link tiles (admin/staff nav) ── */
    .nav-tile-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; }
    .nav-tile {
        background: #fff; border: 1px solid #eee; border-radius: var(--radius-md);
        padding: 16px 10px; display: flex; flex-direction: column; align-items: center; gap: 6px;
        text-decoration: none; transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    }
    .nav-tile:hover { border-color: var(--primary-light); box-shadow: 0 4px 14px rgba(108,92,231,0.12); transform: translateY(-2px); }
    .nav-tile-icon {
        font-size: 1.2rem; width: 40px; height: 40px; border-radius: var(--radius-sm);
        background: var(--primary-soft); display: flex; align-items: center; justify-content: center;
    }
    .nav-tile-label { font-family: 'Goldman', monospace; font-size: 0.62rem; color: #555; letter-spacing: 0.02em; text-align: center; }

    /* ── mission card (today's action, restyled) ── */
    .mission-card {
        width: 100%; box-sizing: border-box; border-radius: var(--radius-lg); padding: 30px 20px;
        min-height: 200px; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden;
        background-image:
            linear-gradient(90deg, rgba(255,247,237,0.94) 0%, rgba(255,247,237,0.8) 38%, rgba(255,247,237,0.05) 62%),
            url('/img/mission-hero.jpg');
        background-size: cover;
        background-position: right center;
        background-repeat: no-repeat;
        text-align: left; box-shadow: 0 10px 24px rgba(217,150,60,0.25);
    }
    .mission-badge {
        display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.85);
        color: #2d2d3a; font-family: 'Goldman', monospace; font-size: 0.6rem; letter-spacing: 0.04em;
        border-radius: 100px; padding: 5px 12px; margin-bottom: 12px; align-self: flex-start;
    }
    .mission-card.resume .mission-badge { color: #b45309; background: rgba(255,255,255,0.9); }
    .mission-title { font-family: 'Fredoka One', cursive; font-size: 1.2rem; color: #2B2622; margin-bottom: 6px; line-height: 1.25; max-width: 60%; }
    .mission-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.78rem; color: #4b4b63; margin-bottom: 14px; max-width: 58%; }
    .mission-cta {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; align-self: flex-start; width: auto; box-sizing: border-box;
        background: #2B2622; color: #fff; font-family: 'Fredoka One', cursive; font-size: 1rem;
        padding: 13px 22px; border-radius: 16px; text-decoration: none; box-shadow: 0 6px 16px rgba(0,0,0,0.15); transition: transform 0.15s;
    }
    .mission-cta:hover { transform: translateY(-2px); color: #fff; }

    /* ── 4-card stat grid ── */
    .stat-grid-4 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; }
    @media (min-width: 520px) { .stat-grid-4 { grid-template-columns: repeat(4, 1fr); } }
    .stat-card-4 {
        background: #fff;
        border-radius: var(--radius-md); padding: 14px; text-align: left;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1.5px solid #f5f5f5; text-decoration: none; color: inherit;
        display: flex; position: relative; overflow: hidden; min-height: 100px;
    }
    .stat-card-4-content { position: relative; z-index: 1; max-width: 66%; display: flex; flex-direction: column; gap: 8px; }
    .stat-card-4-icon-img {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 65px;
        object-fit: contain; z-index: 0; pointer-events: none;
    }
    .stat-card-4-head { display: flex; align-items: center; gap: 8px; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.6rem; color: #999; text-transform: uppercase; letter-spacing: 0.02em; }
    .stat-card-4-val { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #2B2622; }
    .stat-card-4-link { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.58rem; color: var(--primary); }


    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        backdrop-filter: blur(4px); z-index: 1000;
        display: none; flex-direction: column; align-items: center;
        justify-content: flex-start; padding: 80px 16px 48px;
        overflow-y: auto;
    }
    .modal-overlay.open { display: flex; }
    .mbox {
        background: #fff; border-radius: 16px; padding: 28px 24px;
        width: 100%; max-width: 440px; flex-shrink: 0;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        animation: modalIn 0.25s cubic-bezier(0.175,0.885,0.32,1.275);
    }
    @keyframes modalIn { from { transform: scale(0.92); opacity:0; } to { transform: scale(1); opacity:1; } }
    .modal-title {
        font-family: 'Goldman', monospace; font-size: 0.9rem; color: #111;
        letter-spacing: 0.08em; margin-bottom: 22px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-close { background: none; border: none; color: #bbb; font-size: 1.1rem; cursor: pointer; padding: 0; }
    .modal-close:hover { color: #555; }
    .mlbl {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #999;
        letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px;
    }
    .mlbl span { color: #e74c3c; margin-left: 2px; }
    .minput {
        width: 100%; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 8px;
        font-family: 'Goldman', monospace; font-size: 0.82rem; color: #111;
        padding: 10px 14px; outline: none; margin-bottom: 16px; box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .minput:focus { border-color: #aaa; background: #fff; }
    .mrow { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .mchip {
        background: #f5f5f5; border: 1.5px solid #e8e8e8; border-radius: 6px;
        font-family: 'Goldman', monospace; font-size: 0.7rem; color: #888;
        padding: 6px 14px; cursor: pointer; transition: all 0.15s; user-select: none;
    }
    .mchip:hover { border-color: #bbb; color: #444; }
    .mchip.sel { background: #C24A0A; border-color: #111; color: #fff; }
    .mchip.sel-green { background: #e8f5e9; border-color: #81c784; color: #2e7d32; }
    .msave {
        width: 100%; background: #C24A0A; border: none; border-radius: 8px;
        font-family: 'Goldman', monospace; font-size: 0.82rem; color: #fff;
        padding: 13px; cursor: pointer; letter-spacing: 0.06em; margin-top: 6px;
        transition: background 0.2s;
    }
    .msave:hover { background: #333; }
    .msave-danger {
        background: transparent; border: 1px solid #ddd; color: #888;
    }
    .msave-danger:hover { border-color: #e74c3c; color: #e74c3c; background: transparent; }
    .merr { font-family: 'Goldman', monospace; font-size: 0.65rem; color: #e74c3c; margin-top: -12px; margin-bottom: 10px; }

    /* ── multi-step wizard (add child) ── */
    .wiz-step { display: none; }
    .wiz-step.active { display: block; animation: wizIn 0.22s ease; }
    @keyframes wizIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
    .wiz-step-title {
        font-family: 'Goldman', monospace; font-size: 0.92rem; color: #111;
        letter-spacing: 0.02em; margin-bottom: 16px;
    }

    .wiz-nav { display: flex; gap: 8px; margin-top: 6px; }
    .wiz-btn {
        flex: 1; border-radius: 8px; font-family: 'Goldman', monospace; font-size: 0.82rem;
        padding: 13px; cursor: pointer; letter-spacing: 0.06em; transition: all 0.2s;
    }
    .wiz-btn-prev { background: transparent; border: 1px solid #ddd; color: #888; }
    .wiz-btn-prev:hover { border-color: #aaa; color: #444; }
    .wiz-btn-next, .wiz-btn-submit { background: #C24A0A; border: none; color: #fff; }
    .wiz-btn-next:hover, .wiz-btn-submit:hover { background: #333; }
    .caction {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        border: 1px solid #ebebeb; border-radius: 4px; padding: 5px 10px;
        text-decoration: none; letter-spacing: 0.04em; transition: all 0.2s; white-space: nowrap;
    }
    .caction:hover { color: #333; border-color: #aaa; }
    .caction.primary { color: #C24A0A; border-color: #FFD9BF; background: #FFF3EA; }
    .caction.primary:hover { background: #FFE3CC; border-color: #FFC49A; }
    .caction.remind { color: #059669; border-color: #a7f3d0; background: #ecfdf5; }
    .caction.remind:hover { background: #d1fae5; border-color: #6ee7b7; }
    .remind-modal { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:1000;display:none;flex-direction:column;align-items:center;justify-content:flex-start;padding:80px 16px 48px;overflow-y:auto; }
    .remind-modal.open { display:flex; }
    .remind-box { background:#fff;border-radius:16px;padding:24px;width:100%;max-width:400px;flex-shrink:0;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.175,0.885,0.32,1.275); }
    .remind-title { font-family:'Goldman',monospace;font-size:0.88rem;color:#111;letter-spacing:0.06em;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between; }
    .remind-textarea { width:100%;border:1px solid #e8e8e8;border-radius:8px;padding:10px 12px;font-family:'Goldman',monospace;font-size:0.8rem;color:#333;resize:none;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:#fafafa; }
    .remind-textarea:focus { border-color:#aaa;background:#fff; }
    .remind-hint { font-family:'Goldman',monospace;font-size:0.58rem;color:#ccc;margin:6px 0 14px;letter-spacing:0.04em; }
    .remind-send { width:100%;background:#059669;border:none;border-radius:8px;color:#fff;font-family:'Goldman',monospace;font-size:0.82rem;letter-spacing:0.06em;padding:12px;cursor:pointer;transition:background 0.2s; }
    .remind-send:hover { background:#047857; }
    .no-children {
        font-family: 'Goldman', monospace; font-size: 0.72rem; color: #ccc;
        text-align: center; padding: 20px;
        border: 1px dashed #e0e0e0; border-radius: 8px; letter-spacing: 0.06em;
    }

    .notif-btn {
        display: inline-flex; align-items: center; gap: 10px; padding: 11px 28px;
        font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em;
        color: #888; background: transparent; border: 1px solid #ddd; border-radius: 4px;
        cursor: pointer; transition: color 0.2s, border-color 0.2s;
    }
    .notif-btn:hover { color: #333; border-color: #aaa; }
    .notif-btn.on { color: #111; border-color: #111; }
    .dash-toast-wrap {
        position: fixed; top: 0; left: 50%; transform: translate(-50%, -130%);
        z-index: 2000; display: flex; flex-direction: column; gap: 8px; align-items: center;
        width: 100%; max-width: 420px; padding: 14px 16px 0; box-sizing: border-box;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }
    .dash-toast-wrap.show { transform: translate(-50%, 0); }
    .dash-toast {
        font-family: 'Goldman', monospace; font-size: 0.7rem; letter-spacing: 0.03em;
        background: #fff; border-radius: 14px; padding: 12px 20px; text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18); max-width: 100%;
    }
    .dash-toast.ok   { color: #15803d; }
    .dash-toast.err  { color: #dc2626; }
    .dash-toast.warn { color: #b45309; }
    /* ── child view ── */
    .mini-cards-row { display: flex; flex-direction: column; gap: 10px; width: 100%; margin-top: 12px; }
    @media (min-width: 520px) { .mini-cards-row { display: grid; grid-template-columns: 1fr 1fr; } }

    .games-card, .oly-card {
        display: flex; align-items: center; gap: 14px; width: 100%; box-sizing: border-box;
        min-height: 110px; position: relative;
        border-radius: var(--radius-lg);
        padding: 16px 18px; text-decoration: none; text-align: left;
        transition: transform 0.15s;
    }
    .games-card { background: linear-gradient(160deg, #ecfdf5, #d1fae5); color: #14532d; box-shadow: 0 8px 20px rgba(22,163,74,0.15); }
    .games-card:hover { transform: translateY(-2px); color: #14532d; }
    .games-card-icon { font-size: 2.2rem; flex-shrink: 0; }
    .games-card-text { flex: 1; min-width: 0; }
    .games-card-title { font-family: 'Fredoka One', cursive; font-size: 1rem; margin-bottom: 2px; color: #14532d; }
    .games-card-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.7rem; color: #166534; opacity: 0.85; }
    .games-card-arrow { font-size: 1.1rem; flex-shrink: 0; opacity: 0.85; }

    .oly-card { background: linear-gradient(160deg, #fffbeb, #fde68a); color: #78350f; box-shadow: 0 8px 20px rgba(217,119,6,0.18); }
    .oly-card:hover { transform: translateY(-2px); color: #78350f; }
    .oly-card.locked { opacity: 0.55; filter: grayscale(0.35); }
    .oly-card-icon { font-size: 2.2rem; flex-shrink: 0; }
    .oly-card-text { flex: 1; min-width: 0; }
    .oly-card-title { font-family: 'Fredoka One', cursive; font-size: 1rem; margin-bottom: 2px; color: #78350f; }
    .oly-card-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.7rem; color: #92400e; opacity: 0.9; }
    .oly-card-arrow { font-size: 1.1rem; flex-shrink: 0; opacity: 0.85; }


    /* ── responsive widths + multi-column grids on desktop (kept last so it wins the cascade) ── */
    @media (min-width: 760px) {
        .dash-inner { max-width: 700px; }
        .nav-tile-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (min-width: 1040px) {
        .dash-inner { max-width: 960px; }
        .nav-tile-grid { grid-template-columns: repeat(6, 1fr); }
    }

    /* ── mobile: compact child dashboard, scrolls naturally with breathing room at the bottom ── */
    @media (max-width: 759px) {
        .child-dash-fit {
            min-height: calc(100dvh - var(--nav-h, 56px));
            padding: 28px 16px 100px;
            justify-content: flex-start;
            gap: 8px;
        }
        .child-dash-fit .dash-inner { justify-content: flex-start; gap: 10px; }
        .child-dash-fit .mission-card { min-height: 0; padding: 16px 16px; justify-content: center; }
        .child-dash-fit .mission-title { font-size: 1.05rem; margin-bottom: 4px; }
        .child-dash-fit .mission-sub { font-size: 0.72rem; margin-bottom: 10px; }
        .child-dash-fit .mission-cta { padding: 11px 20px; font-size: 0.92rem; }
        .child-dash-fit .stat-grid-4 { flex: 1 1 auto; gap: 8px; }
        .child-dash-fit .stat-card-4 { min-height: 0; padding: 10px; }
        .child-dash-fit .stat-card-4-content { gap: 5px; justify-content: center; }
        .child-dash-fit .stat-card-4-val { font-size: 1.15rem; }
        .child-dash-fit .mini-cards-row { flex: 0 0 auto; gap: 8px; margin-top: 8px; }
        .child-dash-fit .games-card, .child-dash-fit .oly-card { min-height: 0; padding: 12px 14px; }
    }
</style>

@php
    $__toasts = [];
    if (session('success'))     $__toasts[] = ['type' => 'ok',   'text' => session('success')];
    if (session('test_done'))   $__toasts[] = ['type' => 'ok',   'text' => session('test_done')];
    if (session('child_added')) $__toasts[] = ['type' => 'ok',   'text' => session('child_added')];
    if (session('test_error'))  $__toasts[] = ['type' => 'err',  'text' => session('test_error')];
    foreach ((array) session('grade_change_notice', []) as $__notice) {
        $__toasts[] = ['type' => 'warn', 'text' => $__notice];
    }
@endphp
@if(count($__toasts))
@push('toasts')
<div class="dash-toast-wrap" id="dashToastWrap">
    @foreach($__toasts as $__toast)
    <div class="dash-toast {{ $__toast['type'] }}">{{ $__toast['text'] }}</div>
    @endforeach
</div>
<script>
(function(){
    var wrap = document.getElementById('dashToastWrap');
    if (!wrap) return;
    requestAnimationFrame(function(){ wrap.classList.add('show'); });
    setTimeout(function(){
        wrap.classList.remove('show');
        setTimeout(function(){ wrap.remove(); }, 400);
    }, 3200);
})();
</script>
@endpush
@endif

@if(auth()->user()->role === 'child')
@include('child.partials.home')
@else
<div class="dash-hero{{ in_array(auth()->user()->role, ['parent', 'admin']) ? ' ks-parent-dash' : '' }}">
    <div class="dash-inner">

        @if(auth()->user()->role !== 'child')
        @php
            if (in_array(auth()->user()->role, ['parent', 'admin'])) {
                $currentPkg  = auth()->user()->currentPackage();
                $activeSub   = auth()->user()->activeSubscription();
                $children    = auth()->user()->children()->with(['childSetting.grade','themes','topics'])->withTimestamps()->orderByPivot('created_at','asc')->get();
                $atChildLimit = $currentPkg->max_children > 0 && $children->count() >= $currentPkg->max_children;
            }
        @endphp
        @if(in_array(auth()->user()->role, ['parent', 'admin']))
        {{-- redesign-kidsmart v2: მშობლის მთავარი („ჩემი შვილები“) --}}
        {{-- ზედა ბარათი (ბავშვის მისიის ბარათის მსგავსად) --}}
        <div class="ks-pp-hero">
            <div class="ks-pp-hero-blob" aria-hidden="true"></div>
            <div class="ks-pp-hello">
                @if(auth()->user()->photo_url)
                <img src="{{ auth()->user()->photo_url }}" alt="" class="ks-pp-photo" referrerpolicy="no-referrer">
                @else
                <span class="ks-pp-ini" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                @endif
                <span class="ks-pp-hello-t">
                    <b>გამარჯობა, {{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') ?: auth()->user()->name }}!</b>
                    <button type="button" class="ks-pp-pkg" onclick="document.getElementById('plansModal').classList.add('open')" title="გეგმის შეცვლა">
                        <i class="bi bi-gem"></i>{{ $currentPkg->name }}@if($activeSub?->expires_at)<small>· {{ $activeSub->expires_at->format('d.m') }}-მდე</small>@endif
                    </button>
                    <small>მშობლის პანელი</small>
                </span>
                <button type="button" id="notifBtn" class="ks-pp-bell" aria-label="შეტყობინებები" title="შეტყობინებების ჩართვა/გამორთვა" onclick="window._notifToast=true;toggleNotifications()">
                    <i id="notifIcon" class="bi bi-bell"></i>
                </button>
            </div>
        </div>
        @else
        <div class="ks-pp-head">
            <div>
                <span class="ks-pp-hi">გამარჯობა!</span>
                <h1 class="ks-pp-title">{{ auth()->user()->name }}</h1>
            </div>
        </div>
        @endif
        @endif

        {{-- მშობლის ხედი --}}
        @if(in_array(auth()->user()->role, ['parent', 'admin']))
        {{-- redesign-kidsmart v3: მშობლის მთავარი = დიზაინი D3 (შვილების პროგრესი) — მთავარი + ანალიტიკა ერთ გვერდზე --}}
        @php
            $__kid = $children->firstWhere('id', (int) request('kid')) ?? $children->first();
            $__allPending = $children->isEmpty() ? collect() : \App\Models\MarketPurchase::with(['item', 'child'])
                ->whereIn('child_id', $children->pluck('id'))->where('status', 'pending')->latest()->get();
            $__pdAv = fn($k) => $k->avatar === 'boy' ? '👦' : ($k->avatar === 'girl' ? '👧' : '🙂');
        @endphp

        <div class="ks-pd-head">
            <h1>შვილების პროგრესი</h1>
            <div class="ks-pd-tabs" role="tablist" aria-label="შვილი">
                @foreach($children as $__k)
                <a href="{{ route('dashboard', ['kid' => $__k->id]) }}" role="tab" aria-selected="{{ $__kid && $__k->id === $__kid->id ? 'true' : 'false' }}"
                   class="ks-pd-tab {{ $__kid && $__k->id === $__kid->id ? 'on' : '' }}">
                    <span class="ks-pd-tab-av {{ $__k->avatar }}">@include('partials.kid-av', ['k' => $__k])</span>{{ $__k->name }}
                </a>
                @endforeach
                <button type="button" class="ks-pd-tab add" data-guide="add-child"
                    onclick="document.getElementById('{{ $atChildLimit ? 'plansModal' : 'addChildModal' }}').classList.add('open'); {{ $atChildLimit ? '' : 'wizReset();' }}"
                    @if($atChildLimit) title="{{ $currentPkg->name }} პლანი მხოლოდ {{ $currentPkg->max_children }} ბავშვს იძლევა" @endif>
                    <i class="bi bi-plus-lg"></i>{{ $atChildLimit ? 'გეგმის განახლება' : 'შვილის დამატება' }}
                </button>
            </div>
        </div>

        @include('partials.guide-checklist', ['children' => $children])

        @if(!$__kid)
        <div class="ks-pd-empty">
            <b>ჯერ შვილი არ გყავს დამატებული</b>
            <span>დაამატე შვილი და აქ გამოჩნდება მისი პროგრესი, ტესტები და შედეგები.</span>
        </div>
        @else
        @php
            $child = $__kid;
            $__s      = $child->childSetting;
            $__all    = $child->tests()->where('is_olympiad', false)->whereNotNull('completed_at')->get(['completed_at', 'correct_count', 'total_questions']);
            $__total  = $__all->count();
            $__mon    = today()->startOfWeek(\Carbon\Carbon::MONDAY);
            $__thisWk = $__all->filter(fn($t) => $t->completed_at->gte($__mon));
            $__prevWk = $__all->filter(fn($t) => $t->completed_at->gte($__mon->copy()->subWeek()) && $t->completed_at->lt($__mon));
            $__pctOf  = fn($c) => $c->sum('total_questions') > 0 ? (int) round($c->sum('correct_count') / $c->sum('total_questions') * 100) : null;
            $__avgAll = $__pctOf($__all);
            $__avgWk  = $__pctOf($__thisWk);
            $__avgPrev = $__pctOf($__prevWk);
            $__dates  = $__all->map(fn($t) => $t->completed_at->format('Y-m-d'))->unique();
            $__streak = 0; $__cur = today();
            if (!$__dates->contains($__cur->format('Y-m-d'))) { $__cur = $__cur->copy()->subDay(); }
            while ($__dates->contains($__cur->format('Y-m-d'))) { $__streak++; $__cur = $__cur->copy()->subDay(); }
            $__max    = (int) ($__s?->grade?->max_level ?? \App\Models\Grade::DEFAULT_MAX_LEVEL);
            $__todayDone = $__all->filter(fn($t) => $t->completed_at->isToday())->count();
            $__coins  = (int) floor($__s?->coins ?? 0);
            $__kidPending = $__allPending->where('child_id', $child->id);

            $__days   = ['ორშ', 'სამ', 'ოთხ', 'ხუთ', 'პარ', 'შაბ', 'კვი'];
            $__week   = collect(range(0, 6))->map(function ($i) use ($__mon, $__all) {
                $d  = $__mon->copy()->addDays($i);
                $ts = $__all->filter(fn($t) => $t->completed_at->isSameDay($d));
                return $ts->count() ? (int) round($ts->sum('correct_count') / max(1, $ts->sum('total_questions')) * 100) : 0;
            });

            $__topics = \App\Models\TestAnswer::query()
                ->join('test_questions', 'test_answers.test_question_id', '=', 'test_questions.id')
                ->join('tests', 'test_answers.test_id', '=', 'tests.id')
                ->join('question_templates', 'test_questions.template_id', '=', 'question_templates.id')
                ->join('topics', 'question_templates.topic_id', '=', 'topics.id')
                ->where('tests.child_id', $child->id)->where('tests.is_olympiad', false)
                ->when($__s?->grade_id, fn($q) => $q->where('tests.grade_id', $__s->grade_id))
                ->whereNotNull('tests.completed_at')
                ->selectRaw('topics.name as name, SUM(test_answers.is_correct) as ok, COUNT(*) as n')
                ->groupBy('topics.name')->get()
                ->map(fn($r) => (object) ['name' => $r->name, 'pct' => $r->n > 0 ? (int) round($r->ok / $r->n * 100) : 0])
                ->sortByDesc('pct')->values();
            $__weak   = $__topics->filter(fn($t) => $t->pct < 70)->sortBy('pct')->first();
            $__recent = $child->tests()->whereNotNull('completed_at')->with('theme')->latest('completed_at')->take(5)->get();
            $__prac   = \App\Models\PracticeAnswerLog::where('child_id', $child->id)->whereDate('created_at', today())->count();
            $__ach    = $child->achievements()->count();
            $__oly    = $child->tests()->whereNotNull('completed_at')->where('is_olympiad', true)->count();
            $__link   = $child->child_code ? route('child.magic-login', $child->child_code) : null;
        @endphp

        {{-- არჩეული შვილის ზოლი: სტატუსი + მოქმედებები --}}
        <div class="ks-pd-kid {{ !$child->is_active ? 'off' : '' }}" data-guide="kid">
            <button type="button" class="ks-pp-av {{ $child->avatar }}" title="პროფილის არჩევა"
                onclick="document.getElementById('avatarModal{{ $child->id }}').classList.add('open')">@include('partials.kid-av', ['k' => $child])</button>
            <div class="ks-pd-kid-t">
                <b>{{ $child->name }}</b>
                <span>{{ $__s?->grade?->name ?? 'კლასი —' }}{{ $__s ? ' · დონე ' . $__s->difficulty . '/' . $__max : '' }}</span>
            </div>
            @if(!$child->is_active)
            <button type="button" class="ks-pd-chip off" onclick="document.getElementById('plansModal').classList.add('open')">⊘ გათიშულია · გეგმა ↑</button>
            @elseif($__todayDone > 0)
            <span class="ks-pd-chip ok"><i class="bi bi-check-lg"></i> დღეს დაწერა</span>
            @else
            <span class="ks-pd-chip wait"><i class="bi bi-clock"></i> დღეს ჯერ არა</span>
            @endif
            <div class="ks-pd-kid-act">
                @if(auth()->user()->isAdmin() && $child->is_active)
                <form method="POST" action="{{ route('child.as-child', $child) }}">
                    @csrf
                    <button type="submit" class="ks-pd-btn" title="{{ $child->name }}-ის თვალით ნახვა"><i class="bi bi-person-video2"></i><span>ბავშვის რეჟიმი</span></button>
                </form>
                @endif
                @if($child->child_code)
                <button type="button" class="ks-pd-btn" data-guide="share" onclick="toggleShare({{ $child->id }})"><i class="bi bi-link-45deg"></i><span>შესვლის გაზიარება</span></button>
                @endif
                <button type="button" class="ks-pd-btn icon" data-guide="settings" title="პარამეტრები" aria-label="{{ $child->name }} — პარამეტრები"
                    onclick="document.getElementById('editChildModal{{ $child->id }}').classList.add('open')"><i class="bi bi-gear"></i></button>
            </div>
        </div>

        @if($child->child_code)
        @push('modals')
        <div id="shareModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
            <div class="mbox ks-add">
                <div class="ks-add-head">
                    <h2>შესვლის გაზიარება</h2>
                    <button type="button" class="ks-set-x" aria-label="დახურვა" onclick="document.getElementById('shareModal{{ $child->id }}').classList.remove('open')">✕</button>
                </div>
                <p class="ks-add-note">აირჩიე, როგორ შევიდეს {{ $child->name }}: კოდით, ბმულით ან QR-ით — და გაუზიარე.</p>
            <div class="cc-share-panel ks-share ks-pd-share open" id="sharePanel{{ $child->id }}" data-name="{{ $child->name }}" data-code="{{ $child->child_code }}" data-link="{{ $__link }}">
                <div class="ks-share-tabs" role="tablist" aria-label="გაზიარების ტიპი">
                    <button type="button" role="tab" aria-selected="true" data-k="code" onclick="ksShareTab({{ $child->id }}, 'code')">კოდი</button>
                    <button type="button" role="tab" aria-selected="false" data-k="link" onclick="ksShareTab({{ $child->id }}, 'link')">ბმული</button>
                    <button type="button" role="tab" aria-selected="false" data-k="qr" onclick="ksShareTab({{ $child->id }}, 'qr')">QR</button>
                </div>
                <div class="ks-share-box" data-p="code"><span class="ks-share-code">{{ $child->child_code }}</span><small>შესვლის ფანჯარაში „ბავშვის“ ტაბზე ჩაწერეთ ეს კოდი</small></div>
                <div class="ks-share-box" data-p="link" hidden><span class="cc-share-link-text" id="shareLinkText{{ $child->id }}">{{ $__link }}</span><small>ბმულზე გადასვლით ბავშვი პირდაპირ შევა</small></div>
                <div class="ks-share-box" data-p="qr" hidden><div class="ks-share-qr" id="shareQr{{ $child->id }}"></div><small>დაასკანერეთ ტელეფონით შესასვლელად</small></div>
                <div class="cc-share-actions">
                    <button type="button" class="cc-share-btn" id="shareCopyBtn{{ $child->id }}" onclick="ksShareCopy({{ $child->id }})"><i class="bi bi-clipboard"></i> კოპირება</button>
                    <button type="button" class="cc-share-btn dark" onclick="ksShareSend({{ $child->id }})"><i class="bi bi-share"></i> გაზიარება</button>
                </div>
            </div>
            </div>
        </div>
        @endpush
        @endif

        {{-- KPI --}}
        <div class="ks-pd-kpis" data-guide="kpis">
            <a href="{{ route('child.stats', $child) }}" class="ks-pd-kpi">
                <span>ტესტი სულ</span><b>{{ $__total }}</b>
                <small class="{{ $__thisWk->count() ? 'up' : '' }}">{{ $__thisWk->count() ? '+' . $__thisWk->count() . ' ამ კვირაში' : 'ამ კვირაში ჯერ არა' }}</small>
            </a>
            <div class="ks-pd-kpi">
                <span>საშუალო შედეგი</span><b>{{ $__avgAll !== null ? $__avgAll . '%' : '—' }}</b>
                @if($__avgWk !== null && $__avgPrev !== null)
                <small class="{{ $__avgWk >= $__avgPrev ? 'up' : 'down' }}">{{ $__avgWk >= $__avgPrev ? '+' : '−' }}{{ abs($__avgWk - $__avgPrev) }}% წინა კვირასთან</small>
                @else
                <small>{{ $__avgWk !== null ? 'ეს კვირა: ' . $__avgWk . '%' : 'ყველა ტესტით' }}</small>
                @endif
            </div>
            <div class="ks-pd-kpi">
                <span>დონე</span><b>{{ $__s ? $__s->difficulty . '/' . $__max : '—' }}</b>
                <small>{{ $__s?->grade?->name ?? '' }}</small>
            </div>
            <div class="ks-pd-kpi">
                <span>ზედიზედ დღეები</span><b>{{ $__streak }}</b>
                <small>{{ $__todayDone ? 'დღესაც დაწერა' : ($__streak ? 'დღეს გააგრძელოს!' : 'დღეს დაიწყოს!') }}</small>
            </div>
            <a href="{{ route('market.index', $child) }}" class="ks-pd-kpi">
                <span>მონეტები</span><b>{{ $__coins }}</b>
                <small class="{{ $__kidPending->count() ? 'warn' : '' }}">{{ $__kidPending->count() ? $__kidPending->count() . ' ჯილდო მოთხოვნილია' : 'მოთხოვნა არ არის' }}</small>
            </a>
        </div>

        {{-- გრაფიკი + თემები / რჩევა --}}
        <div class="ks-pd-row">
            <div class="ks-pd-card ks-pd-wide" data-guide="week">
                <div class="ks-pd-card-h">
                    <h2>ტესტის შედეგები · ეს კვირა</h2>
                    <span class="ks-pd-legend"><span><i style="background:#E2590F"></i>85%+</span><span><i style="background:#F7B58A"></i>85%-მდე</span></span>
                </div>
                <div class="ks-pd-bars" role="img" aria-label="{{ $child->name }}: {{ $__week->map(fn($v, $i) => $__days[$i] . ' ' . ($v ? $v . '%' : 'არ წერდა'))->implode(', ') }}">
                    @foreach($__week as $__i => $__v)
                    <div class="ks-pd-bar">
                        <span>{{ $__v ? $__v . '%' : '—' }}</span>
                        <i style="height: {{ $__v ? max(4, round($__v * 0.8)) : 3 }}%; background: {{ $__v >= 85 ? '#E2590F' : ($__v ? '#F7B58A' : '#ECE9E4') }}"></i>
                    </div>
                    @endforeach
                </div>
                <div class="ks-pd-days">
                    @foreach($__days as $__i => $__d)
                    <span class="{{ $__mon->copy()->addDays($__i)->isToday() ? 'today' : '' }}">{{ $__d }}</span>
                    @endforeach
                </div>
            </div>

            <div class="ks-pd-side">
                <div class="ks-pd-card" data-guide="topics">
                    <h2>თემების მიხედვით</h2>
                    @forelse($__topics->take(5) as $__t)
                    <div class="ks-pd-topic">
                        <div><span>{{ $__t->name }}</span><span>{{ $__t->pct }}%</span></div>
                        <div class="ks-pd-topic-bar"><span style="width: {{ max(3, $__t->pct) }}%; background: {{ $__t->pct >= 85 ? '#1E8A4C' : ($__t->pct >= 70 ? '#E2590F' : '#D93B3B') }}"></span></div>
                    </div>
                    @empty
                    <p class="ks-pd-muted">ტესტები ჯერ არ დაწერილა — თემების შედეგები აქ გამოჩნდება.</p>
                    @endforelse
                </div>
                <div class="ks-pd-insight">
                    <span class="ks-pd-insight-ic"><i class="bi bi-lightbulb"></i></span>
                    <span>
                        @if($__todayDone === 0)
                            <b>დღეს ჯერ არ დაუწყია</b>
                            <small>გაუგზავნე შეხსენება — ერთი ტესტი დღეში საკმარისია.</small>
                        @elseif($__weak)
                            <b>{{ $__weak->name }} — უჭირს</b>
                            <small>ამ თემაში სწორია {{ $__weak->pct }}%. ვარჯიში ამ თემაზე დაეხმარება.</small>
                        @else
                            <b>ყოჩაღ, {{ $child->name }}!</b>
                            <small>{{ $__topics->isNotEmpty() ? 'ყველა თემაში 70%-ზე მეტი სწორი პასუხი აქვს.' : 'დღევანდელი ტესტი დაწერილია.' }}</small>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ბოლო ტესტები + დღეს / მოთხოვნები --}}
        <div class="ks-pd-row">
            <div class="ks-pd-card ks-pd-wide" id="tests" data-guide="tests">
                <div class="ks-pd-card-h">
                    <h2>ბოლო ტესტები</h2>
                    @if($__recent->count())<a href="{{ route('child.stats', $child) }}" class="ks-pd-more">ყველა →</a>@endif
                </div>
                @if($__recent->isEmpty())
                <p class="ks-pd-muted">ტესტები ჯერ არ დაწერილა.</p>
                @else
                <div class="ks-pd-table-wrap">
                    <table class="ks-pd-table">
                        <thead><tr><th>თარიღი</th><th>თემატიკა</th><th>სწორი</th><th>შედეგი</th><th></th></tr></thead>
                        <tbody>
                        @foreach($__recent as $__rt)
                        @php $__rp = (int) round($__rt->correct_count / max(1, $__rt->total_questions) * 100); @endphp
                        <tr>
                            <td>{{ $__rt->completed_at->format('d.m · H:i') }}</td>
                            <td class="b">{{ $__rt->is_olympiad ? '🏆 ოლიმპიადა' : ($__rt->theme?->name ?? 'ტესტი') }}</td>
                            <td>{{ $__rt->correct_count }}/{{ $__rt->total_questions }}</td>
                            <td><span class="ks-pd-score {{ $__rp >= 85 ? 'hi' : ($__rp >= 70 ? 'mid' : 'lo') }}">{{ $__rp }}%</span></td>
                            <td class="r"><a href="{{ route('child.test.show', [$child, $__rt]) }}">ნახვა</a></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <div class="ks-pd-side">
                <div class="ks-pd-card" data-guide="today">
                    <h2>დღეს</h2>
                    <p class="ks-pd-today">
                        {{ min($__todayDone, 1) }}/1 ტესტი{{ $__todayDone ? ' · დაწერილია ✓' : ' · ჯერ არ დაუწყია' }}<br>
                        ვარჯიში: {{ $__prac }} პასუხი
                    </p>
                    @if(session('reminder_sent_' . $child->id))
                    <div class="ks-pp-remind sent"><i class="bi bi-check-lg"></i> შეხსენება გაიგზავნა</div>
                    @elseif($child->is_active)
                    <button type="button" class="ks-pd-remind {{ $__todayDone ? 'soft' : '' }}" onclick="openRemind({{ $child->id }}, '{{ addslashes($child->name) }}')">
                        <i class="bi bi-bell"></i> {{ $child->name }}ს შეხსენება
                    </button>
                    @endif
                </div>

                @if($__allPending->isNotEmpty())
                @php $__fp = $__kidPending->first() ?? $__allPending->first(); @endphp
                <a href="{{ route('market.index', $__fp->child_id) }}" class="ks-pp-req">
                    <span class="ks-pp-req-ic"><i class="bi bi-bag"></i></span>
                    <span class="ks-pp-req-t">
                        <b>{{ $__allPending->count() }} ახალი მოთხოვნა</b>
                        <small>{{ $__fp->child?->name }}-ს სურს: {{ $__fp->item?->title }} · {{ $__fp->coins_spent }} მონეტა</small>
                    </span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif

                <div class="ks-pd-links">
                    <a href="{{ route('child.practice-stats', $child) }}"><i class="bi bi-bullseye" style="color:#C24A0A"></i>ვარჯიში</a>
                    <a href="{{ route('child.olympiad-stats', $child) }}"><i class="bi bi-trophy" style="color:#E2590F"></i>ოლიმპიადა · {{ $__oly }}</a>
                    <a href="{{ route('child.achievements', $child) }}"><i class="bi bi-award" style="color:#C24A0A"></i>მედლები · {{ $__ach }}</a>
                </div>
            </div>
        </div>
        @endif
<style>
/* ── redesign-kidsmart v3: მშობლის მთავარი (D3) ── */
.ks-parent-dash .dash-inner { max-width: 1240px; }
.ks-pd-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; }
.ks-pd-head h1 { margin: 0; font-size: var(--fs-display, clamp(28px, calc(1.375rem + 1.5vw), 40px)); line-height: .95; font-weight: 900; font-stretch: 66%; text-transform: uppercase; color: #2B2622; }
.ks-pd-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
.ks-pd-tab { height: 48px; padding: 0 16px 0 5px; border-radius: 24px; background: #fff; color: #2B2622; border: 2px solid #ECE9E4; font-family: inherit; font-size: 15px; font-weight: 800; display: inline-flex; align-items: center; gap: 9px; text-decoration: none; cursor: pointer; }
.ks-pd-tab:hover { color: #2B2622; border-color: #FFC49A; }
.ks-pd-tab.on, .ks-pd-tab.on:hover { background: #E2590F; border-color: #E2590F; color: #fff; }
.ks-pd-tab-av { width: 36px; height: 36px; border-radius: 50%; background: #FFD9BF; display: flex; align-items: center; justify-content: center; font-size: 19px; }
.ks-pd-tab-av.boy { background: #FFF3EA; } .ks-pd-tab-av.girl { background: #FFF3EA; }
.ks-pd-tab.add { border-style: dashed; border-color: #D9D4CC; background: transparent; color: #4F473F; padding: 0 16px; gap: 6px; font-weight: 700; font-size: 14px; }
.ks-pd-empty { background: #fff; border-radius: 24px; padding: 28px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 2px 0 #ECE9E4; }
.ks-pd-empty b { font-size: 18px; } .ks-pd-empty span { color: #6B625A; }

.ks-pd-kid { background: #FFFFFF; border: 2px solid #FFD9BF; border-radius: 24px; padding: 12px 14px; display: flex; align-items: center; flex-wrap: wrap; gap: 12px; box-shadow: 0 6px 18px rgba(226,89,15,.10); }
.ks-pd-kid.off { opacity: .7; }
.ks-pd-kid .ks-pp-av { flex-shrink: 0; }
.ks-pd-kid-t { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.ks-pd-kid-t b { font-size: 18px; font-weight: 800; }
.ks-pd-kid-t span { font-size: 13px; color: #6B625A; }
.ks-pd-chip { border: none; font-family: inherit; font-size: 13px; font-weight: 800; border-radius: 999px; padding: 7px 12px; display: inline-flex; align-items: center; gap: 5px; }
.ks-pd-chip.ok { background: #DFF5E3; color: #14612F; }
.ks-pd-chip.wait { background: #FFE3CC; color: #8A3407; }
.ks-pd-chip.off { background: #FDE2E2; color: #A12626; cursor: pointer; }
.ks-pd-kid-act { margin-left: auto; display: flex; gap: 8px; }
.ks-pd-btn { height: 44px; padding: 0 16px; border-radius: 22px; border: 2px solid #FFD9BF; background: #fff; color: #A33E08; font-family: inherit; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 7px; cursor: pointer; }
.ks-pd-btn:hover { background: #FFF3EA; }
.ks-pd-btn .bi { font-size: 18px; }
.ks-pd-btn.icon { width: 44px; padding: 0; }
.ks-pd-share { border-radius: 20px !important; background: #fff !important; border: 2px solid #FFF3EA !important; padding: 14px !important; flex-direction: column; gap: 12px; }
.ks-pd-share.open { display: flex !important; }

.ks-pd-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; }
.ks-pd-kpi { background: #fff; border-radius: 22px; padding: 16px 18px; display: flex; flex-direction: column; gap: 3px; box-shadow: 0 2px 0 #ECE9E4; text-decoration: none; color: #2B2622; }
a.ks-pd-kpi:hover { color: #2B2622; box-shadow: 0 2px 0 #E2DED7, 0 0 0 2px #FFF3EA; }
.ks-pd-kpi span { font-size: 13px; color: #6B625A; }
.ks-pd-kpi b { font-size: 28px; line-height: 1.1; font-weight: 900; }
.ks-pd-kpi small { font-size: 12px; font-weight: 700; color: #6B625A; }
.ks-pd-kpi small.up { color: #14612F; } .ks-pd-kpi small.down { color: #A12626; } .ks-pd-kpi small.warn { color: #8A3407; }

.ks-pd-row { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start; }
.ks-pd-wide { flex: 2 1 460px; min-width: 0; }
.ks-pd-side { flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 16px; }
.ks-pd-card { background: #fff; border-radius: 26px; padding: 22px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 2px 0 #ECE9E4; box-sizing: border-box; }
.ks-pd-card h2 { margin: 0; font-size: var(--fs-h3, 17px); font-weight: 800; }
.ks-pd-card-h { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
.ks-pd-legend { display: flex; gap: 14px; font-size: 12px; color: #4F473F; }
.ks-pd-legend span { display: flex; align-items: center; gap: 6px; }
.ks-pd-legend i { width: 12px; height: 12px; border-radius: 3px; display: block; }
.ks-pd-bars { height: 200px; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 14px; align-items: end; border-bottom: 1px solid #ECE9E4; }
.ks-pd-bar { height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 6px; }
.ks-pd-bar span { font-size: 12px; font-weight: 700; color: #4F473F; }
.ks-pd-bar i { display: block; width: 100%; max-width: 60px; border-radius: 12px 12px 4px 4px; }
.ks-pd-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 14px; }
.ks-pd-days span { font-size: 12px; color: #6B625A; text-align: center; }
.ks-pd-days span.today { color: #2B2622; font-weight: 800; }
.ks-pd-topic { display: flex; flex-direction: column; gap: 6px; }
.ks-pd-topic > div:first-child { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; font-weight: 700; }
.ks-pd-topic-bar { height: 10px; border-radius: 5px; background: #F1EFEB; overflow: hidden; }
.ks-pd-topic-bar span { display: block; height: 10px; border-radius: 5px; }
.ks-pd-muted { margin: 0; font-size: 14px; color: #6B625A; }
.ks-pd-insight { background: #FFF3EA; border-radius: 22px; padding: 16px 18px; display: flex; gap: 12px; align-items: flex-start; }
.ks-pd-insight-ic { width: 40px; height: 40px; border-radius: 12px; background: #fff; color: #C24A0A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 18px; }
.ks-pd-insight > span:last-child { display: flex; flex-direction: column; gap: 3px; }
.ks-pd-insight b { font-size: 15px; font-weight: 800; color: #7A3206; }
.ks-pd-insight small { font-size: 13px; line-height: 1.45; color: #8A3D0B; }
.ks-pd-more { font-size: 13px; font-weight: 800; color: #C24A0A; text-decoration: none; }
.ks-pd-table-wrap { overflow-x: auto; }
.ks-pd-table { width: 100%; min-width: 460px; border-collapse: collapse; font-size: 14px; }
.ks-pd-table th { text-align: left; font-size: 12px; font-weight: 700; color: #6B625A; padding: 8px 10px; }
.ks-pd-table td { padding: 12px 10px; border-top: 1px solid #F1EFEB; }
.ks-pd-table td.b { font-weight: 700; }
.ks-pd-table td.r { text-align: right; }
.ks-pd-table td a { font-weight: 800; color: #C24A0A; text-decoration: none; }
.ks-pd-score { font-weight: 800; border-radius: 999px; padding: 4px 10px; }
.ks-pd-score.hi { background: #DFF5E3; color: #14612F; } .ks-pd-score.mid { background: #FFE3CC; color: #8A3407; } .ks-pd-score.lo { background: #FDE2E2; color: #A12626; }
.ks-pd-today { margin: 0; font-size: 14px; line-height: 1.6; color: #4F473F; }
.ks-pd-remind { height: 48px; border-radius: 24px; border: none; background: #C24A0A; color: #fff; font-family: inherit; font-size: 15px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; }
.ks-pd-remind:hover { background: #A33E08; }
.ks-pd-remind.soft { background: #F1EFEB; color: #2B2622; }
.ks-pd-remind.soft:hover { background: #E6E2DC; }
.ks-pd-links { display: flex; flex-wrap: wrap; gap: 8px; }
.ks-pd-links a { flex: 1 1 120px; height: 46px; border-radius: 16px; background: #fff; box-shadow: 0 2px 0 #ECE9E4; color: #2B2622; text-decoration: none; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 7px; }
.ks-pd-links a:hover { color: #2B2622; background: #FFFFFF; }
.ks-pd-links .bi { font-size: 17px; }
@media (max-width: 640px) {
    .ks-pd-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .ks-pd-kpi { padding: 12px 14px; border-radius: 18px; }
    .ks-pd-kpi b { font-size: 22px; }
    .ks-pd-card { padding: 16px; border-radius: 22px; }
    .ks-pd-bars, .ks-pd-bar { height: 150px; }
    .ks-pd-bars, .ks-pd-days { gap: 6px; }
    .ks-pd-bar i { border-radius: 8px 8px 3px 3px; }
    .ks-pd-bar span { font-size: 10px; }
    .ks-pd-kid-act { margin-left: 0; width: 100%; }
    .ks-pd-kid-act .ks-pd-btn:not(.icon) { flex: 1; }
    .ks-pd-tab { height: 44px; font-size: 14px; }
    .ks-pd-tab-av { width: 32px; height: 32px; font-size: 17px; }
}
</style>
        @endif


        @php
        $_adminMap = [
            'admin.panel'     => ['route' => 'admin.panel',             'icon' => '⚡', 'name' => 'Push'],
            'admin.grades'    => ['route' => 'admin.grades.index',      'icon' => '🎓', 'name' => 'კლასები'],
            'admin.themes'    => ['route' => 'admin.themes.index',      'icon' => '🎨', 'name' => 'თემატიკა'],
            'admin.topics'    => ['route' => 'admin.topics.index',      'icon' => '📚', 'name' => 'თემები'],
            'admin.questions' => ['route' => 'admin.questions.index',   'icon' => '❓',  'name' => 'კითხვები'],
            'admin.qcounts'   => ['route' => 'admin.question-counts.index', 'icon' => '🔢', 'name' => 'ტესტის ზომა'],
            'admin.levelrules' => ['route' => 'admin.level-rules.index',    'icon' => '📈', 'name' => 'დონის ცვლილება'],
            'admin.olympiadrules' => ['route' => 'admin.olympiad-rules.index', 'icon' => '🏆', 'name' => 'ოლიმპიადა'],
            'admin.pointrules' => ['route' => 'admin.point-rules.index', 'icon' => '💰', 'name' => 'ქულების წესები'],
            'admin.achievements' => ['route' => 'admin.achievements.index', 'icon' => '🏅', 'name' => 'მიღწევები'],
            'admin.users'     => ['route' => 'admin.users.index',       'icon' => '👥', 'name' => 'მომხმარებლები'],
            'admin.perms'     => ['route' => 'admin.permissions.index', 'icon' => '🔐', 'name' => 'ნებართვები'],
            'admin.packages'  => ['route' => 'admin.packages.index',    'icon' => '📦', 'name' => 'პაკეტები'],
        ];
        @endphp

        {{-- ადმინის ხედი: ადმინი აქ მშობლად ხედავს გვერდს, ადმინის გვერდები — „ადმინ რეჟიმი“ ღილაკით --}}
        @if(false)
        <div class="section-label">გვერდები · {{ count($_adminMap) + 1 }}</div>
        <div class="nav-tile-grid">
            <a href="{{ route('test.preview') }}" class="nav-tile">
                <span class="nav-tile-icon">🧪</span>
                <span class="nav-tile-label">ტესტის გადახედვა</span>
            </a>
            @foreach($_adminMap as $info)
            <a href="{{ route($info['route']) }}" class="nav-tile">
                <span class="nav-tile-icon">{{ $info['icon'] }}</span>
                <span class="nav-tile-label">{{ $info['name'] }}</span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- მასწავლებლის / სტაფის ხედი --}}
        @if(!in_array(auth()->user()->role, ['parent', 'admin', 'child']))
        @php
            $_myPerms    = \App\Models\RolePermission::allowedPages(auth()->user()->role) ?? [];
            $_myAdmPages = array_filter($_myPerms, fn($p) => str_starts_with($p, 'admin.'));
        @endphp
        <div class="section-label">გვერდები</div>
        <div class="nav-tile-grid">
            <a href="{{ route('test.preview') }}" class="nav-tile">
                <span class="nav-tile-icon">🧪</span>
                <span class="nav-tile-label">ტესტის გადახედვა</span>
            </a>
            @foreach($_adminMap as $key => $info)
            @if(in_array($key, $_myAdmPages))
            <a href="{{ route($info['route']) }}" class="nav-tile">
                <span class="nav-tile-icon">{{ $info['icon'] }}</span>
                <span class="nav-tile-label">{{ $info['name'] }}</span>
            </a>
            @endif
            @endforeach
        </div>
        @endif



        @if(false)
        <a href="{{ route('admin.panel') }}" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;">
            admin →
        </a>
        @endif


    </div>
</div>
@endif

{{-- Edit / avatar modals (one per child) — pushed to the 'modals' stack, rendered outside both
     .dash-hero (overflow:hidden would clip their fixed backdrop) and .container-fluid (whose
     z-index:1 stacking context would trap them below the shared top-bar) --}}
@push('modals')
@if(in_array(auth()->user()->role, ['parent', 'admin']))
@foreach($children as $child)
@php
    $es = $child->childSetting;
    $eThemeIds = $child->themes->pluck('id')->toArray();
@endphp
<div id="editChildModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox ks-set">
        <div class="ks-set-head">
            <h2>{{ $child->name }} · პარამეტრები</h2>
            <button type="button" class="ks-set-x" aria-label="დახურვა"
                onclick="document.getElementById('editChildModal{{ $child->id }}').classList.remove('open')">✕</button>
        </div>

        <form method="POST" action="{{ route('child.settings.update', $child) }}"
            id="editChildForm{{ $child->id }}" data-orig-grade="{{ $es?->grade_id }}"
            onsubmit="return handleEditChildSubmit(event, {{ $child->id }}, '{{ addslashes($child->name) }}')">
            @csrf @method('PUT')
            <input type="hidden" name="tests_per_week" value="{{ $es?->tests_per_week ?? 1 }}">
            <input type="hidden" name="difficulty" value="{{ $es?->difficulty ?? 1 }}">
            <input type="hidden" name="notify_form" value="1">


            <div class="ks-set-card ks-set-main">
                <div class="ks-set-row">
                    <label for="ename{{ $child->id }}">სახელი</label>
                    <input type="text" id="ename{{ $child->id }}" name="name" value="{{ $child->name }}" maxlength="50" autocomplete="off">
                </div>
                <div class="ks-set-row" role="radiogroup" aria-labelledby="egl{{ $child->id }}">
                    <label id="egl{{ $child->id }}">სქესი</label>
                    <div class="ks-set-seg">
                        @foreach(['boy' => 'ბიჭი', 'girl' => 'გოგონა'] as $__ak => $__al)
                        <label><input type="radio" name="avatar" value="{{ $__ak }}" @checked($child->avatar === $__ak)><span>{{ $__al }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div class="ks-set-row">
                    <label for="egid{{ $child->id }}">კლასი</label>
                    <select id="egid{{ $child->id }}" name="grade_id" required>
                        @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" @selected($es?->grade_id == $grade->id)>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($themes->where('is_active', true)->count())
                @php $__selTheme = count($eThemeIds) ? $eThemeIds[0] : ($defaultThemeId ?? null); @endphp
                <div class="ks-set-row">
                    <label for="etheme{{ $child->id }}">თემატიკა</label>
                    <select id="etheme{{ $child->id }}" name="theme_ids[]">
                        @foreach($themes->where('is_active', true) as $theme)
                        <option value="{{ $theme->id }}" @selected($__selTheme == $theme->id)>{{ $theme->icon }} {{ $theme->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="ks-set-sub">შეტყობინებები მშობელს</div>
            <div class="ks-set-card ks-set-notif">
                @foreach(\App\Models\ChildSetting::PARENT_NOTIFY as $__nk => [$__nt, $__nd])
                @php $__on = $es ? $es->wantsParentNotify($__nk) : true; @endphp
                <label class="ks-set-row ks-set-sw">
                    <span class="ks-set-sw-t"><b>{{ $__nt }}</b><small>{{ $__nd }}</small></span>
                    <input type="checkbox" name="notify[{{ $__nk }}]" value="1" @checked($__on)>
                    <span class="ks-set-track" aria-hidden="true"><span></span></span>
                </label>
                @endforeach
            </div>
            <div class="ks-set-hint">შეტყობინებები მოვა, თუ ამ მოწყობილობაზე შეტყობინებები ჩართული გაქვს.</div>

            <div class="ks-set-actions">
                <button type="submit" class="ks-set-save">შენახვა</button>
                <button type="button" class="ks-set-del"
                    onclick="confirmDeleteChild({{ $child->id }}, '{{ addslashes($child->name) }}')">წაშლა</button>
            </div>
        </form>

        <form id="deleteChildForm{{ $child->id }}" method="POST"
            action="{{ route('child.destroy', $child) }}" style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

@php $__avG = in_array($child->avatar, ['boy', 'girl']) ? $child->avatar : 'boy'; @endphp
<div id="avatarModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox ks-add ks-avp">
        <div class="ks-add-head">
            <h2>პროფილის სურათი</h2>
            <button type="button" class="ks-set-x" aria-label="დახურვა"
                onclick="document.getElementById('avatarModal{{ $child->id }}').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('child.avatar.update', $child) }}">
            @csrf @method('PUT')
            @if(in_array($child->avatar, ['boy', 'girl']))
            <input type="hidden" name="avatar" value="{{ $child->avatar }}">
            @else
            <div class="ks-add-tabs" role="radiogroup" aria-label="სქესი">
                @foreach(['boy' => 'ბიჭი', 'girl' => 'გოგონა'] as $__g => $__gl)
                <label class="ks-add-tab {{ $__avG === $__g ? 'on' : '' }}">
                    <input type="radio" name="avatar" value="{{ $__g }}" class="ks-avp-g" @checked($__avG === $__g) onchange="ksAvGender(this)">{{ $__gl }}
                </label>
                @endforeach
            </div>
            @endif
            @foreach(['boy', 'girl'] as $__g)
            <div class="ks-avp-grid" data-g="{{ $__g }}" @if($__avG !== $__g) hidden @endif>
                @foreach(\App\Models\User::avatarChoices($__g) as $__img)
                <label class="ks-avp-item">
                    <input type="radio" name="avatar_img" value="{{ $__img }}" @checked($child->avatarKey() === $__img)>
                    <img src="{{ asset('avatars/' . $__img . '.webp') }}" alt="" loading="lazy">
                </label>
                @endforeach
            </div>
            @endforeach
            <button type="submit" class="msave">შენახვა</button>
        </form>
    </div>
</div>
@endforeach
@endif
@endpush

@push('modals')
{{-- Remind Modal --}}
@if(in_array(auth()->user()->role, ['parent', 'admin']))
<div id="remindModal" class="remind-modal" onclick="if(event.target===this)closeRemind()">
    <div class="remind-box">
        <div class="remind-title">
            <span>🔔 შეხსენება — <span id="remindChildName"></span></span>
            <button type="button" class="modal-close" onclick="closeRemind()">✕</button>
        </div>
        <form id="remindForm" method="POST" action="">
            @csrf
            <textarea name="message" class="remind-textarea" rows="3"
                placeholder="ტექსტი (სურვილისამებრ)&#10;მაგ: ახლავე გააკეთე ტესტი! 📝"></textarea>
            <div class="remind-hint">ცარიელი = სტანდარტული შეტყობინება</div>
            <button type="submit" class="remind-send">📤 გაგზავნა</button>
        </form>
    </div>
</div>
@endif
@endpush

@push('modals')
{{-- Add Child Modal --}}
@if(in_array(auth()->user()->role, ['parent', 'admin']))
<div id="addChildModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox ks-add">
        <div class="ks-add-head">
            <h2>შვილის დამატება</h2>
            <button type="button" class="ks-set-x" aria-label="დახურვა" onclick="document.getElementById('addChildModal').classList.remove('open')">✕</button>
        </div>
        <div class="ks-add-tabs" role="tablist" aria-label="დამატების გზა">
            <button type="button" id="tabNew" role="tab" aria-selected="true" class="ks-add-tab on" onclick="switchChildTab('new')"><i class="bi bi-plus-lg"></i> ახალი ბავშვი</button>
            <button type="button" id="tabLink" role="tab" aria-selected="false" class="ks-add-tab" onclick="switchChildTab('link')"><i class="bi bi-link-45deg"></i> კოდით მიბმა</button>
        </div>

        {{-- Tab: Link by code --}}
        <div id="panelLink" style="display:none;">
            <p class="ks-add-note">ჩაწერე შვილის კოდი — ბავშვი შენს ანგარიშსაც დაემატება (მაგ. მეორე მშობლისთვის).</p>
            <form method="POST" action="{{ route('child.link') }}" class="ks-add-card">
                @csrf
                <div class="mlbl">ბავშვის კოდი <span>*</span></div>
                <input type="text" name="child_code" class="minput" placeholder="მაგ: AB3K7F"
                    value="{{ old('child_code') }}" required maxlength="8" autocomplete="off"
                    style="text-transform:uppercase;letter-spacing:0.12em;">
                @error('child_code_link')<div class="merr">{{ $message }}</div>@enderror
                <button type="submit" class="msave"><i class="bi bi-link-45deg"></i> მიბმა</button>
            </form>
        </div>

        {{-- Tab: New child --}}
        <div id="panelNew">
        <form method="POST" action="{{ route('child.store') }}" id="addChildForm">
            @csrf
            <div class="ks-add-steps" aria-hidden="true"><i class="on"></i><i></i><i></i></div>

            {{-- Step 1: Name + gender --}}
            <div class="wiz-step ks-add-card" data-step="1">
                <div class="wiz-step-title">ვინ არის შენი შვილი?</div>

                <div class="mlbl">სახელი <span>*</span></div>
                <input type="text" name="name" id="wiz_name" class="minput" placeholder="სახელი"
                    value="{{ old('name') }}" required maxlength="50" autocomplete="off">
                @error('name')<div class="merr">{{ $message }}</div>@enderror
                <div class="merr" id="wizNameErr" style="display:none;">შეიყვანე სახელი</div>

                <div class="mlbl">სქესი <span>*</span></div>
                <div class="mrow" id="genderRow">
                    <label class="mchip {{ old('avatar') == 'boy' ? 'sel' : '' }}"
                        onclick="chipSingle(this,'avatar_input','boy')">ბიჭი</label>
                    <label class="mchip {{ old('avatar') == 'girl' ? 'sel' : '' }}"
                        onclick="chipSingle(this,'avatar_input','girl')">გოგონა</label>
                </div>
                <input type="hidden" name="avatar" id="avatar_input" value="{{ old('avatar') }}">
                @error('avatar')<div class="merr">{{ $message }}</div>@enderror
                <div class="merr" id="wizGenderErr" style="display:none;">აირჩიე სქესი</div>
            </div>

            {{-- Step 2: Grade --}}
            <div class="wiz-step ks-add-card" data-step="2">
                <div class="wiz-step-title">რომელ კლასშია?</div>

                <div class="mlbl">კლასი <span>*</span></div>
                <div class="mrow" id="gradeRow">
                    @foreach($grades as $grade)
                    <label class="mchip {{ old('grade_id') == $grade->id ? 'sel' : '' }}"
                        onclick="chipSingle(this,'grade_id_input','{{ $grade->id }}')">{{ $grade->name }}</label>
                    @endforeach
                </div>
                <input type="hidden" name="grade_id" id="grade_id_input" value="{{ old('grade_id') }}">
                @error('grade_id')<div class="merr">{{ $message }}</div>@enderror
                <div class="merr" id="wizGradeErr" style="display:none;">აირჩიე კლასი</div>
            </div>

            {{-- Step 3: Theme (optional) --}}
            <div class="wiz-step ks-add-card" data-step="3">
                <div class="wiz-step-title">ბოლო ნაბიჯი — თემატიკა</div>

                @if($themes->count())
                @php
                    $addThemeOld = old('theme_ids', []);
                    $addDefaultThemeId = $defaultThemeId ?? null;
                @endphp
                <div class="mlbl">თემატიკა <span class="opt">(სურვილისამებრ)</span></div>
                <div class="mrow" id="addThemeRow">
                    @foreach($themes as $theme)
                    @php
                        $isSelected = count($addThemeOld)
                            ? in_array($theme->id, $addThemeOld)
                            : ($theme->id == $addDefaultThemeId && $theme->is_active);
                    @endphp
                    @if($theme->is_active)
                    <label class="mchip {{ $isSelected ? 'sel' : '' }}"
                        onclick="chipSingleTheme(this, '{{ $theme->id }}')">{{ $theme->icon }} {{ $theme->name }}</label>
                    @else
                    <span class="mchip" style="opacity:0.4;cursor:default;pointer-events:none;">{{ $theme->icon }} {{ $theme->name }}<span style="font-size:0.75em;margin-left:4px;color:#aaa;">მალე</span></span>
                    @endif
                    @endforeach
                </div>
                <input type="hidden" name="theme_ids[]" id="add_theme_input"
                    value="{{ count($addThemeOld) ? ($addThemeOld[0] ?? '') : ($addDefaultThemeId ?? '') }}">
                @endif
            </div>

            <div class="wiz-nav">
                <button type="button" class="wiz-btn wiz-btn-prev" id="wizPrevBtn" onclick="wizPrev()" style="visibility:hidden;">← წინა</button>
                <button type="button" class="wiz-btn wiz-btn-next" id="wizNextBtn" onclick="wizNext()">შემდეგი →</button>
                <button type="submit" class="wiz-btn wiz-btn-submit" id="wizSubmitBtn" style="display:none;">+ შვილის შენახვა</button>
            </div>
        </form>
        </div>{{-- /panelNew --}}
    </div>
</div>
@endif
@endpush

@push('modals')
{{-- Plans Modal — redesign-kidsmart: იგივე ენა, რაც ლენდინგის „აირჩიე პაკეტი“ და მშობლის პანელი --}}
@if(in_array(auth()->user()->role, ['parent', 'admin']))
@php
    $atChildLimit = isset($currentPkg) && $currentPkg->max_children > 0 && isset($children) && $children->count() >= $currentPkg->max_children;
    $allPkgs = App\Models\Package::where('is_active', true)->orderBy('sort_order')->get();
    $__ksMoney = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
@endphp
<div id="plansModal" class="modal-overlay ksp-ov" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="ksp" role="dialog" aria-modal="true" aria-labelledby="kspTitle">
        <div class="ksp-head">
            <span class="ksp-ico" aria-hidden="true"><i class="bi bi-gem"></i></span>
            <div class="ksp-head-t">
                <h2 id="kspTitle">სატარიფო გეგმები</h2>
                <p>გეგმის შესაცვლელად დაუკავშირდი ადმინს</p>
            </div>
            <button type="button" class="ksp-x" aria-label="დახურვა" onclick="document.getElementById('plansModal').classList.remove('open')"><i class="bi bi-x-lg"></i></button>
        </div>

        @if($atChildLimit)
        <div class="ksp-warn" role="status">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <span>შენი მიმდინარე გეგმა (<b>{{ $currentPkg->name }}</b>) მხოლოდ <b>{{ $currentPkg->max_children }}</b> ბავშვს იძლევა. მეტი ბავშვის დასამატებლად გადადი უფრო მაღალ გეგმაზე.</span>
        </div>
        @endif

        <div class="ksp-grid">
            @foreach($allPkgs as $pkg)
            @php
                $isCurrent = $currentPkg->id && $currentPkg->id === $pkg->id;
                $__lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $pkg->description)), 'strlen'));
                $__sub = array_shift($__lines);
            @endphp
            <article class="ksp-card {{ $isCurrent ? 'cur' : '' }}">
                <div class="ksp-card-h">
                    <b>{{ $pkg->name }}</b>
                    @if($isCurrent)<span class="ksp-badge">მიმდინარე</span>@endif
                </div>
                @if($__sub)<div class="ksp-sub">{{ $__sub }}</div>@endif
                <div class="ksp-price">
                    @if($pkg->is_free)
                        <b>0 ₾</b><span>მუდამ უფასო</span>
                    @elseif($pkg->price_monthly > 0)
                        <b>{{ $__ksMoney($pkg->price_monthly) }} ₾</b><span>/ თვეში</span>
                    @elseif($pkg->price_yearly > 0)
                        <b>{{ $__ksMoney($pkg->price_yearly) }} ₾</b><span>/ წელიწადში</span>
                    @endif
                </div>
                @if(!$pkg->is_free && $pkg->price_monthly > 0 && $pkg->price_yearly > 0)
                <div class="ksp-year">ან <b>{{ $__ksMoney($pkg->price_yearly) }} ₾</b> წელიწადში</div>
                @endif
                <ul class="ksp-feat">
                    <li><i class="bi bi-check-lg" aria-hidden="true"></i>{{ $pkg->max_children === 0 ? 'შეუზღუდავი რაოდენობის შვილი' : $pkg->max_children . ' შვილის პროფილი' }}</li>
                    <li><i class="bi bi-check-lg" aria-hidden="true"></i>{{ (int) $pkg->max_difficulty >= 5 ? 'სირთულის ყველა დონე' : 'სირთულის 1–' . (int) $pkg->max_difficulty . ' დონე' }}</li>
                    @foreach($__lines as $__l)
                    <li><i class="bi bi-check-lg" aria-hidden="true"></i>{{ $__l }}</li>
                    @endforeach
                </ul>
                <div class="ksp-foot">
                    @if($isCurrent)
                        <span class="ksp-state cur"><i class="bi bi-check2-circle"></i> შენი გეგმა</span>
                    @elseif(!$pkg->is_free)
                        <span class="ksp-state"><i class="bi bi-chat-dots"></i> გასააქტიურებლად დაუკავშირდი ადმინს</span>
                    @else
                        <span class="ksp-state muted">საბაზო გეგმა</span>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
    </div>
</div>
<style>
    .ksp-ov { padding: 56px 16px 40px; }
    .ksp { width: 100%; max-width: 880px; flex-shrink: 0; background: #F7F6F3; border-radius: 28px; padding: 22px; box-shadow: 0 24px 64px rgba(43, 38, 34, .28); font-family: 'Noto Sans Georgian', sans-serif; color: #2B2622; animation: modalIn .25s cubic-bezier(.175, .885, .32, 1.275); display: flex; flex-direction: column; gap: 16px; }
    .ksp-head { display: flex; align-items: center; gap: 14px; }
    .ksp-ico { width: 48px; height: 48px; border-radius: 16px; background: #E2590F; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .ksp-head-t { flex: 1; min-width: 0; }
    .ksp-head-t h2 { margin: 0; font-size: 22px; font-weight: 900; color: #2B2622; }
    .ksp-head-t p { margin: 2px 0 0; font-size: 14px; color: #6B625A; }
    .ksp-x { width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid #ECE9E4; background: #fff; color: #4F473F; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; font-size: 16px; }
    .ksp-x:hover { border-color: #FFC49A; color: #C24A0A; }
    .ksp-x:focus-visible { outline: 3px solid rgba(226, 89, 15, .35); outline-offset: 2px; }
    .ksp-warn { display: flex; gap: 10px; align-items: flex-start; background: #FFF1C2; color: #5A3E00; border-radius: 16px; padding: 12px 14px; font-size: 14px; line-height: 1.5; }
    .ksp-warn i { font-size: 18px; margin-top: 1px; }
    .ksp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; }
    .ksp-card { background: #fff; border: 1.5px solid #ECE9E4; border-radius: 22px; padding: 20px 18px 18px; display: flex; flex-direction: column; gap: 10px; }
    .ksp-card.cur { border: 2.5px solid #E2590F; background: linear-gradient(180deg, #FFF3EA 0, #fff 120px); }
    .ksp-card-h { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ksp-card-h b { font-size: 19px; font-weight: 900; }
    .ksp-badge { background: #E2590F; color: #fff; font-size: 12px; font-weight: 800; border-radius: 999px; padding: 4px 10px; }
    .ksp-sub { font-size: 13px; color: #6B625A; margin-top: -4px; }
    .ksp-price { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
    .ksp-price b { font-size: 30px; font-weight: 900; color: #2B2622; letter-spacing: -.01em; }
    .ksp-price span { font-size: 14px; color: #6B625A; font-weight: 700; }
    .ksp-year { font-size: 13px; color: #6B625A; margin-top: -6px; }
    .ksp-year b { color: #C24A0A; }
    .ksp-feat { list-style: none; margin: 4px 0 0; padding: 12px 0 0; border-top: 1px solid #ECE9E4; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .ksp-feat li { display: flex; gap: 8px; align-items: flex-start; font-size: 14px; line-height: 1.45; }
    .ksp-feat i { width: 20px; height: 20px; border-radius: 50%; background: #DFF5E3; color: #14612F; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; margin-top: 1px; }
    .ksp-foot { margin-top: 4px; }
    .ksp-state { display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center; border-radius: 14px; padding: 11px 12px; font-size: 13px; font-weight: 800; background: #FFF3EA; color: #C24A0A; }
    .ksp-state.cur { background: #E2590F; color: #fff; }
    .ksp-state.muted { background: #F1EFEB; color: #6B625A; }
    @media (max-width: 600px) {
        .ksp-ov { padding: 16px 10px 90px; }
        .ksp { padding: 16px; border-radius: 22px; }
        .ksp-head-t h2 { font-size: 19px; }
        .ksp-price b { font-size: 26px; }
    }
</style>
@endif
@endpush

<script>
// redesign-kidsmart: გვერდითი მენიუდან „გეგმის შეცვლა“ → dashboard#plans
if (location.hash === '#plans') { document.addEventListener('DOMContentLoaded', function () { var m = document.getElementById('plansModal'); if (m) m.classList.add('open'); }); }

function ksAvGender(el) {
    var f = el.form;
    f.querySelectorAll('.ks-avp-grid').forEach(function (g) { g.hidden = g.dataset.g !== el.value; });
    f.querySelectorAll('.ks-add-tab').forEach(function (t) { t.classList.toggle('on', t.contains(el)); });
}
function switchChildTab(tab) {
    const isNew = tab === 'new';
    document.getElementById('panelNew').style.display  = isNew ? '' : 'none';
    document.getElementById('panelLink').style.display = isNew ? 'none' : '';
    [['tabNew', isNew], ['tabLink', !isNew]].forEach(function (x) {
        var b = document.getElementById(x[0]);
        b.classList.toggle('on', x[1]);
        b.setAttribute('aria-selected', x[1] ? 'true' : 'false');
    });
}

function openRemind(childId, childName) {
    document.getElementById('remindChildName').textContent = childName;
    document.getElementById('remindForm').action = '/push/remind/' + childId;
    document.getElementById('remindModal').classList.add('open');
}
function closeRemind() {
    document.getElementById('remindModal').classList.remove('open');
}

function confirmDeleteChild(childId, childName) {
    Swal.fire({
        title: childName + '-ის წაშლა?',
        html: '<span style="font-size:0.9rem;color:#555;">შენს სიაში წაიშლება.<br>თუ სხვა მშობელი არ არის, ბაზიდანაც სრულად წაიშლება.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'დიახ, წავშალო',
        cancelButtonText: 'გაუქმება',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deleteChildForm' + childId).submit();
        }
    });
}

function handleEditChildSubmit(event, childId, childName) {
    const form = document.getElementById('editChildForm' + childId);
    const newGrade = document.getElementById('egid' + childId).value;
    const origGrade = form.dataset.origGrade;
    if (origGrade && newGrade && origGrade !== newGrade) {
        event.preventDefault();
        Swal.fire({
            title: childName + '-ის კლასის შეცვლა?',
            html: '<span style="font-size:0.9rem;color:#555;">დონე დაუბრუნდება თავიდან და ტესტების მთვლელი განულდება.<br>ძველი კლასის ნამუშევრები და მიღწეული დონე უსაფრთხოდ შენახული დარჩება სტატისტიკაში.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'დიახ, შევცვალო',
            cancelButtonText: 'გაუქმება',
        }).then(result => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
    return true;
}

function copyChildCode(el, code) {
    navigator.clipboard.writeText(code).then(() => {
        const orig = el.textContent;
        el.textContent = '✓ კოპირებულია';
        setTimeout(() => {
            el.textContent = orig;
        }, 1500);
    });
}


// redesign-kidsmart: გაზიარება — კოდი / ბმული / QR
function ksShareTab(id, k) {
    var p = document.getElementById('sharePanel' + id);
    p.dataset.mode = k;
    p.querySelectorAll('.ks-share-tabs button').forEach(function (b) { b.setAttribute('aria-selected', b.dataset.k === k ? 'true' : 'false'); });
    p.querySelectorAll('.ks-share-box').forEach(function (b) { b.hidden = b.dataset.p !== k; });
    var copyBtn = document.getElementById('shareCopyBtn' + id);
    copyBtn.innerHTML = k === 'qr' ? '<i class="bi bi-download"></i> ჩამოტვირთვა' : '<i class="bi bi-clipboard"></i> კოპირება';
    if (k === 'qr') ksQrRender(id);
}
function ksQrRender(id, cb) {
    var p = document.getElementById('sharePanel' + id), box = document.getElementById('shareQr' + id);
    function draw() {
        if (!box.dataset.done) { box.innerHTML = ''; new QRCode(box, { text: p.dataset.link, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M }); box.dataset.done = '1'; }
        if (cb) setTimeout(cb, 50);
    }
    if (window.QRCode) return draw();
    var sc = document.createElement('script');
    sc.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    sc.onload = draw;
    sc.onerror = function () { box.textContent = 'QR ვერ ჩაიტვირთა — სცადეთ ბმული'; };
    document.head.appendChild(sc);
}
function ksQrBlob(id, cb) {
    ksQrRender(id, function () {
        var c = document.querySelector('#shareQr' + id + ' canvas');
        if (c && c.toBlob) c.toBlob(cb, 'image/png'); else cb(null);
    });
}
function ksShareFlash(id, html) {
    var btn = document.getElementById('shareCopyBtn' + id), orig = btn.innerHTML;
    btn.innerHTML = html; setTimeout(function () { btn.innerHTML = orig; }, 1500);
}
function ksShareCopy(id) {
    var p = document.getElementById('sharePanel' + id), mode = p.dataset.mode || 'code';
    if (mode === 'qr') {
        ksQrBlob(id, function (blob) {
            if (!blob) return;
            var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
            a.download = 'KidSmart-' + p.dataset.name + '-QR.png'; a.click();
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 2000);
        });
        return;
    }
    var text = mode === 'code' ? p.dataset.code : p.dataset.link;
    navigator.clipboard.writeText(text).then(function () { ksShareFlash(id, '✓ დაკოპირდა'); });
}
function ksShareSend(id) {
    var p = document.getElementById('sharePanel' + id), mode = p.dataset.mode || 'code', name = p.dataset.name;
    if (mode === 'qr') {
        ksQrBlob(id, function (blob) {
            var file = blob ? new File([blob], 'KidSmart-' + name + '-QR.png', { type: 'image/png' }) : null;
            if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({ title: 'KidSmart — ' + name, text: name + '-ის შესვლის QR', files: [file] }).catch(function () {});
            } else { ksShareCopy(id); }
        });
        return;
    }
    var data = mode === 'code'
        ? { title: 'KidSmart — ' + name, text: name + '-ის შესვლის კოდი: ' + p.dataset.code }
        : { title: 'KidSmart — ' + name, text: name + '-ის შესვლის ბმული', url: p.dataset.link };
    if (navigator.share) navigator.share(data).catch(function () {}); else ksShareCopy(id);
}

function toggleShare(childId) {
    var m = document.getElementById('shareModal' + childId);
    if (m) { m.classList.add('open'); return; }
    document.getElementById('sharePanel' + childId).classList.toggle('open');
}


function copyShareLink(childId) {
    const text = document.getElementById('shareLinkText' + childId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('shareCopyBtn' + childId);
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ დაკოპირდა';
        btn.classList.add('copied');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('copied'); }, 1500);
    });
}

function shareLink(childId, childName) {
    const text = document.getElementById('shareLinkText' + childId).textContent;
    if (navigator.share) {
        navigator.share({ title: 'KidSmart — ' + childName, text: childName + '-ის შესვლის ბმული', url: text }).catch(() => {});
    } else {
        copyShareLink(childId);
    }
}

function chipSingle(el, inputId, value) {
    el.closest('.mrow').querySelectorAll('.mchip').forEach(c => c.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById(inputId).value = value;
}

function chipSingleTheme(el, value) {
    document.getElementById('addThemeRow').querySelectorAll('.mchip').forEach(c => c.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('add_theme_input').value = value;
}

function chipMulti(el, name, value) {
    el.classList.toggle('sel');
    const existing = el.closest('form').querySelector('input[type="hidden"][name="' + name + '"][value="' + value + '"]');
    if (el.classList.contains('sel')) {
        if (!existing) {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name; inp.value = value;
            el.closest('form').appendChild(inp);
        }
    } else {
        if (existing) existing.remove();
    }
}

// ── Add-child wizard ──
const WIZ_TOTAL = 3;
let wizStep = 1;

function wizGoTo(n) {
    n = Math.min(Math.max(n, 1), WIZ_TOTAL);
    document.querySelectorAll('#panelNew .wiz-step').forEach(el => {
        el.classList.toggle('active', +el.dataset.step === n);
    });
    document.getElementById('wizPrevBtn').style.visibility = n === 1 ? 'hidden' : 'visible';
    document.getElementById('wizNextBtn').style.display = n === WIZ_TOTAL ? 'none' : '';
    document.getElementById('wizSubmitBtn').style.display = n === WIZ_TOTAL ? '' : 'none';
    document.querySelectorAll('.ks-add-steps i').forEach(function (d, i) { d.classList.toggle('on', i < n); });
    wizStep = n;
}

function wizValidateStep(n) {
    if (n === 1) {
        const okName = document.getElementById('wiz_name').value.trim().length > 0;
        document.getElementById('wizNameErr').style.display = okName ? 'none' : 'block';
        const okGender = !!document.getElementById('avatar_input').value;
        document.getElementById('wizGenderErr').style.display = okGender ? 'none' : 'block';
        return okName && okGender;
    }
    if (n === 2) {
        const ok = !!document.getElementById('grade_id_input').value;
        document.getElementById('wizGradeErr').style.display = ok ? 'none' : 'block';
        return ok;
    }
    return true;
}

function wizNext() {
    if (!wizValidateStep(wizStep)) return;
    wizGoTo(wizStep + 1);
}
function wizPrev() {
    wizGoTo(wizStep - 1);
}
function wizReset() {
    document.getElementById('wizNameErr').style.display = 'none';
    document.getElementById('wizGenderErr').style.display = 'none';
    document.getElementById('wizGradeErr').style.display = 'none';
    wizGoTo(1);
}
document.getElementById('addChildForm').addEventListener('submit', function(e) {
    // guards against Enter-key submitting the form before the last step
    if (wizStep !== WIZ_TOTAL) {
        e.preventDefault();
        wizNext();
    }
});

@if($errors->hasAny(['name','avatar','grade_id','theme_ids']))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addChildModal').classList.add('open');
    @if($errors->has('grade_id'))
    wizGoTo(2);
    @else
    wizGoTo(1);
    @endif
});
@else
document.addEventListener('DOMContentLoaded', function() { wizGoTo(1); });
@endif
</script>
@endsection
