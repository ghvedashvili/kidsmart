@extends('layouts.app')
{{-- მასწავლებლების კითხვების განხილვა: დადასტურება ან დაბრუნება კომენტარით --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__types = ['numeric' => 'ასარჩევი (რიცხვი)', 'text' => 'ასარჩევი (ტექსტი)', 'numeric_input' => 'ჩასაწერი'];
    $__special = ['pyramid' => 'პირამიდა', 'code' => 'კოდი', 'crossword' => 'კროსვორდი', 'scale' => 'სასწორი', 'data_table' => 'ცხრილი'];
    $__isFlag = $status === 'flagged';
@endphp
<style>
    .rv-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .rv-item { display: flex; flex-direction: column; gap: 14px; }
    .rv-top { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .rv-who { font-weight: 800; color: #2B2622; }
    .rv-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); gap: 14px; }
    @media (max-width: 1100px) { .rv-grid { grid-template-columns: 1fr; } }
    .rv-orig { background: #F7F6F3; border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 14px; }
    .rv-orig .t { font-size: 16px; line-height: 1.55; white-space: pre-line; color: #2B2622; }
    .rv-kids { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 10px; }
    .rv-kids .tq-kcard { padding: 14px 12px 12px; }
    .rv-kids .tq-ktext { font-size: 14px; }
    .rv-kids .tq-kopt { min-height: 40px; font-size: 15px; border-width: 2px; }
    .rv-tech { font-size: 12px; color: #6B625A; font-family: ui-monospace, Menlo, Consolas, monospace; background: #fff; border: 1px dashed #ECE9E4; border-radius: 10px; padding: 8px 10px; word-break: break-word; }
    .rv-notes { margin: 0; padding-left: 18px; font-size: 13px; color: #6B4A00; }
    .rv-act { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: start; border-top: 1px solid #ECE9E4; padding-top: 14px; }
    @media (max-width: 700px) { .rv-act { grid-template-columns: 1fr; } }
    .rv-act textarea { width: 100%; min-height: 70px; border: 2px solid #ECE9E4; border-radius: 14px; padding: 10px 12px; font-size: 15px; font-family: inherit; resize: vertical; }
    .rv-act textarea:focus { outline: none; border-color: #E2590F; box-shadow: 0 0 0 4px rgba(226, 89, 15, .12); }
    .rv-btns { display: flex; flex-direction: column; gap: 8px; min-width: 210px; }
</style>

<div class="tq-page">
    <header class="ks-page-head">
        <div class="ks-page-title">
            <span class="ks-eyebrow">მასწავლებლების კითხვები</span>
            <h1 class="ks-display ks-h1">განსახილველი კითხვები</h1>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="ks-btn ks-btn-ghost">← ყველა კითხვა</a>
    </header>

    @if(session('success'))<div class="tq-flash" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="tq-flash err" role="alert">{{ $errors->first() }}</div>@endif

    <div class="rv-tabs">
        <a href="{{ route('admin.questions.review.index') }}" class="tq-btn {{ $status === 'pending' ? 'tq-btn-main' : 'tq-btn-ghost' }} tq-btn-sm">განხილვაზე · {{ $counts['pending'] }}</a>
        <a href="{{ route('admin.questions.review.index', ['status' => 'rejected']) }}" class="tq-btn {{ $status === 'rejected' ? 'tq-btn-main' : 'tq-btn-ghost' }} tq-btn-sm">დაბრუნებული (მასწავლებელთან) · {{ $counts['rejected'] }}</a>
        <a href="{{ route('admin.questions.review.index', ['status' => 'flagged']) }}" class="tq-btn {{ $__isFlag ? 'tq-btn-main' : 'tq-btn-ghost' }} tq-btn-sm"><i class="bi bi-flag"></i> შეცდომის შეტყობინებები · {{ $counts['flagged'] }}</a>
    </div>

    @forelse($questions as $__q)
    @php $__td = (array) ($__q->teacher_data ?? []); @endphp
    <article class="tq-card rv-item" id="rv-{{ $__q->id }}">
        <div class="rv-top">
            @if($__isFlag)
            <span class="rv-who"><i class="bi bi-flag-fill" style="color:#A12626;"></i> {{ $__q->flagger?->name ?? ($__td['flag']['name'] ?? 'მასწავლებელი') }}</span>
            <span class="tq-pill">{{ optional($__q->flagged_at)->format('d.m.Y H:i') }}</span>
            <span class="tq-pill r">გამორთულია ბავშვებთან</span>
            <span class="tq-pill">#{{ $__q->id }}{{ $__q->title ? ' · ' . $__q->title : '' }}</span>
            @else
            <span class="rv-who"><i class="bi bi-person-badge"></i> {{ $__q->creator?->name ?? 'უცნობი' }}</span>
            <span class="tq-pill">{{ optional($__q->submitted_at)->format('d.m.Y H:i') }}</span>
            @endif
            <span class="tq-pill o">{{ $__q->topic?->grade?->name }} · {{ $__q->topic?->name }}</span>
            <span class="tq-pill">დონე {{ $__q->difficulty }}</span>
            <span class="tq-pill">{{ $__types[$__q->answer_type] ?? $__q->answer_type }}</span>
            @if($__q->is_olympiad)<span class="tq-pill y">ოლიმპიადა</span>@endif
            @unless($__isFlag)<span class="tq-pill {{ !empty($__td['auto_used']) ? 'g' : '' }}">{{ !empty($__td['auto_used']) ? 'რიცხვები იცვლება' : 'ფიქსირებული' }}</span>@endunless
        </div>

        @if($__isFlag)
        <div class="tq-flash" style="background:#FDE2E2;color:#7A1C1C;font-weight:600;"><i class="bi bi-flag"></i> <b>რა არის შესასწორებელი:</b> {{ $__td['flag']['comment'] ?? '—' }}</div>
        @php $__more = array_values(array_filter((array) ($__td['history'] ?? []), fn ($h) => is_array($h) && ($h['event'] ?? '') === 'comment')); @endphp
        @if($__more)
        <div>@include('teacher.partials.history', ['history' => $__more])</div>
        @endif
        @endif

        @if(!$__isFlag && !empty($__td['teacher_comment']))
        <div class="tq-flash" style="background:#FFF3EA;color:#7A3306;font-weight:600;"><i class="bi bi-chat-left-quote"></i> <b>მასწავლებლის კომენტარი:</b> {{ $__td['teacher_comment'] }}</div>
        @endif
        @if($__isFlag)
        @elseif(count((array) ($__td['history'] ?? [])) > 1)
        <details>
            <summary style="cursor:pointer;font-size:13px;font-weight:800;color:#6B625A;">მიმოწერის ისტორია ({{ count($__td['history']) }})</summary>
            <div style="margin-top:8px;">@include('teacher.partials.history', ['history' => $__td['history']])</div>
        </details>
        @elseif(!empty($__td['previous_comment']))
        <div class="tq-flash" style="background:#F1EFEB;color:#4F473F;font-weight:600;"><b>წინა დაბრუნების კომენტარი:</b> {{ $__td['previous_comment'] }}</div>
        @endif
        @if($status === 'rejected' && $__q->review_comment)
        <div class="tq-flash" style="background:#FFF1C2;color:#5A3E00;font-weight:600;"><b>შენი კომენტარი:</b> {{ $__q->review_comment }}</div>
        @endif

        <div class="rv-grid">
            <div class="rv-orig">
                <b style="font-size:13px;color:#6B625A;">{{ $__isFlag ? 'კითხვის შაბლონი' : 'მასწავლებლის კითხვა' }}</b>
                <div class="t">{{ $__td['text'] ?? $__q->template_text }}</div>
                <div>სწორი: <b>{{ $__td['answer'] ?? '—' }}</b>@if(!empty($__td['wrong'])) · არასწორი: {{ implode(' · ', $__td['wrong']) }}@endif</div>
                @if(!empty($__td['hint']))<div>მინიშნება: {{ $__td['hint'] }}</div>@endif
                <div class="rv-tech">შაბლონი: {{ $__q->template_text }}<br>ფორმულა: {{ $__q->correct_formula }}</div>
                @if(!empty($__td['notes']))
                <ul class="rv-notes">@foreach(array_slice($__td['notes'], 0, 5) as $__n)<li>{{ $__n }}</li>@endforeach</ul>
                @endif
            </div>
            <div class="tq-kid" style="padding-top:20px;">
                <span class="tq-kid-lbl">ასე ნახავს ბავშვი</span>
                <div class="rv-kids">
                    @forelse($samples[$__q->id] ?? [] as $__s)
                        @if(isset($__special[$__q->question_type ?? '']))
                        @include('teacher.partials.interactive-card', ['q' => $__s])
                        @else
                        @include('teacher.partials.kid-card', ['s' => $__s, 'showAnswer' => true])
                        @endif
                    @empty
                        <div class="tq-kcard" style="color:#A12626;font-weight:700;">⚠ კითხვა ვერ გენერირდა — გახსენი რედაქტორში.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($status === 'pending')
        <div class="rv-act">
            <form method="POST" action="{{ route('admin.questions.review.reject', $__q) }}" id="rvRej{{ $__q->id }}" style="margin:0;">
                @csrf
                <label for="rvC{{ $__q->id }}" style="font-size:13px;font-weight:800;color:#4F473F;display:block;margin-bottom:6px;">კომენტარი მასწავლებლისთვის <span style="font-weight:600;color:#6B625A;">(სავალდებულოა დაბრუნებისას)</span></label>
                <textarea id="rvC{{ $__q->id }}" name="comment" maxlength="1000" placeholder="მაგ.: პასუხი არასწორია — 15-ს 6 რომ გამოაკლო, 9 გამოვა."></textarea>
            </form>
            <div class="rv-btns">
                <form method="POST" action="{{ route('admin.questions.review.approve', $__q) }}" style="margin:0;" onsubmit="this.querySelector('[name=comment]').value=document.getElementById('rvC{{ $__q->id }}').value;">
                    @csrf
                    <input type="hidden" name="comment">
                    <button type="submit" class="tq-btn tq-btn-main" style="width:100%;"><i class="bi bi-check2-circle"></i> დადასტურება</button>
                </form>
                <button type="submit" form="rvRej{{ $__q->id }}" class="tq-btn tq-btn-danger" style="width:100%;" onclick="var t=document.getElementById('rvC{{ $__q->id }}'); if(!t.value.trim()){t.focus(); t.setAttribute('aria-invalid','true'); t.style.borderColor='#A12626'; return false;}"><i class="bi bi-arrow-return-left"></i> დაბრუნება</button>
                <a href="{{ route('admin.questions.edit', $__q) }}" class="tq-btn tq-btn-ghost" style="width:100%;"><i class="bi bi-sliders"></i> რედაქტორში გახსნა</a>
            </div>
        </div>
        @endif

        @if($__isFlag)
        <div class="rv-act">
            <form method="POST" action="{{ route('admin.questions.review.resolve', $__q) }}" id="rvRes{{ $__q->id }}" style="margin:0;">
                @csrf
                <label for="rvR{{ $__q->id }}" style="font-size:13px;font-weight:800;color:#4F473F;display:block;margin-bottom:6px;">პასუხი მასწავლებელს <span style="font-weight:600;color:#6B625A;">(არასავალდებულო)</span></label>
                <textarea id="rvR{{ $__q->id }}" name="comment" maxlength="1000" placeholder="მაგ.: გასწორდა — პასუხის ფორმულა შეცვლილია. მადლობა!"></textarea>
            </form>
            <div class="rv-btns">
                <a href="{{ route('admin.questions.edit', $__q) }}" class="tq-btn tq-btn-soft" style="width:100%;"><i class="bi bi-pencil"></i> რედაქტორში გასწორება</a>
                <button type="submit" form="rvRes{{ $__q->id }}" class="tq-btn tq-btn-main" style="width:100%;"><i class="bi bi-check2-circle"></i> გასწორდა — ჩართე</button>
                <span style="font-size:12px;color:#6B625A;text-align:center;">ჯერ გაასწორე, მერე ჩართე. სანამ არ ჩართავ, ბავშვებს არ უჩანთ.</span>
            </div>
        </div>
        @endif
    </article>
    @empty
    <div class="tq-card" style="text-align:center;padding:40px 16px;color:#6B625A;">
        <i class="bi bi-inbox" style="font-size:38px;color:#FFC49A;"></i>
        <p style="margin:8px 0 0;font-weight:700;">{{ $status === 'pending' ? 'განსახილველი კითხვა არ არის.' : ($__isFlag ? 'შეცდომის შეტყობინება არ არის.' : 'დაბრუნებული კითხვა არ არის.') }}</p>
    </div>
    @endforelse

    @if($questions->hasPages())<div>{{ $questions->links('admin.pagination') }}</div>@endif
</div>
@endsection
