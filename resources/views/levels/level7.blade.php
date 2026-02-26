

@if($userLevel == $level)

<style>
/* navbar-ის padding-top კომპენსაცია და სრული ეკრანი */
.domino-section {
    min-height: calc(100vh - 56px);
    background: linear-gradient(#800000, #8B0000);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 10px;
    box-sizing: border-box;
    overflow-x: hidden;
}

.domino {
  border-radius: clamp(5px, 1.5vw, 15px);
  background-color: #FFF8DC;
  display: inline-block;
  box-shadow: inset 3px 5px 10px 1px #F5F5DC;
  padding: 3px;
}

.line {
  width: var(--line-w);
  height: var(--line-h);
  margin: 0 auto;
  background-color: #000;
  border-radius: 5px;
}

.upper, .lower {
  width: var(--tile-size);
  height: var(--tile-size);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  padding: 0;
}

.dots {
  display: flex;
  justify-content: space-between;
  width: 100%;
  height: 100%;
  padding: var(--dot-padding);
  box-sizing: border-box;
}

.dot {
  border-radius: 50%;
  width: var(--dot-size);
  height: var(--dot-size);
  background-color: #000;
  flex-shrink: 0;
}

.dot-row {
  display: flex;
  flex-flow: column;
  justify-content: space-between;
  height: 100%;
}

.dot-row:nth-child(2) { 
  justify-content: center; 
}

/* ბრაილი */
.br-f .dot, .br-i .dot, .br-r .dot,
.br-e .dot, .br-m .dot, .br-a .dot,
.br-n .dot, .br-blank .dot { opacity: 0; }

.br-f .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }
.br-f .dot-row:nth-child(1) .dot:nth-child(2) { opacity: 1; }
.br-f .dot-row:nth-child(3) .dot:nth-child(1) { opacity: 1; }

.br-i .dot-row:nth-child(1) .dot:nth-child(2) { opacity: 1; }
.br-i .dot-row:nth-child(3) .dot:nth-child(1) { opacity: 1; }

.br-r .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }
.br-r .dot-row:nth-child(1) .dot:nth-child(2) { opacity: 1; }
.br-r .dot-row:nth-child(1) .dot:nth-child(3) { opacity: 1; }
.br-r .dot-row:nth-child(3) .dot:nth-child(2) { opacity: 1; }

.br-e .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }
.br-e .dot-row:nth-child(3) .dot:nth-child(2) { opacity: 1; }

.br-m .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }
.br-m .dot-row:nth-child(1) .dot:nth-child(3) { opacity: 1; }
.br-m .dot-row:nth-child(3) .dot:nth-child(1) { opacity: 1; }

.br-a .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }

.br-n .dot-row:nth-child(1) .dot:nth-child(1) { opacity: 1; }
.br-n .dot-row:nth-child(1) .dot:nth-child(3) { opacity: 1; }
.br-n .dot-row:nth-child(3) .dot:nth-child(1) { opacity: 1; }
.br-n .dot-row:nth-child(3) .dot:nth-child(2) { opacity: 1; }

/* ჩვეულებრივი */
.zero .dot { opacity: 0; }
.one .dot { opacity: 0; }
.one .dot-row:nth-child(2) .dot { opacity: 1; }
.two .dot { opacity: 0; }
.two .dot-row:nth-child(1) .dot:nth-child(1),
.two .dot-row:nth-child(3) .dot:nth-child(3) { opacity: 1; }
.three .dot { opacity: 0; }
.three .dot-row:nth-child(1) .dot:nth-child(1),
.three .dot-row:nth-child(2) .dot:nth-child(1),
.three .dot-row:nth-child(3) .dot:nth-child(3) { opacity: 1; }
.four .dot { opacity: 1; }
.four .dot-row:nth-child(1) .dot:nth-child(2),
.four .dot-row:nth-child(2) .dot:nth-child(1),
.four .dot-row:nth-child(3) .dot:nth-child(2) { opacity: 0; }
.five .dot { opacity: 1; }
.five .dot-row:nth-child(1) .dot:nth-child(2),
.five .dot-row:nth-child(3) .dot:nth-child(2) { opacity: 0; }
.six .dot { opacity: 1; }
.six .dot-row:nth-child(2) .dot { opacity: 0; }

/* CSS ცვლადები */
:root {
  --tile-size: clamp(40px, 11vw, 75px);
  --dot-size: calc(var(--tile-size) * 0.16);
  --dot-padding: calc(var(--tile-size) * 0.1);
  --line-w: var(--tile-size);
  --line-h: clamp(2px, 0.6vw, 5px);
}

/* მედია ქვერი პატარა ეკრანებისთვის */
@media (max-width: 768px) {
  :root {
    --tile-size: clamp(35px, 10vw, 55px);
    --dot-size: calc(var(--tile-size) * 0.15);
    --dot-padding: calc(var(--tile-size) * 0.08);
  }
}

