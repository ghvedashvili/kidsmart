@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero purple">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎬</span>
        <span class="ks-cp-eyebrow">ისწავლე ვიდეოებით</span>
        <h1 class="ks-cp-title">ვიდეოები</h1>
        <span class="ks-cp-sub">მოკლე გაკვეთილები შენი კლასის თემებზე</span>
    </div>

    @if($topics->isEmpty())
    <div class="ks-cp-empty">
        <div class="ks-cp-empty-ic">🎬</div>
        <div class="ks-cp-empty-t">ვიდეოები ჯერ არ დამატებულა</div>
        <div class="ks-cp-empty-s">მალე გამოჩნდება შენი კლასის ვიდეოები</div>
    </div>
    @else
    <div class="ks-cp-list">
        @foreach($topics as $topic)
        <div class="ks-cp-acc" id="tc{{ $topic->id }}">
            <div class="ks-cp-acc-h" onclick="toggleTopic({{ $topic->id }})">
                <span class="ks-cp-row-ic" style="background:#FFF3EA;">▶</span>
                <span class="ks-cp-acc-t">{{ $topic->name }}</span>
                <span class="ks-cp-chip">{{ $topic->videos->count() }} ვიდეო</span>
                <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
            </div>
            <div class="ks-cp-acc-b">
                <div class="ks-cp-vids">
                    @foreach($topic->videos as $vid)
                    <div class="ks-cp-vid">
                        <iframe src="{{ $vid->embedUrl() }}"
                            title="{{ $vid->title ?: $topic->name }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen loading="lazy"></iframe>
                        @if($vid->title)
                        <div class="ks-cp-vid-t">{{ $vid->title }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function toggleTopic(id) {
    const card = document.getElementById('tc' + id);
    card.classList.toggle('open');
}
</script>
@endsection
