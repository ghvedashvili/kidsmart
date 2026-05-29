@if($userLevel == $level)

<style>
    nav.navbar { display: none !important; }

    body {
        margin: 0;
        padding: 0 !important;
        overflow: hidden;
        background: #080808 !important;
        height: 100dvh;
        height: 100vh;
    }

    body.dot-light::before { display: none; }

    .level4-hero {
        height: 100dvh;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .level4-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }

    @keyframes gridMove {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(28px, 28px); }
    }

    .level4-card {
        position: relative;
        z-index: 1;
        max-width: 480px;
        width: 100%;
        padding: 0 24px;
        font-family: 'Goldman', monospace;
    }

    .level4-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        flex-wrap: nowrap;
    }

    .level4-code {
        font-size: clamp(0.85rem, 3vw, 1.05rem);
        color: #555;
        letter-spacing: 0.08em;
        padding-right: 20px;
        border-right: 1px solid #2a2a2a;
        white-space: nowrap;
    }

    .level4-info {
        padding-left: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .level4-type {
        font-size: clamp(0.72rem, 2.2vw, 0.88rem);
        color: #4a4a4a;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .level4-severity {
        font-size: clamp(0.65rem, 2vw, 0.78rem);
        color: #333;
        letter-spacing: 0.05em;
    }

    .level4-severity span {
        font-size: 1em;
        opacity: 0.8;
    }

    .level4-initiator {
        margin-top: 20px;
        text-align: center;
        font-size: clamp(0.65rem, 2vw, 0.75rem);
        color: #333;
        letter-spacing: 0.06em;
    }
</style>

<div class="level4-hero">
    <div class="level4-card">
        <div class="level4-row">
            <div class="level4-code">ERROR 004</div>
            <div class="level4-info">
                <div class="level4-type">Type: user not recognized</div>
                <div class="level4-severity">
                    Severity: not fol <span>(low error)</span>
                </div>
            </div>
        </div>
        <div class="level4-initiator">iniciator: @veravart_game</div>
    </div>
</div>

@if($completed)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Correct',
    text: 'Truth matters.',
    confirmButtonText: 'Next Level'
}).then(() => {
    window.location.href = '/levels/{{ $userLevel }}';
});
</script>
@endif

@else

@include('levels.levelcomplete', ['level' => $level, 'userLevel' => auth()->user()->level])

@endif
