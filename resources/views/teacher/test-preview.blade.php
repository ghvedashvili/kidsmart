@extends('layouts.app')
@section('content')
<style>
    /* redesign-kidsmart: ტესტის გადახედვა — ადმინის სტილი (ბარათები 20px, ნარინჯისფერი აქცენტი, ქართული შრიფტი) */
    .aw { max-width: 860px; margin: 0 auto; display: flex; flex-direction: column; gap: 14px; }
    .card-filter, .card-dark { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; padding: 18px; display: flex; flex-direction: column; gap: 12px; }
    .tp-sec-h { display: flex; align-items: center; gap: 10px; }
    .tp-sec-h .tp-ic { width: 38px; height: 38px; border-radius: 12px; background: #FFF3EA; color: #C24A0A; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .tp-sec-h b { font-size: 16px; font-weight: 900; color: #2B2622; display: block; }
    .tp-sec-h small { font-size: 13px; color: #6B625A; display: block; line-height: 1.4; }

    /* pill button groups */
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-lbl { font-size: 12px; font-weight: 800; color: #6B625A; }
    .pill-row { display: flex; flex-wrap: wrap; gap: 6px; }
    .pill-btn { position: relative; margin: 0; }
    .pill-btn input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .pill-btn span { display: inline-flex; align-items: center; min-height: 36px; padding: 6px 14px; border-radius: 999px; border: 1.5px solid #E6E2DC; background: #fff; font-size: 14px; font-weight: 800; color: #4F473F; cursor: pointer; transition: all .14s; user-select: none; white-space: nowrap; }
    .pill-btn span:hover { border-color: #FFC49A; color: #C24A0A; }
    .pill-btn:has(input:checked) span { border-color: #E2590F; background: #FFF3EA; color: #C24A0A; }
    .pill-btn:has(input:focus-visible) span { outline: 2px solid #FFC49A; outline-offset: 2px; }

    /* dropdowns + generate row */
    .dd-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .dd-row > select { flex: 1; min-width: 180px; }
    .fc-sm { background: #fff; border: 2px solid #ECE9E4; border-radius: 12px; color: #2B2622; font-family: inherit; font-size: 14px; padding: 9px 12px; outline: none; cursor: pointer; min-height: 42px; }
    .fc-sm:focus { border-color: #E2590F; box-shadow: 0 0 0 4px rgba(226, 89, 15, .12); }
    .btn-gen { display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: #E2590F; border: none; color: #fff; font-family: inherit; font-size: 14px; font-weight: 800; padding: 10px 18px; min-height: 42px; border-radius: 12px; cursor: pointer; transition: background .15s; white-space: nowrap; text-decoration: none; }
    .btn-gen:hover { background: #C24A0A; color: #fff; }
    .btn-gen:focus-visible { outline: 3px solid rgba(226, 89, 15, .35); outline-offset: 2px; }
    .btn-gen.soft { background: #FFF3EA; color: #C24A0A; }
    .btn-gen.soft:hover { background: #FFE3CC; color: #C24A0A; }

    /* question cards */
    .q-card { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; padding: 18px; }
    .q-num { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13px; font-weight: 900; color: #C24A0A; margin-bottom: 10px; }
    .q-num::before { content: ''; }
    .q-topic { display: inline-flex; font-size: 12px; font-weight: 800; color: #6B625A; background: #F1EFEB; border-radius: 999px; padding: 3px 10px; margin-left: 0; }
    .q-text { font-size: 17px; font-weight: 700; color: #2B2622; line-height: 1.6; margin-bottom: 12px; }
    .opts { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; margin-bottom: 8px; }
    .opt { display: flex; align-items: center; justify-content: center; text-align: center; min-height: 46px; font-size: 16px; font-weight: 800; padding: 8px 12px; border-radius: 14px; border: 2px solid #ECE9E4; color: #2B2622; background: #F7F6F3; }
    .opt.correct { background: #E5F6EC; border-color: #1E8A4C; color: #14612F; }
    .hint { font-size: 13px; color: #6B625A; border-left: 3px solid #FFE3CC; padding-left: 10px; margin-top: 4px; line-height: 1.5; }
    .err { font-size: 14px; font-weight: 700; color: #A12626; background: #FDE2E2; border-radius: 14px; padding: 12px 14px; }
    .result-meta { font-size: 14px; font-weight: 800; color: #6B625A; }
    .theme-tag { color: #2B2622; }

    /* child search dropdown */
    .child-search-wrap { position: relative; flex: 1; min-width: 220px; }
    .child-search-results { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid #ECE9E4; border-radius: 14px; box-shadow: 0 12px 32px rgba(43, 38, 34, .14); max-height: 260px; overflow-y: auto; margin-top: 4px; }
    .child-search-item { padding: 10px 14px; font-size: 14px; color: #2B2622; cursor: pointer; border-bottom: 1px solid #F1EFEB; }
    .child-search-item:last-child { border-bottom: none; }
    .child-search-item:hover { background: #FFF3EA; }
    .child-search-empty { padding: 10px 14px; font-size: 13px; color: #6B625A; }
    .child-selected-badge { display: none; font-size: 13px; font-weight: 700; color: #C24A0A; background: #FFF3EA; border-radius: 10px; padding: 6px 12px; }
    .tp-note { font-size: 13px; color: #6B625A; line-height: 1.5; }

    @media (max-width: 640px) {
        .card-filter, .card-dark, .q-card { padding: 14px; border-radius: 18px; }
        .btn-gen { width: 100%; }
        .q-text { font-size: 16px; }
    }
</style>

<div class="aw">
    <header class="ks-page-head">
        <div class="ks-page-title">
            <span class="ks-eyebrow">კითხვები</span>
            <h1 class="ks-display ks-h1">ტესტის გადახედვა</h1>
        </div>
    </header>
    <p class="ks-muted" style="margin:0;font-size:14px;">ნახე, როგორი ტესტი გენერირდება კლასის, თემისა და დონის მიხედვით, ან კონკრეტული ბავშვისთვის. აქედან არაფერი ინახება.</p>

    <div class="card-filter">
        <div class="tp-sec-h"><span class="tp-ic"><i class="bi bi-sliders"></i></span><div><b>ტესტის გენერაცია</b><small>აირჩიე კლასი, თემა, თემატიკა და დონე</small></div></div>
        <form method="GET" action="{{ route('test.preview') }}" style="display:flex;flex-direction:column;gap:12px;margin:0;">
            <div class="filter-group">
                <div class="filter-lbl">კლასი</div>
                <div class="pill-row">
                    @foreach($grades as $g)
                    <label class="pill-btn">
                        <input type="radio" name="grade_id" value="{{ $g->id }}"
                               {{ $selectedGradeId == $g->id ? 'checked' : '' }}
                               onchange="onGradeChange(this.value)">
                        <span>{{ $g->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="dd-row">
                <select name="topic_id" id="sel_topic" class="fc-sm" aria-label="თემა" onchange="onTopicChange(this.value)">
                    <option value="">— თემა —</option>
                </select>
                <select name="theme_id" id="sel_theme" class="fc-sm" aria-label="თემატიკა" onchange="onThemeChange(this.value)">
                    <option value="">— თემატიკა —</option>
                </select>
            </div>
            <div class="filter-group">
                <div class="filter-lbl">დონე</div>
                <div class="pill-row" id="diffPills">
                    @for($d = 1; $d <= 5; $d++)
                    <label class="pill-btn" data-diff="{{ $d }}">
                        <input type="radio" name="difficulty" value="{{ $d }}" {{ $selectedDiff == $d ? 'checked' : '' }}>
                        <span>{{ $d }}</span>
                    </label>
                    @endfor
                </div>
            </div>
            <div class="dd-row">
                <button type="submit" class="btn-gen"><i class="bi bi-shuffle"></i> გენერაცია</button>
            </div>
        </form>
    </div>

    <div class="card-filter">
        <div class="tp-sec-h"><span class="tp-ic"><i class="bi bi-person"></i></span><div><b>კონკრეტული ბავშვის ტესტი</b><small>ზუსტად ის, რასაც ეს ბავშვი ახლა ტესტის დაწყებისას მიიღებდა</small></div></div>
        <form method="GET" action="{{ route('test.preview') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:0;">
            <div class="child-search-wrap">
                <input type="text" id="childSearchInput" class="fc-sm" style="width:100%;box-sizing:border-box;" autocomplete="off"
                    placeholder="ძებნა სახელით, კოდით, კლასით…" aria-label="ბავშვის ძებნა" oninput="onChildSearchInput()"
                    onfocus="onChildSearchInput()" onblur="setTimeout(hideChildSearchResults, 150)">
                <div id="childSearchResults" class="child-search-results"></div>
            </div>
            <select name="child_id" id="childSelect" class="fc-sm" style="flex:1;min-width:160px;" aria-label="ბავშვი">
                <option value="">— ბავშვი —</option>
                @foreach($children as $c)
                @php
                    $gradeName = $c->childSetting?->grade?->name ?? 'კლასი არ არის';
                    $diffLabel = $c->childSetting?->difficulty ? ('დონე ' . $c->childSetting->difficulty) : null;
                    $themeNames = $c->themes->pluck('name')->implode(', ');
                    $metaParts = array_filter([$gradeName, $diffLabel, $themeNames ?: null]);
                    $label = $c->name . ' (' . implode(', ', $metaParts) . ')' . ($c->child_code ? ' — კოდი: ' . $c->child_code : '');
                    $searchStr = Str::lower($c->name . ' ' . $c->child_code . ' ' . $gradeName . ' ' . $themeNames);
                @endphp
                <option value="{{ $c->id }}" data-search="{{ $searchStr }}"
                    {{ (string) $selectedChildId === (string) $c->id ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn-gen"><i class="bi bi-eye"></i> ტესტის ნახვა</button>
        </form>
        <div id="childSelectedBadge" class="child-selected-badge"></div>
        <div class="tp-note">არაფერი ინახება — ბავშვის ისტორია და კითხვების მონაცვლეობა არ იცვლება.</div>
    </div>

    <div class="card-filter" style="flex-direction:row;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div class="tp-sec-h" style="flex:1;min-width:240px;"><span class="tp-ic"><i class="bi bi-shield-check"></i></span><div><b>შაბლონების შემოწმება</b><small>თუ რომელიმე აქტიური შაბლონის ფორმულა ვერ გამოითვლება, აქ გამოჩნდება</small></div></div>
        <form method="GET" action="{{ route('test.preview') }}" style="margin:0;">
            <input type="hidden" name="check_templates" value="1">
            <button type="submit" class="btn-gen soft"><i class="bi bi-search"></i> ყველას შემოწმება</button>
        </form>
    </div>

    @if($error)
    <div class="err">{{ $error }}</div>
    @endif

    @if($brokenTemplates !== null)
    <div class="card-dark">
        <div class="result-meta">🔍 შემოწმებულია ყველა აქტიური შაბლონი — {{ count($brokenTemplates) }} გატეხილი</div>
        @forelse($brokenTemplates as $b)
        <div class="q-card" style="border-color:#F5C2C2;background:#FFFAFA;">
            <div class="q-num">#{{ $b['template']->id }} <span class="q-topic">{{ $b['template']->topic->grade->name ?? '' }} / {{ $b['template']->topic->name ?? '' }}</span></div>
            <div class="q-text">{{ $b['template']->template_text ?: '(' . $b['template']->question_type . ')' }}</div>
            <div class="err" style="margin-bottom:10px;">⚠ {{ $b['error'] }}</div>
            <a href="{{ route('admin.questions.edit', $b['template']) }}" class="btn-gen soft"><i class="bi bi-pencil"></i> გასწორება</a>
        </div>
        @empty
        <div style="color:#14612F;background:#DFF5E3;border-radius:14px;padding:12px 14px;font-size:14px;font-weight:700;"><i class="bi bi-check2-circle"></i> ყველა აქტიური შაბლონი წესრიგშია</div>
        @endforelse
    </div>
    @endif

    @if($questions)
    <div class="result-meta">
        @if($selectedChild)<span class="theme-tag">👶 {{ $selectedChild->name }} · </span>@elseif($selectedTheme)<span class="theme-tag">🎨 {{ $selectedTheme->name }} · </span>@endif{{ $questions->count() }} კითხვა
    </div>
    @foreach($questions as $i => $q)
    @php
        $qType = $q['question_type'] ?? 'multiple_choice';
        if ($qType === 'multiple_choice' && str_starts_with((string)($q['correct_answer'] ?? ''), '{')) {
            $caKeys = array_keys(json_decode($q['correct_answer'], true) ?? []);
            if (count($caKeys) && str_contains((string)($caKeys[0] ?? ''), ',')) {
                $qType = 'pyramid';
            } else {
                $qtJson = json_decode($q['question_text'] ?? '', true) ?? [];
                $qType  = ($qtJson['type'] ?? '') === 'crossword' ? 'crossword' : 'code';
            }
        }
    @endphp
    <div class="q-card">
        <div class="q-num">კითხვა {{ $i + 1 }}<span class="q-topic">{{ $q['topic_name'] }}</span></div>
        @if($qType === 'pyramid')
        @php
            $pyrRows  = json_decode($q['question_text'], true) ?? [];
            $pyrSols  = json_decode($q['correct_answer'], true) ?? [];
        @endphp
        <div class="q-text" style="margin-bottom:12px;">🔺 მათემატიკური პირამიდა</div>
        @if(!empty($q['hint_text']))<div class="hint">{{ $q['hint_text'] }}</div>@endif
        <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:5px;">
                @foreach($row as $c => $val)
                @php $pos = "$r,$c"; $sol = $pyrSols[$pos] ?? null; @endphp
                @if($val === null)
                <div style="width:44px;height:44px;border-radius:10px;border:2px dashed #FFC49A;background:#FFF3EA;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#C24A0A;font-weight:700;">{{ $sol }}</div>
                @else
                <div style="width:44px;height:44px;border-radius:10px;background:#C24A0A;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $val }}</div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
        @elseif($qType === 'crossword')
        @php
            $cwQ = json_decode($q['question_text'], true) ?? [];
            if (!isset($cwQ['row_ops'])) {
                $cwQ = ['type'=>'crossword','rows'=>2,'cols'=>2,
                    'row_ops'=>[$cwQ['op_r0']??'+',$cwQ['op_r1']??'+'],
                    'col_ops'=>[$cwQ['op_c0']??'+',$cwQ['op_c1']??'+'],
                    'row_results'=>[$cwQ['res_r0']??0,$cwQ['res_r1']??0],
                    'col_results'=>[$cwQ['res_c0']??0,$cwQ['res_c1']??0],
                ];
            }
            $cwR = (int)($cwQ['rows']??2); $cwC = (int)($cwQ['cols']??2);
            $cwRevealed = $cwQ['revealed'] ?? [];
            $cwAns = json_decode($q['correct_answer'], true) ?? [];
        @endphp
        <div class="q-text" style="margin-bottom:10px;">🧮 კროსვორდი</div>
        @if(!empty($q['hint_text']))<div class="hint">{{ $q['hint_text'] }}</div>@endif
        @php
        $_rops=$cwQ['row_ops']??[];$_cops=$cwQ['col_ops']??[];
        $_rres=$cwQ['row_results']??[];$_cres=$cwQ['col_results']??[];
        $_cp=[];for($i=0;$i<$cwC;$i++){if($i>0)$_cp[]='20px';$_cp[]='52px';}$_cp[]='20px';$_cp[]='52px';
        $_rp=[];for($i=0;$i<$cwR;$i++){if($i>0)$_rp[]='14px';$_rp[]='52px';}$_rp[]='14px';$_rp[]='52px';
        @endphp
        <div style="overflow-x:auto;margin-bottom:8px;">
        <div style="display:grid;grid-template-columns:{{ implode(' ',$_cp) }};grid-template-rows:{{ implode(' ',$_rp) }};gap:4px;align-items:center;justify-items:center;max-width:max-content;padding:4px;">
        @for($dr=0;$dr<=$cwR*2;$dr++)
        @for($dc=0;$dc<=$cwC*2;$dc++)
        @php
            $_r=(int)($dr/2); $_c=(int)($dc/2);
            $_isDR=($dr%2===0&&$_r<$cwR); $_isDC=($dc%2===0&&$_c<$cwC);
            $_isOR=($dr%2===1&&(int)(($dr-1)/2)<$cwR-1);
            $_isOC=($dc%2===1&&(int)(($dc-1)/2)<$cwC-1);
            $_isEqR=($dr===2*$cwR-1); $_isEqC=($dc===2*$cwC-1);
            $_isRR=($dr===2*$cwR);    $_isRC=($dc===2*$cwC);
            $_gv=false; $_cv=''; $_bg=''; $_fg=''; $_bd='';
            if($_isDR&&$_isDC){
                $_pos=$_r*$cwC+$_c; $_gv=in_array($_pos,$cwRevealed);
                $_cv=$cwAns[(string)$_pos]??'?';
                $_bg=$_gv?'#d1fae5':'#C24A0A';
                $_fg=$_gv?'#065f46':'white';
                $_bd=$_gv?'2.5px solid #6ee7b7':'none';
            }
        @endphp
        @if($_isDR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:{{ $_bg }};color:{{ $_fg }};border:{{ $_bd }};display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;">{{ $_cv }}</div>
        @elseif($_isDR&&$_isOC)
            <div style="font-size:0.9rem;color:#6B625A;font-weight:700;">{{ $_rops[$_r]??'+' }}</div>
        @elseif($_isDR&&$_isEqC)
            <div style="font-size:0.75rem;color:#8A8078;">=</div>
        @elseif($_isDR&&$_isRC)
            <div style="width:52px;height:52px;border-radius:10px;background:#F1EFEB;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#374151;">{{ $_rres[$_r]??'' }}</div>
        @elseif($_isOR&&$_isDC)
            <div style="font-size:0.75rem;color:#6B625A;font-weight:700;">{{ $_cops[$_c]??'+' }}</div>
        @elseif($_isEqR&&$_isDC)
            <div style="font-size:0.75rem;color:#8A8078;">=</div>
        @elseif($_isRR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:#F1EFEB;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#374151;">{{ $_cres[$_c]??'' }}</div>
        @else
            <div></div>
        @endif
        @endfor
        @endfor
        </div></div>

        @elseif($qType === 'code')
        @php
            $codeQ   = json_decode($q['question_text'], true) ?? [];
            $codeAns = json_decode($q['correct_answer'], true) ?? [];
        @endphp
        <div class="q-text" style="margin-bottom:10px;">🕵️ კოდის გაშიფვრა</div>
        @if(!empty($q['hint_text']))<div class="hint">{{ $q['hint_text'] }}</div>@endif
        <div style="background:#fff8e7;border-radius:8px;padding:8px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;font-size:0.82rem;font-weight:700;color:#374151;">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div>{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.6rem;color:#8A8078;margin-bottom:6px;letter-spacing:0.08em;">სამიზნე კოდი</div>
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:4px;">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            @php $cv = $codeAns[$pos] ?? '?'; @endphp
            <div style="text-align:center;">
                <div style="width:40px;height:40px;border-radius:8px;background:#C24A0A;color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:3px;">{{ $sym }}</div>
                <div style="font-size:0.78rem;font-weight:800;color:#15803d;">{{ $cv }}</div>
            </div>
            @endforeach
        </div>
        @elseif(empty($q['options']))
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q['question_text'], true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q['question_text'], true) ?? []) !!}
        @else
        <div class="q-text">{!! nl2br(e($q['question_text'])) !!}</div>
        @endif
        <div style="margin-top:8px;font-size:0.85rem;font-weight:800;color:#15803d;">✍️ სწორი პასუხი: {{ $q['correct_answer'] }}</div>
        @if(!empty($q['hint_text']))
        <div class="hint">{{ $q['hint_text'] }}</div>
        @endif

        @else
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q['question_text'], true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q['question_text'], true) ?? []) !!}
        @else
        <div class="q-text">{!! nl2br(e($q['question_text'])) !!}</div>
        @endif
        <div class="opts">
            @foreach($q['options'] ?? [] as $opt)
            <span class="opt {{ $opt === $q['correct_answer'] ? 'correct' : '' }}">
                {{ $opt }}{{ $opt === $q['correct_answer'] ? ' ✓' : '' }}
            </span>
            @endforeach
        </div>
        @if(!empty($q['hint_text']))
        <div class="hint">{{ $q['hint_text'] }}</div>
        @endif
        @endif
    </div>
    @endforeach
    @endif
</div>

<script>
const _combos   = @json($templateCombos);
const _topics   = @json($topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'grade_id' => $t->grade_id])->values());
const _themes   = @json($themes->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'icon' => $t->icon ?? ''])->values());
const _preTopic = {{ $selectedTopicId ? (int)$selectedTopicId : 'null' }};
const _preTheme = {{ $selectedThemeId ? (int)$selectedThemeId : 'null' }};
const _preDiff  = {{ (int)$selectedDiff }};
</script>
@verbatim
<script>
function gradeId()  { const r = document.querySelector('input[name="grade_id"]:checked'); return r ? +r.value : null; }
function topicId()  { return +document.getElementById('sel_topic').value || null; }
function themeId()  { return +document.getElementById('sel_theme').value || null; }

function filteredCombos(gid, tid, thid) {
    return _combos.filter(c =>
        (!gid  || c.grade_id  == gid)  &&
        (!tid  || c.topic_id  == tid)  &&
        (!thid || c.theme_id  == thid)
    );
}

function updateTopics(gid) {
    const sel  = document.getElementById('sel_topic');
    const prev = topicId();
    const avail = new Set(filteredCombos(gid, null, null).map(c => c.topic_id));
    sel.innerHTML = '<option value="">— თემა —</option>';
    _topics.filter(t => !gid || t.grade_id == gid).forEach(t => {
        if (!avail.has(t.id)) return;
        const o = new Option(t.name, t.id, false, t.id === prev || t.id === _preTopic);
        sel.add(o);
    });
}

function updateThemes(gid, tid) {
    const sel  = document.getElementById('sel_theme');
    const prev = themeId();
    const avail = new Set(filteredCombos(gid, tid, null).map(c => c.theme_id).filter(Boolean));
    sel.innerHTML = '<option value="">— თემატიკა —</option>';
    _themes.filter(t => avail.has(t.id)).forEach(t => {
        const o = new Option((t.icon ? t.icon + ' ' : '') + t.name, t.id, false, t.id === prev || t.id === _preTheme);
        sel.add(o);
    });
}

function updateDiffs(gid, tid, thid) {
    const avail = new Set(filteredCombos(gid, tid, thid).map(c => c.difficulty));
    let anyChecked = false;
    document.querySelectorAll('#diffPills .pill-btn').forEach(pill => {
        const d = +pill.dataset.diff;
        const has = avail.has(d);
        pill.style.display = has ? '' : 'none';
        const inp = pill.querySelector('input');
        if (!has && inp.checked) inp.checked = false;
        if (inp.checked) anyChecked = true;
    });
    // auto-select first available diff if none selected
    if (!anyChecked && avail.size) {
        const first = Math.min(...avail);
        const inp = document.querySelector('#diffPills .pill-btn[data-diff="' + first + '"] input');
        if (inp) inp.checked = true;
    }
}

function onGradeChange(gid) {
    updateTopics(gid);
    updateThemes(gid, null);
    updateDiffs(gid, null, null);
}
function onTopicChange(tid) {
    const gid = gradeId();
    updateThemes(gid, tid || null);
    updateDiffs(gid, tid || null, null);
}
function onThemeChange(thid) {
    updateDiffs(gradeId(), topicId(), thid || null);
}

document.addEventListener('DOMContentLoaded', function () {
    const gid  = gradeId();
    updateTopics(gid);
    updateThemes(gid, _preTopic);
    // restore preselected topic
    if (_preTopic) document.getElementById('sel_topic').value = _preTopic;
    // restore preselected theme
    if (_preTheme) document.getElementById('sel_theme').value = _preTheme;
    updateDiffs(gid, _preTopic, _preTheme);
    // restore preselected difficulty
    if (_preDiff) {
        const inp = document.querySelector('#diffPills input[value="' + _preDiff + '"]');
        if (inp) inp.checked = true;
    }
});

// ── Child search dropdown — shows which children actually match (by name,
// login code, or grade) as a visible, clickable list, mirroring the
// template-search dropdown on the question-create page.
function escHtmlCp(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function onChildSearchInput() {
    renderChildSearchResults();
}
function renderChildSearchResults() {
    const box = document.getElementById('childSearchResults');
    const search = (document.getElementById('childSearchInput').value || '').trim().toLowerCase();
    if (!search) { box.style.display = 'none'; box.innerHTML = ''; return; }

    const sel = document.getElementById('childSelect');
    const matches = Array.from(sel.options)
        .filter(o => o.value && (o.dataset.search || '').includes(search))
        .slice(0, 40);

    box.innerHTML = matches.length
        ? matches.map(o =>
            `<div class="child-search-item" onclick="pickChildSearchResult(${o.value})">${escHtmlCp(o.textContent.trim())}</div>`
          ).join('')
        : '<div class="child-search-empty">არაფერი ვერ მოიძებნა</div>';
    box.style.display = 'block';
}
function hideChildSearchResults() {
    document.getElementById('childSearchResults').style.display = 'none';
}
function pickChildSearchResult(id) {
    const sel = document.getElementById('childSelect');
    const opt = Array.from(sel.options).find(o => String(o.value) === String(id));
    if (!opt) return;
    sel.value = String(id);
    document.getElementById('childSearchInput').value = '';
    hideChildSearchResults();
    const badge = document.getElementById('childSelectedBadge');
    badge.textContent = '✓ არჩეულია: ' + opt.textContent.trim();
    badge.style.display = 'block';
}
</script>
@endverbatim
@endsection
