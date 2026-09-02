@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
body { font-family: 'Nunito', sans-serif; background: transparent !important; }
.wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
@media (min-width: 760px)  { .wrap { max-width: 700px; } .item-grid, .reward-grid { grid-template-columns: repeat(5, 1fr); } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } .item-grid, .reward-grid { grid-template-columns: repeat(6, 1fr); } }

.back-btn { display:inline-flex; align-items:center; gap:6px; margin:0 0 0; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#d97706; text-decoration:none; background:white; border-radius:99px; padding:8px 16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.coin-pill { display:inline-flex; align-items:center; gap:6px; margin:10px 0 0; background:#fef9c3; border:1px solid #fde68a; border-radius:99px; padding:8px 16px; font-family:'Fredoka One',cursive; font-size:0.85rem; color:#92400e; }

.section { padding:16px 0 0; }
.sec-title { font-size:0.68rem; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:#64748b; margin-bottom:10px; padding-left:2px; }

/* Flash */
.flash-ok  { background:#ecfdf5; border:1px solid #6ee7b7; border-radius:10px; padding:10px 14px; font-size:0.8rem; font-weight:700; color:#065f46; margin-bottom:12px; }
.flash-err { background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:10px 14px; font-size:0.8rem; font-weight:700; color:#dc2626; margin-bottom:12px; }

/* Item cubes — same grid language as the achievements page */
.item-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:28px; }
.item-cube {
    background: white; border-radius: 16px; padding: 16px 12px 14px;
    text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    border: 2px solid transparent; transition: transform 0.15s;
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.item-cube.can-afford { border-color: #fde68a; box-shadow: 0 2px 12px rgba(245,158,11,0.15); }
.item-cube:hover { transform: translateY(-2px); }
.item-cube .item-icon { font-size: 2.2rem; line-height:1; margin-bottom: 2px; }
.item-title { font-family:'Fredoka One',cursive; font-size: 0.82rem; color: #1e293b; line-height:1.25; }
.item-cost  { display:inline-flex; align-items:center; gap:4px; background:#fef9c3; border:1px solid #fde68a; border-radius:20px; padding:3px 10px; font-size:0.7rem; font-weight:900; color:#92400e; margin-top:2px; }
.buy-btn {
    width: 100%; margin-top: 6px; box-sizing: border-box;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; border: none; border-radius: 10px;
    font-family: 'Fredoka One', cursive; font-size: 0.8rem;
    padding: 8px 10px; cursor: pointer; transition: opacity 0.15s;
}
.buy-btn:hover { opacity: 0.9; }
.buy-btn:disabled, .buy-btn.disabled {
    background: #e2e8f0; color: #94a3b8; cursor: not-allowed; opacity: 1;
}
.pend-badge {
    display:inline-block; margin-top:6px; background:#fef3c7; border:1px solid #fde68a;
    border-radius:20px; padding:4px 12px; font-size:0.68rem; font-weight:800;
    color:#92400e; white-space:nowrap;
}

/* Approved purchases — cube style matching the achievements page's earned cards */
.reward-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; }
.reward-cube {
    background: white; border-radius: 16px; padding: 16px 10px 14px;
    text-align: center; box-shadow: 0 2px 12px rgba(245,158,11,0.15);
    border: 2px solid #fde68a; position: relative;
}
.reward-cube .earned-badge {
    position: absolute; top: 8px; right: 8px; width: 16px; height: 16px;
    background: #f59e0b; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.55rem; color: white; font-weight: 900;
}
.reward-cube .r-icon  { font-size: 2rem; margin-bottom: 6px; }
.reward-cube .r-title { font-family:'Fredoka One',cursive; font-size: 0.8rem; color: #1e293b; }
.reward-cube .r-coins { font-size: 0.6rem; font-weight: 800; color: #f59e0b; margin-top: 4px; }
.reward-cube .r-date  { font-size: 0.56rem; color: #94a3b8; font-weight: 700; margin-top: 2px; }
</style>

<div class="wrap">
<a href="{{ route('dashboard') }}" class="back-btn">← დაბრუნება</a>
<div class="coin-pill">💰 {{ $coins }} მონეტა</div>

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

    <div class="item-grid">
    @foreach($items as $item)
    @php
        $pending   = $item->pendingPurchase(auth()->id());
        $canAfford = $coins >= $item->coin_cost;
    @endphp
    <div class="item-cube {{ $canAfford && !$pending ? 'can-afford' : '' }}">
        <div class="item-icon">{{ $item->icon }}</div>
        <div class="item-title">{{ $item->title }}</div>
        <div class="item-cost">💰 {{ $item->coin_cost }} მონეტა</div>
        @if($pending)
            <span class="pend-badge">⏳ ელოდება</span>
        @elseif($canAfford)
            <form method="POST" action="{{ route('market.buy', $item) }}" style="width:100%;">
                @csrf
                <button type="submit" class="buy-btn">🛍 ყიდვა</button>
            </form>
        @else
            <button class="buy-btn disabled" disabled>💰 {{ $item->coin_cost - $coins }} კიდევ</button>
        @endif
    </div>
    @endforeach
    </div>

    @endif
</div>

@if($approved->count())
<div class="section" style="margin-top:24px;">
    <div class="sec-title">✓ ჩემი ჯილდოები</div>
    <div class="reward-grid">
    @foreach($approved as $ap)
    <div class="reward-cube">
        <div class="earned-badge">✓</div>
        <div class="r-icon">{{ $ap->item->icon }}</div>
        <div class="r-title">{{ $ap->item->title }}</div>
        <div class="r-coins">💰 {{ $ap->coins_spent }} მონეტა</div>
        <div class="r-date">{{ $ap->updated_at->format('d.m.Y') }}</div>
    </div>
    @endforeach
    </div>
</div>
@endif
</div>
@endsection
