@extends('layouts.app')
@section('content')
<style>
    .hub-toast-wrap { position: fixed; top: 16px; left: 50%; transform: translate(-50%, -140%); z-index: 2000; transition: transform .35s cubic-bezier(.175,.885,.32,1.275); pointer-events: none; }
    .hub-toast-wrap.show { transform: translate(-50%, 0); }
    .hub-toast { background: #1D1A17; color: #fff; border-radius: 999px; padding: 10px 20px; font-family: 'Noto Sans Georgian', sans-serif; font-weight: 800; font-size: 14px; box-shadow: 0 10px 28px rgba(0,0,0,.2); }
    .remind-modal { position: fixed; inset: 0; background: rgba(29,26,23,.45); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 16px; }
    .remind-modal.open { display: flex; }
    .remind-box { background: #fff; border-radius: 26px; padding: 24px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.2); font-family: 'Noto Sans Georgian', sans-serif; color: #1D1A17; }
    .remind-title { font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .remind-close { background: #F5EFE8; border: none; color: #1D1A17; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; flex-shrink: 0; }
    .remind-textarea { width: 100%; border: 1.5px solid #E8DFD5; border-radius: 16px; padding: 12px 14px; font-family: inherit; font-size: 15px; color: #1D1A17; resize: none; outline: none; box-sizing: border-box; }
    .remind-textarea:focus { border-color: #E2590F; box-shadow: 0 0 0 3px rgba(226,89,15,.14); }
    .remind-hint { font-size: 13px; color: #6B625A; margin: 6px 0 14px; }
    .remind-send { width: 100%; height: 50px; background: #1D1A17; border: none; border-radius: 25px; color: #fff; font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer; }
    .remind-send:hover { background: #000; }
</style>

@if(session('reminder_sent_' . $child->id))
@push('toasts')
<div class="hub-toast-wrap" id="hubToastWrap">
    <div class="hub-toast">✓ შეხსენება გაიგზავნა</div>
</div>
<script>
(function(){
    var wrap = document.getElementById('hubToastWrap');
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

@php $__av = $child->avatar === 'boy' ? '👦' : ($child->avatar === 'girl' ? '👧' : '🙂'); @endphp
<div class="ks-cp">
    <div class="ks-cp-hero purple">
        <span class="ks-cp-hero-ic" aria-hidden="true">{{ $__av }}</span>
        <span class="ks-cp-eyebrow">ყველა საჭირო ინფორმაცია ერთ ადგილზე</span>
        <h1 class="ks-cp-title">{{ $child->name }}</h1>
        <span class="ks-cp-sub">
            {{ $child->childSetting?->grade?->name ?? 'კლასი არ არის არჩეული' }}
            @if($child->childSetting) · დონე {{ $child->childSetting->difficulty }} @endif
        </span>
    </div>

    <div class="ks-tiles" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <a href="{{ route('child.practice-stats', $child) }}" class="ks-tile">
            <span class="ks-tile-ic">🎯</span>
            <span><span class="ks-tile-v">{{ $practiceAnsweredCount }}</span><br><span class="ks-tile-l">ვარჯიშის კითხვა · დღეს {{ $practiceTodayCount }}</span></span>
        </a>
        <a href="{{ route('child.stats', $child) }}" class="ks-tile">
            <span class="ks-tile-ic">📋</span>
            <span><span class="ks-tile-v">{{ $totalTestsCount }}</span><br><span class="ks-tile-l">ტესტი · ისტორია და ანალიტიკა</span></span>
        </a>
        <a href="{{ route('child.achievements', $child) }}" class="ks-tile">
            <span class="ks-tile-ic">🏅</span>
            <span><span class="ks-tile-v">{{ $achCount }}</span><br><span class="ks-tile-l">მედალი</span></span>
        </a>
        <a href="{{ route('market.index', $child) }}" class="ks-tile" style="{{ $pendingMarket ? 'background:#FFE3CC;box-shadow:none;' : '' }}">
            <span class="ks-tile-ic">🛍️</span>
            <span><span class="ks-tile-v">{{ (int) floor($coins) }}</span><br><span class="ks-tile-l">
                @if($pendingMarket)
                    მონეტა · <b style="color:#8A3407;">{{ $pendingMarket }} მოთხოვნა ელოდება</b>
                @else
                    მონეტა · ოჯახის მარკეტი
                @endif
            </span></span>
        </a>
    </div>

    <div class="ks-row">
        <a href="{{ route('child.olympiad-stats', $child) }}" class="ks-wide ks-cp-row" style="padding:18px 20px;">
            <span class="ks-cp-row-ic" style="background:#FFF1C9;">🏆</span>
            <span class="ks-cp-row-b">
                <span class="ks-cp-row-t">ოლიმპიადა</span>
                <span class="ks-cp-row-s">
                    @if($olympiadStatus['already_attempted_today'])
                        {{ $olympiadStatus['todays_test']?->completed_at ? 'დღევანდელი ოლიმპიადა დასრულებულია' : 'ოლიმპიადა დაწყებულია' }}
                    @elseif($olympiadStatus['eligible_today'])
                        დღეს შეუძლია დაწეროს — სულ {{ $olympiadCount }} ოლიმპიადა
                    @else
                        {{ $olympiadStatus['reason'] }}
                    @endif
                </span>
            </span>
            <i class="bi bi-chevron-right ks-cp-row-end"></i>
        </a>
        <button type="button" class="ks-narrow ks-cp-row" style="padding:18px 20px;border:none;text-align:left;font-family:inherit;cursor:pointer;" onclick="document.getElementById('remindModal').classList.add('open')">
            <span class="ks-cp-row-ic" style="background:#EEE8FF;">🔔</span>
            <span class="ks-cp-row-b">
                <span class="ks-cp-row-t">შეხსენება</span>
                <span class="ks-cp-row-s">გაუგზავნე შეტყობინება {{ $child->name }}-ს</span>
            </span>
            <i class="bi bi-chevron-right ks-cp-row-end"></i>
        </button>
    </div>
</div>

@push('modals')
<div id="remindModal" class="remind-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="remind-box">
        <div class="remind-title">
            <span>შეხსენება — {{ $child->name }}</span>
            <button type="button" class="remind-close" aria-label="დახურვა" onclick="document.getElementById('remindModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('push.remind', $child) }}">
            @csrf
            <textarea name="message" class="remind-textarea" rows="3"
                placeholder="ტექსტი (სურვილისამებრ)&#10;მაგ: ახლავე გააკეთე ტესტი! 📝"></textarea>
            <div class="remind-hint">ცარიელი = სტანდარტული შეტყობინება</div>
            <button type="submit" class="remind-send">გაგზავნა</button>
        </form>
    </div>
</div>
@endpush
@endsection
