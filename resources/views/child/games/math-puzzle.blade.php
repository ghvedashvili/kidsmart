@extends('layouts.app')
{{-- მათემატიკური თავსატეხები: იპოვე რიცხვები (numbers) / დასვი ნიშანი (signs) / რიცხვების ჯაჭვი (chain).
     5 რაუნდი, 3 სიცოცხლე, სირთულე ბავშვის კლასზე და რაუნდზეა დამოკიდებული. --}}
@section('content')
@include('partials.achievement-toast')
@php
    $__best = (int) $session->player_score;
    $__rules = [
        'numbers' => ['ჩასვი რიცხვები ცარიელ უჯრებში ისე, რომ სამიზნე რიცხვი გამოვიდეს.', 'დააჭირე რიცხვს ქვემოთ — ის პირველ ცარიელ უჯრაში ჩაჯდება. შეცდომით ჩასმულს უჯრაზე დაჭერით ამოიღებ.'],
        'signs'   => ['რიცხვებს შორის დასვი ნიშნები ისე, რომ სამიზნე რიცხვი გამოვიდეს.', 'ნიშნები რამდენჯერაც გინდა, იმდენჯერ გამოიყენე. × და ÷ ჯერ სრულდება, მერე + და −.'],
        'chain'   => ['დაიწყე რიცხვიდან და ბარათებით ნაბიჯ-ნაბიჯ მიიყვანე სამიზნემდე.', 'ყველა ბარათი საჭირო არ არის — ზოგი ზედმეტია! შეცდომისას „უკან“ დააჭირე.'],
    ][$game['mode']];
