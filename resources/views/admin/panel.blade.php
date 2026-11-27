@extends('layouts.app')

{{-- redesign-kidsmart: ადმინ პანელი (დიზაინი A1) --}}
@section('content')
@php
$_map = [
    'admin.questions' => ['route' => 'admin.questions.index',   'name' => 'ახალი შაბლონი',     'sub' => 'კითხვის კონსტრუქტორი', 'bg' => '#FFE3CC', 'create' => true],
    'admin.topics'    => ['route' => 'admin.topics.index',      'name' => 'თემები',            'sub' => 'კლასების თემები',      'bg' => '#FFF3EA'],
    'admin.themes'    => ['route' => 'admin.themes.index',      'name' => 'ცვლადები',          'sub' => 'თემატიკის სიტყვები',   'bg' => '#FFF3EA'],
    'admin.olympiadrules' => ['route' => 'admin.olympiad-rules.index', 'name' => 'ოლიმპიადა', 'sub' => 'თარიღი და წესები',     'bg' => '#FFF1C2'],
    'admin.users'     => ['route' => 'admin.users.index',       'name' => 'პაკეტის მინიჭება',  'sub' => 'მომხმარებლებში',       'bg' => '#DFF5E3'],
    'admin.grades'    => ['route' => 'admin.grades.index',      'name' => 'კლასები',           'sub' => 'მაქს. დონე, სტატუსი',  'bg' => '#FFF3EA'],
];
$_visibleMap = $isAdmin
    ? $_map
    : array_filter($_map, fn($key) => in_array($key, \App\Models\RolePermission::allowedPages(auth()->user()->role) ?? []), ARRAY_FILTER_USE_KEY);

