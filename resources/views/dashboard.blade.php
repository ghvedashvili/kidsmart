@extends('layouts.app')

@section('content')
<style>
    body {
        background: #f5f5f5 !important;
        padding: 0 !important;
        overflow-x: hidden;
        min-height: 100dvh;
    }
    .dash-hero {
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 40px 24px 60px;
        gap: 28px;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.07) 1px, transparent 1px);
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
        max-width: 480px;
        text-align: center;
    }
    .dash-greeting {
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 4vw, 1.4rem);
        color: #111;
        letter-spacing: 0.06em;
    }

    .pcode-wrap { text-align: center; width: 100%; }
    .pcode-label {
        font-family: 'Goldman', monospace;
        font-size: 0.62rem; color: #aaa;
        letter-spacing: 0.14em; margin-bottom: 8px; text-transform: uppercase;
    }
    .pcode {
        font-family: 'Goldman', monospace;
        font-size: clamp(1.6rem, 6vw, 2.2rem);
        color: #111; letter-spacing: 0.3em;
        border: 1px solid #ddd; border-radius: 8px;
        padding: 12px 28px; display: inline-block;
        cursor: pointer; transition: border-color 0.2s;
    }
    .pcode:hover { border-color: #aaa; }
    .pcode-copy { font-family: 'Goldman', monospace; font-size: 0.6rem; color: #bbb; margin-top: 6px; letter-spacing: 0.08em; }

    .children-section { width: 100%; }
    .section-label {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; text-align: left;
    }
    .child-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 16px 18px; margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        text-decoration: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .child-card:hover { border-color: #bbb; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .child-info { flex: 1; text-align: left; }
    .child-name { font-family: 'Goldman', monospace; font-size: 0.88rem; color: #111; letter-spacing: 0.04em; margin-bottom: 5px; }
    .child-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .ctag {
        font-family: 'Goldman', monospace; font-size: 0.6rem; color: #ccc;
        border: 1px solid #ebebeb; border-radius: 3px; padding: 2px 7px; letter-spacing: 0.04em;
    }
    .ctag.set { color: #555; border-color: #ccc; }
    .child-arrow { color: #ccc; font-size: 0.9rem; }
    .no-children {
        font-family: 'Goldman', monospace; font-size: 0.72rem; color: #ccc;
        text-align: center; padding: 20px;
        border: 1px dashed #e0e0e0; border-radius: 8px; letter-spacing: 0.06em;
    }

    .notif-btn {
        display: inline-flex; align-items: center; gap: 10px; padding: 11px 28px;
        font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em;
        color: #888; background: transparent; border: 1px solid #ddd; border-radius: 4px;
        cursor: pointer; transition: color 0.2s, border-color 0.2s;
    }
    .notif-btn:hover { color: #333; border-color: #aaa; }
    .notif-btn.on { color: #111; border-color: #111; }
    .flash { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #2ecc71; letter-spacing: 0.06em; }
    .flash-err { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #e74c3c; letter-spacing: 0.06em; }
    .test-btn {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 16px 36px; border-radius: 10px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white; font-family: 'Goldman', monospace;
        font-size: 0.88rem; letter-spacing: 0.08em;
        text-decoration: none; transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(79,70,229,0.3);
    }
    .test-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,0.4); }
    .test-pending {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 18px 20px; width: 100%; text-align: center;
    }
    .test-pending-label { font-family: 'Goldman', monospace; font-size: 0.65rem; color: #bbb; letter-spacing: 0.1em; margin-bottom: 10px; }
    .test-pending-score { font-family: 'Goldman', monospace; font-size: 1.4rem; color: #111; letter-spacing: 0.06em; }
    .test-pending-sub { font-family: 'Goldman', monospace; font-size: 0.62rem; color: #ccc; margin-top: 4px; }
</style>

<div class="dash-hero">
    <div class="dash-inner">

        @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
        @endif
        @if(session('test_error'))
        <div class="flash-err">{{ session('test_error') }}</div>
        @endif

        <div class="dash-greeting">გამარჯობა, {{ auth()->user()->name }}</div>

        {{-- მშობლის ხედი --}}
        @if(auth()->user()->role === 'parent')
        @php $children = auth()->user()->children()->with(['childSetting.grade','themes','topics'])->get(); @endphp

        @if(auth()->user()->parent_code)
        <div class="pcode-wrap">
            <div class="pcode-label">შვილის კოდი</div>
            <div class="pcode" onclick="copyCode(this)">{{ auth()->user()->parent_code }}</div>
            <div class="pcode-copy" id="copyHint">დააჭირე დასაკოპირებლად</div>
        </div>
        @endif

        <div class="children-section">
            <div class="section-label">შვილები · {{ $children->count() }}</div>
            @forelse($children as $child)
            @php $s = $child->childSetting; @endphp
            <a href="{{ route('child.settings.edit', $child) }}" class="child-card">
                <div class="child-info">
                    <div class="child-name">{{ $child->name }}</div>
                    <div class="child-tags">
                        @if($s?->grade)
                            <span class="ctag set">{{ $s->grade->name }}</span>
                        @else
                            <span class="ctag">კლასი —</span>
                        @endif
                        @if($s)
                            <span class="ctag set">დონე {{ $s->difficulty }}</span>
                            <span class="ctag set">კვ. {{ $s->tests_per_week }}×</span>
                        @endif
                        @foreach($child->themes->take(2) as $theme)
                            <span class="ctag set">{{ $theme->icon }} {{ $theme->name }}</span>
                        @endforeach
                        @if($child->themes->count() > 2)
                            <span class="ctag">+{{ $child->themes->count() - 2 }}</span>
                        @endif
                    </div>
                </div>
                <span class="child-arrow">→</span>
            </a>
            @empty
            <div class="no-children">
                ბავშვი ჯერ არ დარეგისტრირებულა<br>
                <span style="font-size:0.62rem;color:#ccc;margin-top:4px;display:block;">კოდი გაუზიარე შვილს</span>
            </div>
            @endforelse
        </div>
        @endif

        {{-- ბავშვის ხედი --}}
        @if(auth()->user()->role === 'child')
        @php
            $activeTest    = auth()->user()->tests()->whereNull('completed_at')->latest()->first();
            $lastCompleted = auth()->user()->tests()->whereNotNull('completed_at')->latest()->first();
        @endphp

        @if($activeTest)
        <a href="{{ route('test.show', $activeTest) }}" class="test-btn">
            📝 ტესტი გელოდება →
        </a>
        @else
        <a href="{{ route('test.start') }}" class="test-btn">
            ▶ ტესტის დაწყება
        </a>
        @endif

        @if($lastCompleted)
        <div class="test-pending">
            <div class="test-pending-label">ბოლო ტესტი</div>
            <div class="test-pending-score">
                {{ $lastCompleted->correct_count }} / {{ $lastCompleted->total_questions }}
                @php $pct = round($lastCompleted->correct_count / $lastCompleted->total_questions * 100); @endphp
                <span style="font-size:0.8rem;color:#bbb;"> · {{ $pct }}%</span>
            </div>
            <div class="test-pending-sub">{{ $lastCompleted->completed_at->diffForHumans() }}</div>
        </div>
        @endif
        @endif

        <button class="notif-btn" id="notifBtn" onclick="toggleNotifications()">
            <i class="bi bi-bell" id="notifIcon"></i>
            <span id="notifText">შეტყობინებების ჩართვა!</span>
        </button>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.panel') }}" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;">
            admin →
        </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="background:none;border:none;font-family:'Goldman',monospace;font-size:0.68rem;color:#ccc;letter-spacing:0.06em;cursor:pointer;">
                გასვლა
            </button>
        </form>

    </div>
</div>

<script>
function copyCode(el) {
    navigator.clipboard.writeText(el.textContent.trim()).then(() => {
        document.getElementById('copyHint').textContent = '✓ დაკოპირდა!';
        setTimeout(() => document.getElementById('copyHint').textContent = 'დააჭირე დასაკოპირებლად', 2000);
    });
}
</script>
@endsection
