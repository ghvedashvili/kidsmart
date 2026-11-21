{{-- მასწავლებელსა და ადმინს შორის მიმოწერა კითხვაზე. $history = teacher_data.history --}}
@php
    $__ev = [
        'submitted'   => ['გაგზავნა', 'bi-send'],
        'resubmitted' => ['შეასწორა და ხელახლა გაგზავნა', 'bi-arrow-repeat'],
        'rejected'    => ['დააბრუნა', 'bi-arrow-return-left'],
        'approved'    => ['დაადასტურა', 'bi-check2-circle'],
    ];
    $__hist = array_values(array_filter((array) ($history ?? []), 'is_array'));
@endphp
@if($__hist)
<div class="tqh" aria-label="მიმოწერა">
    @foreach($__hist as $__h)
    @php [$__l, $__i] = $__ev[$__h['event'] ?? ''] ?? ['', 'bi-dot']; $__adm = ($__h['by'] ?? '') === 'admin'; @endphp
    <div class="tqh-row {{ $__adm ? 'adm' : '' }} {{ ($__h['event'] ?? '') }}">
        <i class="bi {{ $__i }}" aria-hidden="true"></i>
        <div>
            <div class="tqh-meta"><b>{{ $__adm ? 'ადმინი' : 'მასწავლებელი' }}{{ !empty($__h['name']) ? ' · ' . $__h['name'] : '' }}</b> {{ $__l }} · {{ \Illuminate\Support\Carbon::parse($__h['at'] ?? now())->format('d.m.Y H:i') }}</div>
            @if(!empty($__h['text']))<div class="tqh-text">{{ $__h['text'] }}</div>@endif
        </div>
    </div>
    @endforeach
</div>
@once
<style>
    .tqh { display: flex; flex-direction: column; gap: 6px; }
    .tqh-row { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; }
    .tqh-row > i { width: 24px; height: 24px; border-radius: 50%; background: #FFF3EA; color: #C24A0A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 12px; }
    .tqh-row.adm > i { background: #F1EFEB; color: #4F473F; }
    .tqh-row.rejected > i { background: #FFF1C2; color: #6B4A00; }
    .tqh-row.approved > i { background: #DFF5E3; color: #14612F; }
    .tqh-meta { color: #6B625A; }
    .tqh-meta b { color: #2B2622; }
    .tqh-text { margin-top: 2px; background: #F7F6F3; border-radius: 10px; padding: 6px 10px; color: #2B2622; white-space: pre-line; }
</style>
@endonce
@endif
