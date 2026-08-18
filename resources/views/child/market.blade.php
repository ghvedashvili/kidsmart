<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>ჩემი მარკეტი</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Nunito', sans-serif; background: #f1f5f9; min-height: 100vh; }
.hero {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; padding: 32px 20px 24px; text-align: center;
}
.hero-name  { font-size: 0.78rem; font-weight: 800; opacity: 0.75; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 4px; }
.hero-title { font-size: 1.6rem; font-weight: 900; margin-bottom: 14px; }
.hero-coins { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.2); border-radius:12px; padding:8px 20px; font-size:1.1rem; font-weight:900; backdrop-filter:blur(4px); }

.back-btn { display:inline-flex; align-items:center; gap:6px; margin:16px 20px 0; font-family:'Nunito',sans-serif; font-size:0.8rem; font-weight:700; color:#d97706; text-decoration:none; background:white; border-radius:10px; padding:8px 16px; box-shadow:0 1px 4px rgba(0,0,0,0.08); }

.section { padding:16px 14px 0; max-width:560px; margin:0 auto; }
.sec-title { font-size:0.68rem; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; margin-bottom:10px; padding-left:2px; }

/* Flash */
.flash-ok  { background:#ecfdf5; border:1px solid #6ee7b7; border-radius:10px; padding:10px 14px; font-size:0.8rem; font-weight:700; color:#065f46; margin-bottom:12px; }
.flash-err { background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:10px 14px; font-size:0.8rem; font-weight:700; color:#dc2626; margin-bottom:12px; }

/* Item cards */
.item-card {
    background: white; border-radius: 16px; padding: 16px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: flex; align-items: center; gap: 14px;
    border: 2px solid transparent; transition: border-color 0.15s;
}
.item-card.can-afford { border-color: #fde68a; }
.item-icon { font-size: 2rem; flex-shrink: 0; }
.item-info { flex: 1; }
.item-title { font-family:'Fredoka One',cursive; font-size: 1rem; color: #1e293b; }
.item-cat   { font-size: 0.62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px; }
.item-cost  { display:inline-flex; align-items:center; gap:4px; background:#fef9c3; border:1px solid #fde68a; border-radius:20px; padding:3px 10px; font-size:0.76rem; font-weight:900; color:#92400e; margin-top:6px; }
.buy-btn {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; border: none; border-radius: 10px;
    font-family: 'Fredoka One', cursive; font-size: 0.88rem;
    padding: 9px 16px; cursor: pointer; transition: opacity 0.15s;
    white-space: nowrap; flex-shrink: 0;
}
.buy-btn:hover { opacity: 0.9; }
.buy-btn:disabled, .buy-btn.disabled {
    background: #e2e8f0; color: #94a3b8; cursor: not-allowed; opacity: 1;
}
.pend-badge {
    display:inline-block; background:#fef3c7; border:1px solid #fde68a;
    border-radius:20px; padding:3px 10px; font-size:0.68rem; font-weight:800;
    color:#92400e; white-space:nowrap; flex-shrink:0;
}

/* Approved purchases */
.reward-card {
    background: white; border-radius: 14px; padding: 14px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display:flex; align-items:center; gap:12px;
    border-left: 4px solid #34d399;
}
.reward-icon  { font-size: 1.6rem; flex-shrink:0; }
.reward-title { font-family:'Fredoka One',cursive; font-size:0.92rem; color:#1e293b; }
.reward-date  { font-size:0.6rem; font-weight:700; color:#94a3b8; margin-top:2px; }
.reward-coins { font-size:0.62rem; font-weight:800; color:#f59e0b; }

.bottom-pad { height:50px; }
</style>
</head>
<body>

<div class="hero">
    <div class="hero-name">{{ auth()->user()->name }}</div>
    <div class="hero-title">🛒 მარკეტი</div>
    <div class="hero-coins">💰 {{ $coins }} მონეტა</div>
</div>

<a href="{{ route('dashboard') }}" class="back-btn">← დაბრუნება</a>

<div class="section" style="padding-top:20px;">

    @if(session('market_ok'))
    <div class="flash-ok">{{ session('market_ok') }}</div>
    @endif
    @if(session('market_error'))
    <div class="flash-err">{{ session('market_error') }}</div>
    @endif

    @if($items->isEmpty())
    <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:10px;">🛒</div>
        <div style="font-family:'Fredoka One',cursive;font-size:1rem;">მარკეტი ჯერ ცარიელია</div>
        <div style="font-size:0.78rem;margin-top:4px;">მშობელი მალე დაამატებს პროდუქტებს</div>
    </div>
    @else

    @php $byCategory = $items->groupBy(fn($i) => $i->category ?: ''); @endphp
    @foreach($byCategory as $cat => $catItems)
    @if($cat)<div class="sec-title">{{ $cat }}</div>@endif
    @foreach($catItems as $item)
    @php
        $pending   = $item->pendingPurchase(auth()->id());
        $canAfford = $coins >= $item->coin_cost;
    @endphp
    <div class="item-card {{ $canAfford && !$pending ? 'can-afford' : '' }}">
        <div class="item-icon">{{ $item->icon }}</div>
        <div class="item-info">
            <div class="item-title">{{ $item->title }}</div>
            @if($item->category)<div class="item-cat">{{ $item->category }}</div>@endif
            <div class="item-cost">💰 {{ $item->coin_cost }} მონეტა</div>
        </div>
        @if($pending)
            <span class="pend-badge">⏳ ელოდება</span>
        @elseif($canAfford)
            <form method="POST" action="{{ route('market.buy', $item) }}">
                @csrf
                <button type="submit" class="buy-btn">🛍 ყიდვა</button>
            </form>
        @else
            <button class="buy-btn disabled" disabled>💰 {{ $item->coin_cost - $coins }} კიდევ</button>
        @endif
    </div>
    @endforeach
    @endforeach

    @endif
</div>

@if($approved->count())
<div class="section" style="margin-top:24px;">
    <div class="sec-title">✓ ჩემი ჯილდოები</div>
    @foreach($approved as $ap)
    <div class="reward-card">
        <div class="reward-icon">{{ $ap->item->icon }}</div>
        <div>
            <div class="reward-title">{{ $ap->item->title }}</div>
            <div class="reward-coins">💰 {{ $ap->coins_spent }} მონეტა</div>
            <div class="reward-date">{{ $ap->updated_at->format('d.m.Y') }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="bottom-pad"></div>
</body>
</html>
