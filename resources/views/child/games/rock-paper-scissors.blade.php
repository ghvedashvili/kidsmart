@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Acme&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
@keyframes changeOrder {
  from { z-index: 9; }
  to { z-index: 1; }
}
@keyframes handShake {
  0%, 100% { transform: rotate(10deg); }
  50% { transform: rotate(-10deg); }
}
@keyframes handShake2 {
  0%, 100% { transform: rotateY(180deg) rotate(10deg); }
  50% { transform: rotateY(180deg) rotate(-10deg); }
}

body { background: transparent !important; font-family: Acme, Arial, sans-serif; }
.wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
@media (min-width: 760px)  { .wrap { max-width: 700px; } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } }

.back-btn { display:inline-flex; align-items:center; gap:6px; margin:0 0 20px; font-family:'Nunito',sans-serif; font-size:0.82rem; font-weight:800; color:#16a34a; text-decoration:none; background:white; border-radius:99px; padding:8px 16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }

form {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  width: 100%;
}

h1 {
  text-align: center;
  margin-bottom: 10px;
  font-size: 1.5rem;
}

#hands {
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
}

input:checked ~ div .hand {
  animation: none !important;
}

.hand {
  margin: 10px;
  width: 120px;
  height: 120px;
  position: relative;
  transform: rotate(10deg);
  display: inline-block;
  animation: handShake 2s infinite;
}

.hand > div {
  position: absolute;
  box-sizing: border-box;
  border: 2px solid black;
  background: gold;
  transition: all 0.1s;
}

.fist {
  height: 66px;
  left: 24px;
  top: 30px;
  width: 54px;
  border-radius: 12px 0 0 12px;
}

.finger {
  width: 42px;
  height: 18px;
  border-radius: 12px;
  left: 48px;
  transform-origin: 0 50%;
}

.finger-1 { top: 30px; --dif: 0px; }
.finger-2 { top: 47px; left: 50px; --dif: 2px; }
.finger-3 { top: 64px; --dif: 0px; }
.finger-4 { top: 80px; height: 16px; left: 46px; --dif: -5px; }

div.thumb {
  width: 21px;
  height: 42px;
  border-radius: 0 12px 12px 12px;
  top: 30px;
  left: 48px;
  border-left: 0 solid;
  box-shadow: -10px 4px 0 -9px black;
}

div.arm {
  width: 13px;
  height: 42px;
  left: 12px;
  top: 42px;
  border: 0;
  border-top: 2px solid black;
  border-bottom: 2px solid black;
}

#computer-hand {
  transform: rotateY(180deg);
  animation: handShake2 2s infinite;
  position: relative;
}

input[type="radio"] {
  position: absolute;
  top: -1000in;
  left: -1000in;
}

input[id$="scissors"]:checked ~ div #user-hand .finger-1,
input[id^="scissors"]:checked ~ div #computer-hand .finger-1 {
  width: 78px;
  transform: rotate(5deg);
}
input[id$="scissors"]:checked ~ div #user-hand .finger-2,
input[id^="scissors"]:checked ~ div #computer-hand .finger-2 {
  width: 78px;
  transform: rotate(-5deg);
}
input[id$="paper"]:checked ~ div #user-hand .finger-1,
input[id$="paper"]:checked ~ div #user-hand .finger-2,
input[id$="paper"]:checked ~ div #user-hand .finger-3,
input[id$="paper"]:checked ~ div #user-hand .finger-4,
input[id^="paper"]:checked ~ div #computer-hand .finger-1,
input[id^="paper"]:checked ~ div #computer-hand .finger-2,
input[id^="paper"]:checked ~ div #computer-hand .finger-3,
input[id^="paper"]:checked ~ div #computer-hand .finger-4 {
  left: 74px;
  left: calc(74px + var(--dif));
  width: 48px;
  border-left: 0;
  border-radius: 0 12px 12px 0;
}

#rock-rock:checked ~ div h2::before,
#paper-paper:checked ~ div h2::before,
#scissors-scissors:checked ~ div h2::before { content: "ფრე!"; }
#rock-paper:checked ~ div h2::before,
#paper-scissors:checked ~ div h2::before,
#scissors-rock:checked ~ div h2::before { content: "შენ მოიგე!"; }
#rock-scissors:checked ~ div h2::before,
#paper-rock:checked ~ div h2::before,
#scissors-paper:checked ~ div h2::before { content: "Kidsmart-მა მოიგო!"; }

#message { text-align: center; font-size: 1.2rem; margin: 10px 0; }
#score-display { text-align: center; font-size: 1rem; font-weight: bold; margin: 0 15px; }

#icons-container { display: flex; justify-content: center; width: 100%; max-width: 400px; margin: 0 auto; }
#icons { width: 100%; max-width: 300px; height: auto; display: flex; justify-content: center; }
#icons > div {
  flex: 1;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  overflow: hidden;
  position: relative;
  margin: 5px;
  padding: 10px;
  text-align: center;
  touch-action: manipulation;
}

