@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero dark">
        <span class="ks-cp-hero-ic" aria-hidden="true">🛍️</span>
        <span class="ks-cp-eyebrow" style="color:#FFC49A;">{{ (int) floor($coins) }} მონეტა — აირჩიე შენი ჯილდო!</span>
        <h1 class="ks-cp-title">მარკეტი</h1>
        <span class="ks-cp-sub" style="color:#FFE3CC;">ჯილდოებს მშობელი ამატებს და ადასტურებს</span>
    </div>

    @if(session('market_ok'))
    <div class="ks-cp-flash ok">{{ session('market_ok') }}</div>
    @endif
    @if(session('market_error'))
    <div class="ks-cp-flash err">{{ session('market_error') }}</div>
    @endif

    @if($items->isEmpty())
    <div class="ks-cp-empty">
        <div class="ks-cp-empty-ic">🛒</div>
        <div class="ks-cp-empty-t">მარკეტი ჯერ ცარიელია</div>
        <div class="ks-cp-empty-s">მშობელი მალე დაამატებს ჯილდოებს</div>
    </div>
    @else
    <div class="ks-cp-grid">
    @foreach($items as $item)
    @php
        $pending   = $item->pendingPurchase(auth()->id());
        $canAfford = $coins >= $item->coin_cost;
    @endphp
    <div class="ks-cp-item {{ $canAfford && !$pending ? 'hl' : '' }}">
        <div class="ks-cp-item-ic">{{ $item->icon }}</div>
        <div class="ks-cp-item-t">{{ $item->title }}</div>
        <span class="ks-pill" style="font-size:14px;padding:3px 12px 3px 3px;box-shadow:none;background:#F7F6F3;">
            <svg viewBox="0 0 24 24" style="width:22px;height:22px;" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>
            {{ $item->coin_cost }}
        </span>
        @if($pending)
            <span class="ks-cp-chip" style="margin-top:6px;background:#FFF1C9;color:#8A5A00;">⏳ მშობელს ელოდება</span>
        @elseif($canAfford)
            <form method="POST" action="{{ route('market.buy', $item) }}" style="width:100%;">
                @csrf
                <button type="submit" class="ks-cp-btn orange">ყიდვა</button>
            </form>
        @else
            <button class="ks-cp-btn disabled" disabled>კიდევ {{ $item->coin_cost - $coins }}</button>
        @endif
    </div>
    @endforeach
    </div>
    @endif

    @if($approved->count())
    <h2 class="ks-cp-h2">ჩემი ჯილდოები</h2>
    <div class="ks-cp-grid">
    @foreach($approved as $ap)
    <div class="ks-cp-item">
        <span class="ks-cp-item-badge">✓</span>
        <div class="ks-cp-item-ic">{{ $ap->item->icon }}</div>
        <div class="ks-cp-item-t">{{ $ap->item->title }}</div>
        <div class="ks-cp-item-s">{{ $ap->coins_spent }} მონეტა · {{ $ap->updated_at->format('d.m.Y') }}</div>
    </div>
    @endforeach
    </div>
    @endif
</div>
@endsection
