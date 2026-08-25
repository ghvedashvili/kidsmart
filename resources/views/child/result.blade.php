<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>შედეგი</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Nunito', sans-serif; background: #f1f5f9; min-height: 100vh; }

.hero {
    padding: 40px 20px 32px;
    text-align: center;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
}
.score-circle {
    width: 130px; height: 130px;
    border-radius: 50%;
    border: 5px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.15);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    margin: 0 auto 20px;
    backdrop-filter: blur(4px);
}
.score-num {
    font-size: 2.8rem;
    font-weight: 900;
    line-height: 1;
}
.score-total {
    font-size: 0.85rem;
    font-weight: 700;
    opacity: 0.75;
}
.score-label {
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 6px;
    opacity: 0.9;
}
.score-pct {
    font-size: 0.82rem;
    opacity: 0.65;
    margin-bottom: 20px;
}
.stars {
    font-size: 1.8rem;
    letter-spacing: 6px;
    margin-bottom: 6px;
}
.theme-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.15); border-radius: 99px;
    padding: 5px 14px; font-size: 0.78rem; font-weight: 700;
}

/* Review */
.review-wrap { max-width: 640px; margin: 0 auto; padding: 20px 16px 80px; }
.section-title {
    font-size: 0.72rem; font-weight: 800; color: #94a3b8;
    letter-spacing: 0.1em; text-transform: uppercase;
    margin: 20px 0 12px;
}
.q-review {
    background: white; border-radius: 14px; padding: 16px 18px;
    margin-bottom: 10px;
    border-left: 4px solid #e2e8f0;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.q-review.correct { border-left-color: #16a34a; }
.q-review.wrong   { border-left-color: #ef4444; }
.q-review.skipped { border-left-color: #f59e0b; }
.q-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.q-badge {
    font-size: 0.68rem; font-weight: 900; padding: 2px 9px;
    border-radius: 99px; color: white;
}
.q-badge.correct { background: #16a34a; }
.q-badge.wrong   { background: #ef4444; }
.q-badge.skipped { background: #f59e0b; }
.q-text-r { font-size: 0.92rem; font-weight: 700; color: #334155; line-height: 1.5; margin-bottom: 8px; }
.ans-row { font-size: 0.8rem; font-weight: 700; }
.ans-correct { color: #16a34a; }
.ans-wrong   { color: #ef4444; }
.ans-note    { color: #64748b; }

/* Coins + achievements banner */
.reward-bar {
    max-width: 640px; margin: 0 auto;
    padding: 16px 16px 0;
}
.coins-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white; border-radius: 99px;
    padding: 10px 20px; font-size: 1rem; font-weight: 900;
    box-shadow: 0 4px 14px rgba(245,158,11,0.35);
    margin-bottom: 12px;
}
.ach-unlocked {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;
}
.ach-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white; border-radius: 99px;
    padding: 8px 16px; font-size: 0.78rem; font-weight: 800;
    box-shadow: 0 3px 10px rgba(99,102,241,0.3);
    animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes popIn {
    from { transform: scale(0.5); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}
.ach-pill:nth-child(2) { animation-delay: 0.1s; }
.ach-pill:nth-child(3) { animation-delay: 0.2s; }
.ach-pill:nth-child(4) { animation-delay: 0.3s; }

/* Bottom */
.bottom-btns {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: white; border-top: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex; gap: 8px; max-width: 640px; margin: 0 auto;
}
.btn-retry, .btn-home, .btn-hist {
    flex: 1; border-radius: 12px; padding: 13px;
    font-family: 'Nunito', sans-serif; font-size: 0.82rem; font-weight: 800;
    cursor: pointer; border: none; transition: all 0.18s;
    text-decoration: none; display: flex; align-items: center; justify-content: center;
}
.btn-retry {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    box-shadow: 0 4px 14px rgba(79,70,229,0.3);
}
.btn-retry:hover { transform: translateY(-1px); }
.btn-home { background: #f1f5f9; color: #64748b; }
.btn-home:hover { background: #e2e8f0; }
.btn-hist { background: #e0f2fe; color: #0284c7; }
.btn-hist:hover { background: #bae6fd; }
</style>
</head>
<body>

@php
    $correct = $test->correct_count;
    $total   = $test->total_questions;
    $pct     = $total > 0 ? round($correct / $total * 100) : 0;
    $stars   = $pct >= 90 ? 5 : ($pct >= 70 ? 4 : ($pct >= 50 ? 3 : ($pct >= 30 ? 2 : 1)));
@endphp

<div class="hero">
    <div class="score-circle">
        <div class="score-num">{{ $correct }}</div>
        <div class="score-total">/ {{ $total }}</div>
    </div>
    <div class="stars">{{ str_repeat('⭐', $stars) }}</div>
    <div class="score-label">
        @if($pct >= 90) გამოჩენილია! 🏆
        @elseif($pct >= 70) კარგი სამუშაო! 👏
        @elseif($pct >= 50) გააგრძელე! 💪
        @else მეცადე კიდევ! 📚
        @endif
    </div>
    <div class="score-pct">{{ $pct }}% სწორი</div>
    @if($test->theme)
    <div class="theme-badge">{{ $test->theme->icon }} {{ $test->theme->name }}</div>
    @endif
</div>

@if($achievement_result)
<div class="reward-bar">
    <div>
        <span class="coins-pill">
            💰 +{{ $achievement_result['coins'] }} მონეტა
            <span style="font-size:0.7rem;opacity:0.85;font-weight:700;">სულ: {{ $achievement_result['total_coins'] }}</span>
        </span>
    </div>
    @if(count($achievement_result['new_achievements']))
    <div class="ach-unlocked">
        @foreach($achievement_result['new_achievements'] as $ach)
        <span class="ach-pill">{{ $ach['emoji'] }} {{ $ach['name'] }}</span>
        @endforeach
    </div>
    @endif
    <a href="{{ route('achievements') }}" style="font-size:0.72rem;color:#6366f1;font-weight:700;text-decoration:none;display:inline-block;margin-bottom:8px;">🏆 ჩემი მიღწევები →</a>
</div>
@endif

<div class="review-wrap">
    <div class="section-title">კითხვების განხილვა</div>

    @foreach($questions as $i => $q)
    @php
        $ans    = $answers[$q->id] ?? null;
        $qType  = $q->question_type ?? 'multiple_choice';
        if ($qType === 'multiple_choice' && str_starts_with((string)$q->correct_answer, '{')) {
            $caKeys = array_keys(json_decode($q->correct_answer, true) ?? []);
            if (count($caKeys) && str_contains((string)($caKeys[0] ?? ''), ',')) {
                $qType = 'pyramid';
            } else {
                $qtJson = json_decode($q->question_text ?? '', true) ?? [];
                $qType  = ($qtJson['type'] ?? '') === 'crossword' ? 'crossword' : 'code';
            }
        }
        $status = $ans === null ? 'skipped' : ($ans->is_correct ? 'correct' : 'wrong');
    @endphp
    <div class="q-review {{ $status }}">
        <div class="q-header">
            <span class="q-badge {{ $status }}">
                @if($status === 'correct') ✓ სწორი
                @elseif($status === 'wrong') ✗ არასწორი
                @else — გამოტოვებული
                @endif
            </span>
            <span style="font-size:0.68rem;color:#94a3b8;font-weight:700;">{{ $i+1 }}</span>
        </div>
        @if($qType === 'pyramid')
        @php
            $pyrRows   = json_decode($q->question_text, true) ?? [];
            $solutions = json_decode($q->correct_answer, true) ?? [];
            $userCells = $ans ? (json_decode($ans->selected_answer, true) ?? []) : [];
        @endphp
        <div class="q-text-r" style="margin-bottom:10px;">🔺 პირამიდა</div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:4px;">
                @foreach($row as $c => $val)
                @php
                    $pos      = "$r,$c";
                    $hidden   = $val === null;
                    $sol      = $solutions[$pos] ?? null;
                    $userVal  = $userCells[$pos] ?? null;
                    $cellOk   = $hidden && $sol !== null && (int)($userVal ?? -1) === $sol;
                    $bg = $hidden ? ($cellOk ? '#dcfce7' : '#fee2e2') : '#4f46e5';
                    $color = $hidden ? ($cellOk ? '#15803d' : '#dc2626') : 'white';
                @endphp
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $bg }};color:{{ $color }};display:flex;align-items:center;justify-content:center;font-family:'Nunito',sans-serif;font-weight:900;font-size:0.85rem;">
                    {{ $hidden ? $sol : $val }}
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
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
            $correct  = json_decode($q->correct_answer, true) ?? [];
            $userInps = $ans ? (json_decode($ans->selected_answer, true) ?? []) : [];
        @endphp
        <div class="q-text-r" style="margin-bottom:10px;">🧮 კროსვორდი</div>
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
            // precompute cell vars (used only when $_isDR && $_isDC)
            $_gv=false; $_cv=null; $_uv=null; $_ok=false; $_bg=''; $_fg=''; $_bd='';
            if($_isDR&&$_isDC){
                $_pos=$_r*$cwC+$_c; $_gv=in_array($_pos,$cwRevealed);
                $_cv=$correct[(string)$_pos]??null; $_uv=$userInps[(string)$_pos]??null;
                $_ok=$_cv!==null&&(int)($_uv??PHP_INT_MIN)===(int)$_cv;
                if($_gv){$_bg='#d1fae5';$_fg='#065f46';$_bd='2.5px solid #6ee7b7';}
                elseif($_ok){$_bg='#dcfce7';$_fg='#15803d';$_bd='2px solid #86efac';}
                elseif($_uv!==null){$_bg='#fee2e2';$_fg='#991b1b';$_bd='2px solid #fca5a5';}
                else{$_bg='#4f46e5';$_fg='white';$_bd='none';}
            }
        @endphp
        @if($_isDR&&$_isDC)
            <div style="width:52px;height:52px;border-radius:10px;background:{{ $_bg }};color:{{ $_fg }};border:{{ $_bd }};display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1rem;font-weight:800;line-height:1.1;">
                <span>{{ $_cv }}</span>
                @if(!$_gv&&!$_ok&&$_uv!==null)<span style="font-size:0.48rem;color:#ef4444;line-height:1;">{{ $_uv }}</span>@endif
            </div>
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
            $codeQ    = json_decode($q->question_text, true) ?? [];
            $correct  = json_decode($q->correct_answer, true) ?? [];
            $userInps = $ans ? (json_decode($ans->selected_answer, true) ?? []) : [];
        @endphp
        <div class="q-text-r" style="margin-bottom:8px;">🕵️ კოდის გაშიფვრა</div>
        <div style="background:#fff8e7;border-radius:8px;padding:8px 12px;margin-bottom:10px;border:1.5px dashed #ffe194;font-size:0.85rem;font-weight:700;color:#374151;">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div>{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.6rem;color:#94a3b8;margin-bottom:4px;letter-spacing:0.08em;">სამიზნე კოდი</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            @php
                $cv   = $correct[$pos] ?? null;
                $uv   = $userInps[$pos] ?? null;
                $ok   = $cv !== null && (int)($uv ?? PHP_INT_MIN) === (int)$cv;
            @endphp
            <div style="text-align:center;">
                <div style="width:40px;height:40px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:3px;">{{ $sym }}</div>
                @if($ans)
                <div style="font-size:0.75rem;font-weight:900;color:{{ $ok ? '#15803d' : '#dc2626' }};">
                    @if(!$ok && $uv !== null)<span style="color:#dc2626;">{{ $uv }}</span><span style="color:#94a3b8;">/</span>@endif
                    <span style="color:#15803d;">{{ $cv }}</span>
                </div>
                @else
                <div style="font-size:0.75rem;color:#94a3b8;">?</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="q-text-r">{!! nl2br(e($q->question_text)) !!}</div>
        <div class="ans-row">
            @if($status === 'correct')
                <span class="ans-correct">✓ {{ $ans->selected_answer }}</span>
            @elseif($status === 'wrong')
                <span class="ans-wrong">✗ {{ $ans->selected_answer }}</span>
                <span class="ans-note"> → სწორი: <strong>{{ $q->correct_answer }}</strong></span>
            @else
                <span class="ans-note">პასუხი არ გასცემია · სწორი: <strong>{{ $q->correct_answer }}</strong></span>
            @endif
        </div>
        @endif
    </div>
    @endforeach
</div>

<div style="height:80px;"></div>
<div class="bottom-btns">
    <a href="{{ route('test.start') }}" class="btn-retry">ახალი ტესტი →</a>
    <a href="{{ route('my.test.show', $test) }}" class="btn-hist">📋 გადახედვა</a>
    <a href="{{ route('dashboard') }}" class="btn-home">🏠</a>
</div>

</body>
</html>