label:active { position: static; margin-left: 60px; }
label:active::before {
  content: "";
  position: absolute;
  top: 0; left: 0;
  width: 60px; z-index: 10; height: 60px;
}
label {
  animation: changeOrder 0.45s infinite linear;
  background: #f5f5f5;
  border: 1px solid #ccc;
  box-sizing: border-box;
  cursor: pointer;
  display: block;
  height: 60px;
  width: 60px;
  line-height: 60px;
  font-size: 2rem;
  position: absolute;
  top: 0; left: 0;
  user-select: none;
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
label:nth-of-type(1) { animation-delay: -0s; }
label:nth-of-type(2) { animation-delay: -0.15s; }
label:nth-of-type(3) { animation-delay: -0.3s; }

#game-over { text-align: center; display: none; margin-top: 10px; }
#game-over h2 { color: #d35400; font-size: 1.3rem; }

button {
  background-color: #16a34a;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  font-family: 'Acme', sans-serif;
  font-size: 0.9em;
  transition: background-color 0.3s;
}
button:hover { background-color: #15803d; }

#overall-stats {
  margin-top: 10px;
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 5px;
  text-align: center;
  font-size: 0.9rem;
}
#overall-stats h3 { margin-top: 0; color: #2c3e50; font-size: 1.1rem; }

.disabled { pointer-events: none; opacity: 0.6; }

@media (min-width: 768px) {
  h1 { font-size: 2rem; }
  .hand { width: 200px; height: 200px; margin: 20px; }
  .fist { height: 110px; left: 40px; top: 50px; width: 90px; border-radius: 20px 0 0 20px; }
  .finger { width: 70px; height: 30px; left: 80px; }
  .finger-1 { top: 50px; --dif: 0px; }
  .finger-2 { top: 78px; left: 84px; --dif: 4px; }
  .finger-3 { top: 106px; --dif: 0px; }
  .finger-4 { top: 134px; height: 26px; left: 76px; --dif: -8px; }
  div.thumb { width: 35px; height: 70px; top: 50px; left: 80px; box-shadow: -17px 6px 0 -15px black; }
  div.arm { width: 22px; height: 70px; left: 20px; top: 70px; }
  input[id$="scissors"]:checked ~ div #user-hand .finger-1,
  input[id^="scissors"]:checked ~ div #computer-hand .finger-1 { width: 130px; }
  input[id$="scissors"]:checked ~ div #user-hand .finger-2,
  input[id^="scissors"]:checked ~ div #computer-hand .finger-2 { width: 130px; }
  input[id$="paper"]:checked ~ div #user-hand .finger-1,
  input[id$="paper"]:checked ~ div #user-hand .finger-2,
  input[id$="paper"]:checked ~ div #user-hand .finger-3,
  input[id$="paper"]:checked ~ div #user-hand .finger-4,
  input[id^="paper"]:checked ~ div #computer-hand .finger-1,
  input[id^="paper"]:checked ~ div #computer-hand .finger-2,
  input[id^="paper"]:checked ~ div #computer-hand .finger-3,
  input[id^="paper"]:checked ~ div #computer-hand .finger-4 {
    left: 124px;
    left: calc(124px + var(--dif));
    width: 80px;
  }
  #message { font-size: 1.5rem; }
  #score-display { font-size: 1.2rem; }
  #game-over h2 { font-size: 1.5rem; }
  button { padding: 10px 15px; font-size: 1em; }
  #overall-stats { font-size: 1rem; padding: 15px; }
  #overall-stats h3 { font-size: 1.2rem; }
  #icons > div { margin: 10px; padding: 40px; }
}

@media (max-width: 767px) {
  #icons > div { width: 80px; height: 80px; }
  label { width: 80px; height: 80px; line-height: 80px; font-size: 2.5rem; }
}
</style>