@endphp
<style>
    .mp, .mp-ov { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; --ok: #1E8A4C; --bad: #C2372B;
          color: var(--ink); }
    .mp { max-width: 560px; margin: 0 auto; padding: 12px 14px 24px; display: flex; flex-direction: column; gap: 12px; color: var(--ink); user-select: none; -webkit-user-select: none; }
    .mp * { box-sizing: border-box; }
    .mp-top { display: flex; align-items: center; gap: 8px; }
    .mp-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 999px; background: #fff; color: var(--o2); font-weight: 800; font-size: 13px; text-decoration: none; box-shadow: 0 1px 0 var(--line); }
    .mp-tabs { display: flex; gap: 4px; background: #fff; border-radius: 14px; padding: 4px; margin-left: auto; box-shadow: 0 1px 0 var(--line); overflow-x: auto; }
    .mp-tab { padding: 7px 10px; border-radius: 10px; font-size: 12px; font-weight: 800; color: var(--mut); text-decoration: none; white-space: nowrap; }
    .mp-tab.on { background: var(--t1); color: var(--o2); }
    .mp-icon-btn { width: 36px; height: 36px; border: 0; border-radius: 12px; background: #fff; color: var(--mut); font-size: 17px; cursor: pointer; box-shadow: 0 1px 0 var(--line); flex-shrink: 0; }
    .mp-stats { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .mp-dots { display: flex; gap: 6px; }
    .mp-score { display: inline-flex; align-items: center; gap: 6px; background: #fff; border-radius: 99px; padding: 5px 12px; font-weight: 900; font-size: 15px; box-shadow: 0 1px 0 var(--line); }
    .mp-score i { color: var(--o); }
    .mp-lvl { font-size: 12px; font-weight: 800; color: var(--mut); align-self: center; }
    .mp-dot { width: 26px; height: 26px; border-radius: 50%; background: #fff; border: 2px solid var(--line); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900; color: #B3A99F; }
    .mp-dot.on { border-color: var(--o); color: var(--o2); background: var(--t1); }
    .mp-dot.done { background: var(--ok); border-color: var(--ok); color: #fff; }
    .mp-right { display: flex; align-items: center; gap: 12px; font-weight: 900; }
    .mp-time { font-size: 15px; color: var(--mut); font-variant-numeric: tabular-nums; }
    .mp-hearts { display: flex; gap: 3px; font-size: 18px; color: var(--bad); }
    .mp-hearts .lost { color: #E6E2DC; }
    .mp-card { background: #fff; border-radius: 26px; padding: 22px 14px 24px; box-shadow: 0 2px 0 var(--line), 0 12px 30px rgba(226, 89, 15, .08); display: flex; flex-direction: column; align-items: center; gap: 14px; min-height: 300px; justify-content: center; position: relative; transition: background .2s; }
    .mp-card.good { background: #E8F7EE; }
    .mp-card.shake { animation: mpShake .42s ease-in-out; }
    @keyframes mpShake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-9px); } 40%, 80% { transform: translateX(9px); } }
    .mp-tlbl { font-size: 12px; font-weight: 900; color: #B3A99F; letter-spacing: .08em; text-transform: uppercase; }
    .mp-target { font-size: 56px; line-height: 1; font-weight: 900; color: var(--ink); display: flex; align-items: center; gap: 10px; }
    .mp-target small { font-size: 16px; font-weight: 800; color: var(--o2); background: var(--t1); padding: 4px 10px; border-radius: 99px; }
    .mp-eq { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
    .mp-slot { width: 60px; height: 60px; border-radius: 16px; border: 2.5px dashed #D9D3CB; background: #FAF9F7; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; color: var(--ink); transition: transform .12s, background .15s; padding: 0; font-family: inherit; }
    button.mp-slot { cursor: pointer; }
    .mp-slot.filled { border: 2.5px solid var(--o); background: var(--o); color: #fff; box-shadow: 0 4px 12px rgba(226, 89, 15, .3); }
    .mp-slot.fixed { border: 2.5px solid var(--line); background: #fff; }
    .mp-slot.op { width: 48px; height: 48px; font-size: 24px; border-radius: 14px; }
    .mp-slot.next { border-color: var(--o); background: var(--t1); }
    .mp-op { font-size: 26px; font-weight: 900; color: #B3A99F; min-width: 18px; text-align: center; }
    .mp-eqsign { font-size: 26px; font-weight: 900; color: #B3A99F; }
    /* ტოლობა ყოველთვის ერთ ხაზზე — ვიწრო ეკრანზე --k ამცირებს ზომას */
    .mp-eq { --k: 1; flex-wrap: nowrap; gap: calc(8px * var(--k)); }
    .mp-eq .mp-slot { flex: 0 0 auto; width: calc(60px * var(--k)); height: calc(60px * var(--k)); font-size: calc(26px * var(--k)); border-radius: calc(16px * var(--k)); border-width: 2px; }
    .mp-eq .mp-slot.op { width: calc(48px * var(--k)); height: calc(48px * var(--k)); font-size: calc(24px * var(--k)); border-radius: calc(14px * var(--k)); }
    .mp-eq .mp-eqsign, .mp-eq .mp-op { flex: 0 0 auto; font-size: calc(26px * var(--k)); min-width: 0; }
    .mp-chain { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .mp-cur { font-size: 40px; font-weight: 900; color: var(--o2); background: var(--t1); border-radius: 20px; padding: 6px 22px; min-width: 96px; text-align: center; }
    .mp-trail { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; font-size: 14px; font-weight: 800; color: var(--mut); min-height: 22px; }
    .mp-trail span { background: #F4F2EE; border-radius: 99px; padding: 2px 10px; }
    .mp-steps { font-size: 13px; font-weight: 800; color: var(--mut); }
    .mp-bank { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; min-height: 84px; align-items: center; padding: 6px 0; }
    .mp-item { min-width: 70px; height: 70px; padding: 0 14px; border: 0; border-radius: 20px; background: #fff; box-shadow: 0 2px 0 var(--line), 0 8px 18px rgba(43, 38, 34, .08); font-size: 28px; font-weight: 900; color: var(--ink); cursor: pointer; transition: transform .1s, opacity .15s; font-family: inherit; }
    .mp-item:active { transform: scale(.92); }
    .mp-item:focus-visible, .mp-slot:focus-visible { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .mp-item.used { visibility: hidden; }
    .mp-item.opc { background: var(--o); color: #fff; }
    .mp-item.chainc { background: var(--o); color: #fff; font-size: 24px; }
    .mp-item.nope { animation: mpNope .35s; }
    @keyframes mpNope { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    .mp-msg { font-size: 13px; font-weight: 800; color: var(--bad); min-height: 18px; text-align: center; }
    .mp-tools { display: flex; justify-content: center; gap: 10px; }
    .mp-tool { border: 0; background: #fff; border-radius: 12px; padding: 8px 14px; font-weight: 800; font-size: 13px; color: var(--mut); cursor: pointer; box-shadow: 0 1px 0 var(--line); font-family: inherit; }
    .mp-tool:disabled { opacity: .4; cursor: default; }
    .mp-praise { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); background: var(--ok); color: #fff; font-weight: 900; font-size: 14px; border-radius: 99px; padding: 5px 14px; opacity: 0; transition: opacity .2s; pointer-events: none; }
    .mp-card.good .mp-praise { opacity: 1; }
    .mp-ov { position: fixed; inset: 0; background: rgba(43, 38, 34, .55); display: flex; align-items: center; justify-content: center; padding: 18px; z-index: 1200; }
    .mp-ov[hidden] { display: none; }
    .mp-modal { background: #fff; border-radius: 26px; padding: 26px 20px 20px; width: 100%; max-width: 360px; text-align: center; display: flex; flex-direction: column; gap: 10px; align-items: center; }
    .mp-modal-ic { font-size: 46px; line-height: 1; }
    .mp-modal h2 { margin: 0; font-size: 22px; font-weight: 900; }
    .mp-modal p { margin: 0; color: var(--mut); font-size: 14px; line-height: 1.55; }
    .mp-modal .mp-hint { background: #F7F6F3; border-radius: 14px; padding: 10px 12px; font-size: 13px; text-align: left; }
    .mp-btn { border: 0; border-radius: 16px; padding: 14px 26px; font-size: 16px; font-weight: 900; background: var(--o); color: #fff; cursor: pointer; width: 100%; font-family: inherit; }
    .mp-btn:hover { background: var(--o2); }
    .mp-link { color: var(--o2); font-weight: 800; font-size: 13px; text-decoration: none; }
    .mp-best { font-size: 13px; font-weight: 800; color: var(--o2); background: var(--t1); border-radius: 99px; padding: 4px 12px; }
    @media (max-width: 420px) {
        .mp-slot { width: 52px; height: 52px; font-size: 22px; }
        .mp-slot.op { width: 42px; height: 42px; }
        .mp-item { min-width: 62px; height: 62px; font-size: 24px; }
        .mp-target { font-size: 48px; }
        .mp-tab { padding: 6px 8px; font-size: 11px; }
    }
</style>

<div class="mp" id="mp">
    <div class="mp-stats">
        <div class="mp-dots" id="mpDots"></div>
        <div class="mp-right">
            <span class="mp-hearts" id="mpHearts" aria-label="სიცოცხლე"></span>
        </div>
    </div>

    <section class="mp-card" id="mpCard" aria-live="polite">
        <span class="mp-praise">სწორია!</span>
        <span class="mp-tlbl">სამიზნე</span>
        <div class="mp-target" id="mpTarget">?</div>
        <div id="mpBoard"></div>
    </section>

    <div class="mp-bank" id="mpBank"></div>
    <div class="mp-tools" id="mpTools"></div>
</div>

{{-- დაწყება / დასასრული --}}
<div class="mp-ov" id="mpOv">
    <div class="mp-modal" role="dialog" aria-modal="true" aria-labelledby="mpMTitle">
        <div class="mp-modal-ic" id="mpMIc">{{ $game['icon'] }}</div>
        <h2 id="mpMTitle">{{ $game['name'] }}</h2>
        <p id="mpMText">{{ $__rules[0] }}</p>
        <p class="mp-hint" id="mpMHint">{{ $__rules[1] }}<br>3 სიცოცხლე — ითამაშე, სანამ არ ამოიწურება. რაც მეტს ამოხსნი, მით რთულდება!</p>
        <span class="mp-best" id="mpMBest" @if(!$__best) hidden @endif>შენი რეკორდი: <b id="mpBestVal">{{ $__best }}</b> ამოხსნილი</span>
        <button type="button" class="mp-btn" id="mpStart">დაწყება</button>
        <a href="{{ route('games.index') }}" class="mp-link" id="mpMBack" hidden>სხვა თამაშები</a>
    </div>
</div>

<script>
(function () {
    var MODE = @json($game['mode']), SLUG = @json($slug), GRADE = @json($grade);
    var FINISH_URL = @json(route('games.math.finish')), CSRF = @json(csrf_token());
    var LIVES = 3;
    var $ = function (id) { return document.getElementById(id); };
    var card = $('mpCard'), board = $('mpBoard'), bank = $('mpBank'), tools = $('mpTools');
    var SYM = { '+': '+', '-': '−', '*': '×', '/': '÷' };

    var rnd = function (a, b) { return a + Math.floor(Math.random() * (b - a + 1)); };
    var pick = function (arr) { return arr[Math.floor(Math.random() * arr.length)]; };
    var shuffle = function (a) { a = a.slice(); for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = a[i]; a[i] = a[j]; a[j] = t; } return a; };

    // ── სირთულე: კლასი (II–V) + საფეხური (ყოველ 3 ამოხსნილზე იზრდება, მაქს. 5) ──
    function stageOf(n) { return Math.min(5, 1 + Math.floor((n - 1) / 3)); }
    function level(n) {
        var g = GRADE, r = stageOf(n);
        var L = {
            maxN:   g <= 2 ? 5 + r : (g === 3 ? 8 + 2 * r : 10 + 3 * r),
            maxRes: g <= 2 ? 20 : (g === 3 ? 50 : (g === 4 ? 100 : 200)),
            ops:    ['+', '-'],
            mulMax: g === 3 ? 5 : 9,
            terms:  (g >= 3 && r >= 4) ? 4 : 3,
            // ჯაჭვი: თავიდან მხოლოდ საჭირო ბარათები, მერე ემატება ზედმეტები — ასარჩევად
            chainSteps: r <= 2 ? 2 : (r >= 5 && g >= 4 ? 4 : 3),
            chainExtra: r === 1 ? 0 : (r === 2 ? 1 : (r === 3 ? 2 : 3)),
        };
        if (g >= 3 && r >= 3) L.ops = ['+', '-', '*'];
        if (g >= 4 && r >= 3) L.ops = ['+', '-', '*', '/'];
        if (g >= 5) L.ops = ['+', '-', '*', '/'];
        return L;
    }

    // გამოსახულების მნიშვნელობა: × და ÷ ჯერ, მერე + და − მარცხნიდან; უარყოფითი/წილადი → null
    function evaluate(nums, ops) {
        var vals = [nums[0]], add = [];
        for (var i = 0; i < ops.length; i++) {
            var n = nums[i + 1];
            if (ops[i] === '*' || ops[i] === '/') {
                var last = vals.pop();
                if (ops[i] === '/') { if (!n || last % n) return null; vals.push(last / n); }
                else vals.push(last * n);
            } else { add.push(ops[i]); vals.push(n); }
        }
        var acc = vals[0];
        for (var j = 0; j < add.length; j++) {
            acc = add[j] === '+' ? acc + vals[j + 1] : acc - vals[j + 1];
            if (acc < 0) return null;
        }
        return acc;
    }

    function perms(a) {
        if (a.length < 2) return [a.slice()];
        var out = [], seen = {};
        a.forEach(function (x, i) {
            perms(a.slice(0, i).concat(a.slice(i + 1))).forEach(function (p) {
                var q = [x].concat(p), k = q.join(',');
                if (!seen[k]) { seen[k] = 1; out.push(q); }
            });
        });
        return out;
    }
    function opCombos(ops, n) { if (!n) return [[]]; var out = []; opCombos(ops, n - 1).forEach(function (c) { ops.forEach(function (o) { out.push(c.concat(o)); }); }); return out; }
    function share(list, ok) { return list.filter(ok).length / list.length; }

    // რიცხვები + ნიშნები, რომელთა პასუხიც ბავშვისთვის „ლამაზია“
    function genExpr(L) {
        for (var t = 0; t < 800; t++) {
            var nums = [], ops = [];
            for (var i = 0; i < L.terms; i++) nums.push(rnd(1, L.maxN));
            for (var k = 0; k < L.terms - 1; k++) {
                var op = pick(L.ops);
                ops.push(op);
                if (op === '*') nums[k + 1] = rnd(2, L.mulMax);
                if (op === '/') { var d = rnd(2, Math.min(9, L.mulMax)); nums[k + 1] = d; nums[k] = d * rnd(1, Math.max(2, Math.floor(L.maxN / 2))); }
            }
            var res = evaluate(nums, ops);
            if (res === null || res < 1 || res > L.maxRes) continue;
            // ×1 / 1× — უაზრო გამრავლება
            if (ops.some(function (o, i) { return (o === '*' || o === '/') && (nums[i] === 1 || nums[i + 1] === 1); })) continue;
            // თუ თითქმის ყველა ვარიანტი სწორია (მაგ. + +), თავსატეხს აზრი არ აქვს
            if (MODE === 'numbers' && share(perms(nums), function (p) { return evaluate(p, ops) === res; }) > .5) continue;
            if (MODE === 'signs' && share(opCombos(L.ops, ops.length), function (o) { return evaluate(nums, o) === res; }) > .5) continue;
            return { nums: nums, ops: ops, res: res };
        }
        return { nums: [3, 4, 2], ops: ['+', '-'], res: 5 };
    }

    // ── ჯაჭვის ბარათები ──
    function chainOp(L) {
        var g = GRADE, add = g <= 2 ? 5 : (g === 3 ? 9 : 15);
        var kinds = ['+', '-', '+', '-', '*', '/'];
        var k = pick(kinds);
        if (k === '+' || k === '-') { var v = rnd(1, add); return { op: k, v: v }; }
        var m = g <= 2 ? 2 : (g === 3 ? pick([2, 3]) : rnd(2, 5));
        return { op: k, v: m };
    }
    function applyCard(x, c, L) {
        var y = c.op === '+' ? x + c.v : c.op === '-' ? x - c.v : c.op === '*' ? x * c.v : (x % c.v ? null : x / c.v);
        if (y === null || y < 0 || y > L.maxRes * 2) return null;
        return y;
    }
    var label = function (c) { return SYM[c.op] + c.v; };
    function genChain(L) {
        for (var t = 0; t < 800; t++) {
            var start = rnd(2, GRADE <= 2 ? 10 : (GRADE === 3 ? 20 : 30)), x = start, sol = [], seen = [start], ok = true;
            for (var s = 0; s < L.chainSteps; s++) {
                var c = chainOp(L), y = applyCard(x, c, L);
                // ზედიზედ ერთნაირი მოქმედება (+3, +4) უაზროა — ერთ ნაბიჯად ერთიანდება
                if (sol.length && sol[sol.length - 1].op === c.op) { ok = false; break; }
                if (y === null || seen.indexOf(y) !== -1) { ok = false; break; }
                sol.push(c); x = y; seen.push(y);
            }
            if (!ok || x > L.maxRes) continue;
            var extra = [], tries = 0;
            while (extra.length < L.chainExtra && tries++ < 200) {
                var d = chainOp(L);
                if (sol.concat(extra).some(function (c) { return label(c) === label(d); })) continue;
                extra.push(d);
            }
            return { start: start, target: x, cards: shuffle(sol.concat(extra)), steps: L.chainSteps };
        }
        return { start: 4, target: 10, cards: [{ op: '*', v: 2 }, { op: '+', v: 2 }, { op: '-', v: 1 }], steps: 2 };
    }

    // ── ხმა (ბრაუზერი ხმას მხოლოდ პირველი დაჭერის შემდეგ უშვებს) ──
    var muted = false, actx = null;
    function tone(f, d, type) {
        if (muted) return;
        try {
            actx = actx || new (window.AudioContext || window.webkitAudioContext)();
            var o = actx.createOscillator(), g = actx.createGain();
            o.type = type || 'sine'; o.frequency.value = f; o.connect(g); g.connect(actx.destination);
            g.gain.setValueAtTime(.18, actx.currentTime);
            g.gain.exponentialRampToValueAtTime(.0001, actx.currentTime + (d || .12));
            o.start(); o.stop(actx.currentTime + (d || .12));
        } catch (e) {}
    }
    var sfx = { tap: function () { tone(520, .07); }, good: function () { tone(660, .12); setTimeout(function () { tone(880, .18); }, 110); }, bad: function () { tone(180, .28, 'triangle'); } };

    // ── თამაშის მდგომარეობა ──
    var round, lives, sec, timer, busy, P, BEST = @json($__best);
    function renderTop() {
        $('mpDots').innerHTML = '<span class="mp-score" aria-label="ამოხსნილი"><i class="bi bi-star-fill"></i>' + (round - 1) + '</span><span class="mp-lvl">დონე ' + stageOf(round) + '</span>';
        var h = '';
        for (var j = 0; j < LIVES; j++) h += '<i class="bi bi-heart-fill ' + (j < lives ? '' : 'lost') + '"></i>';
        $('mpHearts').innerHTML = h;
        $('mpHearts').setAttribute('aria-label', 'სიცოცხლე: ' + lives);
    }
    function start() {
        round = 1; lives = LIVES; sec = 0; busy = false;
        $('mpOv').hidden = true;
        clearInterval(timer);
        timer = setInterval(function () { sec++; }, 1000);
        setupRound();
    }
    function setupRound() {
        renderTop();
        card.classList.remove('good');
        var L = level(round);
        if (MODE === 'chain') setupChain(L);
        else setupExpr(L);
    }

    // ── იპოვე რიცხვები / დასვი ნიშანი ──
    function setupExpr(L) {
        var E = genExpr(L);
        P = { E: E, filled: [] };
        $('mpTarget').textContent = E.res;
        var h = '';
        if (MODE === 'numbers') {
            for (var i = 0; i < E.nums.length; i++) {
                if (i) h += '<span class="mp-op">' + SYM[E.ops[i - 1]] + '</span>';
                h += '<button type="button" class="mp-slot" data-slot="' + i + '" aria-label="ცარიელი უჯრა ' + (i + 1) + '"></button>';
            }
            // ზედა რაუნდებში ერთი ზედმეტი რიცხვიც (III კლასიდან)
            var pool = E.nums.slice();
            if (GRADE >= 3 && stageOf(round) >= 4) { var x; do { x = rnd(1, L.maxN); } while (pool.indexOf(x) !== -1); pool.push(x); }
            P.pool = shuffle(pool);
            P.filled = E.nums.map(function () { return null; });
            bank.innerHTML = P.pool.map(function (n, k) { return '<button type="button" class="mp-item" data-b="' + k + '">' + n + '</button>'; }).join('');
            tools.innerHTML = '';
        } else {
            for (var j = 0; j < E.nums.length; j++) {
                if (j) h += '<button type="button" class="mp-slot op" data-slot="' + (j - 1) + '" aria-label="ნიშანი ' + j + '"></button>';
                h += '<span class="mp-slot fixed">' + E.nums[j] + '</span>';
            }
            P.filled = E.ops.map(function () { return null; });
            bank.innerHTML = L.ops.map(function (o) { return '<button type="button" class="mp-item opc" data-op="' + o + '" aria-label="ნიშანი ' + SYM[o] + '">' + SYM[o] + '</button>'; }).join('');
            tools.innerHTML = '';
        }
        board.innerHTML = '<div class="mp-eq">' + h + '<span class="mp-eqsign">=</span><span class="mp-slot fixed" style="color:#C24A0A;">' + E.res + '</span></div>';
        fitEq();
        markNext();
    }
    function markNext() {
        var slots = board.querySelectorAll('[data-slot]'), first = true;
        slots.forEach(function (s, i) {
            var empty = P.filled[i] === null;
            s.classList.toggle('next', empty && first);
            if (empty && first) first = false;
        });
    }
    function fillSlot(val, bankIdx) {
        var i = P.filled.indexOf(null);
        if (i === -1 || busy) return;
        sfx.tap();
        P.filled[i] = { v: val, b: bankIdx };
        var s = board.querySelector('[data-slot="' + i + '"]');
        s.textContent = MODE === 'numbers' ? val : SYM[val];
        s.classList.add('filled');
        s.setAttribute('aria-label', 'ჩასმულია ' + s.textContent + ' — დააჭირე ამოსაღებად');
        if (bankIdx !== undefined) bank.querySelector('[data-b="' + bankIdx + '"]').classList.add('used');
        markNext();
        if (P.filled.indexOf(null) === -1) setTimeout(checkExpr, 220);
    }
    function clearSlot(i) {
        if (busy || !P.filled[i]) return;
        if (P.filled[i].b !== undefined) bank.querySelector('[data-b="' + P.filled[i].b + '"]').classList.remove('used');
        P.filled[i] = null;
        var s = board.querySelector('[data-slot="' + i + '"]');
        s.textContent = ''; s.classList.remove('filled');
        markNext();
    }
    function checkExpr() {
        var E = P.E, res;
        if (MODE === 'numbers') res = evaluate(P.filled.map(function (f) { return f.v; }), E.ops);
        else res = evaluate(E.nums, P.filled.map(function (f) { return f.v; }));
        if (res === E.res) success(); else fail(function () { P.filled.forEach(function (f, i) { if (f) clearSlot(i); }); });
    }

    // ── რიცხვების ჯაჭვი ──
    function setupChain(L) {
        var C = genChain(L);
        P = { C: C, L: L, cur: C.start, hist: [] };
        $('mpTarget').textContent = C.target;
        bank.innerHTML = C.cards.map(function (c, k) { return '<button type="button" class="mp-item chainc" data-c="' + k + '">' + label(c) + '</button>'; }).join('');
        tools.innerHTML = '<button type="button" class="mp-tool" id="mpUndo"><i class="bi bi-arrow-counterclockwise"></i> უკან</button>';
        $('mpUndo').onclick = undoChain;
        drawChain();
    }
    function drawChain() {
        var C = P.C;
        board.innerHTML = '<div class="mp-chain"><span class="mp-cur" aria-label="ახლა">' + P.cur + '</span></div><div class="mp-msg" id="mpMsg" aria-live="polite"></div>';
        bank.querySelectorAll('[data-c]').forEach(function (b) {
            var k = +b.getAttribute('data-c');
            b.classList.toggle('used', P.hist.some(function (h) { return h.k === k; }));
        });
        $('mpUndo').disabled = !P.hist.length;
    }
    function useCard(k) {
        if (busy) return;
        var y = applyCard(P.cur, P.C.cards[k], P.L);
        if (y === null) {
            // ეს ნაბიჯი ამ რიცხვზე არ გამოდის (უარყოფითი/წილადი) — სიცოცხლე არ იკარგება
            var b = bank.querySelector('[data-c="' + k + '"]');
            b.classList.remove('nope'); void b.offsetWidth; b.classList.add('nope');
            var m = $('mpMsg'); if (m) m.textContent = P.C.cards[k].op === '/' ? P.cur + ' უნაშთოდ არ იყოფა ' + P.C.cards[k].v + '-ზე' : 'ასე უარყოფითი გამოვა';
            return;
        }
        sfx.tap();
        P.hist.push({ k: k, from: P.cur, to: y });
        P.cur = y;
        drawChain();
        if (P.cur === P.C.target) setTimeout(success, 200);
        else if (P.hist.length >= P.C.steps) setTimeout(function () { fail(function () { P.cur = P.C.start; P.hist = []; drawChain(); }); }, 250);
    }
    function undoChain() {
        if (busy || !P.hist.length) return;
        var h = P.hist.pop();
        P.cur = h.from;
        drawChain();
    }

    // ── შედეგი ──
    function success() {
        busy = true; sfx.good();
        card.classList.add('good');
        setTimeout(function () {
            busy = false;
            round++;
            setupRound();
        }, 900);
    }
    function fail(reset) {
        busy = true; sfx.bad();
        lives--;
        renderTop();
        card.classList.remove('shake'); void card.offsetWidth; card.classList.add('shake');
        setTimeout(function () {
            card.classList.remove('shake');
            busy = false;
            if (lives <= 0) finish(); else reset();
        }, 480);
    }
    function finish() {
        clearInterval(timer);
        busy = true;
        var score = round - 1, rec = score > BEST;
        $('mpMIc').textContent = rec ? '🏆' : '💪';
        $('mpMTitle').textContent = rec ? 'ახალი რეკორდი!' : 'სიცოცხლეები გათავდა';
        $('mpMText').textContent = 'ამოხსენი ' + score + ' ' + (score === 1 ? 'თავსატეხი' : 'თავსატეხი') + '. ' + (rec ? 'გილოცავ! 🎉' : 'სცადე კიდევ — გამოგივა!');
        $('mpMHint').hidden = true;
        $('mpStart').textContent = 'თავიდან თამაში';
        $('mpMBack').hidden = false;
        $('mpOv').hidden = false;
        $('mpStart').focus();
        var fd = new FormData();
        fd.append('game', SLUG); fd.append('score', score); fd.append('time', Math.max(1, sec));
        fetch(FINISH_URL, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.best_score) { BEST = j.best_score; $('mpBestVal').textContent = j.best_score; $('mpMBest').hidden = false; }
                if (j.new_achievements && j.new_achievements.length && window.showAchievementToasts) window.showAchievementToasts(j.new_achievements);
            })
            .catch(function () {});
    }

    // ── დაჭერები ──
    bank.addEventListener('click', function (e) {
        var b = e.target.closest('button');
        if (!b || b.disabled) return;
        if (b.hasAttribute('data-c')) useCard(+b.getAttribute('data-c'));
        else if (b.hasAttribute('data-op')) fillSlot(b.getAttribute('data-op'));
        else if (b.hasAttribute('data-b') && !b.classList.contains('used')) fillSlot(P.pool[+b.getAttribute('data-b')], +b.getAttribute('data-b'));
    });
    function fitEq() {
        var eq = board.querySelector('.mp-eq');
        if (!eq) return;
        eq.style.setProperty('--k', 1);
        var avail = card.clientWidth - 28;
        for (var i = 0; i < 3; i++) {
            var k = parseFloat(eq.style.getPropertyValue('--k')) || 1, kids = eq.children, need = 8 * k * (kids.length - 1);
            for (var j = 0; j < kids.length; j++) need += kids[j].offsetWidth;
            if (need <= avail) break;
            eq.style.setProperty('--k', Math.max(.45, k * avail / need * .98).toFixed(3));
        }
    }
    window.addEventListener('resize', fitEq);
    board.addEventListener('click', function (e) {
        var s = e.target.closest('[data-slot]');
        if (s) clearSlot(+s.getAttribute('data-slot'));
    });
    $('mpStart').addEventListener('click', start);
    $('mpStart').focus();
})();
</script>
@endsection
