@extends('layouts.app')
@section('content')
<style>
    :root { --t-ink:#2B2622; }
    .wrap { max-width: 760px; margin: 0; padding: 0 0 60px; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: #2B2622; }

    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 10px; }
    .back { font-size: 0.86rem; font-weight: 800; color: #2B2622; text-decoration: none; padding: 8px 14px; background: white; border-radius: 14px; border: 1px solid #ECE9E4; }
    .back:hover { background: #FFF3EA; color: #2B2622; }
    .test-date { font-size: 0.86rem; font-weight: 700; color: #6B625A; margin-left: auto; }

    /* Summary card */
    .summary-card {
        position: relative; overflow: hidden;
        background: #F7883E; border-radius: 28px; padding: 24px 26px;
        color: white; margin-bottom: 28px;
        display: flex; align-items: center; gap: 20px;
    }
    .summary-card::before { content: ''; position: absolute; right: -60px; top: -80px; width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,.2); }
    .summary-card > * { position: relative; }
    .sum-icon { font-size: 3rem; width: 76px; height: 76px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .sum-name { font-size: 0.95rem; font-weight: 700; color: #3D1A04; margin-bottom: 4px; }
    .sum-score { font-size: 3rem; line-height: .95; font-weight: 900; font-stretch: 70%; }
    .sum-pct { display: inline-block; margin-top: 8px; font-size: 0.9rem; font-weight: 800; padding: 3px 14px; border-radius: 99px; }
    .pct-hi { background: #E8F7EE; color: #1E7A45; }
    .pct-mid { background: #FFF1C9; color: #8A5A00; }
    .pct-lo { background: #FDECEA; color: #B42318; }
    .pitch-strip { display: none; }

    /* Question cards */
    .q-card {
        background: white; border-radius: 26px;
        padding: 30px 22px 22px; margin-bottom: 24px;
        box-shadow: 0 2px 0 #ECE9E4; position: relative;
    }
    .q-badge {
        position: absolute; top: -14px; left: 20px;
        background: #C24A0A; color: white; font-weight: 900; font-size: 0.95rem;
        padding: 3px 16px; border-radius: 99px;
    }
    .q-card:nth-child(3n+2) .q-badge { background: #E2590F; }
    .q-card:nth-child(3n+3) .q-badge { background: #E2590F; }

    .q-text { font-size: clamp(1rem, 3vw, 1.15rem); font-weight: 700; color: #2B2622; line-height: 1.65; margin-bottom: 6px; }
    .q-hint { font-size: 0.86rem; color: #6B625A; margin-bottom: 14px; line-height: 1.5; border-left: 3px solid #FFE3CC; padding-left: 10px; }

    .opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
    .opt-row {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 12px 10px; border-radius: 18px; border: 2.5px solid #ECE9E4; background: #F7F6F3;
        font-weight: 900; font-size: clamp(1.05rem, 3.5vw, 1.25rem);
        color: #6B625A; text-align: center; min-height: 56px; position: relative;
    }
    .opt-row.correct   { background: #E8F7EE; border-color: #3FAE6E; color: #14532D; }
    .opt-row.wrong     { background: #FDECEA; border-color: #E5484D; color: #B42318; }
    .opt-row.missed    { background: #E8F7EE; border-color: #7FCB9C; color: #14532D; opacity: 0.6; }

    .opt-icon { font-size: 1rem; }
    .opt-tag { position: absolute; top: -10px; right: 8px; font-size: 0.66rem; font-weight: 800; padding: 1px 9px; border-radius: 99px; }
    .tag-correct { background: #1E8A4C; color: white; }
    .tag-wrong   { background: #C62828; color: white; }
    .tag-answer  { background: #E2590F; color: white; }

    .no-ans { font-size: 0.86rem; font-weight: 800; color: #8A5A00; margin-top: 10px; }

    .vid-btn { display:inline-flex; align-items:center; gap:6px; margin-top:12px; padding:8px 16px; background:#FFF3EA; border-radius:99px; font-weight:800; font-size:0.84rem; color:#A33E08; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
    .vid-btn:hover { background:#FFF3EA; }
    .vid-panel-q { margin-top:10px; display:none; }
    .vid-panel-q iframe { width:100%; aspect-ratio:16/9; border:none; border-radius:16px; }
    .vid-title-q { font-weight:700; font-size:0.84rem; color:#6B625A; margin-top:6px; }
    @media (max-width: 600px) { .summary-card { padding: 20px; border-radius: 24px; } .sum-score { font-size: 2.4rem; } .q-card { padding: 26px 16px 18px; } }
</style>

<div class="wrap">
    <div class="topbar">
        @if(!empty($isChild))
        <a href="{{ route('dashboard') }}" class="back">← მთავარი</a>
        @endif
        <span class="test-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</span>
    </div>

    @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
    <div class="summary-card">
        <div class="sum-icon">{{ $test->theme?->icon ?? '📝' }}</div>
        <div>
            <div class="sum-name">{{ $child->name }} · ტესტი #{{ $test->id }}</div>
            <div class="sum-score">{{ $test->correct_count }} / {{ $test->total_questions }}</div>
            <span class="sum-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">{{ $pct }}%</span>
        </div>
    </div>

    <div class="pitch-strip"></div>

    @php $themeIcon = ($test->theme && $test->theme->name !== 'სტანდარტი') ? $test->theme->icon : null; @endphp
    @foreach($questions as $i => $q)
    @php
        $answer   = $answers->get($q->id);
        $selected = $answer?->selected_answer;
        $correct  = $q->correct_answer;
        $qType = $q->question_type ?? 'multiple_choice';
        if ($qType === 'multiple_choice' && str_starts_with((string)$correct, '{')) {
            $caKeys = array_keys(json_decode($correct, true) ?? []);
            if (count($caKeys) && str_contains((string)($caKeys[0] ?? ''), ',')) {
                $qType = 'pyramid';
            } else {
                $qtJson = json_decode($q->question_text ?? '', true) ?? [];
                $qType  = ($qtJson['type'] ?? '') === 'crossword' ? 'crossword' : 'code';
            }
        }
    @endphp
    <div class="q-card">
        <div class="q-badge">{{ $themeIcon ? $themeIcon.' ' : '' }}{{ $i + 1 }}</div>

        @if($qType === 'pyramid')
        @php
            $pyrRows   = json_decode($q->question_text, true) ?? [];
            $solutions = json_decode($correct, true) ?? [];
            $userCells = $selected ? (json_decode($selected, true) ?? []) : [];
            $pyrOk     = $answer?->is_correct;
        @endphp
        <div class="q-text" style="margin-bottom:12px;">🔺 მათემატიკური პირამიდა</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div style="display:flex;flex-direction:column;align-items:center;gap:5px;margin-bottom:12px;">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:5px;">
                @foreach($row as $c => $val)
                @php
                    $pos    = "$r,$c";
                    $sol    = $solutions[$pos] ?? null;
                    $uv     = ($sol !== null && isset($userCells[$pos])) ? $userCells[$pos] : null;
                    $cellOk = $sol !== null && (int)($uv ?? PHP_INT_MIN) === $sol;
                @endphp
                @if($val !== null)
                <div style="width:44px;height:44px;border-radius:10px;background:#E2590F;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $val }}</div>
                @elseif($cellOk)
                <div style="width:44px;height:44px;border-radius:10px;background:#16a34a;color:white;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">{{ $sol }}</div>
                @elseif($uv !== null)
                <div style="width:54px;min-height:44px;border-radius:10px;background:#fee2e2;border:2px solid #ef4444;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2px 3px;gap:1px;">
                    <span style="color:#ef4444;font-size:0.78rem;font-weight:800;line-height:1;">{{ $uv }}</span>
                    <span style="color:#8A8078;font-size:0.6rem;line-height:1;">/</span>
                    <span style="color:#16a34a;font-size:0.78rem;font-weight:800;line-height:1;">{{ $sol }}</span>
                </div>
                @else
                <div style="width:44px;height:44px;border-radius:10px;border:2px dashed #fca5a5;background:#fff1f2;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;">
                    <span style="color:#8A8078;font-size:0.62rem;line-height:1;">—</span>
                    <span style="color:#16a34a;font-size:0.72rem;font-weight:800;line-height:1;">{{ $sol }}</span>
                </div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
        @if($pyrOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;">❌ არასწორი — წითელი = შეცდომა</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @elseif($qType === 'crossword')
        @php
            $cwQ = json_decode($q->question_text, true) ?? [];
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
            $cwAns    = json_decode($correct, true) ?? [];
            $userInps = $selected ? (json_decode($selected, true) ?? []) : [];
            $cwOk     = $answer?->is_correct;
        @endphp
        <div class="q-text" style="margin-bottom:10px;">🧮 კროსვორდი</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        @php
        $_rops=$cwQ['row_ops']??[];$_cops=$cwQ['col_ops']??[];
        $_rres=$cwQ['row_results']??[];$_cres=$cwQ['col_results']??[];
        $_cp=[];for($i=0;$i<$cwC;$i++){if($i>0)$_cp[]='20px';$_cp[]='52px';}$_cp[]='20px';$_cp[]='52px';
        $_rp=[];for($i=0;$i<$cwR;$i++){if($i>0)$_rp[]='14px';$_rp[]='52px';}$_rp[]='14px';$_rp[]='52px';
        @endphp
        <div style="overflow-x:auto;margin-bottom:10px;">
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
            $_gv=false; $_cv=null; $_uv=null; $_ok=false; $_bg=''; $_fg=''; $_bd='';
            if($_isDR&&$_isDC){
                $_pos=$_r*$cwC+$_c; $_gv=in_array($_pos,$cwRevealed);
                $_cv=$cwAns[(string)$_pos]??null; $_uv=$userInps[(string)$_pos]??null;
                $_ok=$_cv!==null&&(int)($_uv??PHP_INT_MIN)===(int)$_cv;
                if($_gv){$_bg='#d1fae5';$_fg='#065f46';$_bd='2.5px solid #6ee7b7';}
                elseif($selected!==null&&$_ok){$_bg='#dcfce7';$_fg='#15803d';$_bd='2px solid #86efac';}
                elseif($selected!==null&&$_uv!==null){$_bg='#fee2e2';$_fg='#991b1b';$_bd='2px solid #fca5a5';}
                else{$_bg='#E2590F';$_fg='white';$_bd='none';}
            }
        @endphp
        @if($_isDR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:{{ $_bg }};color:{{ $_fg }};border:{{ $_bd }};display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1rem;font-weight:800;line-height:1.1;">
                <span>{{ $_cv }}</span>
                @if(!$_gv&&$selected!==null&&!$_ok&&$_uv!==null)<span style="font-size:0.48rem;color:#ef4444;line-height:1;">{{ $_uv }}</span>@endif
            </div>
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
        @if($cwOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;">❌ არასწორი</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @elseif($qType === 'code')
        @php
            $codeQ    = json_decode($q->question_text, true) ?? [];
            $codeAns  = json_decode($correct, true) ?? [];
            $userInps = $selected ? (json_decode($selected, true) ?? []) : [];
            $codeOk   = $answer?->is_correct;
        @endphp
        <div class="q-text" style="margin-bottom:10px;">🕵️ კოდის გაშიფვრა</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div style="background:#fff8e7;border-radius:8px;padding:8px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;font-size:0.88rem;font-weight:700;color:#374151;">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div>{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.6rem;color:#8A8078;margin-bottom:6px;letter-spacing:0.08em;">სამიზნე კოდი</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            @php
                $cv   = $codeAns[$pos] ?? null;
                $uv   = $userInps[$pos] ?? null;
                $ok   = $cv !== null && (int)($uv ?? PHP_INT_MIN) === (int)$cv;
            @endphp
            <div style="text-align:center;">
                <div style="width:44px;height:44px;border-radius:8px;background:#E2590F;color:white;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:3px;">{{ $sym }}</div>
                @if($selected !== null)
                    @if($ok)
                    <div style="font-size:0.78rem;font-weight:900;color:#16a34a;">{{ $cv }}</div>
                    @elseif($uv !== null)
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0;">
                        <span style="color:#ef4444;font-size:0.75rem;font-weight:800;line-height:1.2;">{{ $uv }}</span>
                        <span style="color:#8A8078;font-size:0.55rem;line-height:1;">/</span>
                        <span style="color:#16a34a;font-size:0.75rem;font-weight:800;line-height:1.2;">{{ $cv }}</span>
                    </div>
                    @else
                    <div style="color:#f59e0b;font-size:0.75rem;font-weight:800;">— / {{ $cv }}</div>
                    @endif
                @else
                <div style="font-size:0.72rem;color:#16a34a;font-weight:800;">{{ $cv }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @if($codeOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;">❌ არასწორი</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @elseif(empty($q->options))
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q->question_text, true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q->question_text, true) ?? []) !!}
        @else
        <div class="q-text">{!! nl2br(e($q->question_text)) !!}</div>
        @endif
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        @php $numOk = $answer?->is_correct; @endphp
        <div style="display:flex;align-items:center;gap:10px;margin-top:14px;flex-wrap:wrap;">
            @if($selected !== null)
            <div style="font-size:0.68rem;color:#8A8078;">პასუხი:</div>
            <div style="font-weight:900;font-size:1rem;color:{{ $numOk ? '#16a34a' : '#ef4444' }};">{{ $selected }}</div>
            @endif
            @if(!$numOk)
            <div style="font-size:0.68rem;color:#8A8078;">სწორი:</div>
            <div style="font-weight:900;font-size:1rem;color:#16a34a;">{{ $correct }}</div>
            @endif
        </div>
        @if($numOk)
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;margin-top:8px;">✅ სწორი</div>
        @elseif($selected !== null)
        <div style="color:#ef4444;font-size:0.8rem;font-weight:700;margin-top:8px;">❌ არასწორი</div>
        @else
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif

        @else
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q->question_text, true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q->question_text, true) ?? []) !!}
        @else
        <div class="q-text">{!! nl2br(e($q->question_text)) !!}</div>
        @endif
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div class="opts">
            @foreach($q->options ?? [] as $opt)
            @php
                $isCor = $opt === $correct;
                $isSel = $opt === $selected;
                $cls   = $isSel && $isCor ? 'correct' : ($isSel && !$isCor ? 'wrong' : (!$isSel && $isCor ? 'missed' : ''));
            @endphp
            <div class="opt-row {{ $cls }}">
                <span class="opt-icon">
                    @if($isSel && $isCor) ✅
                    @elseif($isSel && !$isCor) ❌
                    @elseif(!$isSel && $isCor && $selected !== null) ⭕
                    @else &nbsp;
                    @endif
                </span>
                {{ $opt }}
                @if($isSel && $isCor)
                    <span class="opt-tag tag-correct">სწორი ✓</span>
                @elseif($isSel && !$isCor)
                    <span class="opt-tag tag-wrong">მისი პასუხი</span>
                @elseif(!$isSel && $isCor && $selected !== null)
                    <span class="opt-tag tag-answer">სწორი პასუხი</span>
                @endif
            </div>
            @endforeach
        </div>
        @if($selected === null)
        <div class="no-ans">⚠️ პასუხი არ გაუცია</div>
        @endif
        @endif

        @if(!empty($isChild) && $selected !== $correct)
        @php $topicVids = $q->template?->topic?->videos ?? collect(); @endphp
        @if($topicVids->isNotEmpty())
        <button class="vid-btn" onclick="toggleVidQ('vq{{ $q->id }}', this)">📹 ახსნა-ვიდეო</button>
        <div class="vid-panel-q" id="vq{{ $q->id }}">
            @foreach($topicVids as $vid)
            <div style="margin-bottom:10px;">
                <iframe src="{{ $vid->embedUrl() }}"
                    title="{{ $vid->title ?: $q->template?->topic?->name }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe>
                @if($vid->title)<div class="vid-title-q">{{ $vid->title }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>
    @endforeach
</div>
<script>
function toggleVidQ(id, btn) {
    const el = document.getElementById(id);
    const open = el.style.display === 'block';
    el.style.display = open ? 'none' : 'block';
    btn.textContent = open ? '📹 ახსნა-ვიდეო' : '📹 დახურვა';
}
</script>
@endsection
