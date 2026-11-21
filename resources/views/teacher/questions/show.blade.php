@extends('layouts.app')
{{-- ერთი კითხვა: სტატუსი, კომენტარი და როგორ ნახავს ბავშვი --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__td = (array) ($question->teacher_data ?? []);
    $__st = ['pending' => ['განხილვაზე', 'y'], 'rejected' => ['დაბრუნებულია', 'r'], 'approved' => ['დადასტურებულია', 'g']][$question->review_status] ?? ['—', ''];
@endphp
<style>
    .tq-show { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 1000px) { .tq-show { grid-template-columns: 1fr; } }
    .tq-kgrid { display: flex; flex-direction: column; gap: 12px; }
    .tq-dl { display: grid; grid-template-columns: auto 1fr; gap: 8px 14px; font-size: 15px; margin: 0; }
    .tq-dl dt { color: #6B625A; font-weight: 700; } .tq-dl dd { margin: 0; color: #2B2622; }
</style>
<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>{{ $question->title ?: 'კითხვა' }}</h1>
            <p><span class="tq-pill {{ $__st[1] }}">{{ $__st[0] }}</span> · {{ $question->topic?->grade?->name }} · {{ $question->topic?->name }} · დონე {{ $question->difficulty }}</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('teacher.questions.index', ['tab' => $question->review_status]) }}" class="tq-btn tq-btn-ghost">← უკან</a>
            @if($question->review_status !== 'approved')
            <a href="{{ route('teacher.questions.edit', $question) }}" class="tq-btn tq-btn-main"><i class="bi bi-pencil"></i> შესწორება</a>
            @endif
        </div>
    </div>

    @if($question->review_comment)
    <div class="tq-flash {{ $question->review_status === 'rejected' ? 'err' : '' }}" style="{{ $question->review_status === 'rejected' ? 'background:#FFF1C2;color:#5A3E00;' : '' }}">
        <i class="bi bi-chat-left-text"></i> <b>ადმინის კომენტარი:</b> {{ $question->review_comment }}
    </div>
    @endif

    <div class="tq-show">
        <section class="tq-card" style="display:flex;flex-direction:column;gap:12px;">
            <h2 style="font-size:18px;font-weight:900;margin:0;">შენი კითხვა</h2>
            <div class="tq-item-text" style="font-size:16px;line-height:1.6;white-space:pre-line;">{{ $__td['text'] ?? $question->template_text }}</div>
            <dl class="tq-dl">
                <dt>სწორი პასუხი</dt><dd><b>{{ $__td['answer'] ?? '—' }}</b></dd>
                @if(!empty($__td['wrong']))<dt>არასწორი</dt><dd>{{ implode(' · ', $__td['wrong']) }}</dd>@endif
                <dt>ტიპი</dt><dd>{{ ($__td['mode'] ?? '') === 'input' ? 'ჩასაწერი' : 'ასარჩევი' }}</dd>
                <dt>რიცხვები</dt><dd>{{ !empty($__td['auto_used']) ? 'ყოველ ჯერზე იცვლება' : 'ფიქსირებული' }}</dd>
                @if(!empty($__td['hint']))<dt>მინიშნება</dt><dd>{{ $__td['hint'] }}</dd>@endif
            </dl>
            @if(!empty($__td['history']))
            <h3 style="font-size:15px;font-weight:900;margin:6px 0 0;">მიმოწერა ადმინთან</h3>
            @include('teacher.partials.history', ['history' => $__td['history']])
            @endif
        </section>
        <section class="tq-kid">
            <span class="tq-kid-lbl">ასე ნახავს ბავშვი</span>
            <div class="tq-kgrid">
                @forelse($samples as $__s)
                    @include('teacher.partials.kid-card', ['s' => $__s, 'showAnswer' => true])
                @empty
                    <div class="tq-kcard">წინასწარი ხედი ვერ შეიქმნა.</div>
                @endforelse
            </div>
            <div class="tq-kmeta">მწვანე — სწორი პასუხი{{ count($samples) > 1 ? ' · ყოველი ბავშვი სხვა რიცხვებს ნახავს' : '' }}</div>
        </section>
    </div>
</div>
@endsection
