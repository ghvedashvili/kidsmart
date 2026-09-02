@extends('layouts.app')
@section('content')
<style>
* { box-sizing: border-box; }
body { background: transparent !important; font-family: "Segoe UI", system-ui, -apple-system, sans-serif; }

.wrap { max-width: 520px; margin: 0 auto; padding: 14px 16px 16px; }
@media (min-width: 760px)  { .wrap { max-width: 700px; } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } }

.game { width: 100%; text-align: center; color: #1b5e20; }

.header-row { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
.back-btn { display:inline-flex; align-items:center; gap:6px; flex-shrink:0; font-family:'Nunito',sans-serif; font-size:0.78rem; font-weight:800; color:#16a34a; text-decoration:none; background:white; border-radius:99px; padding:7px 14px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
h1 { margin: 0; font-size: 20px; color: #1b5e20; flex: 1; text-align: left; }
.subtitle { color: #43a047; font-size: 12px; font-weight: 600; margin: 2px 0 8px; text-align: left; }

.status-box {
    background: #ffffff;
    border: 2px solid #a5d6a7;
    border-radius: 16px;
    padding: 7px 12px;
    margin-bottom: 10px;
    box-shadow: 0 6px 14px rgba(46, 125, 50, 0.12);
}
.status { font-size: 14px; font-weight: 800; color: #2e7d32; min-height: 18px; }

.board-wrap {
    position: relative;
    width: min(380px, 90vw, 38vh);
    aspect-ratio: 380 / 500;
    margin: 0 auto;
}
.board {
    position: absolute;
    top: 0; left: 0;
    width: 380px;
    height: 500px;
    transform-origin: top left;
    background: radial-gradient(circle at 50% 30%, #ffffff 0%, #e8f5e9 100%);
    border: 6px solid #388e3c;
    border-radius: 32px;
    box-shadow: 0 16px 32px rgba(27, 94, 32, 0.2);
    overflow: hidden;
}
.board svg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; z-index: 1; }
.game-line { stroke: #4caf50; stroke-width: 5; stroke-linecap: round; opacity: 0.9; }

.node {
    position: absolute;
    width: 56px; height: 56px;
    margin-left: -28px; margin-top: -28px;
    border-radius: 50%;
    background: #ffffff;
    border: 4px solid #2e7d32;
    display: flex; justify-content: center; align-items: center;
    font-size: 30px;
    cursor: pointer;
    z-index: 3;
    user-select: none;
    transition: transform 0.18s, background 0.18s, box-shadow 0.18s;
}
.node:hover { transform: scale(1.08); }
.node.selected {
    background: #fff9c4;
    border-color: #fbc02d;
    transform: scale(1.18);
    box-shadow: 0 0 0 5px rgba(251, 192, 45, 0.2);
}
.node.possible {
    background: #a5d6a7;
    border-color: #1b5e20;
    animation: pulse 1.2s infinite;
}
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4); }
    50%      { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
}

.restart-btn {
    width: min(380px, 90vw, 38vh);
    margin-top: 10px;
    padding: 9px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
    color: white;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(46, 125, 50, 0.25);
    transition: transform 0.15s;
}
.restart-btn:hover { transform: translateY(-2px); }
.restart-btn:active { transform: translateY(0); }

.stats-box {
    margin-top: 10px;
    padding: 7px 12px;
    background: #ffffff;
    border: 2px solid #a5d6a7;
    border-radius: 12px;
    text-align: center;
    font-size: 0.72rem;
}
.stats-box p { margin: 2px 0; color: #388e3c; }
.stats-box span { font-weight: 800; color: #1b5e20; }
</style>

<div class="wrap">
    <div class="game">
        <div class="header-row">
            <a href="{{ route('games.index') }}" class="back-btn">← თამაშები</a>
            <h1>🐰 ბაჭია და მგლები 🐺</h1>
        </div>
        <div class="subtitle">შენ ხარ 3 მგელი — დაიჭირე Kidsmart-ის ბაჭია!</div>

        <div class="status-box">
            <div class="status" id="status">თამაში იწყება...</div>
        </div>

        <div class="board-wrap" id="boardWrap">
            <div class="board" id="board">
                <svg id="lines"></svg>
            </div>
        </div>

        <button class="restart-btn" onclick="newGame()">🔄 ხელახლა დაწყება</button>

        <div class="stats-box">
            <p>შენ vs Kidsmart: <span id="personal-score">{{ $session->wins }} - {{ $session->losses }}</span></p>
            <p>ყველა ბავშვი vs Kidsmart: <span id="overall-score">{{ $global->total_wins }} - {{ $global->total_losses }}</span></p>
        </div>
    </div>
</div>

<script>
(function() {

const nodes = [
    { id: 0,  x: 190, y: 45 },
    { id: 1,  x: 90,  y: 135 },
    { id: 2,  x: 190, y: 135 },
    { id: 3,  x: 290, y: 135 },
    { id: 4,  x: 90,  y: 245 },
    { id: 5,  x: 190, y: 245 },
    { id: 6,  x: 290, y: 245 },
    { id: 7,  x: 90,  y: 355 },
    { id: 8,  x: 190, y: 355 },
    { id: 9,  x: 290, y: 355 },
    { id: 10, x: 190, y: 445 }
];

const adj = {
    0:  [1, 2, 3],
    1:  [0, 2, 4, 5],
    2:  [0, 1, 3, 5],
    3:  [0, 2, 5, 6],
    4:  [1, 5, 7],
    5:  [1, 2, 3, 4, 6, 7, 8, 9],
    6:  [3, 5, 9],
    7:  [4, 5, 8, 10],
    8:  [5, 7, 9, 10],
    9:  [5, 6, 8, 10],
    10: [7, 8, 9]
};

let wolves = [];
let hare = 0;
let currentTurn = 'USER';
let selectedWolf = null;
let gameOver = false;

const personalScoreDisplay = document.getElementById('personal-score');
const overallScoreDisplay  = document.getElementById('overall-score');
const boardWrap = document.getElementById('boardWrap');
const board = document.getElementById('board');

function scaleBoard() {
    const scale = boardWrap.clientWidth / 380;
    board.style.transform = 'scale(' + scale + ')';
}
window.addEventListener('resize', scaleBoard);

function csrfHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    };
}

function saveState() {
    fetch("{{ route('games.wolves-hare.state') }}", {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ state: { wolves, hare, currentTurn, gameOver } }),
    }).catch(() => {});
}

function reportFinish(userWon) {
    fetch("{{ route('games.wolves-hare.finish') }}", {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ result: userWon ? 'win' : 'lose' }),
    })
        .then(r => r.json())
        .then(data => {
            personalScoreDisplay.textContent = `${data.wins} - ${data.losses}`;
            overallScoreDisplay.textContent = `${data.global.total_wins} - ${data.global.total_losses}`;
        })
        .catch(() => {});
}

function newGame() {
    gameOver = false;
    selectedWolf = null;

    wolves = [7, 9, 10];
    hare = 0;
    currentTurn = 'AI';
    document.getElementById("status").textContent = "ბაჭია იწყებს... 🐰";
    drawLines();
    render();
    saveState();
    setTimeout(aiHareMove, 500);
}

function drawLines() {
    const svg = document.getElementById("lines");
    svg.innerHTML = "";
    const drawn = new Set();

    for (const from in adj) {
        adj[from].forEach(to => {
            const key = Math.min(from, to) + "-" + Math.max(from, to);
            if (drawn.has(key)) return;
            drawn.add(key);

            const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
            line.setAttribute("x1", nodes[from].x);
            line.setAttribute("y1", nodes[from].y);
            line.setAttribute("x2", nodes[to].x);
            line.setAttribute("y2", nodes[to].y);
            line.classList.add("game-line");
            svg.appendChild(line);
        });
    }
}

function getWolfMoves(fromNode) {
    return adj[fromNode].filter(to => {
        if (nodes[to].y > nodes[fromNode].y + 10) return false;
        if (wolves.includes(to) || hare === to) return false;
        return true;
    });
}

function getHareMoves() {
    return adj[hare].filter(to => !wolves.includes(to));
}

function render() {
    const boardEl = document.getElementById("board");
    boardEl.querySelectorAll(".node").forEach(n => n.remove());

    nodes.forEach(node => {
        const el = document.createElement("div");
        el.className = "node";
        el.style.left = node.x + "px";
        el.style.top = node.y + "px";

        if (wolves.includes(node.id)) {
            el.textContent = "🐺";
        } else if (hare === node.id) {
            el.textContent = "🐰";
        }

        if (selectedWolf === node.id) {
            el.classList.add("selected");
        }

        if (selectedWolf !== null && currentTurn === 'USER' &&
            getWolfMoves(selectedWolf).includes(node.id)) {
            el.classList.add("possible");
        }

        el.onclick = () => clickNode(node.id);
        boardEl.appendChild(el);
    });
}

function clickNode(id) {
    if (gameOver || currentTurn !== 'USER') return;

    if (wolves.includes(id)) {
        selectedWolf = id;
        render();
        return;
    }
    if (selectedWolf !== null && getWolfMoves(selectedWolf).includes(id)) {
        const idx = wolves.indexOf(selectedWolf);
        wolves[idx] = id;
        selectedWolf = null;

        if (checkWin()) return;

        currentTurn = 'AI';
        document.getElementById("status").textContent = "ბაჭია ფიქრობს... 🐰";
        render();
        saveState();
        setTimeout(aiHareMove, 400);
    }
}

function aiHareMove() {
    if (gameOver) return;
    const moves = getHareMoves();

    if (moves.length === 0) {
        finish("🎉 მგლებმა ბაჭია ალყაში მოაქციეს! მოიგე! 🐺", true);
        return;
    }

    moves.sort((a, b) => nodes[b].y - nodes[a].y);
    hare = moves[0];

    if (checkWin()) return;

    currentTurn = 'USER';
    document.getElementById("status").textContent = "შენი სვლაა! აირჩიე მგელი 🐺";
    render();
    saveState();
}

function checkWin() {
    const maxWolfY = Math.max(...wolves.map(id => nodes[id].y));

    if (nodes[hare].y > maxWolfY) {
        finish("🐰 ბაჭიამ გაასწრო ყველა მგელს! ბაჭიამ მოიგო!", false);
        return true;
    }
    if (getHareMoves().length === 0) {
        finish("🎉 მგლებმა ბაჭია ალყაში მოაქციეს! მოიგე! 🐺", true);
        return true;
    }
    return false;
}

function finish(msg, userWon) {
    gameOver = true;
    document.getElementById("status").textContent = msg;
    selectedWolf = null;
    render();
    reportFinish(userWon);
}

// ── Start: resume the saved match if there is one, otherwise start fresh ──
scaleBoard();
const savedState = @json($session->state);

if (savedState && !savedState.gameOver) {
    wolves      = savedState.wolves;
    hare        = savedState.hare;
    currentTurn = savedState.currentTurn;
    gameOver    = false;

    drawLines();
    render();

    if (currentTurn === 'USER') {
        document.getElementById("status").textContent = "შენი სვლაა! აირჩიე მგელი 🐺";
    } else {
        document.getElementById("status").textContent = "ბაჭია ფიქრობს... 🐰";
        setTimeout(aiHareMove, 500);
    }
} else {
    newGame();
}

window.newGame = newGame;

})();
</script>
@endsection
