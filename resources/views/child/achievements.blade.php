@extends('layouts.app')
@push('head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
@endpush
@section('content')
<style>
body { font-family: 'Nunito', sans-serif; background: transparent !important; }
.wrap { max-width: 520px; margin: 0 auto; padding: 28px 16px 80px; }
@media (min-width: 760px)  { .wrap { max-width: 700px; } .ach-grid { grid-template-columns: repeat(5, 1fr); } }
@media (min-width: 1040px) { .wrap { max-width: 960px; } .ach-grid { grid-template-columns: repeat(6, 1fr); } }

.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem; font-weight: 800;
    color: #4f46e5; text-decoration: none;
    background: white; border-radius: 99px;
    padding: 8px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.page-hero {
    width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
    min-height: 140px; display: flex; flex-direction: column; justify-content: center;
    position: relative; overflow: hidden; margin: 16px 0 0;
    background-image:
        linear-gradient(90deg, rgba(245,243,255,0.94) 0%, rgba(245,243,255,0.78) 45%, rgba(245,243,255,0.08) 68%),
        url('/img/achievements-hero.jpg');
    background-size: cover; background-position: right center; background-repeat: no-repeat;
    box-shadow: 0 8px 20px rgba(79,70,229,0.18);
}
.page-hero-title { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.1rem; color: #4c1d95; margin-bottom: 4px; }
.page-hero-sub { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.75rem; color: #6d28d9; }

.stats-row {
    display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;
    margin: 12px 0 0;
}
.stat-box {
    background: white; border-radius: 14px;
    padding: 12px 20px; text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    min-width: 80px;
}
.stat-val { font-size: 1.4rem; font-weight: 900; line-height: 1; color: #1e293b; }
.stat-lbl { font-size: 0.62rem; font-weight: 700; color: #94a3b8; margin-top: 4px; }

/* Section */
.section { padding: 20px 0 0; }
.sec-title {
    font-size: 0.72rem; font-weight: 800; letter-spacing: 0.12em;
    text-transform: uppercase; color: #64748b;
    margin-bottom: 12px; padding-left: 2px;
}

/* Achievement grid */
.ach-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
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
</style>

<div class="wrap">
<a href="{{ route('dashboard') }}" class="back-btn">← დაბრუნება</a>

<div class="page-hero">
    <div class="page-hero-title">🏆 ჩემი მედლები</div>
    <div class="page-hero-sub">{{ $earned->count() }}/{{ count($achievements) }} მედალი მოგებული — გააგრძელე!</div>
</div>

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
</div>

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

    <!-- Market rewards -->
    @if($marketRewards->count())
    <div class="sec-title" style="color:#d97706;">🛒 მარკეტის ჯილდოები</div>
    <div class="ach-grid" style="margin-bottom:28px;">
        @foreach($marketRewards as $reward)
        <div class="ach-card earned" style="border-color:#fde68a;box-shadow:0 2px 12px rgba(245,158,11,0.15);">
            <div class="earned-badge" style="background:#f59e0b;">✓</div>
            <div class="ach-emoji">{{ $reward->item->icon }}</div>
            <div class="ach-name">{{ $reward->item->title }}</div>
            <div class="ach-desc" style="color:#f59e0b;">💰 {{ $reward->coins_spent }} მონეტა</div>
            <div class="ach-date" style="color:#f59e0b;">{{ $reward->updated_at->format('d.m.Y') }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Stickers -->
    @php $stickers = array_filter($achievements, fn($a) => $a['type'] === 'sticker'); @endphp
    <div class="sec-title">⭐ სტიკერები</div>
    <div style="font-family:'Nunito',sans-serif;font-size:0.7rem;font-weight:700;color:#94a3b8;margin-bottom:12px;padding-left:2px;">
        📌 დღეში მხოლოდ 1 სტიკერის გახსნა შეიძლება
    </div>
    <div class="ach-grid">
        @foreach($stickers as $slug => $ach)
        @php $isEarned = isset($earned[$slug]); @endphp
        <div class="ach-card {{ $isEarned ? 'earned' : 'locked' }}">
            @if($isEarned)<div class="earned-badge">✓</div>@endif
            <div class="ach-emoji">{{ $ach['emoji'] }}</div>
            <div class="ach-name">{{ $ach['name'] }}</div>
            <div class="ach-desc">{{ $ach['desc'] }}</div>
            @if($isEarned)
            <div class="ach-date">{{ $earned[$slug]->format('d.m.Y') }}</div>
            @endif
        </div>
        @endforeach
    </div>

</div>
</div>
@endsection
