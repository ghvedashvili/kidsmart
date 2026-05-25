@extends('layouts.app')

@section('content')
@if($userLevel == $level)
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  body {
    background: #f5f5f5 !important;
    position: relative;
  }
  body::before {
    content: '';
    position: fixed;
    inset: -100%;
    background-image: radial-gradient(rgba(0,0,0,0.13) 1px, transparent 1px);
    background-size: 28px 28px;
    animation: dotGridMove 18s linear infinite;
    pointer-events: none;
    z-index: 0;
  }
  @keyframes dotGridMove {
    0%   { transform: translate(0, 0); }
    100% { transform: translate(28px, 28px); }
  }
  .container-fluid { position: relative; z-index: 1; }
  @keyframes shakeX {
    0%   { transform: translateX(0); }
    50%  { transform: translateX(-4px); }
    100% { transform: translateX(0); }
  }

  .rule-shake-once {
    animation: shakeX 0.4s ease-in-out 1;
  }

  .rules-card {
    height: calc(100vh - 150px);
    max-height: calc(100vh - 150px);
    display: flex;
    flex-direction: column;
  }

  .rules-scroll {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    padding-right: 4px;
    font-size: 0.7rem;
  }

  .captcha-container {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .captcha-letters {
    display: inline-flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .captcha-letter {
    font-size: 1.3em;
    font-weight: bold;
    min-width: 30px;
    min-height: 30px;
    padding: 4px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    position: relative;
  }
</style>

<div class="container-fluid">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8 col-xl-6">
      <div class=" p-3 mb-3 rules-card">
        <h2 class="h5 mb-3 text-center">შეიყვანე შენი nickname</h2>

        <textarea id="nicknameInput" class="form-control mt-2" rows="3" placeholder="შეიყვანეთ Nickname"></textarea>
        <div id="charCounter" class="text-muted mt-1" style="display:none;">0/35</div>
        <div id="rulesContainer" class="rules-scroll"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const nicknameInput = document.getElementById('nicknameInput');
const rulesContainer = document.getElementById('rulesContainer');
const charCounter = document.getElementById('charCounter');

let allRules = [];
let activeRuleIds = [];
let completedRuleIds = new Set();
let gameWon = false;
let isSubmitting = false;
let shakenRuleIds = new Set();

// BLOCK COPY/PASTE
['copy','paste','cut','contextmenu','drop'].forEach(evt=>{
  nicknameInput.addEventListener(evt,e=>e.preventDefault());
});
nicknameInput.addEventListener('keydown', e=>{
  if((e.ctrlKey||e.metaKey)&&['v','c','x'].includes(e.key.toLowerCase())) e.preventDefault();
});

// FETCH rules
async function fetchRules() {
  try {
    const res = await fetch(`/level/{{ $level }}/nickname/live`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ nickname: nicknameInput.value })
    });

    if (!res.ok) return;

    const data = await res.json();
    if (data.locked) return;

    allRules = data.rules || [];
    if (activeRuleIds.length === 0 && allRules.length > 0) activeRuleIds.push(allRules[0].id);
  } catch (err) {
    console.error('fetchRules error:', err);
  }
}

// SUBMIT nickname
async function submitNickname() {
  if (isSubmitting) return;
  isSubmitting = true;
  try {
    const res = await fetch(`/level/{{ $level }}/nickname/submit`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ nickname: nicknameInput.value })
    });
    if (!res.ok) throw new Error('Submit failed');
    const data = await res.json();
    if (data.status === 'success') {
      Swal.fire({
        title: '🎉 Nickname accepted!',
        html: `<b>${data.nickname}</b>`,
        confirmButtonText: 'NEXT LEVEL',
        allowOutsideClick: false
      }).then(() => {
        window.location.href = `/levels/${data.newLevel}`;
      });
    } else {
      gameWon = false;
      allRules = data.rules || allRules;
      checkRules();
    }
  } catch (err) {
    console.error(err);
    gameWon = false;
  } finally {
    isSubmitting = false;
  }
}

