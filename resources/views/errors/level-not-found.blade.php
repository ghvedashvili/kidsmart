<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ლეველი არ არსებობს</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Goldman&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #080808;
            color: #636b6f;
            font-family: 'Goldman', monospace;
            height: 100dvh;
            height: 100vh;
            overflow: hidden;
        }

        .hero {
            height: 100dvh;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: -100%;
            background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
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
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
            text-align: center;
        }

        .error-row {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .error-code {
            font-size: clamp(14px, 4vw, 26px);
            color: #636b6f;
            border-right: 2px solid #2a2a2a;
            padding: 0 clamp(12px, 3vw, 20px);
            white-space: nowrap;
            letter-spacing: 0.06em;
        }

        .error-msg {
            font-size: clamp(12px, 3vw, 16px);
            padding: 0 clamp(12px, 3vw, 20px);
            letter-spacing: 0.04em;
            color: #636b6f;
        }

        .info-text {
            font-size: clamp(0.65rem, 2vw, 0.78rem);
            color: #333;
            letter-spacing: 0.06em;
            line-height: 1.8;
        }

        .info-text span {
            color: #555;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 28px;
            font-family: 'Goldman', monospace;
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            letter-spacing: 0.08em;
            color: #555;
            border: 1px solid #2a2a2a;
            border-radius: 3px;
            text-decoration: none;
            transition: color 0.2s, border-color 0.2s;
        }

        .back-btn:hover { color: #aaa; border-color: #555; }

        @media (max-width: 400px) {
            .error-row { flex-direction: column; gap: 14px; }
            .error-code { border-right: none; border-bottom: 2px solid #2a2a2a; padding: 0 0 12px; }
            .error-msg { padding: 0; }
        }
    </style>
</head>
<body>
<div class="hero">
    <div class="card">
        <div class="error-row">
            <div class="error-code">LEVEL {{ $level }}</div>
            <div class="error-msg">ეს ლეველი არ არსებობს</div>
        </div>

        <div class="info-text">
            ამ ეტაპზე სულ <span>{{ $maxLevel }}</span> ლეველია<br>
            შენ ხელმისაწვდომია <span>1 – {{ $maxLevel }}</span>
        </div>

        @auth
        <a href="{{ route('levels.show', auth()->user()->level) }}" class="back-btn">
            ← {{ auth()->user()->level }}-ე ლეველზე დაბრუნება
        </a>
        @else
        <a href="{{ url('/') }}" class="back-btn">← მთავარ გვერდზე</a>
        @endauth
    </div>
</div>
</body>
</html>
