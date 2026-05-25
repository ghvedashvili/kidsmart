@extends('layouts.app')

@section('bodyClass', 'dot-light')

@section('content')
@if($userLevel == $level)
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
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

  .nickname-wrap {
    position: relative;
    margin-top: 0.5rem;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }
  .nickname-wrap:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
  }
  .nickname-highlight {
    position: absolute;
    inset: 0;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-family: inherit;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
    overflow: hidden;
    pointer-events: none;
    background: transparent;
    z-index: 1;
    box-sizing: border-box;
  }
  #nicknameInput {
    position: relative;
    z-index: 2;
    color: transparent !important;
    caret-color: #212529;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    resize: vertical;
    width: 100%;
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

        <div class="nickname-wrap mt-2">
          <div id="nicknameHighlight" class="nickname-highlight" aria-hidden="true"></div>
          <textarea id="nicknameInput" class="form-control" rows="3" placeholder="შეიყვანეთ Nickname"></textarea>
        </div>
        <div id="charCounter" class="text-muted mt-1" style="display:none;">0/35</div>
        <div id="rulesContainer" class="rules-scroll"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const nicknameInput   = document.getElementById('nicknameInput');
const nicknameHighlight = document.getElementById('nicknameHighlight');
const rulesContainer  = document.getElementById('rulesContainer');
const charCounter     = document.getElementById('charCounter');

function updateHighlight(text) {
    const rule27Active = activeRuleIds.includes(27);

    const counts = {};
    if (rule27Active) {
        [...text].forEach(c => {
            if (/[a-zA-Z]/.test(c)) counts[c.toLowerCase()] = (counts[c.toLowerCase()] || 0) + 1;
        });
    }
    const dups = new Set(Object.keys(counts).filter(l => counts[l] > 1));

    const html = [...text].map(c => {
        if (c === '\n') return '\n';
        const safe = c.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        if (rule27Active && /[a-zA-Z]/.test(c) && dups.has(c.toLowerCase())) {
            return `<span style="color:#e74c3c;font-weight:700;">${safe}</span>`;
        }
        return `<span style="color:#212529;">${safe}</span>`;
    }).join('');

    nicknameHighlight.innerHTML = html + ' ';
    nicknameHighlight.scrollTop = nicknameInput.scrollTop;
}

nicknameInput.addEventListener('scroll', () => {
    nicknameHighlight.scrollTop = nicknameInput.scrollTop;
});

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
        title: '🎉 გილოცავთ!',
        html: `გილოცავთ, რომ აირჩიეთ nickname <b>"${data.nickname}"</b>.<br><br>
               თუმცა გაგვიკვირდა — რატომ აირჩიეთ ასეთი რთული?<br>
               ამიტომ აუცილებლად უნდა დავადასტუროთ,<br>რომ არ ხართ <b>რობოტი</b>.<br><br>
               გთხოვთ გაიაროთ <b>CAPTCHA</b> ტესტი.`,
        confirmButtonText: 'გავიარო CAPTCHA →',
        allowOutsideClick: false,
        allowEscapeKey: false,
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
  return `ნიკნეიმი უნდა შეიცავდეს ქაფთჩას:<br>
    <img src="/img/captcha.png" alt="captcha"
         style="max-width:180px;margin-top:6px;border-radius:6px;display:block;">`;
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

  updateHighlight(nicknameInput.value);
}

// CHARACTER COUNTER
charCounter.style.display = 'none';
charCounter.style.opacity = '0';

function stripGeorgian() {
  const val = nicknameInput.value;
  const stripped = val.replace(/[Ⴀ-ჿⴀ-⴯]/g, '');
  if (stripped !== val) {
    const pos = Math.max(0, nicknameInput.selectionStart - (val.length - stripped.length));
    nicknameInput.value = stripped;
    nicknameInput.setSelectionRange(pos, pos);
  }
}
['keyup', 'compositionend', 'compositionupdate'].forEach(evt =>
  nicknameInput.addEventListener(evt, stripGeorgian)
);

const STORAGE_KEY = 'nickname_draft_level{{ $level }}';

nicknameInput.addEventListener('input', async ()=>{
  stripGeorgian();
  localStorage.setItem(STORAGE_KEY, nicknameInput.value);
  updateHighlight(nicknameInput.value);
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

const saved = localStorage.getItem(STORAGE_KEY);
if (saved) {
  nicknameInput.value = saved;
  updateHighlight(saved);
}
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