// RENDER captcha
function renderCaptchaRuleHTML(r) {
  const prefix = 'აკრიფე ეს ქაფთჩა: "';
  const suffix = '"';
  if (!r.text.startsWith(prefix) || !r.text.endsWith(suffix)) return r.text;

  const captcha = r.text.slice(prefix.length, -1);
  let lettersHTML = '';
  [...captcha].forEach((char, i) => {
    const hue = Math.floor(Math.random() * 360);
    const rot = Math.floor(Math.random() * 360);
    const tx = (Math.random()*10-5).toFixed(1);
    const ty = (Math.random()*10-5).toFixed(1);
    const angle = Math.floor(Math.random() * 360);
    lettersHTML += `<span class="captcha-letter"
      style="
        color:hsl(${hue},70%,50%);
        transform:rotate(${rot}deg) translate(${tx}px,${ty}px);
        background:repeating-linear-gradient(
          ${angle}deg,
          hsla(${hue},70%,50%,.15),
          hsla(${hue},70%,50%,.15) 1px,
          hsla(${hue+40},70%,50%,.1) 3px
        );
        border:1px solid hsla(${hue},70%,50%,.2);
      ">
      ${char}</span>`;
  });
  return `
    აკრიფე ეს ქაფთჩა:
    <span class="captcha-container">
      "<span class="captcha-letters">${lettersHTML}</span>"
    </span>`;
}

// CHECK rules
function checkRules() {
  allRules.forEach(r=>r.passed?completedRuleIds.add(r.id):completedRuleIds.delete(r.id));

  const allActivePassed = activeRuleIds.every(id=>{
    const r = allRules.find(x=>x.id===id);
    return r && r.passed;
  });

  if(allActivePassed){
    const next = allRules.find(r=>!activeRuleIds.includes(r.id) && !r.passed);
    if(next) activeRuleIds.push(next.id);
  }

  renderRules();

  const allPassed = allRules.length && allRules.every(r=>r.passed);
  if(allPassed && !gameWon && !isSubmitting && nicknameInput.value.trim()!==''){
    gameWon = true;
    submitNickname();
  }
}

// RENDER rules
function renderRules() {
  rulesContainer.innerHTML = '';
  const pending = [], passed = [];
  activeRuleIds.forEach(id => {
    const r = allRules.find(x => x.id === id);
    if(!r) return;
    r.passed ? passed.push(r) : pending.push(r);
  });

  pending.forEach((r,i)=>{
    rulesContainer.insertAdjacentHTML('beforeend',`
      <div class="rule ${!shakenRuleIds.has(r.id)&&i===0?'rule-shake-once':''}"
           style="background:#ffeaea;color:#e74c3c;border-left:6px solid #c0392b;padding:10px 15px;margin:5px 0;border-radius:8px;font-weight:bold;">
        ${r.id===999?renderCaptchaRuleHTML(r):r.text}
      </div>
    `);
    shakenRuleIds.add(r.id);
  });

  passed.forEach(r=>{
    rulesContainer.insertAdjacentHTML('beforeend',`
      <div class="rule"
           style="background:#d4f8e8;color:#27ae60;border-left:6px solid #2ecc71;padding:10px 15px;margin:5px 0;border-radius:8px;font-weight:bold;">
        ${r.id===999?renderCaptchaRuleHTML(r):r.text}
      </div>
    `);
  });
}

// CHARACTER COUNTER
charCounter.style.display = 'none';
charCounter.style.opacity = '0';

nicknameInput.addEventListener('input', async ()=>{
  await fetchRules();
  checkRules();
  const len = nicknameInput.value.length;
  const rule12 = allRules.find(r=>r.id===12);
  const hasRule12 = rule12 && activeRuleIds.includes(12);

  if(hasRule12){
    charCounter.textContent = `${len}/35`;
    if(charCounter.style.display==='none'){
      charCounter.style.display='block';
      setTimeout(()=>{charCounter.style.opacity='1'},10);
    }
  } else if(charCounter.style.display!=='none'){
    charCounter.style.opacity='0';
    setTimeout(()=>{charCounter.style.display='none'},300);
  }
});

fetchRules().then(()=>{ checkRules(); });

</script>

@else
<!-- <div class="container mt-5">
  <div class="alert alert-success text-center">
    <b>თქვენ უკვე შექმენით თქვენი ნიკნეიმი</b>
    <br>
     <b>{{ $nickname }}</b>
  </div>
</div> -->

@include('levels.levelcomplete', ['level' => $level,  'userLevel' => auth()->user()->level])

@endif
@endsection
