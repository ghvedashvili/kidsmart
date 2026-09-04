@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
@endpush
@section('content')
<style>
* { box-sizing: border-box; }
body { background: transparent !important; overflow: hidden; }
.wrap {
    max-width: 520px; margin: 0 auto; padding: 14px 16px 16px;
    min-height: calc(100dvh - 56px);
    display: flex; flex-direction: column;
}
@media (min-width: 760px)  { .wrap { max-width: 700px; } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } }
@media (max-width: 640px) { .wrap { min-height: calc(100dvh - 56px - 74px); } }

.header-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-shrink: 0; }
.game-stage { flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; }
.back-btn { display:inline-flex; align-items:center; gap:6px; flex-shrink:0; font-family:'Nunito',sans-serif; font-size:0.78rem; font-weight:800; color:#16a34a; text-decoration:none; background:white; border-radius:99px; padding:7px 14px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
h1 { margin: 0; font-family:'Fredoka One',cursive; font-size: 1.15rem; color: #14532d; flex: 1; text-align: left; }

/* ── game card ── */
#container {
    position: relative;
    width: min(420px, 92vw);
    height: min(540px, 58vh);
    min-height: 380px;
    margin: 0 auto;
    border-radius: 24px;
    overflow: hidden;
    background: linear-gradient(160deg, #f0fdf4 0%, #dcfce7 100%);
    border: 2px solid #bbf7d0;
    box-shadow: 0 14px 34px rgba(22,163,74,0.16);
    color: #14532d;
    font-family: 'Fredoka One', cursive;
    user-select: none;
}

#startScreen {
    position: absolute; inset: 0; background: rgba(240,253,244,0.97);
    display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 200;
    text-align: center; padding: 24px;
}
#startScreen .mem-icon { font-size: 2.6rem; margin-bottom: 10px; }
#startScreen h2 { font-family:'Fredoka One',cursive; font-size: 1.2rem; margin: 0 0 10px; color: #14532d; }
#startScreen p { color:#4d7c0f; font-family:'Nunito',sans-serif; font-weight:700; font-size:0.78rem; margin: 0 0 22px; max-width: 280px; line-height: 1.6; }
#startGame {
    background: linear-gradient(135deg,#10b981,#059669); color:#fff; border:none;
    padding: 13px 30px; border-radius: 14px; font-family:'Fredoka One',cursive; font-size: 0.95rem;
    cursor: pointer; box-shadow: 0 6px 20px rgba(16,185,129,0.4); transition: transform 0.15s;
}
#startGame:hover { transform: translateY(-2px); }
#startLeaderboardBtn {
    background: none; color: #b45309; border: none;
    padding: 10px 16px; margin-top: 10px; font-family: 'Fredoka One', cursive; font-size: 0.78rem;
    cursor: pointer; transition: opacity 0.15s;
}
#startLeaderboardBtn:hover { opacity: 0.7; }
#bestScoreLine { font-family:'Nunito',sans-serif; font-weight:800; font-size:0.7rem; color:#b45309; margin-top: 16px; }

