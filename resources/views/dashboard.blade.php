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
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        position: relative;
        overflow-x: hidden;
        padding: 40px 24px 60px;
        gap: 20px;
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
    .dash-greeting {
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 4vw, 1.4rem);
        color: #111;
        letter-spacing: 0.06em;
    }



    .children-section { width: 100%; }
    .section-label {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; text-align: left;
    }
    .child-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 14px 16px; margin-bottom: 10px;
        display: flex; flex-direction: column; gap: 10px;
        text-decoration: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .child-card:hover { border-color: #bbb; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .child-row-top { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .child-name { font-family: 'Goldman', monospace; font-size: 0.88rem; color: #111; letter-spacing: 0.04em; }
    .child-row-bottom { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .ctag {
        font-family: 'Goldman', monospace; font-size: 0.6rem; color: #aaa;
        border: 1px solid #ebebeb; border-radius: 3px; padding: 2px 7px; letter-spacing: 0.04em; white-space: nowrap;
    }
    .ctag.set { color: #555; border-color: #ccc; }
    .child-arrow { color: #ccc; font-size: 0.9rem; }
    .add-child-btn {
        width: 100%; background: #fff; border: 1px dashed #d0d0d0; border-radius: 10px;
        font-family: 'Goldman', monospace; font-size: 0.75rem; color: #aaa;
        padding: 13px; cursor: pointer; letter-spacing: 0.06em;
        transition: all 0.2s; margin-bottom: 12px;
    }
    .add-child-btn:hover { border-color: #999; color: #555; background: #fafafa; }

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
    .child-code-badge {
        display: inline-block;
        font-family: 'Goldman', monospace; font-size: 0.75rem; color: #111;
        background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 4px;
        padding: 2px 10px; letter-spacing: 0.14em; cursor: pointer; transition: background 0.2s;
    }
    .child-code-badge:hover { background: #ebebeb; }
    .child-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-left: auto; }
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
    .child-hello {
        font-family: 'Fredoka One', cursive;
        font-size: clamp(1.9rem, 8vw, 2.6rem);
        color: #1a7a3c;
        line-height: 1.1;
        margin-top: 4px;
    }
    .child-stats-row {
        display: flex; gap: 10px; width: 100%;
    }
    .cstat {
        flex: 1; background: white; border-radius: 16px;
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
        display: flex; align-items: center; justify-content: center; gap: 12px;
        width: 100%; padding: 22px 20px;
        background: linear-gradient(135deg, #1a7a3c, #0f5c2a);
        color: white; font-family: 'Fredoka One', cursive;
        font-size: 1.25rem; letter-spacing: 0.03em;
        text-decoration: none; border-radius: 20px;
        box-shadow: 0 6px 24px rgba(26,122,60,0.35);
        transition: all 0.2s;
    }
    .child-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(26,122,60,0.45); color: white; }
    .child-cta.resume {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 6px 24px rgba(245,158,11,0.35);
    }
    .child-cta.resume:hover { box-shadow: 0 10px 32px rgba(245,158,11,0.45); }
    .child-done-card {
        background: white; border-radius: 18px; padding: 28px 20px;
        text-align: center; width: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 1.5px solid #f0f0f0;
    }
    .child-done-icon { font-size: 2.8rem; margin-bottom: 8px; }
    .child-done-title { font-family: 'Fredoka One', cursive; font-size: 1.4rem; color: #1a7a3c; margin-bottom: 4px; }
    .child-done-sub { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.8rem; color: #aaa; }
    .child-waiting-card {
        background: white; border-radius: 18px; padding: 28px 20px;
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
        <div class="dash-greeting">გამარჯობა, {{ auth()->user()->name }}</div>
        @endif

        {{-- მშობლის ხედი --}}
        @if(in_array(auth()->user()->role, ['parent', 'admin']))
        @php
            $children = auth()->user()->children()->with(['childSetting.grade','themes','topics'])->withTimestamps()->orderByPivot('created_at','asc')->get();
            $currentPkg = auth()->user()->currentPackage();
            $activeSub  = auth()->user()->activeSubscription();
        @endphp

        {{-- Plan badge --}}
        <button type="button" onclick="document.getElementById('plansModal').classList.add('open')"
            style="display:inline-flex;align-items:center;gap:7px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 14px;font-family:'Goldman',monospace;font-size:0.68rem;color:#374151;cursor:pointer;letter-spacing:0.04em;transition:all 0.2s;"
            onmouseover="this.style.borderColor='#94a3b8'" onmouseout="this.style.borderColor='#e2e8f0'">
            <span style="width:8px;height:8px;border-radius:50%;background:{{ $currentPkg->is_free ? '#10b981' : '#3b82f6' }};display:inline-block;"></span>
            {{ $currentPkg->name }}
            @if($activeSub?->expires_at)
                <span style="color:#94a3b8;font-size:0.6rem;">· {{ $activeSub->expires_at->format('d.m.Y') }}-მდე</span>
            @endif
            <span style="color:#94a3b8;font-size:0.65rem;">↑ გეგმა</span>
        </button>

        @if(session('child_added'))
        <div class="flash">{{ session('child_added') }}</div>
        @endif

        <div class="children-section">
            <div class="section-label">შვილები · {{ $children->count() }}</div>

            @php $atChildLimit = $currentPkg->max_children > 0 && $children->count() >= $currentPkg->max_children; @endphp
            <button type="button" class="add-child-btn"
                onclick="document.getElementById('{{ $atChildLimit ? 'plansModal' : 'addChildModal' }}').classList.add('open')"
                @if($atChildLimit) title="{{ $currentPkg->name }} პლანი მხოლოდ {{ $currentPkg->max_children }} ბავშვს იძლევა" @endif>
                @if($atChildLimit)
                    ↑ გეგმის განახლება
                @else
                    + შვილის დამატება
                @endif
            </button>
            @forelse($children as $child)
            @php
                $s = $child->childSetting;
                $todayDone = $child->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
                $pendingMarket = \App\Models\MarketPurchase::where('child_id', $child->id)->where('status','pending')->count();
            @endphp
            <div class="child-card" style="cursor:default;{{ !$child->is_active ? 'opacity:0.55;' : '' }}">
                @if(!$child->is_active)
                <div style="display:flex;align-items:center;gap:6px;background:#fef2f2;border:1px solid #fecaca;border-radius:5px;padding:5px 10px;margin-bottom:8px;font-size:0.68rem;color:#dc2626;">
                    <span>⊘</span>
                    <span>გათიშული — <strong>{{ $currentPkg->name }}</strong> გეგმა ამ ბავშვს არ მოიცავს</span>
                    <button type="button" onclick="document.getElementById('plansModal').classList.add('open')"
                        style="margin-left:auto;background:none;border:1px solid #fca5a5;color:#dc2626;font-family:'Goldman',monospace;font-size:0.6rem;padding:2px 8px;border-radius:3px;cursor:pointer;white-space:nowrap;">↑ გეგმა</button>
                </div>
                @endif
                {{-- ზედა ხაზი: სახელი · კლასი · დონე · დღეს --}}
                <div class="child-row-top">
                    <span class="child-name">{{ $child->name }}</span>
                    @if($s?->grade)
                        <span class="ctag set">{{ $s->grade->name }}</span>
                    @else
                        <span class="ctag">კლასი —</span>
                    @endif
                    @if($s)
                        <span class="ctag set">დონე {{ $s->difficulty }}</span>
                        <span class="ctag set">დღეს {{ $todayDone }}/{{ $s->tests_per_week }}</span>
                    @endif
                </div>
                {{-- ქვედა ხაზი: კოდი · ღილაკები --}}
                <div class="child-row-bottom">
                    @if($child->child_code)
                    <span class="child-code-badge" onclick="copyChildCode(this, '{{ $child->child_code }}')"
                        title="კოდის კოპირება">{{ $child->child_code }}</span>
                    @endif
                    <div class="child-actions">
                        <button type="button"
                            onclick="openRemind({{ $child->id }}, '{{ addslashes($child->name) }}')"
                            style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:4px;color:#059669;font-family:'Goldman',monospace;font-size:0.72rem;padding:5px 10px;cursor:pointer;">
                            &#128276;
                        </button>
                        <a href="{{ route('market.index', $child) }}" class="caction" style="{{ $pendingMarket ? 'color:#d97706;border-color:#fde68a;background:#fffbeb;' : '' }}">
                            🛒@if($pendingMarket) <span style="font-size:0.7rem;font-weight:900;">{{ $pendingMarket }}</span>@endif
                        </a>
                        <a href="{{ route('child.stats', $child) }}" class="caction primary">სტატისტიკა</a>
                        <button type="button" class="caction" onclick="document.getElementById('editChildModal{{ $child->id }}').classList.add('open')">⚙</button>
                    </div>
                </div>
                @if(session('reminder_sent_' . $child->id))
                <div style="font-family:'Goldman',monospace;font-size:0.62rem;color:#059669;margin-top:4px;">&#10003; შეხსენება გაიგზავნა</div>
                @endif
            </div>
            @empty
            <div class="no-children">
                ბავშვი ჯერ არ დარეგისტრირებულა<br>
                <span style="font-size:0.62rem;color:#ccc;margin-top:4px;display:block;">კოდი გაუზიარე შვილს</span>
            </div>
            @endforelse
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
        <div class="children-section">
            <a href="{{ route('test.preview') }}" class="child-card" style="color:inherit;">
                <div class="child-row-top">
                    <span style="font-size:1.1rem;line-height:1;">🧪</span>
                    <span class="child-name">ტესტის გადახედვა</span>
                    <span style="margin-left:auto;color:#ccc;font-size:0.8rem;">→</span>
                </div>
            </a>
            <div class="section-label" style="margin-top:8px;">ადმინის გვერდები · {{ count($_adminMap) }}</div>
            @foreach($_adminMap as $info)
            <a href="{{ route($info['route']) }}" class="child-card" style="color:inherit;">
                <div class="child-row-top">
                    <span style="font-size:1.1rem;line-height:1;">{{ $info['icon'] }}</span>
                    <span class="child-name">{{ $info['name'] }}</span>
                    <span style="margin-left:auto;color:#ccc;font-size:0.8rem;">→</span>
                </div>
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
        <div class="children-section">
            <a href="{{ route('test.preview') }}" class="child-card" style="color:inherit;">
                <div class="child-row-top">
                    <span style="font-size:1.1rem;line-height:1;">🧪</span>
                    <span class="child-name">ტესტის გადახედვა</span>
                    <span style="margin-left:auto;color:#ccc;font-size:0.8rem;">→</span>
                </div>
            </a>
            @if(!empty($_myAdmPages))
            <div class="section-label" style="margin-top:8px;">ადმინის გვერდები · {{ count($_myAdmPages) }}</div>
            @foreach($_adminMap as $key => $info)
            @if(in_array($key, $_myAdmPages))
            <a href="{{ route($info['route']) }}" class="child-card" style="color:inherit;">
                <div class="child-row-top">
                    <span style="font-size:1.1rem;line-height:1;">{{ $info['icon'] }}</span>
                    <span class="child-name">{{ $info['name'] }}</span>
                    <span style="margin-left:auto;color:#ccc;font-size:0.8rem;">→</span>
                </div>
            </a>
            @endif
            @endforeach
            @endif
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

        <div class="child-hello">{{ auth()->user()->name }} 👋</div>

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

        {{-- მთავარი მოქმედება --}}
        @if($activeTest)
            <a href="{{ route('test.show', $activeTest) }}" class="child-cta resume">
                📝 გააგრძელე ტესტი →
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
                ▶ ტესტის დაწყება
            </a>
        @endif

        {{-- მარკეტი --}}
        @php $marketCount = \App\Models\MarketItem::where('child_id', auth()->id())->where('is_active', true)->count(); @endphp
        @if($marketCount)
        <a href="{{ route('market.child') }}" style="display:flex;align-items:center;justify-content:space-between;width:100%;background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:16px;padding:14px 18px;text-decoration:none;transition:all 0.2s;border:1.5px solid #fde68a;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1.5rem;">🛒</span>
                <div>
                    <div style="font-family:'Fredoka One',cursive;font-size:0.95rem;color:#92400e;">მარკეტი</div>
                    <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:0.62rem;color:#b45309;">{{ $marketCount }} პროდუქტი · 💰 {{ $setting?->coins ?? 0 }} მონეტა</div>
                </div>
            </div>
            <span style="color:#d97706;font-size:1rem;">→</span>
        </a>
        @endif

        {{-- ვიდეოთეკა --}}
        @php $hasVids = \App\Models\Topic::where('grade_id', $setting?->grade_id)->whereHas('videos')->exists(); @endphp
        @if($hasVids)
        <a href="{{ route('videos.library') }}" style="display:flex;align-items:center;justify-content:space-between;width:100%;background:linear-gradient(135deg,#ede9fe,#ddd6fe);border-radius:16px;padding:14px 18px;text-decoration:none;border:1.5px solid #ddd6fe;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1.5rem;">📹</span>
                <div>
                    <div style="font-family:'Fredoka One',cursive;font-size:0.95rem;color:#5b21b6;">ვიდეოთეკა</div>
                    <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:0.62rem;color:#7c3aed;">ახსნა-განმარტებითი ვიდეოები</div>
                </div>
            </div>
            <span style="color:#7c3aed;font-size:1rem;">→</span>
        </a>
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