if ($isAdmin) {
    $_kUsers     = $users->count();
    $_kNewWeek   = $users->where('created_at', '>=', now()->subDays(7))->count();
    $_kChildren  = $users->where('role', 'child')->count();
    $_kTestsDay  = \App\Models\Test::whereNotNull('completed_at')->whereDate('completed_at', today())->count();
    $_kPaid      = \App\Models\UserSubscription::where(fn($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
                        ->whereHas('package', fn($q) => $q->where('is_free', false))->count();
    $_kExpiring  = \App\Models\UserSubscription::whereBetween('expires_at', [today(), today()->addDays(7)])->count();

    // კონტენტის დაფარვა: კლასი × სირთულე
    $_grades = \App\Models\Grade::orderBy('number')->withCount('topics')->get();
    $_cover  = \App\Models\QuestionTemplate::query()
        ->join('topics', 'topics.id', '=', 'question_templates.topic_id')
        ->selectRaw('topics.grade_id as grade_id, question_templates.difficulty as difficulty, count(*) as c')
        ->groupBy('topics.grade_id', 'question_templates.difficulty')
        ->get()
        ->groupBy('grade_id');
    $_emptyTopics = \App\Models\Topic::doesntHave('questionTemplates')->selectRaw('grade_id, count(*) as c')->groupBy('grade_id')->pluck('c', 'grade_id');
}
@endphp

<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">{{ now()->locale('ka')->translatedFormat('l, j F') }}</span>
        <h1 class="ks-display ks-h1">ადმინ პანელი</h1>
    </div>
    @if($isAdmin || in_array('admin.questions', \App\Models\RolePermission::allowedPages(auth()->user()->role) ?? []))
    <a href="{{ route('admin.questions.create') }}" class="ks-btn ks-btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        ახალი შაბლონი
    </a>
    @endif
</header>

@if($isAdmin)
<div class="ks-kpis">
    <div class="ks-kpi"><span class="ks-kpi-label">მომხმარებელი</span><span class="ks-kpi-value">{{ number_format($_kUsers, 0, '.', ' ') }}</span><span class="ks-kpi-note up">+{{ $_kNewWeek }} ბოლო 7 დღეში</span></div>
    <div class="ks-kpi"><span class="ks-kpi-label">ბავშვი</span><span class="ks-kpi-value">{{ number_format($_kChildren, 0, '.', ' ') }}</span><span class="ks-kpi-note">რეგისტრირებული</span></div>
    <div class="ks-kpi"><span class="ks-kpi-label">ტესტი დღეს</span><span class="ks-kpi-value">{{ number_format($_kTestsDay, 0, '.', ' ') }}</span><span class="ks-kpi-note">დასრულებული</span></div>
    <div class="ks-kpi"><span class="ks-kpi-label">ფასიანი გამოწერა</span><span class="ks-kpi-value">{{ number_format($_kPaid, 0, '.', ' ') }}</span><span class="ks-kpi-note">{{ $_kExpiring }} იწურება 7 დღეში</span></div>
</div>

{{-- „დაბრუნება“ — რამდენ დღეს ბრუნდება ბავშვი კვირაში (child_activity_days) --}}
@if($retention)
@php
    $_r = $retention;
    $_dMax = max(1, max($_r['dist']));
    $_wMax = max(1, max(array_column($_r['weeks'], 'kids')));
@endphp
<style>
    .rt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
    .rt-stat { background: #F7F6F3; border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 4px; }
    .rt-stat b { font-size: 26px; font-weight: 900; color: #2B2622; line-height: 1.1; }
    .rt-stat span { font-size: 13px; font-weight: 800; color: #4F473F; }
    .rt-stat small { font-size: 12px; color: #6B625A; line-height: 1.4; }
    .rt-stat.main { background: #FFF3EA; }
    .rt-stat.main b { color: #C24A0A; }
    .rt-charts { display: flex; flex-wrap: wrap; gap: 20px; }
    .rt-chart { flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 10px; }
    .rt-chart h3 { margin: 0; font-size: 14px; font-weight: 900; color: #2B2622; }
    .rt-bars { display: flex; align-items: flex-end; gap: 8px; height: 140px; }
    .rt-bar { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 4px; height: 100%; min-width: 0; }
    .rt-bar i { display: block; width: 100%; border-radius: 8px 8px 4px 4px; background: #E2590F; min-height: 3px; }
    .rt-bar i.soft { background: #FFC49A; }
    .rt-bar em { font-style: normal; font-size: 12px; font-weight: 800; color: #2B2622; }
    .rt-bar span { font-size: 11px; color: #6B625A; white-space: nowrap; }
</style>
<section class="ks-card" style="display:flex;flex-direction:column;gap:16px;">
    <div style="display:flex;justify-content:space-between;align-items:baseline;gap:10px;flex-wrap:wrap;">
        <h2 class="ks-h2">დაბრუნება</h2>
        <span style="font-size:13px;color:var(--ks-muted);">რამდენ დღეს ბრუნდება ბავშვი კვირაში · ბოლო 7 დღე</span>
    </div>
    <div class="rt-grid">
        <div class="rt-stat main">
            <span>საშუალოდ კვირაში</span>
            <b>{{ $_r['avg_days'] }} დღე</b>
            <small>აქტიური ბავშვი ამდენ დღეს შემოდის 7-დან</small>
        </div>
        <div class="rt-stat">
            <span>აქტიური ბავშვი</span>
            <b>{{ $_r['active7'] }}</b>
            <small>დღეს {{ $_r['active_today'] }} · 30 დღეში {{ $_r['active30'] }} · სულ {{ $_r['children'] }}</small>
        </div>
        <div class="rt-stat">
            <span>დაბრუნდა ამ კვირასაც</span>
            <b>{{ $_r['wow']['pct'] !== null ? $_r['wow']['pct'] . '%' : '—' }}</b>
            <small>წინა კვირის {{ $_r['wow']['prev'] }} აქტიურიდან {{ $_r['wow']['back'] }} ამ კვირასაც შემოვიდა</small>
        </div>
        <div class="rt-stat">
            <span>ახლები — მეორე კვირაში</span>
            <b>{{ $_r['new']['pct'] !== null ? $_r['new']['pct'] . '%' : '—' }}</b>
            <small>8–35 დღის წინ დარეგისტრირებული {{ $_r['new']['total'] }}-დან {{ $_r['new']['back'] }} დაბრუნდა 7 დღის შემდეგ</small>
        </div>
    </div>
    <div class="rt-charts">
        <div class="rt-chart">
            <h3>რამდენ დღეს შემოვიდნენ ბოლო 7 დღეში</h3>
            <div class="rt-bars" role="img" aria-label="ბავშვების განაწილება აქტიური დღეების მიხედვით">
                @foreach($_r['dist'] as $_d => $_n)
                <div class="rt-bar"><em>{{ $_n }}</em><i class="{{ $_d < 3 ? 'soft' : '' }}" style="height: {{ round($_n / $_dMax * 100) }}%;"></i><span>{{ $_d }} დღე</span></div>
                @endforeach
            </div>
        </div>
        <div class="rt-chart">
            <h3>აქტიური ბავშვები კვირების მიხედვით</h3>
            <div class="rt-bars" role="img" aria-label="აქტიური ბავშვები ბოლო 8 კვირაში">
                @foreach($_r['weeks'] as $_w)
                <div class="rt-bar" title="საშუალოდ {{ $_w['avg'] }} დღე"><em>{{ $_w['kids'] }}</em><i style="height: {{ round($_w['kids'] / $_wMax * 100) }}%;"></i><span>{{ $_w['label'] }}</span></div>
                @endforeach
            </div>
            <small style="font-size:12px;color:var(--ks-muted);">თითო სვეტი — 7 დღე, ქვემოთ კვირის ბოლო დღე. მიიტანე მაუსი: საშუალო დღეები.</small>
        </div>
    </div>
</section>
@endif

<section style="display:flex;flex-wrap:wrap;gap:20px;">
    <div class="ks-card" style="flex:2 1 460px;min-width:0;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <h2 class="ks-h2">კონტენტი კლასების მიხედვით</h2>
            <a href="{{ route('admin.topics.index') }}" style="font-size:13px;font-weight:700;color:var(--ks-orange-dark);">თემების მართვა</a>
        </div>
        <div class="ks-table-wrap">
            <table class="ks-table" style="min-width:520px;">
                <thead><tr><th>კლასი</th><th>თემა</th><th>შაბლონი</th><th>დაფარვა D1–D5</th><th>ყურადღება</th></tr></thead>
                <tbody>
                @forelse($_grades as $_g)
                    @php
                        $_byDiff  = ($_cover[$_g->id] ?? collect())->pluck('c', 'difficulty');
                        $_tplSum  = $_byDiff->sum();
                        $_maxLvl  = $_g->max_level ?: 5;
                        $_missing = collect(range(1, $_maxLvl))->filter(fn($d) => !($_byDiff[$d] ?? 0));
                        $_empty   = $_emptyTopics[$_g->id] ?? 0;
                    @endphp
                    <tr>
                        <td style="font-weight:800;">{{ $_g->name }} @unless($_g->is_active)<span class="ks-chip" style="margin-left:6px;">გათიშული</span>@endunless</td>
                        <td>{{ $_g->topics_count }}</td>
                        <td>{{ $_tplSum }}</td>
                        <td>
                            <span style="display:flex;gap:4px;">
                            @for($d = 1; $d <= 5; $d++)
                                @php $_has = ($_byDiff[$d] ?? 0) > 0; $_out = $d > $_maxLvl; @endphp
                                <span title="დონე {{ $d }}: {{ $_out ? 'კლასის ზღვარს გარეთ' : ($_byDiff[$d] ?? 0).' შაბლონი' }}"
                                      style="width:22px;height:22px;border-radius:6px;background:{{ $_out ? 'transparent' : ($_has ? '#F07A2E' : '#ECE9E4') }};{{ $_out ? 'border:1px dashed #E3D9CF;' : '' }}"></span>
                            @endfor
                            </span>
                        </td>
                        <td>
                            @if($_empty)
                                <span class="ks-chip ks-chip-orange">{{ $_empty }} თემა შაბლონის გარეშე</span>
                            @elseif($_missing->count())
                                <span class="ks-chip ks-chip-orange">ცარიელი: D{{ $_missing->implode(', D') }}</span>
                            @else
                                <span class="ks-chip ks-chip-green">ყველაფერი რიგზეა</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ks-muted">კლასი ჯერ არ არის დამატებული</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form id="pushForm" class="ks-card" style="flex:1 1 300px;display:flex;flex-direction:column;gap:10px;">
        @csrf
        <h2 class="ks-h2">Push შეტყობინება</h2>
        <div class="ks-field"><label class="ks-label" for="pTitle">სათაური</label><input type="text" class="ks-input" id="pTitle" required></div>
        <div class="ks-field"><label class="ks-label" for="pBody">ტექსტი</label><textarea class="ks-textarea" id="pBody" rows="2" placeholder="მაგ: ახალი თემა დაემატა — სცადე!" required></textarea></div>
        <div class="ks-field"><label class="ks-label" for="pUrl">ბმული <span class="ks-muted" style="font-weight:500;">(სურვილისამებრ)</span></label><input type="text" class="ks-input" id="pUrl" placeholder="/"></div>
        <div class="ks-field">
            <label class="ks-label" for="pUser">ვის</label>
            <select class="ks-select" id="pUser">
                <option value="">ყველა მომხმარებელი</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="ks-btn ks-btn-dark" style="margin-top:4px;">გაგზავნა</button>
        <span class="ks-muted" style="font-size:12px;">Push-ის გამომწერი: {{ $subscriptions->count() }}</span>
        <div id="pushResults"></div>
    </form>
</section>
@endif

<section style="display:flex;flex-wrap:wrap;gap:20px;">
    <div class="ks-card" style="flex:1 1 360px;display:flex;flex-direction:column;gap:12px;">
        <h2 class="ks-h2">სწრაფი მოქმედებები</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;">
            @foreach($_visibleMap as $_info)
            <a href="{{ !empty($_info['create']) && \Illuminate\Support\Facades\Route::has('admin.questions.create') ? route('admin.questions.create') : route($_info['route']) }}"
               style="text-decoration:none;color:var(--ks-ink);border-radius:16px;padding:14px;min-height:70px;box-sizing:border-box;display:flex;flex-direction:column;gap:4px;background:{{ $_info['bg'] }};">
                <span style="font-size:14px;font-weight:800;">{{ $_info['name'] }}</span>
                <span style="font-size:12px;color:var(--ks-ink-2);">{{ $_info['sub'] }}</span>
            </a>
            @endforeach
            <a href="{{ route('test.preview') }}" style="text-decoration:none;color:var(--ks-ink);border-radius:16px;padding:14px;min-height:70px;box-sizing:border-box;display:flex;flex-direction:column;gap:4px;background:#F1EFEB;">
                <span style="font-size:14px;font-weight:800;">ტესტის გადახედვა</span>
                <span style="font-size:12px;color:var(--ks-ink-2);">როგორ ხედავს ბავშვი</span>
            </a>
        </div>
    </div>

    @if($isAdmin)
    <div class="ks-card" style="flex:1 1 360px;display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2 class="ks-h2">ბოლო რეგისტრაციები</h2>
            @if(\Illuminate\Support\Facades\Route::has('admin.users.index'))
            <a href="{{ route('admin.users.index') }}" style="font-size:13px;font-weight:700;color:var(--ks-orange-dark);">ყველა</a>
            @endif
        </div>
        @php
            $_roleChip = ['admin' => ['ადმინი', 'ks-chip-dark'], 'parent' => ['მშობელი', 'ks-chip-purple'], 'child' => ['ბავშვი', 'ks-chip-orange'], 'teacher' => ['მასწავლებელი', 'ks-chip-blue'], 'staff' => ['სტაფი', 'ks-chip-blue']];
        @endphp
        @foreach($users->take(6) as $u)
        @php $hasSub = $subscriptions->where('user_id', $u->id)->count() > 0; @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--ks-line-soft);">
            <span style="width:34px;height:34px;border-radius:50%;background:#F1EFEB;display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;">{{ mb_substr($u->name, 0, 1) }}</span>
            <span style="flex-grow:1;min-width:0;display:flex;flex-direction:column;">
                <span style="font-size:14px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->name }}</span>
                <span class="ks-muted" style="font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->email }}</span>
            </span>
            @if($hasSub)<span title="Push გამოწერილია" style="width:8px;height:8px;border-radius:50%;background:var(--ks-green);flex-shrink:0;"></span>@endif
            <span class="ks-chip {{ $_roleChip[$u->role][1] ?? '' }}">{{ $_roleChip[$u->role][0] ?? $u->role }}</span>
            <span class="ks-muted" style="font-size:12px;white-space:nowrap;">{{ $u->created_at?->format('d.m') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</section>

@if($isAdmin)
<script>
document.getElementById('pushForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = document.getElementById('pushResults');
    res.innerHTML = '<div class="ks-muted" style="font-size:13px;">იგზავნება…</div>';

    const resp = await fetch('{{ route("push.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            title:   document.getElementById('pTitle').value,
            body:    document.getElementById('pBody').value,
            url:     document.getElementById('pUrl').value || '/',
            user_id: document.getElementById('pUser').value || null,
        })
    });

    const data = await resp.json();
    if (!data.success) { res.innerHTML = '<span class="ks-chip ks-chip-red">შეცდომა</span>'; return; }

    if (!data.results || data.results.length === 0) {
        res.innerHTML = '<span class="ks-chip">გამომწერი არ არის</span>';
        return;
    }

    const okCount  = data.results.filter(r => r.success).length;
    const errCount = data.results.filter(r => !r.success).length;
    res.innerHTML = '<span class="ks-chip ks-chip-green">✓ ' + okCount + ' გაიგზავნა</span>'
        + (errCount > 0 ? ' <span class="ks-chip ks-chip-red">✗ ' + errCount + ' შეცდომა</span>' : '');
});
</script>
@endif
@endsection
