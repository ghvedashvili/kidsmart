@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
:root {
    --t-ink: #1D1A17;
    --t-orange: #E2590F;
    --t-orange-s: #C24A0A;
    --t-purple: #6D4FE0;
    --t-blue: #2F6FD6;
}

body {
    font-family: 'Noto Sans Georgian', system-ui, sans-serif !important;
    background: #FFF8F1 !important;
    background-image: none !important;
    padding-bottom: 60px !important;
    color: var(--t-ink);
}
html { background: #FFF8F1 !important; background-image: none !important; }

/* Sticky to the viewport top — child pages render .child-top-bar instead of the
   fixed main nav, so nothing sits above this header once scrolled past it. */
.hdr {
    background: rgba(255,248,241,.94);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    padding: 10px 20px;
    position: sticky;
    top: 0;
    z-index: 50;
    border-bottom: 1px solid #F1E3D3;
    overflow: visible;
}
body.ks-child .hdr { border: 1px solid #F1E3D3; border-radius: 20px; margin: 0 auto; max-width: 720px; width: 100%; box-sizing: border-box; top: 8px; }
.hdr-in { max-width: 720px; margin: 0 auto; display: flex; align-items: center; gap: 14px; }
.hdr-lbl { font-size: 14px; font-weight: 800; white-space: nowrap; }
.prog-bar-wrap {
    position: relative;
    flex: 1;
    background: #F1E3D3;
    border-radius: 99px;
    height: 12px;
    margin: 8px 0;
    overflow: visible;
}
.prog-bar-fill {
    height: 100%;
    background: var(--t-orange);
    border-radius: 99px;
    transition: width 0.35s ease;
    width: 0%;
}
.prog-ball {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    width: 22px; height: 22px;
    border-radius: 50%;
    background: #fff;
    border: 4px solid var(--t-orange);
    box-sizing: border-box;
    transition: left 0.35s ease;
    pointer-events: none;
    z-index: 2;
    margin-left: -6px;
}

.wrap { max-width: 720px; margin: 0 auto; padding: 28px 16px 20px; }

.q-card {
    background: #fff; border-radius: 28px; padding: 30px 22px 22px;
    box-shadow: 0 2px 0 #F1E3D3;
    border: 2px solid transparent; position: relative;
    margin-bottom: 26px; transition: border-color 0.2s;
}
.q-card.answered { border-color: #BFE6CD; }

.q-badge {
    position: absolute; top: -14px; left: 20px;
    background: var(--t-orange-s); color: #fff;
    font-weight: 900; font-size: 0.95rem;
    padding: 3px 16px; border-radius: 99px;
}
.q-card:nth-child(3n+2) .q-badge { background: var(--t-purple); }
.q-card:nth-child(3n+3) .q-badge { background: var(--t-blue); }

.q-text { font-size: clamp(1.02rem, 3.4vw, 1.2rem); font-weight: 700; color: var(--t-ink); line-height: 1.65; margin-bottom: 6px; }
.q-hint { font-size: 0.86rem; color: #6B625A; margin-bottom: 14px; line-height: 1.5; border-left: 3px solid #FFE3CC; padding-left: 10px; }

.opts { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; }
.opt-lbl { position: relative; cursor: pointer; display: block; margin: 0; }
.opt-lbl input { position: absolute; opacity: 0; width: 0; height: 0; }
.opt-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    background: #FFF8F1; border: 2.5px solid #F1E3D3; border-radius: 18px;
    padding: 14px 10px; font-family: inherit; font-weight: 900;
    font-size: clamp(1.1rem, 4.2vw, 1.35rem); color: var(--t-ink); text-align: center;
    transition: all 0.15s; min-height: 62px; user-select: none;
}
.opt-lbl:hover .opt-btn { border-color: #FFC49A; transform: translateY(-2px); }
.opt-lbl input:focus-visible + .opt-btn { outline: 3px solid #FFC49A; outline-offset: 2px; }
.opt-lbl input:checked + .opt-btn {
    border-color: var(--t-ink); background: var(--t-ink); color: #fff;
    transform: translateY(-2px);
}

.check-mark {
    width: 30px; height: 30px; border-radius: 50%;
    background: #1E8A4C; color: #fff; font-size: 0.9rem; font-weight: 900;
    display: none; align-items: center; justify-content: center;
    position: absolute; top: -12px; right: -8px;
}
.q-card.answered .check-mark { display: flex; }

/* დასრულების ღილაკი ეკრანის ქვედა კიდეზეა მიმაგრებული და სქროლვისას არ მოძრაობს
   (sticky — მთელი ფორმის ფარგლებში; მობილურზე ქვედა მენიუს ზემოთ დგას) */
.submit-wrap {
    position: sticky; bottom: 16px; z-index: 60;
    max-width: 720px; margin: 0 auto; padding: 0 16px 16px;
}
body.ks-child .submit-wrap { bottom: 16px; }
@media (max-width: 900px) {
    body.ks-child .submit-wrap { bottom: calc(76px + env(safe-area-inset-bottom)); padding-bottom: 8px; }
}
.submit-btn.vis { box-shadow: 0 10px 28px rgba(29,26,23,.28); }
.warn { background: #fff; border-radius: 14px; padding: 8px 12px; }
.submit-btn {
    width: 100%; background: var(--t-ink);
    border: none; border-radius: 30px; color: #fff;
    font-family: inherit; font-weight: 800; font-size: 1.15rem;
    height: 60px; cursor: pointer; transition: background 0.2s;
    display: none;
}
.submit-btn.vis { display: block; animation: popIn 0.4s cubic-bezier(0.175,0.885,0.32,1.275); }
.submit-btn:hover { background: #000; }
@keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.warn { font-size: 0.9rem; color: #B42318; font-weight: 800; text-align: center; margin-top: 10px; display: none; }

.code-eq-box { background:#FFF6DC;border-radius:16px;padding:12px 16px;margin-bottom:12px;border:2px dashed #F2CF6B; }
.code-eq { font-size:clamp(0.95rem,3.5vw,1.1rem);font-weight:700;color:var(--t-ink);margin:3px 0; }
.code-target-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:8px; }
.code-sym-box { width:50px;height:50px;border-radius:14px;background:var(--t-purple);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.35rem;flex-shrink:0; }
.code-inp-row { display:flex;gap:8px;justify-content:center;flex-wrap:wrap; }
.code-inp { width:50px;height:50px;border-radius:14px;border:2.5px solid #C9BBFF;background:#EEE8FF;color:#4B33B8;font-family:inherit;font-weight:900;font-size:1.15rem;text-align:center;outline:none;padding:0; }
.code-inp:focus, .cw-inp:focus { border-color: var(--t-purple); }
.cw-grid { display:grid;gap:5px;align-items:center;justify-items:center;margin:0 auto 8px;max-width:max-content; }
.cw-inp { width:100%;height:100%;border:2.5px solid #C9BBFF;border-radius:14px;background:#EEE8FF;color:#4B33B8;font-family:inherit;font-weight:900;font-size:1.15rem;text-align:center;outline:none;padding:0; }
.cw-given { width:52px;height:52px;border-radius:14px;background:#DFF5E3;border:2.5px solid #9FD9B5;color:#14532D;font-family:inherit;font-size:1.15rem;display:flex;align-items:center;justify-content:center;font-weight:900; }
.cw-op { font-family:inherit;font-weight:900;font-size:1.15rem;color:#6B625A;text-align:center; }
.cw-eq { font-family:inherit;font-weight:900;font-size:1rem;color:#B5AAA0;text-align:center; }
.cw-res { width:100%;height:100%;border-radius:12px;background:#F5EFE8;display:flex;align-items:center;justify-content:center;font-family:inherit;font-size:1.05rem;color:var(--t-ink);font-weight:900; }
.num-ans-wrap { display:flex; justify-content:center; margin-top:20px; }
.num-ans-inp { width:160px; height:62px; border-radius:18px; border:2.5px solid #F1E3D3; background:#FFF8F1; color:var(--t-ink); font-family:inherit; font-weight:900; font-size:1.45rem; text-align:center; outline:none; padding:0 10px; transition:border-color .15s, box-shadow .15s; }
.num-ans-inp:focus { border-color:var(--t-orange); box-shadow:0 0 0 4px rgba(226,89,15,.15); }
@media (max-width: 600px) {
    .q-card { padding: 26px 16px 18px; border-radius: 24px; }
    .opts { gap: 10px; }
}
</style>

<form method="POST" action="{{ route('test.submit', $test) }}" id="testForm">
@csrf

<div class="hdr">
    <div class="hdr-in">
        <span class="hdr-lbl">{{ $test->is_olympiad ? '🏆 ოლიმპიადა' : 'ტესტი' }} · {{ count($questions) }} კითხვა</span>
        <div class="prog-bar-wrap">
            <div class="prog-bar-fill" id="progFill"></div>
            <div class="prog-ball" id="progBall"></div>
        </div>
    </div>
</div>

<div class="wrap">
    @php $themeIcon = ($test->theme && $test->theme->name !== 'სტანდარტი') ? $test->theme->icon : null; @endphp
    @foreach($questions as $i => $q)
    <div class="q-card" id="card-{{ $i }}">
        <div class="q-badge">{{ $themeIcon ? $themeIcon.' ' : '' }}{{ $i + 1 }}</div>
        <div class="check-mark">✓</div>
        @php
            $qType = $q->question_type ?? 'multiple_choice';
            if ($qType === 'multiple_choice' && str_starts_with((string)$q->correct_answer, '{')) {
                $caKeys = array_keys(json_decode($q->correct_answer, true) ?? []);
                if (count($caKeys) && str_contains((string)($caKeys[0] ?? ''), ',')) {
                    $qType = 'pyramid';
                } else {
                    $qtJson = json_decode($q->question_text ?? '', true) ?? [];
                    $qType  = ($qtJson['type'] ?? '') === 'crossword' ? 'crossword' : 'code';
                }
            }
        @endphp

        @if($qType === 'pyramid')
        @php $pyrRows = json_decode($q->question_text, true) ?? []; @endphp
        <div class="q-text" style="margin-bottom:16px;">შეავსე ცარიელი უჯრები</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;" id="pyr_{{ $q->id }}">
            @foreach($pyrRows as $r => $row)
            <div style="display:flex;gap:6px;">
                @foreach($row as $c => $val)
                @if($val === null)
                <input type="number"
                    name="pyramid_answers[{{ $q->id }}][{{ $r }},{{ $c }}]"
                    style="width:52px;height:52px;border-radius:12px;border:2.5px solid #C9BBFF;background:#EEE8FF;color:#4B33B8;font-family:inherit;font-weight:900;font-size:1.05rem;border-radius:14px;text-align:center;outline:none;padding:0;"
                    data-qid="{{ $q->id }}"
                    onchange="onPyrAnswer({{ $q->id }})">
                @else
                <div style="width:52px;height:52px;border-radius:12px;background:#6D4FE0;color:white;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.05rem;">{{ $val }}</div>
                @endif
                @endforeach
            </div>
            @endforeach
        </div>
        <input type="hidden" name="answers[{{ $q->id }}]" value="pyramid_submitted">

        @elseif($qType === 'code')
        @php $codeQ = json_decode($q->question_text, true) ?? []; @endphp
        <div class="q-text" style="margin-bottom:12px;">🕵️ გაშიფრე კოდი</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div class="code-eq-box">
            @foreach($codeQ['equations'] ?? [] as $eq)
            <div class="code-eq">{{ $eq }}</div>
            @endforeach
        </div>
        <div style="font-size:0.78rem;font-weight:800;color:#6B625A;text-align:center;margin-bottom:6px;">სამიზნე კოდი</div>
        <div class="code-target-row">
            @foreach($codeQ['target'] ?? [] as $sym)
            <div class="code-sym-box">{{ $sym }}</div>
            @endforeach
        </div>
        <div class="code-inp-row">
            @foreach($codeQ['target'] ?? [] as $pos => $sym)
            <input type="number" class="code-inp"
                name="code_answers[{{ $q->id }}][{{ $pos }}]"
                placeholder="?"
                data-qid="{{ $q->id }}"
                onchange="onCodeAnswer({{ $q->id }}, {{ $i }})">
            @endforeach
        </div>
        <input type="hidden" name="answers[{{ $q->id }}]" value="code_submitted">

        @elseif($qType === 'crossword')
        @php
            $cwQ = json_decode($q->question_text, true) ?? [];
            // Normalize old 2×2 format
            if (!isset($cwQ['row_ops'])) {
                $cwQ = ['type'=>'crossword','rows'=>2,'cols'=>2,
                    'row_ops'=>[$cwQ['op_r0']??'+',$cwQ['op_r1']??'+'],
                    'col_ops'=>[$cwQ['op_c0']??'+',$cwQ['op_c1']??'+'],
                    'row_results'=>[$cwQ['res_r0']??0,$cwQ['res_r1']??0],
                    'col_results'=>[$cwQ['res_c0']??0,$cwQ['res_c1']??0],
                ];
            }
            $cwR = (int)($cwQ['rows']??2); $cwC = (int)($cwQ['cols']??2);
            $cwRowOps = $cwQ['row_ops'] ?? array_fill(0,$cwR,'+');
            $cwColOps = $cwQ['col_ops'] ?? array_fill(0,$cwC,'+');
            $cwRowRes = $cwQ['row_results'] ?? [];
            $cwColRes = $cwQ['col_results'] ?? [];
            $cwTpl = []; for($ccc=0;$ccc<$cwC;$ccc++){$cwTpl[]='52px';if($ccc<$cwC-1)$cwTpl[]='24px';} $cwTpl[]='20px';$cwTpl[]='46px';
            $cwRTpl= []; for($rrr=0;$rrr<$cwR;$rrr++){$cwRTpl[]='52px';if($rrr<$cwR-1)$cwRTpl[]='24px';} $cwRTpl[]='20px';$cwRTpl[]='46px';
            $cwRevealed = $cwQ['revealed'] ?? [];
            $cwCorrect  = json_decode($q->correct_answer, true) ?? [];
        @endphp
        <div class="q-text" style="margin-bottom:14px;">🧮 შეავსე კროსვორდი</div>
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div style="display:grid;grid-template-columns:{{ implode(' ',$cwTpl) }};grid-template-rows:{{ implode(' ',$cwRTpl) }};gap:5px;align-items:center;justify-items:center;margin:0 auto 8px;max-width:max-content;">
        @for($cwDr=0;$cwDr<=2*$cwR;$cwDr++)
        @for($cwDc=0;$cwDc<=2*$cwC;$cwDc++)
        @php
            $cwIsInp=false;$cwCls='';$cwTxt='';$cwIdx=-1;
            if($cwDr<=2*$cwR-2){
                if($cwDr%2===0){
                    $cwr=intdiv($cwDr,2);
                    if($cwDc<=2*$cwC-2){if($cwDc%2===0){$cwIdx=$cwr*$cwC+intdiv($cwDc,2);$cwIsInp=true;}else{$cwCls='cw-op';$cwTxt=$cwRowOps[$cwr]??'+';}}
                    elseif($cwDc===2*$cwC-1){$cwCls='cw-eq';$cwTxt='=';}
                    else{$cwCls='cw-res';$cwTxt=$cwRowRes[$cwr]??'?';}
                }elseif($cwDc<=2*$cwC-2&&$cwDc%2===0){$cwCls='cw-op';$cwTxt=$cwColOps[intdiv($cwDc,2)]??'+';}
            }elseif($cwDr===2*$cwR-1&&$cwDc<=2*$cwC-2&&$cwDc%2===0){$cwCls='cw-eq';$cwTxt='=';}
            elseif($cwDr===2*$cwR&&$cwDc<=2*$cwC-2&&$cwDc%2===0){$cwCls='cw-res';$cwTxt=$cwColRes[intdiv($cwDc,2)]??'?';}
        @endphp
        @if($cwIsInp && in_array($cwIdx, $cwRevealed))
        <div class="cw-given">{{ $cwCorrect[$cwIdx] ?? '?' }}</div>
        <input type="hidden" name="crossword_answers[{{ $q->id }}][{{ $cwIdx }}]" value="{{ $cwCorrect[$cwIdx] ?? '' }}">
        @elseif($cwIsInp)<input type="number" class="cw-inp" name="crossword_answers[{{ $q->id }}][{{ $cwIdx }}]" placeholder="?" data-qid="{{ $q->id }}" onchange="onCrosswordAnswer({{ $q->id }}, {{ $i }})">
        @elseif($cwCls)<div class="{{ $cwCls }}">{{ $cwTxt }}</div>
        @else<div></div>
        @endif
        @endfor
        @endfor
        </div>
        <input type="hidden" name="answers[{{ $q->id }}]" value="crossword_submitted">

        @elseif(empty($q->options))
        @if($qType === 'scale')
        {!! \App\Services\ScaleService::renderPansHtml(json_decode($q->question_text, true) ?? []) !!}
        @elseif($qType === 'data_table')
        {!! \App\Services\DataTableService::renderTableHtml(json_decode($q->question_text, true) ?? []) !!}
        @else
        <div class="q-text">{!! nl2br(e($q->question_text)) !!}</div>
        @endif
        @if($q->hint_text)<div class="q-hint">{{ $q->hint_text }}</div>@endif
        <div class="num-ans-wrap">
            <input type="number" inputmode="numeric" class="num-ans-inp" name="answers[{{ $q->id }}]"
                data-qid="{{ $q->id }}" data-idx="{{ $i }}" placeholder="?"
                oninput="if(this.value.trim()!=='') onAnswer({{ $i }}, {{ $q->id }}, this.value)">
        </div>

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
            <label class="opt-lbl">
                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}"
                    data-qid="{{ $q->id }}" data-idx="{{ $i }}"
                    onchange="onAnswer({{ $i }}, {{ $q->id }}, this.value)">
                <div class="opt-btn">{{ $opt }}</div>
            </label>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</div>

<div class="submit-wrap">
    <button type="submit" class="submit-btn" id="submitBtn">
        ✓ პასუხების გაგზავნა
    </button>
    <div class="warn" id="warnMsg"></div>
</div>

</form>

<script>
const totalQ = {{ count($questions) }};
const CACHE_KEY = 'test_{{ $test->id }}';
let answeredCount = 0;
const answeredSet = new Set();

function onPyrAnswer(qid) {
    const inputs = document.querySelectorAll(`input[data-qid="${qid}"]`);
    const allFilled = Array.from(inputs).every(inp => inp.value.trim() !== '');
    if (!allFilled) return;
    // find card index via card id — pyramid cards have no radio so track separately
    const pyrCards = document.querySelectorAll('.q-card');
    pyrCards.forEach((card, i) => {
        if (card.querySelector(`[data-qid="${qid}"]`) && !answeredSet.has(i)) {
            answeredSet.add(i);
            answeredCount++;
            card.classList.add('answered');
            const pct = Math.round(answeredCount / totalQ * 100);
            document.getElementById('progFill').style.width  = pct + '%';
            document.getElementById('progBall').style.left   = pct === 0 ? '0' : 'calc(' + pct + '% - 5px)';
            if (answeredCount === totalQ) document.getElementById('submitBtn').classList.add('vis');
        }
    });
}

function onCodeAnswer(qid, cardIdx) {
    const inputs = document.querySelectorAll(`.code-inp[data-qid="${qid}"]`);
    const allFilled = Array.from(inputs).every(inp => inp.value.trim() !== '');
    if (!allFilled) return;
    if (!answeredSet.has(cardIdx)) {
        answeredSet.add(cardIdx);
        answeredCount++;
        document.getElementById('card-' + cardIdx).classList.add('answered');
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width  = pct + '%';
        document.getElementById('progBall').style.left   = pct === 0 ? '0' : 'calc(' + pct + '% - 5px)';
        if (answeredCount === totalQ) document.getElementById('submitBtn').classList.add('vis');
    }
}

function onCrosswordAnswer(qid, cardIdx) {
    const inputs = document.querySelectorAll(`.cw-inp[data-qid="${qid}"]`);
    const allFilled = Array.from(inputs).every(inp => inp.value.trim() !== '');
    if (!allFilled) return;
    if (!answeredSet.has(cardIdx)) {
        answeredSet.add(cardIdx);
        answeredCount++;
        document.getElementById('card-' + cardIdx).classList.add('answered');
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width  = pct + '%';
        document.getElementById('progBall').style.left   = pct === 0 ? '0' : 'calc(' + pct + '% - 5px)';
        if (answeredCount === totalQ) document.getElementById('submitBtn').classList.add('vis');
    }
}

function onAnswer(i, qid, val) {
    const cache = JSON.parse(localStorage.getItem(CACHE_KEY) || '{}');
    cache[qid] = val;
    localStorage.setItem(CACHE_KEY, JSON.stringify(cache));

    if (!answeredSet.has(i)) {
        answeredSet.add(i);
        answeredCount++;
        document.getElementById('card-' + i).classList.add('answered');
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width = pct + '%';
        document.getElementById('progBall').style.left = pct === 0 ? '0' : 'calc(' + pct + '% - 5px)';
    }
    if (answeredCount === totalQ) {
        document.getElementById('submitBtn').classList.add('vis');
    }
}

(function restoreCache() {
    const cache = JSON.parse(localStorage.getItem(CACHE_KEY) || '{}');
    Object.entries(cache).forEach(([qid, val]) => {
        const radio = document.querySelector(
            'input[type=radio][data-qid="' + qid + '"][value="' + CSS.escape(val) + '"]'
        );
        if (radio) {
            radio.checked = true;
            const idx = parseInt(radio.dataset.idx);
            if (!answeredSet.has(idx)) {
                answeredSet.add(idx);
                answeredCount++;
                document.getElementById('card-' + idx).classList.add('answered');
            }
            return;
        }
        const numInp = document.querySelector('.num-ans-inp[data-qid="' + qid + '"]');
        if (numInp) {
            numInp.value = val;
            const idx = parseInt(numInp.dataset.idx);
            if (!answeredSet.has(idx)) {
                answeredSet.add(idx);
                answeredCount++;
                document.getElementById('card-' + idx).classList.add('answered');
            }
        }
    });
    if (answeredCount > 0) {
        const pct = Math.round(answeredCount / totalQ * 100);
        document.getElementById('progFill').style.width = pct + '%';
        document.getElementById('progBall').style.left = pct === 0 ? '0' : 'calc(' + pct + '% - 5px)';
    }
    if (answeredCount === totalQ) {
        document.getElementById('submitBtn').classList.add('vis');
    }
})();

document.getElementById('testForm').addEventListener('submit', function() {
    localStorage.removeItem(CACHE_KEY);
});
</script>
@endsection
