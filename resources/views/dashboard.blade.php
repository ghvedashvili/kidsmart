@extends('layouts.app')

@section('content')
<style>
    body {
        background: #f5f5f5 !important;
        padding: 0 !important;
        overflow: hidden;
        min-height: 100dvh;
        min-height: 100vh;
    }
    .dash-hero {
        min-height: 100dvh;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 32px 24px;
        gap: 28px;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.1) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }
    @keyframes gridMove {
        0%   { transform: translate(0,0); }
        100% { transform: translate(28px,28px); }
    }
    .dash-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        width: 100%;
        max-width: 420px;
        text-align: center;
    }
    .dash-greeting {
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 4vw, 1.5rem);
        color: #111;
        letter-spacing: 0.06em;
    }
    .notif-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 32px;
        font-family: 'Goldman', monospace;
        font-size: 0.82rem;
        letter-spacing: 0.08em;
        color: #555;
        background: transparent;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s;
    }
    .notif-btn:hover { color: #111; border-color: #888; }
    .notif-btn.on { color: #111; border-color: #111; }
</style>

<div class="dash-hero">
    <div class="dash-inner">
        <div class="dash-greeting">გამარჯობა, {{ auth()->user()->name }}</div>

        @if(auth()->user()->role === 'parent' && auth()->user()->parent_code)
        <div style="text-align:center;">
            <div style="font-family:'Goldman',monospace;font-size:0.65rem;color:#aaa;letter-spacing:0.12em;margin-bottom:8px;">შვილის კოდი</div>
            <div style="font-family:'Goldman',monospace;font-size:clamp(1.4rem,5vw,2rem);color:#111;letter-spacing:0.25em;border:1px solid #ccc;border-radius:6px;padding:10px 24px;display:inline-block;">
                {{ auth()->user()->parent_code }}
            </div>
        </div>
        @endif

        <button class="notif-btn" id="notifBtn" onclick="toggleNotifications()">
            <i class="bi bi-bell" id="notifIcon"></i>
            <span id="notifText">შეტყობინებების ჩართვა!</span>
        </button>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.panel') }}" style="font-family:'Goldman',monospace;font-size:0.75rem;color:#888;letter-spacing:0.06em;text-decoration:none;">
            admin →
        </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="background:none;border:none;font-family:'Goldman',monospace;font-size:0.7rem;color:#bbb;letter-spacing:0.06em;cursor:pointer;">
                გასვლა
            </button>
        </form>
    </div>
</div>
@endsection
