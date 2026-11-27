@extends('layouts.app')
{{-- თამაშების მართვა: ჩართვა/გათიშვა და რომელ კლასებს უჩანს --}}
@section('content')
<style>
    .gm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
    .gm-card { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .gm-card.off { opacity: .6; }
    .gm-top { display: flex; align-items: center; gap: 12px; }
    .gm-ic { width: 48px; height: 48px; border-radius: 14px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
    .gm-name { font-weight: 900; font-size: 16px; color: #2B2622; }
    .gm-meta { font-size: 12px; color: #6B625A; }
    .gm-lbl { font-size: 12px; font-weight: 800; color: #6B625A; }
    .gm-grades { display: flex; flex-wrap: wrap; gap: 6px; }
    .gm-chip { margin: 0; cursor: pointer; }
    .gm-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .gm-chip span { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 99px; border: 1.5px solid #E6E2DC; background: #fff; font-size: 13px; font-weight: 800; color: #4F473F; }
    .gm-chip input:checked + span { border-color: #E2590F; background: #FFF3EA; color: #C24A0A; }
    .gm-chip input:focus-visible + span { outline: 2px solid #FFC49A; outline-offset: 2px; }
    .gm-chip.all span { border-style: dashed; }
    .gm-who { font-size: 13px; font-weight: 700; color: #14612F; background: #DFF5E3; border-radius: 10px; padding: 6px 10px; }
    .gm-who.no { color: #A12626; background: #FDE2E2; }
    .gm-sw { display: inline-flex; align-items: center; gap: 8px; margin: 0 0 0 auto; cursor: pointer; font-size: 12px; font-weight: 800; color: #4F473F; }
    .gm-sw input { position: absolute; opacity: 0; width: 0; height: 0; }
    .gm-sw i { width: 40px; height: 22px; border-radius: 99px; background: #D9D3CB; position: relative; transition: background .15s; }
    .gm-sw i::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: left .15s; }
    .gm-sw input:checked + i { background: #E2590F; }
    .gm-sw input:checked + i::after { left: 21px; }
    .gm-sw input:focus-visible + i { outline: 2px solid #FFC49A; outline-offset: 2px; }
    .gm-gate { background: #fff; border: 1px solid #ECE9E4; border-radius: 20px; padding: 16px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .gm-gate-ic { width: 48px; height: 48px; border-radius: 14px; background: #FFF3EA; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
    .gm-gate-t { flex: 1; min-width: 220px; }
    .gm-gate-t b { display: block; font-size: 16px; font-weight: 900; color: #2B2622; }
    .gm-gate-t span { font-size: 13px; color: #6B625A; }
    .gm-test { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; background: #F7F6F3; }
    .gm-test-t { flex: 1; font-size: 13px; font-weight: 800; color: #2B2622; }
    .gm-test-t small { display: block; font-size: 12px; font-weight: 600; color: #6B625A; }
    .gm-test .gm-sw { margin: 0; }
    .gm-test.dis { opacity: .5; }
</style>

<div style="display:flex;flex-direction:column;gap:14px;">
    <header class="ks-page-head">
        <div class="ks-page-title">
            <span class="ks-eyebrow">ბავშვის გვერდი</span>
            <h1 class="ks-display ks-h1">თამაშები</h1>
        </div>
    </header>
    <p class="ks-muted" style="margin:0;font-size:14px;">აირჩიე, რომელი თამაში რომელ კლასს უჩანდეს. თუ კლასი არცერთი არ არის მონიშნული — ყველა კლასს უჩანს. გათიშული თამაში არავის უჩანს.</p>

    @if(session('success'))<div class="msg" role="status">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('admin.games.gate') }}" class="gm-gate">
        @csrf @method('PUT')
        <span class="gm-gate-ic" aria-hidden="true">📝</span>
        <div class="gm-gate-t">
            <b>თამაშები მხოლოდ დღის ტესტის შემდეგ</b>
            <span>ჩართულისას ბავშვი მონიშნულ თამაშებს ვერ ითამაშებს, სანამ იმ დღის ტესტს არ დაწერს. გამორთულისას ყველა თამაში ტესტის გარეშეც იხსნება.</span>
        </div>
        <label class="gm-sw" title="ზოგადი ჩამრთველი">
            <input type="hidden" name="gate" value="0">
            <input type="checkbox" name="gate" value="1" @checked($gateOn) onchange="this.form.submit()"><i></i><span>{{ $gateOn ? 'ჩართული' : 'გამორთული' }}</span>
        </label>
        <noscript><button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button></noscript>
    </form>

    <div class="gm-grid">
        @foreach($games as $__g)
        <form method="POST" action="{{ route('admin.games.update', $__g['slug']) }}" class="gm-card {{ $__g['is_active'] ? '' : 'off' }}" data-gm>
            @csrf @method('PUT')
            <div class="gm-top">
                <span class="gm-ic" aria-hidden="true">{{ $__g['icon'] }}</span>
                <div style="min-width:0;">
                    <div class="gm-name">{{ $__g['name'] }}</div>
                    <div class="gm-meta">{{ $__g['players'] }} ბავშვს უთამაშია</div>
                </div>
                <label class="gm-sw" title="ჩართვა / გათიშვა">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($__g['is_active']) data-gm-active><i></i><span data-gm-al>{{ $__g['is_active'] ? 'ჩართული' : 'გათიშული' }}</span>
                </label>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <span class="gm-lbl">ვის უჩანს</span>
                <div class="gm-grades" role="group" aria-label="კლასები — {{ $__g['name'] }}">
                    @foreach($grades as $__gr)
                    <label class="gm-chip"><input type="checkbox" name="grade_ids[]" value="{{ $__gr->id }}" @checked(in_array($__gr->id, $__g['grade_ids'], true)) data-gm-grade><span>{{ $__gr->name }}</span></label>
                    @endforeach
                </div>
                <div class="gm-who" data-gm-who aria-live="polite"></div>
            </div>
            <div class="gm-test {{ $gateOn ? '' : 'dis' }}">
                <div class="gm-test-t">იხსნება დღის ტესტის შემდეგ<small>{{ $gateOn ? 'გამორთე, თუ ეს თამაში ტესტის გარეშეც უნდა იხსნებოდეს' : 'ზოგადი ჩამრთველი გამორთულია — ახლა ყველა თამაში ღიაა' }}</small></div>
                <label class="gm-sw" title="დღის ტესტის მოთხოვნა">
                    <input type="hidden" name="needs_test" value="0">
                    <input type="checkbox" name="needs_test" value="1" @checked($__g['needs_test']) data-gm-test><i></i><span data-gm-tl>{{ $__g['needs_test'] ? 'კი' : 'არა' }}</span>
                </label>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
            </div>
        </form>
        @endforeach
    </div>
</div>

<script>
document.querySelectorAll('[data-gm]').forEach(function (f) {
    var who = f.querySelector('[data-gm-who]'), act = f.querySelector('[data-gm-active]');
    function sync() {
        var names = Array.prototype.filter.call(f.querySelectorAll('[data-gm-grade]'), function (c) { return c.checked; })
            .map(function (c) { return c.nextElementSibling.textContent.trim(); });
        f.querySelector('[data-gm-al]').textContent = act.checked ? 'ჩართული' : 'გათიშული';
        var t = f.querySelector('[data-gm-test]');
        if (t) f.querySelector('[data-gm-tl]').textContent = t.checked ? 'კი' : 'არა';
        f.classList.toggle('off', !act.checked);
        who.classList.toggle('no', !act.checked);
        who.textContent = !act.checked ? 'არავის უჩანს (გათიშულია)' : (names.length ? 'უჩანს: ' + names.join(', ') : 'უჩანს ყველა კლასს');
    }
    f.addEventListener('change', sync);
    sync();
});
</script>
@endsection
