@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    @media (min-width: 760px)  { .wrap { max-width: 700px; } }
    @media (min-width: 1040px) { .wrap { max-width: 960px; } }

    .back-btn { display:inline-flex; align-items:center; gap:6px; margin:0 0 24px; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#16a34a; text-decoration:none; background:white; border-radius:99px; padding:8px 16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }

    .hero-card {
        border-radius:20px; padding:22px 20px; margin-bottom:20px; color:#fff;
        position: relative; overflow: hidden; min-height: 140px;
        display: flex; flex-direction: column; justify-content: center;
        background-image:
            linear-gradient(90deg, rgba(21,128,61,0.92) 0%, rgba(21,128,61,0.75) 45%, rgba(21,128,61,0.15) 70%),
            url('/img/games-hero.jpg');
        background-size: cover; background-position: right center; background-repeat: no-repeat;
        box-shadow: 0 10px 24px rgba(22,163,74,0.25);
    }
    .hero-title { font-family:'Fredoka One',cursive; font-size:1.5rem; margin-bottom:4px; }
    .hero-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.78rem; opacity:0.9; }

    .game-card {
        display:flex; align-items:center; gap:14px; width:100%; box-sizing:border-box;
        background:#fff; border-radius:16px; padding:14px 16px; margin-bottom:10px;
        text-decoration:none; box-shadow:0 4px 14px rgba(0,0,0,0.06); transition:box-shadow 0.15s, transform 0.15s;
    }
    .game-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.1); transform:translateY(-2px); }
    .game-icon-tile {
        width:48px; height:48px; border-radius:14px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center; font-size:1.5rem;
        background: linear-gradient(135deg, #86efac, #22c55e);
    }
    .game-info { flex:1; min-width:0; text-align:left; }
    .game-name { font-family:'Fredoka One',cursive; font-size:0.95rem; color:#14532d; }
    .game-record { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.66rem; color:#94a3b8; margin-top:2px; }
    .game-arrow { font-size:1rem; color:#cbd5e1; flex-shrink:0; }
</style>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back-btn">← მთავარი</a>

    <div class="hero-card">
        <div class="hero-title">🎮 თამაშები</div>
        <div class="hero-sub">ითამაშე Kidsmart-თან და გაიუმჯობესე შენი ანგარიში!</div>
    </div>

    @foreach($games as $game)
    <a href="{{ route($game['route']) }}" class="game-card">
        <div class="game-icon-tile">{{ $game['icon'] }}</div>
        <div class="game-info">
            <div class="game-name">{{ $game['name'] }}</div>
            <div class="game-record">
                @if(($game['type'] ?? 'versus') === 'score')
                    შენი საუკეთესო: {{ $game['best_score'] }} წყვილი
                @else
                    შენი ანგარიში: {{ $game['wins'] }} მოგება · {{ $game['losses'] }} წაგება
                @endif
            </div>
        </div>
        <div class="game-arrow">→</div>
    </a>
    @endforeach
</div>
@endsection
