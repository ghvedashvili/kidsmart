<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ჩაკეტილი ლეველი</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Goldman&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #080808;
            font-family: 'Goldman', monospace;
            overflow: hidden;
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
            max-width: 400px;
            width: 100%;
            text-align: center;
            padding: 0 24px;
        }

        .lock-icon {
            font-size: 2.8rem;
            margin-bottom: 24px;
            opacity: 0.7;
        }

        h1 {
            font-size: clamp(0.9rem, 4vw, 1.1rem);
            color: #c8c8c8;
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }

        p {
            font-size: clamp(0.7rem, 3vw, 0.78rem);
            color: #444;
            line-height: 1.9;
            letter-spacing: 0.03em;
            margin-bottom: 36px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 32px;
            font-family: 'Goldman', monospace;
            font-size: clamp(0.7rem, 2.5vw, 0.8rem);
            letter-spacing: 0.08em;
            color: #888;
            background: transparent;
            border: 1px solid #2a2a2a;
            border-radius: 3px;
            text-decoration: none;
            transition: color 0.2s, border-color 0.2s;
        }
        .back-btn:hover {
            color: #ccc;
            border-color: #555;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="lock-icon">🔒</div>
        <h1>ეს ლეველი ჩაკეტილია</h1>
        <p>
            ჯერ წინა ტურები უნდა გაიარო.<br>
            მიმდინარე ლეველი გელოდება.
        </p>
        @auth
        <a href="{{ route('levels.show', auth()->user()->level) }}" class="back-btn">
            ← {{ auth()->user()->level }}-ე ლეველზე დაბრუნება
        </a>
        @else
        <a href="{{ url('/') }}" class="back-btn">
            ← მთავარ გვერდზე დაბრუნება
        </a>
        @endauth
    </div>
</body>
</html>
