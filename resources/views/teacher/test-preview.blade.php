@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 700px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-filter { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 18px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-dark   { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }

    /* pill button groups */
    .filter-group { margin-bottom: 10px; }
    .filter-lbl { font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 5px; }
    .pill-row { display: flex; flex-wrap: wrap; gap: 5px; }
    .pill-btn { position: relative; }
    .pill-btn input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .pill-btn span {
        display: block;
        font-family: 'Goldman', monospace;
        font-size: 0.72rem;
        padding: 4px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        color: #64748b;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.14s;
        user-select: none;
        white-space: nowrap;
    }
    .pill-btn:has(input:checked) span { background: #1e293b; border-color: #1e293b; color: #fff; }
    .pill-btn span:hover { border-color: #94a3b8; color: #1e293b; }

    /* dropdowns + generate row */
    .dd-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
    .fc-sm { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 5px 10px; outline: none; cursor: pointer; }
    .fc-sm:focus { border-color: #94a3b8; }
    .btn-gen { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 5px 18px; border-radius: 4px; cursor: pointer; transition: all 0.15s; white-space: nowrap; margin-left: auto; }
    .btn-gen:hover { border-color: #94a3b8; color: #1e293b; }

    /* question cards */
    .q-card { background: #fafcff; border: 1px solid #e8eef6; border-radius: 7px; padding: 16px 18px; margin-bottom: 12px; }
    .q-num { font-size: 0.62rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; }
    .q-topic { display: inline-block; font-size: 0.6rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 6px; margin-left: 6px; }
    .q-text { font-size: 0.86rem; color: #1e293b; line-height: 1.5; margin-bottom: 12px; }
    .opts { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 8px; }
    .opt { font-size: 0.78rem; padding: 6px 14px; border-radius: 4px; border: 1px solid #e2e8f0; color: #374151; background: #fff; }
    .opt.correct { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; font-weight: 600; }
    .hint { font-size: 0.7rem; color: #64748b; border-left: 2px solid #e2e8f0; padding-left: 9px; margin-top: 2px; }
    .err { font-size: 0.76rem; color: #ef4444; padding: 10px 0; }
    .result-meta { font-family:'Goldman',monospace; font-size:0.66rem; color:#94a3b8; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:12px; }
    .theme-tag { color:#374151; }

    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .card-filter { padding: 12px 14px; }
        .card-dark { padding: 14px; }
        .btn-gen { margin-left: 0; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:20px;">← back</a>

    <div class="card-filter">
        <form method="GET" action="{{ route('test.preview') }}">
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
            <div class="dd-row" style="margin-bottom:8px;">
                <select name="topic_id" id="sel_topic" class="fc-sm" onchange="onTopicChange(this.value)">
                    <option value="">— თემა —</option>
                </select>
                <select name="theme_id" id="sel_theme" class="fc-sm" onchange="onThemeChange(this.value)">
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
                <button type="submit" class="btn-gen" style="margin-left:0;">გენერაცია →</button>
            </div>
        </form>
    </div>

    @if($error)
    <div class="err">{{ $error }}</div>
    @endif

    @if($questions)
    <div class="result-meta">
        @if($selectedTheme)<span class="theme-tag">🎨 {{ $selectedTheme->name }} · </span>@endif{{ $questions->count() }} კითხვა
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
        <div class="q-num">{{ $i + 1 }}<span class="q-topic">{{ $q['topic_name'] }}</span></div>
        @if($qType === 'pyramid')
        @php
            $pyrRows  = json_decode($q['question_text'], true) ?? [];
            $pyrSols  = json_decode($q['correct_answer'], true) ?? [];
        @endphp
        <div class="q-text" style="margin-bottom:12px;">🔺 მათემატიკური პირამიდა</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:5px;">
                @foreach($row as $c => $val)
                @php $pos = "$r,$c"; $sol = $pyrSols[$pos] ?? null; @endphp
                @if($val === null)
                <div style="width:44px;height:44px;border-radius:10px;border:2px dashed #a5b4fc;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#4f46e5;font-weight:700;">{{ $sol }}</div>
                @else
                <div style="width:44px;height:44px;border-radius:10px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $val }}</div>
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
                $_bg=$_gv?'#d1fae5':'#4f46e5';
                $_fg=$_gv?'#065f46':'white';
                $_bd=$_gv?'2.5px solid #6ee7b7':'none';
            }
        @endphp
        @if($_isDR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:{{ $_bg }};color:{{ $_fg }};border:{{ $_bd }};display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;">{{ $_cv }}</div>
        @elseif($_isDR&&$_isOC)
            <div style="font-size:0.9rem;color:#64748b;font-weight:700;">{{ $_rops[$_r]??'+' }}</div>
        @elseif($_isDR&&$_isEqC)
            <div style="font-size:0.75rem;color:#94a3b8;">=</div>
        @elseif($_isDR&&$_isRC)
            <div style="width:52px;height:52px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#374151;">{{ $_rres[$_r]??'' }}</div>
        @elseif($_isOR&&$_isDC)
            <div style="font-size:0.75rem;color:#64748b;font-weight:700;">{{ $_cops[$_c]??'+' }}</div>
        @elseif($_isEqR&&$_isDC)
            <div style="font-size:0.75rem;color:#94a3b8;">=</div>
        @elseif($_isRR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#374151;">{{ $_cres[$_c]??'' }}</div>
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
        <div style="background:#fff8e7;border-radius:8px;padding:8px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;font-size:0.82rem;font-weight:700;color:#374151;">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div>{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.6rem;color:#94a3b8;margin-bottom:6px;letter-spacing:0.08em;">სამიზნე კოდი</div>
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:4px;">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            @php $cv = $codeAns[$pos] ?? '?'; @endphp
            <div style="text-align:center;">
                <div style="width:40px;height:40px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:3px;">{{ $sym }}</div>
                <div style="font-size:0.78rem;font-weight:800;color:#15803d;">{{ $cv }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="q-text">{!! nl2br(e($q['question_text'])) !!}</div>
        <div class="opts">
            @foreach($q['options'] as $opt)
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
</script>
@endverbatim
@endsection
