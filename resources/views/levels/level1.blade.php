@if($userLevel == $level)
<input type="text"
       id="nickname"
       class="form-control mb-3"
       placeholder="Type your nickname...">

<ul id="rules" class="list-group mb-3">
    {{-- Progressive + completed rules dynamically --}}
</ul>

<!-- <button id="submitBtn" class="btn btn-success" disabled>
    Submit
</button> -->

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<script>
const input = document.getElementById('nickname');
const rulesEl = document.getElementById('rules');
// const submitBtn = document.getElementById('submitBtn');

let allRules = [];           // ყველა წესი backend-დან
let activeRuleIds = [];      // აქტიური წესები
let completedRuleIds = new Set(); // შესრულებული წესები
let gameWon = false;
let isSubmitting = false;    // თავიდან ავიცილოთ მრავალჯერადი submit

// Live check AJAX
async function fetchRules() {
    const res = await fetch("{{ url('/level/'.$level.'/nickname/live') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({ nickname: input.value })
    });

    const data = await res.json();
    if(data.locked) return;

    allRules = data.rules;

    // პირველი წესი ყოველთვის აქტიური, ჩათვალეთ თავიდანვე
    if(activeRuleIds.length === 0 && allRules.length > 0){
        activeRuleIds.push(allRules[0].id);
    }
}

// წესი შესრულებულია თუ არა
function isRulePassed(rule, nickname) {
    if(typeof rule.passed === 'boolean') return rule.passed;
    if(typeof rule.passed === 'function') return rule.passed();
    return false;
}

// Submit nickname to server
async function submitNickname() {
    if (isSubmitting) return; // თავიდან ავიცილოთ მრავალჯერადი გაგზავნა
    
    isSubmitting = true;
    
    try {
        const res = await fetch("{{ url('/level/'.$level.'/nickname/submit') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ nickname: input.value })
        });

        const data = await res.json();

        if(data.status === 'success'){
            showWinState(data.nickname, data.newLevel);
        } else if(data.rules){
            allRules = data.rules;
            checkRules();
        }
    } catch (error) {
        console.error('Error submitting nickname:', error);
    } finally {
        isSubmitting = false;
    }
}

// ყველა წესის განახლება
function checkRules() {
    const nicknameVal = input.value;

    // 1️⃣ განვაახლოთ completedRules
    allRules.forEach(rule => {
        const passed = isRulePassed(rule, nicknameVal);
        if(passed){
            completedRuleIds.add(rule.id);
        } else {
            completedRuleIds.delete(rule.id);
        }
    });

    // 2️⃣ აქტიური წესების შემოწმება
    const allActivePassed = activeRuleIds.every(id => {
        const rule = allRules.find(r => r.id === id);
        return isRulePassed(rule, nicknameVal);
    });

    // 3️⃣ ახალი წესის გახსნა თუ ყველაფერი აქტიური შესრულებულია
    if(allActivePassed){
        const nextRule = allRules.find(r => 
            !activeRuleIds.includes(r.id) && !isRulePassed(r, nicknameVal)
        );
        if(nextRule){
            activeRuleIds.push(nextRule.id);
        }
    }

    renderRules();

    // 4️⃣ თუ ყველა წესი შესრულებულია, ავტომატურად გააგზავნე submit
    const allPassed = allRules.every(rule => isRulePassed(rule, nicknameVal));
    if(allPassed && !gameWon && input.value.trim() !== ''){
        gameWon = true;
        // ავტომატურად გაგზავნა სერვერზე
        submitNickname();
    }
}

// წესების რენდერი
function renderRules() {
    rulesEl.innerHTML = '';

    const nicknameVal = input.value;

    const notPassedRules = [];
    const passedRules = [];

    // დაყოფა შესრულებულ/არასრულებულ წესებად
    activeRuleIds.forEach(id => {
        const rule = allRules.find(r => r.id === id);
        const passed = isRulePassed(rule, nicknameVal);
        if(passed){
            passedRules.push(rule);
        } else {
            notPassedRules.push(rule);
        }
    });

    // ჯერ ვაჩვენოთ არასრულად შესრულებული წესები
    notPassedRules.forEach(rule => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-warning';
        li.textContent = '➡ ' + rule.text;
        rulesEl.appendChild(li);
    });

    // შემდეგ შესრულებული წესები
    passedRules.forEach(rule => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-success';
        li.textContent = '✅ ' + rule.text;
        rulesEl.appendChild(li);
    });
}


// SweetAlert2 გამარჯვების შეტყობინება
function showWinState(nicknameVal, newLevel){
    Swal.fire({
        title: '🎉 Nickname მიღებულია!',
        html: `<b>${nicknameVal}</b>`,
        showCancelButton: true,
        confirmButtonText: 'NEXT LEVEL',
        cancelButtonText: 'OK',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if(result.isConfirmed){
            // გადაგზავნა შემდეგ LevelController show ლეველზე
            window.location.href = `/levels/${newLevel}`;
        } else {
            // OK ღილაკი უბრალოდ დახურავს
        }
    });
}

// Keyup + input listener
input.addEventListener('input', async () => {
    await fetchRules();
    checkRules();
});

// Submit listener (საჭიროების შემთხვევაში)
// submitBtn.addEventListener('click', () => {
//     submitNickname();
// });

// Initialize
fetchRules();

</script>
@endif
