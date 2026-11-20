@extends('layouts.app')
@section('content')
@php
    $__gameBg = ['#FFE3CC', '#EEE8FF', '#DCEBFF', '#DFF5E3', '#FCE4EC', '#FFF1C9'];
@endphp
<div class="ks-cp">
    <div class="ks-cp-hero green">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎮</span>
        <span class="ks-cp-eyebrow">დაისვენე და ითამაშე</span>
        <h1 class="ks-cp-title">თამაშები</h1>
        <span class="ks-cp-sub">ითამაშე KidSmart-თან და გააუმჯობესე შენი ანგარიში!</span>
    </div>

    <div class="ks-games">
        @foreach($games as $game)
        <a href="{{ route($game['route']) }}" class="ks-game" style="background: {{ $__gameBg[$loop->index % count($__gameBg)] }}; min-height: 170px;">
            <span class="ks-game-ic" aria-hidden="true">{{ $game['icon'] }}</span>
            <span class="ks-game-n">{{ $game['name'] }}</span>
            <span class="ks-game-s">
                @if(($game['type'] ?? 'versus') === 'score')
                    შენი საუკეთესო: {{ $game['best_score'] }} წყვილი
                @else
                    {{ $game['wins'] }} მოგება · {{ $game['losses'] }} წაგება
                @endif
            </span>
        </a>
        @endforeach
    </div>
</div>
@endsection
