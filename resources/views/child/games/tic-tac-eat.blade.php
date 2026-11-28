@extends('layouts.app')
{{-- ცხოველების X-O (Tic-Tac-Eat): 3×3 დაფა. შენ — 🐰 კურდღლები, მელას — 🦊 მელიები; თითოეულს 3 პატარა, 3 საშუალო, 3 დიდი.
     უფრო დიდი ფიგურა მოწინააღმდეგის პატარაზე ჯდება და ადგილს იკავებს. მარაგი თაროზე დალაგებული ჩანს. --}}
@section('content')
@include('partials.achievement-toast')
<style>
    .te, .te-ov { --o: #E2590F; --o2: #C24A0A; --t1: #FFF3EA; --t2: #FFE3CC; --ink: #2B2622; --mut: #6B625A; --line: #ECE9E4; --ok: #1E8A4C; --bad: #C2372B; --fx: #6D4FE0; --fx2: #5A3CCB; --fxt: #EFEAFF; color: var(--ink); font-family: 'Noto Sans Georgian', system-ui, sans-serif; }
    .te { max-width: 520px; margin: 0 auto; padding: 6px 14px 12px; display: flex; flex-direction: column; gap: 10px; height: calc(100dvh - 70px); box-sizing: border-box; user-select: none; -webkit-user-select: none; touch-action: manipulation; }
    .te-hud { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-shrink: 0; }
    .te-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff; border-radius: 99px; padding: 6px 14px; font-weight: 900; font-size: 14px; box-shadow: 0 1px 0 var(--line); white-space: nowrap; }
    .te-pill small { font-size: 12px; font-weight: 800; color: var(--mut); }
    .te-turn { flex: 1; justify-content: center; font-size: 15px; transition: background .2s, color .2s; }
    .te-turn.me { background: var(--t1); color: var(--o2); }
    .te-turn.fox { background: var(--fxt); color: var(--fx2); }

    .te-tray { flex-shrink: 0; display: flex; align-items: center; gap: 12px; background: #fff; border-radius: 22px; padding: 10px 14px; box-shadow: 0 2px 0 var(--line); }
    .te-tray.fox { background: #FBFAFF; }
    .te-who { display: flex; align-items: center; gap: 8px; min-width: 0; flex: 0 0 auto; font-size: 13px; font-weight: 900; }
    .te-who svg { width: 34px; height: 34px; flex-shrink: 0; }
    .te-who .me-av { width: 34px; height: 34px; border-radius: 50%; background: var(--t2); color: inherit; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; }
    .te-pcs { flex: 1; display: flex; align-items: flex-end; justify-content: space-around; gap: 6px; }
    .te-pc { position: relative; border: 0; padding: 0 0 8px; background: none; cursor: pointer; display: flex; align-items: flex-end; justify-content: center; font-family: inherit; color: inherit; }
    .te-pc:disabled { cursor: default; opacity: 1; color: inherit; }
    /* მარაგი: დარჩენილი ფიგურები ერთმანეთის უკან დგანან */
    .te-stk { position: relative; display: block; width: calc(var(--d) + 2 * var(--dx)); height: calc(var(--d) + 2 * var(--dy)); }
    .te-pc.s1 { --d: 30px; --dx: 17px; --dy: 0px; --f: 24px; }
    .te-pc.s2 { --d: 40px; --dx: 23px; --dy: 0px; --f: 33px; }
    .te-pc.s3 { --d: 52px; --dx: 30px; --dy: 0px; --f: 43px; }
    .te-stk i { position: absolute; bottom: 0; width: var(--d); height: var(--d); display: flex; align-items: flex-end; justify-content: center; font-style: normal; font-size: var(--f); line-height: 1; filter: drop-shadow(0 3px 2px rgba(43, 38, 34, .2)); transition: transform .15s, filter .15s; }
    .te-stk i:nth-child(1) { left: 0; }
    .te-stk i:nth-child(2) { left: var(--dx); }
    .te-stk i:nth-child(3) { left: calc(2 * var(--dx)); bottom: 0; }
    .te-stk i.top { z-index: 2; }
    .te-stk .ghost { left: var(--dx); bottom: var(--dy); border: 2.5px dashed #D9D2C9; border-radius: 50%; box-sizing: border-box; filter: none; }
    .te-tray.me .te-pc:not(:disabled):hover .te-stk i.top { transform: translateY(-3px); }
    .te-pc.sel .te-stk i.top { transform: translateY(-6px) scale(1.1); filter: drop-shadow(0 5px 6px rgba(226, 89, 15, .6)); }
    .te-pc.sel::after { content: ''; position: absolute; left: 50%; bottom: 0; width: 70%; height: 4px; border-radius: 2px; background: var(--o); transform: translateX(-50%); }
    .te-pc:focus-visible { outline: 3px solid rgba(226, 89, 15, .45); outline-offset: 3px; border-radius: 12px; }

    .te-boardwrap { flex: 1 1 auto; min-height: 0; display: flex; align-items: center; justify-content: center; }
    .te-board { --sz: min(100%, 360px); width: var(--sz); aspect-ratio: 1; max-height: 100%; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); gap: 8px; padding: 8px; background: #F1E7DC; border-radius: 26px; box-shadow: 0 2px 0 var(--line), 0 12px 30px rgba(226, 89, 15, .08); box-sizing: border-box; }
    .te-cell { position: relative; border: 0; padding: 0; background: #fff; border-radius: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-family: inherit; transition: background .15s, box-shadow .15s; }
    .te-cell.me { background: var(--t2); }
    .te-cell.fox { background: var(--fxt); }
    .te-cell.can { background: #FFF8F1; box-shadow: inset 0 0 0 2.5px #FFC49A; }
    .te-cell.can:hover { background: var(--t1); }
    .te-cell.win { background: #E9FBE9; box-shadow: inset 0 0 0 3px var(--ok); }
    .te-cell.lose { background: #F2EDFF; box-shadow: inset 0 0 0 3px var(--fx); }
    .te-cell:focus-visible { outline: 3px solid rgba(226, 89, 15, .45); outline-offset: 2px; }
    .te-bp { border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; border: 3px solid; box-sizing: border-box; box-shadow: 0 4px 10px rgba(43, 38, 34, .14); animation: teIn .22s ease-out; }
    .te-bp { border: 0 !important; background: none !important; box-shadow: none !important; filter: drop-shadow(0 4px 3px rgba(43, 38, 34, .2)); }
    .te-bp.s1 { width: 40%; height: 40%; font-size: clamp(16px, 5vw, 26px); }
    .te-bp.s2 { width: 62%; height: 62%; font-size: clamp(24px, 8vw, 40px); }
    .te-bp.s3 { width: 86%; height: 86%; font-size: clamp(34px, 11vw, 58px); }
    .te-bp.eat { animation: teEat .35s ease-out; }
    @keyframes teIn { from { transform: scale(.3); opacity: .2; } to { transform: none; opacity: 1; } }
    @keyframes teEat { 0% { transform: scale(1.35); } 60% { transform: scale(.92); } 100% { transform: none; } }

    .te-msg { flex-shrink: 0; min-height: 20px; margin: 0; text-align: center; font-size: 13px; font-weight: 800; color: var(--bad); }
    .te-msg.tip { color: var(--mut); }
    @media (max-height: 700px) { .te { gap: 7px; } .te-tray { padding: 7px 12px; } .te-pc.s3 { --d: 46px; --dx: 26px; --f: 37px; } .te-pc.s2 { --d: 36px; --dx: 20px; --f: 29px; } .te-pc.s1 { --d: 28px; --dx: 15px; --f: 21px; } }
    @media (max-width: 420px) { .te-who .te-wn { display: none; } .te-tray { gap: 6px; padding-left: 10px; padding-right: 10px; } .te-pcs { gap: 2px; } }

    .te-ov { position: fixed; inset: 0; background: rgba(43, 38, 34, .55); display: flex; align-items: center; justify-content: center; padding: 18px; z-index: 1200; }
    .te-ov[hidden] { display: none; }
    .te-modal { background: #fff; border-radius: 26px; padding: 24px 20px 20px; width: 100%; max-width: 380px; text-align: center; display: flex; flex-direction: column; gap: 10px; align-items: center; max-height: calc(100dvh - 36px); overflow-y: auto; }
    .te-modal-ic { font-size: 44px; line-height: 1; }
    .te-modal-ic svg { width: 64px; height: 64px; }
    .te-logo { display: inline-flex; align-items: flex-end; gap: 2px; filter: drop-shadow(0 3px 2px rgba(43, 38, 34, .18)); }
    .te-logo i { font-style: normal; line-height: 1; }
    .te-logo { align-items: center; gap: 8px; } .te-logo i { font-size: 46px; } .te-logo b { font-size: 22px; font-weight: 900; color: var(--mut); }
    .te-modal h2 { margin: 0; font-size: 22px; font-weight: 900; }
    .te-modal p { margin: 0; color: var(--mut); font-size: 14px; line-height: 1.55; }
    .te-rules { width: 100%; margin: 0; padding: 10px 12px; list-style: none; background: var(--t1); border-radius: 16px; text-align: left; font-size: 13px; line-height: 1.5; color: var(--ink); display: flex; flex-direction: column; gap: 4px; }
    .te-rules li::before { content: '• '; color: var(--o); font-weight: 900; }
    .te-lv { display: flex; flex-direction: column; gap: 8px; width: 100%; }
    .te-lv button { border: 2px solid var(--line); border-radius: 16px; padding: 11px 14px; background: #fff; font-family: inherit; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 12px; }
    .te-lv button:hover { border-color: #FFC49A; background: var(--t1); }
    .te-lv i { font-style: normal; font-size: 22px; }
    .te-lv b { display: block; font-size: 15px; font-weight: 900; color: var(--ink); }
    .te-lv small { display: block; font-size: 12px; color: var(--mut); }
    .te-btn { width: 100%; height: 50px; border: 0; border-radius: 25px; background: var(--o); color: #fff; font-family: inherit; font-size: 16px; font-weight: 900; cursor: pointer; }
    .te-btn:hover { background: var(--o2); }
    .te-link { font-size: 14px; font-weight: 800; color: var(--o2); text-decoration: none; padding: 6px; }
    .te-score { display: flex; gap: 8px; justify-content: center; }
</style>

<div class="te" id="te">
    <div class="te-hud">
        <span class="te-pill" title="მოგება / წაგება"><small>მოგება</small> <b id="teW">{{ $session->wins }}</b> <small>· წაგება</small> <b id="teL">{{ $session->losses }}</b></span>
        <span class="te-pill te-turn" id="teTurn">…</span>
    </div>

    <div class="te-tray fox" aria-label="მელას მელიები">
        <span class="te-who">@include('partials.ks-fox', ['size' => 34, 'smile' => true]) <span class="te-wn">მელა</span></span>
        <div class="te-pcs" id="teFox"></div>
    </div>

    <div class="te-boardwrap"><div class="te-board" id="teBoard" role="grid" aria-label="დაფა 3×3"></div></div>

    <div class="te-tray me" aria-label="შენი კურდღლები">
        <span class="te-who"><span class="me-av" aria-hidden="true">🐰</span> <span class="te-wn">შენ</span></span>
        <div class="te-pcs" id="teMe"></div>
    </div>
    <p class="te-msg tip" id="teMsg" role="status">აირჩიე კურდღელი, მერე უჯრა</p>
</div>

<div class="te-ov" id="teOv">
    <div class="te-modal" role="dialog" aria-modal="true" aria-labelledby="teMTitle">
        <div class="te-modal-ic" id="teMIc"><span class="te-logo" aria-hidden="true"><i>🐰</i><b>×</b><i>🦊</i></span></div>
        <h2 id="teMTitle">{{ $game['name'] }}</h2>
        <ul class="te-rules" id="teRules">
            <li>შენ ხარ 🐰 კურდღლები, მელა — 🦊 მელიები. დაალაგე სამი კურდღელი ერთ ხაზზე — ჰორიზონტალურად, ვერტიკალურად ან დიაგონალზე.</li>
            <li>გყავს 3 პატარა, 3 საშუალო და 3 დიდი კურდღელი.</li>
            <li>უფრო დიდი კურდღელი მელიას პატარაზე დაჯდება და ადგილს წაართმევს. მელიაც იგივეს გააკეთებს!</li>
        </ul>
        <p id="teMText" hidden></p>
        <div class="te-lv" id="teLv">
            <button type="button" data-lv="1"><i>🙂</i><span><b>იოლი</b><small>მელა ახლა სწავლობს</small></span></button>
            <button type="button" data-lv="2"><i>⚡</i><span><b>საშუალო</b><small>მელა ფრთხილად თამაშობს</small></span></button>
            <button type="button" data-lv="3"><i>🔥</i><span><b>რთული</b><small>ეშმაკი მელა — სცადე, თუ შეგიძლია!</small></span></button>
        </div>
        <button type="button" class="te-btn" id="teAgain" hidden>კიდევ ვითამაშოთ</button>
        <a href="{{ route('games.index') }}" class="te-link" id="teBack" hidden>სხვა თამაშები</a>
    </div>
</div>

<script>
(function () {
    var FINISH_URL = @json(route('games.tic-tac-eat.finish')), CSRF = @json(csrf_token());
    var PC = { P: '🐰', C: '🦊' }, SZ = ['', 'პატარა', 'საშუალო', 'დიდი'], SZ_ERG = ['', 'პატარამ', 'საშუალომ', 'დიდმა'];
    var ME = 'P', FOX = 'C', LINES = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
    var $ = function (id) { return document.getElementById(id); };
    var board, inv, sel, turn, over, level = 2, lastEat = -1, lastAt = -1;

    // ── წესები ──
    function canPlace(b, i, size, who) { var t = b[i]; return !t || (t.o !== who && size > t.s); }
    function moves(b, iv, who) {
        var r = [];
        for (var s = 3; s >= 1; s--) { if (iv[who][s] <= 0) continue; for (var i = 0; i < 9; i++) if (canPlace(b, i, s, who)) r.push({ i: i, s: s }); }
        return r;
    }
    function winLine(b, who) { for (var k = 0; k < 8; k++) { var L = LINES[k]; if (L.every(function (i) { return b[i] && b[i].o === who; })) return L; } return null; }

    // ── მელას ჭკუა: minimax α-β-ით, სიღრმე დონის მიხედვით ──
    function evalPos(b) {
        var sc = 0;
        LINES.forEach(function (L) {
            var c = 0, p = 0, cs = 0, ps = 0;
            L.forEach(function (i) { if (!b[i]) return; if (b[i].o === FOX) { c++; cs += b[i].s; } else { p++; ps += b[i].s; } });
            if (c && !p) sc += Math.pow(10, c) + cs; else if (p && !c) sc -= Math.pow(10, p) + ps;
        });
        if (b[4]) sc += (b[4].o === FOX ? 15 : -15) * b[4].s / 2;
        return sc;
    }
    function apply(b, iv, m, who) { var old = b[m.i]; b[m.i] = { o: who, s: m.s }; iv[who][m.s]--; return old; }
    function undo(b, iv, m, who, old) { b[m.i] = old; iv[who][m.s]++; }
    function minimax(b, iv, depth, max, a, be) {
        if (winLine(b, FOX)) return 10000 + depth;
        if (winLine(b, ME)) return -10000 - depth;
        if (depth === 0) return evalPos(b);
        var who = max ? FOX : ME, ms = moves(b, iv, who);
        if (!ms.length) { var other = moves(b, iv, max ? ME : FOX); return other.length ? minimax(b, iv, depth - 1, !max, a, be) : 0; }
        var best = max ? -Infinity : Infinity;
        for (var k = 0; k < ms.length; k++) {
            var old = apply(b, iv, ms[k], who), v = minimax(b, iv, depth - 1, !max, a, be);
            undo(b, iv, ms[k], who, old);
            if (max) { if (v > best) best = v; if (v > a) a = v; } else { if (v < best) best = v; if (v < be) be = v; }
            if (be <= a) break;
        }
        return best;
    }
    function foxPick() {
        var ms = moves(board, inv, FOX);
        if (!ms.length) return null;
        if (board.every(function (c) { return !c; })) return { i: 4, s: 2 };
        // იოლ დონეზე: მოგებას/დაბლოკვას ხედავს, დანარჩენი — შემთხვევით
        if (level === 1) {
            var k, old;
            for (k = 0; k < ms.length; k++) { old = apply(board, inv, ms[k], FOX); var w = winLine(board, FOX); undo(board, inv, ms[k], FOX, old); if (w) return ms[k]; }
            if (Math.random() < .5) {
                var pm = moves(board, inv, ME);
                for (k = 0; k < pm.length; k++) { old = apply(board, inv, pm[k], ME); var lw = winLine(board, ME); undo(board, inv, pm[k], ME, old); if (lw) { var blk = ms.filter(function (m) { return m.i === pm[k].i; }); if (blk.length) return blk[0]; } }
            }
            return ms[Math.floor(Math.random() * ms.length)];
        }
        var filled = board.filter(Boolean).length, depth = level === 2 ? 2 : (filled < 3 ? 3 : 4);
        var best = -Infinity, pick = [];
        ms.forEach(function (m) {
            var old = apply(board, inv, m, FOX), v = minimax(board, inv, depth, false, -Infinity, Infinity);
            undo(board, inv, m, FOX, old);
            if (v > best) { best = v; pick = [m]; } else if (v === best) pick.push(m);
        });
        return pick[Math.floor(Math.random() * pick.length)];
    }

    // ── ხატვა ──
    function trayHtml(who) {
        return [1, 2, 3].map(function (s) {
            var n = inv[who][s], mine = who === ME, st = '';
            for (var k = 3 - n; k < 3; k++) st += '<i class="' + (k === 2 ? 'top' : '') + '" style="' + (k < 2 ? '' : '') + '">' + PC[who] + '</i>';
            if (!n) st = '<i class="ghost"></i>';
            return '<button type="button" class="te-pc s' + s + (n <= 0 ? ' out' : '') + (mine && sel === s ? ' sel' : '') + '" data-s="' + s + '"'
                + (mine && n > 0 && turn === ME && !over ? '' : ' disabled') + ' aria-label="' + SZ[s] + ' ' + (mine ? 'კურდღელი' : 'მელია') + ', დარჩა ' + n + '"'
                + (mine ? ' aria-pressed="' + (sel === s) + '"' : '') + '><span class="te-stk">' + st + '</span></button>';
        }).join('');
    }
    function render(win) {
        var bh = '';
        for (var i = 0; i < 9; i++) {
            var c = board[i], can = !over && turn === ME && sel && canPlace(board, i, sel, ME);
            var cls = 'te-cell' + (c ? (c.o === ME ? ' me' : ' fox') : '') + (can ? ' can' : '') + (win && win.L.indexOf(i) >= 0 ? (win.who === ME ? ' win' : ' lose') : '');
            bh += '<button type="button" class="' + cls + '" data-i="' + i + '" role="gridcell" aria-label="უჯრა ' + (i + 1) + (c ? (c.o === ME ? ', შენი ' + SZ[c.s] + ' კურდღელი' : ', მელას ' + SZ[c.s] + ' მელია') : ', ცარიელი') + '">'
                + (c ? '<span class="te-bp s' + c.s + ' ' + (c.o === ME ? 'me' : 'fox') + (i === lastEat ? ' eat' : '') + '"' + (i === lastAt || i === lastEat ? '' : ' style="animation:none"') + '>' + PC[c.o] + '</span>' : '') + '</button>';
        }
        $('teBoard').innerHTML = bh;
        $('teMe').innerHTML = trayHtml(ME);
        $('teFox').innerHTML = trayHtml(FOX);
        var t = $('teTurn');
        t.className = 'te-pill te-turn ' + (over ? '' : (turn === ME ? 'me' : 'fox'));
        if (!over) t.textContent = turn === ME ? 'შენი სვლაა 👆' : 'მელა ფიქრობს… 🦊';
    }
    function msg(t, tip) { var m = $('teMsg'); m.textContent = t || ''; m.className = 'te-msg' + (tip ? ' tip' : ''); }

    // ── სვლები ──
    function place(m, who) {
        var old = board[m.i];
        board[m.i] = { o: who, s: m.s }; inv[who][m.s]--;
        lastAt = m.i; lastEat = old ? m.i : -1;
        return old;
    }
    function afterMove(who) {
        var L = winLine(board, who);
        if (L) return finish(who === ME ? 'win' : 'lose', { L: L, who: who });
        var next = who === ME ? FOX : ME;
        if (!moves(board, inv, next).length) {
            if (!moves(board, inv, who).length) return finish('draw');
            next = who;   // მეორეს სავალი აღარ აქვს — ისევ იგივე აგრძელებს
            msg(who === ME ? 'მელას სავალი აღარ აქვს — ისევ შენ!' : 'შენ სავალი აღარ გაქვს — ისევ მელა თამაშობს', true);
        }
        turn = next;
        render();
        if (turn === FOX) setTimeout(foxTurn, 650);
    }
    function foxTurn() {
        if (over) return;
        var m = foxPick();
        if (!m) return afterMove(ME);
        var old = place(m, FOX);
        msg(old ? 'მელას ' + SZ_ERG[m.s] + ' 🦊 მელიამ შენს ' + SZ[old.s] + ' 🐰 კურდღელს ადგილი წაართვა! 😮' : '', true);
        afterMove(FOX);
    }
    $('teMe').addEventListener('click', function (e) {
        var b = e.target.closest('.te-pc'); if (!b || b.disabled) return;
        var s = +b.dataset.s;
        sel = sel === s ? null : s;
        msg(sel ? 'ახლა აირჩიე უჯრა ' + SZ[sel] + ' 🐰 კურდღლისთვის' : 'აირჩიე კურდღელი, მერე უჯრა', true);
        render();
    });
    $('teBoard').addEventListener('click', function (e) {
        var c = e.target.closest('.te-cell'); if (!c || over || turn !== ME) return;
        var i = +c.dataset.i;
        if (!sel) { msg('ჯერ ქვემოთ აირჩიე კურდღელი 👇'); return; }
        if (!canPlace(board, i, sel, ME)) { msg(board[i] && board[i].o === ME ? 'აქ უკვე შენი კურდღელია' : SZ[sel] + ' კურდღელი ' + SZ[board[i].s] + ' მელიას ვერ აჯობებს — აირჩიე უფრო დიდი'); return; }
        var m2 = { i: i, s: sel }, old = place(m2, ME);
        sel = null;
        msg(old ? 'შენმა ' + SZ_ERG[m2.s] + ' 🐰 კურდღელმა ' + SZ[old.s] + ' 🦊 მელიას ადგილი წაართვა! 😋' : '', true);
        afterMove(ME);
    });

    // ── თამაშის დაწყება / დასრულება ──
    function start(lv) {
        level = lv || level;
        board = [null, null, null, null, null, null, null, null, null];
        inv = { P: { 1: 3, 2: 3, 3: 3 }, C: { 1: 3, 2: 3, 3: 3 } };
        sel = null; over = false; lastAt = lastEat = -1;
        turn = Math.random() < .5 ? ME : FOX;
        $('teOv').hidden = true;
        msg(turn === ME ? 'შენ იწყებ! აირჩიე კურდღელი, მერე უჯრა' : 'მელა იწყებს…', true);
        render();
        if (turn === FOX) setTimeout(foxTurn, 700);
    }
    function finish(res, win) {
        over = true; sel = null;
        render(win);
        var t = $('teTurn'); t.className = 'te-pill te-turn ' + (res === 'win' ? 'me' : (res === 'lose' ? 'fox' : ''));
        t.textContent = res === 'win' ? 'შენ მოიგე! 🏆' : (res === 'lose' ? 'მელამ მოიგო 🦊' : 'ფრე 🤝');
        msg('', true);
        var fd = new FormData(); fd.append('result', res);
        fetch(FINISH_URL, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.wins !== undefined) { $('teW').textContent = j.wins; $('teL').textContent = j.losses; }
                if (j.new_achievements && j.new_achievements.length && window.showAchievementToasts) window.showAchievementToasts(j.new_achievements);
            }).catch(function () {});
        setTimeout(function () {
            $('teMIc').textContent = res === 'win' ? '🏆' : (res === 'lose' ? '🦊' : '🤝');
            $('teMTitle').textContent = res === 'win' ? 'შენ მოიგე!' : (res === 'lose' ? 'ამჯერად მელამ მოიგო' : 'ფრე!');
            $('teMText').textContent = res === 'win' ? 'ბრწყინვალე! კურდღლებმა მელიებს აჯობეს 🐰🎉' : (res === 'lose' ? 'არაუშავს — სცადე კიდევ, ახლა უკვე იცი მისი ხრიკები!' : 'ძლიერი თამაში იყო ორივესგან!');
            $('teMText').hidden = false; $('teRules').hidden = true;
            $('teAgain').hidden = false; $('teBack').hidden = false;
            $('teLv').hidden = true;
            $('teOv').hidden = false;
        }, 1400);
    }
    $('teLv').addEventListener('click', function (e) { var b = e.target.closest('[data-lv]'); if (b) start(+b.dataset.lv); });
    $('teAgain').onclick = function () { start(level); };
    board = [null, null, null, null, null, null, null, null, null];
    inv = { P: { 1: 3, 2: 3, 3: 3 }, C: { 1: 3, 2: 3, 3: 3 } };
    turn = ME; over = true; render();
    $('teTurn').textContent = 'აირჩიე დონე';
})();
</script>
@endsection
