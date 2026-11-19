@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 1000px; margin: 0; padding: 0 0 60px; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #1D1A17; display: flex; flex-direction: column; gap: 18px; }
    .card  { background:#fff; border:none; border-radius:24px; padding:22px; margin-bottom:0; box-shadow:0 2px 0 #F1E3D3; }
    .sec   { font-size:1.15rem; font-weight:800; color:#1D1A17; margin-bottom:14px; }
    .fc    { background:#fff; border:1.5px solid #E8DFD5; border-radius:14px; color:#1D1A17; font-family:inherit; font-size:0.95rem; padding:10px 14px; outline:none; box-sizing:border-box; min-height:46px; }
    .fc:focus { border-color:#E2590F; box-shadow:0 0 0 3px rgba(226,89,15,.14); }
    .aw .btn, .m-box .btn { background:#F5EFE8; border:none; color:#1D1A17; font-family:inherit; font-size:0.9rem; font-weight:800; padding:10px 18px; border-radius:999px; cursor:pointer; }
    .aw .btn:hover { background:#EADFD3; color:#1D1A17; }
    .btn-sm { padding:7px 14px !important; font-size:0.82rem !important; }
    .btn-ok { background:#1E8A4C !important; color:#fff !important; }
    .btn-ok:hover { background:#166B3A !important; }
    .btn-del { background:none; border:none; color:#B5AAA0; font-size:0.85rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-del:hover { color:#C62828; }
    .ks-mk-add { width:100%; height:56px; border:2px dashed #DCCFC2; background:#fff; border-radius:24px; font-family:inherit; font-size:1rem; font-weight:800; color:#1D1A17; cursor:pointer; }
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
        background: #1D1A17; color: #fff; border-radius: 999px; padding: 11px 20px; text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18); max-width: 100%; opacity: 1; border: none; width: auto;
    }

    .item-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:12px; }
    .item-card { background:#FFF8F1; border:none; border-radius:20px; padding:16px; display:flex; flex-direction:column; gap:6px; position:relative; }
    .item-card-del { position:absolute; top:10px; right:10px; }
    .item-card-icon { font-size:2.2rem; line-height:1; }
    .item-card-title { font-size:0.95rem; font-weight:800; padding-right:18px; }
    .item-cat   { font-size:0.75rem; color:#6B625A; margin-top:-2px; }
    .item-card-footer { display:flex; align-items:center; justify-content:space-between; gap:6px; margin-top:6px; }
    .cost-badge { font-size:0.82rem; font-weight:800; background:#FFF1C9; color:#8A5A00; border-radius:999px; padding:3px 10px; white-space:nowrap; }
    .toggle-pill { font-size:0.75rem; font-weight:800; border-radius:999px; padding:4px 10px; border:none; cursor:pointer; font-family:inherit; }
    .toggle-pill.on  { background:#DFF5E3; color:#1E7A45; }
    .toggle-pill.off { background:#EFE6DC; color:#6B625A; }

    /* template grid */
    .tpl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:8px; margin-top:10px; }
    .tpl-card { border:1.5px dashed #E8DFD5; border-radius:14px; padding:9px 11px; cursor:pointer; transition:all 0.15s; display:flex; align-items:center; gap:8px; background:none; width:100%; text-align:left; font-family:inherit; }
    .tpl-card:hover { border-color:#C24A0A; background:#FFF4EA; }
    .tpl-card.added { border-color:#9FD9B5; background:#E8F7EE; opacity:0.7; pointer-events:none; cursor:default; }
    .tpl-icon { font-size:1.3rem; flex-shrink:0; }
    .tpl-info { flex:1; min-width:0; }
    .tpl-name { font-size:0.82rem; font-weight:700; color:#1D1A17; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
    .tpl-cost { font-size:0.72rem; color:#1E7A45; font-weight:800; display:block; }
    .cat-lbl { font-size:0.72rem; font-weight:800; color:#6B625A; letter-spacing:0.06em; text-transform:uppercase; margin:14px 0 6px; }

    /* pending cards */
    .pend-card { background:#FFF4EA; border:none; border-radius:18px; padding:14px 16px; margin-bottom:10px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .pend-icon { font-size:1.9rem; flex-shrink:0; }
    .pend-info { flex:1; min-width:120px; }
    .pend-title { font-size:1rem; font-weight:800; }
    .pend-meta  { font-size:0.8rem; color:#8A3407; margin-top:2px; }
    .pend-actions { display:flex; gap:6px; flex-shrink:0; }
    .pend-actions form { margin:0; }

    .appr-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #F5EFE8; font-size:0.92rem; font-weight:600; }
    .appr-row:last-child { border-bottom:none; }
    .appr-date { font-size:0.78rem; color:#6B625A; margin-left:auto; white-space:nowrap; }

    /* ── Price modal ── */
    .m-overlay { position:fixed;inset:0;background:rgba(29,26,23,0.45);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px; }
    .m-overlay.open { display:flex; }
    .m-box { background:#fff;border-radius:26px;padding:24px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:mIn 0.2s cubic-bezier(0.175,0.885,0.32,1.275);font-family:'Noto Sans Georgian',sans-serif;color:#1D1A17; }
    @keyframes mIn { from{transform:scale(0.92);opacity:0} to{transform:scale(1);opacity:1} }
    .m-title { font-size:1.1rem;font-weight:800;margin-bottom:18px;display:flex;align-items:center;gap:10px; }
    .m-lbl { font-size:0.75rem;font-weight:800;color:#6B625A;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:6px; }
    .m-actions { display:flex;gap:8px;margin-top:18px; }
    .m-save { flex:1;background:#1D1A17;border:none;border-radius:24px;color:#fff;font-family:inherit;font-size:0.95rem;font-weight:800;height:48px;padding:0 16px;cursor:pointer; }
    .m-save:hover { background:#000; }
    .m-cancel { background:#F5EFE8;border:none;border-radius:24px;color:#1D1A17;font-family:inherit;font-size:0.9rem;font-weight:800;padding:11px 16px;cursor:pointer; }
    .m-cancel:hover { background:#EADFD3; }

    /* ── Emoji picker ── */
    .emoji-btn { width:54px;height:46px;background:#FFF8F1;border:1.5px solid #E8DFD5;border-radius:14px;font-size:1.3rem;cursor:pointer;display:flex;align-items:center;justify-content:center; }
    .emoji-btn:hover { border-color:#C24A0A; }
    .emoji-overlay { position:fixed;inset:0;background:rgba(29,26,23,0.45);backdrop-filter:blur(2px);z-index:1100;display:none;align-items:center;justify-content:center;padding:16px; }
    .emoji-overlay.open { display:flex; }
    .emoji-sheet { background:#fff;border-radius:22px;padding:16px;width:100%;max-width:340px;max-height:70vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.25); }
    .emoji-sheet-title { font-family:'Noto Sans Georgian',sans-serif;font-size:0.8rem;font-weight:800;color:#6B625A;margin-bottom:10px; }
    .emoji-grid { display:grid;grid-template-columns:repeat(8,1fr);gap:2px; }
    .ep { font-size:1.3rem;padding:5px;border-radius:8px;cursor:pointer;text-align:center;border:none;background:none;line-height:1.4; }
    .ep:hover { background:#FFF4EA; }

    /* ── Add-item modal tab switcher ── */
    .tab-switch { display:flex; gap:6px; margin-bottom:16px; background:#F5EFE8; border-radius:999px; padding:4px; border:none; }
    .tab-btn { flex:1; background:none; border:none; border-radius:999px; font-family:inherit; font-size:0.88rem; font-weight:800; color:#6B625A; padding:9px; cursor:pointer; margin:0; }
    .tab-btn.active { background:#fff; color:#1D1A17; box-shadow:0 1px 0 #E8DFD5; }

    @media(max-width:640px) { .card{padding:16px;border-radius:22px;} .tpl-grid{grid-template-columns:repeat(auto-fill,minmax(130px,1fr));} .emoji-grid{grid-template-columns:repeat(7,1fr);} .item-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
</style>

<div class="aw">
    <div class="ks-cp-hero dark">
        <span class="ks-cp-hero-ic" aria-hidden="true">🛍️</span>
        <span class="ks-cp-eyebrow" style="color:#FFC49A;">{{ $child->name }}-ს აქვს {{ (int) floor($child->childSetting?->coins ?? 0) }} მონეტა</span>
        <h1 class="ks-cp-title">ოჯახის მარკეტი</h1>
        <span class="ks-cp-sub" style="color:#E8DCCF;">დაამატე ჯილდოები, რომლებსაც შვილი მონეტებით იყიდის</span>
    </div>

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

    {{-- Pending requests --}}
    @if($pending->count())
    <div class="card" style="box-shadow:0 0 0 2px #FFC49A;">
        <div class="sec" style="color:#8A3407;">⏳ ელოდება პასუხს · {{ $pending->count() }}</div>
        @foreach($pending as $p)
        <div class="pend-card">
            <div class="pend-icon">{{ $p->item->icon }}</div>
            <div class="pend-info">
                <div class="pend-title">{{ $p->item->title }}</div>
                <div class="pend-meta">💰 {{ $p->coins_spent }} მონეტა · {{ $p->created_at->diffForHumans() }}</div>
            </div>
            <div class="pend-actions">
                <form method="POST" action="{{ route('market.approve', $p) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-ok">✓ დადასტურება</button>
                </form>
                <form method="POST" action="{{ route('market.cancel', $p) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm" onclick="return confirm('გაუქმდეს? მონეტები დაბრუნდება.')">✕ გაუქმება</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Current market items --}}
    <div class="card">
        <div class="sec">მარკეტის პროდუქტები · {{ $items->count() }}</div>
        @forelse($items as $item)
        @if($loop->first)<div class="item-grid">@endif
        <div class="item-card" style="{{ !$item->is_active ? 'opacity:0.55;' : '' }}">
            <form method="POST" action="{{ route('market.item.destroy', $item) }}" class="item-card-del">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
            <div class="item-card-icon">{{ $item->icon }}</div>
            <div class="item-card-title">{{ $item->title }}</div>
            @if($item->category)<div class="item-cat">{{ $item->category }}</div>@endif
            <div class="item-card-footer">
                <span class="cost-badge">💰 {{ $item->coin_cost }}</span>
                <form method="POST" action="{{ route('market.item.toggle', $item) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="toggle-pill {{ $item->is_active ? 'on' : 'off' }}">
                        {{ $item->is_active ? '● ჩართ.' : '○ გამრ.' }}
                    </button>
                </form>
            </div>
        </div>
        @if($loop->last)</div>@endif
        @empty
        <div style="color:#6B625A;font-size:0.92rem;text-align:center;padding:14px 0;">მარკეტი ცარიელია — დაამატე შაბლონიდან ან ხელით</div>
        @endforelse
    </div>

    <button type="button" class="ks-mk-add" onclick="openAddItemModal()">+ ჯილდოს დამატება</button>

    {{-- Approved history --}}
    @if($approved->count())
    <div class="card">
        <div class="sec">✓ დადასტურებული · ბოლო {{ $approved->count() }}</div>
        @foreach($approved as $ap)
        <div class="appr-row">
            <span>{{ $ap->item->icon }}</span>
            <span>{{ $ap->item->title }}</span>
            <span class="cost-badge">💰 {{ $ap->coins_spent }}</span>
            <span class="appr-date">{{ $ap->updated_at->format('d.m.Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('modals')
{{-- Add item modal (manual entry / templates) --}}
<div class="m-overlay" id="addItemModal" onclick="if(event.target===this)closeAddItemModal()">
    <div class="m-box" style="max-width:480px;">
        <div class="m-title">
            <span style="flex:1;">+ პროდუქტის დამატება</span>
            <button type="button" class="m-cancel" style="padding:4px 10px;" onclick="closeAddItemModal()">✕</button>
        </div>

        <div class="tab-switch">
            <button type="button" class="tab-btn active" id="aiTabManual" onclick="switchItemTab('manual')">✎ ხელით</button>
            <button type="button" class="tab-btn" id="aiTabTpl" onclick="switchItemTab('templates')">📋 შაბლონები</button>
        </div>

        <div id="aiPanelManual">
            <form method="POST" action="{{ route('market.store', $child) }}" id="customForm">
                @csrf
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <button type="button" class="emoji-btn" id="emojiBtn" onclick="openEmojiModal()" title="სმაილის არჩევა">
                        <span id="emojiDisplay">🎁</span>
                    </button>
                    <input type="hidden" name="icon" id="iconInput" value="🎁">
                    <input type="text" name="title" class="fc" placeholder="პროდუქტის სახელი" required style="flex:1;min-width:140px;">
                    <input type="number" name="coin_cost" class="fc" placeholder="💰 მონეტა" min="1" max="9999" required style="width:110px;">
                </div>
                <button type="submit" class="m-save" style="width:100%;margin-top:14px;">+ მარკეტში დამატება</button>
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
