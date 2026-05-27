<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>გახსენი ბრაუზერში</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Goldman&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #080808;
            font-family: 'Goldman', monospace;
            padding: 24px;
        }

        body::before {
            content: '';
            position: fixed;
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

        .card {
            position: relative;
            z-index: 1;
            max-width: 360px;
            width: 100%;
            text-align: center;
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        h1 {
            font-size: clamp(1rem, 4vw, 1.3rem);
            color: #c8c8c8;
            margin-bottom: 12px;
            letter-spacing: 0.04em;
        }

        p {
            font-size: clamp(0.75rem, 3vw, 0.85rem);
            color: #555;
            line-height: 1.8;
            margin-bottom: 28px;
            letter-spacing: 0.03em;
        }

        .url-box {
            background: #111;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 0.8rem;
            color: #888;
            letter-spacing: 0.05em;
            margin-bottom: 28px;
            word-break: break-all;
        }

        .steps {
            text-align: left;
            background: #0d0d0d;
            border: 1px solid #222;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 8px;
        }

        .steps p {
            margin-bottom: 0;
            color: #444;
            font-size: 0.75rem;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            color: #666;
            font-size: clamp(0.7rem, 2.5vw, 0.78rem);
            line-height: 1.6;
        }

        .step:last-child { margin-bottom: 0; }

        .step-num {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #1a1a1a;
            border: 1px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            color: #555;
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🌐</div>
        <h1>გახსენი ბრაუზერში</h1>
        <p>
            Google-ს შესვლა არ მუშაობს<br>
            Messenger / Instagram / TikTok-ის<br>
            შიდა ბრაუზერიდან.
        </p>

        <div class="url-box" id="urlBox" onclick="copyUrl()" style="cursor:pointer;display:flex;align-items:center;gap:10px;justify-content:space-between;">
            <span style="word-break:break-all;flex:1;">https://veravart.laravel.cloud/levels/4/complete?t={{ env('LEVEL4_TOKEN') }}</span>
            <span id="copyIcon" style="flex-shrink:0;font-size:1rem;opacity:0.5;transition:opacity .15s;">📋</span>
        </div>
        <div id="copyToast" style="opacity:0;transition:opacity .2s;font-size:0.72rem;color:#2ecc71;margin-top:6px;margin-bottom:14px;letter-spacing:0.03em;">✓ დაკოპირდა</div>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <span>დააჭირე ზემოთ მოცემულ ბმულს — დაკოპირდება</span>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <span>გახსენი <strong style="color:#666;">Safari</strong> ან <strong style="color:#666;">Chrome</strong></span>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <span>ჩასვი მისამართი და გახსენი</span>
            </div>
        </div>

        <script>
        function copyUrl() {
            const text = document.querySelector('#urlBox span').textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const icon  = document.getElementById('copyIcon');
                const toast = document.getElementById('copyToast');
                const box   = document.getElementById('urlBox');

                icon.textContent = '✓';
                icon.style.opacity = '1';
                icon.style.color = '#2ecc71';
                box.style.borderColor = '#2ecc71';

                toast.style.opacity = '1';

                setTimeout(() => {
                    icon.textContent = '📋';
                    icon.style.opacity = '0.5';
                    icon.style.color = '';
                    box.style.borderColor = '';
                    toast.style.opacity = '0';
                }, 2000);
            });
        }
        </script>
    </div>
</body>
</html>
