@extends('layouts.app')
{{-- მასწავლებლის კითხვები: შესასწორებელი / განხილვაზე / დადასტურებული --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__tabs = [
        'rejected' => ['შესასწორებელი', 'bi-arrow-return-left', 'ადმინმა დაგიბრუნა კომენტარით — შეასწორე და ხელახლა გაგზავნე.'],
        'pending'  => ['განხილვაზე', 'bi-hourglass-split', 'ადმინი ამ კითხვებს ჯერ ათვალიერებს.'],
        'approved' => ['დადასტურებული', 'bi-check2-circle', 'ეს კითხვები უკვე ბავშვების ტესტებშია.'],
    ];
    $__types = ['numeric' => 'ასარჩევი', 'text' => 'ასარჩევი', 'numeric_input' => 'ჩასაწერი'];
@endphp
<style>
    .tq-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .tq-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 14px; background: #fff; border: 1.5px solid #ECE9E4; font-weight: 800; color: #4F473F; text-decoration: none; }
    .tq-tab:hover { border-color: #FFC49A; color: #C24A0A; }
    .tq-tab.on { background: #FFF3EA; border-color: #E2590F; color: #C24A0A; }
    .tq-tab em { font-style: normal; background: #F1EFEB; border-radius: 99px; padding: 1px 8px; font-size: 12px; }
    .tq-tab.warn em { background: #E2590F; color: #fff; }
    .tq-list { display: flex; flex-direction: column; gap: 12px; }
    .tq-item { display: flex; flex-direction: column; gap: 10px; }
    .tq-item-top { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .tq-item-text { font-size: 16px; line-height: 1.55; color: #2B2622; white-space: pre-line; }
    .tq-item-meta { font-size: 13px; color: #6B625A; }
    .tq-item-act { display: flex; flex-wrap: wrap; gap: 8px; }
    .tq-cmt { border-radius: 14px; padding: 12px 14px; background: #FFF1C2; color: #5A3E00; font-size: 15px; line-height: 1.5; }
    .tq-cmt b { display: block; font-size: 13px; margin-bottom: 2px; }
    .tq-empty { text-align: center; padding: 40px 16px; display: flex; flex-direction: column; align-items: center; gap: 10px; color: #6B625A; }
    .tq-empty i { font-size: 40px; color: #FFC49A; }
</style>

<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>ჩემი კითხვები</h1>
            <p>{{ $__tabs[$tab][2] }}</p>
        </div>
        <a href="{{ route('teacher.questions.create') }}" class="tq-btn tq-btn-main"><i class="bi bi-plus-lg"></i> ახალი კითხვა</a>
    </div>

    @if(session('success'))<div class="tq-flash" role="status">{{ session('success') }}</div>@endif

    <div class="tq-tabs" role="tablist">
        @foreach($__tabs as $__k => [$__l, $__i])
        <a href="{{ route('teacher.questions.index', ['tab' => $__k]) }}" role="tab" aria-selected="{{ $tab === $__k ? 'true' : 'false' }}" class="tq-tab {{ $tab === $__k ? 'on' : '' }} {{ $__k === 'rejected' && ($counts[$__k] ?? 0) ? 'warn' : '' }}">
            <i class="bi {{ $__i }}"></i>{{ $__l }} <em>{{ $counts[$__k] ?? 0 }}</em>
        </a>
        @endforeach
    </div>

    @if($questions->isEmpty())
        <div class="tq-card tq-empty">
            <i class="bi {{ $__tabs[$tab][1] }}"></i>
            @if($tab === 'rejected')<b>შესასწორებელი კითხვა არ გაქვს</b><span>ყველაფერი რიგზეა!</span>
            @elseif($tab === 'pending')<b>განხილვაზე კითხვა არ გაქვს</b><span>შექმენი ახალი კითხვა — ადმინი გადახედავს და დაადასტურებს.</span>
            @else<b>დადასტურებული კითხვა ჯერ არ გაქვს</b><span>ადმინის დადასტურებული კითხვები აქ გამოჩნდება.</span>@endif
            <a href="{{ route('teacher.questions.create') }}" class="tq-btn tq-btn-soft"><i class="bi bi-plus-lg"></i> ახალი კითხვა</a>
        </div>
    @else
    <div class="tq-list">
        @foreach($questions as $__q)
        @php $__td = (array) ($__q->teacher_data ?? []); @endphp
        <article class="tq-card tq-item">
            <div class="tq-item-top">
                <span class="tq-pill o">{{ $__q->topic?->grade?->name }} · {{ $__q->topic?->name }}</span>
                <span class="tq-pill">დონე {{ $__q->difficulty }}</span>
                <span class="tq-pill">{{ $__types[$__q->answer_type] ?? 'ასარჩევი' }}</span>
                @if($__q->is_olympiad)<span class="tq-pill y">ოლიმპიადა</span>@endif
                @if(!empty($__td['auto_used']))<span class="tq-pill g"><i class="bi bi-shuffle"></i> რიცხვები იცვლება</span>@endif
            </div>
            <div class="tq-item-text">{{ \Illuminate\Support\Str::limit($__td['text'] ?? $__q->template_text, 300) }}</div>
            <div class="tq-item-meta">
                სწორი პასუხი: <b>{{ $__td['answer'] ?? '—' }}</b>
                · {{ $tab === 'pending' ? 'გაგზავნილია ' . optional($__q->submitted_at)->format('d.m.Y H:i') : ($__q->reviewed_at ? ($tab === 'approved' ? 'დადასტურდა ' : 'დაბრუნდა ') . $__q->reviewed_at->format('d.m.Y') : '') }}
            </div>
            @if($tab === 'rejected' && $__q->review_comment)
            <div class="tq-cmt"><b><i class="bi bi-chat-left-text"></i> ადმინის კომენტარი{{ $__q->reviewer ? ' (' . $__q->reviewer->name . ')' : '' }}:</b>{{ $__q->review_comment }}</div>
            @elseif($tab === 'approved' && $__q->review_comment)
            <div class="tq-cmt" style="background:#DFF5E3;color:#14612F;"><b>ადმინის შენიშვნა:</b>{{ $__q->review_comment }}</div>
            @endif
            <div class="tq-item-act">
                <a href="{{ route('teacher.questions.show', $__q) }}" class="tq-btn tq-btn-ghost tq-btn-sm"><i class="bi bi-eye"></i> როგორ ნახავს ბავშვი</a>
                @if($tab !== 'approved')
                <a href="{{ route('teacher.questions.edit', $__q) }}" class="tq-btn {{ $tab === 'rejected' ? 'tq-btn-main' : 'tq-btn-soft' }} tq-btn-sm"><i class="bi bi-pencil"></i> {{ $tab === 'rejected' ? 'შესწორება' : 'რედაქტირება' }}</a>
                <form method="POST" action="{{ route('teacher.questions.destroy', $__q) }}" onsubmit="return confirm('წავშალო ეს კითხვა?');" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="tq-btn tq-btn-danger tq-btn-sm"><i class="bi bi-trash"></i> წაშლა</button>
                </form>
                @endif
            </div>
        </article>
        @endforeach
    </div>
    @if($questions->hasPages())<div>{{ $questions->links('admin.pagination') }}</div>@endif
    @endif
</div>
@endsection
