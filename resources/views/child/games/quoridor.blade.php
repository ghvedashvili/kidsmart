@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
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

.header-row { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-shrink: 0; }
.back-btn { display:inline-flex; align-items:center; gap:6px; flex-shrink:0; font-family:'Nunito',sans-serif; font-size:0.78rem; font-weight:800; color:#2563eb; text-decoration:none; background:white; border-radius:99px; padding:7px 14px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
h1 { margin: 0; font-family:'Fredoka One',cursive; font-size: 1.1rem; color: #1e3a8a; flex: 1; text-align: left; }
#info-btn {
    flex-shrink: 0; width: 30px; height: 30px; border: none; border-radius: 50%;
    background: #e2e8f0; color: #2563eb; font-size: 14px; font-weight: 800; cursor: pointer;
}

.subtitle { text-align: center; color: #64748b; font-family:'Nunito',sans-serif; font-weight:700; font-size: 12px; margin: 2px 0 8px; }

.game-stage { flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 8px; }

.status-box {
    background: #ffffff; border: 2px solid #dbeafe; border-radius: 14px;
    padding: 7px 12px; box-shadow: 0 6px 14px rgba(37,99,235,0.1);
    width: min(380px, 90vw, 55vh);
}
.status {
    font-family:'Nunito',sans-serif; font-size: 13px; font-weight: 800; color: #1d4ed8; text-align: center;
    height: 36px; line-height: 1.25; display: flex; align-items: center; justify-content: center; overflow: hidden;
}

.players-info { display: flex; gap: 8px; width: min(380px, 90vw, 55vh); }
.player-box {
    flex: 1; min-width: 0; background: #f8fafc; border: 2px solid #e2e8f0; padding: 8px 6px;
    border-radius: 12px; transition: all 0.2s ease;
    display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: nowrap;
    white-space: nowrap; overflow: hidden;
}
.player-box.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.player-title { font-family:'Nunito',sans-serif; font-weight: 800; font-size: 13px; flex-shrink: 0; }
.wall-count { font-family:'Nunito',sans-serif; font-weight: 700; font-size: 11px; color: #475569; overflow: hidden; text-overflow: ellipsis; }

#board-container {
    width: min(380px, 90vw, 55vh); aspect-ratio: 1;
    position: relative; background: #cbd5e1; border-radius: 16px; padding: 8px;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
}
#board {
    width: 100%; height: 100%; display: grid;
    grid-template-columns: repeat(5, 1fr); grid-template-rows: repeat(5, 1fr);
    gap: 10px; position: relative;
}
.cell { background: #ffffff; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s ease; user-select: none; }
.cell:hover { background: #f1f5f9; }
.cell.possible { background: #dbeafe; border: 2px dashed #3b82f6; }
.cell.possible:hover { background: #bfdbfe; }

.pawn { width: 65%; height: 65%; border-radius: 50%; box-shadow: 0 3px 5px rgba(0,0,0,0.2); }
.human-pawn { background: #2563eb; border: 2px solid #1d4ed8; }
.ai-pawn { background: #ef4444; border: 2px solid #dc2626; }

#walls-layer { position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; pointer-events: none; }
.wall-slot { position: absolute; transform: translate(-50%, -50%); z-index: 30; cursor: pointer; touch-action: none; }
.wall-elem { position: absolute; border-radius: 4px; z-index: 20; pointer-events: none; }
.wall-placed { background: #78350f; box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.wall-preview.valid { background: rgba(245,158,11,0.75); border: 2px solid #b45309; }
.wall-preview.invalid { background: rgba(239,68,68,0.75); border: 2px solid #b91c1c; }
@keyframes pulse-wall { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
.wall-preview.armed { animation: pulse-wall 0.9s ease-in-out infinite; }

.controls { display: flex; gap: 8px; width: min(380px, 90vw, 55vh); flex-shrink: 0; }
button.action-btn {
    flex: 1; border: none; border-radius: 10px; padding: 10px;
    font-family:'Fredoka One',cursive; font-size: 13px; cursor: pointer; transition: all 0.2s ease;
}
#wall-btn { background: #f59e0b; color: #ffffff; }
#wall-btn.active { background: #b45309; }
button.action-btn:hover { opacity: 0.9; }

.stats-box {
    margin-top: 4px; padding: 7px 12px; background: #ffffff;
    border: 2px solid #dbeafe; border-radius: 12px; text-align: center;
    font-family:'Nunito',sans-serif; font-size: 0.72rem; width: min(380px, 90vw, 55vh); flex-shrink: 0;
}
.stats-box p { margin: 2px 0; color: #475569; font-weight: 700; }
.stats-box span { font-weight: 900; color: #1e3a8a; }

.instructions { font-size: 12px; color: #475569; line-height: 1.5; text-align: left; font-family:'Nunito',sans-serif; }

/* ── modals ── */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(15,23,42,0.55);
    display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 200;
}
.modal-overlay.hidden { display: none; }
.modal-box {
    position: relative; width: 100%; max-width: 320px; max-height: 90dvh; overflow-y: auto;
    background: #ffffff; border-radius: 16px; padding: 22px 18px; text-align: center;
    box-shadow: 0 20px 45px rgba(0,0,0,0.3); font-family:'Nunito',sans-serif;
}
.modal-close {
    position: absolute; top: 10px; right: 10px; width: 26px; height: 26px; border: none;
    border-radius: 50%; background: #f1f5f9; color: #475569; font-size: 14px; cursor: pointer;
}
.win-emoji { font-size: 40px; margin-bottom: 4px; }
.modal-box h2 { margin: 0 0 8px 0; color: #2563eb; font-family:'Fredoka One',cursive; font-size: 18px; }
.modal-box p { color: #64748b; font-size: 13px; margin: 0 0 14px 0; font-weight: 700; }
#win-restart-btn {
    width: 100%; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; padding: 12px;
    border-radius: 10px; border: none; font-family:'Fredoka One',cursive; font-size: 14px; cursor: pointer;
}
</style>

<div class="wrap">
    <div class="header-row">
        <a href="{{ route('games.index') }}" class="back-btn">← თამაშები</a>
        <h1>🧱 ქუორიდორი 5×5</h1>
        <button id="info-btn" aria-label="წესები">ⓘ</button>
    </div>
    <div class="subtitle">მიაღწიე საპირისპირო მხარეს — Kidsmart-ზე ადრე!</div>

    <div class="game-stage">
        <div class="status-box">
            <div class="status" id="status">თამაში იწყება...</div>
        </div>

        <div class="players-info">
            <div class="player-box active" id="p1-box">
                <div class="player-title">🧒 <b>შენ</b></div>
                <div class="wall-count">კედლები: <span id="p1-walls">5</span></div>
            </div>
            <div class="player-box" id="p2-box">
                <div class="player-title">🤖 <b>Kidsmart</b></div>
                <div class="wall-count">კედლები: <span id="p2-walls">5</span></div>
            </div>
        </div>

        <div id="board-container">
            <div id="board"></div>
            <div id="walls-layer"></div>
        </div>

        <div class="controls">
            <button id="wall-btn" class="action-btn">🧱 კედელი</button>
        </div>

        <div class="stats-box">
            <p>შენ vs Kidsmart: <span id="personal-score">{{ $session->wins }} - {{ $session->losses }}</span></p>
            <p>ყველა ბავშვი vs Kidsmart: <span id="overall-score">{{ $global->total_wins }} - {{ $global->total_losses }}</span></p>
        </div>
    </div>
</div>

<div id="info-modal" class="modal-overlay hidden">
    <div class="modal-box">
        <button class="modal-close" id="info-close-btn" aria-label="დახურვა">✕</button>
        <h2>📖 წესები</h2>
        <div class="instructions">
            • <b>მიზანი:</b> პირველი, ვინც საპირისპირო მხარეს მიაღწევს, იმარჯვებს.<br><br>
            • <b>სვლა:</b> დააჭირე ლურჯად მონიშნულ უჯრას.<br><br>
            • <b>კედელი (მაუსი):</b> დააჭირე „კედელი"-ს, გადაატარე კურსორი — მიმართულება ავტომატურად შეირჩევა, დააჭირე დასადებად.<br><br>
            • <b>კედელი (მობილური):</b> დააჭირე „კედელი"-ს, შემდეგ შეეხე სასურველ ადგილს — ჯერ მხოლოდ ციმციმებს, იმავე ადგილას მეორე შეხებით დაისმევა.
        </div>
    </div>
</div>

<div id="win-modal" class="modal-overlay hidden">
    <div class="modal-box">
        <button class="modal-close" id="win-close-btn" aria-label="დახურვა">✕</button>
        <div class="win-emoji" id="win-emoji">🎉</div>
        <h2 id="win-title">შენ გაიმარჯვე!</h2>
        <p id="win-msg">საოცარი თამაში იყო!</p>
        <button id="win-restart-btn">🔄 თავიდან თამაში</button>
    </div>
</div>

<script>
(function() {

const SIZE = 5;

let human = { r: 4, c: 2 };
let ai = { r: 0, c: 2 };
let humanWalls = 5;
let aiWalls = 5;
let walls = [];
let turn = "human";
let isWallMode = false;
let wallDir = "h";
let gameOver = false;

let isTouchDevice = false;
let armedWall = null;
window.addEventListener('touchstart', () => { isTouchDevice = true; }, { once: true, passive: true });

const board = document.getElementById("board");
const wallsLayer = document.getElementById("walls-layer");
const wallBtn = document.getElementById("wall-btn");
const statusEl = document.getElementById("status");
const personalScoreDisplay = document.getElementById('personal-score');
const overallScoreDisplay  = document.getElementById('overall-score');

const infoBtn = document.getElementById("info-btn");
const infoModal = document.getElementById("info-modal");
const infoCloseBtn = document.getElementById("info-close-btn");

const winModal = document.getElementById("win-modal");
const winCloseBtn = document.getElementById("win-close-btn");
const winRestartBtn = document.getElementById("win-restart-btn");
const winEmoji = document.getElementById("win-emoji");
const winTitle = document.getElementById("win-title");
const winMsg = document.getElementById("win-msg");

wallBtn.addEventListener("click", toggleWallMode);

infoBtn.addEventListener("click", () => infoModal.classList.remove("hidden"));
infoCloseBtn.addEventListener("click", () => infoModal.classList.add("hidden"));
infoModal.addEventListener("click", (e) => { if (e.target === infoModal) infoModal.classList.add("hidden"); });

winCloseBtn.addEventListener("click", hideWinModal);
winRestartBtn.addEventListener("click", () => initGame());

function csrfHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    };
}

function saveState() {
    fetch("{{ route('games.quoridor.state') }}", {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ state: { human, ai, humanWalls, aiWalls, walls, turn, gameOver } }),
    }).catch(() => {});
}

function reportFinish(humanWon) {
    fetch("{{ route('games.quoridor.finish') }}", {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ result: humanWon ? 'win' : 'lose' }),
    })
        .then(r => r.json())
        .then(data => {
            personalScoreDisplay.textContent = `${data.wins} - ${data.losses}`;
            overallScoreDisplay.textContent = `${data.global.total_wins} - ${data.global.total_losses}`;
        })
        .catch(() => {});
}

function hideWinModal() {
    winModal.classList.add("hidden");
}

function showWinModal(humanWon) {
    winEmoji.textContent = humanWon ? "🎉" : "🤖";
    winTitle.textContent = humanWon ? "შენ გაიმარჯვე!" : "Kidsmart-მა გაიმარჯვა!";
    winMsg.textContent = humanWon ? "საოცარი სვლები იყო!" : "სცადე ისევ — გაიმარჯვებ!";
    updateStatus(humanWon ? "🎉 გილოცავ! შენ გაიმარჯვე!" : "🤖 Kidsmart-მა გაიმარჯვა!");
    winModal.classList.remove("hidden");
    reportFinish(humanWon);
}

function initGame() {
    human = { r: 4, c: 2 };
    ai = { r: 0, c: 2 };
    humanWalls = 5;
    aiWalls = 5;
    walls = [];
    isWallMode = false;
    wallDir = "h";
    gameOver = false;
    armedWall = null;

    wallBtn.classList.remove("active");
    hideWinModal();

    turn = Math.random() < 0.5 ? "human" : "ai";
    saveState();
    beginTurn();
}

function toggleWallMode() {
    if (gameOver || turn !== "human") return;
    if (humanWalls <= 0 && !isWallMode) {
        updateStatus("❌ კედლები აღარ გრჩება!");
        return;
    }
    isWallMode = !isWallMode;
    armedWall = null;
    wallBtn.classList.toggle("active", isWallMode);
    clearPreviews();
    if (isWallMode) {
        updateStatus(isTouchDevice ? "🧱 შეეხე სასურველ ადგილს" : "🧱 გადაატარე კურსორი და დააჭირე");
    } else {
        updateStatus("🔵 შენი სვლაა");
    }
    render();
}

function beginTurn() {
    render();
    if (turn === "ai") {
        updateStatus("🤖 Kidsmart ფიქრობს...");
        setTimeout(aiTurn, 600);
    } else {
        updateStatus("🔵 შენი სვლაა");
    }
}

function updateStatus(text) {
    statusEl.textContent = text;
}

/* ================= RENDER ENGINE ================= */

function render() {
    board.innerHTML = "";
    wallsLayer.innerHTML = "";

    for (let r = 0; r < SIZE; r++) {
        for (let c = 0; c < SIZE; c++) {
            const cell = document.createElement("div");
            cell.className = "cell";

            if (turn === "human" && !isWallMode && !gameOver) {
                if (getValidMoves(human, ai).some(m => m.r === r && m.c === c)) {
                    cell.classList.add("possible");
                }
            }

            if (human.r === r && human.c === c) {
                const pawn = document.createElement("div");
                pawn.className = "pawn human-pawn";
                cell.appendChild(pawn);
            }
            if (ai.r === r && ai.c === c) {
                const pawn = document.createElement("div");
                pawn.className = "pawn ai-pawn";
                cell.appendChild(pawn);
            }

            cell.addEventListener("click", () => handleCellClick(r, c));
            board.appendChild(cell);
        }
    }

    for (let r = 0; r < SIZE - 1; r++) {
        for (let c = 0; c < SIZE - 1; c++) {
            const slot = document.createElement("div");
            slot.className = "wall-slot";

            const pos = getIntersectionPosition(r, c);
            const zoneSize = pos.cellWidth + pos.gap;
            slot.style.left = pos.x + "px";
            slot.style.top = pos.y + "px";
            slot.style.width = zoneSize + "px";
            slot.style.height = zoneSize + "px";
            slot.style.pointerEvents = isWallMode ? "auto" : "none";

            slot.addEventListener("mousemove", (e) => handleWallSlotHover(e, r, c));
            slot.addEventListener("mouseleave", clearPreviews);
            slot.addEventListener("click", (e) => handleWallSlotClick(e, r, c));

            slot.addEventListener("touchstart", (e) => handleWallSlotHover(e, r, c), { passive: true });
            slot.addEventListener("touchmove", (e) => { e.preventDefault(); handleWallSlotHover(e, r, c); }, { passive: false });

            wallsLayer.appendChild(slot);
        }
    }

    walls.forEach(w => {
        const wallEl = createWallElement(w.r, w.c, w.dir, "wall-placed");
        wallsLayer.appendChild(wallEl);
    });

    document.getElementById("p1-walls").textContent = humanWalls;
    document.getElementById("p2-walls").textContent = aiWalls;
    document.getElementById("p1-box").classList.toggle("active", turn === "human");
    document.getElementById("p2-box").classList.toggle("active", turn === "ai");
}

/* ================= EXACT CALCULATIONS FOR WALL POSITIONING ================= */

function getIntersectionPosition(r, c) {
    const boardRect = board.getBoundingClientRect();
    const gap = 10;
    const cellWidth = (boardRect.width - 4 * gap) / 5;

    const x = (c + 1) * cellWidth + c * gap + gap / 2;
    const y = (r + 1) * cellWidth + r * gap + gap / 2;

    return { x, y, cellWidth, gap };
}

function createWallElement(r, c, dir, extraClass) {
    const pos = getIntersectionPosition(r, c);
    const wallEl = document.createElement("div");
    wallEl.className = `wall-elem ${extraClass}`;

    const wallLength = 2 * pos.cellWidth + pos.gap;
    const wallThickness = pos.gap;

    if (dir === 'h') {
        wallEl.style.width = wallLength + "px";
        wallEl.style.height = wallThickness + "px";
        wallEl.style.left = (pos.x - wallLength / 2) + "px";
        wallEl.style.top = (pos.y - wallThickness / 2) + "px";
    } else {
        wallEl.style.width = wallThickness + "px";
        wallEl.style.height = wallLength + "px";
        wallEl.style.left = (pos.x - wallThickness / 2) + "px";
        wallEl.style.top = (pos.y - wallLength / 2) + "px";
    }

    return wallEl;
}

/* ================= HOVER & PLACEMENT LOGIC ================= */

function computeDirFromEvent(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    const clientX = (e.touches && e.touches.length) ? e.touches[0].clientX : e.clientX;
    const clientY = (e.touches && e.touches.length) ? e.touches[0].clientY : e.clientY;
    const dx = clientX - (rect.left + rect.width / 2);
    const dy = clientY - (rect.top + rect.height / 2);
    return Math.abs(dx) >= Math.abs(dy) ? 'h' : 'v';
}

function handleWallSlotHover(e, r, c) {
    if (!isWallMode || turn !== "human" || gameOver) return;
    wallDir = computeDirFromEvent(e);
    clearPreviews();

    const isValid = canPlaceWall(r, c, wallDir);
    const preview = createWallElement(r, c, wallDir, `wall-preview ${isValid ? 'valid' : 'invalid'}`);
    preview.id = "active-preview";
    wallsLayer.appendChild(preview);
}

function clearPreviews() {
    const prev = document.getElementById("active-preview");
    if (prev) prev.remove();
}

function handleWallSlotClick(e, r, c) {
    if (!isWallMode || turn !== "human" || gameOver) return;

    const dir = computeDirFromEvent(e);

    if (isTouchDevice) {
        if (armedWall && armedWall.r === r && armedWall.c === c && armedWall.dir === dir) {
            armedWall = null;
            if (canPlaceWall(r, c, dir)) {
                walls.push({ r, c, dir });
                humanWalls--;
                isWallMode = false;
                wallBtn.classList.remove("active");
                clearPreviews();
                endTurn();
            } else {
                clearPreviews();
                updateStatus("❌ აქ კედელს ვერ დადებ!");
            }
            return;
        }

        armedWall = { r, c, dir };
        clearPreviews();
        const isValid = canPlaceWall(r, c, dir);
        const preview = createWallElement(r, c, dir, `wall-preview armed ${isValid ? 'valid' : 'invalid'}`);
        preview.id = "active-preview";
        wallsLayer.appendChild(preview);
        updateStatus(isValid
            ? "👉 შეეხე იმავე ადგილას კიდევ ერთხელ დასადასტურებლად"
            : "❌ აქ კედელს ვერ დადებ — სცადე სხვაგან");
        return;
    }

    if (canPlaceWall(r, c, dir)) {
        walls.push({ r, c, dir });
        humanWalls--;
        isWallMode = false;
        wallBtn.classList.remove("active");
        clearPreviews();
        endTurn();
    } else {
        updateStatus("❌ აქ კედელს ვერ დადებ!");
    }
}

function handleCellClick(r, c) {
    if (isWallMode || turn !== "human" || gameOver) return;

    const validMoves = getValidMoves(human, ai);
    if (validMoves.some(m => m.r === r && m.c === c)) {
        human.r = r;
        human.c = c;
        endTurn();
    }
}

function endTurn() {
    if (human.r === 0) {
        gameOver = true;
        render();
        showWinModal(true);
        return;
    }
    if (ai.r === SIZE - 1) {
        gameOver = true;
        render();
        showWinModal(false);
        return;
    }

    turn = turn === "human" ? "ai" : "human";
    saveState();
    beginTurn();
}

/* ================= RULES & MOVEMENT ================= */

function getValidMoves(pawn, opponent) {
    const moves = [];
    const dirs = [[-1,0], [1,0], [0,-1], [0,1]];

    dirs.forEach(([dr, dc]) => {
        const nr = pawn.r + dr;
        const nc = pawn.c + dc;

        if (nr >= 0 && nr < SIZE && nc >= 0 && nc < SIZE) {
            if (!isWallBetween(pawn.r, pawn.c, nr, nc)) {
                if (nr === opponent.r && nc === opponent.c) {
                    const jumpR = nr + dr;
                    const jumpC = nc + dc;
                    if (jumpR >= 0 && jumpR < SIZE && jumpC >= 0 && jumpC < SIZE) {
                        if (!isWallBetween(nr, nc, jumpR, jumpC)) {
                            moves.push({ r: jumpR, c: jumpC });
                        }
                    }
                } else {
                    moves.push({ r: nr, c: nc });
                }
            }
        }
    });

    return moves;
}

function isWallBetween(r1, c1, r2, c2) {
    for (let w of walls) {
        if (w.dir === 'h') {
            if (Math.abs(r1 - r2) === 1 && c1 === c2) {
                const minR = Math.min(r1, r2);
                if (w.r === minR && (w.c === c1 || w.c === c1 - 1)) return true;
            }
        } else if (w.dir === 'v') {
            if (Math.abs(c1 - c2) === 1 && r1 === r2) {
                const minC = Math.min(c1, c2);
                if (w.c === minC && (w.r === r1 || w.r === r1 - 1)) return true;
            }
        }
    }
    return false;
}

function canPlaceWall(r, c, dir) {
    for (let w of walls) {
        if (w.r === r && w.c === c) return false;
        if (w.dir === dir) {
            if (dir === 'h' && w.r === r && Math.abs(w.c - c) < 2) return false;
            if (dir === 'v' && w.c === c && Math.abs(w.r - r) < 2) return false;
        }
    }

    walls.push({ r, c, dir });
    const humanCanReach = hasPath(human, 0);
    const aiCanReach = hasPath(ai, SIZE - 1);
    walls.pop();

    return humanCanReach && aiCanReach;
}

function hasPath(pawn, targetRow) {
    const queue = [{ r: pawn.r, c: pawn.c }];
    const visited = new Set([`${pawn.r},${pawn.c}`]);

    while (queue.length > 0) {
        const curr = queue.shift();
        if (curr.r === targetRow) return true;

        const dirs = [[-1,0], [1,0], [0,-1], [0,1]];
        for (let [dr, dc] of dirs) {
            const nr = curr.r + dr;
            const nc = curr.c + dc;

            if (nr >= 0 && nr < SIZE && nc >= 0 && nc < SIZE) {
                if (!isWallBetween(curr.r, curr.c, nr, nc)) {
                    const key = `${nr},${nc}`;
                    if (!visited.has(key)) {
                        visited.add(key);
                        queue.push({ r: nr, c: nc });
                    }
                }
            }
        }
    }
    return false;
}

function getShortestPathLength(pawn, targetRow) {
    const queue = [{ r: pawn.r, c: pawn.c, dist: 0 }];
    const visited = new Set([`${pawn.r},${pawn.c}`]);

    while (queue.length > 0) {
        const curr = queue.shift();
        if (curr.r === targetRow) return curr.dist;

        const dirs = [[-1,0], [1,0], [0,-1], [0,1]];
        for (let [dr, dc] of dirs) {
            const nr = curr.r + dr;
            const nc = curr.c + dc;

            if (nr >= 0 && nr < SIZE && nc >= 0 && nc < SIZE) {
                if (!isWallBetween(curr.r, curr.c, nr, nc)) {
                    const key = `${nr},${nc}`;
                    if (!visited.has(key)) {
                        visited.add(key);
                        queue.push({ r: nr, c: nc, dist: curr.dist + 1 });
                    }
                }
            }
        }
    }
    return Infinity;
}

/* ================= AI LOGIC ================= */

function aiTurn() {
    if (gameOver) return;

    let bestAction = null;
    const humanDist = getShortestPathLength(human, 0);
    const aiDist = getShortestPathLength(ai, SIZE - 1);

    if (aiWalls > 0 && (humanDist <= aiDist || Math.random() < 0.3)) {
        let maxDelay = 0;
        let bestWall = null;

        for (let r = 0; r < SIZE - 1; r++) {
            for (let c = 0; c < SIZE - 1; c++) {
                for (let dir of ['h', 'v']) {
                    if (canPlaceWall(r, c, dir)) {
                        walls.push({ r, c, dir });
                        const newHumanDist = getShortestPathLength(human, 0);
                        const newAiDist = getShortestPathLength(ai, SIZE - 1);
                        walls.pop();

                        const delay = (newHumanDist - humanDist) - (newAiDist - aiDist);
                        if (delay > maxDelay) {
                            maxDelay = delay;
                            bestWall = { r, c, dir };
                        }
                    }
                }
            }
        }

        if (bestWall && maxDelay > 0) {
            bestAction = { type: 'wall', ...bestWall };
        }
    }

    if (!bestAction) {
        const validMoves = getValidMoves(ai, human);
        let minBonusDist = Infinity;
        let bestMove = null;

        validMoves.forEach(m => {
            const dist = getShortestPathLength(m, SIZE - 1);
            if (dist < minBonusDist) {
                minBonusDist = dist;
                bestMove = m;
            }
        });

        if (bestMove) {
            bestAction = { type: 'move', target: bestMove };
        }
    }

    if (bestAction && bestAction.type === 'wall') {
        walls.push({ r: bestAction.r, c: bestAction.c, dir: bestAction.dir });
        aiWalls--;
    } else if (bestAction && bestAction.type === 'move') {
        ai.r = bestAction.target.r;
        ai.c = bestAction.target.c;
    }

    endTurn();
}

window.addEventListener('resize', render);

// ── Start: resume the saved match if there is one, otherwise start fresh ──
const savedState = @json($session->state);

if (savedState && !savedState.gameOver) {
    human      = savedState.human;
    ai         = savedState.ai;
    humanWalls = savedState.humanWalls;
    aiWalls    = savedState.aiWalls;
    walls      = savedState.walls;
    turn       = savedState.turn;
    gameOver   = false;

    beginTurn();
} else {
    initGame();
}

})();
</script>
@endsection
