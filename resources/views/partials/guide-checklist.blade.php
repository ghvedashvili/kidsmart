{{-- „პირველი ნაბიჯები“ — მშობლის მთავარზე, სანამ ყველაფერი არ შესრულდება ან მშობელი არ დახურავს. $children --}}
@php
    $__cu = auth()->user();
    $__cOk = $__cu->role === 'parent' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'guide_state');
    $__cs = (array) ($__cu->guide_state ?? []);
    $__cIds = $children->pluck('id');
    $__cKid = $children->first();
    $__cItems = $__cOk ? [
        ['შვილი დაამატე',                     $children->isNotEmpty(), route('dashboard', ['guide' => 'add'])],
        ['ბმული გაუგზავნე შვილს',              !empty($__cs['shared']), route('dashboard', ['guide' => 'share'])],
        ['გვერდი დაათვალიერე',                  ($__cs['tour'] ?? null) === 'done', route('dashboard', ['guide' => 'tour'])],
        ['შვილმა პირველი ტესტი დაწერა',        $__cIds->isNotEmpty() && \App\Models\Test::whereIn('child_id', $__cIds)->whereNotNull('completed_at')->exists(), null],
        ['მარკეტში პირველი ჯილდო დაამატე',     $__cIds->isNotEmpty() && \App\Models\MarketItem::whereIn('child_id', $__cIds)->exists(), $__cKid ? route('market.index', ['child' => $__cKid, 'guide' => 'reward']) : null],
    ] : [];
    $__cDone = collect($__cItems)->where(1, true)->count();
    $__cShow = $__cOk && empty($__cs['checklist_hidden']) && $__cDone < count($__cItems);
@endphp
@if($__cShow)
<section class="ksgc" id="ksgChecklist" aria-label="პირველი ნაბიჯები">
    <div class="ksgc-h">
        <span class="ksgc-fox">@include('partials.ks-fox', ['size' => 34, 'smile' => true])</span>
        <div class="ksgc-t"><b>პირველი ნაბიჯები</b><span>{{ $__cDone }} / {{ count($__cItems) }} შესრულებულია</span></div>
        <button type="button" class="ksgc-x" aria-label="დახურვა" title="დახურვა" id="ksgcClose"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="ksgc-bar" role="progressbar" aria-valuemin="0" aria-valuemax="{{ count($__cItems) }}" aria-valuenow="{{ $__cDone }}"><span style="width: {{ round($__cDone / count($__cItems) * 100) }}%"></span></div>
    <ul class="ksgc-list">
        @foreach($__cItems as [$__t, $__d, $__u])
        <li class="{{ $__d ? 'done' : '' }}">
            <span class="ksgc-ck" aria-hidden="true">@if($__d)<i class="bi bi-check-lg"></i>@endif</span>
            @if(!$__d && $__u)<a href="{{ $__u }}">{{ $__t }} <i class="bi bi-arrow-right"></i></a>@else<span>{{ $__t }}{{ $__d ? '' : '' }}</span>@endif
            @if($__d)<span class="ksgc-sr">— შესრულებულია</span>@endif
        </li>
        @endforeach
    </ul>
</section>
<style>
    .ksgc { background: #fff; border-radius: 24px; padding: 16px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 2px 0 #ECE9E4; border: 2px solid #FFE3CC; }
    .ksgc-h { display: flex; align-items: center; gap: 10px; }
    .ksgc-fox { width: 44px; height: 44px; border-radius: 14px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ksgc-t { flex: 1; display: flex; flex-direction: column; }
    .ksgc-t b { font-size: 16px; font-weight: 900; } .ksgc-t span { font-size: 12px; color: #6B625A; }
    .ksgc-x { width: 36px; height: 36px; border: 0; border-radius: 10px; background: #F7F6F3; color: #6B625A; cursor: pointer; }
    .ksgc-x:hover { background: #FFE3CC; color: #C24A0A; }
    .ksgc-bar { height: 8px; border-radius: 99px; background: #ECE9E4; overflow: hidden; }
    .ksgc-bar span { display: block; height: 100%; border-radius: 99px; background: #E2590F; }
    .ksgc-list { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 6px 16px; }
    .ksgc-list li { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: #2B2622; min-height: 30px; }
    .ksgc-list li.done { color: #6B625A; } .ksgc-list li.done > span:nth-child(2) { text-decoration: line-through; }
    .ksgc-list a { color: #C24A0A; text-decoration: none; } .ksgc-list a:hover { text-decoration: underline; }
    .ksgc-ck { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; border: 2px solid #ECE9E4; display: flex; align-items: center; justify-content: center; color: #14612F; font-size: 14px; }
    .ksgc-list li.done .ksgc-ck { background: #DFF5E3; border-color: #DFF5E3; }
    .ksgc-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
</style>
<script>
document.getElementById('ksgcClose').addEventListener('click', function () {
    var box = document.getElementById('ksgChecklist');
    box.remove();
    fetch(@json(route('guide.save')), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ state: { checklist_hidden: true } }) });
});
</script>
@endif
