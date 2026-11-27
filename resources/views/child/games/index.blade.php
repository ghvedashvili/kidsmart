@extends('layouts.app')
@section('content')
@php
    $__gameBg = ['#FFE3CC', '#FFF3EA', '#FFF3EA', '#DFF5E3', '#FFF3EA', '#FFF1C9'];
@endphp
<style>
.ks-cp .ks-games { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
.ks-cp .ks-games .ks-game { justify-content: flex-start !important; gap: 6px; min-height: 0; padding: 16px; }
.ks-cp .ks-games .ks-game-ic { position: static; flex: 0 0 auto; margin: 0; }
.ks-cp .ks-games .ks-game-n { margin-top: 14px; display: block; }
.ks-cp .ks-games .ks-game-s { display: block; font-size: 13px !important; line-height: 1.45; font-weight: 600; color: #4A423B; overflow-wrap: anywhere; }
@media (max-width: 520px) {
    .ks-cp .ks-games { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .ks-cp .ks-games .ks-game { min-height: 0; padding: 14px; border-radius: 20px; }
    .ks-cp .ks-games .ks-game-ic { width: 44px; height: 44px; font-size: 22px; }
    .ks-cp .ks-games .ks-game-n { margin-top: 10px; }
}
</style>
<div class="ks-cp">
    <div class="ks-cp-hero green">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎮</span>
        <span class="ks-cp-eyebrow">დაისვენე და ითამაშე</span>
        <h1 class="ks-cp-title">თამაშები</h1>
        <span class="ks-cp-sub">ითამაშე KidSmart-თან და გააუმჯობესე შენი ანგარიში!</span>
    </div>

    @if(session('error'))<div role="alert" style="background:#FFF1C2;color:#5A3E00;border-radius:16px;padding:12px 16px;font-weight:700;">{{ session('error') }}</div>@endif
    @if($games->isEmpty())<div style="background:#fff;border-radius:20px;padding:30px 16px;text-align:center;color:#6B625A;font-weight:700;">თამაშები ჯერ არ არის.</div>@endif
    @if(!empty($locked))
    <div role="status" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:#FFF3EA;border:2px solid #FFC49A;border-radius:20px;padding:14px 16px;">
        <span aria-hidden="true" style="font-size:30px;line-height:1;">🔒</span>
        <span style="flex:1;min-width:200px;"><b style="display:block;font-size:16px;font-weight:900;color:#2B2622;">ჯერ დღის ტესტი დაწერე</b><span style="font-size:14px;color:#6B625A;">თამაშები გაიხსნება, როგორც კი დღევანდელ ტესტს დაასრულებ.</span></span>
        <a href="{{ route('test.start') }}" style="display:inline-flex;align-items:center;gap:6px;height:46px;padding:0 18px;border-radius:16px;background:#E2590F;color:#fff;font-weight:900;text-decoration:none;">დღის ტესტი <i class="bi bi-arrow-right"></i></a>
    </div>
    @endif
    <div class="ks-games"@if(!empty($locked)) style="opacity:.55;filter:grayscale(.4);"@endif>
        @foreach($games as $game)
        <a href="{{ !empty($locked) ? route('test.start') : route($game['route']) }}" class="ks-game" style="background: {{ $__gameBg[$loop->index % count($__gameBg)] }};"@if(!empty($locked)) aria-label="{{ $game['name'] }} — ჯერ დღის ტესტი დაწერე"@endif>
            <span class="ks-game-ic" aria-hidden="true">{{ $game['icon'] }}</span>
            <span class="ks-game-n">{{ $game['name'] }}</span>
            <span class="ks-game-s">
                @if(($game['type'] ?? 'versus') === 'puzzle')
                    @if($game['best_score'])
                        რეკორდი: {{ $game['best_score'] }} ამოხსნილი
                    @else
                        {{ $game['desc'] ?? 'ახალი თამაში!' }}
                    @endif
                @elseif(($game['type'] ?? 'versus') === 'quest')
                    @if($game['stars'])
                        ★ {{ $game['stars'] }} · სახლი {{ $game['house'] + 1 }} / 22
                    @else
                        {{ $game['desc'] ?? 'ახალი თამაში!' }}
                    @endif
                @elseif(($game['type'] ?? 'versus') === 'rain')
                    @if($game['best_score'])
                        რეკორდი: {{ $game['best_score'] }} ქულა
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