#game { position: absolute; inset: 0; }
.foundTxt { position: absolute; left: 0; top: 16px; width: 100%; text-align: center; font-size: 0.95rem; color: #14532d; }
.timeTxt { position: absolute; left: 50%; bottom: 18px; transform: translateX(-50%); font-size: 1.7rem; text-align: center; color: #14532d; }
.timePlus { position: absolute; left: 50%; bottom: 54px; transform: translateX(-50%); font-size: 0.95rem; opacity: 0; color: #059669; }

#btnArea {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
    width: calc(100% - 40px); position: absolute; top: 46%; left: 50%; transform: translate(-50%, -50%);
    justify-items: center; align-items: center;
}
.btn {
    width: 100%; aspect-ratio: 1 / 1; max-width: 58px; border-radius: 50%; border: 3px solid #bbf7d0;
    text-align: center; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;
    cursor: pointer; background: #fff; opacity: 0; box-shadow: 0 2px 6px rgba(22,163,74,0.1);
}

#hintBtn, #leaderboardBtn {
    position: absolute; bottom: 12px; width: 44px; height: 44px; border-radius: 50%;
    font-size: 1.25rem; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}
#hintBtn { right: 12px; background: #0284c7; opacity: 0; }
#leaderboardBtn { left: 12px; background: #f59e0b; }

/* ── modals ── */
.mem-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(2,9,23,0.7); backdrop-filter: blur(5px);
    align-items: center; justify-content: center; padding: 16px;
}
.mem-modal-overlay.open { display: flex; }
.mem-modal-box {
    background: #fff; border-radius: 22px; padding: 26px 22px; width: 100%; max-width: 360px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; font-family: 'Nunito', sans-serif;
    max-height: 82vh; overflow-y: auto;
}
.mem-modal-box h2 { font-family:'Fredoka One',cursive; font-size: 1.15rem; color: #0f172a; margin: 0 0 10px; }
.mem-modal-box p { font-size: 0.9rem; color: #475569; font-weight: 700; margin: 0 0 6px; }
.mem-rank { font-family:'Fredoka One',cursive; font-size: 0.95rem; color: #059669; margin: 10px 0 18px; }
.mem-btn {
    width: 100%; padding: 12px; margin-top: 6px;
    font-family: 'Fredoka One', cursive; font-size: 0.85rem;
    background: linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius: 14px;
    cursor: pointer; transition: transform 0.15s; box-shadow: 0 6px 20px rgba(16,185,129,0.35);
}
.mem-btn:hover { transform: translateY(-2px); }
.mem-btn-ghost { background: #f1f5f9; color: #64748b; box-shadow: none; }

.lb-row {
    display: flex; align-items: center; gap: 10px; padding: 9px 8px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 800; color: #334155;
}
.lb-row.me { background: #ecfdf5; }
.lb-rank { width: 26px; text-align: center; flex-shrink: 0; }
.lb-name { flex: 1; min-width: 0; text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lb-score { flex-shrink: 0; color: #059669; }
.lb-row.top-1 .lb-rank { color: #d4af00; }
.lb-row.top-2 .lb-rank { color: #94a3b8; }
.lb-row.top-3 .lb-rank { color: #b45309; }
.lb-empty { font-size: 0.8rem; color: #94a3b8; font-weight: 700; padding: 20px 0; }
</style>

<div class="wrap">
    <div class="header-row">
        <a href="{{ route('games.index') }}" class="back-btn">← თამაშები</a>
        <h1>🧠 მეხსიერების თამაში</h1>
    </div>

    <div class="game-stage">
    <div id="container">
        <div id="startScreen">
            <div class="mem-icon">🧠</div>
            <h2>იპოვე ტყუპი სმაილები!</h2>
            <p>16 სმაილს შორის მხოლოდ ორი ერთნაირია — იპოვე ისინი რაც შეიძლება მალე. სწორ პასუხზე +3 წამი ემატება დროს!</p>
            <button id="startGame">▶ დაწყება</button>
            <button id="startLeaderboardBtn" type="button">🏆 ტაბლო</button>
            <div id="bestScoreLine" style="{{ $session->player_score > 0 ? '' : 'display:none;' }}">⭐ შენი რეკორდი: <span id="bestScoreVal">{{ $session->player_score }}</span> წყვილი</div>
        </div>

        <div id="game">
            <div class="foundTxt"></div>
            <div class="timeTxt">30:00</div>
            <div class="timePlus">+3 წმ</div>

            <div id="btnArea">
                <div id="b1" class="btn"></div>
            </div>
        </div>

        <div id="hintBtn">💡</div>
        <div id="leaderboardBtn">🏆</div>
    </div>
    </div>
</div>

{{-- Result modal --}}
<div id="resultModal" class="mem-modal-overlay">
    <div class="mem-modal-box">
        <h2>🎉 თამაში დასრულდა!</h2>
        <p>შენ იპოვე <span id="finalScore">0</span> წყვილი</p>
        <div id="rankDisplay" class="mem-rank"></div>
        <button id="submitName" class="mem-btn">🏆 ტაბლოს ნახვა</button>
    </div>
</div>

{{-- Leaderboard modal --}}
<div id="leaderboard" class="mem-modal-overlay">
    <div class="mem-modal-box">
        <h2>🏆 ტოპ 10</h2>
        <div id="leaderboardList"></div>
        <button id="closeLeaderboard" class="mem-btn mem-btn-ghost">დახურვა</button>
    </div>
</div>

<script>
(function() {

var btnArea = document.getElementById('btnArea'),
    hintBtn = document.getElementById('hintBtn'),
    leaderboardBtn = document.getElementById('leaderboardBtn'),
    resultModal = document.getElementById('resultModal'),
    finalScore = document.getElementById('finalScore'),
    rankDisplay = document.getElementById('rankDisplay'),
    leaderboard = document.getElementById('leaderboard'),
    leaderboardList = document.getElementById('leaderboardList'),
    closeLeaderboard = document.getElementById('closeLeaderboard'),
    startScreen = document.getElementById('startScreen'),
    startGameBtn = document.getElementById('startGame'),
    timeLimit = 30 * 1000,
    endAt,
    timeInt,
    lastBtn,
    hint,
    hintAt,
    hintShown = false,
    matched = false,
    art = ['😄','🤣','🙂','🙃','😉','😇','😍','🤥','😘','😚','😛','😜','😋','🤗','🤔','🤐','😶','🤑','😏','🙄','😳','😬','😴','🤕','🤠','🤧','😢','😵','😎','🤓','😡','🤢','😭','😫','😠'],
    found = 0,
    hintCount = 0,
    bestScore = {{ $session->player_score }};

for (var i = 1; i <= 16; i++) {
    var b;
    if (i == 1) b = document.getElementById('b1');
    else {
        b = document.getElementById('b1').cloneNode(true);
        b.id = 'b' + (i);
        btnArea.appendChild(b);
    }
    b.onclick = b.ontouchend = btnClick;
}

startGameBtn.addEventListener('click', function() {
    startScreen.style.display = 'none';
    found = 0;
    hintCount = 0;
    if (timeInt !== undefined) { clearInterval(timeInt); timeInt = undefined; }
    new TimelineMax({onStart: populate})
        .set('.foundTxt', {textContent: 'იპოვე ტყუპი სმაილები...', rotation: 0, scale: 1, y: 0})
        .set([btnArea, '.timeTxt'], {autoAlpha: 1})
        .set('.btn', {border: '3px solid #bbf7d0'});
});

function populate() {
    lastBtn = undefined;
    matched = false;
    hintAt = Date.now() + 5000;
    hintShown = false;
    TweenMax.set(hintBtn, {autoAlpha: 0, textContent: "💡", fontSize: 20});
    TweenMax.staggerFromTo('.btn', 0.3, {scale: 0.2, alpha: 0, rotation: 1}, {
        rotation: 0, alpha: 1, scale: 1,
        ease: Back.easeOut.config(4),
        stagger: {grid: [4, 4], from: "center", amount: 0.2}
    });

    var btns = [];
    for (var i = 0; i < 15; i++) makeNewNum();
    function makeNewNum() {
        var n = art[Math.floor(rand(0, art.length - 1))], valExists = false;
        for (var i = 0; i < btns.length; i++) if (n == btns[i]) valExists = true;
        (valExists) ? makeNewNum() : btns.push(n);
    }

    hint = btns[14];
    btns.push(btns[14]);
    shuffleArray(btns);
    for (var b = 1; b <= 16; b++) window['b' + b].textContent = btns[b - 1];
}

function btnClick(e) {
    if (e.type == 'touchend') e.currentTarget.onclick = undefined;
    if (matched) return;
    if (timeInt == undefined) {
        endAt = Date.now() + timeLimit;
        timeInt = setInterval(updateTime, 100);
    }

    var b = e.currentTarget;
    TweenMax.to(b, 0.05, {scale: 0.95, yoyo: true, repeat: 1});

    if (lastBtn != undefined && lastBtn != b) {
        if (b.textContent == lastBtn.textContent) {
            found++;
            matched = true;
            endAt += 3000;
            new TimelineMax({onComplete: populate})
                .set('.foundTxt', {textContent: 'ნაპოვნია: ' + found, fontWeight: 500}, 0)
                .to('.timePlus', 0.1, {autoAlpha: 1, yoyo: true, repeat: 1, repeatDelay: 0.4}, 0)
                .fromTo('.timePlus', 0.3, {scale: 0, rotation: 0.1}, {scale: 1, rotation: 0}, 0)
                .to([b, lastBtn], 0.1, {border: '3px solid #08c04d'}, 0)
                .to(b, 0.3, {rotation: 1, scale: 0.8, ease: Back.easeIn.config(7), yoyo: true, repeat: 1}, 0)
                .to(lastBtn, 0.3, {rotation: 1, scale: 0.8, ease: Back.easeIn.config(7), yoyo: true, repeat: 1}, 0)
                .to('.btn', 0.1, {border: '3px solid transparent'}, 0.5);
            return;
        } else {
            TweenMax.to(lastBtn, 0.1, {border: '3px solid transparent'});
        }
    }
    TweenMax.to(b, 0.1, {border: '3px solid #006da6'});
    lastBtn = e.currentTarget;
}

function updateTime() {
    var remaining = endAt - Date.now();
    if (remaining > 0) {
        var mil = Math.floor(remaining % 1000 / 10);
        var sec = Math.floor(remaining / 1000);
        if (mil < 10) mil = "0" + mil;
        if (sec < 10) sec = "0" + sec;
        TweenMax.set('.timeTxt', {textContent: sec + ":" + mil});
        if (!hintShown && Date.now() >= hintAt) {
            hintShown = true;
            TweenMax.to(hintBtn, 0.5, {autoAlpha: 1});
        }
    } else {
        clearInterval(timeInt);
        timeInt = undefined;
        gameOver();
    }
}

// keep the clock honest the instant the tab/screen becomes active again —
// don't wait for the next throttled interval tick while backgrounded
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && timeInt !== undefined) updateTime();
});

function gameOver() {
    reportFinish();
}

function csrfHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    };
}

function reportFinish() {
    finalScore.textContent = found;
    fetch("{{ route('games.memory.finish') }}", {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ pairs_found: found, hints_used: hintCount }),
    })
        .then(r => r.json())
        .then(data => {
            bestScore = data.best_score;
            if (data.is_best) {
                rankDisplay.innerHTML = '🎉 ახალი რეკორდი! ტოპ ' + data.rank + '-ე ადგილი';
            } else {
                rankDisplay.textContent = 'შენი საუკეთესო: ' + bestScore + ' წყვილი · ტოპ ' + data.rank + '-ე ადგილი';
            }
            if (bestScore > 0) {
                document.getElementById('bestScoreVal').textContent = bestScore;
                document.getElementById('bestScoreLine').style.display = '';
            }
            resultModal.classList.add('open');
        })
        .catch(() => { resultModal.classList.add('open'); });
}

function loadLeaderboard() {
    leaderboardList.innerHTML = '<div class="lb-empty">იტვირთება...</div>';
    fetch("{{ route('games.memory.leaderboard') }}", { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            var rows = data.leaderboard || [];
            if (!rows.length) {
                leaderboardList.innerHTML = '<div class="lb-empty">ჯერ არავის უთამაშია — იყავი პირველი!</div>';
                return;
            }
            leaderboardList.innerHTML = rows.map(function(row, i) {
                var medal = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : (i + 1);
                var cls = 'lb-row' + (row.is_me ? ' me' : '') + (i < 3 ? ' top-' + (i + 1) : '');
                return '<div class="' + cls + '">' +
                    '<span class="lb-rank">' + medal + '</span>' +
                    '<span class="lb-name">' + row.name + (row.is_me ? ' (შენ)' : '') + '</span>' +
                    '<span class="lb-score">' + row.score + '</span>' +
                    '</div>';
            }).join('');
        })
        .catch(() => { leaderboardList.innerHTML = '<div class="lb-empty">ვერ ჩაიტვირთა</div>'; });
}

hintBtn.onclick = hintBtn.ontouchend = function(e) {
    if (e.type == 'touchend') hintBtn.onclick = undefined;
    TweenMax.set(hintBtn, {textContent: hint, fontSize: 20});
    hintCount++;
};

function openLeaderboard() {
    loadLeaderboard();
    leaderboard.classList.add('open');
}

leaderboardBtn.onclick = leaderboardBtn.ontouchend = function(e) {
    if (e.type == 'touchend') leaderboardBtn.onclick = undefined;
    openLeaderboard();
};

document.getElementById('startLeaderboardBtn').addEventListener('click', openLeaderboard);

document.getElementById('submitName').addEventListener('click', function() {
    resultModal.classList.remove('open');
    loadLeaderboard();
    leaderboard.classList.add('open');
});

closeLeaderboard.addEventListener('click', function() {
    leaderboard.classList.remove('open');
    if (timeInt === undefined) {
        startScreen.style.display = 'flex';
    }
});

function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
}
function rand(min, max) {
    min = min || 0;
    max = max || 1;
    return min + (max - min) * Math.random();
}

})();
</script>
@endsection
