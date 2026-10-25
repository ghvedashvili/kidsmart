{{--
    Reusable "achievement unlocked" toast — call window.showAchievementToasts(arr)
    with an AJAX response's `new_achievements` array (shape: [{slug, name, desc,
    image, tier}, ...], see AchievementService::checkAchievements()) from any
    page that doesn't do a full reload after the action that might unlock one
    (practice, games) — child/result.blade.php shows the same achievements
    server-rendered instead, since that page already reloads.
--}}
<style>
    .ach-toast-wrap {
        position: fixed; top: 14px; left: 50%; transform: translateX(-50%);
        z-index: 99999; display: flex; flex-direction: column; gap: 8px;
        align-items: center; pointer-events: none;
    }
    .ach-toast-pill {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; border-radius: 99px;
        padding: 10px 18px; font-size: 0.82rem; font-weight: 800;
        box-shadow: 0 6px 20px rgba(99,102,241,0.4);
        animation: achToastIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
        white-space: nowrap;
    }
    .ach-toast-pill.out { animation: achToastOut 0.3s ease both; }
    .ach-toast-icon { width: 22px; height: 22px; object-fit: contain; flex-shrink: 0; }
    @keyframes achToastIn {
        from { transform: scale(0.5) translateY(-10px); opacity: 0; }
        to   { transform: scale(1)   translateY(0);      opacity: 1; }
    }
    @keyframes achToastOut {
        from { transform: scale(1);   opacity: 1; }
        to   { transform: scale(0.8); opacity: 0; }
    }
</style>
<div class="ach-toast-wrap" id="achToastWrap"></div>
<script>
function showAchievementToasts(achievements) {
    if (!achievements || !achievements.length) return;
    const wrap = document.getElementById('achToastWrap');
    if (!wrap) return;
    achievements.forEach((ach, i) => {
        setTimeout(() => {
            const pill = document.createElement('div');
            pill.className = 'ach-toast-pill';
            const icon = ach.image
                ? `<img class="ach-toast-icon" src="${ach.image}" alt="">`
                : '🏅';
            pill.innerHTML = `${icon} <span>${ach.name}</span>`;
            wrap.appendChild(pill);
            setTimeout(() => {
                pill.classList.add('out');
                setTimeout(() => pill.remove(), 300);
            }, 3200);
        }, i * 250);
    });
}
</script>
