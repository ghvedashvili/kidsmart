@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
    body { background: #f0f9ff !important; }
    .wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
    .topbar { display:flex; align-items:center; margin-bottom:24px; }
    .back { font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#0284c7; text-decoration:none; padding:6px 16px; background:white; border-radius:99px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    .page-title { font-family:'Fredoka One',cursive; font-size:1.6rem; color:#0c4a6e; margin-bottom:4px; }
    .page-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.78rem; color:#94a3b8; margin-bottom:28px; }
    .section-lbl { font-family:'Nunito',sans-serif; font-weight:900; font-size:0.65rem; color:#94a3b8; letter-spacing:0.12em; text-transform:uppercase; margin-bottom:10px; }

    .card {
        display:flex; align-items:center; justify-content:space-between;
        background:white; border-radius:18px; padding:16px 18px; margin-bottom:10px;
        text-decoration:none; box-shadow:0 4px 14px rgba(0,0,0,0.06);
        transition:box-shadow 0.15s;
    }
    .card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.10); }
    .card-left { display:flex; align-items:center; gap:12px; }
    .card-icon { font-size:1.6rem; }
    .card-name { font-family:'Fredoka One',cursive; font-size:1rem; color:#0c4a6e; }
    .card-sub { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.62rem; color:#94a3b8; margin-top:1px; }
    .card-arrow { font-size:1rem; color:#cbd5e1; }

    .level-pill { font-family:'Nunito',sans-serif; font-weight:900; font-size:0.65rem; padding:3px 10px; border-radius:99px; }
    .lp-1 { background:#dcfce7; color:#15803d; }
    .lp-2 { background:#fef9c3; color:#ca8a04; }
    .lp-3 { background:#fed7aa; color:#c2410c; }
    .lp-4 { background:#fce7f3; color:#db2777; }
    .lp-5 { background:#ede9fe; color:#7c3aed; }

    .empty { text-align:center; padding:40px 20px; }
    .empty-icon { font-size:2.5rem; margin-bottom:10px; }
    .empty-txt { font-family:'Fredoka One',cursive; font-size:1rem; color:#94a3b8; }
</style>

<div class="wrap">
    <div class="topbar">
        <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>
    </div>

    <div class="page-title">🎯 სავარჯიშოები</div>
    <div class="page-sub">აირჩიე თემა და ივარჯიშე</div>

    {{-- Topics --}}
    <div class="section-lbl">თემები</div>
    @forelse($topics as $topic)
    @php $sess = $sessions->get($topic->id); @endphp
    <a href="{{ route('practice.show', $topic->id) }}" class="card">
        <div class="card-left">
            <div class="card-icon">📘</div>
            <div>
                <div class="card-name">{{ $topic->name }}</div>
                <div class="card-sub">
                    {{ $sess ? 'დონე ' . $sess->level . ' · ' . $sess->total_correct . '/' . $sess->total_answered . ' სწორი' : 'ახალი' }}
                </div>
            </div>
        </div>
        @if($sess)
        <span class="level-pill lp-{{ $sess->level }}">L{{ $sess->level }}</span>
        @else
        <span class="card-arrow">›</span>
        @endif
    </a>
    @empty
    <div class="empty">
        <div class="empty-icon">📚</div>
        <div class="empty-txt">თემები ჯერ არ დამატებულა</div>
    </div>
    @endforelse
</div>
@endsection
