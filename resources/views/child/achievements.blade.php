<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ჩემი მიღწევები</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Nunito', sans-serif; background: #f1f5f9; min-height: 100vh; }

/* Header */
.hero {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    color: white;
    padding: 36px 20px 28px;
    text-align: center;
}
.hero-name { font-size: 0.8rem; font-weight: 700; opacity: 0.7; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px; }
.hero-title { font-size: 1.6rem; font-weight: 900; margin-bottom: 18px; }

.stats-row {
    display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;
}
.stat-box {
    background: rgba(255,255,255,0.15);
    border-radius: 14px;
    padding: 12px 22px;
    text-align: center;
    backdrop-filter: blur(4px);
    min-width: 90px;
}
.stat-val { font-size: 1.7rem; font-weight: 900; line-height: 1; }
.stat-lbl { font-size: 0.68rem; font-weight: 700; opacity: 0.75; margin-top: 3px; }

/* Back button */
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    margin: 18px 20px 0;
    font-family: 'Nunito', sans-serif;
    font-size: 0.8rem; font-weight: 700;
    color: #6366f1; text-decoration: none;
    background: white; border-radius: 10px;
    padding: 8px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

/* Section */
.section { padding: 20px 16px 0; max-width: 600px; margin: 0 auto; }
.sec-title {
    font-size: 0.72rem; font-weight: 800; letter-spacing: 0.12em;
    text-transform: uppercase; color: #64748b;
    margin-bottom: 12px; padding-left: 2px;
}

/* Achievement grid */
.ach-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 10px;
    margin-bottom: 28px;
}

.ach-card {
    background: white;
    border-radius: 16px;
    padding: 16px 10px 14px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    position: relative;
    transition: transform 0.15s;
}
.ach-card.earned {
    box-shadow: 0 2px 12px rgba(99,102,241,0.18);
    border: 2px solid #e0e7ff;
}
.ach-card.locked {
    opacity: 0.45;
    filter: grayscale(0.6);
}
.ach-card.earned:hover { transform: translateY(-2px); }

.ach-emoji { font-size: 2.4rem; line-height: 1; margin-bottom: 8px; }
.ach-name  { font-size: 0.72rem; font-weight: 800; color: #1e293b; line-height: 1.3; }
.ach-desc  { font-size: 0.62rem; font-weight: 600; color: #94a3b8; margin-top: 4px; line-height: 1.4; }
.ach-date  { font-size: 0.58rem; color: #a5b4fc; font-weight: 700; margin-top: 6px; }

.earned-badge {
    position: absolute; top: 8px; right: 8px;
    width: 16px; height: 16px;
    background: #6366f1; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.55rem; color: white; font-weight: 900;
}

/* Progress bar */
.progress-wrap { margin-bottom: 28px; }
.progress-info { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.72rem; font-weight: 700; color: #64748b; }
.progress-bar { height: 8px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: 99px; transition: width 0.6s ease; }

.bottom-pad { height: 40px; }
</style>
</head>
<body>

<div class="hero">
    <div class="hero-name">{{ auth()->user()->name }}</div>
    <div class="hero-title">🏆 ჩემი მიღწევები</div>
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-val">💰{{ $setting?->coins ?? 0 }}</div>
            <div class="stat-lbl">მონეტა</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">{{ $totalTests }}</div>
            <div class="stat-lbl">ტესტი</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">{{ $earned->count() }}/{{ count($achievements) }}</div>
            <div class="stat-lbl">მიღწეული</div>
        </div>
        <div class="stat-box">
            <div class="stat-val">⚡{{ $setting?->difficulty ?? 1 }}</div>
            <div class="stat-lbl">სირთულე</div>
        </div>
    </div>
</div>

<a href="{{ route('dashboard') }}" class="back-btn">← დაბრუნება</a>

<div class="section">
    <!-- Progress -->
    <div class="progress-wrap">
        @php $pct = count($achievements) > 0 ? round($earned->count() / count($achievements) * 100) : 0; @endphp
        <div class="progress-info">
            <span>პროგრესი</span>
            <span>{{ $earned->count() }} / {{ count($achievements) }}</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <!-- Stickers -->
    @php $stickers = array_filter($achievements, fn($a) => $a['type'] === 'sticker'); @endphp
    <div class="sec-title">⭐ სტიკერები</div>
    <div class="ach-grid">
        @foreach($stickers as $slug => $ach)
        @php $isEarned = isset($earned[$slug]); @endphp
        <div class="ach-card {{ $isEarned ? 'earned' : 'locked' }}">
            @if($isEarned)<div class="earned-badge">✓</div>@endif
            <div class="ach-emoji">{{ $ach['emoji'] }}</div>
            <div class="ach-name">{{ $ach['name'] }}</div>
            <div class="ach-desc">{{ $isEarned ? $ach['desc'] : '???' }}</div>
            @if($isEarned)
            <div class="ach-date">{{ $earned[$slug]->format('d.m.Y') }}</div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Medals -->
    @php $medals = array_filter($achievements, fn($a) => $a['type'] === 'medal'); @endphp
    <div class="sec-title">🏅 მედლები</div>
    <div class="ach-grid">
        @foreach($medals as $slug => $ach)
        @php $isEarned = isset($earned[$slug]); @endphp
        <div class="ach-card {{ $isEarned ? 'earned' : 'locked' }}">
            @if($isEarned)<div class="earned-badge">✓</div>@endif
            <div class="ach-emoji">{{ $ach['emoji'] }}</div>
            <div class="ach-name">{{ $ach['name'] }}</div>
            <div class="ach-desc">{{ $isEarned ? $ach['desc'] : '???' }}</div>
            @if($isEarned)
            <div class="ach-date">{{ $earned[$slug]->format('d.m.Y') }}</div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<div class="bottom-pad"></div>
</body>
</html>
