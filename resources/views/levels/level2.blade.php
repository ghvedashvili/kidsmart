@extends('layouts.app')

@section('bodyClass', 'dot-light')

@section('content')
<style>
    :root {
        --google-blue: #4285f4;
        --google-green: #34a853;
        --google-yellow: #fbbc05;
        --google-red: #ea4335;
        --google-gray: #f8f9fa;
        --google-dark-gray: #dadce0;
        --google-text: #202124;
    }

    .captcha-wrapper {
        min-height: calc(100vh - 150px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        width: 100%;
    }

    .captcha-container {
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid var(--google-dark-gray);
        margin: 0 auto;
    }

    .google-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 20px 0 10px;
        background: white;
    }

    .google-logo span {
        font-size: 28px;
        font-weight: 500;
        line-height: 1;
    }

    .google-blue { color: var(--google-blue); }
    .google-red { color: var(--google-red); }
    .google-yellow { color: var(--google-yellow); }
    .google-green { color: var(--google-green); }

    .progress-container { padding: 20px 20px 0; }
    .phase-indicator { display: flex; align-items: center; margin-bottom: 10px; }
    .phase-dot {
        width: 30px; height: 30px; border-radius: 50%; background-color: var(--google-dark-gray);
        color: white; display: flex; align-items: center; justify-content: center; font-size: 14px;
        font-weight: 500; position: relative; z-index: 1;
    }
    .phase-dot.active { background-color: var(--google-blue); }
    .phase-line { flex-grow: 1; height: 2px; background-color: var(--google-dark-gray); margin: 0 8px; }
    .phase-line.active { background-color: var(--google-blue); }

    .captcha-challenge { display: none; padding:5px 20px; }
    .captcha-challenge.active { display: block; }

    .captcha-display-container { margin-bottom: 20px; }
    .captcha-label { display: block; margin-bottom: 10px; color: var(--google-text); font-size: 15px; line-height: 1.4; }
    .captcha-display {
        background-color: #f1f3f4; border-radius: 4px; padding: 10px;
        font-family: 'Courier New', monospace; font-weight: 600; text-align: center;
        letter-spacing: 0.6rem; border: 2px solid #dfe1e5; user-select: none; font-size: 2rem;
        min-height: 70px; display: flex; align-items: center; justify-content: center; word-break: break-all;
    }
    
    .captcha-display.selectable {
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: text; /* ეს აუცილებელია მონიშვნისთვის */
}
    .captcha-display.selectable:hover { background-color: #e8f0fe; border-color: var(--google-blue); }

    .captcha-input-container { margin-bottom: 20px; }
    .captcha-input {
        width: 100%; font-size: 2rem; font-family: 'Courier New', monospace;
        letter-spacing: 0.6rem; text-align: center; padding: 10px; border: 2px solid var(--google-dark-gray);
        border-radius: 4px; transition: border-color 0.2s ease; background-color: white;
    }
    .captcha-input:focus {
        border-color: var(--google-blue); box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.2); outline: none;
    }
    .captcha-input::placeholder { color: #999; letter-spacing: normal; font-size: 16px; font-family: 'Roboto', sans-serif; }

    .captcha-footer { display: flex; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--google-dark-gray); }
    .btn-verify {
        background-color: var(--google-blue); color: white; border: none; border-radius: 4px;
        padding: 12px 28px; font-size: 15px; font-weight: 500; cursor: pointer; transition: background-color 0.2s ease;
        min-width: 120px;
    }
    .btn-verify:hover { background-color: #3367d6; }
    .btn-verify:active { background-color: #2a56c6; }

    .success-container { display: none; text-align: center; padding: 40px 20px; }
    .success-icon { font-size: 64px; color: var(--google-green); margin-bottom: 20px; }
    .success-message h4 { color: var(--google-text); margin-bottom: 10px; font-weight: 500; }
    .success-message p { color: #5f6368; margin-bottom: 25px; }

    @keyframes shake { 0%,100%{transform:translateX(0);}10%,30%,50%,70%,90%{transform:translateX(-5px);}20%,40%,60%,80%{transform:translateX(5px);} }
    .shake { animation: shake 0.6s ease; border-color: var(--google-red) !important; }

    /* ── scratch card ── */
    .scratch-wrapper {
        position: relative;
        background-color: #f1f3f4;
        border-radius: 4px;
        border: 2px solid #dfe1e5;
        min-height: 70px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .scratch-reveal {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        font-size: 2rem;
        letter-spacing: 0.6rem;
        color: #202124;
        user-select: none;
        z-index: 1;
    }
    #scratchCanvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        touch-action: none;
    }

    .btn-verify.loading { position: relative; color: transparent; }
    .btn-verify.loading::after {
        content: ''; position: absolute; top: 50%; left: 50%; width: 20px; height: 20px; margin: -10px 0 0 -10px;
        border: 2px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

@if($userLevel == $level)
<div class="captcha-wrapper">
    <div class="captcha-container">
        <div class="google-logo" style="position:relative;">
            <span class="google-blue">G</span><span class="google-red">o</span><span class="google-yellow">o</span>
            <span class="google-blue">g</span><span class="google-green">l</span><span class="google-red">e</span>
            CAPTCHA
            <button onclick="showCaptchaInfo()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#5f6368;font-size:1.1rem;padding:4px;" title="რატომ CAPTCHA?">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
        <hr>
        <div class="progress-container">
            <div class="phase-indicator">
                <div class="phase-dot active" id="dot1">1</div>
                <div class="phase-line" id="line1"></div>
                <div class="phase-dot" id="dot2">2</div>
                <div class="phase-line" id="line2"></div>
                <div class="phase-dot" id="dot3">3</div>
                <div class="phase-line" id="line3"></div>
                <div class="phase-dot" id="dot4">4</div>
            </div>
        </div>

        <!-- Step 1: Standard CAPTCHA -->
        <div class="captcha-challenge active" id="step1">
            <div class="captcha-display-container">
                <span class="captcha-label text-center"><i class="bi bi-info-circle text-primary">  გთხოვთ, შეიყვანოთ ქვემოთ მოცემული ქაფთჩა:</i></span>
                <div class="captcha-display">{{ $standardCaptcha }}</div>
            </div>
            <div class="captcha-input-container">
                <input type="text" class="captcha-input" id="input1" placeholder="ჩაწერეთ captcha">
            </div>
            <div class="captcha-footer">
                <button type="button" class="btn-verify" onclick="verify(1)">შემოწმება</button>
            </div>
        </div>

        <!-- Step 2: Scratch Card CAPTCHA -->
        <div class="captcha-challenge" id="step2">
            <div class="captcha-display-container">
                <span class="captcha-label text-center"><i class="bi bi-info-circle text-primary"> არ არსებობს დაფარული, რომ არ გამოჩნდეს, და არც რამ საიდუმლო რომ არ გამჟღავნდეს.</i></span>
                <div class="scratch-wrapper" id="scratchWrapper">
                    <div class="scratch-reveal" id="scratchReveal"></div>
                    <canvas id="scratchCanvas"></canvas>
                </div>
            </div>
            <div class="captcha-input-container">
                <input type="text" class="captcha-input" id="input2" placeholder="ჩაწერეთ captcha">
            </div>
            <div class="captcha-footer">
                <button type="button" class="btn-verify" onclick="verify(2)">შემოწმება</button>
            </div>
        </div>


        <!-- Step 3: Georgian CAPTCHA (moved from step2) -->
        <div class="captcha-challenge" id="step3">
            <div class="captcha-display-container">
                 <span class="captcha-label text-center"><i class="bi bi-info-circle text-primary">  არასოდეს დაივიწყო ის, რაც შენს ეროვნულ იდენტობას ქმნის.</i></span>
                <div class="captcha-display">{{ $georgianCaptcha }}</div>
            </div>
            <div class="captcha-input-container">
                <input type="text" class="captcha-input" id="input3" placeholder="ჩაწერეთ captcha">
            </div>
            <div class="captcha-footer">
                <button type="button" class="btn-verify" onclick="verify(3)">შემოწმება</button>
            </div>
        </div>

        <!-- Step 4: Rotating CAPTCHA -->
        <div class="captcha-challenge" id="step4">
            <div class="captcha-display-container">
                 <span class="captcha-label text-center"><i class="bi bi-info-circle text-primary">  ცვლიებები არასდროს არის შემთხვევითობა! ყველაფერს აქვს თავისი კანონზომიერება.</i></span>
                <div class="captcha-display" id="rotatingDisplay">{{ $rotatingCaptcha }}</div>
            </div>
            <div class="captcha-input-container">
                <input type="text" class="captcha-input" id="input4" placeholder="ჩაწერეთ captcha">
            </div>
            <div class="captcha-footer">
                <button type="button" class="btn-verify" onclick="verify(4)">შემოწმება</button>
            </div>
        </div>

        <div class="success-container" id="success">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <div class="success-message">
                <h4>გილოცავთ!</h4>
                <p>თქვენ წარმატებით დაასრულეთ CAPTCHA ტესტი.</p>
                <button type="button" class="btn-verify" onclick="nextLevel()">შემდეგი დონე</button>
            </div>
        </div>
    </div>
</div>
@else

@include('levels.levelcomplete', ['level' => $level,  'userLevel' => auth()->user()->level])
@endif

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let selectionCaptcha = @json($selectionCaptcha);
let rotatingCaptcha  = @json($rotatingCaptcha);

function updateProgress(step){
    document.querySelectorAll('.phase-dot').forEach(dot=>dot.classList.remove('active'));
    document.querySelectorAll('.phase-line').forEach(line=>line.classList.remove('active'));
    for(let i=1;i<=step;i++){
        const dot = document.getElementById(`dot${i}`); if(dot) dot.classList.add('active');
        if(i>1){ const line = document.getElementById(`line${i-1}`); if(line) line.classList.add('active'); }
    }
}

// ── scratch card ──
let revealed = false;

function initScratch() {
    const wrapper = document.getElementById('scratchWrapper');
    const canvas  = document.getElementById('scratchCanvas');
    const reveal  = document.getElementById('scratchReveal');
    if (!wrapper || !canvas) return;

    reveal.textContent = selectionCaptcha;

    canvas.width  = wrapper.offsetWidth;
    canvas.height = wrapper.offsetHeight;

    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#dadce0';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    function getXY(e) {
        const r  = canvas.getBoundingClientRect();
        const src = e.touches ? e.touches[0] : e;
        return {
            x: (src.clientX - r.left) * canvas.width  / r.width,
            y: (src.clientY - r.top)  * canvas.height / r.height
        };
    }

    function checkDone() {
        if (revealed) return;
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        let transparent = 0;
        for (let i = 3; i < data.length; i += 4) { if (data[i] < 128) transparent++; }
        if (transparent / (data.length / 4) > 0.50) {
            revealed = true;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    canvas.style.cursor = 'default';
    ctx.lineJoin = 'round';
    ctx.lineCap  = 'round';
    ctx.lineWidth = 48;

    let painting = false;
    let firstPoint = true;

    function startScratch(e) {
        painting = true;
        firstPoint = true;
        const { x, y } = getXY(e);
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath();
        ctx.arc(x, y, 24, 0, Math.PI * 2, true);
        ctx.closePath();
        ctx.fill();
        ctx.beginPath();
        ctx.moveTo(x, y);
        firstPoint = false;
        checkDone();
    }

    function moveScratch(e) {
        if (!painting || firstPoint) return;
        const { x, y } = getXY(e);
        ctx.globalCompositeOperation = 'destination-out';
        ctx.lineTo(x, y);
        ctx.stroke();
        checkDone();
    }

    function stopScratch() {
        if (!painting) return;
        ctx.closePath();
        painting = false;
        firstPoint = true;
    }

    canvas.addEventListener('mousedown',  startScratch);
    canvas.addEventListener('mousemove',  moveScratch);
    canvas.addEventListener('mouseup',    stopScratch);
    canvas.addEventListener('mouseleave', stopScratch);
    canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startScratch(e); }, { passive: false });
    canvas.addEventListener('touchmove',  (e) => { e.preventDefault(); moveScratch(e);  }, { passive: false });
    canvas.addEventListener('touchend',   (e) => { e.preventDefault(); stopScratch();   }, { passive: false });
}



function randomChar(){ const chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; return chars[Math.floor(Math.random()*chars.length)]; }

document.getElementById('rotatingDisplay').innerText = rotatingCaptcha;
let lastVal='';
document.getElementById('input4').addEventListener('input', e=>{
    if(e.target.value !== lastVal){ rotateCaptcha(); lastVal=e.target.value; }
});
function rotateCaptcha(){
    rotatingCaptcha = rotatingCaptcha.split('').map(c=>{
        if(c>='0'&&c<='9') return (parseInt(c)+1)%10;
        let base=c>='A'&&c<='Z'?65:97;
        return String.fromCharCode(((c.charCodeAt(0)-base+1)%26)+base);
    }).join('');
    document.getElementById('rotatingDisplay').innerText=rotatingCaptcha;
}

function verify(step){
    const input=document.getElementById('input'+step);
    const button=document.querySelector('#step'+step+' .btn-verify');
    const value=input.value.trim();
    if(!value){ showError(input,'გთხოვთ, შეიყვანოთ CAPTCHA ტექსტი'); return; }
    const originalText=button.textContent;
    button.classList.add('loading'); button.textContent='';
    fetch("{{ route('level2.verify') }}",{
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({step,input:value,finalCaptcha:step===2?selectionCaptcha:step===4?rotatingCaptcha:null})
    }).then(r=>r.json()).then(res=>{
        button.classList.remove('loading'); button.textContent=originalText;
        if(res.success){
            updateProgress(step+1);
            Swal.fire({
                icon: step<4?'success':'success', title: step<4?'სწორია!':'🎉 გილოცავთ!',
                text: step<4?'გადადიხართ შემდეგ ეტაპზე':'თქვენ წარმატებით დაასრულეთ Level 2',
                confirmButtonText: step<4?'გაგრძელება':'კარგი', confirmButtonColor: 'var(--google-blue)',
                allowOutsideClick:false, allowEscapeKey:false
            }).then(result=>{
                document.querySelector('.captcha-challenge.active').classList.remove('active');
                if(step<4){ document.getElementById('step'+(step+1)).classList.add('active'); document.getElementById('input'+(step+1)).value=''; setTimeout(()=>{document.getElementById('input'+(step+1)).focus();},100); if(step===1) setTimeout(initScratch, 80); }
                else{ if(res.newLevel){ window.location.href=`/levels/${res.newLevel}`; } else{ document.querySelector('.captcha-challenge.active')?.classList.remove('active'); document.getElementById('success').style.display='block'; } }
            });
        } else { showError(input,'არასწორი CAPTCHA. სცადეთ თავიდან.'); }
    }).catch(error=>{
        button.classList.remove('loading'); button.textContent=originalText;
        Swal.fire({icon:'error',title:'შეცდომა',text:'დაფიქსირდა შეცდომა. გთხოვთ სცადოთ მოგვიანებით.',confirmButtonText:'კარგი',confirmButtonColor:'var(--google-red)'});
    });
}

function showError(inputElement,message){
    inputElement.classList.add('shake');
    Swal.fire({icon:'error',title:'შეცდომა',text:message,confirmButtonText:'კარგი',confirmButtonColor:'var(--google-red)',timer:2000,timerProgressBar:true,showConfirmButton:false});
    setTimeout(()=>{inputElement.classList.remove('shake');},600);
    inputElement.focus(); inputElement.select();
}

function nextLevel(){ window.location.href="/levels/3"; }

function showCaptchaInfo(){
    Swal.fire({
        icon: 'info',
        title: 'რატომ CAPTCHA?',
        text: 'ეს CAPTCHA ტესტი მხოლოდ იმიტომ გახდა საჭირო, რომ ძალიან უცნაური Nickname გაქვს 😄',
        confirmButtonText: 'გასაგებია',
        confirmButtonColor: 'var(--google-blue)',
    });
}

document.addEventListener('DOMContentLoaded',function(){
    updateProgress(1);
    setTimeout(()=>{ const input1=document.getElementById('input1'); if(input1) input1.focus(); },300);
});
</script>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
