@extends('layouts.app')
@section('content')
@php
    $__s     = auth()->user()->childSetting;
    $__topics = $__s?->grade_id
        ? \App\Models\Topic::where('grade_id', $__s->grade_id)->where('is_active', true)
            ->whereHas('questionTemplates')->withCount('videos')->orderBy('sequence')->orderBy('name')->get()
        : collect();
    $__stats = $__topics->isEmpty() ? collect() : \App\Models\PracticeAnswerLog::where('child_id', auth()->id())
        ->whereIn('topic_id', $__topics->pluck('id'))
        ->selectRaw('topic_id, COUNT(*) as n, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as ok')
        ->groupBy('topic_id')->get()->keyBy('topic_id');
    $__pal = [
        ['#E2590F', '#FFE3CC', '+'], ['#F07A2E', '#FFF3EA', '×'], ['#E2590F', '#FFF3EA', '?'],
        ['#1E8A4C', '#DFF5E3', '#'], ['#C2185B', '#FFF3EA', '÷'], ['#C98A00', '#FFF1C2', '='],
    ];
@endphp
<style>
    .lp { display: flex; flex-direction: column; gap: 16px; max-width: 760px; width: 100%; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; }
    .lp-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
    .lp-head small { font-size: 13px; color: #6B625A; }
    .lp-title { margin: 4px 0 0; font-size: 46px; line-height: .95; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .lp-grade { font-size: 13px; font-weight: 700; background: #fff; border: 1px solid #ECE9E4; border-radius: 999px; padding: 8px 14px; white-space: nowrap; }
    .lp-start { text-decoration: none; color: #fff; background: #C24A0A; border-radius: 22px; padding: 18px; display: flex; align-items: center; gap: 14px; }
    .lp-start:hover { color: #fff; background: #A33E08; }
    .lp-start-ic { width: 54px; height: 54px; border-radius: 50%; background: #E2590F; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
    .lp-start-t { flex: 1; display: flex; flex-direction: column; gap: 3px; }
    .lp-start-t b { font-size: 17px; font-weight: 800; }
    .lp-start-t span { font-size: 12px; color: #FFE3CC; line-height: 1.4; }
    .lp-h { font-size: 18px; font-weight: 800; margin: 4px 0 -4px; }
    .lp-list { display: flex; flex-direction: column; gap: 10px; }
    .lp-topic { text-decoration: none; color: #2B2622; background: #fff; border-radius: 20px; padding: 12px 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 0 #ECE9E4; }
    .lp-topic:hover { color: #2B2622; background: #FFFAF5; }
    .lp-sym { width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; }
    .lp-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
    .lp-top { display: flex; justify-content: space-between; gap: 8px; }
    .lp-name { font-size: 14px; font-weight: 700; }
    .lp-pct { font-size: 12px; font-weight: 700; color: #6B625A; flex-shrink: 0; }
    .lp-bar { height: 8px; border-radius: 4px; background: #F1EFEB; display: block; overflow: hidden; }
    .lp-bar span { display: block; height: 8px; border-radius: 4px; }
    .lp-sub { font-size: 11px; color: #6B625A; }
    .lp-empty { background: #fff; border-radius: 20px; padding: 24px; text-align: center; color: #6B625A; font-size: 14px; }
</style>

<div class="lp">
    <div class="ks-cp-hero blue">
        <span class="ks-cp-hero-ic" aria-hidden="true">🎯</span>
        <span class="ks-cp-eyebrow">აირჩიე თემა და ივარჯიშე</span>
        <h1 class="ks-cp-title">ვარჯიში</h1>
        @if($__s?->grade?->name)<span class="ks-cp-sub">{{ $__s->grade->name }}</span>@endif
    </div>

    <a href="{{ route('practice.show', 'auto') }}" class="lp-start">
        <span class="lp-start-ic"><i class="bi bi-play-fill"></i></span>
        <span class="lp-start-t"><b>ვარჯიშის დაწყება</b><span>კითხვები ყველა თემიდან რიგრიგობით, შენს დონეზე</span></span>
        <i class="bi bi-arrow-right" style="font-size:22px;"></i>
    </a>

    <div class="lp-h">ივარჯიშე თემების მიხედვით</div>
    <div class="lp-list">
        @forelse($__topics as $__t)
        @php
            [$__c, $__bg, $__sym] = $__pal[$loop->index % count($__pal)];
            $__st  = $__stats->get($__t->id);
            $__n   = (int) ($__st->n ?? 0);
            $__pct = $__n > 0 ? (int) round($__st->ok / $__n * 100) : null;
        @endphp
        <a href="{{ route('practice.show', $__t->id) }}" class="lp-topic">
            <span class="lp-sym" style="background: {{ $__bg }}; color: {{ $__c }}">{{ $__sym }}</span>
            <span class="lp-body">
                <span class="lp-top"><span class="lp-name">{{ $__t->name }}</span><span class="lp-pct">{{ $__pct !== null ? $__pct . '%' : '' }}</span></span>
                <span class="lp-bar"><span style="width: {{ max(3, $__pct ?? 0) }}%; background: {{ $__c }}"></span></span>
                <span class="lp-sub">
                    {{ $__n > 0 ? $__n . ' ამოცანა' : 'ახალი თემა · დაიწყე!' }}{{ $__t->videos_count ? ' · ვიდეო გაკვეთილი' : '' }}
                </span>
            </span>
        </a>
        @empty
        <div class="lp-empty">თემები მალე გამოჩნდება — მშობელმა ჯერ კლასი უნდა აგირჩიოს.</div>
        @endforelse
    </div>

    {{-- ვიდეო გაკვეთილები თემების მიხედვით --}}
    <div class="lp-h" id="secVideos">ვიდეო გაკვეთილები</div>
    <div class="ks-cp-list">
        @forelse($videoTopics as $vtopic)
        <div class="ks-cp-acc" id="vtc{{ $vtopic->id }}">
            <div class="ks-cp-acc-h" onclick="document.getElementById('vtc{{ $vtopic->id }}').classList.toggle('open')">
                <span class="ks-cp-row-ic" style="background:#FFF1C2;color:#C98A00;"><i class="bi bi-play-fill"></i></span>
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
        <div class="lp-empty">ვიდეოები ჯერ არ დამატებულა</div>
        @endforelse
    </div>
</div>
@endsection
