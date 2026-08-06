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
        <span class="q-text">{{ $q['text'] }}</span>
    </div>
    <div class="q-tags">
        <span class="q-tag">{{ $q['grade'] }}</span>
        <span class="q-tag">{{ $q['topic'] }}</span>
        <span class="q-tag">დონე {{ $q['difficulty'] }}</span>
    </div>
    <div class="q-answer-row">
        <span class="q-answer-label">პასუხი:</span>
        <span class="q-answer">{{ $q['answer'] }}</span>
    </div>
</div>
@if(! $loop->last)<hr class="divider">@endif
@endforeach

<script>window.onload = () => window.print();</script>
</body>
</html>
