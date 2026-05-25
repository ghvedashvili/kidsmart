<style>
    .levelcomplete-wrap {
        min-height: calc(100vh - 56px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }

    .levelcomplete-badge {
        font-size: 5rem;
        line-height: 1;
        margin-bottom: 16px;
        animation: badgePop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    .levelcomplete-title {
        font-size: clamp(1.4rem, 5vw, 2.2rem);
        font-weight: 700;
        color: #111;
        margin-bottom: 8px;
        animation: fadeUp 0.5s 0.15s ease both;
    }

    .levelcomplete-sub {
        font-size: clamp(0.85rem, 2.5vw, 1rem);
        color: #666;
        margin-bottom: 32px;
        animation: fadeUp 0.5s 0.25s ease both;
    }

    .levelcomplete-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 36px;
        background: #111;
        color: #f5f5f5;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
        animation: fadeUp 0.5s 0.35s ease both;
    }

    .levelcomplete-btn:hover {
        background: #333;
        color: #fff;
    }

    @keyframes badgePop {
        0%   { transform: scale(0) rotate(-15deg); opacity: 0; }
        100% { transform: scale(1) rotate(0deg);   opacity: 1; }
    }

    @keyframes fadeUp {
        0%   { transform: translateY(20px); opacity: 0; }
        100% { transform: translateY(0);    opacity: 1; }
    }

    /* confetti */
    .confetti-piece {
        position: fixed;
        width: 10px;
        height: 10px;
        top: -10px;
        border-radius: 2px;
        animation: confettiFall linear forwards;
        pointer-events: none;
        z-index: 9999;
    }

    @keyframes confettiFall {
        0%   { transform: translateY(0)      rotate(0deg);   opacity: 1; }
        80%  { opacity: 1; }
        100% { transform: translateY(110vh)  rotate(720deg); opacity: 0; }
    }
</style>

<div class="levelcomplete-wrap">
    <div class="levelcomplete-badge">🏆</div>
    <div class="levelcomplete-title">Level {{ $level }} — გავლილია!</div>
    <div class="levelcomplete-sub">
        @if($userLevel > $level)
            {{ $question->success_message ?? '' }}
        @endif
    </div>
    <a href="{{ route('levels.show', ['level' => $userLevel]) }}"
       class="levelcomplete-btn"
       data-loader
       data-loader-text="იტვირთება...">
        შემდეგი ეტაპი →
    </a>
</div>

<script>
(function spawnConfetti() {
    const colors = ['#f94144','#f3722c','#f8961e','#f9c74f','#90be6d','#43aa8b','#577590','#a855f7','#ec4899'];
    const shapes = ['square','circle','strip'];
    const total  = 120;

    for (let i = 0; i < total; i++) {
        setTimeout(() => {
            const el = document.createElement('div');
            el.className = 'confetti-piece';

            const color = colors[Math.floor(Math.random() * colors.length)];
            const shape = shapes[Math.floor(Math.random() * shapes.length)];
            const left  = Math.random() * 100;
            const dur   = 2.5 + Math.random() * 2.5;
            const size  = 7 + Math.random() * 9;
            const drift = (Math.random() - 0.5) * 180;

            el.style.cssText = `
                left: ${left}vw;
                width: ${shape === 'strip' ? size * 0.4 : size}px;
                height: ${shape === 'strip' ? size * 2.5 : size}px;
                background: ${color};
                border-radius: ${shape === 'circle' ? '50%' : '2px'};
                animation-duration: ${dur}s;
                animation-delay: ${Math.random() * 1.2}s;
                transform-origin: center;
            `;

            el.animate([
                { transform: `translateY(0) translateX(0) rotate(0deg)`,       opacity: 1 },
                { transform: `translateY(110vh) translateX(${drift}px) rotate(${360 + Math.random()*360}deg)`, opacity: 0 }
            ], {
                duration: dur * 1000,
                delay: Math.random() * 1200,
                easing: 'linear',
                fill: 'forwards'
            });

            document.body.appendChild(el);
            setTimeout(() => el.remove(), (dur + 1.5) * 1000);
        }, 0);
    }
})();
</script>
