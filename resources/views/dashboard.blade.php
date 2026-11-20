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
        --primary: #6c5ce7;
        --primary-light: #a29bfe;
        --primary-soft: #eeebff;
        --accent-yellow: #fdcb6e;
        --accent-pink: #ff7675;
        --accent-green: #55efc4;
        --accent-blue: #74b9ff;
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
        background: var(--primary-soft); border: 2px dashed #c9c0f7; border-radius: var(--radius-lg);
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        width: 100%; min-height: 140px; padding: 20px 10px; cursor: pointer;
        font-family: inherit; color: var(--primary); transition: all 0.2s;
    }
    .add-card:hover { border-color: var(--primary-light); background: #e2ddff; }
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
        border: 1px solid #f1f5f9;
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
        background: linear-gradient(135deg, var(--primary-soft), #e2ddff);
    }
    .cc-mheader.boy  { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
    .cc-mheader.girl { background: linear-gradient(135deg, #fdf2f8, #fce7f3); }
    .cc-mavatar {
        width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0; position: relative;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        padding: 2px; border: none; cursor: pointer;
    }
    .cc-mheader.boy  .cc-mavatar { background: linear-gradient(135deg, #3b82f6, #93c5fd); }
    .cc-mheader.girl .cc-mavatar { background: linear-gradient(135deg, #ec4899, #f9a8d4); }
    .cc-mavatar-inner {
        width: 100%; height: 100%; border-radius: 50%; background: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
    }
    .cc-mheader-info { flex: 1; min-width: 0; }
    .cc-mheader-name {
        font-family: 'Goldman', monospace; font-size: 1.05rem; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cc-mheader-code {
        display: inline-flex; margin-top: 6px;
        font-family: 'Goldman', monospace; font-size: 0.62rem; font-weight: 700; color: #4338ca;
        background: rgba(255,255,255,0.8); border-radius: 8px; padding: 3px 9px; letter-spacing: 0.04em; cursor: pointer;
    }
    .cc-mheader-edit {
        position: absolute; top: 12px; right: 12px; z-index: 1;
        width: 30px; height: 30px; border-radius: 10px; border: none;
        background: rgba(255,255,255,0.65); color: #4338ca;
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem; cursor: pointer;
    }

    .cc-mstat-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .cc-mstat {
        display: flex; align-items: center; gap: 7px; min-width: 0;
        background: #f8fafc; border-radius: 12px; padding: 9px 11px;
    }
    .cc-mstat-icon { font-size: 0.95rem; flex-shrink: 0; }
    .cc-mstat-val {
        font-family: 'Goldman', monospace; font-size: 0.7rem; font-weight: 700; color: #334155;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
    }
    .cc-mstat.purple  { background: #faf5ff; } .cc-mstat.purple  .cc-mstat-val { color: #7e22ce; }
    .cc-mstat.blue    { background: #eff6ff; } .cc-mstat.blue    .cc-mstat-val { color: #1d4ed8; }
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
        background: linear-gradient(135deg, var(--primary-soft), #e2ddff);
        border: none; cursor: pointer; padding: 0; transition: transform 0.15s;
    }
    .cc-photo:hover { transform: scale(1.02); }
    .cc-photo.boy  { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
    .cc-photo.girl { background: linear-gradient(135deg, #fdf2f8, #fce7f3); }
    @media (min-width: 700px) { .cc-photo { display: flex; width: 150px; } }

    .cc-avatar-ring {
        width: 56px; height: 56px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        padding: 2px; display: flex; align-items: center; justify-content: center;
    }
    .cc-photo.boy  .cc-avatar-ring { background: linear-gradient(135deg, #3b82f6, #93c5fd); }
    .cc-photo.girl .cc-avatar-ring { background: linear-gradient(135deg, #ec4899, #f9a8d4); }
    @media (min-width: 700px) { .cc-avatar-ring { width: 88px; height: 88px; } }

    .cc-avatar-inner {
        width: 100%; height: 100%; border-radius: 50%; background: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.7rem;
    }
    @media (min-width: 700px) { .cc-avatar-inner { font-size: 2.6rem; } }
    .cc-avatar-picker-edit {
        position: absolute; bottom: 8px; right: 8px; width: 24px; height: 24px; border-radius: 50%;
        background: #fff; color: #334155; font-size: 0.65rem; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 1px 4px rgba(0,0,0,0.25); border: 1px solid #e2e8f0;
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
    .cc-name { font-family: 'Goldman', monospace; font-size: 1.2rem; color: #1e293b; letter-spacing: 0.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .cc-code {
        display: inline-flex; flex-shrink: 0;
        font-family: 'Goldman', monospace; font-size: 0.64rem; font-weight: 700; color: #64748b;
        background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; padding: 5px 10px;
        letter-spacing: 0.05em; cursor: pointer; white-space: nowrap; transition: background 0.15s, color 0.15s;
    }
    .cc-code:hover { background: #e2e8f0; color: #334155; }

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
    .cc-mini.purple  { background: #faf5ff; border-color: #f3e8ff; } .cc-mini.purple  .cc-mini-icon { background: #f3e8ff; color: #9333ea; } .cc-mini.purple  .cc-mini-label { color: #c084fc; } .cc-mini.purple  .cc-mini-value { color: #581c87; }
    .cc-mini.blue    { background: #eff6ff; border-color: #dbeafe; } .cc-mini.blue    .cc-mini-icon { background: #dbeafe; color: #2563eb; } .cc-mini.blue    .cc-mini-label { color: #60a5fa; } .cc-mini.blue    .cc-mini-value { color: #1e3a8a; }
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
        width: 44px; height: 44px; border-radius: 16px; flex-shrink: 0; border: 1px solid #e2e8f0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; background: #f1f5f9; color: #334155; cursor: pointer; transition: background 0.15s;
    }
    .cc-mini-btn:hover { background: #e2e8f0; }
    .cc-mini-btn:active { transform: scale(0.98); }

    .cc-full-btn-secondary {
        display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box;
        background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; border-radius: 16px;
        padding: 13px 14px; font-family: 'Goldman', monospace; font-size: 0.7rem; letter-spacing: 0.02em;
        cursor: pointer; transition: background 0.15s;
    }
    .cc-full-btn-secondary:hover { background: #e2e8f0; }
    .cc-full-btn-secondary:active { transform: scale(0.98); }

    .cc-share-panel {
        display: none; flex-direction: column; gap: 8px;
        background: #f8fafc; border-radius: 14px; padding: 12px;
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
    .cc-share-btn:hover { background: #e2ddff; }
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
    .mission-title { font-family: 'Fredoka One', cursive; font-size: 1.2rem; color: #1a1a2e; margin-bottom: 6px; line-height: 1.25; max-width: 60%; }
    .mission-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.78rem; color: #4b4b63; margin-bottom: 14px; max-width: 58%; }
    .mission-cta {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; align-self: flex-start; width: auto; box-sizing: border-box;
        background: #1a1a2e; color: #fff; font-family: 'Fredoka One', cursive; font-size: 1rem;
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
    .stat-card-4-val { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #1a1a2e; }
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
    .mchip.sel { background: #111; border-color: #111; color: #fff; }
    .mchip.sel-green { background: #e8f5e9; border-color: #81c784; color: #2e7d32; }
    .msave {
        width: 100%; background: #111; border: none; border-radius: 8px;
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
    .wiz-btn-next, .wiz-btn-submit { background: #111; border: none; color: #fff; }
    .wiz-btn-next:hover, .wiz-btn-submit:hover { background: #333; }
    .caction {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        border: 1px solid #ebebeb; border-radius: 4px; padding: 5px 10px;
        text-decoration: none; letter-spacing: 0.04em; transition: all 0.2s; white-space: nowrap;
    }
    .caction:hover { color: #333; border-color: #aaa; }
    .caction.primary { color: #4f46e5; border-color: #c7d2fe; background: #eef2ff; }
    .caction.primary:hover { background: #e0e7ff; border-color: #a5b4fc; }
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
        <div class="ks-pp-sec">
            <h1>ჩემი შვილები</h1>
            <span>{{ $children->count() }} შვილი</span>
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
        @php
            $__wkDays = collect(range(6, 0))->map(fn($d) => today()->copy()->subDays($d));
            $__wkLbl  = ['ორ', 'სამ', 'ოთხ', 'ხუთ', 'პარ', 'შაბ', 'კვი'];
            $__allPending = $children->isEmpty() ? collect() : \App\Models\MarketPurchase::with(['item', 'child'])
                ->whereIn('child_id', $children->pluck('id'))->where('status', 'pending')->latest()->get();
        @endphp
        <div class="ks-pp-kids">
            @foreach($children as $child)
            @php
                $s = $child->childSetting;
                $todayDone = $child->tests()->where('is_olympiad', false)->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
                $pendingMarket = $__allPending->where('child_id', $child->id)->count();
                $__wkTests = $child->tests()->whereNotNull('completed_at')
                    ->where('completed_at', '>=', today()->copy()->subDays(6))
                    ->get(['completed_at', 'correct_count', 'total_questions']);
                $__wkCounts = $__wkDays->map(fn($d) => $__wkTests->filter(fn($t) => $t->completed_at->isSameDay($d))->count());
                $__wkMax = max(1, $__wkCounts->max());
                $__wkC = $__wkTests->sum('correct_count'); $__wkT = $__wkTests->sum('total_questions');
                $__wkAvg = $__wkT > 0 ? (int) round($__wkC / $__wkT * 100) : null;
                $__av = $child->avatar === 'boy' ? '👦' : ($child->avatar === 'girl' ? '👧' : '🙂');
                $__last = $child->tests()->whereNotNull('completed_at')->latest('completed_at')->first(['correct_count', 'total_questions']);
                $__lastPct = $__last ? (int) round($__last->correct_count / max(1, $__last->total_questions) * 100) : null;
                $__dates = $child->tests()->whereNotNull('completed_at')->pluck('completed_at')->map(fn($d) => $d->format('Y-m-d'))->unique();
                $__streak = 0; $__cur = today();
                if (!$__dates->contains($__cur->format('Y-m-d'))) { $__cur = $__cur->copy()->subDay(); }
                while ($__dates->contains($__cur->format('Y-m-d'))) { $__streak++; $__cur = $__cur->copy()->subDay(); }
                $__link = $child->child_code ? route('child.magic-login', $child->child_code) : null;
            @endphp
            <div class="ks-pp-card {{ !$child->is_active ? 'off' : '' }}">
                @if(!$child->is_active)
                <div class="ks-pp-off">
                    <span>⊘ პროფილი გათიშულია</span>
                    <button type="button" onclick="document.getElementById('plansModal').classList.add('open')">გეგმა ↑</button>
                </div>
                @endif

                <div class="ks-pp-card-h">
                    <button type="button" class="ks-pp-av {{ $child->avatar }}" title="პროფილის არჩევა"
                        onclick="document.getElementById('avatarModal{{ $child->id }}').classList.add('open')">{{ $__av }}</button>
                    <div class="ks-pp-name">
                        <b>{{ $child->name }}</b>
                        <span>{{ $s?->grade?->name ?? 'კლასი —' }}{{ $s ? ' · დონე ' . $s->difficulty . '/' . ($s->grade?->max_level ?? \App\Models\Grade::DEFAULT_MAX_LEVEL) : '' }}</span>
                    </div>
                    @if($todayDone > 0)
                    <span class="ks-pp-status ok">დღეს აქტიურია</span>
                    @else
                    <span class="ks-pp-status">0/1 ტესტი</span>
                    @endif
                    <button type="button" class="ks-pp-gear" title="პარამეტრები"
                        onclick="document.getElementById('editChildModal{{ $child->id }}').classList.add('open')"><i class="bi bi-gear"></i></button>
                </div>

                <div class="ks-pp-mini">
                    <div><b>{{ min($todayDone, 1) }}/1</b><span>ტესტი დღეს</span></div>
                    <div><b>{{ $__lastPct !== null ? $__lastPct . '%' : '—' }}</b><span>ბოლო შედეგი</span></div>
                    <div><b>{{ $__streak }} დღე</b><span>ზედიზედ</span></div>
                </div>

                <div class="ks-pp-week">
                    <div class="ks-pp-week-h">ბოლო 7 დღე <span>{{ $__wkTests->count() }} ტესტი{{ $__wkAvg !== null ? ' · საშ. ' . $__wkAvg . '%' : '' }}</span></div>
                    <div class="ks-pp-bars" role="img" aria-label="ტესტები ბოლო 7 დღეში: {{ $__wkCounts->implode(', ') }}">
                        @foreach($__wkDays as $__di => $__d)
                        @php $__n = $__wkCounts[$__di]; @endphp
                        <div class="ks-pp-bar" title="{{ $__d->format('d.m') }} — {{ $__n }} ტესტი">
                            <i class="{{ $__n === 0 ? 'zero' : ($__d->isToday() ? 'today' : '') }}" style="height: {{ $__n === 0 ? 4 : max(12, round($__n / $__wkMax * 64)) }}px"></i>
                            <span>{{ $__wkLbl[$__d->dayOfWeekIso - 1] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($todayDone === 0 && $child->is_active)
                <button type="button" class="ks-pp-remind" onclick="openRemind({{ $child->id }}, '{{ addslashes($child->name) }}')">
                    <i class="bi bi-bell"></i> შეხსენების გაგზავნა
                </button>
                @endif
                @if(session('reminder_sent_' . $child->id))
                <div class="ks-pp-remind sent"><i class="bi bi-check-lg"></i> შეხსენება გაიგზავნა</div>
                @endif

                <div class="ks-pp-btns">
                    <a href="{{ route('child.hub', $child) }}" class="ks-pp-btn dark">სრულად <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ route('child.stats', $child) }}" class="ks-pp-btn" title="ანალიტიკა"><i class="bi bi-bar-chart-line"></i></a>
                    <a href="{{ route('market.index', $child) }}" class="ks-pp-btn" title="მარკეტი"><i class="bi bi-bag"></i>@if($pendingMarket)<span class="ks-pnav-badge">{{ $pendingMarket }}</span>@endif</a>
                    @if($child->child_code)
                    <button type="button" class="ks-pp-btn" title="შესვლის გაზიარება" onclick="toggleShare({{ $child->id }})"><i class="bi bi-link-45deg"></i></button>
                    @endif
                </div>

                @if($child->child_code)
                {{-- გაზიარება: კოდი / ბმული / QR — რომელსაც აირჩევს, ის გაზიარდება --}}
                <div class="cc-share-panel ks-share" id="sharePanel{{ $child->id }}" data-name="{{ $child->name }}" data-code="{{ $child->child_code }}" data-link="{{ $__link }}">
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
                @endif
            </div>
            @endforeach
        </div>

        <button type="button" class="ks-pp-addkid"
            onclick="document.getElementById('{{ $atChildLimit ? 'plansModal' : 'addChildModal' }}').classList.add('open'); {{ $atChildLimit ? '' : 'wizReset();' }}"
            @if($atChildLimit) title="{{ $currentPkg->name }} პლანი მხოლოდ {{ $currentPkg->max_children }} ბავშვს იძლევა" @endif>
            <i class="bi bi-plus-lg"></i> {{ $atChildLimit ? 'გეგმის განახლება — შვილის დასამატებლად' : 'შვილის დამატება' }}
        </button>

        @if($__allPending->isNotEmpty())
        @php $__fp = $__allPending->first(); @endphp
        <a href="{{ route('market.index', $__fp->child_id) }}" class="ks-pp-req">
            <span class="ks-pp-req-ic"><i class="bi bi-bag"></i></span>
            <span class="ks-pp-req-t">
                <b>{{ $__allPending->count() }} ახალი მოთხოვნა მარკეტში</b>
                <small>{{ $__fp->child?->name }}-ს სურს: {{ $__fp->item?->title }} · {{ $__fp->coins_spent }} მონეტა</small>
            </span>
            <i class="bi bi-chevron-right"></i>
        </a>
        @endif
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

        {{-- ადმინის ხედი --}}
        @if(auth()->user()->role === 'admin')
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



        @if(auth()->user()->isAdmin())
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

            {{-- ავატარი --}}
            <div class="ks-set-avs" role="radiogroup" aria-label="ავატარი">
                @foreach(['boy' => '👦', 'girl' => '👧'] as $__ak => $__ae)
                <label class="ks-set-av {{ $__ak }}">
                    <input type="radio" name="avatar" value="{{ $__ak }}" @checked($child->avatar === $__ak)>
                    <span>{{ $__ae }}</span>
                </label>
                @endforeach
            </div>

            <div class="ks-set-card">
                <div class="ks-set-row">
                    <label for="ename{{ $child->id }}">სახელი</label>
                    <input type="text" id="ename{{ $child->id }}" name="name" value="{{ $child->name }}" maxlength="50" autocomplete="off">
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
            <div class="ks-set-card">
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

<div id="avatarModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox" style="max-width:320px;">
        <div class="modal-title">
            პროფილის არჩევა
            <button type="button" class="modal-close"
                onclick="document.getElementById('avatarModal{{ $child->id }}').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('child.avatar.update', $child) }}"
            style="display:flex;gap:14px;justify-content:center;padding:8px 0 4px;">
            @csrf @method('PUT')
            <button type="submit" name="avatar" value="boy" style="background:{{ $child->avatar === 'boy' ? '#eef2ff' : '#fafafa' }};border:2px solid {{ $child->avatar === 'boy' ? '#818cf8' : '#eee' }};border-radius:14px;padding:18px 20px;font-size:2.2rem;cursor:pointer;transition:all 0.15s;">👦</button>
            <button type="submit" name="avatar" value="girl" style="background:{{ $child->avatar === 'girl' ? '#fdf2f8' : '#fafafa' }};border:2px solid {{ $child->avatar === 'girl' ? '#f9a8d4' : '#eee' }};border-radius:14px;padding:18px 20px;font-size:2.2rem;cursor:pointer;transition:all 0.15s;">👧</button>
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
    <div class="mbox">
        {{-- Tab switcher --}}
        <div style="display:flex;gap:0;margin-bottom:16px;border-bottom:2px solid #f0f0f0;">
            <button type="button" id="tabNew" onclick="switchChildTab('new')"
                style="flex:1;background:none;border:none;border-bottom:2px solid #111;margin-bottom:-2px;
                font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.06em;color:#111;
                padding:8px;cursor:pointer;">
                + ახალი ბავშვი
            </button>
            <button type="button" id="tabLink" onclick="switchChildTab('link')"
                style="flex:1;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;
                font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.06em;color:#aaa;
                padding:8px;cursor:pointer;">
                🔗 კოდით მიბმა
            </button>
        </div>

        {{-- Tab: Link by code --}}
        <div id="panelLink" style="display:none;">
            <div class="modal-title" style="margin-bottom:12px;">
                კოდით მიბმა
                <button type="button" class="modal-close" onclick="document.getElementById('addChildModal').classList.remove('open')">✕</button>
            </div>
            <p style="font-family:'Goldman',monospace;font-size:0.7rem;color:#888;letter-spacing:0.04em;margin:0 0 16px;">
                შვილის კოდი ჩაწერე — ბავშვი შენს ანგარიშსაც დაემატება
            </p>
            <form method="POST" action="{{ route('child.link') }}">
                @csrf
                <div class="mlbl">ბავშვის კოდი <span>*</span></div>
                <input type="text" name="child_code" class="minput" placeholder="მაგ: AB3K7F"
                    value="{{ old('child_code') }}" required maxlength="8" autocomplete="off"
                    style="text-transform:uppercase;letter-spacing:0.12em;">
                @error('child_code_link')<div class="merr">{{ $message }}</div>@enderror
                <button type="submit" class="msave">🔗 მიბმა</button>
            </form>
        </div>

        {{-- Tab: New child --}}
        <div id="panelNew">
        <form method="POST" action="{{ route('child.store') }}" id="addChildForm">
            @csrf
            <div class="modal-title">
                შვილის დამატება
                <button type="button" class="modal-close" onclick="document.getElementById('addChildModal').classList.remove('open')">✕</button>
            </div>


            {{-- Step 1: Name + gender --}}
            <div class="wiz-step" data-step="1">
                <div class="wiz-step-title">ვინ არის შენი შვილი?</div>

                <div class="mlbl">სახელი <span>*</span></div>
                <input type="text" name="name" id="wiz_name" class="minput" placeholder="სახელი"
                    value="{{ old('name') }}" required maxlength="50" autocomplete="off">
                @error('name')<div class="merr">{{ $message }}</div>@enderror
                <div class="merr" id="wizNameErr" style="display:none;">შეიყვანე სახელი</div>

                <div class="mlbl">სქესი <span>*</span></div>
                <div class="mrow" id="genderRow">
                    <label class="mchip {{ old('avatar') == 'boy' ? 'sel' : '' }}"
                        onclick="chipSingle(this,'avatar_input','boy')">👦 ბიჭი</label>
                    <label class="mchip {{ old('avatar') == 'girl' ? 'sel' : '' }}"
                        onclick="chipSingle(this,'avatar_input','girl')">👧 გოგონა</label>
                </div>
                <input type="hidden" name="avatar" id="avatar_input" value="{{ old('avatar') }}">
                @error('avatar')<div class="merr">{{ $message }}</div>@enderror
                <div class="merr" id="wizGenderErr" style="display:none;">აირჩიე სქესი</div>
            </div>

            {{-- Step 2: Grade --}}
            <div class="wiz-step" data-step="2">
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
            <div class="wiz-step" data-step="3">
                <div class="wiz-step-title">ბოლო ნაბიჯი — თემატიკა</div>

                @if($themes->count())
                @php
                    $addThemeOld = old('theme_ids', []);
                    $addDefaultThemeId = $defaultThemeId ?? null;
                @endphp
                <div class="mlbl">თემატიკა <span style="color:#aaa;font-size:0.9em;">(სურვილისამებრ)</span></div>
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
{{-- Plans Modal --}}
@if(in_array(auth()->user()->role, ['parent', 'admin']))
<div id="plansModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox" style="max-width:520px;">
        <div class="modal-title">
            სატარიფო გეგმები
            <button type="button" class="modal-close" onclick="document.getElementById('plansModal').classList.remove('open')">✕</button>
        </div>
        @php $atChildLimit = isset($currentPkg) && $currentPkg->max_children > 0 && isset($children) && $children->count() >= $currentPkg->max_children; @endphp
        @if($atChildLimit)
        <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:0.72rem;color:#92400e;">
            თქვენი მიმდინარე გეგმა (<strong>{{ $currentPkg->name }}</strong>) მხოლოდ <strong>{{ $currentPkg->max_children }}</strong> ბავშვს იძლევა. მეტი ბავშვის დასამატებლად გადადით უფრო მაღალ გეგმაზე.
        </div>
        @endif
        <div style="font-family:'Goldman',monospace;font-size:0.65rem;color:#94a3b8;letter-spacing:0.06em;margin-bottom:14px;">
            გეგმის შესაცვლელად მიმართეთ ადმინს
        </div>

        @php $allPkgs = App\Models\Package::where('is_active', true)->orderBy('sort_order')->get(); @endphp

        @foreach($allPkgs as $pkg)
        @php $isCurrent = $currentPkg->id && $currentPkg->id === $pkg->id; @endphp
        <div style="border:{{ $isCurrent ? '2px solid #3b82f6' : '1px solid #e2e8f0' }};border-radius:10px;padding:16px 18px;margin-bottom:10px;position:relative;">
            @if($isCurrent)
            <span style="position:absolute;top:10px;right:12px;background:#eff6ff;color:#3b82f6;font-size:0.58rem;padding:2px 8px;border-radius:8px;font-family:'Goldman',monospace;letter-spacing:0.06em;">მიმდინარე</span>
            @endif
            <div style="font-family:'Goldman',monospace;font-size:0.88rem;font-weight:700;color:#1e293b;margin-bottom:4px;">{{ $pkg->name }}</div>
            @if($pkg->description)
            <div style="font-size:0.7rem;color:#64748b;margin-bottom:10px;">{{ $pkg->description }}</div>
            @endif
            <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:0.7rem;color:#374151;margin-bottom:10px;">
                @if($pkg->is_free)
                    <span>✓ უფასო</span>
                @else
                    @if($pkg->price_monthly > 0)<span>{{ number_format($pkg->price_monthly, 2) }}₾ / თვე</span>@endif
                    @if($pkg->price_yearly > 0)<span>{{ number_format($pkg->price_yearly, 2) }}₾ / წელი</span>@endif
                @endif
                <span>👶 {{ $pkg->max_children === 0 ? 'შეუზღ.' : $pkg->max_children }} ბავშვი</span>
                <span>⭐ სირთ. {{ $pkg->max_difficulty === 5 ? 'ყველა' : '1–'.$pkg->max_difficulty }}</span>
            </div>
            @if(!$isCurrent && !$pkg->is_free)
            <div style="font-family:'Goldman',monospace;font-size:0.62rem;color:#94a3b8;letter-spacing:0.04em;">
                გეგმის გასააქტიურებლად დაუკავშირდით ადმინს
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endpush

<script>
// redesign-kidsmart: გვერდითი მენიუდან „გეგმის შეცვლა“ → dashboard#plans
if (location.hash === '#plans') { document.addEventListener('DOMContentLoaded', function () { var m = document.getElementById('plansModal'); if (m) m.classList.add('open'); }); }

function switchChildTab(tab) {
    const isNew = tab === 'new';
    document.getElementById('panelNew').style.display  = isNew ? '' : 'none';
    document.getElementById('panelLink').style.display = isNew ? 'none' : '';
    document.getElementById('tabNew').style.borderBottomColor  = isNew ? '#111' : 'transparent';
    document.getElementById('tabNew').style.color  = isNew ? '#111' : '#aaa';
    document.getElementById('tabLink').style.borderBottomColor = isNew ? 'transparent' : '#111';
    document.getElementById('tabLink').style.color = isNew ? '#aaa' : '#111';
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
