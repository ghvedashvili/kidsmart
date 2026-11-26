@extends('layouts.app')
@section('content')
@php
    $__gameBg = ['#FFE3CC', '#FFF3EA', '#FFF3EA', '#DFF5E3', '#FFF3EA', '#FFF1C9'];
@endphp
<div class="ks-cp">
    <div class="ks-cp-hero green">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎮</span>
        <span class="ks-cp-eyebrow">დაისვენე და ითამაშე</span>
        <h1 class="ks-cp-title">თამაშები</h1>
        <span class="ks-cp-sub">ითამაშე KidSmart-თან და გააუმჯობესე შენი ანგარიში!</span>
    </div>

    @if(session('error'))<div role="alert" style="background:#FFF1C2;color:#5A3E00;border-radius:16px;padding:12px 16px;font-weight:700;">{{ session('error') }}</div>@endif
    @if($games->isEmpty())<div style="background:#fff;border-radius:20px;padding:30px 16px;text-align:center;color:#6B625A;font-weight:700;">თამაშები ჯერ არ არის.</div>@endif
    <div class="ks-games">
        @foreach($games as $game)
        <a href="{{ route($game['route']) }}" class="ks-game" style="background: {{ $__gameBg[$loop->index % count($__gameBg)] }}; min-height: 170px;">
            <span class="ks-game-ic" aria-hidden="true">{{ $game['icon'] }}</span>
            <span class="ks-game-n">{{ $game['name'] }}</span>
            <span class="ks-game-s">
                @if(($game['type'] ?? 'versus') === 'puzzle')
                    @if($game['best_score'])
                        რეკორდი: {{ $game['best_score'] }} ამოხსნილი
                    @else
                        {{ $game['desc'] ?? 'ახალი თამაში!' }}
                    @endif
                @elseif(($game['type'] ?? 'versus') === 'score')
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
