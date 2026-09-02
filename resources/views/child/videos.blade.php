@extends('layouts.app')
@section('navTitle', '📹 ვიდეოთეკა')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: #f0f9ff !important; }
    .wrap { max-width: 640px; margin: 0 auto; padding: 28px 16px 80px; }
    .back { display:inline-flex; align-items:center; gap:6px; margin:16px 0 24px; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#db2777; text-decoration:none; padding:8px 16px; background:white; border-radius:99px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    .back:hover { box-shadow:0 4px 12px rgba(0,0,0,0.12); }

    .topic-card { background:white; border-radius:18px; margin-bottom:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.07); }
    .topic-header {
        display:flex; align-items:center; justify-content:space-between;
        padding:16px 18px; cursor:pointer; user-select:none;
        transition:background 0.15s;
    }
    .topic-header:hover { background:#f8fafc; }
    .topic-name { font-family:'Fredoka One',cursive; font-size:1.05rem; color:#0c4a6e; }
    .topic-count { font-family:'Nunito',sans-serif; font-size:0.72rem; font-weight:800; color:#38bdf8; background:#e0f2fe; border-radius:99px; padding:3px 10px; }
    .topic-arrow { font-size:0.8rem; color:#94a3b8; transition:transform 0.2s; }
    .topic-card.open .topic-arrow { transform:rotate(90deg); }

    .vid-list { padding:0 18px 16px; display:none; }
    .topic-card.open .vid-list { display:block; }

    .vid-item { margin-bottom:16px; }
    .vid-embed { width:100%; aspect-ratio:16/9; border-radius:12px; border:none; }
    .vid-title { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.82rem; color:#334155; margin-top:8px; }

    .empty-state { text-align:center; padding:60px 20px; }
    .empty-icon { font-size:3rem; margin-bottom:12px; }
    .empty-txt { font-family:'Fredoka One',cursive; font-size:1.1rem; color:#94a3b8; }
    .empty-sub { font-family:'Nunito',sans-serif; font-weight:700; font-size:0.78rem; color:#cbd5e1; margin-top:6px; }
</style>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>

    @if($topics->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">🎬</div>
        <div class="empty-txt">ვიდეოები ჯერ არ დამატებულა</div>
        <div class="empty-sub">მალე გამოჩნდება შენი კლასის ვიდეოები</div>
    </div>
    @else
    @foreach($topics as $topic)
    <div class="topic-card" id="tc{{ $topic->id }}">
        <div class="topic-header" onclick="toggleTopic({{ $topic->id }})">
            <span class="topic-name">{{ $topic->name }}</span>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="topic-count">{{ $topic->videos->count() }} ვიდეო</span>
                <span class="topic-arrow">▶</span>
            </div>
        </div>
        <div class="vid-list">
            @foreach($topic->videos as $vid)
            <div class="vid-item">
                <iframe class="vid-embed" src="{{ $vid->embedUrl() }}"
                    title="{{ $vid->title ?: $topic->name }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe>
                @if($vid->title)
                <div class="vid-title">{{ $vid->title }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleTopic(id) {
    const card = document.getElementById('tc' + id);
    card.classList.toggle('open');
}
</script>
@endsection
