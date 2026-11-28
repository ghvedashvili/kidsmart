@extends('layouts.app')
{{-- ციფრის გადატანა: ტოლობა არასწორია — გადაიტანე ერთი ციფრი სხვა ადგილას, რომ სწორი გახდეს.
     ამოცანები შეუზღუდავია და თანდათან რთულდება (მხოლოდ მიმატება და გამოკლება); დონე ინახება. ციფრი ითრევა (ან დააჭირე ციფრს და მერე ადგილს). გადატანისას მხოლოდ ერთი ცარიელი ადგილი იხსნება. --}}
@section('content')
@include('partials.achievement-toast')
<style>
    .dm, .dm-ov { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; --ok: #1E8A4C; --okt: #E9FBE9; --bad: #C2372B; color: var(--ink); font-family: 'Noto Sans Georgian', system-ui, sans-serif; }
    .dm { max-width: 640px; margin: 0 auto; padding: 8px 14px 12px; display: flex; flex-direction: column; gap: 14px; height: calc(100dvh - 70px); box-sizing: border-box; user-select: none; -webkit-user-select: none; touch-action: manipulation; }
    .dm-hud { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-shrink: 0; }
    .dm-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff; border-radius: 99px; padding: 6px 14px; font-weight: 900; font-size: 14px; box-shadow: 0 1px 0 var(--line); white-space: nowrap; }
    .dm-pill small { font-size: 12px; font-weight: 800; color: var(--mut); }
        #dmLv { background: var(--t1); color: var(--o2); }
    .dm-task { margin: 0; text-align: center; font-size: 15px; font-weight: 800; color: var(--mut); flex-shrink: 0; }
    .dm-stage { flex: 1 1 auto; min-height: 0; display: flex; align-items: center; justify-content: center; }
    .dm-card { width: 100%; background: #fff; border-radius: 26px; padding: 26px 10px; box-shadow: 0 2px 0 var(--line), 0 12px 30px rgba(226, 89, 15, .08); box-sizing: border-box; transition: background .25s, box-shadow .25s; }
    .dm-card.win { background: var(--okt); box-shadow: 0 0 0 3px var(--ok), 0 12px 30px rgba(30, 138, 76, .12); }
    .dm-eq { --tw: clamp(36px, 10vw, 58px); --th: clamp(50px, 13vw, 74px); display: flex; align-items: center; justify-content: center; flex-wrap: nowrap; touch-action: none; }
    .dm-dg { width: var(--tw); height: var(--th); flex: 0 0 auto; display: flex; align-items: center; justify-content: center; background: #fff; border: 2.5px solid #E6DCCF; border-bottom-width: 5px; border-radius: 14px; box-sizing: border-box; font-size: calc(var(--tw) * .62); font-weight: 900; color: var(--ink); cursor: grab; transition: transform .12s, opacity .12s, border-color .12s, background .12s; }
    .dm-dg:hover { border-color: #FFC49A; }
    .dm-dg.drag { opacity: .3; }
    .dm-dg.sel { border-color: var(--o); background: var(--t1); transform: translateY(-4px); }
    .dm-dg.hint { animation: dmHint 1s ease-in-out infinite; border-color: var(--o); }
    .dm-card.win .dm-dg { border-color: #9AD8B0; background: #fff; }
    @keyframes dmHint { 50% { transform: translateY(-6px); background: var(--t2); } }
    .dm-op { flex: 0 0 auto; padding: 0 clamp(4px, 1.6vw, 12px); font-size: calc(var(--tw) * .6); font-weight: 900; color: #B3A99F; }
    /* ჩასასმელი ადგილი: ჩვეულებრივ ვიწროა; გადათრევისას მხოლოდ ერთი — ყველაზე ახლოს მყოფი — იხსნება */
    .dm-dz { flex: 0 0 auto; width: 6px; height: var(--th); border-radius: 10px; box-sizing: border-box; transition: width .14s ease, background .14s, border-color .14s, margin .14s; position: relative; }
    .dm-eq.picking .dm-dz.ok::after { content: ''; position: absolute; left: 50%; top: 20%; bottom: 20%; width: 3px; margin-left: -1.5px; border-radius: 2px; background: #FFC49A; }
    .dm-eq.picking .dm-dz.ok::before { content: ''; position: absolute; left: -12px; right: -12px; top: -10px; bottom: -10px; }
    .dm-dz.on { width: var(--tw); margin: 0 3px; background: rgba(30, 138, 76, .12); border: 2.5px dashed var(--ok); }
    .dm-dz.on::after { display: none; }
    .dm-dz.hint { width: var(--tw); margin: 0 3px; border: 2.5px dashed var(--o); background: var(--t1); }
    .dm-ghost { position: fixed; z-index: 1300; pointer-events: none; display: flex; align-items: center; justify-content: center; box-sizing: border-box; border: 2.5px solid #E2590F; border-bottom-width: 5px; border-radius: 14px; background: #fff; color: #2B2622; font-family: 'Noto Sans Georgian', system-ui, sans-serif; font-weight: 900; transform: translate(-50%, -60%) scale(1.08); box-shadow: 0 10px 24px rgba(43, 38, 34, .25); }
    .dm-msg { flex-shrink: 0; min-height: 22px; margin: 0; text-align: center; font-size: 15px; font-weight: 800; }
    .dm-msg.bad { color: var(--bad); } .dm-msg.ok { color: var(--ok); } .dm-msg.tip { color: var(--mut); }
    .dm-acts { flex-shrink: 0; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    .dm-btn { height: 48px; padding: 0 20px; border-radius: 24px; border: 2px solid var(--line); background: #fff; font-family: inherit; font-size: 15px; font-weight: 900; color: var(--ink); cursor: pointer; }
    .dm-btn:hover { border-color: var(--ink); }
    .dm-btn.main { background: var(--o); border-color: var(--o); color: #fff; }
    .dm-btn.main:hover { background: var(--o2); border-color: var(--o2); }
    .dm-btn[disabled] { opacity: .5; cursor: default; }
    .dm [hidden], .dm-ov [hidden], .dm-ov[hidden] { display: none !important; }

    .dm-ov { position: fixed; inset: 0; background: rgba(43, 38, 34, .55); display: flex; align-items: center; justify-content: center; padding: 18px; z-index: 1200; }
    .dm-modal { background: #fff; border-radius: 26px; padding: 24px 20px 20px; width: 100%; max-width: 380px; text-align: center; display: flex; flex-direction: column; gap: 10px; align-items: center; max-height: calc(100dvh - 36px); overflow-y: auto; }
    .dm-modal-ic { font-size: 46px; line-height: 1; }
    .dm-modal h2 { margin: 0; font-size: 22px; font-weight: 900; }
    .dm-modal p { margin: 0; color: var(--mut); font-size: 14px; line-height: 1.55; }
    .dm-ex { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 900; background: var(--t1); border-radius: 14px; padding: 8px 14px; }
    .dm-ex u { color: var(--o); text-decoration: none; background: var(--t2); border-radius: 4px; padding: 0 3px; }
    .dm-ex b { color: var(--ok); }
    .dm-best { font-size: 13px; font-weight: 800; color: var(--o2); background: var(--t1); border-radius: 99px; padding: 4px 12px; }
    .dm-modal .dm-btn { width: 100%; }
    .dm-link { font-size: 14px; font-weight: 800; color: var(--o2); text-decoration: none; padding: 6px; }
</style>

<div class="dm" id="dm">
    <div class="dm-hud">
        <span class="dm-pill"><small>ამოცანა</small> <b id="dmN">1</b></span>
        <span class="dm-pill" id="dmLv"><small>დონე</small> <b id="dmL">1</b></span>
        <span class="dm-pill"><small>ამოხსნილი</small> <b id="dmS">{{ $best }}</b></span>
    </div>
    <p class="dm-task">გადაიტანე <b>ერთი ციფრი</b> სხვა ადგილას, რომ ტოლობა სწორი გახდეს</p>
    <div class="dm-stage"><div class="dm-card" id="dmCard"><div class="dm-eq" id="dmEq" role="group" aria-label="ტოლობა"></div></div></div>
    <p class="dm-msg tip" id="dmMsg" role="status">გადაათრიე ციფრი — ან დააჭირე ციფრს, მერე ადგილს</p>
    <div class="dm-acts">
        <button type="button" class="dm-btn" id="dmReset">↺ თავიდან</button>
        <button type="button" class="dm-btn" id="dmHint">🦊 მინიშნება</button>
        <button type="button" class="dm-btn main" id="dmNext" hidden>შემდეგი →</button>
    </div>
</div>

<div class="dm-ov" id="dmOv">
    <div class="dm-modal" role="dialog" aria-modal="true" aria-labelledby="dmMTitle">
        <div class="dm-modal-ic" id="dmMIc">{{ $game['icon'] }}</div>
        <h2 id="dmMTitle">{{ $game['name'] }}</h2>
        <p id="dmMText">ტოლობა არასწორია. გადაიტანე მხოლოდ <b>ერთი ციფრი</b> სხვა ადგილას — და ტოლობა სწორი გახდება!</p>
        <div class="dm-ex" id="dmMEx"><span>12 + <u>1</u>3 = 5</span> → <b>12 + 3 = <u>1</u>5</b></div>
        <span class="dm-best" id="dmBest" @if(!$best) hidden @endif>უკვე ამოხსენი: <b>{{ $best }}</b> ამოცანა — გავაგრძელოთ!</span>
        <button type="button" class="dm-btn main" id="dmGo">@if($level) გაგრძელება @else დაწყება @endif</button>
    </div>
</div>

<script>
(function () {
    var FINISH_URL = @json(route('games.math-move.finish')), CSRF = @json(csrf_token());
    var $ = function (id) { return document.getElementById(id); };
    var eqEl = $('dmEq');
    var tok, init, sol, idx = {{ (int) $level }}, solved = {{ (int) $best }}, usedHint = false, done = false, drag = null, pick = null, onZone = null;

    // ── ხმები ──
    var AC = null;
    function tone(f, t0, d, v) { try { AC = AC || new (window.AudioContext || window.webkitAudioContext)(); if (AC.state === 'suspended') AC.resume(); var o = AC.createOscillator(), g = AC.createGain(), t = AC.currentTime + (t0 || 0); o.type = 'triangle'; o.frequency.value = f; g.gain.setValueAtTime(.0001, t); g.gain.exponentialRampToValueAtTime(v || .15, t + .01); g.gain.exponentialRampToValueAtTime(.0001, t + d); o.connect(g); g.connect(AC.destination); o.start(t); o.stop(t + d + .02); } catch (e) {} }
    var SND = { pick: function () { tone(660, 0, .08, .08); }, drop: function () { tone(880, 0, .1, .1); }, win: function () { [523, 659, 784, 1047].forEach(function (f, i) { tone(f, i * .1, .3, .14); }); }, bad: function () { tone(220, 0, .18, .08); } };

    // ── ამოცანები: ვიღებთ სწორ ტოლობას და ერთ ციფრს ვაგდებთ სხვაგან ──
    var R = function (a, b) { return a + Math.floor(Math.random() * (b - a + 1)); };
    function valid(t) { return [0, 2, 4].every(function (k) { var x = t[k]; return x.length > 0 && x.length <= 3 && !(x.length > 1 && x[0] === '0'); }); }
    function correct(t) { if (!valid(t)) return false; var a = +t[0], b = +t[2], c = +t[4]; return t[1] === '+' ? a + b === c : a - b === c; }
    function allMoves(t) {
        var out = [];
        [0, 2, 4].forEach(function (a) {
            for (var i = 0; i < t[a].length; i++) [0, 2, 4].forEach(function (b) {
                var tt = t.slice(), ch = tt[a][i];
                tt[a] = tt[a].slice(0, i) + tt[a].slice(i + 1);
                for (var j = 0; j <= tt[b].length; j++) {
                    var u = tt.slice(); u[b] = u[b].slice(0, j) + ch + u[b].slice(j);
                    if (u.join('|') !== t.join('|')) out.push({ t: u, from: [a, i], to: [b, j] });
                }
            });
        });
        return out;
    }
    // სირთულე თანდათან იზრდება — მხოლოდ მიმატება და გამოკლება
    function lvl(n) { return n < 4 ? 1 : n < 9 ? 2 : n < 15 ? 3 : n < 22 ? 4 : n < 32 ? 5 : 6; }
    function makePuzzle(n) {
        var L = lvl(n);
        for (var guard = 0; guard < 800; guard++) {
            var plus = L === 1 ? true : (L === 2 ? Math.random() < .7 : Math.random() < .5), a, b, c;
            if (L === 1)      { a = R(10, 49); b = R(1, 9); }
            else if (L === 2) { a = R(10, 79); b = R(1, 9); }
            else if (L === 3) { a = R(10, 89); b = R(1, 19); }
            else if (L === 4) { a = R(20, 99); b = R(10, 49); }
            else if (L === 5) { a = R(30, 99); b = R(11, 89); }
            else              { a = R(50, 150); b = R(11, 99); }
            if (!plus && b >= a) { var x = a; a = b + 1 + R(0, 9); b = x; }
            c = plus ? a + b : a - b;
            if (c < 1 || c > (L >= 6 ? 199 : 99)) continue;
            var good = [String(a), plus ? '+' : '-', String(b), '=', String(c)];
            var cands = allMoves(good).filter(function (m) { return valid(m.t) && !correct(m.t); });
            if (!cands.length) continue;
            var m = cands[R(0, cands.length - 1)];
            var back = allMoves(m.t).filter(function (x) { return correct(x.t); });
            if (!back.length) continue;
            return { t: m.t, sol: back[0] };
        }
    }
    function sym(op) { return op === '-' ? '−' : op; }

    // ── ხატვა ──
    function zoneOk(t, p) {
        var src = drag || pick; if (!src) return false;
        if (t === src.t && (p === src.i || p === src.i + 1)) return false;   // იგივე ადგილი
        if (t !== src.t && tok[src.t].length === 1) return false;            // რიცხვი ცარიელი არ უნდა დარჩეს
        return true;
    }
    function render() {
        var h = '';
        [0, 1, 2, 3, 4].forEach(function (k) {
            if (k % 2) { h += '<span class="dm-op" aria-hidden="true">' + sym(tok[k]) + '</span>'; return; }
            var s = tok[k];
            for (var p = 0; p <= s.length; p++) {
                var ok = zoneOk(k, p);
                h += '<span class="dm-dz' + (ok ? ' ok' : '') + '" data-t="' + k + '" data-p="' + p + '"' + (ok ? ' role="button" aria-label="ჩასვი აქ"' : '') + '></span>';
                if (p < s.length) {
                    var src = drag || pick, me = src && src.t === k && src.i === p;
                    h += '<span class="dm-dg' + (drag && me ? ' drag' : '') + (pick && me ? ' sel' : '') + '" data-t="' + k + '" data-i="' + p + '" role="button" tabindex="0" aria-label="ციფრი ' + s[p] + '">' + s[p] + '</span>';
                }
            }
        });
        eqEl.innerHTML = h;
        eqEl.classList.toggle('picking', !!pick);   // გადათრევისას ზოლები არ ჩანს — მხოლოდ ერთი ადგილი იხსნება
        onZone = null;
    }
    function setZone(z) {
        if (onZone === z) return;
        if (onZone) onZone.classList.remove('on');
        onZone = z; if (z) z.classList.add('on');
    }
    function msg(t, c) { var m = $('dmMsg'); m.textContent = t || ''; m.className = 'dm-msg ' + (c || 'tip'); }
    function dots() { $('dmN').textContent = idx + 1; $('dmL').textContent = lvl(idx); $('dmS').textContent = solved; }

    // ── გადატანა ──
    function doMove(src, t, p) {
        var ch = tok[src.t][src.i], nt = tok.slice();
        nt[src.t] = nt[src.t].slice(0, src.i) + nt[src.t].slice(src.i + 1);
        if (t === src.t && p > src.i) p--;
        nt[t] = nt[t].slice(0, p) + ch + nt[t].slice(p);
        tok = nt; drag = pick = null; SND.drop();
        render(); check();
    }
    function check() {
        if (!valid(tok)) { msg('რიცხვი ნულით ვერ დაიწყება — სცადე სხვაგან', 'bad'); SND.bad(); return; }
        if (correct(tok)) {
            done = true; $('dmCard').classList.add('win'); SND.win();
            if (!usedHint) solved++;
            save(!usedHint);
            msg(usedHint ? 'სწორია! შემდეგს მინიშნების გარეშე სცადე 😉' : 'ყოჩაღ! ტოლობა სწორია! 🎉', 'ok');
            $('dmNext').hidden = false; $('dmHint').disabled = true; $('dmReset').disabled = true;
            dots();
            setTimeout(function () { try { $('dmNext').focus({ preventScroll: true }); } catch (e) {} }, 50);
        } else msg('ჯერ არ არის სწორი — სცადე კიდევ ან დააჭირე „თავიდან“', 'bad');
    }
    function zoneAt(x, y) {
        var r = eqEl.getBoundingClientRect();
        if (y < r.top - 90 || y > r.bottom + 90 || x < r.left - 60 || x > r.right + 60) return null;
        var bestZ = null, bd = Infinity;
        eqEl.querySelectorAll('.dm-dz.ok').forEach(function (z) {
            var zr = z.getBoundingClientRect(), d = Math.abs(x - (zr.left + zr.width / 2));
            if (z === onZone) d -= 16;   // გახსნილ ადგილს ცოტა „ვამაგრებთ“, რომ ორ ადგილს შორის არ ხტოდეს
            if (d < bd) { bd = d; bestZ = z; }
        });
        return bestZ;
    }
    var ghost = null, start = null;
    eqEl.addEventListener('pointerdown', function (e) {
        if (done) return;
        var d = e.target.closest('.dm-dg'), z = e.target.closest('.dm-dz.ok');
        if (z && pick) { doMove(pick, +z.dataset.t, +z.dataset.p); return; }
        if (!d) return;
        e.preventDefault();
        start = { x: e.clientX, y: e.clientY, t: +d.dataset.t, i: +d.dataset.i, el: d, moved: false, id: e.pointerId };
        try { eqEl.setPointerCapture(e.pointerId); } catch (er) {}
    });
    eqEl.addEventListener('pointermove', function (e) {
        if (!start || e.pointerId !== start.id) return;
        if (!start.moved && Math.hypot(e.clientX - start.x, e.clientY - start.y) < 7) return;
        if (!start.moved) {
            start.moved = true; pick = null; drag = { t: start.t, i: start.i }; SND.pick();
            var cs = start.el.getBoundingClientRect();
            ghost = document.createElement('div'); ghost.className = 'dm-ghost'; ghost.textContent = start.el.textContent;
            ghost.style.width = cs.width + 'px'; ghost.style.height = cs.height + 'px'; ghost.style.fontSize = getComputedStyle(start.el).fontSize;
            document.body.appendChild(ghost);
            render();
        }
        ghost.style.left = e.clientX + 'px'; ghost.style.top = e.clientY + 'px';
        setZone(zoneAt(e.clientX, e.clientY));
    });
    function endPointer(e) {
        if (!start || e.pointerId !== start.id) return;
        var s = start; start = null;
        if (ghost) { ghost.remove(); ghost = null; }
        if (s.moved) {
            var z = onZone || zoneAt(e.clientX, e.clientY), src = drag;
            if (z && e.type === 'pointerup') doMove(src, +z.dataset.t, +z.dataset.p); else { drag = null; render(); }
            return;
        }
        // დაჭერა: ციფრის მონიშვნა (მერე ადგილზე დააჭერ)
        if (pick && pick.t === s.t && pick.i === s.i) { pick = null; msg('გადაათრიე ციფრი — ან დააჭირე ციფრს, მერე ადგილს'); }
        else { pick = { t: s.t, i: s.i }; SND.pick(); msg('ახლა დააჭირე ადგილს, სადაც ეს ციფრი უნდა ჩადგეს'); }
        render();
    }
    eqEl.addEventListener('pointerup', endPointer);
    eqEl.addEventListener('pointercancel', endPointer);
    // მონიშნული ციფრისას: ადგილზე მიტანა — მხოლოდ ის ერთი ადგილი იხსნება
    eqEl.addEventListener('mousemove', function (e) { if (pick && !start) setZone(zoneAt(e.clientX, e.clientY)); });
    eqEl.addEventListener('keydown', function (e) {
        var d = e.target.closest('.dm-dg');
        if (d && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); pick = { t: +d.dataset.t, i: +d.dataset.i }; render(); var f = eqEl.querySelector('.dm-dz.ok'); if (f) f.setAttribute('tabindex', '0'); }
    });

    // ── მართვა ──
    function newPuzzle() {
        var P = makePuzzle(idx);
        tok = P.t.slice(); init = P.t.slice(); sol = P.sol;
        usedHint = false; done = false; drag = pick = null;
        $('dmCard').classList.remove('win'); $('dmNext').hidden = true; $('dmHint').disabled = false; $('dmReset').disabled = false;
        msg('გადაათრიე ციფრი — ან დააჭირე ციფრს, მერე ადგილს');
        render(); dots();
    }
    $('dmReset').onclick = function () { if (done) return; tok = init.slice(); drag = pick = null; render(); msg('ტოლობა დაბრუნდა საწყის მდგომარეობაში'); };
    $('dmHint').onclick = function () {
        if (done) return;
        usedHint = true; tok = init.slice(); pick = { t: sol.from[0], i: sol.from[1] }; render();
        var d = eqEl.querySelector('.dm-dg[data-t="' + sol.from[0] + '"][data-i="' + sol.from[1] + '"]');
        if (d) d.classList.add('hint');
        var to = sol.to[1] + (sol.to[0] === sol.from[0] && sol.to[1] >= sol.from[1] ? 1 : 0);
        var z = eqEl.querySelector('.dm-dz[data-t="' + sol.to[0] + '"][data-p="' + to + '"]');
        if (z) z.classList.add('hint');
        msg('🦊 ეს ციფრი გადაიტანე მონიშნულ ადგილას', 'tip');
    };
    $('dmNext').onclick = function () {
        var before = lvl(idx); idx++; newPuzzle();
        if (lvl(idx) > before) msg('🎉 ახალი დონე: ' + lvl(idx) + ' — ახლა ცოტა უფრო რთულია!', 'ok');
    };
    function save(ok) {
        var fd = new FormData(); fd.append('solved', ok ? 1 : 0); fd.append('level', idx + 1);
        fetch(FINISH_URL, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.new_achievements && j.new_achievements.length && window.showAchievementToasts) window.showAchievementToasts(j.new_achievements); })
            .catch(function () {});
    }
    $('dmGo').onclick = function () { $('dmOv').hidden = true; newPuzzle(); };
    tok = ['12', '+', '13', '=', '5']; init = tok.slice(); render(); dots();
})();
</script>
@endsection
