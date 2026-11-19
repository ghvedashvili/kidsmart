@extends('layouts.app')
@section('content')
<div class="ks-cp">
    <div class="ks-cp-hero blue">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎯</span>
        <span class="ks-cp-eyebrow">ივარჯიშე ყოველდღე</span>
        <h1 class="ks-cp-title">სწავლა</h1>
        <span class="ks-cp-sub">კითხვები მოვა ყველა თემიდან რიგრიგობით</span>
    </div>

    {{-- ვარჯიშის დაწყება — ყველა თემა თანმიმდევრობით, აღარ ირჩევა ცალკე თემა --}}
    <a href="{{ route('practice.show', 'auto') }}" class="ks-market" style="padding:22px 24px;">
        <i class="bi bi-play-circle-fill" style="font-size:34px;"></i>
        <span class="ks-market-t"><b style="font-size:19px;">ვარჯიშის დაწყება</b><span>კითხვები ყველა თემიდან, შენს დონეზე</span></span>
        <i class="bi bi-arrow-right" style="font-size:22px;color:#fff;"></i>
    </a>

    {{-- ვიდეო გაკვეთილები თემების მიხედვით --}}
    <div class="ks-cp-acc open" id="secVideos">
        <div class="ks-cp-acc-h" onclick="toggleSection('secVideos')">
            <span class="ks-cp-row-ic" style="background:#FCE4EC;">📹</span>
            <span class="ks-cp-acc-t">ვიდეო გაკვეთილები თემების მიხედვით</span>
            <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
        </div>
        <div class="ks-cp-acc-b">
            <div class="ks-cp-list">
            @forelse($videoTopics as $vtopic)
            <div class="ks-cp-acc" id="vtc{{ $vtopic->id }}" style="background:#FFF8F1;box-shadow:none;">
                <div class="ks-cp-acc-h" onclick="toggleVideoTopic({{ $vtopic->id }})">
                    <span class="ks-cp-acc-t" style="font-size:15px;">{{ $vtopic->name }}</span>
                    <span class="ks-cp-chip">{{ $vtopic->videos->count() }} ვიდეო</span>
                    <i class="bi bi-chevron-right ks-cp-acc-arrow"></i>
                </div>
                <div class="ks-cp-acc-b">
                    <div class="ks-cp-vids">
                    @foreach($vtopic->videos as $vid)
                    <div class="ks-cp-vid">
                        <iframe src="{{ $vid->embedUrl() }}"
                            title="{{ $vid->title ?: $vtopic->name }}"
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
            @empty
            <div class="ks-cp-empty" style="box-shadow:none;background:#FFF8F1;">
                <div class="ks-cp-empty-ic">🎬</div>
                <div class="ks-cp-empty-t">ვიდეოები ჯერ არ დამატებულა</div>
            </div>
            @endforelse
            </div>
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