<div class="wrap">
    <a href="{{ route('games.index') }}" class="back-btn">← თამაშები</a>

    <form onsubmit="return false;">
      <input type="radio" id="rock-rock" name="rock-paper-scissors">
      <input type="radio" id="rock-paper" name="rock-paper-scissors">
      <input type="radio" id="rock-scissors" name="rock-paper-scissors">
      <input type="radio" id="paper-rock" name="rock-paper-scissors">
      <input type="radio" id="paper-paper" name="rock-paper-scissors">
      <input type="radio" id="paper-scissors" name="rock-paper-scissors">
      <input type="radio" id="scissors-rock" name="rock-paper-scissors">
      <input type="radio" id="scissors-paper" name="rock-paper-scissors">
      <input type="radio" id="scissors-scissors" name="rock-paper-scissors">

      <div>
        <h1>✊🖐️✌️ ქვა-ქაღალდი-მაკრატელი</h1>
        <div id="score-display">ანგარიში: შენ {{ $session->player_score }} - {{ $session->computer_score }} Kidsmart</div>

        <div id="hands">
          <div class="hand" id="user-hand">
            <div class="fist"></div>
            <div class="finger finger-1"></div>
            <div class="finger finger-2"></div>
            <div class="finger finger-3"></div>
            <div class="finger finger-4"></div>
            <div class="thumb"></div>
            <div class="arm"></div>
          </div>
          <div class="hand" id="computer-hand">
            <div class="fist"></div>
            <div class="finger finger-1"></div>
            <div class="finger finger-2"></div>
            <div class="finger finger-3"></div>
            <div class="finger finger-4"></div>
            <div class="thumb"></div>
            <div class="arm"></div>
          </div>
        </div>

        <div id="message"><h2></h2></div>

        <div id="icons-container">
          <div id="icons">
            <div>
              <label for="rock-rock">✊</label>
              <label for="paper-rock">✊</label>
              <label for="scissors-rock">✊</label>
            </div>
            <div>
              <label for="rock-paper">🖐️</label>
              <label for="paper-paper">🖐️</label>
              <label for="scissors-paper">🖐️</label>
            </div>
            <div>
              <label for="rock-scissors">✌</label>
              <label for="paper-scissors">✌</label>
              <label for="scissors-scissors">✌</label>
            </div>
          </div>
        </div>
      </div>

      <div id="game-over">
        <h2>მატჩი დასრულდა!</h2>
        <p id="final-result"></p>
        <button type="button" id="play-again-btn">კიდევ ითამაშე</button>
      </div>

      <div id="overall-stats">
        <h3>ანგარიშები</h3>
        <p>შენ vs Kidsmart: <span id="personal-score">{{ $session->wins }} - {{ $session->losses }}</span></p>
        <p>ყველა ბავშვი vs Kidsmart: <span id="overall-score">{{ $global->total_wins }} - {{ $global->total_losses }}</span></p>
      </div>
    </form>
</div>

<script>
(function() {
  const iconsContainer = document.getElementById('icons');
  const labels = document.querySelectorAll('#icons label');
  const scoreDisplay = document.getElementById('score-display');
  const gameOverDiv = document.getElementById('game-over');
  const finalResult = document.getElementById('final-result');
  const playAgainBtn = document.getElementById('play-again-btn');
  const overallScoreDisplay = document.getElementById('overall-score');
  const personalScoreDisplay = document.getElementById('personal-score');

  let roundActive = false;

  function disableChoices() { iconsContainer.classList.add('disabled'); }
  function enableChoices() { iconsContainer.classList.remove('disabled'); }

  function resetSelections() {
    document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
  }

  function determineResult(id) {
    if (id.includes('-rock')) {
      if (id.startsWith('rock')) return 'tie';
      if (id.startsWith('paper')) return 'lose';
      return 'win';
    } else if (id.includes('-paper')) {
      if (id.startsWith('paper')) return 'tie';
      if (id.startsWith('scissors')) return 'lose';
      return 'win';
    } else if (id.includes('-scissors')) {
      if (id.startsWith('scissors')) return 'tie';
      if (id.startsWith('rock')) return 'lose';
      return 'win';
    }
    return 'tie';
  }

  function submitRound(result) {
    fetch("{{ route('games.rps.round') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ result: result }),
    })
      .then(r => r.json())
      .then(data => {
        scoreDisplay.textContent = `ანგარიში: შენ ${data.player_score} - ${data.computer_score} Kidsmart`;
        personalScoreDisplay.textContent = `${data.wins} - ${data.losses}`;
        overallScoreDisplay.textContent = `${data.global.total_wins} - ${data.global.total_losses}`;

        setTimeout(() => {
          resetSelections();
          if (data.match_over) {
            finalResult.textContent = data.won
              ? `შენ მოიგე მატჩი ${data.final_player_score}-${data.final_computer_score}! 🎉`
              : `Kidsmart-მა მოიგო მატჩი ${data.final_computer_score}-${data.final_player_score}!`;
            gameOverDiv.style.display = 'block';
            // choices stay disabled until "play again"
          } else {
            roundActive = false;
            enableChoices();
          }
        }, 2000);
      })
      .catch(() => {
        roundActive = false;
        enableChoices();
      });
  }

  function processChoice(id) {
    if (roundActive) return;
    roundActive = true;
    disableChoices();
    submitRound(determineResult(id));
  }

  function handleChoice(e) {
    e.preventDefault();
    if (roundActive) return;
    const radioId = e.currentTarget.getAttribute('for');
    const radio = document.getElementById(radioId);
    if (radio) {
      radio.checked = true;
      processChoice(radioId);
    }
  }

  labels.forEach(label => {
    label.addEventListener('click', handleChoice);
    label.addEventListener('touchend', handleChoice);
    label.addEventListener('touchstart', (e) => { e.preventDefault(); }, { passive: false });
  });

  playAgainBtn.addEventListener('click', () => {
    gameOverDiv.style.display = 'none';
    roundActive = false;
    enableChoices();
  });
})();
</script>
@endsection
