@extends('layouts.app')
{{-- კითხვების ბანკი: ყველა კითხვა, რასაც ბავშვები ხედავენ + შეცდომის შეტყობინებები --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__views = [
        'all'     => ['ყველა კითხვა', 'bi-collection', 'ყველა კითხვა, რომელიც ბავშვებს ტესტებსა და ვარჯიშში ხვდებათ. გახსენი და ნახე, როგორ დაინახავს ბავშვი.'],
        'flagged' => ['გამორთული (შეცდომით)', 'bi-flag', 'მასწავლებლების მიერ შეცდომით მონიშნული კითხვები — ადმინის გასწორებამდე ბავშვებს არ უჩანთ.'],
        'mine'    => ['ჩემი შეტყობინებები', 'bi-person-check', 'კითხვები, რომლებზეც შენ შეატყობინე შეცდომა, და მათი მდგომარეობა.'],
    ];
    $__topics = $grades->flatMap(fn ($g) => $g->topics->map(fn ($t) => ['id' => $t->id, 'g' => $g->id, 'n' => $t->name]))->values();
@endphp
<style>
    .tb-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .tb-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 14px; background: #fff; border: 1.5px solid #ECE9E4; font-weight: 800; color: #4F473F; text-decoration: none; }
    .tb-tab:hover { border-color: #FFC49A; color: #C24A0A; }
    .tb-tab.on { background: #FFF3EA; border-color: #E2590F; color: #C24A0A; }
    .tb-tab em { font-style: normal; background: #F1EFEB; border-radius: 99px; padding: 1px 8px; font-size: 12px; }
    .tb-filter { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto; gap: 10px; align-items: end; }
    @media (max-width: 900px) { .tb-filter { grid-template-columns: 1fr 1fr; } }
    .tb-filter label { display: block; font-size: 12px; font-weight: 800; color: #6B625A; margin-bottom: 4px; }
    .tb-filter select, .tb-filter input { width: 100%; border: 2px solid #ECE9E4; border-radius: 12px; padding: 9px 10px; font-size: 14px; font-family: inherit; background: #fff; color: #2B2622; }
    .tb-filter select:focus, .tb-filter input:focus { outline: none; border-color: #E2590F; }
    .tb-list { display: flex; flex-direction: column; gap: 10px; }
    .tb-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: center; text-decoration: none; color: inherit; }
    .tb-row:hover { border-color: #FFC49A; }
    .tb-row.off { background: #FFFAFA; border-color: #F5C2C2; }
    .tb-top { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 6px; }
    .tb-text { font-size: 15px; line-height: 1.5; color: #2B2622; white-space: pre-line; }
    .tb-ans { font-size: 13px; color: #6B625A; margin-top: 4px; }
    .tb-go { color: #C24A0A; font-weight: 800; font-size: 14px; white-space: nowrap; }
    .tb-empty { text-align: center; padding: 40px 16px; color: #6B625A; }
    .tb-empty i { font-size: 38px; color: #FFC49A; }
</style>

<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>კითხვების ბანკი</h1>
            <p>{{ $__views[$view][2] }}</p>
        </div>
    </div>

    @if(session('success'))<div class="tq-flash" role="status">{{ session('success') }}</div>@endif

    <div class="tb-tabs" role="tablist">
        @foreach($__views as $__k => [$__l, $__i])
        <a href="{{ route('teacher.bank.index', array_merge($f, ['view' => $__k])) }}" role="tab" aria-selected="{{ $view === $__k ? 'true' : 'false' }}" class="tb-tab {{ $view === $__k ? 'on' : '' }}">
            <i class="bi {{ $__i }}"></i>{{ $__l }}@if($__k !== 'all') <em>{{ $counts[$__k] ?? 0 }}</em>@endif
        </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('teacher.bank.index') }}" class="tq-card tb-filter">
        <input type="hidden" name="view" value="{{ $view }}">
        <div>
            <label for="tbGrade">კლასი</label>
            <select id="tbGrade" name="grade_id" onchange="tbTopics()">
                <option value="">ყველა</option>
                @foreach($grades as $__g)<option value="{{ $__g->id }}" @selected(($f['grade_id'] ?? '') == $__g->id)>{{ $__g->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="tbTopic">თემა</label>
            <select id="tbTopic" name="topic_id" data-sel="{{ $f['topic_id'] ?? '' }}"><option value="">ყველა</option></select>
        </div>
        <div>
            <label for="tbDiff">დონე</label>
            <select id="tbDiff" name="difficulty">
                <option value="">ყველა</option>
                @for($__i = 1; $__i <= 5; $__i++)<option value="{{ $__i }}" @selected(($f['difficulty'] ?? '') == $__i)>დონე {{ $__i }}</option>@endfor
                <option value="olympiad" @selected(($f['difficulty'] ?? '') === 'olympiad')>ოლიმპიადა</option>
            </select>
        </div>
        <div>
            <label for="tbSearch">ძებნა</label>
            <input id="tbSearch" type="search" name="search" value="{{ $f['search'] ?? '' }}" placeholder="სიტყვა კითხვიდან">
        </div>
        <button type="submit" class="tq-btn tq-btn-main tq-btn-sm" style="height:42px;"><i class="bi bi-search"></i> ძებნა</button>
    </form>

    @if($questions->isEmpty())
        <div class="tq-card tb-empty">
            <i class="bi {{ $__views[$view][1] }}"></i>
            <p style="margin:8px 0 0;font-weight:700;">{{ $view === 'all' ? 'ამ ფილტრით კითხვა ვერ მოიძებნა.' : ($view === 'flagged' ? 'გამორთული კითხვა არ არის.' : 'ჯერ არცერთ კითხვაზე არ შეგიტყობინებია შეცდომა.') }}</p>
        </div>
    @else
    <div style="font-size:13px;color:#6B625A;">სულ {{ $questions->total() }} კითხვა</div>
    <div class="tb-list">
        @foreach($questions as $__q)
        @php
            $__p = $preview[$__q->id] ?? ['text' => $__q->template_text, 'answer' => null, 'special' => null];
            $__flag = $__q->review_status === \App\Models\QuestionTemplate::REVIEW_FLAGGED;
        @endphp
        <a href="{{ route('teacher.bank.show', $__q) }}" class="tq-card tb-row {{ $__flag ? 'off' : '' }}">
            <div>
                <div class="tb-top">
                    <span class="tq-pill o">{{ $__q->topic?->grade?->name }} · {{ $__q->topic?->name }}</span>
                    <span class="tq-pill">{{ $__q->is_olympiad ? 'ოლიმპიადა' : 'დონე ' . $__q->difficulty }}</span>
                    @if($__q->theme)<span class="tq-pill">{{ $__q->theme->name }}</span>@endif
                    @if($__p['special'])<span class="tq-pill y">{{ $__p['special'] }}</span>@endif
                    @if($__flag)
                        <span class="tq-pill r"><i class="bi bi-flag-fill"></i> გამორთულია · {{ $__q->flagger?->name }}</span>
                    @elseif($view === 'mine')
                        <span class="tq-pill g"><i class="bi bi-check2-circle"></i> გასწორდა, ჩართულია</span>
                    @endif
                    @if(!empty($__p['broken']))<span class="tq-pill r">⚠ ვერ გენერირდება</span>@endif
                </div>
                <div class="tb-text">{{ \Illuminate\Support\Str::limit($__p['text'], 260) }}</div>
                @if($__p['answer'] !== null && $__p['answer'] !== '')<div class="tb-ans">სწორი პასუხი: <b>{{ $__p['answer'] }}</b></div>@endif
            </div>
            <span class="tb-go">ნახვა <i class="bi bi-chevron-right"></i></span>
        </a>
        @endforeach
    </div>
    @if($questions->hasPages())<div>{{ $questions->links('admin.pagination') }}</div>@endif
    @endif
</div>

<script>
const TB_TOPICS = @json($__topics);
function tbTopics() {
    const g = document.getElementById('tbGrade').value;
    const sel = document.getElementById('tbTopic');
    const keep = sel.value || sel.dataset.sel;
    sel.innerHTML = '<option value="">ყველა</option>';
    TB_TOPICS.filter(t => !g || String(t.g) === g).forEach(t => {
        const o = document.createElement('option');
        o.value = t.id; o.textContent = t.n;
        if (String(t.id) === String(keep)) o.selected = true;
        sel.appendChild(o);
    });
    sel.dataset.sel = '';
}
tbTopics();
</script>
@endsection
