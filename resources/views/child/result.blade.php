<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>შედეგი</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wdth,wght@62.5..100,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Georgian', system-ui, sans-serif; background: #FFF8F1; min-height: 100vh; color: #1D1A17; }

.hero {
    position: relative; overflow: hidden;
    max-width: 760px; margin: 16px auto 0; border-radius: 32px;
    padding: 34px 24px 30px; text-align: center;
    background: #F7883E; color: #fff;
}
.hero::before { content: ''; position: absolute; right: -70px; top: -90px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,.2); }
.hero > * { position: relative; }
.score-circle {
    width: 136px; height: 136px; border-radius: 50%;
    background: #fff; color: #1D1A17;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    margin: 0 auto 16px; box-shadow: 0 6px 0 rgba(0,0,0,.08);
}
.score-num { font-size: 3rem; font-weight: 900; line-height: 1; }
.score-total { font-size: .9rem; font-weight: 800; color: #6B625A; }
.score-label { font-size: 2.6rem; line-height: .95; font-weight: 900; font-stretch: 66%; text-transform: uppercase; margin-bottom: 8px; }
.score-pct { font-size: .95rem; font-weight: 700; color: #3D1A04; margin-bottom: 14px; }
.stars { font-size: 1.7rem; letter-spacing: 4px; margin-bottom: 10px; }
.theme-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.85); color: #3D1A04; border-radius: 99px; padding: 6px 14px; font-size: .82rem; font-weight: 800; }

/* Review */
.review-wrap { max-width: 760px; margin: 0 auto; padding: 8px 16px 40px; }
.section-title { font-size: 1.35rem; font-weight: 800; color: #1D1A17; margin: 22px 0 12px; }
.q-review { background: #fff; border-radius: 22px; padding: 16px 18px; margin-bottom: 12px; border-left: 6px solid #EFE6DC; box-shadow: 0 2px 0 #F1E3D3; }
.q-review.correct { border-left-color: #3FAE6E; }
.q-review.wrong   { border-left-color: #E5484D; }
.q-review.skipped { border-left-color: #F5B301; }
.q-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.q-badge { font-size: .76rem; font-weight: 800; padding: 3px 11px; border-radius: 99px; color: #fff; }
.q-badge.correct { background: #1E8A4C; }
.q-badge.wrong   { background: #C62828; }
.q-badge.skipped { background: #F5B301; color: #3D2A00; }
.q-text-r { font-size: 1rem; font-weight: 700; color: #1D1A17; line-height: 1.55; margin-bottom: 8px; }
.ans-row { font-size: .9rem; font-weight: 700; }
.ans-correct { color: #1E7A45; }
.ans-wrong   { color: #B42318; }
.ans-note    { color: #6B625A; }

/* Coins + achievements banner */
.reward-bar { max-width: 760px; margin: 0 auto; padding: 16px 16px 0; display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
.coins-pill { display: inline-flex; align-items: center; gap: 8px; background: #1D1A17; color: #fff; border-radius: 99px; padding: 10px 20px; font-size: 1rem; font-weight: 900; box-shadow: 0 2px 0 #F1E3D3; }
.ach-unlocked { display: flex; flex-wrap: wrap; gap: 8px; }
.ach-pill { display: inline-flex; align-items: center; gap: 6px; background: #6D4FE0; color: #fff; border-radius: 99px; padding: 8px 16px; font-size: .84rem; font-weight: 800; animation: popIn .4s cubic-bezier(0.34,1.56,0.64,1) both; }
@keyframes popIn { from { transform: scale(.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.ach-pill:nth-child(2) { animation-delay: .1s; }
.ach-pill:nth-child(3) { animation-delay: .2s; }
.ach-pill:nth-child(4) { animation-delay: .3s; }

/* Bottom */
.bottom-btns {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #fff; border-top: 1px solid #F1E3D3;
    padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
    display: flex; gap: 8px; justify-content: center;
}
.btn-retry, .btn-home, .btn-hist {
    flex: 1; max-width: 300px; border-radius: 26px; height: 52px; padding: 0 16px;
    font-family: inherit; font-size: .95rem; font-weight: 800;
    cursor: pointer; border: none; transition: background .15s;
    text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.btn-retry { background: #1D1A17; color: #fff; }
.btn-retry:hover { background: #000; }
.btn-hist { background: #FFE3CC; color: #8A3407; }
.btn-hist:hover { background: #FFD2AE; }
.btn-home { background: #F5EFE8; color: #1D1A17; flex: 0 0 52px; padding: 0; }
.btn-home:hover { background: #EADFD3; }
@media (max-width: 600px) {
    .hero { margin: 12px 12px 0; border-radius: 28px; }
    .score-label { font-size: 2.1rem; }
}
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
    @if(($achievement_result['level_change'] ?? null) === 'up')
    <div style="margin-top:8px;">
        <span class="coins-pill" style="background:#dcfce7;color:#15803d;">🚀 დონე აიწია! ახლა დონე {{ $achievement_result['difficulty'] }}-ზე ხარ</span>
    </div>
    @elseif(($achievement_result['level_change'] ?? null) === 'down')
    <div style="margin-top:8px;">
        <span class="coins-pill" style="background:#fef3c7;color:#92400e;">💪 დონე შემცირდა {{ $achievement_result['difficulty'] }}-ზე — ივარჯიშე და დაბრუნდები!</span>
    </div>
    @endif
    @if(count($achievement_result['new_achievements']))
    <div class="ach-unlocked">
        @foreach($achievement_result['new_achievements'] as $ach)
        <span class="ach-pill">
            @if($ach['image'])<img src="{{ $ach['image'] }}" style="width:18px;height:18px;object-fit:contain;" alt="">@else 🏅 @endif
            {{ $ach['name'] }}
        </span>
        @endforeach
    </div>
    @endif
    <a href="{{ route('achievements') }}" style="font-size:0.72rem;color:#4B33B8;font-weight:800;text-decoration:none;display:inline-block;margin-bottom:4px;">ჩემი მედლები →</a>
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
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $bg }};color:{{ $color }};display:flex;align-items:center;justify-content:center;font-weight:900;font-size:0.85rem;">
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
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q->question_text, true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q->question_text, true) ?? []) !!}
        @else
        <div class="q-text-r">{!! nl2br(e($q->question_text)) !!}</div>
        @endif
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
    <a href="{{ route('test.start') }}" class="btn-retry">ახალი ტესტი <i class="bi bi-arrow-right"></i></a>
    <a href="{{ route('my.test.show', $test) }}" class="btn-hist"><i class="bi bi-clipboard-check"></i> გადახედვა</a>
    <a href="{{ route('dashboard') }}" class="btn-home" aria-label="მთავარი"><i class="bi bi-house-door" style="font-size:1.2rem;"></i></a>
</div>

</body>
</html>
