@extends('layouts.app')
@section('content')
<style>
    .pm-head { display: flex; flex-direction: column; gap: 4px; }
    .pm-head span { font-size: 13px; color: #6B625A; }
    .pm-head h1 { margin: 0; font-size: 44px; line-height: .95; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .pm-sec { display: flex; flex-direction: column; gap: 8px; }
    .pm-sec-h { display: flex; justify-content: space-between; align-items: center; }
    .pm-sec-h span { font-size: 15px; font-weight: 800; }
    .pm-sec-h small { font-size: 12px; color: #6B625A; }
    .pm-req { background: #fff; border-radius: 20px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 0 #ECE9E4; }
    .pm-req form { margin: 0; }
    .pm-av { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .pm-req-t { flex-grow: 1; display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .pm-req-t b { font-size: 14px; font-weight: 800; }
    .pm-req-t small { font-size: 12px; color: #6B625A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pm-round { width: 44px; height: 44px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; }
    .pm-round.no { background: #F1EFEB; color: #4F473F; }
    .pm-round.no:hover { background: #E6E2DC; }
    .pm-round.ok { background: #1E8A4C; color: #fff; }
    .pm-round.ok:hover { background: #166B3A; }
    .pm-badge { font-size: 12px; font-weight: 700; border-radius: 999px; padding: 6px 10px; background: #DFF5E3; color: #14612F; flex-shrink: 0; }
    .pm-empty { font-size: 13px; color: #6B625A; padding: 4px 2px; }
    .pm-list { background: #fff; border-radius: 22px; padding: 4px 14px; box-shadow: 0 2px 0 #ECE9E4; display: flex; flex-direction: column; }
    .pm-item { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #F1EFEB; }
    .pm-item:last-child { border-bottom: none; }
    .pm-item form { margin: 0; }
    .pm-item.off .pm-item-ic, .pm-item.off .pm-item-t { opacity: .5; }
    .pm-item-ic { font-size: 24px; width: 32px; text-align: center; flex-shrink: 0; }
    .pm-item-t { flex-grow: 1; display: flex; flex-direction: column; min-width: 0; }
    .pm-item-t b { font-size: 14px; font-weight: 700; }
    .pm-item-t small { font-size: 12px; color: #6B625A; }
    .pm-del { width: 36px; height: 36px; border-radius: 12px; border: none; background: transparent; color: #B5AAA0; cursor: pointer; font-size: 15px; }
    .pm-del:hover { color: #C62828; background: #FDECEA; }
    .pm-switch { width: 52px; height: 32px; border-radius: 16px; border: none; padding: 3px; cursor: pointer; display: flex; background: #D9D4CC; justify-content: flex-start; }
    .pm-switch.on { background: #1E8A4C; justify-content: flex-end; }
    .pm-switch span { width: 26px; height: 26px; border-radius: 50%; background: #fff; display: block; }
    .pm-add-wrap { position: sticky; bottom: 16px; z-index: 40; padding-top: 6px; }
    .pm-add { width: 100%; height: 56px; border-radius: 28px; border: none; background: #E2590F; color: #fff; font-family: inherit; font-size: 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 8px 20px rgba(226,89,15,.3); }
    .pm-add:hover { background: #E2590F; }
    @media (max-width: 900px) { .pm-add-wrap { bottom: calc(80px + env(safe-area-inset-bottom)); } }
    body { background: transparent !important; }
    .aw { max-width: 1000px; margin: 0; padding: 0 0 60px; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; display: flex; flex-direction: column; gap: 18px; }
    .card  { background:#fff; border:none; border-radius:24px; padding:22px; margin-bottom:0; box-shadow:0 2px 0 #ECE9E4; }
    .sec   { font-size:1.15rem; font-weight:800; color:#2B2622; margin-bottom:14px; }
    .fc    { background:#fff; border:1.5px solid #E6E2DC; border-radius:14px; color:#2B2622; font-family:inherit; font-size:0.95rem; padding:10px 14px; outline:none; box-sizing:border-box; min-height:46px; }
    .fc:focus { border-color:#E2590F; box-shadow:0 0 0 3px rgba(226,89,15,.14); }
    .aw .btn, .m-box .btn { background:#F1EFEB; border:none; color:#2B2622; font-family:inherit; font-size:0.9rem; font-weight:800; padding:10px 18px; border-radius:999px; cursor:pointer; }
    .aw .btn:hover { background:#E6E2DC; color:#2B2622; }
    .btn-sm { padding:7px 14px !important; font-size:0.82rem !important; }
    .btn-ok { background:#1E8A4C !important; color:#fff !important; }
    .btn-ok:hover { background:#166B3A !important; }
    .btn-del { background:none; border:none; color:#B5AAA0; font-size:0.85rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-del:hover { color:#C62828; }
    .ks-mk-add { width:100%; height:56px; border:2px dashed #DDD8D1; background:#fff; border-radius:24px; font-family:inherit; font-size:1rem; font-weight:800; color:#2B2622; cursor:pointer; }
    .ks-mk-add:hover { border-color:#C24A0A; color:#C24A0A; }

    .toast-wrap {
        position: fixed; top: 0; left: 50%; transform: translate(-50%, -130%);
        z-index: 2000; display: flex; flex-direction: column; gap: 8px; align-items: center;
        width: 100%; max-width: 420px; padding: 14px 16px 0; box-sizing: border-box;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }
    .toast-wrap.show { transform: translate(-50%, 0); }
    .toast-wrap .toast {
        display: block; font-family: 'Noto Sans Georgian', sans-serif; font-size: 0.9rem; font-weight: 800;
        background: #C24A0A; color: #fff; border-radius: 999px; padding: 11px 20px; text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18); max-width: 100%; opacity: 1; border: none; width: auto;
    }

    .item-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:12px; }
    .item-card { background:#F7F6F3; border:none; border-radius:20px; padding:16px; display:flex; flex-direction:column; gap:6px; position:relative; }
    .item-card-del { position:absolute; top:10px; right:10px; }
    .item-card-icon { font-size:2.2rem; line-height:1; }
    .item-card-title { font-size:0.95rem; font-weight:800; padding-right:18px; }
    .item-cat   { font-size:0.75rem; color:#6B625A; margin-top:-2px; }
    .item-card-footer { display:flex; align-items:center; justify-content:space-between; gap:6px; margin-top:6px; }
    .cost-badge { font-size:0.82rem; font-weight:800; background:#FFF1C9; color:#8A5A00; border-radius:999px; padding:3px 10px; white-space:nowrap; }
    .toggle-pill { font-size:0.75rem; font-weight:800; border-radius:999px; padding:4px 10px; border:none; cursor:pointer; font-family:inherit; }
    .toggle-pill.on  { background:#DFF5E3; color:#1E7A45; }
    .toggle-pill.off { background:#ECE9E4; color:#6B625A; }

    /* template grid */
    .tpl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:8px; margin-top:10px; }
    .tpl-card { border:1.5px dashed #E6E2DC; border-radius:14px; padding:9px 11px; cursor:pointer; transition:all 0.15s; display:flex; align-items:center; gap:8px; background:none; width:100%; text-align:left; font-family:inherit; }
    .tpl-card:hover { border-color:#C24A0A; background:#FFF3EA; }
    .tpl-card.added { border-color:#9FD9B5; background:#E8F7EE; opacity:0.7; pointer-events:none; cursor:default; }
    .tpl-icon { font-size:1.3rem; flex-shrink:0; }
    .tpl-info { flex:1; min-width:0; }
    .tpl-name { font-size:0.82rem; font-weight:700; color:#2B2622; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
    .tpl-cost { font-size:0.72rem; color:#1E7A45; font-weight:800; display:block; }
    .cat-lbl { font-size:0.72rem; font-weight:800; color:#6B625A; letter-spacing:0.06em; text-transform:uppercase; margin:14px 0 6px; }

    /* pending cards */
    .pend-card { background:#FFF3EA; border:none; border-radius:18px; padding:14px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .pend-icon { font-size:1.9rem; flex-shrink:0; }
    .pend-info { flex:1; min-width:120px; }
    .pend-title { font-size:1rem; font-weight:800; }
    .pend-meta  { font-size:0.8rem; color:#8A3407; margin-top:2px; }
    .pend-actions { display:flex; gap:6px; flex-shrink:0; }
    .pend-actions form { margin:0; }

    .appr-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #F1EFEB; font-size:0.92rem; font-weight:600; }
    .appr-row:last-child { border-bottom:none; }
    .appr-date { font-size:0.78rem; color:#6B625A; margin-left:auto; white-space:nowrap; }

    /* ── Price modal ── */
    .m-overlay { position:fixed;inset:0;background:rgba(29,26,23,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px; }
    .m-overlay.open { display:flex; }
    .m-box { background:#fff;border-radius:26px;padding:24px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:mIn 0.2s cubic-bezier(0.175,0.885,0.32,1.275);font-family:'Noto Sans Georgian',sans-serif;color:#2B2622; }
    @keyframes mIn { from{transform:scale(0.92);opacity:0} to{transform:scale(1);opacity:1} }
    .m-title { font-size:1.1rem;font-weight:800;margin-bottom:18px;display:flex;align-items:center;gap:10px; }
    .m-lbl { font-size:0.75rem;font-weight:800;color:#6B625A;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:6px; }
    .m-actions { display:flex;gap:8px;margin-top:18px; }
    .m-save { flex:1;background:#C24A0A;border:none;border-radius:24px;color:#fff;font-family:inherit;font-size:0.95rem;font-weight:800;height:48px;padding:0 16px;cursor:pointer; }
    .m-save:hover { background:#A33E08; }
    .m-cancel { background:#F1EFEB;border:none;border-radius:24px;color:#2B2622;font-family:inherit;font-size:0.9rem;font-weight:800;padding:11px 16px;cursor:pointer; }
    .m-cancel:hover { background:#E6E2DC; }

    /* ── Emoji picker ── */
    .emoji-btn { width:54px;height:46px;background:#F7F6F3;border:1.5px solid #E6E2DC;border-radius:14px;font-size:1.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center; }
    .emoji-btn:hover { border-color:#C24A0A; }
    .emoji-overlay { position:fixed;inset:0;background:rgba(29,26,23,0.45);backdrop-filter:blur(2px);z-index:1100;display:none;align-items:center;justify-content:center;padding:16px; }
    .emoji-overlay.open { display:flex; }
    .emoji-sheet { background:#fff;border-radius:22px;padding:16px;width:100%;max-width:340px;max-height:70vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.25); }
    .emoji-sheet-title { font-family:'Noto Sans Georgian',sans-serif;font-size:0.8rem;font-weight:800;color:#6B625A;margin-bottom:10px; }
    .emoji-grid { display:grid;grid-template-columns:repeat(8,1fr);gap:2px; }
    .ep { font-size:1.3rem;padding:5px;border-radius:8px;cursor:pointer;text-align:center;border:none;background:none;line-height:1.4; }
    .ep:hover { background:#FFF3EA; }

    /* ── Add-item modal tab switcher ── */
    .tab-switch { display:flex; gap:6px; margin-bottom:16px; background:#F1EFEB; border-radius:999px; padding:4px; border:none; }
    .tab-btn { flex:1; background:none; border:none; border-radius:999px; font-family:inherit; font-size:0.88rem; font-weight:800; color:#6B625A; padding:9px; cursor:pointer; margin:0; }
    .tab-btn.active { background:#fff; color:#2B2622; box-shadow:0 1px 0 #E6E2DC; }

    @media(max-width:640px) { .card{padding:16px;border-radius:22px;} .tpl-grid{grid-template-columns:repeat(auto-fill,minmax(130px,1fr));} .emoji-grid{grid-template-columns:repeat(7,1fr);} .item-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
</style>

<div class="aw">
    <div class="ks-cp-hero dark">
        <span class="ks-cp-hero-ic" aria-hidden="true">🛍️</span>
        <span class="ks-cp-eyebrow" style="color:#FFC49A;">თავად დააწესე ჯილდოები · {{ $child->name }}-ს აქვს {{ (int) floor($child->childSetting?->coins ?? 0) }} მონეტა</span>
        <h1 class="ks-cp-title">ოჯახის მარკეტი</h1>
    </div>
    @include('parent.partials.kid-switcher')

    @if(session('success'))
    @push('toasts')
    <div class="toast-wrap" id="marketToastWrap">
        <div class="toast">✓ {{ session('success') }}</div>
    </div>
    <script>
    (function(){
        var wrap = document.getElementById('marketToastWrap');
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

    {{-- მოთხოვნები --}}
    <section class="pm-sec" data-guide="market-requests">
        <div class="pm-sec-h"><span>მოთხოვნები</span>@if($pending->count())<small>{{ $pending->count() }} ელოდება</small>@endif</div>
        @forelse($pending as $p)
        <div class="pm-req">
            <span class="pm-av" style="background: {{ $child->avatar === 'girl' ? '#FFF3EA' : ($child->avatar === 'boy' ? '#FFF3EA' : '#FFD9BF') }}">{{ $p->item->icon }}</span>
            <span class="pm-req-t">
                <b>{{ $p->item->title }}</b>
                <small>{{ $child->name }} · {{ $p->coins_spent }} მონეტა · {{ $p->created_at->diffForHumans() }}</small>
            </span>
            <form method="POST" action="{{ route('market.cancel', $p) }}">
                @csrf @method('PATCH')
                <button type="submit" class="pm-round no" aria-label="უარყოფა — მონეტები დაბრუნდება" title="უარყოფა (მონეტები დაბრუნდება)" onclick="return confirm('გაუქმდეს? მონეტები დაბრუნდება.')"><i class="bi bi-x-lg"></i></button>
            </form>
            <form method="POST" action="{{ route('market.approve', $p) }}">
                @csrf @method('PATCH')
                <button type="submit" class="pm-round ok" aria-label="დადასტურება" title="დადასტურება"><i class="bi bi-check-lg"></i></button>
            </form>
        </div>
        @empty
        <div class="pm-empty">ახალი მოთხოვნა არ არის</div>
        @endforelse
        @foreach($approved->take(3) as $ap)
        <div class="pm-req">
            <span class="pm-av" style="background: {{ $child->avatar === 'girl' ? '#FFF3EA' : ($child->avatar === 'boy' ? '#FFF3EA' : '#FFD9BF') }}">{{ $ap->item->icon }}</span>
            <span class="pm-req-t">
                <b>{{ $ap->item->title }}</b>
                <small>{{ $child->name }} · {{ $ap->coins_spent }} მონეტა · {{ $ap->updated_at->format('d.m.Y') }}</small>
            </span>
            <span class="pm-badge">დადასტურდა</span>
        </div>
        @endforeach
    </section>

    {{-- ჯილდოები --}}
    <section class="pm-sec">
        <div class="pm-sec-h"><span>ჯილდოები</span><small>{{ $items->where('is_active', true)->count() }} აქტიური</small></div>
        <div class="pm-list">
            @forelse($items as $item)
            <div class="pm-item {{ $item->is_active ? '' : 'off' }}">
                <span class="pm-item-ic">{{ $item->icon }}</span>
                <span class="pm-item-t">
                    <b>{{ $item->title }}</b>
                    <small>{{ $item->coin_cost }} მონეტა{{ $item->category ? ' · ' . $item->category : '' }}</small>
                </span>
                <form method="POST" action="{{ route('market.item.destroy', $item) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="pm-del" aria-label="{{ $item->title }} — წაშლა" title="წაშლა" onclick="return confirm('წაიშალოს?')"><i class="bi bi-trash3"></i></button>
                </form>
                <form method="POST" action="{{ route('market.item.toggle', $item) }}">
                    @csrf @method('PATCH')
                    <button type="submit" role="switch" aria-checked="{{ $item->is_active ? 'true' : 'false' }}" aria-label="{{ $item->title }} — ჩართვა" class="pm-switch {{ $item->is_active ? 'on' : '' }}"><span></span></button>
                </form>
            </div>
            @empty
            <div class="pm-empty" style="padding:18px 0;">ჯილდოები ჯერ არ არის — დაამატე ხელით ან შაბლონიდან</div>
            @endforelse
        </div>
    </section>

    <div class="pm-add-wrap">
        <button type="button" class="pm-add" data-guide="market-add" onclick="openAddItemModal()"><i class="bi bi-plus-lg"></i> ახალი ჯილდოს დამატება</button>
    </div>
</div>

@push('modals')
{{-- Add item modal (manual entry / templates) --}}
<div class="m-overlay" id="addItemModal" onclick="if(event.target===this)closeAddItemModal()">
    <div class="m-box" style="max-width:480px;">
        <div class="m-title">
            <span style="flex:1;">+ პროდუქტის დამატება</span>
            <button type="button" class="m-cancel" style="padding:4px 10px;" onclick="closeAddItemModal()">✕</button>
        </div>

        <div class="tab-switch" data-guide="reward-tabs">
            <button type="button" class="tab-btn active" id="aiTabManual" onclick="switchItemTab('manual')">✎ ხელით</button>
            <button type="button" class="tab-btn" id="aiTabTpl" data-guide="reward-tpl" onclick="switchItemTab('templates')">📋 შაბლონები</button>
        </div>

        <div id="aiPanelManual">
            <form method="POST" action="{{ route('market.store', $child) }}" id="customForm">
                @csrf
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <button type="button" class="emoji-btn" id="emojiBtn" data-guide="reward-icon" onclick="openEmojiModal()" title="სმაილის არჩევა">
                        <span id="emojiDisplay">🎁</span>
                    </button>
                    <input type="hidden" name="icon" id="iconInput" value="🎁">
                    <input type="text" name="title" class="fc" data-guide="reward-title" placeholder="პროდუქტის სახელი" required style="flex:1;min-width:140px;">
                    <input type="number" name="coin_cost" class="fc" data-guide="reward-cost" placeholder="💰 მონეტა" min="1" max="9999" required style="width:110px;">
                </div>
                <button type="submit" class="m-save" data-guide="reward-save" style="width:100%;margin-top:14px;">+ მარკეტში დამატება</button>
            </form>
        </div>

        <div id="aiPanelTpl" style="display:none;max-height:50vh;overflow-y:auto;">
            @php $existingTitles = $items->pluck('title')->flip()->toArray(); @endphp
            @foreach($templates as $catName => $tplItems)
            <div class="cat-lbl">{{ $catName }}</div>
            <div class="tpl-grid">
                @foreach($tplItems as $tpl)
                @php $alreadyAdded = isset($existingTitles[$tpl['title']]); @endphp
                @if($alreadyAdded)
                <div class="tpl-card added">
                    <span class="tpl-icon">{{ $tpl['icon'] }}</span>
                    <span class="tpl-info">
                        <span class="tpl-name">{{ $tpl['title'] }}</span>
                        <span class="tpl-cost">✓ დამატებულია</span>
                    </span>
                </div>
                @else
                <button type="button" class="tpl-card"
                    onclick="openPriceModal('{{ addslashes($tpl['title']) }}','{{ $tpl['icon'] }}','{{ $catName }}')">
                    <span class="tpl-icon">{{ $tpl['icon'] }}</span>
                    <span class="tpl-info">
                        <span class="tpl-name">{{ $tpl['title'] }}</span>
                    </span>
                </button>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Emoji overlay --}}
<div class="emoji-overlay" id="emojiOverlay" onclick="if(event.target===this)closeEmojiModal()">
    <div class="emoji-sheet">
        <div class="emoji-sheet-title">სმაილის არჩევა</div>
        <div class="emoji-grid" id="emojiGrid"></div>
    </div>
</div>

{{-- Price modal --}}
<div class="m-overlay" id="priceModal" onclick="if(event.target===this)closePriceModal()">
    <div class="m-box">
        <div class="m-title">
            <span id="pm-icon" style="font-size:1.6rem;"></span>
            <span id="pm-title" style="flex:1;"></span>
        </div>
        <form method="POST" action="{{ route('market.store', $child) }}" id="priceForm">
            @csrf
            <input type="hidden" name="icon"     id="pm-icon-inp">
            <input type="hidden" name="title"    id="pm-title-inp">
            <input type="hidden" name="category" id="pm-cat-inp">
            <div class="m-lbl">მონეტების რაოდენობა</div>
            <input type="number" name="coin_cost" id="pm-cost" class="fc" min="1" max="9999" required
                style="width:100%;font-size:1rem;text-align:center;" placeholder="მაგ: 50">
            <div class="m-actions">
                <button type="button" class="m-cancel" onclick="closePriceModal()">გაუქმება</button>
                <button type="submit" class="m-save">+ მარკეტში დამატება</button>
            </div>
        </form>
    </div>
</div>
@endpush

<script>
// ── Add item modal ───────────────────────────────────
function openAddItemModal() {
    document.getElementById('addItemModal').classList.add('open');
    switchItemTab('manual');
}
function closeAddItemModal() {
    document.getElementById('addItemModal').classList.remove('open');
}
function switchItemTab(tab) {
    const isManual = tab === 'manual';
    document.getElementById('aiPanelManual').style.display = isManual ? 'block' : 'none';
    document.getElementById('aiPanelTpl').style.display    = isManual ? 'none' : 'block';
    document.getElementById('aiTabManual').classList.toggle('active', isManual);
    document.getElementById('aiTabTpl').classList.toggle('active', !isManual);
}

// ── Price modal ──────────────────────────────────────
function openPriceModal(title, icon, category) {
    closeAddItemModal();
    document.getElementById('pm-icon').textContent      = icon;
    document.getElementById('pm-title').textContent     = title;
    document.getElementById('pm-icon-inp').value        = icon;
    document.getElementById('pm-title-inp').value       = title;
    document.getElementById('pm-cat-inp').value         = category;
    document.getElementById('pm-cost').value            = '';
    document.getElementById('priceModal').classList.add('open');
    setTimeout(() => document.getElementById('pm-cost').focus(), 120);
}
function closePriceModal() {
    document.getElementById('priceModal').classList.remove('open');
}

// ── Emoji picker ─────────────────────────────────────
const EMOJIS = [
    '🎡','🦁','🍔','🎬','🎳','🏊','🎠','⛸️','🏖️','🌊',
    '🍕','🍦','📚','🎁','🧁','🍫','🍬','🎂','🍭','🍰',
    '👕','👟','🧢','🎒','🧦','🧣','🕶️','👗','👒','🎽',
    '🎮','🧩','🎨','⚽','🎯','🛴','🚲','🎸','🎻','🏀',
    '🎲','🎥','🍳','🚴','🌳','🛁','🎪','🎭','🎉','🎊',
    '⭐','🌟','💫','✨','🏆','🥇','🎖️','🏅','🎀','💝',
    '🐶','🐱','🐰','🦊','🐸','🦋','🐠','🦄','🐧','🦈',
    '🌈','☀️','🌸','🍀','🌺','🌻','🌙','❄️','🔥','💎',
];

function buildEmojiGrid() {
    const grid = document.getElementById('emojiGrid');
    EMOJIS.forEach(e => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ep';
        btn.textContent = e;
        btn.onclick = () => pickEmoji(e);
        grid.appendChild(btn);
    });
}

function pickEmoji(e) {
    document.getElementById('emojiDisplay').textContent = e;
    document.getElementById('iconInput').value = e;
    closeEmojiModal();
}

function openEmojiModal()  { document.getElementById('emojiOverlay').classList.add('open'); }
function closeEmojiModal() { document.getElementById('emojiOverlay').classList.remove('open'); }

document.addEventListener('DOMContentLoaded', buildEmojiGrid);
</script>
@endsection
