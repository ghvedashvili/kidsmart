<!doctype html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>კითხვები — KidSmart</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12pt; color: #111; background: #fff; padding: 20mm 18mm; }
    h1 { font-size: 14pt; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 6mm; color: #1e293b; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 4mm; }
    .meta { font-size: 9pt; color: #64748b; margin-bottom: 10mm; }
    .q-block { margin-bottom: 8mm; page-break-inside: avoid; }
    .q-header { display: flex; align-items: baseline; gap: 8px; margin-bottom: 2mm; }
    .q-num { font-size: 10pt; font-weight: 700; color: #94a3b8; min-width: 22px; flex-shrink: 0; }
    .q-text { font-size: 12pt; font-weight: 600; color: #111; line-height: 1.5; }
    .q-tags { display: flex; gap: 6px; margin-left: 30px; margin-bottom: 2mm; }
    .q-tag { font-size: 8pt; color: #94a3b8; border: 0.5px solid #e2e8f0; border-radius: 2px; padding: 1px 5px; }
    .q-answer-row { display: flex; align-items: center; gap: 8px; margin-left: 30px; }
    .q-answer-label { font-size: 9pt; color: #94a3b8; }
    .q-answer { font-size: 11pt; font-weight: 700; color: #059669; border: 1px solid #a7f3d0; border-radius: 4px; padding: 2px 10px; background: #f0fdf4; }
    .divider { border: none; border-top: 0.5px solid #f1f5f9; margin: 0 0 8mm; }

    @media print {
        body { padding: 15mm 14mm; }
        @page { margin: 0; }
        .no-print { display: none !important; }
    }
</style>
</head>
<body>

<div class="no-print" style="position:fixed;top:16px;right:16px;display:flex;gap:8px;">
    <button onclick="window.print()"
        style="background:#1e293b;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:11pt;cursor:pointer;font-family:sans-serif;">
        🖨 PDF / ბეჭდვა
    </button>
    <button onclick="window.close()"
        style="background:#f1f5f9;color:#555;border:none;border-radius:6px;padding:8px 14px;font-size:11pt;cursor:pointer;font-family:sans-serif;">
        ✕
    </button>
</div>

<h1>KidSmart — კითხვები</h1>
<div class="meta">{{ now()->format('d.m.Y') }} · {{ $questions->count() }} კითხვა</div>

@foreach($questions as $q)
<div class="q-block">
    <div class="q-header">
        <span class="q-num">{{ $q['num'] }}.</span>
    </div>
    <div class="q-tags">
        <span class="q-tag">{{ $q['grade'] }}</span>
        <span class="q-tag">{{ $q['topic'] }}</span>
        <span class="q-tag">დონე {{ $q['difficulty'] }}</span>
    </div>

    @if($q['broken'])
    <div class="q-text">{{ $q['text'] }}</div>

    @elseif($q['question_type'] === 'pyramid')
    @php
        $pyrRows = json_decode($q['text'], true) ?? [];
        $pyrSols = json_decode($q['answer'], true) ?? [];
    @endphp
    <div class="q-text" style="margin-bottom:8px;">🔺 მათემატიკური პირამიდა</div>
    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
        @foreach($pyrRows as $r => $row)
        <div style="display:flex;gap:4px;">
            @foreach($row as $c => $val)
            @php $pos = "$r,$c"; $sol = $pyrSols[$pos] ?? null; @endphp
            @if($val === null)
            <div style="width:36px;height:36px;border-radius:8px;border:1.5px dashed #a5b4fc;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:11pt;color:#4f46e5;font-weight:700;">{{ $sol }}</div>
            @else
            <div style="width:36px;height:36px;border-radius:8px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:11pt;font-weight:700;">{{ $val }}</div>
            @endif
            @endforeach
        </div>
        @endforeach
    </div>

    @elseif($q['question_type'] === 'code')
    @php
        $codeQ   = json_decode($q['text'], true) ?? [];
        $codeAns = json_decode($q['answer'], true) ?? [];
    @endphp
    <div class="q-text" style="margin-bottom:8px;">🕵️ კოდის გაშიფვრა</div>
    <div style="background:#fff8e7;border-radius:6px;padding:6px 10px;margin-bottom:8px;border:1.5px dashed #ffe194;font-size:11pt;font-weight:700;color:#374151;">
        @foreach($codeQ['equations'] ?? [] as $eq)
        <div>{{ $eq }}</div>
        @endforeach
    </div>
    <div style="font-size:8pt;color:#94a3b8;margin-bottom:5px;letter-spacing:0.05em;">სამიზნე კოდი</div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
        @foreach($codeQ['target'] ?? [] as $pos => $sym)
        @php $cv = $codeAns[$pos] ?? '?'; @endphp
        <div style="text-align:center;">
            <div style="width:32px;height:32px;border-radius:6px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:10pt;margin-bottom:2px;">{{ $sym }}</div>
            <div style="font-size:9pt;font-weight:800;color:#15803d;">{{ $cv }}</div>
        </div>
        @endforeach
    </div>

    @elseif($q['question_type'] === 'crossword')
    @php
        $cwQ = json_decode($q['text'], true) ?? [];
        $cwR = (int) ($cwQ['rows'] ?? 2);
        $cwC = (int) ($cwQ['cols'] ?? 2);
        $cwRevealed = $cwQ['revealed'] ?? [];
        $cwAns = json_decode($q['answer'], true) ?? [];
        $_rops = $cwQ['row_ops'] ?? []; $_cops = $cwQ['col_ops'] ?? [];
        $_rres = $cwQ['row_results'] ?? []; $_cres = $cwQ['col_results'] ?? [];
        $_cp = []; for ($i = 0; $i < $cwC; $i++) { if ($i > 0) $_cp[] = '16px'; $_cp[] = '40px'; } $_cp[] = '16px'; $_cp[] = '40px';
        $_rp = []; for ($i = 0; $i < $cwR; $i++) { if ($i > 0) $_rp[] = '12px'; $_rp[] = '40px'; } $_rp[] = '12px'; $_rp[] = '40px';
    @endphp
    <div class="q-text" style="margin-bottom:8px;">🧮 კროსვორდი</div>
    <div style="overflow-x:auto;">
    <div style="display:grid;grid-template-columns:{{ implode(' ', $_cp) }};grid-template-rows:{{ implode(' ', $_rp) }};gap:3px;align-items:center;justify-items:center;max-width:max-content;">
    @for($dr = 0; $dr <= $cwR * 2; $dr++)
    @for($dc = 0; $dc <= $cwC * 2; $dc++)
    @php
        $_r = (int) ($dr / 2); $_c = (int) ($dc / 2);
        $_isDR = ($dr % 2 === 0 && $_r < $cwR); $_isDC = ($dc % 2 === 0 && $_c < $cwC);
        $_isOR = ($dr % 2 === 1 && (int) (($dr - 1) / 2) < $cwR - 1);
        $_isOC = ($dc % 2 === 1 && (int) (($dc - 1) / 2) < $cwC - 1);
        $_isEqR = ($dr === 2 * $cwR - 1); $_isEqC = ($dc === 2 * $cwC - 1);
        $_isRR = ($dr === 2 * $cwR); $_isRC = ($dc === 2 * $cwC);
        $_gv = false; $_cv = ''; $_bg = ''; $_fg = ''; $_bd = '';
        if ($_isDR && $_isDC) {
            $_pos = $_r * $cwC + $_c; $_gv = in_array($_pos, $cwRevealed);
            $_cv = $cwAns[(string) $_pos] ?? '?';
            $_bg = $_gv ? '#d1fae5' : '#4f46e5';
            $_fg = $_gv ? '#065f46' : 'white';
            $_bd = $_gv ? '1.5px solid #6ee7b7' : 'none';
        }
    @endphp
    @if($_isDR && $_isDC)
        <div style="width:40px;height:40px;border-radius:8px;background:{{ $_bg }};color:{{ $_fg }};border:{{ $_bd }};display:flex;align-items:center;justify-content:center;font-size:10pt;font-weight:700;">{{ $_cv }}</div>
    @elseif($_isDR && $_isOC)
        <div style="font-size:9pt;color:#64748b;font-weight:700;">{{ $_rops[$_r] ?? '+' }}</div>
    @elseif($_isDR && $_isEqC)
        <div style="font-size:8pt;color:#94a3b8;">=</div>
    @elseif($_isDR && $_isRC)
        <div style="width:40px;height:40px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:10pt;font-weight:700;color:#374151;">{{ $_rres[$_r] ?? '' }}</div>
    @elseif($_isOR && $_isDC)
        <div style="font-size:8pt;color:#64748b;font-weight:700;">{{ $_cops[$_c] ?? '+' }}</div>
    @elseif($_isEqR && $_isDC)
        <div style="font-size:8pt;color:#94a3b8;">=</div>
    @elseif($_isRR && $_isDC)
        <div style="width:40px;height:40px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:10pt;font-weight:700;color:#374151;">{{ $_cres[$_c] ?? '' }}</div>
    @else
        <div></div>
    @endif
    @endfor
    @endfor
    </div></div>

    @elseif($q['question_type'] === 'scale')
    {!! \App\Services\ScaleService::renderPansHtml(json_decode($q['text'], true) ?? []) !!}
    <div style="margin-top:6px;font-size:11pt;font-weight:800;color:#15803d;">✍️ სწორი პასუხი: {{ $q['answer'] }}</div>

    @else
    <div class="q-text">{{ $q['text'] }}</div>
    <div class="q-answer-row">
        <span class="q-answer-label">პასუხი:</span>
        <span class="q-answer">{{ $q['answer'] }}</span>
    </div>
    @endif

    @if(!empty($q['hint_text']))
    <div style="font-size:9pt;color:#64748b;border-left:2px solid #e2e8f0;padding-left:8px;margin-top:6px;">{{ $q['hint_text'] }}</div>
    @endif
</div>
@if(! $loop->last)<hr class="divider">@endif
@endforeach

<script>window.onload = () => window.print();</script>
</body>
</html>
