@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Goldman&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    @media (min-width: 760px)  { .wrap { max-width: 700px; } }
    @media (min-width: 1040px) { .wrap { max-width: 960px; } }

    .hub-header {
        display: flex; align-items: center; gap: 14px;
        background: #fff; border-radius: 20px; padding: 18px 20px;
        margin-bottom: 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.055); border: 1px solid #f2f2f5;
    }
    .hub-avatar {
        width: 56px; height: 56px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(160deg, #6c5ce7, #a29bfe);
        color: #fff; font-size: 1.6rem; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 12px rgba(108,92,231,0.32);
    }
    .hub-name { font-family: 'Goldman', monospace; font-size: 1.05rem; color: #111; }
    .hub-sub { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.72rem; color: #999; margin-top: 4px; }

    .hub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (min-width: 520px) { .hub-grid { grid-template-columns: repeat(4, 1fr); } }

    .hub-tile {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        background: #fff; border-radius: 18px; padding: 22px 12px; text-align: center;
        text-decoration: none; box-shadow: 0 4px 18px rgba(0,0,0,0.055); border: 1px solid #f2f2f5;
        transition: box-shadow 0.2s, transform 0.15s; position: relative;
        font-family: inherit; cursor: pointer; width: 100%;
    }
    .hub-tile:hover { box-shadow: 0 8px 26px rgba(0,0,0,0.09); transform: translateY(-2px); color: inherit; }
    .hub-tile-icon { font-size: 1.7rem; line-height: 1; }
    .hub-tile-label { font-family: 'Goldman', monospace; font-size: 0.68rem; color: #333; letter-spacing: 0.02em; }
    .hub-tile-badge {
        position: absolute; top: 10px; right: 10px; background: #dc2626; color: #fff;
        font-family: 'Goldman', monospace; font-size: 0.55rem; font-weight: 700;
        border-radius: 100px; padding: 2px 7px; min-width: 16px;
    }
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
    <div class="hub-header">
        <div class="hub-avatar">{{ $child->avatar === 'boy' ? '👦' : ($child->avatar === 'girl' ? '👧' : '👤') }}</div>
        <div>
            <div class="hub-name">{{ $child->name }}</div>
            <div class="hub-sub">{{ $child->childSetting?->grade?->name ?? 'კლასი —' }}</div>
        </div>
    </div>

    <div class="hub-grid">
        <a href="{{ route('market.index', $child) }}" class="hub-tile">
            <span class="hub-tile-icon">🛒</span>
            <span class="hub-tile-label">მარკეტი</span>
            @if($pendingMarket)<span class="hub-tile-badge">{{ $pendingMarket }}</span>@endif
        </a>
        <a href="{{ route('child.stats', $child) }}" class="hub-tile">
            <span class="hub-tile-icon">📊</span>
            <span class="hub-tile-label">სტატისტიკა</span>
        </a>
        <a href="{{ route('child.achievements', $child) }}" class="hub-tile">
            <span class="hub-tile-icon">🏆</span>
            <span class="hub-tile-label">მიღწევები</span>
        </a>
        <button type="button" class="hub-tile" onclick="document.getElementById('remindModal').classList.add('open')">
            <span class="hub-tile-icon">🔔</span>
            <span class="hub-tile-label">შეხსენება</span>
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
