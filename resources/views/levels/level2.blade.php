@extends('levels.layout')

@if($userLevel == $level)

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
body{
    background: linear-gradient(135deg,#667eea,#764ba2);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}
.container{
    max-width:500px;
    background:#fff;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.2);
    overflow:hidden;
}
header{
    background:#2c3e50;
    color:#fff;
    padding:20px;
    text-align:center;
}
.challenge{
    padding:20px;
    text-align:center;
    display:none;
}
.challenge.active{
    display:block;
}
.captcha-display{
    font-size:2.5rem;
    font-weight:bold;
    letter-spacing:8px;
    padding:20px;
    margin:15px 0;
    border:3px solid #3498db;
    border-radius:15px;
    background:#f8f9fa;
    font-family:monospace;
}
.captcha-display.selectable{
    cursor:pointer;
}
.captcha-input{
    width:100%;
    font-size:2rem;
    text-align:center;
    padding:12px;
    border-radius:10px;
    border:3px solid #3498db;
    letter-spacing:6px;
    font-family:monospace;
}
button{
    margin-top:15px;
    padding:12px 30px;
    border:none;
    border-radius:50px;
    background:#3498db;
    color:#fff;
    font-size:1.1rem;
    cursor:pointer;
}
button:hover{
    background:#2980b9;
}
.phase{
    background:#e8f4fd;
    padding:10px;
    border-radius:10px;
    font-weight:bold;
    margin-bottom:10px;
}
#success{
    display:none;
    text-align:center;
    padding:30px;
}
#success h2{
    color:#2ecc71;
}
</style>

<div class="container">
    <header>
        <h1>CAPTCHA ტესტი</h1>
    </header>

    {{-- STEP 1 --}}
    <div class="challenge active" id="step1">
        <div class="phase">ეტაპი 1/3</div>
        <div class="captcha-display">{{ $georgianCaptcha }}</div>
        <input class="captcha-input" id="input1">
        <button onclick="verify(1)">შემოწმება</button>
        <div id="msg1"></div>
    </div>

    {{-- STEP 2 --}}
    <div class="challenge" id="step2">
        <div class="phase">ეტაპი 2/3</div>
        <div class="captcha-display selectable" id="selectionDisplay">********</div>
        <input class="captcha-input" id="input2">
        <button onclick="verify(2)">შემოწმება</button>
        <div id="msg2"></div>
    </div>

    {{-- STEP 3 --}}
    <div class="challenge" id="step3">
        <div class="phase">ეტაპი 3/3</div>
        <div class="captcha-display" id="rotatingDisplay"></div>
        <input class="captcha-input" id="input3">
        <button onclick="verify(3)">შემოწმება</button>
        <div id="msg3"></div>
    </div>

    {{-- SUCCESS --}}
    <div id="success">
        <h2>✅ გილოცავთ!</h2>
        <p>თქვენ წარმატებით დაასრულეთ Level 2</p>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ====== INITIAL DATA FROM BACKEND ====== */
let selectionCaptcha = @json($selectionCaptcha);
let rotatingCaptcha  = @json($rotatingCaptcha);

/* ====== STEP 2 LOGIC ====== */
let revealed = false;
let changed  = false;

document.getElementById('selectionDisplay').addEventListener('mouseup', () => {
    if(!revealed){
        document.getElementById('selectionDisplay').innerText = selectionCaptcha;
        revealed = true;
        return;
    }
    if(changed) return;

    let arr = selectionCaptcha.split('');
    let i = Math.floor(Math.random()*arr.length);
    arr[i] = randomChar();
    selectionCaptcha = arr.join('');
    document.getElementById('selectionDisplay').innerText = selectionCaptcha;
    changed = true;
});

function randomChar(){
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return chars[Math.floor(Math.random()*chars.length)];
}

/* ====== STEP 3 LOGIC ====== */
document.getElementById('rotatingDisplay').innerText = rotatingCaptcha;
let lastVal = '';

document.getElementById('input3').addEventListener('input', e=>{
    if(e.target.value !== lastVal){
        rotateCaptcha();
        lastVal = e.target.value;
    }
});

function rotateCaptcha(){
    rotatingCaptcha = rotatingCaptcha.split('').map(c=>{
        if(c>='0' && c<='9') return (parseInt(c)+1)%10;
        let base = c>='A' && c<='Z' ? 65 : 97;
        return String.fromCharCode(((c.charCodeAt(0)-base+1)%26)+base);
    }).join('');
    document.getElementById('rotatingDisplay').innerText = rotatingCaptcha;
}

/* ====== VERIFY ====== */
function verify(step){
    const input = document.getElementById('input'+step).value;

    fetch("{{ route('level2.verify') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':CSRF
        },
        body:JSON.stringify({
            step,
            input,
            finalCaptcha:
                step===2 ? selectionCaptcha :
                step===3 ? rotatingCaptcha : null
        })
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){

            // ✅ SWEET ALERT სწორ პასუხზე
            Swal.fire({
                icon: 'success',
                title: step < 3 ? 'სწორია!' : '🎉 გილოცავთ!',
                text: step < 3
                    ? 'გადადიხართ შემდეგ ეტაპზე'
                    : 'თქვენ წარმატებით დაასრულეთ Level 2',
                confirmButtonText: step < 3 ? 'გაგრძელება' : 'NEXT LEVEL',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(result => {

                // 👉 ეტაპებს შორის გადასვლა
                document.getElementById('step'+step).classList.remove('active');

                if(step < 3){
                    document.getElementById('step'+(step+1)).classList.add('active');
                } else {
                    // 🎯 LEVEL COMPLETE
                    // backend-მა უნდა დაგიბრუნოს newLevel
                    if(res.newLevel){
                        window.location.href = `/levels/${res.newLevel}`;
                    } else {
                        // fallback (თუ არ აბრუნებს)
                        document.getElementById('success').style.display='block';
                    }
                }

            });

        } else {
            Swal.fire({
                icon: 'error',
                title: '❌ არასწორია',
                text: 'სცადეთ თავიდან',
                timer: 1200,
                showConfirmButton: false
            });
        }
    });
}

</script>

@endif
