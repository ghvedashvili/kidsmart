{{-- ინტერაქტიული (და ნებისმიერი) კითხვის ბავშვის ხედი სწორი პასუხებით — იგივე რენდერი, რაც ადმინის „ტესტის გადახედვაში“ (teacher/test-preview).
     $q = QuestionTemplate::generate()-ის შედეგი + 'question_type' --}}
<div class="tq-kcard tqi">
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
@once
<style>
    .tqi .q-text { font-size: 17px; font-weight: 700; color: #2B2622; line-height: 1.6; margin-bottom: 12px; }
    .tqi .opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 14px 0 8px; }
    .tqi .opt { display: flex; align-items: center; justify-content: center; min-height: 50px; background: #F7F6F3; border: 2.5px solid #ECE9E4; border-radius: 16px; font-weight: 900; font-size: 17px; color: #2B2622; padding: 8px; text-align: center; }
    .tqi .opt.correct { border-color: #1E8A4C; background: #E5F6EC; color: #14612F; }
    .tqi .hint { font-size: 13px; color: #6B625A; margin-top: 8px; border-left: 3px solid #FFE3CC; padding-left: 10px; }
    .tqi > div[style*="overflow-x"] { max-width: 100%; }
</style>
@endonce
