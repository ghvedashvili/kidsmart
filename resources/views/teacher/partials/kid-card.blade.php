{{-- ერთი კითხვა ისე, როგორც ბავშვი ხედავს. $s = ['text','hint','options','correct','input'] ; $showAnswer — სწორი პასუხის მონიშვნა --}}
<div class="tq-kcard">
    <div class="tq-ktext">{{ $s['text'] }}</div>
    @if(!empty($s['hint']))<div class="tq-khint">💡 {{ $s['hint'] }}</div>@endif
    @if(!empty($s['input']))
        <div class="tq-kinput"><span>{{ ($showAnswer ?? true) ? $s['correct'] : '?' }}</span></div>
    @else
        <div class="tq-kopts">
            @foreach($s['options'] as $__o)
            <div class="tq-kopt {{ ($showAnswer ?? true) && (string) $__o === (string) $s['correct'] ? 'ok' : '' }}">{{ $__o }}</div>
            @endforeach
        </div>
    @endif
</div>
