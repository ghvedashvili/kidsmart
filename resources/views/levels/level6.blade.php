@if($userLevel == $level)

<style>
  body { background: #F1CC38; }

  #level6-wrapper {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    padding-top: var(--nav-h, 56px);
    transition: padding-top 0.3s ease;
    background: #F1CC38;
    background-image: radial-gradient(circle, rgba(255,255,255,0.3) 1px, transparent 1px);
    background-size: 24px 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    z-index: 0;
  }

  .folder-scene {
    perspective: 800px;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    transform-origin: center center;
  }

  @media (max-width: 480px) { .folder-scene { transform: scale(0.78); } }
  @media (max-width: 360px) { .folder-scene { transform: scale(0.62); } }

  .folder {
    width: 340px;
    height: 140px;
    background: tomato;
    position: relative;
    border-top-right-radius: 5px;
    cursor: pointer;
    overflow: visible;
  }

  .folder::before {
    width: 80px;
    height: 20px;
    content: '';
    background: tomato;
    position: absolute;
    top: -20px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
  }

  .folder::after {
    width: 340px;
    height: 210px;
    position: absolute;
    content: '';
    background: #cc3a2a;
    top: 40px;
    box-shadow: 0 0 20px 2px rgba(0,0,0,0.3);
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    transform: rotateX(-15deg);
    z-index: 2;
  }

  .folder-paper {
    width: 310px;
    height: 200px;
    position: absolute;
    background: #fff;
    top: -22px;
    left: 15px;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.12);
    transform: rotate(-4deg);
    border: 1px solid #ddd;
    overflow: hidden;
    cursor: pointer;
    z-index: 1;
    transition: top 250ms ease, box-shadow 250ms ease;
  }

  .folder-paper:hover {
    top: -32px;
    box-shadow: 0 -6px 18px rgba(0,0,0,0.18);
  }

  .folder-paper::before {
    content: '';
    background: repeating-linear-gradient(
      180deg,
      #fff, #fff 24px,
      #e0e0ff 24px, #e0e0ff 25px
    );
    position: absolute;
    top: 30px;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.8;
  }

  /* Overlay */
  .folder-overlay {
    display: none;
    position: fixed;
    inset: 0;
    padding-top: var(--nav-h, 56px);
    background: rgba(0,0,0,0.45);
    z-index: 1050;
    align-items: center;
    justify-content: center;
  }

  .folder-overlay.active {
    display: flex;
    animation: fo-fadeIn 200ms ease;
  }

  @keyframes fo-fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
  }

  .paper-expanded {
    width: min(360px, 92vw);
    max-height: calc(100svh - var(--nav-h, 56px) - 32px);
    overflow-y: auto;
    background: #fffef8;
    border: 1px solid #ddd;
    border-radius: 3px;
    box-shadow: 4px 8px 40px rgba(0,0,0,0.3);
    padding: 40px 28px 28px;
    position: relative;
    transform: rotate(-1deg);
    animation: fo-unfold 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: default;
  }

  @keyframes fo-unfold {
    from {
      transform: rotate(-7deg) scaleY(0.3) translateY(80px);
      opacity: 0;
    }
    to {
      transform: rotate(-1deg) scaleY(1) translateY(0);
      opacity: 1;
    }
  }

  /* ყდის წითელი ზოლი */
  .paper-expanded::before {
    /* content: '';
    position: absolute;
    left: 55px;
    top: 0; bottom: 0;
    width: 1px;
    background: rgba(255,100,100,0.4); */
  }

  /* ხვრელი */
  .paper-expanded::after {
    content: '';
    position: absolute;
    top: 24px; left: 22px;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: rgba(0,0,0,0.08);
    border: 1px solid #ccc;
  }

  /* notebook alerts */
  .nb-alert {
    border: 1px solid #ddd;
    color: #222;
    border-radius: 4px;
    padding: 14px 16px;
    margin-bottom: 14px;
    font-family: 'BPG Chveulebrivy', cursive;
    font-size: 18px;
    line-height: 26px;
  }

  .notebook-grid {
    background-color: #fffef8;
    background-image:
      repeating-linear-gradient(0deg, #dcdcdc 0px, #dcdcdc 1px, transparent 1px, transparent 25px),
      repeating-linear-gradient(90deg, #dcdcdc 0px, #dcdcdc 1px, transparent 1px, transparent 25px);
  }

  .notebook-lines {
    background-color: #fffef8;
    background-image:
      repeating-linear-gradient(to bottom, transparent 0px, transparent 24px, #cfd8ff 25px);
  }

  .notebook-paper {
    background-color: #fdf6e3;
    background-image:
      repeating-linear-gradient(
        to bottom,
        rgba(0,0,0,0.05) 0px, rgba(0,0,0,0.05) 1px,
        transparent 1px, transparent 28px
      );
    box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
  }

  /* Answer form inside paper */
  .paper-answer-form {
    display: flex;
    gap: 8px;
    margin-top: 6px;
  }

  .paper-answer-form input {
    flex: 1;
    border: none;
    border-bottom: 2px solid #aaa;
    background: transparent;
    font-family: 'BPG Chveulebrivy', cursive;
    font-size: 18px;
    padding: 4px 2px;
    outline: none;
    color: #222;
  }

  .paper-answer-form input:focus {
    border-bottom-color: tomato;
  }

  .paper-answer-form button {
    background: tomato;
    border: none;
    color: #fff;
    border-radius: 4px;
    padding: 6px 14px;
    font-family: 'BPG Chveulebrivy', cursive;
    font-size: 16px;
    cursor: pointer;
    transition: background 200ms;
    -webkit-tap-highlight-color: transparent;
  }

  .paper-answer-form button:hover {
    background: #cc3a2a;
  }

  #fo-result-box {
    margin-top: 10px;
    font-family: 'BPG Chveulebrivy', cursive;
    font-size: 17px;
    min-height: 24px;
    text-align: center;
  }

  .fo-close-btn {
    position: absolute;
    top: 8px; right: 8px;
    background: rgba(0,0,0,0.06);
    border: none;
    border-radius: 50%;
    width: 36px; height: 36px;
    font-size: 18px;
    cursor: pointer;
    color: #888;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: background 200ms, color 200ms;
    -webkit-tap-highlight-color: transparent;
  }

  .fo-close-btn:hover, .fo-close-btn:active {
    background: tomato;
    color: #fff;
  }

  #fo-answer-box {
    display: none;
  }

  @media print {
    html, body {
      background: #fff !important;
      height: auto !important;
      overflow: visible !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    body * {
      visibility: hidden !important;
      background: transparent !important;
    }

    #fo-answer-box {
      visibility: visible !important;
      display: flex !important;
      position: fixed !important;
      inset: 0 !important;
      align-items: center !important;
      justify-content: center !important;
      background: #fff !important;
      font-size: 28px !important;
      font-weight: 700 !important;
      color: #000 !important;
      z-index: 99999 !important;
      width: 100vw !important;
      height: 100vh !important;
    }
  }
</style>

{{-- პასუხი encoded სახით, უჩინარია --}}
<div id="fo-answer-box" data-a="{{ $encodedAnswer }}" style="display:none;"></div>

<div id="level6-wrapper">
  {{-- საქაღალდის სცენა --}}
  <div class="folder-scene">
    <div class="folder">
      <div class="folder-paper" id="fo-paper"></div>
    </div>
  </div>
</div>

{{-- გაშლილი ფურცელი --}}
<div class="folder-overlay" id="fo-overlay">
  <div class="paper-expanded">
    <button class="fo-close-btn" id="fo-closeBtn">✕</button>

    <div class="nb-alert notebook-grid" style="text-align:center;">
      ციფრულ სამყარო არც ისე სანდოა.
    </div>
    <div class="nb-alert notebook-lines" style="text-align:center;">
      რაც ეკრანზე ჩანს, ყოველთვის სიმართლე არ არის
    </div>
    <div class="nb-alert notebook-paper" style="text-align:center;"  >
      ძველი მეთოდები ბევრად საიმედოა
    </div>

    <form id="answerForm" >
      @csrf
        <input type="text" class="form-control mb-2" id="answer">
        <button class="btn btn-primary">Submit</button>
    </form>

    <div id="fo-result-box"></div>
  </div>
</div>

<script>
  // ——— Folder open/close ———
  const foPaper   = document.getElementById('fo-paper');
  const foOverlay = document.getElementById('fo-overlay');
  const foClose   = document.getElementById('fo-closeBtn');

  function foOpen(e)  { e.stopPropagation(); foOverlay.classList.add('active'); }
  function foCloseF(e){ e.stopPropagation(); foOverlay.classList.remove('active'); }

  foPaper.addEventListener('click',    foOpen);
  foPaper.addEventListener('touchend', foOpen, { passive: false });

  foClose.addEventListener('click',    foCloseF);
  foClose.addEventListener('touchend', foCloseF, { passive: false });

  foOverlay.addEventListener('click', (e) => {
    if (e.target === foOverlay) foOverlay.classList.remove('active');
  });
  foOverlay.addEventListener('touchend', (e) => {
    if (e.target === foOverlay) foOverlay.classList.remove('active');
  }, { passive: false });

  // ——— Print logic: decode on load, CSS handles visibility ———
  (function() {
    const box = document.getElementById('fo-answer-box');
    if (box && box.dataset.a) box.textContent = '✅ ' + atob(box.dataset.a);
  })();

  // ——— Answer form submit ———
//   document.getElementById('fo-answerForm').addEventListener('submit', async function(e) {
//     e.preventDefault();

//     const answer    = document.getElementById('fo-answer').value.trim();
//     const btn       = this.querySelector('button[type="submit"]');
//     const resultBox = document.getElementById('fo-result-box');

//     if (!answer) return;

//     btn.disabled    = true;
//     btn.textContent = '...';
//     resultBox.textContent = '';

//     try {
//       const res = await fetch('{{ route("levels.check", ["level" => $level]) }}', {
//         method: 'POST',
//         headers: {
//           'Content-Type': 'application/json',
//           'X-CSRF-TOKEN': '{{ csrf_token() }}',
//           'Accept': 'application/json',
//         },
//         body: JSON.stringify({ answer }),
//       });

//       const data = await res.json();

//       if (data.status === 'correct') {
//         resultBox.style.color = 'green';
//         resultBox.textContent = '✅ სწორია! გადადი შემდეგ დონეზე...';
//         setTimeout(() => { window.location.href = '/levels/' + data.nextLevel; }, 1500);

//       } else if (data.status === 'wrong') {
//         resultBox.style.color = 'tomato';
//         resultBox.textContent = '❌ არასწორია, სცადე თავიდან.';
//         btn.disabled    = false;
//         btn.textContent = 'Submit';

//       } else if (data.status === 'locked') {
//         resultBox.style.color = '#b8860b';
//         resultBox.textContent = '⚠️ ეს დონე ჩაკეტილია.';
//         btn.disabled    = false;
//         btn.textContent = 'Submit';
//       }

//     } catch (err) {
//       resultBox.style.color = 'tomato';
//       resultBox.textContent = '❌ სერვერის შეცდომა.';
//       btn.disabled    = false;
//       btn.textContent = 'Submit';
//     }
//   });
</script>

@else

@include('levels.levelcomplete', ['level' => $level,  'userLevel' => auth()->user()->level])

@endif