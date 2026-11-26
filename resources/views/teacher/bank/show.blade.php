@extends('layouts.app')
{{-- ბანკის ერთი კითხვა: როგორ ნახავს ბავშვი (ახალი ვარიანტის გენერაცია) + შეცდომის შეტყობინება ადმინთან --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__flag = $question->review_status === \App\Models\QuestionTemplate::REVIEW_FLAGGED;
    $__mine = $question->flagged_by === auth()->id();
    $__hist = array_values(array_filter((array) ($td['history'] ?? []), fn ($h) => is_array($h) && in_array($h['event'] ?? '', ['flagged', 'comment', 'resolved'], true)));
@endphp
<style>
    .tb-show { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 1000px) { .tb-show { grid-template-columns: 1fr; } }
    .tb-kgrid { display: flex; flex-direction: column; gap: 12px; }
    .tb-side { display: flex; flex-direction: column; gap: 14px; }
    .tb-side h2 { font-size: 17px; font-weight: 900; margin: 0; color: #2B2622; }
    .tb-side p { margin: 0; font-size: 14px; color: #6B625A; line-height: 1.5; }
    .tb-side textarea { width: 100%; min-height: 110px; border: 2px solid #ECE9E4; border-radius: 14px; padding: 10px 12px; font-size: 15px; font-family: inherit; resize: vertical; }
    .tb-side textarea:focus { outline: none; border-color: #E2590F; box-shadow: 0 0 0 4px rgba(226, 89, 15, .12); }
    .tb-off { border-radius: 16px; padding: 14px 16px; background: #FDE2E2; color: #7A1C1C; font-size: 14px; line-height: 1.5; }
    .tb-off b { display: block; font-size: 15px; margin-bottom: 2px; }
    .tb-ok { border-radius: 16px; padding: 14px 16px; background: #DFF5E3; color: #14612F; font-size: 14px; }
    .tb-meta { display: flex; flex-wrap: wrap; gap: 6px; }
</style>

<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>{{ $question->title ?: 'კითხვა #' . $question->id }}</h1>
            <div class="tb-meta" style="margin-top:6px;">
                <span class="tq-pill o">{{ $question->topic?->grade?->name }}</span>
                <span class="tq-pill o">{{ $question->topic?->name }}</span>
                <span class="tq-pill">{{ $question->is_olympiad ? 'ოლიმპიადა' : 'დონე ' . $question->difficulty }}</span>
                @if($question->theme)<span class="tq-pill">თემატიკა: {{ $question->theme->name }}</span>@endif
                @if($__flag)<span class="tq-pill r"><i class="bi bi-flag-fill"></i> გამორთულია</span>@else<span class="tq-pill g">ბავშვებს უჩანთ</span>@endif
            </div>
        </div>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('teacher.bank.index') }}" class="tq-btn tq-btn-ghost">← უკან</a>
    </div>

    @if(session('success'))<div class="tq-flash" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="tq-flash err" role="alert">{{ $errors->first() }}</div>@endif

    <div class="tb-show">
        <section class="tq-kid">
            <span class="tq-kid-lbl">ასე ნახავს ბავშვი</span>
            <div class="tb-kgrid">
                @forelse($samples as $__s)
                    @if($special)
                        @include('teacher.partials.interactive-card', ['q' => $__s])
                    @else
                        @include('teacher.partials.kid-card', ['s' => $__s, 'showAnswer' => true])
                    @endif
                @empty
                    <div class="tq-kcard" style="color:#A12626;font-weight:700;">⚠ ეს კითხვა ვერ გენერირდება — შესაძლოა სწორედ ეს არის შეცდომა.</div>
                @endforelse
            </div>
            <div style="display:flex;justify-content:center;margin-top:12px;">
                <a href="{{ route('teacher.bank.show', $question) }}?v={{ now()->timestamp }}" class="tq-btn tq-btn-ghost tq-btn-sm"><i class="bi bi-shuffle"></i> ახალი ვარიანტები</a>
            </div>
            <div class="tq-kmeta">{{ $special ? $special . ' — ბავშვი ცარიელ უჯრებს ავსებს; აქ სწორი პასუხები უკვე ჩაწერილია' : 'მწვანე — სწორი პასუხი' }} · ყოველ ჯერზე რიცხვები შეიძლება შეიცვალოს</div>
        </section>

        <aside class="tb-side">
            @if($__flag)
                <div class="tb-off">
                    <b><i class="bi bi-flag-fill"></i> კითხვა გამორთულია და ადმინთან არის</b>
                    {{ $question->flagger?->name ?? 'მასწავლებელმა' }} შეატყობინა {{ optional($question->flagged_at)->format('d.m.Y H:i') }}.
                    ბავშვებს არ უჩანთ — არც ტესტებში, არც ვარჯიშში — სანამ ადმინი არ გაასწორებს.
                    @if(!empty($td['flag']['comment']))<div style="margin-top:8px;background:#fff;border-radius:10px;padding:8px 10px;color:#2B2622;white-space:pre-line;">{{ $td['flag']['comment'] }}</div>@endif
                </div>
                <section class="tq-card" style="display:flex;flex-direction:column;gap:10px;">
                    <h2>კომენტარის დამატება</h2>
                    <form method="POST" action="{{ route('teacher.bank.comment', $question) }}" style="display:flex;flex-direction:column;gap:10px;margin:0;">
                        @csrf
                        <label for="tbC" class="visually-hidden">კომენტარი</label>
                        <textarea id="tbC" name="comment" maxlength="1000" required placeholder="დამატებითი შენიშვნა ადმინისთვის…">{{ old('comment') }}</textarea>
                        <button type="submit" class="tq-btn tq-btn-soft"><i class="bi bi-chat-left-text"></i> კომენტარის დამატება</button>
                    </form>
                </section>
            @else
                @if($__mine && !empty($td['flag']['resolved_at']))
                <div class="tb-ok"><i class="bi bi-check2-circle"></i> შენი შეტყობინების შემდეგ ადმინმა ({{ $td['flag']['resolved_by'] ?? '' }}) კითხვა გაასწორა და ისევ ჩართო.</div>
                @endif
                <section class="tq-card" style="display:flex;flex-direction:column;gap:10px;">
                    <h2><i class="bi bi-flag" style="color:#C24A0A;"></i> შეცდომა იპოვე?</h2>
                    <p>აღწერე, რა არის არასწორი. კითხვა <b>მაშინვე გამოირთვება</b> ყველგან (ტესტები, ვარჯიში, ოლიმპიადა, დაწყებული ტესტები) და ადმინთან გაიგზავნება. გასწორების შემდეგ ადმინი ისევ ჩართავს.</p>
                    <form method="POST" action="{{ route('teacher.bank.flag', $question) }}" style="display:flex;flex-direction:column;gap:10px;margin:0;" onsubmit="return confirm('კითხვა გამოირთოს ბავშვებთან და გაიგზავნოს ადმინთან?');">
                        @csrf
                        <label for="tbF" style="font-size:13px;font-weight:800;color:#4F473F;">კომენტარი ადმინისთვის <span style="font-weight:600;color:#6B625A;">(სავალდებულო)</span></label>
                        <textarea id="tbF" name="comment" maxlength="1000" required placeholder="მაგ.: სწორი პასუხი 12 უნდა იყოს და არა 14 — 7+5=12.">{{ old('comment') }}</textarea>
                        <button type="submit" class="tq-btn tq-btn-main"><i class="bi bi-send"></i> გაგზავნე განხილვაზე</button>
                    </form>
                </section>
            @endif

            @if($__hist)
            <section class="tq-card" style="display:flex;flex-direction:column;gap:10px;">
                <h2>მიმოწერა ამ კითხვაზე</h2>
                @include('teacher.partials.history', ['history' => $__hist])
            </section>
            @endif
        </aside>
    </div>
</div>
@endsection
