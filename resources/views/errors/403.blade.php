<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403</title>
    <link href="https://fonts.googleapis.com/css2?family=Goldman&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100dvh; display: flex; align-items: center; justify-content: center;
            background: #080808; font-family: 'Goldman', monospace; overflow: hidden;
        }
        body::before {
            content: ''; position: fixed; inset: -100%;
            background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 28px 28px; animation: gridMove 18s linear infinite; pointer-events: none;
        }
        @keyframes gridMove { 0% { transform: translate(0,0); } 100% { transform: translate(28px,28px); } }
        .card { position: relative; z-index: 1; max-width: 400px; width: 100%; text-align: center; padding: 0 24px; }
        .icon { font-size: 2.8rem; margin-bottom: 24px; opacity: 0.6; }
        h1 { font-size: clamp(0.9rem, 4vw, 1.1rem); color: #c8c8c8; letter-spacing: 0.06em; margin-bottom: 12px; }
        p { font-size: 0.75rem; color: #444; line-height: 1.9; letter-spacing: 0.03em; margin-bottom: 36px; }
        .back-btn {
            display: inline-flex; align-items: center; gap: 10px; padding: 12px 32px;
            font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em;
            color: #888; background: transparent; border: 1px solid #2a2a2a; border-radius: 3px;
            text-decoration: none; transition: color 0.2s, border-color 0.2s;
        }
        .back-btn:hover { color: #ccc; border-color: #555; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔒</div>
        <h1>წვდომა აკრძალულია</h1>
        <p>ამ გვერდის ნახვის უფლება არ გაქვს.</p>
        @auth
        <a href="{{ route('dashboard') }}" class="back-btn">← dashboard-ზე დაბრუნება</a>
        @else
        <a href="{{ url('/') }}" class="back-btn">← მთავარ გვერდზე დაბრუნება</a>
        @endauth
    </div>
</body>
</html>
