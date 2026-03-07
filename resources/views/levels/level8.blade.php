<!-- <!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, interactive-widget=resizes-content"> -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;700&display=swap" rel="stylesheet">
@if($userLevel == $level)
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; overflow: hidden; }
    html {
        height: -webkit-fill-available;
    }
    body {
        min-height: -webkit-fill-available;
    }
    body {
    background-color: #f2f2f7;
    display: flex;
    align-items: center;       /* ვერტიკალური ცენტრი */
    justify-content: center;   /* ჰორიზონტალური ცენტრი */
    height: 100vh;
    font-family: 'Lato', sans-serif;
}

    .phone-wrapper {
       display: flex;
    flex-direction: column;
    align-items: center;
    }

    .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        width: 241.25px;
        height: 509px;
        border-radius: 50px;
        background: black;
        box-shadow: 0px 9px 60px rgba(0,0,0,1);
        flex-shrink: 0;
    }

    /* screen clips everything inside it */
    .screen {
        position: relative;
        width: 231.25px;
        height: 501px;
        top: 4px;
        background: url('https://media.idownloadblog.com/wp-content/uploads/2017/11/iOS-stock-6-for-iPhone-X-768x1663.png');
        background-size: cover;
        background-position: center center;
        border-radius: 45px;
        z-index: 1;
        overflow: hidden;
    }

    /* scrollable layer */
    .screen-scroll {
        position: absolute;
        inset: 0;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: none;
        border-radius: 45px;
    }
    .screen-scroll::-webkit-scrollbar { display: none; }

    .screen-content {
        display: flex;
        flex-direction: column;
        min-height: min-content;
        color: white;
        padding-bottom: 20px;
        font-size: 12px;
    }

    .top-menu {
        display: flex;
        flex-direction: row;
        justify-content: flex-end;
        padding: 6px 20px 0 0;
        font-size: 7px;
        color: #fefdfa;
        gap: 5px;
        position: sticky;
        top: 0;
        z-index: 50;
        pointer-events: none;
    }

    .lock-time {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 0 25px 0;
        color: white;
        text-shadow: 0 0 5px rgba(0,0,0,0.3);
        width: 100%;
    }
    .lock {
        font-size: 35px;
        margin-bottom: 5px;
        cursor: pointer;
        animation: lockPulse 2.5s ease-in-out infinite;
        transition: transform 0.1s;
    }
    .lock:active { transform: scale(0.88); }
    @keyframes lockPulse {
        0%, 100% { filter: drop-shadow(0 0 0px rgba(255,255,255,0)); }
        50%       { filter: drop-shadow(0 0 8px rgba(255,255,255,0.6)); }
    }
    .time { font-size: 48px; font-weight: 300; line-height: 1; pointer-events: none; }
    .date { font-size: 18px; font-weight: 300; letter-spacing: 0.5px; pointer-events: none; }

    .notifications-stack {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 0 6px 20px 6px;
    }
    .alert1 {
        background-color: rgba(220, 222, 233, 0.95);
        color: black;
        border-radius: 20px;
        border: 0.5px solid rgba(255,255,255,0.3);
        flex-shrink: 0;
    }
    .alert-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        background: rgba(255,255,255,0.15);
        border-bottom: 0.5px solid rgba(0,0,0,0.05);
        border-radius: 20px 20px 0 0;
    }
    .alert-icon-type { display: flex; align-items: center; gap: 8px; font-weight: 600; }
    .alert-icon-type .icon { width: 20px; text-align: center; }
    .alert-time { font-size: 11px; opacity: 0.8; font-weight: 400; }
    .alert-body {
        padding: 12px 12px 14px 12px;
        background: rgba(255,255,255,0.3);
        border-top: 0.5px solid rgba(255,255,255,0.5);
        border-radius: 0 0 20px 20px;
    }
    .alert-body b { font-weight: 700; }

    /* ── Backdrop — inside .screen, position:absolute ── */
    .sheet-backdrop {
        position: absolute;
        inset: 0;
        border-radius: 45px;
        background: rgba(0,0,0,0);
        z-index: 100;
        pointer-events: none;
        transition: background 0.38s ease;
    }
    .sheet-backdrop.visible {
        background: rgba(0,0,0,0.6);
        pointer-events: auto;
    }

    /* ── Sheet — inside .screen, slides up from bottom ── */
    .sheet {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(242,242,247,0.98);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        border-radius: 20px 20px 45px 45px;
        z-index: 110;
        padding-bottom: 50px;
        transform: translateY(100%);
        transition: transform 0.44s cubic-bezier(0.32, 0.72, 0, 1);
        box-shadow: 0 -6px 30px rgba(0,0,0,0.3);
    }
    .sheet.open { transform: translateY(0); }

    .sheet-handle {
        width: 36px; height: 4px;
        background: rgba(0,0,0,0.18);
        border-radius: 3px;
        margin: 10px auto 0 auto;
    }
    .sheet-title {
        text-align: center;
        font-size: 14px; font-weight: 700;
        color: #1c1c1e;
        margin: 12px 16px 3px 16px;
    }
    .sheet-sub {
        text-align: center;
        font-size: 10px; color: #6e6e73;
        margin-bottom: 14px; padding: 0 10px;
    }
    .sheet-input-wrap {
        position: relative;
        margin: 0 12px 10px 12px;
    }
    .sheet-input-wrap .input-icon {
        position: absolute; left: 11px; top: 50%;
        transform: translateY(-50%);
        color: #8e8e93; font-size: 12px;
    }
    .sheet-input {
        width: 100%;
        background: white;
        border: 1.5px solid rgba(0,0,0,0.1);
        border-radius: 11px;
        padding: 10px 10px 10px 30px;
        font-family: 'Lato', sans-serif;
        font-size: 13px; color: #1c1c1e;
        outline: none;
        transition: border-color 0.2s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .sheet-input:focus { border-color: #007aff; }
    .sheet-input::placeholder { color: #aeaeb2; }

    .sheet-submit {
        display: block;
        width: calc(100% - 24px); margin: 0 12px;
        background: #007aff; color: white; border: none;
        border-radius: 12px; padding: 11px;
        font-family: 'Lato', sans-serif;
        font-size: 14px; font-weight: 700;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        box-shadow: 0 3px 12px rgba(0,122,255,0.35);
    }
    .sheet-submit:active { background: #0062cc; transform: scale(0.98); }

    .sheet-cancel {
        display: block;
        width: calc(100% - 24px); margin: 7px 12px 0 12px;
        background: rgba(0,0,0,0.07); color: #3a3a3c; border: none;
        border-radius: 12px; padding: 11px;
        font-family: 'Lato', sans-serif;
        font-size: 14px; font-weight: 700;
        cursor: pointer; transition: background 0.15s;
    }
    .sheet-cancel:active { background: rgba(0,0,0,0.13); }

    .bottom-line {
        position: absolute; bottom: 12px;
        left: 50%; transform: translateX(-50%);
        width: 90px; height: 4px; border-radius: 5px;
        background: #dcdee9; z-index: 100;
    }
    .top {
        position: absolute; width: 115px; height: 20px;
        top: 4px; left: 50%; transform: translateX(-50%);
        background: black; border-radius: 0 0 10px 10px; z-index: 100;
    }
</style>


<div class="phone-wrapper">
    <div class="container">
        <div class="top"></div>

        <div class="screen">

            <!-- scrollable content -->
            <div class="screen-scroll">
                <div class="screen-content">
                    <div class="top-menu">
                        <i class="fas fa-signal"></i>
                        <i class="fas fa-wifi"></i>
                        <i class="fas fa-battery-three-quarters"></i>
                    </div>
                    <div class="lock-time">
                        <div class="lock" id="lockIcon"><i class="fas fa-lock"></i></div>
                        <div class="time">23:47</div>
                        <div class="date">Friday, December 31</div>
                    </div>
                    <div class="notifications-stack" id="notificationsStack"></div>
                </div>
            </div>

            <!-- backdrop dims the screen -->
            <div class="sheet-backdrop" id="sheetBackdrop"></div>

            <!-- answer sheet slides up -->
            <div class="sheet" id="answerSheet">
                <div class="sheet-handle"></div>
                <div class="sheet-title">პასუხის შეყვანა</div>
                <div class="sheet-sub">შეიყვანეთ სწორი პასუხი განსაბლოკად</div>
                <form id="answerForm">
                    <div class="sheet-input-wrap">
                        <i class="fas fa-key input-icon"></i>
                        <input class="sheet-input" type="text" id="answer" name="answer" placeholder="შეიყვანეთ პასუხი..." autocomplete="off">
                    </div>
                    <button type="submit" class="sheet-submit">დადასტურება</button>
                </form>
                <button class="sheet-cancel" id="sheetCancel">გაუქმება</button>
            </div>

        </div><!-- /screen -->

        <div class="bottom-line"></div>
    </div>
</div>

<script>
const notifications = [
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'now',        sender:'Rusty',                   message:"The Bellagio fountain show starts in 10. Don't be late." },
    { app:'CALENDAR',     icon:'fas fa-calendar-alt',iconColor:'#f39c12', time:'now',        sender:'Reminder',                message:'Meet T. at the vault entrance - 00:15' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'5 min ago',  sender:'Linus',                   message:'Elevator access code changed to 1956. Got it.' },
    { app:'WHATSAPP',     icon:'fab fa-whatsapp',    iconColor:'#25D366', time:'12 min ago', sender:'Basher',                  message:"Pinch is ready. The van's parked on Spring Mountain Rd." },
    { app:'MAIL',         icon:'fas fa-envelope',    iconColor:'#007aff', time:'23 min ago', sender:'Saul Bloom',              message:'Re: High Roller Suite reservation confirmed for Mr. Zerga' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'45 min ago', sender:'Livingston',              message:'Camera loops are set. You have exactly 3 minutes.' },
    { app:'MISSED CALL',  icon:'fas fa-phone-alt',   iconColor:'#2f9e5a', time:'1h ago',     sender:'Tess',                   message:'📞 Missed call' },
    { app:'TELEGRAM',     icon:'fab fa-telegram',    iconColor:'#0088cc', time:'1h ago',     sender:'Yen',                    message:'Practicing holding breath. 9 minutes now. 👍' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'2h ago',     sender:'Frank',                  message:'Dealer uniforms are in. Black jack tables clear at shift change.' },
    { app:'MAIL',         icon:'fas fa-envelope',    iconColor:'#007aff', time:'3h ago',     sender:'Reuben',                 message:'Re: Investment opportunity - $160M return sounds good to me' },
    { app:'WHATSAPP',     icon:'fab fa-whatsapp',    iconColor:'#25D366', time:'4h ago',     sender:'Turk & Virgil',          message:'The trucks ready. Nevada plates, like you asked.' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'5h ago',     sender:'Rusty',                  message:"Benedict doesn't suspect a thing. We're golden." },
    { app:'CALENDAR',     icon:'fas fa-calendar-alt',iconColor:'#f39c12', time:'6h ago',     sender:'Fight Night',            message:'Lennox Lewis vs. Wladimir Klitschko - MGM Grand, 9 PM' },
    { app:'MAIL',         icon:'fas fa-envelope',    iconColor:'#007aff', time:'8h ago',     sender:'Nevada Gaming Commission',message:'Your casino consultant license has been approved' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'yesterday',  sender:'Linus',                  message:"I've been practicing the watch lift. Think I'm ready." },
    { app:'TELEGRAM',     icon:'fab fa-telegram',    iconColor:'#0088cc', time:'yesterday',  sender:'Saul',                   message:'The prosthetic arm looks incredibly real. 😂' },
    { app:'REMINDERS',    icon:'fas fa-tasks',       iconColor:'#9b59b6', time:'yesterday',  sender:'To Do',                  message:'Review vault blueprints - Level B2, security rotation times' },
    { app:'MESSAGES',     icon:'fas fa-comments',    iconColor:'#34c759', time:'2 days ago', sender:'Tess',                   message:'I still think this is crazy. But... I believe in you. ❤️' },
    { app:'WHATSAPP',     icon:'fab fa-whatsapp',    iconColor:'#25D366', time:'2 days ago', sender:'Basher',                 message:'EMP device tested. Works perfectly. Total blackout.' },
    { app:'MAIL',         icon:'fas fa-envelope',    iconColor:'#007aff', time:'3 days ago', sender:'Las Vegas Parole Board', message:'Your parole has been approved. Welcome back to Nevada.' }
];

(function renderNotifications() {
    const c = document.getElementById('notificationsStack');
    notifications.forEach(n => {
        const d = document.createElement('div');
        d.className = 'alert1';
        d.innerHTML = `
            <div class="alert-header">
                <div class="alert-icon-type">
                    <div class="icon"><i class="${n.icon}" style="color:${n.iconColor}"></i></div>
                    <div class="type">${n.app}</div>
                </div>
                <div class="alert-time">${n.time}</div>
            </div>
            <div class="alert-body"><b>${n.sender}</b><br>${n.message}</div>`;
        c.appendChild(d);
    });
})();

const lockIcon    = document.getElementById('lockIcon');
const sheet       = document.getElementById('answerSheet');
const backdrop    = document.getElementById('sheetBackdrop');
const cancelBtn   = document.getElementById('sheetCancel');
const answerInput = document.getElementById('answer');

function openSheet() {
    sheet.classList.add('open');
    backdrop.classList.add('visible');
    setTimeout(() => answerInput.focus(), 420);
}
function closeSheet() {
    sheet.classList.remove('open');
    backdrop.classList.remove('visible');
    answerInput.value = '';
}

// prevent page scroll/resize on input focus (mobile keyboard fix)
answerInput.addEventListener('focus', (e) => {
    e.preventDefault();
    window.scrollTo(0, 0);
    document.body.scrollTop = 0;
});

lockIcon.addEventListener('click', openSheet);
backdrop.addEventListener('click', closeSheet);
cancelBtn.addEventListener('click', closeSheet);

// validation before native form submit
document.getElementById('answerForm').addEventListener('submit', (e) => {
    const val = answerInput.value.trim();
    if (!val) {
        e.preventDefault();
        answerInput.style.borderColor = '#ff3b30';
        setTimeout(() => answerInput.style.borderColor = '', 900);
    }
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
