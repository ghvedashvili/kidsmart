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
        padding: 40px 24px 60px;
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
        display: flex; align-items: center; justify-content: space-between; gap: 16px; text-align: left;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        box-shadow: 0 10px 24px rgba(108,92,231,0.25);
    }
    .parent-hero-text h2 { font-family: 'Goldman', monospace; font-size: 1.05rem; color: #fff; margin: 0 0 10px; letter-spacing: 0.02em; }
    .parent-hero-avatar { font-size: 2.6rem; line-height: 1; flex-shrink: 0; filter: drop-shadow(0 6px 6px rgba(0,0,0,0.15)); }

    .plan-pill {
        display: inline-flex; align-items: center; gap: 7px;
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);
        border-radius: 100px; padding: 6px 14px; font-family: 'Goldman', monospace;
        font-size: 0.66rem; color: #fff; cursor: pointer; letter-spacing: 0.03em; transition: background 0.2s;
    }
    .plan-pill:hover { background: rgba(255,255,255,0.28); }

    .child-hero-banner {
        width: 100%; box-sizing: border-box; border-radius: var(--radius-lg); padding: 22px 20px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px; text-align: left;
        background: linear-gradient(135deg, var(--primary-light), var(--accent-blue));
        box-shadow: 0 10px 24px rgba(108,92,231,0.25);
    }
    .child-hero-text h2 { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #2d2d3a; margin: 0 0 4px; }
    .child-hero-text p { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.78rem; color: #4b4b63; margin: 0; }
    .child-hero-avatar { font-size: 3.2rem; line-height: 1; flex-shrink: 0; filter: drop-shadow(0 6px 6px rgba(0,0,0,0.15)); }

    .children-section { width: 100%; }
    .section-label {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; text-align: left;
    }
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

    /* ── child card (parent view) — colored side panel + main info panel ── */
    .children-grid { display: grid; grid-template-columns: 1fr; gap: 10px; width: 100%; }
    .child-card {
        background: #fff; border-radius: var(--radius-lg); overflow: hidden;
        display: flex; align-items: stretch; height: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;
    }
    .cc-side {
        width: 78px; flex-shrink: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
        padding: 12px 6px; background: linear-gradient(160deg, var(--primary), var(--primary-light));
    }
    .children-grid .child-card:nth-child(3n+2) .cc-side { background: linear-gradient(160deg, var(--accent-pink), #fca5a5); }
    .children-grid .child-card:nth-child(3n+3) .cc-side { background: linear-gradient(160deg, #2fae74, var(--accent-green)); }
    .cc-side-name {
        font-family: 'Goldman', monospace; font-size: 0.72rem; color: #fff; text-align: center;
        letter-spacing: 0.02em; line-height: 1.25; max-width: 100%;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .cc-avatar-picker {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; position: relative;
        background: rgba(255,255,255,0.22); border: 2px dashed rgba(255,255,255,0.6);
        color: #fff; font-size: 1.3rem; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: background 0.2s;
    }
    .cc-avatar-picker:hover { background: rgba(255,255,255,0.32); }
    .cc-avatar-picker-edit {
        position: absolute; bottom: -2px; right: -2px; width: 16px; height: 16px; border-radius: 50%;
        background: #fff; color: #666; font-size: 0.5rem; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    }

    .cc-main { flex: 1; min-width: 0; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
    .cc-top-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 6px; }
    .cc-tags { display: flex; gap: 4px; flex-wrap: wrap; min-width: 0; }
    .cc-tag {
        font-family: 'Goldman', monospace; font-size: 0.55rem; color: var(--primary);
        background: var(--primary-soft); border: none; border-radius: 100px;
        padding: 3px 8px; white-space: nowrap;
    }
    .cc-mini-actions { display: flex; gap: 4px; flex-shrink: 0; }
    .cc-mini-btn {
        width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.66rem; border: none; cursor: pointer; text-decoration: none;
    }
    .cc-mini-btn.remind { background: #ecfdf5; color: #059669; }
    .cc-mini-btn.edit { background: #f5f5f5; color: #888; }
    .cc-code {
        font-family: 'Goldman', monospace; font-size: 0.58rem; color: #bbb;
        letter-spacing: 0.1em; cursor: pointer; white-space: nowrap;
    }
    .cc-code:hover { color: #888; }

    .cc-stats-row { display: flex; gap: 6px; }
    .cc-stat {
        flex: 1; min-width: 0; text-align: center; background: var(--primary-soft); border-radius: var(--radius-sm);
        padding: 8px 4px; display: flex; flex-direction: column; align-items: center; gap: 2px;
    }
    .cc-stat-icon { font-size: 0.9rem; line-height: 1; }
    .cc-stat-val { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #111; }
    .cc-stat-lbl { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.46rem; color: #aaa; text-transform: uppercase; letter-spacing: 0.02em; }

    .cc-tiles-row { display: flex; gap: 6px; margin-top: auto; }
    .cc-tile {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
        background: var(--primary-soft); border-radius: var(--radius-sm); padding: 10px 6px; position: relative;
        text-decoration: none; transition: background 0.2s;
    }
    .cc-tile:hover { background: #e2ddff; }
    .cc-tile-icon { font-size: 1.1rem; }
    .cc-tile-label { font-family: 'Goldman', monospace; font-size: 0.58rem; color: #555; letter-spacing: 0.02em; }
    .cc-tile-badge {
        position: absolute; top: 6px; right: 8px; background: #dc2626; color: #fff;
        font-family: 'Goldman', monospace; font-size: 0.5rem; font-weight: 700;
        border-radius: 100px; padding: 1px 5px; min-width: 14px; text-align: center;
    }

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


    /* ── badges preview — same card language as .summary-card ── */
    .badges-section {
        width: 100%; box-sizing: border-box; background: #fff; border-radius: var(--radius-lg);
        padding: 18px 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1.5px solid #f0f0f0;
        text-align: left;
    }
    .badges-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .badges-header h3 { font-family: 'Fredoka One', cursive; font-size: 0.95rem; color: #2d2d3a; margin: 0; }
    .badges-header a { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.68rem; color: var(--primary); text-decoration: none; }
    .badges-group { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .badge-card { display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .badge-icon {
        width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; background: var(--primary-soft);
    }
    .badge-card span { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.55rem; color: #444; text-align: center; line-height: 1.2; }
    .badges-empty {
        font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.72rem; color: #bbb; text-align: center;
        padding: 10px 0 2px;
    }

    /* ── weekly summary card ── */
    .summary-card {
        width: 100%; box-sizing: border-box; background: #fff; border-radius: var(--radius-lg);
        padding: 18px 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1.5px solid #f0f0f0;
        text-align: left;
    }
    .summary-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .summary-streak { display: flex; align-items: center; gap: 8px; }
    .summary-streak-flame { font-size: 1.7rem; line-height: 1; }
    .summary-streak-num { font-family: 'Fredoka One', cursive; font-size: 1.05rem; color: #111; line-height: 1.2; }
    .summary-streak-lbl { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.58rem; color: #aaa; text-transform: uppercase; letter-spacing: 0.05em; }
    .summary-week-pill {
        background: var(--primary-soft); color: var(--primary); font-family: 'Goldman', monospace;
        font-size: 0.64rem; padding: 6px 12px; border-radius: 100px; letter-spacing: 0.02em; white-space: nowrap; flex-shrink: 0;
    }
    .summary-days { display: flex; justify-content: space-between; gap: 4px; margin-bottom: 14px; }
    .summary-day { display: flex; flex-direction: column; align-items: center; gap: 5px; flex: 1; }
    .summary-day-dot {
        width: 30px; height: 30px; border-radius: 50%; background: #f5f5f5;
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
        border: 2px solid transparent; box-sizing: border-box;
    }
    .summary-day.done .summary-day-dot { background: linear-gradient(135deg, var(--accent-yellow), #f59e0b); }
    .summary-day.today .summary-day-dot { border-color: var(--primary); }
    .summary-day-lbl { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.55rem; color: #bbb; }
    .summary-day.today .summary-day-lbl { color: var(--primary); }
    .summary-bottom { display: flex; gap: 10px; padding-top: 12px; border-top: 1px solid #f5f5f5; }
    .summary-stat { flex: 1; text-align: center; }
    .summary-stat-val { font-family: 'Fredoka One', cursive; font-size: 1.05rem; color: #111; display: block; }
    .summary-stat-lbl { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.56rem; color: #aaa; text-transform: uppercase; letter-spacing: 0.04em; }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        backdrop-filter: blur(4px); z-index: 150;
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
    .remind-modal { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:200;display:none;flex-direction:column;align-items:center;justify-content:flex-start;padding:80px 16px 48px;overflow-y:auto; }
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
    .flash { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #2ecc71; letter-spacing: 0.06em; }
    .flash-err { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #e74c3c; letter-spacing: 0.06em; }
    /* ── child view ── */
    .games-card {
        display: flex; align-items: center; gap: 14px; width: 100%; box-sizing: border-box;
        background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: var(--radius-lg);
        padding: 16px 18px; text-decoration: none; color: #fff; text-align: left;
        box-shadow: 0 8px 20px rgba(22,163,74,0.3); transition: transform 0.15s;
    }
    .games-card:hover { transform: translateY(-2px); color: #fff; }
    .games-card-icon { font-size: 2.2rem; flex-shrink: 0; }
    .games-card-text { flex: 1; min-width: 0; }
    .games-card-title { font-family: 'Fredoka One', cursive; font-size: 1rem; margin-bottom: 2px; }
    .games-card-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.7rem; opacity: 0.9; }
    .games-card-arrow { font-size: 1.1rem; flex-shrink: 0; opacity: 0.85; }

    .child-stats-row {
        display: flex; gap: 10px; width: 100%;
    }
    .cstat {
        flex: 1; background: white; border-radius: var(--radius-md);
        padding: 14px 8px; text-align: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        text-decoration: none; transition: transform 0.15s;
        border: 1.5px solid #f0f0f0;
    }
    .cstat:hover { transform: translateY(-2px); }
    .cstat-val {
        font-family: 'Fredoka One', cursive;
        font-size: 1.25rem; color: #111; margin-bottom: 2px;
    }
    .cstat-val sub { font-size: 0.82rem; color: #bbb; font-family: 'Nunito', sans-serif; }
    .cstat-lbl {
        font-family: 'Nunito', sans-serif; font-weight: 800;
        font-size: 0.62rem; color: #bbb; text-transform: uppercase; letter-spacing: 0.08em;
    }
    .child-cta {
        display: flex; flex-direction: column; gap: 12px;
        width: 100%; box-sizing: border-box; padding: 18px 20px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white; text-decoration: none; border-radius: var(--radius-lg);
        box-shadow: 0 6px 24px rgba(108,92,231,0.35);
        transition: all 0.2s;
    }
    .child-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(108,92,231,0.45); color: white; }
    .child-cta.resume {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 6px 24px rgba(245,158,11,0.35);
    }
    .child-cta.resume:hover { box-shadow: 0 10px 32px rgba(245,158,11,0.45); }
    .child-cta-main { display: flex; align-items: center; gap: 12px; }
    .child-cta-icon { font-size: 1.6rem; flex-shrink: 0; line-height: 1; }
    .child-cta-text { flex: 1; min-width: 0; text-align: left; }
    .child-cta-title { font-family: 'Fredoka One', cursive; font-size: 1.1rem; letter-spacing: 0.02em; line-height: 1.2; }
    .child-cta-arrow { font-size: 1.2rem; flex-shrink: 0; opacity: 0.85; }
    .child-done-card {
        background: white; border-radius: var(--radius-lg); padding: 28px 20px;
        text-align: center; width: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 1.5px solid #f0f0f0;
    }
    .child-done-icon { font-size: 2.8rem; margin-bottom: 8px; }
    .child-done-title { font-family: 'Fredoka One', cursive; font-size: 1.4rem; color: #1a7a3c; margin-bottom: 4px; }
    .child-done-sub { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.8rem; color: #aaa; }
    .child-waiting-card {
        background: white; border-radius: var(--radius-lg); padding: 28px 20px;
        text-align: center; width: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 1.5px solid #f0f0f0;
    }
    .child-waiting-icon { font-size: 2.5rem; margin-bottom: 8px; }
    .child-waiting-txt { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.88rem; color: #888; line-height: 1.6; }
    .child-last-card {
        background: white; border-radius: 14px; padding: 14px 18px;
        width: 100%; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1.5px solid #f0f0f0;
        display: flex; align-items: center; gap: 14px;
    }
    .clt-bar { width: 3px; height: 36px; border-radius: 99px; background: #e8f5ee; flex-shrink: 0; overflow: hidden; }
    .clt-bar-fill { width: 100%; background: #1a7a3c; border-radius: 99px; transition: height 0.6s; }
    .clt-info { flex: 1; }
    .clt-lbl { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.62rem; color: #bbb; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 3px; }
    .clt-score { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #111; }
    .clt-pct { font-size: 0.85rem; color: #1a7a3c; margin-left: 6px; }
    .clt-time { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.65rem; color: #ccc; flex-shrink: 0; }

    /* ── responsive widths + multi-column grids on desktop (kept last so it wins the cascade) ── */
    @media (min-width: 760px) {
        .dash-inner { max-width: 700px; }
        .children-grid { grid-template-columns: repeat(2, 1fr); }
        .nav-tile-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (min-width: 1040px) {
        .dash-inner { max-width: 960px; }
        .nav-tile-grid { grid-template-columns: repeat(6, 1fr); }
    }
</style>

<div class="dash-hero">
    <div class="dash-inner">

        @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
        @endif
        @if(session('test_error'))
        <div class="flash-err">{{ session('test_error') }}</div>
        @endif
        @if(session('test_done'))
        <div class="flash">{{ session('test_done') }}</div>
        @endif

        @if(auth()->user()->role !== 'child')
        @php
            if (in_array(auth()->user()->role, ['parent', 'admin'])) {
                $currentPkg = auth()->user()->currentPackage();
                $activeSub  = auth()->user()->activeSubscription();
            }
        @endphp
        <div class="parent-hero-banner">
            <div class="parent-hero-text">
                <h2>გამარჯობა, {{ auth()->user()->name }}! 👋</h2>
                @if(in_array(auth()->user()->role, ['parent', 'admin']))
                <button type="button" class="plan-pill" onclick="document.getElementById('plansModal').classList.add('open')">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $currentPkg->is_free ? '#10b981' : '#93c5fd' }};display:inline-block;"></span>
                    {{ $currentPkg->name }}
                    @if($activeSub?->expires_at)
                        <span style="opacity:0.7;font-size:0.6rem;">· {{ $activeSub->expires_at->format('d.m.Y') }}-მდე</span>
                    @endif
                    <span style="opacity:0.8;font-size:0.62rem;">↑ გეგმა</span>
                </button>
                @endif
            </div>
            <div class="parent-hero-avatar">👨‍👩‍👧</div>
        </div>
        @endif

        {{-- მშობლის ხედი --}}
        @if(in_array(auth()->user()->role, ['parent', 'admin']))
        @php
            $children = auth()->user()->children()->with(['childSetting.grade','themes','topics'])->withTimestamps()->orderByPivot('created_at','asc')->get();
        @endphp

        @if(session('child_added'))
        <div class="flash">{{ session('child_added') }}</div>
        @endif

        <div class="children-section">
            <div class="section-label">შვილები · {{ $children->count() }}</div>

            @php $atChildLimit = $currentPkg->max_children > 0 && $children->count() >= $currentPkg->max_children; @endphp
            <div class="children-grid">
            @forelse($children as $child)
            @php
                $s = $child->childSetting;
                $todayDone = $child->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
                $pendingMarket = \App\Models\MarketPurchase::where('child_id', $child->id)->where('status','pending')->count();
            @endphp
            <div class="child-card" style="{{ !$child->is_active ? 'opacity:0.55;' : '' }}">
                <div class="cc-side">
                    <div class="cc-side-name">{{ $child->name }}</div>
                    <button type="button" class="cc-avatar-picker" title="პროფილის არჩევა"
                        onclick="document.getElementById('avatarModal{{ $child->id }}').classList.add('open')">
                        {{ $child->avatar === 'boy' ? '👦' : ($child->avatar === 'girl' ? '👧' : '👤') }}
                        <span class="cc-avatar-picker-edit">✎</span>
                    </button>
                </div>
                <div class="cc-main">
                    @if(!$child->is_active)
                    <div style="display:flex;align-items:center;gap:6px;background:#fef2f2;border:1px solid #fecaca;border-radius:5px;padding:5px 8px;font-size:0.6rem;color:#dc2626;">
                        <span>⊘</span>
                        <span>გათიშული</span>
                        <button type="button" onclick="document.getElementById('plansModal').classList.add('open')"
                            style="margin-left:auto;background:none;border:1px solid #fca5a5;color:#dc2626;font-family:'Goldman',monospace;font-size:0.56rem;padding:2px 7px;border-radius:3px;cursor:pointer;white-space:nowrap;">↑ გეგმა</button>
                    </div>
                    @endif
                    <div class="cc-top-row">
                        <div class="cc-tags">
                            <span class="cc-tag">{{ $s?->grade?->name ?? 'კლასი —' }}</span>
                            @if($s)
                            <span class="cc-tag">დონე {{ $s->difficulty }}</span>
                            @endif
                        </div>
                        <div class="cc-mini-actions">
                            <button type="button" class="cc-mini-btn remind" title="შეხსენება"
                                onclick="openRemind({{ $child->id }}, '{{ addslashes($child->name) }}')">🔔</button>
                            <button type="button" class="cc-mini-btn edit" title="რედაქტირება"
                                onclick="document.getElementById('editChildModal{{ $child->id }}').classList.add('open')">⚙</button>
                        </div>
                    </div>
                    @if($child->child_code)
                    <span class="cc-code" onclick="copyChildCode(this, '{{ $child->child_code }}')"
                        title="კოდის კოპირება">{{ $child->child_code }}</span>
                    @endif
                    @if($s)
                    <div class="cc-stats-row">
                        <div class="cc-stat">
                            <span class="cc-stat-icon">📅</span>
                            <span class="cc-stat-val">{{ $todayDone }}/{{ $s->tests_per_week }}</span>
                            <span class="cc-stat-lbl">დღეს</span>
                        </div>
                        <div class="cc-stat">
                            <span class="cc-stat-icon">💰</span>
                            <span class="cc-stat-val">{{ $s->coins ?? 0 }}</span>
                            <span class="cc-stat-lbl">მონეტა</span>
                        </div>
                    </div>
                    @endif
                    <div class="cc-tiles-row">
                        <a href="{{ route('market.index', $child) }}" class="cc-tile">
                            <span class="cc-tile-icon">🛒</span>
                            <span class="cc-tile-label">მარკეტი</span>
                            @if($pendingMarket)<span class="cc-tile-badge">{{ $pendingMarket }}</span>@endif
                        </a>
                        <a href="{{ route('child.stats', $child) }}" class="cc-tile">
                            <span class="cc-tile-icon">📊</span>
                            <span class="cc-tile-label">სტატისტიკა</span>
                        </a>
                    </div>
                    @if(session('reminder_sent_' . $child->id))
                    <div style="font-family:'Goldman',monospace;font-size:0.6rem;color:#059669;">&#10003; შეხსენება გაიგზავნა</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="no-children">
                ბავშვი ჯერ არ დარეგისტრირებულა<br>
                <span style="font-size:0.62rem;color:#ccc;margin-top:4px;display:block;">კოდი გაუზიარე შვილს</span>
            </div>
            @endforelse
            <button type="button" class="add-card"
                onclick="document.getElementById('{{ $atChildLimit ? 'plansModal' : 'addChildModal' }}').classList.add('open')"
                @if($atChildLimit) title="{{ $currentPkg->name }} პლანი მხოლოდ {{ $currentPkg->max_children }} ბავშვს იძლევა" @endif>
                <span class="add-card-plus">+</span>
                <span class="add-card-label">
                    @if($atChildLimit)
                        ↑ გეგმის განახლება
                    @else
                        შვილის დამატება
                    @endif
                </span>
            </button>
            </div>
        </div>

        {{-- Edit modals (one per child) --}}
        @foreach($children as $child)
        @php
            $es = $child->childSetting;
            $eThemeIds = $child->themes->pluck('id')->toArray();
        @endphp
        <div id="editChildModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
            <div class="mbox">
                {{-- Header bar — same style as add modal tab bar --}}
                <div style="display:flex;align-items:center;gap:0;margin-bottom:16px;border-bottom:2px solid #f0f0f0;">
                    <span style="flex:1;font-family:'Goldman',monospace;font-size:0.72rem;letter-spacing:0.06em;
                        color:#111;padding:8px 0;border-bottom:2px solid #111;margin-bottom:-2px;text-align:left;">
                        ✏️ {{ $child->name }}
                    </span>
                    <button type="button" class="modal-close" style="margin-bottom:-2px;"
                        onclick="document.getElementById('editChildModal{{ $child->id }}').classList.remove('open')">✕</button>
                </div>

                <form method="POST" action="{{ route('child.settings.update', $child) }}">
                    @csrf @method('PUT')

                    <div class="mlbl">სახელი</div>
                    <input type="text" name="name" class="minput" value="{{ $child->name }}" maxlength="50" autocomplete="off">

                    <div class="mlbl">კლასი <span>*</span></div>
                    <div class="mrow">
                        @foreach($grades as $grade)
                        <label class="mchip {{ $es?->grade_id == $grade->id ? 'sel' : '' }}"
                            onclick="chipSingle(this,'egid{{ $child->id }}','{{ $grade->id }}')">{{ $grade->name }}</label>
                        @endforeach
                    </div>
                    <input type="hidden" name="grade_id" id="egid{{ $child->id }}" value="{{ $es?->grade_id }}">

                    <div class="mlbl">ტესტი დღეში</div>
                    <div class="mrow">
                        @for($i=1; $i<=5; $i++)
                        <label class="mchip {{ ($es?->tests_per_week ?? 3) == $i ? 'sel' : '' }}"
                            onclick="chipSingle(this,'etpw{{ $child->id }}','{{ $i }}')">{{ $i }}</label>
                        @endfor
                    </div>
                    <input type="hidden" name="tests_per_week" id="etpw{{ $child->id }}" value="{{ $es?->tests_per_week ?? 3 }}">

                    <input type="hidden" name="difficulty" value="{{ $es?->difficulty ?? 1 }}">

                    @if($themes->count())
                    <div class="mlbl">თემატიკა <span style="color:#aaa;font-size:0.9em;">(სურვილისამებრ)</span></div>
                    <div class="mrow">
                        @foreach($themes as $theme)
                        @if($theme->is_active)
                        <label class="mchip {{ in_array($theme->id, $eThemeIds) ? 'sel' : '' }}"
                            onclick="chipSingle(this,'etheme{{ $child->id }}','{{ $theme->id }}')">{{ $theme->icon }} {{ $theme->name }}</label>
                        @else
                        <span class="mchip" style="opacity:0.4;cursor:default;pointer-events:none;">{{ $theme->icon }} {{ $theme->name }}<span style="font-size:0.75em;margin-left:4px;color:#aaa;">მალე</span></span>
                        @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="theme_ids[]" id="etheme{{ $child->id }}"
                        value="{{ count($eThemeIds) ? $eThemeIds[0] : ($defaultThemeId ?? '') }}">
                    @endif

                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="msave" style="margin-top:0;">შენახვა</button>
                        <button type="button" class="msave msave-danger" style="margin-top:0;flex:0 0 auto;width:auto;padding-left:18px;padding-right:18px;"
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

        @php
        $_adminMap = [
            'admin.panel'     => ['route' => 'admin.panel',             'icon' => '⚡', 'name' => 'Push'],
            'admin.grades'    => ['route' => 'admin.grades.index',      'icon' => '🎓', 'name' => 'კლასები'],
            'admin.themes'    => ['route' => 'admin.themes.index',      'icon' => '🎨', 'name' => 'თემატიკა'],
            'admin.topics'    => ['route' => 'admin.topics.index',      'icon' => '📚', 'name' => 'თემები'],
            'admin.questions' => ['route' => 'admin.questions.index',   'icon' => '❓',  'name' => 'კითხვები'],
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

        {{-- ბავშვის ხედი --}}
        @if(auth()->user()->role === 'child')
        @php
            $activeTest    = auth()->user()->tests()->whereNull('completed_at')->latest()->first();
            $lastCompleted = auth()->user()->tests()->whereNotNull('completed_at')->latest()->first();
            $setting       = auth()->user()->childSetting;
            $required      = $setting?->tests_per_week ?? 0;
            $todayCount    = auth()->user()->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
            $doneToday     = $required > 0 && $todayCount >= $required && !$activeTest;
            $coins         = $setting?->coins ?? 0;
            $achCount      = auth()->user()->achievements()->count();
        @endphp

        <div class="child-hero-banner">
            <div class="child-hero-text">
                <h2>👋, {{ auth()->user()->name }}! </h2>
                <p>მზად ხარ დღეს ჩემპიონო?</p>
            </div>
            <div class="child-hero-avatar">{{ auth()->user()->avatar === 'boy' ? '👦' : (auth()->user()->avatar === 'girl' ? '👧' : '🧒') }}</div>
        </div>

        {{-- მთავარი მოქმედება --}}
        @if($activeTest)
            <a href="{{ route('test.show', $activeTest) }}" class="child-cta resume">
                <div class="child-cta-main">
                    <span class="child-cta-icon">📝</span>
                    <div class="child-cta-text">
                        <div class="child-cta-title">გააგრძელე ტესტი</div>
                    </div>
                    <span class="child-cta-arrow">→</span>
                </div>
            </a>
        @elseif(!$setting || !$setting->grade_id)
            <div class="child-waiting-card">
                <div class="child-waiting-icon">⏳</div>
                <div class="child-waiting-txt">მშობელს ჯერ<br>დავალება არ დაუყენებია</div>
            </div>
        @elseif($doneToday)
            <div class="child-done-card">
                <div class="child-done-icon">🎉</div>
                <div class="child-done-title">დღე დასრულდა!</div>
                <div class="child-done-sub">{{ $todayCount }} ტესტი გააკეთე</div>
            </div>
        @else
            <a href="{{ route('test.start') }}" class="child-cta">
                <div class="child-cta-main">
                    <span class="child-cta-icon">▶</span>
                    <div class="child-cta-text">
                        <div class="child-cta-title">ტესტის დაწყება</div>
                    </div>
                    <span class="child-cta-arrow">→</span>
                </div>
            </a>
        @endif

        {{-- სტატუს ბარათები --}}
        <div class="child-stats-row">
            <div class="cstat">
                <div class="cstat-val">💰 {{ $coins }}</div>
                <div class="cstat-lbl">მონეტები</div>
            </div>
            <div class="cstat">
                <div class="cstat-val">{{ $todayCount }}<sub>/{{ $required }}</sub></div>
                <div class="cstat-lbl">დღეს</div>
            </div>
            <a href="{{ route('achievements') }}" class="cstat">
                <div class="cstat-val">🏆 {{ $achCount }}</div>
                <div class="cstat-lbl">მიღწევები</div>
            </a>
        </div>

        {{-- თამაშები --}}
        <a href="{{ route('games.index') }}" class="games-card">
            <span class="games-card-icon">🎮</span>
            <div class="games-card-text">
                <div class="games-card-title">თამაშები</div>
                <div class="games-card-sub">ითამაშე Kidsmart-თან და აჯობე!</div>
            </div>
            <span class="games-card-arrow">→</span>
        </a>

        {{-- ბოლოს მიღებული მედლები --}}
        @php
            $recentBadges = auth()->user()->achievements()
                ->latest('earned_at')
                ->take(4)
                ->get()
                ->map(fn($ca) => array_merge(
                    ['slug' => $ca->slug, 'earned_at' => $ca->earned_at],
                    \App\Services\AchievementService::ACHIEVEMENTS[$ca->slug] ?? []
                ));
        @endphp
        <div class="badges-section">
            <div class="badges-header">
                <h3>🏆 ჩემი მედლები</h3>
                <a href="{{ route('achievements') }}">ყველას ნახვა</a>
            </div>
            @if($recentBadges->count())
            <div class="badges-group">
                @foreach($recentBadges as $b)
                <div class="badge-card">
                    <div class="badge-icon">{{ $b['emoji'] ?? '🏅' }}</div>
                    <span>{{ $b['name'] ?? '' }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="badges-empty">ჯერ არცერთი მედალი არ გაქვს — გააგრძელე ვარჯიში! 💪</div>
            @endif
        </div>

        {{-- შემაჯამებელი — კვირის მიმოხილვა --}}
        @php
            $weekStart  = \Carbon\Carbon::now()->startOfWeek();
            $weekEnd    = \Carbon\Carbon::now()->endOfWeek();
            $weekTests  = auth()->user()->tests()->whereNotNull('completed_at')->whereBetween('completed_at', [$weekStart, $weekEnd])->get();
            $weekDone   = $weekTests->count();
            $weekTarget = $required > 0 ? $required * 7 : 0;
            $weekAccuracy = $weekDone > 0
                ? round($weekTests->avg(fn($t) => $t->total_questions > 0 ? $t->correct_count / $t->total_questions * 100 : 0))
                : null;

            $doneDates = auth()->user()->tests()->whereNotNull('completed_at')
                ->pluck('completed_at')->map(fn($d) => $d->format('Y-m-d'))->unique();

            $streak = 0;
            $cursor = \Carbon\Carbon::today();
            if (!$doneDates->contains($cursor->format('Y-m-d'))) { $cursor = $cursor->copy()->subDay(); }
            while ($doneDates->contains($cursor->format('Y-m-d'))) { $streak++; $cursor = $cursor->copy()->subDay(); }

            $dayLabels = ['ორშ', 'სამ', 'ოთხ', 'ხუთ', 'პარ', 'შაბ', 'კვ'];
            $weekDays = collect(range(0, 6))->map(function ($i) use ($weekStart, $doneDates, $dayLabels) {
                $d = $weekStart->copy()->addDays($i);
                return [
                    'label'   => $dayLabels[$i],
                    'done'    => $doneDates->contains($d->format('Y-m-d')),
                    'isToday' => $d->isToday(),
                ];
            });
        @endphp
        @if($weekTarget > 0 || $streak > 0)
        <div class="summary-card">
            <div class="summary-top">
                <div class="summary-streak">
                    <span class="summary-streak-flame">🔥</span>
                    <div>
                        <div class="summary-streak-num">{{ $streak }} დღე</div>
                        <div class="summary-streak-lbl">სერია</div>
                    </div>
                </div>
                @if($weekTarget > 0)
                <div class="summary-week-pill">{{ $weekDone }}/{{ $weekTarget }} ამ კვირაში</div>
                @endif
            </div>
            <div class="summary-days">
                @foreach($weekDays as $d)
                <div class="summary-day {{ $d['done'] ? 'done' : '' }} {{ $d['isToday'] ? 'today' : '' }}">
                    <div class="summary-day-dot">{{ $d['done'] ? '🔥' : '' }}</div>
                    <div class="summary-day-lbl">{{ $d['label'] }}</div>
                </div>
                @endforeach
            </div>
            @if($weekAccuracy !== null)
            <div class="summary-bottom">
                <div class="summary-stat">
                    <span class="summary-stat-val">{{ $weekAccuracy }}%</span>
                    <span class="summary-stat-lbl">სიზუსტე ამ კვირაში</span>
                </div>
                <div class="summary-stat">
                    <span class="summary-stat-val">{{ $weekDone }}</span>
                    <span class="summary-stat-lbl">ტესტი ამ კვირაში</span>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ბოლო ტესტი --}}
        @if($lastCompleted)
        @php $pct = round($lastCompleted->correct_count / $lastCompleted->total_questions * 100); @endphp
        <div class="child-last-card">
            <div class="clt-bar">
                <div class="clt-bar-fill" style="height:{{ $pct }}%"></div>
            </div>
            <div class="clt-info">
                <div class="clt-lbl">ბოლო ტესტი</div>
                <div class="clt-score">
                    {{ $lastCompleted->correct_count }}/{{ $lastCompleted->total_questions }}
                    <span class="clt-pct">{{ $pct }}%</span>
                </div>
            </div>
            <div class="clt-time">{{ $lastCompleted->completed_at->diffForHumans() }}</div>
        </div>
        @endif

        @endif


        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.panel') }}" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;">
            admin →
        </a>
        @endif


    </div>
</div>

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
        <form method="POST" action="{{ route('child.store') }}">
            @csrf
            <div class="modal-title">
                შვილის დამატება
                <button type="button" class="modal-close" onclick="document.getElementById('addChildModal').classList.remove('open')">✕</button>
            </div>

            {{-- Name --}}
            <div class="mlbl">სახელი <span>*</span></div>
            <input type="text" name="name" class="minput" placeholder="სახელი"
                value="{{ old('name') }}" required maxlength="50" autocomplete="off">
            @error('name')<div class="merr">{{ $message }}</div>@enderror

            {{-- Grade --}}
            <div class="mlbl">კლასი <span>*</span></div>
            <div class="mrow" id="gradeRow">
                @foreach($grades as $grade)
                <label class="mchip {{ old('grade_id') == $grade->id ? 'sel' : '' }}"
                    onclick="chipSingle(this,'grade_id_input','{{ $grade->id }}')">{{ $grade->name }}</label>
                @endforeach
            </div>
            <input type="hidden" name="grade_id" id="grade_id_input" value="{{ old('grade_id') }}">
            @error('grade_id')<div class="merr">{{ $message }}</div>@enderror

            {{-- Tests per day --}}
            <div class="mlbl">ტესტი დღეში</div>
            <div class="mrow" id="tpwRow">
                @for($i=1; $i<=5; $i++)
                <label class="mchip {{ old('tests_per_week', 3) == $i ? 'sel' : '' }}"
                    onclick="chipSingle(this,'tpw_input','{{ $i }}')">{{ $i }}</label>
                @endfor
            </div>
            <input type="hidden" name="tests_per_week" id="tpw_input" value="{{ old('tests_per_week', 3) }}">

            {{-- Theme (optional, default სტანდარტი) --}}
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

            <button type="submit" class="msave">+ შვილის შენახვა</button>
        </form>
        </div>{{-- /panelNew --}}
    </div>
</div>
@endif

{{-- Plans Modal --}}
@if(in_array(auth()->user()->role, ['parent', 'admin']))
<div id="plansModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')" style="z-index:300;">
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

<script>


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

function copyChildCode(el, code) {
    navigator.clipboard.writeText(code).then(() => {
        const orig = el.textContent;
        el.style.width = el.offsetWidth + 'px';
        el.style.textAlign = 'center';
        el.textContent = '✓';
        setTimeout(() => {
            el.textContent = orig;
            el.style.width = '';
            el.style.textAlign = '';
        }, 1500);
    });
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

@if($errors->hasAny(['name','grade_id','tests_per_week','theme_ids']))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addChildModal').classList.add('open');
});
@endif
</script>
@endsection
