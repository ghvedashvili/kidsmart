@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Goldman&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    @media (min-width: 760px)  { .wrap { max-width: 700px; } }
    @media (min-width: 1040px) { .wrap { max-width: 960px; } }

    .page-hero {
        width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
        min-height: 120px; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden; margin-bottom: 20px;
        background-image:
            linear-gradient(90deg, rgba(238,235,255,0.94) 0%, rgba(238,235,255,0.78) 45%, rgba(238,235,255,0.08) 68%),
            url('/img/mission-hero.jpg');
        background-size: cover; background-position: right center; background-repeat: no-repeat;
        box-shadow: 0 8px 20px rgba(108,92,231,0.18);
    }
    .page-hero-title { font-family:'Fredoka One',cursive; font-size:1.15rem; color:#4338ca; margin-bottom:4px; }
    .page-hero-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.75rem; color:#6c5ce7; }
    .hub-hero-tags { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    .hub-hero-tag {
        font-family: 'Goldman', monospace; font-size: 0.62rem; font-weight: 700; color: #4338ca;
        background: rgba(255,255,255,0.75); border-radius: 100px; padding: 4px 12px;
    }

    /* ── info cards, matching the child's own dashboard stat cards ── */
    .hub-stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; }
    .hub-stat-card {
        background: #fff; border-radius: 16px; padding: 14px; text-align: left;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05); border: 1.5px solid #f5f5f5; text-decoration: none; color: inherit;
        display: flex; position: relative; overflow: hidden; min-height: 100px;
        transition: transform 0.15s;
    }
    .hub-stat-card:hover { transform: translateY(-2px); color: inherit; }
    .hub-stat-card-content { position: relative; z-index: 1; max-width: 66%; display: flex; flex-direction: column; gap: 8px; }
    .hub-stat-card-icon-img { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 65px; object-fit: contain; z-index: 0; pointer-events: none; }
    .hub-stat-card-head { display: flex; align-items: center; gap: 8px; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.6rem; color: #999; text-transform: uppercase; letter-spacing: 0.02em; }
    .hub-stat-card-val { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #1a1a2e; }
    .hub-stat-card-link { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.58rem; color: #6c5ce7; }

    /* ── olympiad + reminder, matching the child's own mini-cards-row ── */
    .hub-mini-row { display: flex; flex-direction: column; gap: 10px; width: 100%; margin-top: 12px; }
    @media (min-width: 520px) { .hub-mini-row { display: grid; grid-template-columns: 1fr 1fr; } }
    .hub-oly-card, .hub-remind-card {
        display: flex; align-items: center; gap: 14px; border-radius: 18px; padding: 18px;
        text-decoration: none; transition: transform 0.15s; border: none; cursor: pointer;
        width: 100%; text-align: left; font-family: inherit;
    }
    .hub-oly-card { background: linear-gradient(160deg, #fffbeb, #fde68a); color: #78350f; box-shadow: 0 8px 20px rgba(217,119,6,0.18); }
    .hub-oly-card:hover { transform: translateY(-2px); color: #78350f; }
    .hub-remind-card { background: linear-gradient(160deg, #eef2ff, #c7d2fe); color: #312e81; box-shadow: 0 8px 20px rgba(79,70,229,0.18); }
    .hub-remind-card:hover { transform: translateY(-2px); color: #312e81; }
    .hub-mini-icon { font-size: 2.2rem; flex-shrink: 0; }
    .hub-mini-text { flex: 1; min-width: 0; }
    .hub-mini-title { font-family: 'Fredoka One', cursive; font-size: 1rem; margin-bottom: 2px; }
    .hub-mini-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.7rem; opacity: 0.85; }
    .hub-mini-arrow { font-size: 1.1rem; flex-shrink: 0; opacity: 0.85; }
    .hub-toast-wrap {
        position: fixed; top: 0; left: 50%; transform: translate(-50%, -130%);
        z-index: 2000; display: flex; flex-direction: column; gap: 8px; align-items: center;
        width: 100%; max-width: 420px; padding: 14px 16px 0; box-sizing: border-box;
        transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }
    .hub-toast-wrap.show { transform: translate(-50%, 0); }
    .hub-toast {
        font-family: 'Goldman', monospace; font-size: 0.7rem; letter-spacing: 0.03em;
        background: #fff; color: #15803d; border-radius: 14px; padding: 12px 20px; text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18); max-width: 100%;
    }

    .remind-modal { position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:200;display:none;flex-direction:column;align-items:center;justify-content:flex-start;padding:80px 16px 48px;overflow-y:auto; }
    .remind-modal.open { display:flex; }
    .remind-box { background:#fff;border-radius:16px;padding:24px;width:100%;max-width:400px;flex-shrink:0;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:modalIn 0.25s cubic-bezier(0.175,0.885,0.32,1.275); }
    @keyframes modalIn { from { transform: scale(0.92); opacity:0; } to { transform: scale(1); opacity:1; } }
    .remind-title { font-family:'Goldman',monospace;font-size:0.88rem;color:#111;letter-spacing:0.06em;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between; }
    .remind-close { background: none; border: none; color: #bbb; font-size: 1.1rem; cursor: pointer; padding: 0; }
    .remind-close:hover { color: #555; }
    .remind-textarea { width:100%;border:1px solid #e8e8e8;border-radius:8px;padding:10px 12px;font-family:'Goldman',monospace;font-size:0.8rem;color:#333;resize:none;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:#fafafa; }
    .remind-textarea:focus { border-color:#aaa;background:#fff; }
    .remind-hint { font-family:'Goldman',monospace;font-size:0.58rem;color:#ccc;margin:6px 0 14px;letter-spacing:0.04em; }
    .remind-send { width:100%;background:#059669;border:none;border-radius:8px;color:#fff;font-family:'Goldman',monospace;font-size:0.82rem;letter-spacing:0.06em;padding:12px;cursor:pointer;transition:background 0.2s; }
    .remind-send:hover { background:#047857; }
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

<div class="wrap">
    <div class="page-hero">
        <div class="page-hero-title">👦 {{ $child->name }}-ის გვერდი</div>
        <div class="page-hero-sub">ყველა საჭირო ინფორმაცია ერთ ადგილზე</div>
        <div class="hub-hero-tags">
            <span class="hub-hero-tag">🎓 {{ $child->childSetting?->grade?->name ?? 'კლასი —' }}</span>
            @if($child->childSetting)
            <span class="hub-hero-tag">🏆 დონე {{ $child->childSetting->difficulty }}</span>
            @endif
        </div>
    </div>

    <div class="hub-stat-grid">
        <a href="{{ route('child.practice-stats', $child) }}" class="hub-stat-card">
            <img src="/img/Mission.jpg" class="hub-stat-card-icon-img" alt="">
            <div class="hub-stat-card-content">
                <div class="hub-stat-card-head">სავარჯიშოები</div>
                <div class="hub-stat-card-val">{{ $practiceAnsweredCount }}</div>
                <div class="hub-stat-card-link">დღეს {{ $practiceTodayCount }} · სტატისტიკა →</div>
            </div>
        </a>
        <a href="{{ route('child.stats', $child) }}" class="hub-stat-card">
            <img src="/img/tests.jpg" class="hub-stat-card-icon-img" alt="">
            <div class="hub-stat-card-content">
                <div class="hub-stat-card-head">ტესტები</div>
                <div class="hub-stat-card-val">{{ $totalTestsCount }}</div>
                <div class="hub-stat-card-link">ისტორია →</div>
            </div>
        </a>
        <a href="{{ route('child.achievements', $child) }}" class="hub-stat-card">
            <img src="/img/Achievements.jpg" class="hub-stat-card-icon-img" alt="">
            <div class="hub-stat-card-content">
                <div class="hub-stat-card-head">მიღწევები</div>
                <div class="hub-stat-card-val">{{ $achCount }}</div>
                <div class="hub-stat-card-link">ყველას ნახვა →</div>
            </div>
        </a>
        <a href="{{ route('market.index', $child) }}" class="hub-stat-card">
            <img src="/img/Market.jpg" class="hub-stat-card-icon-img" alt="">
            <div class="hub-stat-card-content">
                <div class="hub-stat-card-head">მარკეტი</div>
                <div class="hub-stat-card-val">💰 {{ $coins }}</div>
                <div class="hub-stat-card-link">
                    @if($pendingMarket)
                        {{ $pendingMarket }} მოთხოვნა →
                    @else
                        ნახვა →
                    @endif
                </div>
            </div>
        </a>
    </div>

    <div class="hub-mini-row">
        <a href="{{ route('child.olympiad-stats', $child) }}" class="hub-oly-card">
            <span class="hub-mini-icon">🏆</span>
            <div class="hub-mini-text">
                <div class="hub-mini-title">ოლიმპიადა</div>
                <div class="hub-mini-sub">
                    @if($olympiadStatus['already_attempted_today'])
                        {{ $olympiadStatus['todays_test']->completed_at ? 'დღევანდელი ოლიმპიადა დასრულებულია' : 'ოლიმპიადა დაწყებულია' }}
                    @elseif($olympiadStatus['eligible_today'])
                        დღეს შეუძლია დაწეროს — სულ {{ $olympiadCount }} ოლიმპიადა
                    @else
                        {{ $olympiadStatus['reason'] }}
                    @endif
                </div>
            </div>
            <span class="hub-mini-arrow">→</span>
        </a>

        <button type="button" class="hub-remind-card" onclick="document.getElementById('remindModal').classList.add('open')">
            <span class="hub-mini-icon">🔔</span>
            <div class="hub-mini-text">
                <div class="hub-mini-title">შეხსენება</div>
                <div class="hub-mini-sub">გაუგზავნე შეტყობინება {{ $child->name }}-ს</div>
            </div>
            <span class="hub-mini-arrow">→</span>
        </button>
    </div>
</div>

<div id="remindModal" class="remind-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="remind-box">
        <div class="remind-title">
            <span>🔔 შეხსენება — {{ $child->name }}</span>
            <button type="button" class="remind-close" onclick="document.getElementById('remindModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('push.remind', $child) }}">
            @csrf
            <textarea name="message" class="remind-textarea" rows="3"
                placeholder="ტექსტი (სურვილისამებრ)&#10;მაგ: ახლავე გააკეთე ტესტი! 📝"></textarea>
            <div class="remind-hint">ცარიელი = სტანდარტული შეტყობინება</div>
            <button type="submit" class="remind-send">📤 გაგზავნა</button>
        </form>
    </div>
</div>
@endsection
