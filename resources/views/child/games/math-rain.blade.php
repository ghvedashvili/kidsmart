@extends('layouts.app')
{{-- რიცხვების წვიმა: მაგალითები ზემოდან ცვივა — ჩაწერე პასუხი, სანამ წითელ ხაზს მიაღწევს.
     3 სიცოცხლე, კომბო (ზედიზედ სწორი → ქულა მრავლდება), თანდათან ჩქარდება. --}}
@section('content')
@include('partials.achievement-toast')
<style>
    .mr, .mr-ov { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; --ok: #1E8A4C; --bad: #C2372B; color: var(--ink); font-family: 'Noto Sans Georgian', system-ui, sans-serif; }
    .mr { max-width: 560px; margin: 0 auto; padding: 8px 14px 12px; display: flex; flex-direction: column; gap: 10px; height: calc(100dvh - 70px); box-sizing: border-box; user-select: none; -webkit-user-select: none; touch-action: manipulation; }
    .mr-hud { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-shrink: 0; }
    .mr-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff; border-radius: 99px; padding: 6px 14px; font-weight: 900; font-size: 15px; box-shadow: 0 1px 0 var(--line); }
    .mr-pill small { font-size: 12px; font-weight: 800; color: var(--mut); }
    .mr-pill.combo { color: var(--o2); background: var(--t1); }
    .mr-hearts { font-size: 18px; letter-spacing: 2px; }
    .mr-field { position: relative; flex: 1 1 auto; min-height: 180px; border-radius: 26px; overflow: hidden; background: linear-gradient(180deg, #FFFFFF 0%, #FFF3EA 100%); box-shadow: 0 2px 0 var(--line), 0 12px 30px rgba(226, 89, 15, .08); }
    .mr-field canvas { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
    .mr-panel { flex-shrink: 0; display: flex; flex-direction: column; gap: 8px; }
    .mr-input { height: 52px; border-radius: 16px; background: #fff; border: 2.5px solid var(--o); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 900; letter-spacing: 3px; color: var(--ink); }
    .mr-input:empty::before { content: 'შენი პასუხი'; font-size: 15px; font-weight: 700; letter-spacing: 0; color: #B3A99F; }
    .mr-input.bad { border-color: var(--bad); animation: mrShake .3s; }
    .mr-keys { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .mr-key { height: 50px; border: 0; border-radius: 16px; background: #fff; box-shadow: 0 2px 0 var(--line), 0 6px 14px rgba(43, 38, 34, .06); font-family: inherit; font-size: 22px; font-weight: 900; color: var(--ink); cursor: pointer; transition: transform .06s; }
    .mr-key:active { transform: translateY(2px); background: var(--t1); }
    .mr-key.clr { color: var(--bad); font-size: 16px; }
    .mr-key.ok { background: var(--o); color: #fff; font-size: 24px; }
    .mr-key.ok:active { background: var(--o2); }
    .mr-key:focus-visible { outline: 3px solid rgba(226, 89, 15, .4); outline-offset: 2px; }
    @keyframes mrShake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }
    @media (max-height: 700px) { .mr-key { height: 42px; font-size: 19px; } .mr-input { height: 44px; font-size: 24px; } .mr { gap: 8px; } }

    .mr-ov { position: fixed; inset: 0; background: rgba(43, 38, 34, .55); display: flex; align-items: center; justify-content: center; padding: 18px; z-index: 1200; }
    .mr-ov[hidden] { display: none; }
    .mr-modal { background: #fff; border-radius: 26px; padding: 26px 20px 20px; width: 100%; max-width: 360px; text-align: center; display: flex; flex-direction: column; gap: 10px; align-items: center; }
    .mr-modal-ic { font-size: 46px; line-height: 1; }
    .mr-modal h2 { margin: 0; font-size: 22px; font-weight: 900; }
    .mr-modal p { margin: 0; color: var(--mut); font-size: 14px; line-height: 1.55; }
    .mr-lv { display: flex; flex-direction: column; gap: 8px; width: 100%; }
    .mr-lv button { border: 2px solid var(--line); border-radius: 16px; padding: 12px 14px; background: #fff; font-family: inherit; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 12px; }
    .mr-lv button:hover { border-color: #FFC49A; }
    .mr-lv button.rec { border-color: var(--o); background: var(--t1); }
    .mr-lv b { display: block; font-size: 15px; font-weight: 900; color: var(--ink); }
    .mr-lv small { font-size: 12px; color: var(--mut); font-weight: 600; }
    .mr-lv i { font-style: normal; font-size: 22px; width: 32px; text-align: center; }
    .mr-best { font-size: 13px; font-weight: 800; color: var(--o2); background: var(--t1); border-radius: 99px; padding: 4px 12px; }
    .mr-link { color: var(--o2); font-weight: 800; font-size: 13px; text-decoration: none; }
</style>

<div class="mr" id="mr">
    <div class="mr-hud">
        <span class="mr-pill"><small>ქულა</small> <span id="mrScore">0</span></span>
        <span class="mr-pill combo" id="mrComboBox">კომბო ×<span id="mrCombo">1</span></span>
        <span class="mr-pill mr-hearts" id="mrLives" aria-label="სიცოცხლე">❤️❤️❤️</span>
    </div>
    <div class="mr-field" id="mrField">
        <canvas id="mrCanvas" aria-label="ცვივა მათემატიკური მაგალითები"></canvas>
    </div>
    <div class="mr-panel">
        <div class="mr-input" id="mrInput" aria-live="polite"></div>
        <div class="mr-keys" id="mrKeys">
            @foreach(['1','2','3','4','5','6','7','8','9'] as $__k)
            <button type="button" class="mr-key" data-k="{{ $__k }}">{{ $__k }}</button>
            @endforeach
            <button type="button" class="mr-key clr" data-k="C" aria-label="წაშლა">წაშლა</button>
            <button type="button" class="mr-key" data-k="0">0</button>
            <button type="button" class="mr-key ok" data-k="ENTER" aria-label="შემოწმება">✓</button>
        </div>
    </div>
</div>

<div class="mr-ov" id="mrOv">
    <div class="mr-modal" role="dialog" aria-modal="true" aria-labelledby="mrMTitle">
        <div class="mr-modal-ic" id="mrMIc">{{ $game['icon'] }}</div>
        <h2 id="mrMTitle">{{ $game['name'] }}</h2>
        <p id="mrMText">დაწერე სწორი პასუხი, სანამ მაგალითი წითელ ხაზამდე ჩამოვა. ზედიზედ სწორი პასუხები ქულას ამრავლებს!</p>
        <span class="mr-best" id="mrMBest" @if(!$best) hidden @endif>შენი რეკორდი: <b id="mrBestVal">{{ $best }}</b> ქულა</span>
        <div class="mr-lv" id="mrLv">
            <button type="button" data-lv="easy" class="{{ $grade <= 2 ? 'rec' : '' }}"><i>🙂</i><span><b>იოლი</b><small>მიმატება და გამოკლება</small></span></button>
            <button type="button" data-lv="medium" class="{{ $grade === 3 ? 'rec' : '' }}"><i>⚡</i><span><b>საშუალო</b><small>გამრავლება და გაყოფა</small></span></button>
            <button type="button" data-lv="hard" class="{{ $grade >= 4 ? 'rec' : '' }}"><i>🔥</i><span><b>რთული</b><small>ყველა მოქმედება, უფრო სწრაფად</small></span></button>
        </div>
        <a href="{{ route('games.index') }}" class="mr-link" id="mrMBack" hidden>სხვა თამაშები</a>
    </div>
</div>

<script>
(function () {
    var FINISH_URL = @json(route('games.math-rain.finish')), CSRF = @json(csrf_token());
    var BEST = @json($best);
    var $ = function (id) { return document.getElementById(id); };
    var root = $('mr'), field = $('mrField'), canvas = $('mrCanvas'), ctx = canvas.getContext('2d');
    var W = 0, H = 0, DPR = 1;

    // ── ზომა: თამაში ზუსტად ეტევა ეკრანზე (გვერდი არ სქროლდება) ──
    function fitHeight() {
        var top = root.getBoundingClientRect().top + window.scrollY;
        root.style.height = Math.max(420, window.innerHeight - top) + 'px';
        var r = field.getBoundingClientRect();
        DPR = Math.min(2, window.devicePixelRatio || 1);
        W = r.width; H = r.height;
        canvas.width = Math.round(W * DPR); canvas.height = Math.round(H * DPR);
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        render();
    }
    addEventListener('resize', fitHeight);
    addEventListener('orientationchange', function () { setTimeout(fitHeight, 200); });

    // ── ხმა ──
    var actx = null;
    function tone(f1, f2, d, type) {
        try {
            actx = actx || new (window.AudioContext || window.webkitAudioContext)();
            var o = actx.createOscillator(), g = actx.createGain(), t = actx.currentTime;
            o.type = type || 'triangle'; o.connect(g); g.connect(actx.destination);
            o.frequency.setValueAtTime(f1, t); o.frequency.exponentialRampToValueAtTime(f2, t + d);
            g.gain.setValueAtTime(.2, t); g.gain.exponentialRampToValueAtTime(.0001, t + d);
            o.start(t); o.stop(t + d);
        } catch (e) {}
    }
    var sfx = { hit: function () { tone(440, 880, .15); }, miss: function () { tone(220, 110, .3, 'sawtooth'); }, tap: function () { tone(520, 540, .05, 'sine'); } };

    // ── მდგომარეობა ──
    var running = false, score = 0, combo = 1, lives = 3, solved = 0, input = '', level = 'easy';
    var items = [], parts = [], lastSpawn = 0, spawnEvery = 3200, speedMul = .5, lastT = 0;
    var GROUND = 0;   // წითელი ხაზის სიმაღლე

    var rnd = function (a, b) { return a + Math.floor(Math.random() * (b - a + 1)); };
    function problem() {
        var type = level === 'hard' ? (Math.random() < .5 ? 'easy' : 'medium') : level, text, ans;
        if (type === 'easy') {
            var a = rnd(1, 12), b = rnd(1, 12);
            if (Math.random() > .4) { text = a + ' + ' + b; ans = a + b; }
            else { var mx = Math.max(a, b), mn = Math.min(a, b); text = mx + ' − ' + mn; ans = mx - mn; }
        } else if (Math.random() > .3) {
            var x = rnd(2, 9), y = rnd(2, 10); text = x + ' × ' + y; ans = x * y;
        } else {
            var d = rnd(2, 8), q = rnd(2, 9); text = (d * q) + ' ÷ ' + d; ans = q;
        }
        ctx.font = '900 20px "Noto Sans Georgian", system-ui, sans-serif';
        var r = Math.max(34, ctx.measureText(text).width / 2 + 16);
        // ერთმანეთზე რომ არ დაეცეს — სხვა ბუშტისგან მოშორებით ვიწყებთ
        var x0, tries = 0;
        do { x0 = r + 6 + Math.random() * Math.max(1, W - 2 * r - 12); tries++; }
        while (tries < 8 && items.some(function (it) { return it.y < 120 && Math.abs(it.x - x0) < it.r + r + 6; }));
        var base = level === 'hard' ? .62 : .5;
        return { x: x0, y: -r, r: r, text: text, ans: ans, v: (base + Math.random() * .4) * speedMul * (H / 600 + .4) };
    }

    function boom(x, y, good) {
        for (var i = 0; i < 18; i++) parts.push({ x: x, y: y, vx: (Math.random() - .5) * 7, vy: (Math.random() - .5) * 7, life: 1, c: good ? (Math.random() < .5 ? '#E2590F' : '#FFB347') : '#C2372B' });
    }

    function setHud() {
        $('mrScore').textContent = score;
        $('mrCombo').textContent = combo;
        $('mrLives').textContent = '❤️'.repeat(Math.max(0, lives)) + '🤍'.repeat(Math.max(0, 3 - lives));
    }

    function press(k) {
        if (!running) return;
        if (k === 'C') input = '';
        else if (k === 'ENTER') return check();
        else if (input.length < 4) { input += k; sfx.tap(); }
        $('mrInput').textContent = input;
    }
    function check() {
        if (!input) return;
        var val = parseInt(input, 10), idx = -1, maxY = -1e9;
        items.forEach(function (it, i) { if (it.ans === val && it.y > maxY) { maxY = it.y; idx = i; } });
        if (idx >= 0) {
            var it = items.splice(idx, 1)[0];
            boom(it.x, it.y, true); sfx.hit();
            score += 10 * combo; combo++; solved++;
        } else {
            combo = 1; sfx.miss();
            var box = $('mrInput'); box.classList.remove('bad'); void box.offsetWidth; box.classList.add('bad');
        }
        input = ''; $('mrInput').textContent = '';
        setHud();
    }

    $('mrKeys').addEventListener('click', function (e) { var b = e.target.closest('[data-k]'); if (b) press(b.dataset.k); });
    addEventListener('keydown', function (e) {
        if (!running) return;
        if (e.key >= '0' && e.key <= '9') { press(e.key); e.preventDefault(); }
        else if (e.key === 'Backspace') { input = input.slice(0, -1); $('mrInput').textContent = input; e.preventDefault(); }
        else if (e.key === 'Escape' || e.key === 'c' || e.key === 'C') press('C');
        else if (e.key === 'Enter') { press('ENTER'); e.preventDefault(); }
    });
    // თამაშის ველზე თითის მოძრაობამ გვერდი არ ასრიალოს
    root.addEventListener('touchmove', function (e) { e.preventDefault(); }, { passive: false });

    function start(lv) {
        level = lv; score = 0; combo = 1; lives = 3; solved = 0; input = '';
        items = []; parts = [];
        spawnEvery = lv === 'hard' ? 2600 : 3200; speedMul = .5;
        $('mrInput').textContent = '';
        setHud();
        $('mrOv').hidden = true;
        fitHeight();
        running = true;
        lastT = performance.now(); lastSpawn = lastT - spawnEvery + 500;
        requestAnimationFrame(loop);
    }

    function update(dt, now) {
        if (now - lastSpawn > spawnEvery) {
            items.push(problem()); lastSpawn = now;
            if (spawnEvery > 1800) spawnEvery -= 30;
            speedMul = Math.min(.95, speedMul + .004);
        }
        for (var i = items.length - 1; i >= 0; i--) {
            var it = items[i];
            it.y += it.v * (dt / 16);
            if (it.y + it.r * .4 >= GROUND) {
                boom(it.x, GROUND, false); sfx.miss();
                items.splice(i, 1);
                lives--; combo = 1; setHud();
                if (lives <= 0) { finish(); return; }
            }
        }
        for (var j = parts.length - 1; j >= 0; j--) {
            var p = parts[j]; p.x += p.vx; p.y += p.vy; p.vy += .15; p.life -= .03;
            if (p.life <= 0) parts.splice(j, 1);
        }
    }

    function render() {
        if (!W) return;
        ctx.clearRect(0, 0, W, H);
        GROUND = H - 18;
        // საშიში ხაზი
        ctx.save();
        ctx.setLineDash([8, 8]); ctx.strokeStyle = 'rgba(194, 55, 43, .55)'; ctx.lineWidth = 2;
        ctx.beginPath(); ctx.moveTo(10, GROUND); ctx.lineTo(W - 10, GROUND); ctx.stroke();
        ctx.restore();
        items.forEach(function (it) {
            var danger = Math.max(0, Math.min(1, (it.y - GROUND * .55) / (GROUND * .45)));
            ctx.save();
            ctx.beginPath(); ctx.arc(it.x, it.y, it.r, 0, Math.PI * 2);
            ctx.fillStyle = '#FFFFFF';
            ctx.shadowColor = 'rgba(226, 89, 15, .25)'; ctx.shadowBlur = 12; ctx.shadowOffsetY = 4;
            ctx.fill();
            ctx.shadowColor = 'transparent';
            ctx.lineWidth = 3;
            ctx.strokeStyle = danger > .6 ? '#C2372B' : '#E2590F';
            ctx.stroke();
            ctx.font = '900 20px "Noto Sans Georgian", system-ui, sans-serif';
            ctx.fillStyle = '#2B2622'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.fillText(it.text, it.x, it.y + 1);
            ctx.restore();
        });
        parts.forEach(function (p) {
            ctx.save(); ctx.globalAlpha = Math.max(0, p.life); ctx.fillStyle = p.c;
            ctx.beginPath(); ctx.arc(p.x, p.y, 4, 0, Math.PI * 2); ctx.fill(); ctx.restore();
        });
    }

    function loop(now) {
        if (!running) return;
        var dt = Math.min(50, now - lastT);   // ჩანართიდან დაბრუნებისას ყველაფერი ერთბაშად არ ჩამოვარდეს
        lastT = now;
        update(dt, now);
        render();
        if (running) requestAnimationFrame(loop);
    }
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { lastT = performance.now(); } });

    function finish() {
        running = false;
        render();
        var rec = score > BEST;
        $('mrMIc').textContent = rec ? '🏆' : '💪';
        $('mrMTitle').textContent = rec ? 'ახალი რეკორდი!' : 'სიცოცხლეები გათავდა';
        $('mrMText').textContent = 'ამოხსენი ' + solved + ' მაგალითი და დააგროვე ' + score + ' ქულა. ' + (rec ? 'გილოცავ!' : 'სცადე კიდევ — გამოგივა!') + ' აირჩიე დონე და ითამაშე თავიდან:';
        $('mrMBack').hidden = false;
        $('mrOv').hidden = false;
        var fd = new FormData();
        fd.append('score', score); fd.append('solved', solved); fd.append('level', level);
        fetch(FINISH_URL, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.best_score) { BEST = j.best_score; $('mrBestVal').textContent = j.best_score; $('mrMBest').hidden = false; }
                if (j.new_achievements && j.new_achievements.length && window.showAchievementToasts) window.showAchievementToasts(j.new_achievements);
            })
            .catch(function () {});
    }

    $('mrLv').addEventListener('click', function (e) { var b = e.target.closest('[data-lv]'); if (b) start(b.dataset.lv); });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fitHeight); else fitHeight();
    setTimeout(fitHeight, 300);
})();
</script>
@endsection