/* ძალიან პატარა ეკრანებისთვის */
@media (max-width: 480px) {
  :root {
    --tile-size: clamp(30px, 9vw, 45px);
    --dot-size: calc(var(--tile-size) * 0.14);
    --dot-padding: calc(var(--tile-size) * 0.07);
  }
}

.dominoes-row {
  display: flex;
  flex-wrap: nowrap;
  justify-content: center;
  align-items: center;
  gap: clamp(4px, 1.5vw, 12px);
  width: max-content;
  min-width: 100%;
  padding: 15px 20px;
  margin: 0 auto;
}

.dominoes-container {
  width: 100%;
  overflow-x: auto;
  padding: 10px 0;
  -webkit-overflow-scrolling: touch;
}

.domino-wrap {
  flex-shrink: 0;
}

/* ინპუტი */
#answerForm {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  width: min(350px, 95vw);
  margin-top: 40px;
  position: sticky;
  bottom: 20px;
  z-index: 10;
}

#answerForm input.form-control {
  text-align: center;
  background: rgba(255,255,255,0.15);
  border: 2px solid rgba(255,255,255,0.35);
  color: #fff;
  border-radius: 10px;
  font-size: clamp(15px, 4.5vw, 18px);
  padding: clamp(10px, 2.5vw, 14px);
  width: 100%;
}

#answerForm input.form-control::placeholder { 
  color: rgba(255,255,255,0.5); 
}

#answerForm input.form-control:focus {
  background: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.7);
  color: #fff;
  box-shadow: none;
}

#answerForm .btn {
  width: 100%;
  padding: clamp(10px, 2.5vw, 14px);
  font-size: clamp(16px, 4.5vw, 18px);
  background-color: #FFF8DC;
  border: none;
  color: #800000;
  font-weight: bold;
  border-radius: 10px;
  transition: all 0.3s ease;
  cursor: pointer;
}

#answerForm .btn:hover {
  background-color: #FFE4B5;
  transform: scale(1.02);
}

#answerForm .btn:active {
  transform: scale(0.98);
}
#soundToggle {
  position: fixed;
  top: 70px;
  right: 15px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(5px);
  color: #fff;
  font-size: 18px;
  cursor: pointer;
  z-index: 999;
  transition: 0.2s ease;
}

#soundToggle:hover {
  background: rgba(255,255,255,0.35);
  transform: scale(1.1);
}
html, body {
    height: 100%;
    overflow: hidden;
}

.domino-section {
    height: calc(100vh - 56px);
    overflow: hidden;
    justify-content: flex-start;
padding-top: 160px;
}

.dominoes-container {
    overflow: hidden;
}

</style>

<div class="domino-section">
<audio id="bgMusic" loop playsinline preload="auto">
    <source src="{{ asset('audio/music.mp3') }}" type="audio/mpeg">
</audio>

<button id="soundToggle" type="button">🔇</button>


  <div class="dominoes-container">
    <div class="dominoes-row">

      <!-- მარცხენა ჩვეულებრივი 1 -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper four">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower six">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- მარცხენა ჩვეულებრივი 2 -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper two">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower five">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ბრაილი: F / M -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper br-f">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower br-m">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ბრაილი: I / A -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper br-i">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower br-a">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ბრაილი: R / N -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper br-r">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower br-n">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ბრაილი: E / ცარიელი -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper br-e">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower br-blank">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- მარჯვენა ჩვეულებრივი -->
      <div class="domino-wrap">
        <div class="domino">
          <div class="upper six">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
          <div class="line"></div>
          <div class="lower three">
            <div class="dots">
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div></div>
              <div class="dot-row"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- პასუხის ფორმა -->
  <form id="answerForm">
      <input type="text" class="form-control" id="answer" placeholder="შეიყვანეთ პასუხი...">
      <button type="submit" class="btn">Submit</button>
  </form>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const music = document.getElementById("bgMusic");
    const toggleBtn = document.getElementById("soundToggle");

    let isPlaying = false;

    toggleBtn.addEventListener("click", async function () {

        try {
            if (!isPlaying) {
                await music.play();
                toggleBtn.textContent = "🔊";
                isPlaying = true;
            } else {
                music.pause();
                toggleBtn.textContent = "🔇";
                isPlaying = false;
            }
        } catch (err) {
            console.log("Playback blocked:", err);
        }

    });
});
</script>
@else

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">level{{ $level }}</h5>
                    @if($userLevel > $level)
                        <div class="alert alert-success">თქვენ გაიარეთ ეს ტური წარმატებით</div>
                    @else
                        <div class="alert alert-warning">⚠️ ეს დონე ჯერ არ არის ხელმისაწვდომი</div>
                    @endif
                    <a href="{{ route('levels.show', ['level' => $userLevel]) }}" class="btn btn-primary">გადადით მიმდინარე დონეზე</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
