@extends('layouts.app')
@section('content')
<style>
    .pa-kpis { grid-template-columns: repeat(5, minmax(0,1fr)) !important; }
    .pa-more { font-size: 13px; font-weight: 700; color: #C24A0A; text-decoration: none; }
    .pa-test { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #2B2622; padding: 8px 0; border-bottom: 1px solid #F1EFEB; }
    .pa-test:last-of-type { border-bottom: none; }
    .pa-test:hover { color: #2B2622; }
    .pa-test-ic { width: 40px; height: 40px; border-radius: 12px; background: #F7F6F3; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .pa-test-t { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .pa-test-t b { font-size: 15px; font-weight: 700; }
    .pa-test-t small { font-size: 12px; color: #6B625A; }
    .pa-test-p { font-size: 13px; font-weight: 800; border-radius: 999px; padding: 4px 10px; flex-shrink: 0; }
    .pa-test-p.hi { background: #E8F7EE; color: #1E7A45; } .pa-test-p.mid { background: #FFF1C9; color: #8A5A00; } .pa-test-p.lo { background: #FDECEA; color: #B42318; }
    .pa { display: flex; flex-direction: column; gap: 12px; max-width: 760px; width: 100%; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; }
    .pa-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .pa-back { width: 44px; height: 44px; border-radius: 14px; background: #fff; border: 1px solid #ECE9E4; color: #2B2622; display: flex; align-items: center; justify-content: center; font-size: 18px; text-decoration: none; flex-shrink: 0; }
    .pa-back:hover { color: #2B2622; background: #FFF3EA; }
    .pa-title { margin: 0; flex-grow: 1; font-size: 36px; line-height: 1; font-weight: 900; font-stretch: 68%; text-transform: uppercase; }
    .pa-kids { display: flex; gap: 6px; flex-wrap: wrap; }
    .pa-kid { height: 40px; padding: 0 12px; border-radius: 999px; font-size: 13px; font-weight: 700; background: #fff; color: #2B2622; border: 2px solid #ECE9E4; display: inline-flex; align-items: center; text-decoration: none; }
    .pa-kid:hover { color: #2B2622; border-color: #FFC49A; }
    .pa-kid.on { background: #E2590F; color: #fff; border-color: #E2590F; }
    .pa-who { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #4F473F; }
    .pa-who-av { width: 30px; height: 30px; border-radius: 50%; background: #FFD9BF; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .pa-who-av.boy { background: #FFF3EA; } .pa-who-av.girl { background: #FFF3EA; }
    .pa-kpis { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 8px; }
    .pa-kpis div { background: #fff; border-radius: 16px; padding: 10px 4px; display: flex; flex-direction: column; align-items: center; gap: 2px; box-shadow: 0 2px 0 #ECE9E4; }
    .pa-kpis b { font-size: 18px; font-weight: 900; }
    .pa-kpis span { font-size: 10px; color: #6B625A; text-align: center; }
    .pa-card { background: #fff; border-radius: 22px; padding: 14px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 2px 0 #ECE9E4; }
    .pa-card-h { display: flex; justify-content: space-between; align-items: center; }
    .pa-card-h span { font-size: 14px; font-weight: 800; }
    .pa-card-h small { font-size: 11px; color: #6B625A; }
    .pa-bars { height: 130px; display: grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 8px; align-items: end; border-bottom: 1px solid #ECE9E4; }
    .pa-bar { display: flex; flex-direction: column; align-items: center; gap: 4px; justify-content: flex-end; height: 130px; }
    .pa-bar span { font-size: 10px; font-weight: 700; color: #4F473F; }
    .pa-bar i { display: block; width: 100%; max-width: 44px; border-radius: 8px 8px 3px 3px; }
    .pa-days { display: grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 8px; }
    .pa-days span { font-size: 10px; color: #6B625A; text-align: center; }
    .pa-days span.today { color: #2B2622; font-weight: 800; }
    .pa-topic { display: flex; flex-direction: column; gap: 4px; }
    .pa-topic-h { display: flex; justify-content: space-between; gap: 8px; font-size: 12px; font-weight: 700; }
    .pa-topic-bar { height: 8px; border-radius: 4px; background: #F1EFEB; overflow: hidden; }
    .pa-topic-bar span { display: block; height: 8px; border-radius: 4px; }
    .pa-empty { font-size: 13px; color: #6B625A; }
    .pa-insight { background: #FFF3EA; border-radius: 20px; padding: 12px 14px; display: flex; gap: 10px; align-items: flex-start; }
    .pa-insight-ic { width: 36px; height: 36px; border-radius: 12px; background: #fff; color: #C24A0A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 17px; }
    .pa-insight-t { display: flex; flex-direction: column; gap: 2px; }
    .pa-insight-t b { font-size: 13px; font-weight: 800; color: #7A3206; }
    .pa-insight-t small { font-size: 12px; line-height: 1.4; color: #8A3D0B; }
    .pa-mini { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 8px; margin-top: 4px; }
    .pa-mini a, .pa-mini button { text-decoration: none; color: #2B2622; border: none; border-radius: 18px; padding: 12px 10px; display: flex; flex-direction: column; align-items: flex-start; gap: 2px; font-family: inherit; text-align: left; cursor: pointer; min-width: 0; }
    .pa-mini a:hover { color: #2B2622; filter: brightness(.98); }
    .pa-mini i { font-size: 20px; margin-bottom: 2px; }
    .pa-mini b { font-size: 20px; font-weight: 900; line-height: 1.15; }
    .pa-mini b small { font-size: 11px; font-weight: 700; color: #6B625A; }
    .pa-mini span { font-size: 11px; color: #4F473F; line-height: 1.35; }
    @media (min-width: 700px) { .pa-kpis b { font-size: 24px; } .pa-kpis span { font-size: 12px; } .pa-bars, .pa-bar { height: 170px; } }
    .hub-toast-wrap { position: fixed; top: 16px; left: 50%; transform: translate(-50%, -140%); z-index: 2000; transition: transform .35s cubic-bezier(.175,.885,.32,1.275); pointer-events: none; }
    .hub-toast-wrap.show { transform: translate(-50%, 0); }
    .hub-toast { background: #C24A0A; color: #fff; border-radius: 999px; padding: 10px 20px; font-family: 'Noto Sans Georgian', sans-serif; font-weight: 800; font-size: 14px; box-shadow: 0 10px 28px rgba(0,0,0,.2); }
    .remind-modal { position: fixed; inset: 0; background: rgba(29,26,23,.45); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 16px; }
    .remind-modal.open { display: flex; }
    .remind-box { background: #fff; border-radius: 26px; padding: 24px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.2); font-family: 'Noto Sans Georgian', sans-serif; color: #2B2622; }
    .remind-title { font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .remind-close { background: #F1EFEB; border: none; color: #2B2622; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; flex-shrink: 0; }
    .remind-textarea { width: 100%; border: 1.5px solid #E6E2DC; border-radius: 16px; padding: 12px 14px; font-family: inherit; font-size: 15px; color: #2B2622; resize: none; outline: none; box-sizing: border-box; }
    .remind-textarea:focus { border-color: #E2590F; box-shadow: 0 0 0 3px rgba(226,89,15,.14); }
    .remind-hint { font-size: 13px; color: #6B625A; margin: 6px 0 14px; }
    .remind-send { width: 100%; height: 50px; background: #C24A0A; border: none; border-radius: 25px; color: #fff; font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer; }
    .remind-send:hover { background: #A33E08; }
</style>

@if(session('reminder_sent_' . $child->id))
@push('toasts')
<div class="hub-toast-wrap" id="hubToastWrap">
    <div class="hub-toast">✓ შეხსენება გაიგზავნა</div>
</div>
<script>
(function(){
    var wrap = document.getElementById('hubToastWrap');
    if (!wrap) return;
    requestAnimationFrame(function(){ wrap.classList.add('show'); });
    setTimeout(function(){
        wrap.classList.remove('show');
        setTimeout(function(){ wrap.remove(); }, 400);
    }, 3200);
})();
</script>
@endpush
@endif

@php
    $__s      = $child->childSetting;
    $__kids   = auth()->user()->children()->withTimestamps()->orderByPivot('created_at', 'asc')->get();
    $__done   = $child->tests()->where('is_olympiad', false)->whereNotNull('completed_at');
    $__all    = (clone $__done)->get(['completed_at', 'correct_count', 'total_questions']);
    $__avgAll = $__all->count() ? (int) round($__all->avg(fn($t) => $t->correct_count / max(1, $t->total_questions) * 100)) : null;
    $__dates  = $__all->map(fn($t) => $t->completed_at->format('Y-m-d'))->unique();
    $__streak = 0; $__cur = today();
    if (!$__dates->contains($__cur->format('Y-m-d'))) { $__cur = $__cur->copy()->subDay(); }
    while ($__dates->contains($__cur->format('Y-m-d'))) { $__streak++; $__cur = $__cur->copy()->subDay(); }
    $__max    = (int) ($__s?->grade?->max_level ?? \App\Models\Grade::DEFAULT_MAX_LEVEL);

    // ეს კვირა (ორშ–კვი): დღიური საშუალო %
    $__mon    = today()->startOfWeek(\Carbon\Carbon::MONDAY);
    $__days   = ['ორშ', 'სამ', 'ოთხ', 'ხუთ', 'პარ', 'შაბ', 'კვი'];
    $__week   = collect(range(0, 6))->map(function ($i) use ($__mon, $__all) {
        $d  = $__mon->copy()->addDays($i);
        $ts = $__all->filter(fn($t) => $t->completed_at->isSameDay($d));
        return $ts->count() ? (int) round($ts->sum('correct_count') / max(1, $ts->sum('total_questions')) * 100) : 0;
    });

    // თემების მიხედვით (მიმდინარე კლასი)
    $__topics = \App\Models\TestAnswer::query()
        ->join('test_questions', 'test_answers.test_question_id', '=', 'test_questions.id')
        ->join('tests', 'test_answers.test_id', '=', 'tests.id')
        ->join('question_templates', 'test_questions.template_id', '=', 'question_templates.id')
        ->join('topics', 'question_templates.topic_id', '=', 'topics.id')
        ->where('tests.child_id', $child->id)->where('tests.is_olympiad', false)
        ->when($__s?->grade_id, fn($q) => $q->where('tests.grade_id', $__s->grade_id))
        ->whereNotNull('tests.completed_at')
        ->selectRaw('topics.name as name, SUM(test_answers.is_correct) as ok, COUNT(*) as n')
        ->groupBy('topics.name')->get()
        ->map(fn($r) => (object) ['name' => $r->name, 'pct' => $r->n > 0 ? (int) round($r->ok / $r->n * 100) : 0])
        ->sortByDesc('pct')->values();
    $__weak   = $__topics->filter(fn($t) => $t->pct < 70)->sortBy('pct')->first();

    $__todayDone = $__all->filter(fn($t) => $t->completed_at->isToday())->count();
    $__prac7  = \App\Models\PracticeAnswerLog::where('child_id', $child->id)->where('created_at', '>=', today()->subDays(6))->count();
    $__recent = $child->tests()->whereNotNull('completed_at')->with('theme')->latest('completed_at')->take(5)->get();
    $__av     = $child->avatar === 'boy' ? '👦' : ($child->avatar === 'girl' ? '👧' : '🙂');
@endphp

<div class="pa">
    <div class="ks-cp-hero purple">
        <span class="ks-cp-hero-ic" aria-hidden="true">📊</span>
        <span class="ks-cp-eyebrow">{{ $child->name }} · {{ $__s?->grade?->name ?? 'კლასი —' }}</span>
        <h1 class="ks-cp-title">ანალიტიკა</h1>
    </div>
    @include('parent.partials.kid-switcher')

    <div class="pa-kpis">
        <div><b>{{ $totalTestsCount }}</b><span>ტესტი სულ</span></div>
        <div><b>{{ $__avgAll !== null ? $__avgAll . '%' : '—' }}</b><span>საშუალო შედეგი</span></div>
        <div><b>{{ min($__todayDone, 1) }}/1</b><span>დღეს</span></div>
        <div><b>{{ $__s ? $__s->difficulty . '/' . $__max : '—' }}</b><span>დონე</span></div>
        <div><b>{{ $__streak }}</b><span>დღე ზედიზედ</span></div>
    </div>

    <div class="pa-card">
        <div class="pa-card-h"><span>ტესტის შედეგები · ეს კვირა</span><small>% სწორი</small></div>
        <div class="pa-bars" role="img" aria-label="ყოველდღიური შედეგები: {{ $__week->map(fn($v, $i) => $__days[$i] . ' ' . ($v ? $v . '%' : 'არ წერდა'))->implode(', ') }}">
            @foreach($__week as $__i => $__v)
            <div class="pa-bar">
                <span>{{ $__v ?: '—' }}</span>
                <i style="height: {{ $__v ? round($__v * 0.95) : 4 }}px; background: {{ $__v >= 85 ? '#E2590F' : ($__v ? '#F7B58A' : '#ECE9E4') }}"></i>
            </div>
            @endforeach
        </div>
        <div class="pa-days">
            @foreach($__days as $__i => $__d)
            <span class="{{ $__mon->copy()->addDays($__i)->isToday() ? 'today' : '' }}">{{ $__d }}</span>
            @endforeach
        </div>
    </div>

    <div class="pa-card">
        <div class="pa-card-h"><span>თემების მიხედვით</span></div>
        @forelse($__topics as $__t)
        <div class="pa-topic">
            <div class="pa-topic-h"><span>{{ $__t->name }}</span><span>{{ $__t->pct }}%</span></div>
            <div class="pa-topic-bar"><span style="width: {{ max(3, $__t->pct) }}%; background: {{ $__t->pct >= 85 ? '#1E8A4C' : ($__t->pct >= 70 ? '#E2590F' : '#D93B3B') }}"></span></div>
        </div>
        @empty
        <div class="pa-empty">ტესტები ჯერ არ დაწერილა — თემების შედეგები აქ გამოჩნდება.</div>
        @endforelse
    </div>

    <div class="pa-card">
        <div class="pa-card-h"><span>ტესტების ისტორია</span>@if($__recent->count())<a href="{{ route('child.stats', $child) }}" class="pa-more">ყველა →</a>@endif</div>
        @forelse($__recent as $__rt)
        @php $__rp = (int) round($__rt->correct_count / max(1, $__rt->total_questions) * 100); @endphp
        <a href="{{ route('child.test.show', [$child, $__rt]) }}" class="pa-test">
            <span class="pa-test-ic">{{ $__rt->is_olympiad ? '🏆' : ($__rt->theme?->icon ?? '📝') }}</span>
            <span class="pa-test-t"><b>{{ $__rt->correct_count }} / {{ $__rt->total_questions }} სწორი</b><small>{{ $__rt->completed_at->format('d.m.Y · H:i') }}{{ $__rt->is_olympiad ? ' · ოლიმპიადა' : '' }}</small></span>
            <span class="pa-test-p {{ $__rp >= 80 ? 'hi' : ($__rp >= 50 ? 'mid' : 'lo') }}">{{ $__rp }}%</span>
        </a>
        @empty
        <div class="pa-empty">ტესტები ჯერ არ დაწერილა</div>
        @endforelse
    </div>

    <div class="pa-insight">
        <span class="pa-insight-ic"><i class="bi bi-lightbulb"></i></span>
        <span class="pa-insight-t">
            @if($__todayDone === 0)
                <b>დღეს ჯერ არ დაუწყია</b>
                <small>გაუგზავნე შეხსენება — ერთი ტესტი დღეში საკმარისია.</small>
            @elseif($__weak)
                <b>{{ $__weak->name }} — უჭირს</b>
                <small>ამ თემაში სწორია {{ $__weak->pct }}%. ვარჯიში ამ თემაზე დაეხმარება.</small>
            @else
                <b>ყოჩაღ, {{ $child->name }}!</b>
                <small>{{ $__topics->isNotEmpty() ? 'ყველა თემაში 70%-ზე მეტი სწორი პასუხი აქვს.' : 'დღევანდელი ტესტი დაწერილია.' }}</small>
            @endif
        </span>
    </div>

    {{-- დამატებითი ინფორმაცია პატარა ბარათებად --}}
    <div class="pa-mini">
        <a href="{{ route('child.practice-stats', $child) }}" style="background:#FFE3CC;">
            <i class="bi bi-bullseye" style="color:#C24A0A;"></i>
            <b>{{ $practiceTodayCount }} <small>დღეს</small></b>
            <span>ვარჯიში · 7 დღეში {{ $__prac7 }}</span>
        </a>
        <a href="{{ route('market.index', $child) }}" style="background:#FFF1C2;">
            <i class="bi bi-coin" style="color:#8A6200;"></i>
            <b>{{ (int) floor($coins) }}</b>
            <span>მონეტა{{ $pendingMarket ? ' · ' . $pendingMarket . ' მოთხოვნა' : '' }}</span>
        </a>
        <a href="{{ route('child.achievements', $child) }}" style="background:#FFF3EA;">
            <i class="bi bi-award" style="color:#C24A0A;"></i>
            <b>{{ $achCount }}</b>
            <span>მედალი</span>
        </a>
        <a href="{{ route('child.olympiad-stats', $child) }}" style="background:#FFF3EA;">
            <i class="bi bi-trophy" style="color:#E2590F;"></i>
            <b>{{ $olympiadCount }}</b>
            <span>
                @if($olympiadStatus['already_attempted_today']) ოლიმპიადა · დღეს დაწერა
                @elseif($olympiadStatus['eligible_today']) ოლიმპიადა · დღეს ღიაა
                @else ოლიმპიადა @endif
            </span>
        </a>
        <a href="{{ route('child.stats', $child) }}" style="background:#DFF5E3;">
            <i class="bi bi-clipboard-data" style="color:#1E7A45;"></i>
            <b>{{ $__todayDone }}/1</b>
            <span>ტესტი დღეს · ისტორია</span>
        </a>
        <button type="button" onclick="document.getElementById('remindModal').classList.add('open')" style="background:#C24A0A;color:#fff;">
            <i class="bi bi-bell" style="color:#FFC49A;"></i>
            <b style="font-size:15px;">შეხსენება</b>
            <span style="color:#FFE3CC;">გაუგზავნე {{ $child->name }}-ს</span>
        </button>
    </div>
</div>

@push('modals')
<div id="remindModal" class="remind-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="remind-box">
        <div class="remind-title">
            <span>შეხსენება — {{ $child->name }}</span>
            <button type="button" class="remind-close" aria-label="დახურვა" onclick="document.getElementById('remindModal').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('push.remind', $child) }}">
            @csrf
            <textarea name="message" class="remind-textarea" rows="3"
                placeholder="ტექსტი (სურვილისამებრ)&#10;მაგ: ახლავე გააკეთე ტესტი! 📝"></textarea>
            <div class="remind-hint">ცარიელი = სტანდარტული შეტყობინება</div>
            <button type="submit" class="remind-send">გაგზავნა</button>
        </form>
    </div>
</div>
@endpush
@endsection
