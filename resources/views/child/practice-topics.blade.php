@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: transparent !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    .back { display:inline-flex; align-items:center; gap:6px; margin:16px 0 24px; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#2563eb; text-decoration:none; padding:8px 16px; background:white; border-radius:99px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }

    .page-hero {
        width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
        min-height: 140px; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden; margin-bottom: 20px;
        background-image:
            linear-gradient(90deg, rgba(239,246,255,0.94) 0%, rgba(239,246,255,0.78) 45%, rgba(239,246,255,0.08) 68%),
            url('/img/practice-hero.jpg');
        background-size: cover; background-position: right center; background-repeat: no-repeat;
        box-shadow: 0 8px 20px rgba(37,99,235,0.18);
    }
    .page-hero-title { font-family:'Fredoka One',cursive; font-size:1.15rem; color:#1e40af; margin-bottom:4px; }
    .page-hero-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.75rem; color:#2563eb; }

    /* Collapsible top-level cards */
    .collapse-card { background:white; border-radius:18px; margin-bottom:14px; overflow:hidden; box-shadow:0 4px 14px rgba(0,0,0,0.06); }
    .collapse-header { display:flex; align-items:center; justify-content:space-between; padding:16px 18px; cursor:pointer; user-select:none; transition:background 0.15s; }
    .collapse-header:hover { background:#f8fafc; }
    .collapse-title-wrap { display:flex; align-items:center; gap:12px; min-width:0; }
    .collapse-icon-badge { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .collapse-title { font-family:'Fredoka One',cursive; font-size:0.95rem; color:#0c4a6e; }
    .collapse-arrow { font-size:0.85rem; color:#94a3b8; transition:transform 0.2s; flex-shrink:0; }
    .collapse-card.open .collapse-arrow { transform:rotate(90deg); }
    .collapse-body { display:none; padding:0 16px 16px; }
    .collapse-card.open .collapse-body { display:block; }


    /* Nested video accordion */
    .vtopic-card { background:#f8fafc; border-radius:14px; margin-bottom:8px; overflow:hidden; }
    .vtopic-header { display:flex; align-items:center; justify-content:space-between; padding:12px 14px; cursor:pointer; user-select:none; }
    .vtopic-name { font-family:'Fredoka One',cursive; font-size:0.88rem; color:#0c4a6e; }
    .vtopic-count { font-family:'Nunito',sans-serif; font-size:0.66rem; font-weight:800; color:#db2777; background:#fce7f3; border-radius:99px; padding:3px 10px; }
    .vtopic-arrow { font-size:0.75rem; color:#94a3b8; transition:transform 0.2s; }
    .vtopic-card.open .vtopic-arrow { transform:rotate(90deg); }
    .vid-list { padding:0 14px 12px; display:none; }
    .vtopic-card.open .vid-list { display:block; }
    .vid-item { margin-bottom:14px; }
    .vid-embed { width:100%; aspect-ratio:16/9; border-radius:12px; border:none; }
    .vid-title { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.78rem; color:#334155; margin-top:6px; }

    .empty { text-align:center; padding:40px 20px; }
    .empty-icon { font-size:2.5rem; margin-bottom:10px; }
    .empty-txt { font-family:'Fredoka One',cursive; font-size:1rem; color:#94a3b8; }

    .start-practice-card {
        display: flex; align-items: center; gap: 14px; width: 100%; box-sizing: border-box;
        min-height: 96px; margin-bottom: 20px;
        background: linear-gradient(160deg, #eff6ff, #dbeafe);
        border-radius: 18px; padding: 16px 18px; text-decoration: none; color: #1e40af; text-align: left;
        box-shadow: 0 8px 20px rgba(37,99,235,0.15); transition: transform 0.15s;
    }
    .start-practice-card:hover { transform: translateY(-2px); color: #1e40af; }
    .spc-icon { font-size: 2.2rem; flex-shrink: 0; }
    .spc-text { flex: 1; min-width: 0; }
    .spc-title { font-family: 'Fredoka One', cursive; font-size: 1.05rem; margin-bottom: 2px; color: #1e40af; }
    .spc-sub { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.72rem; color: #2563eb; opacity: 0.85; }
    .spc-arrow { font-size: 1.1rem; flex-shrink: 0; opacity: 0.85; }
</style>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>

    <div class="page-hero">
        <div class="page-hero-title">🎯 სავარჯიშოები</div>
        <div class="page-hero-sub">ივარჯიშე დღეს — კითხვები მოვა ყველა თემიდან რიგრიგობით!</div>
    </div>

    {{-- ვარჯიშის დაწყება — ყველა თემა თანმიმდევრობით, აღარ ირჩევა ცალკე თემა --}}
    <a href="{{ route('practice.show', 'auto') }}" class="start-practice-card">
        <span class="spc-icon">🎯</span>
        <div class="spc-text">
            <div class="spc-title">ვარჯიშის დაწყება</div>
            <div class="spc-sub">კითხვები მოვა ყველა თემიდან რიგრიგობით</div>
        </div>
        <span class="spc-arrow">→</span>
    </a>

    {{-- ვიდეო გაკვეთილები თემების მიხედვით --}}
    <div class="collapse-card" id="secVideos">
        <div class="collapse-header" onclick="toggleSection('secVideos')">
            <div class="collapse-title-wrap">
                <span class="collapse-icon-badge" style="background:#fce7f3;color:#db2777;">📹</span>
                <span class="collapse-title">ვიდეო გაკვეთილები თემების მიხედვით</span>
            </div>
            <span class="collapse-arrow">▶</span>
        </div>
        <div class="collapse-body">
            @forelse($videoTopics as $vtopic)
            <div class="vtopic-card" id="vtc{{ $vtopic->id }}">
                <div class="vtopic-header" onclick="toggleVideoTopic({{ $vtopic->id }})">
                    <span class="vtopic-name">{{ $vtopic->name }}</span>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="vtopic-count">{{ $vtopic->videos->count() }} ვიდეო</span>
                        <span class="vtopic-arrow">▶</span>
                    </div>
                </div>
                <div class="vid-list">
                    @foreach($vtopic->videos as $vid)
                    <div class="vid-item">
                        <iframe class="vid-embed" src="{{ $vid->embedUrl() }}"
                            title="{{ $vid->title ?: $vtopic->name }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen loading="lazy"></iframe>
                        @if($vid->title)
                        <div class="vid-title">{{ $vid->title }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="empty">
                <div class="empty-icon">🎬</div>
                <div class="empty-txt">ვიდეოები ჯერ არ დამატებულა</div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleSection(id) {
    document.getElementById(id).classList.toggle('open');
}
function toggleVideoTopic(id) {
    document.getElementById('vtc' + id).classList.toggle('open');
}
</script>
@endsection
