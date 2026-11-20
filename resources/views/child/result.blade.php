<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>შედეგი</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wdth,wght@62.5..100,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Georgian', system-ui, sans-serif; background: #FFF8F1; min-height: 100vh; color: #1D1A17; }

.rs-head { position: relative; overflow: hidden; background: #F7883E; border-radius: 0 0 40px 40px; min-height: 300px; }
.rs-blob { position: absolute; border-radius: 50%; background: #FFA766; }
.rs-blob-1 { left: -60px; top: 150px; width: 260px; height: 260px; }
.rs-blob-2 { right: -30px; top: -60px; width: 220px; height: 220px; }
.rs-head-in { position: relative; max-width: 760px; margin: 0 auto; padding: 48px 24px 90px; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.rs-head-txt { display: flex; flex-direction: column; gap: 8px; }
.rs-eyebrow { font-size: 14px; font-weight: 700; color: #3D1A04; }
.rs-title { margin: 0; font-size: clamp(56px, 12vw, 84px); line-height: .9; font-weight: 900; font-stretch: 66%; text-transform: uppercase; color: #fff; max-width: 440px; overflow-wrap: anywhere; }
.rs-fox { flex-shrink: 0; margin-top: 20px; }
.rs-main { max-width: 760px; margin: 0 auto; padding: 0 20px; }
.rs-score { position: relative; margin-top: -70px; background: #fff; border-radius: 28px; padding: 20px; box-shadow: 0 10px 30px rgba(150,80,20,.15); display: flex; align-items: center; gap: 18px; }
.rs-ring { width: 96px; height: 96px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rs-ring-in { width: 76px; height: 76px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; }
.rs-ring-in span { font-size: 26px; font-weight: 900; line-height: 1; }
.rs-score-txt { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.rs-score-big { font-size: 20px; font-weight: 800; }
.rs-score-sub { font-size: 13px; color: #6B625A; }
.rs-chip { align-self: flex-start; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 4px 10px; }
.rs-won { padding-top: 18px; display: flex; flex-direction: column; gap: 12px; }
.rs-h { font-size: 17px; font-weight: 800; }
.rs-won-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
.rs-tile { border-radius: 20px; padding: 14px; display: flex; align-items: center; gap: 10px; text-decoration: none; color: #1D1A17; min-width: 0; }
a.rs-tile:hover { color: #1D1A17; filter: brightness(.98); }
.rs-tile-t { display: flex; flex-direction: column; min-width: 0; }
.rs-tile-t b { font-size: 20px; font-weight: 900; }
.rs-tile-t small { font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rs-level { border-radius: 20px; padding: 14px 16px; font-size: 14px; font-weight: 800; }
@media (max-width: 480px) { .rs-fox svg { width: 110px; height: 110px; } .rs-head-in { padding-top: 40px; } }

/* Review */
.review-wrap { max-width: 760px; margin: 0 auto; padding: 8px 20px 40px; box-sizing: border-box; }
.section-title { font-size: 1.35rem; font-weight: 800; color: #1D1A17; margin: 22px 0 12px; }
.q-review { background: #fff; border-radius: 22px; padding: 16px 18px; margin-bottom: 12px; border-left: 6px solid #EFE6DC; box-shadow: 0 2px 0 #F1E3D3; }
.q-review.correct { border-left-color: #3FAE6E; }
.q-review.wrong   { border-left-color: #E5484D; }
.q-review.skipped { border-left-color: #F5B301; }
.q-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.q-badge { font-size: .76rem; font-weight: 800; padding: 3px 11px; border-radius: 99px; color: #fff; }
.q-badge.correct { background: #1E8A4C; }
.q-badge.wrong   { background: #C62828; }
.q-badge.skipped { background: #F5B301; color: #3D2A00; }
.q-text-r { font-size: 1rem; font-weight: 700; color: #1D1A17; line-height: 1.55; margin-bottom: 8px; }
.ans-row { font-size: .9rem; font-weight: 700; }
.ans-correct { color: #1E7A45; }
.ans-wrong   { color: #B42318; }
.ans-note    { color: #6B625A; }

/* Coins + achievements banner */
.reward-bar { max-width: 760px; margin: 0 auto; padding: 16px 16px 0; display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
.coins-pill { display: inline-flex; align-items: center; gap: 8px; background: #1D1A17; color: #fff; border-radius: 99px; padding: 10px 20px; font-size: 1rem; font-weight: 900; box-shadow: 0 2px 0 #F1E3D3; }
.ach-unlocked { display: flex; flex-wrap: wrap; gap: 8px; }
.ach-pill { display: inline-flex; align-items: center; gap: 6px; background: #6D4FE0; color: #fff; border-radius: 99px; padding: 8px 16px; font-size: .84rem; font-weight: 800; animation: popIn .4s cubic-bezier(0.34,1.56,0.64,1) both; }
@keyframes popIn { from { transform: scale(.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.ach-pill:nth-child(2) { animation-delay: .1s; }
.ach-pill:nth-child(3) { animation-delay: .2s; }
.ach-pill:nth-child(4) { animation-delay: .3s; }

/* Bottom */
.bottom-btns {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: linear-gradient(to top, #FFF8F1 70%, rgba(255,248,241,0));
    padding: 20px 20px calc(16px + env(safe-area-inset-bottom));
    display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.bottom-btns .btn-retry { width: 100%; max-width: 720px; flex: none; height: 58px; border-radius: 29px; font-size: 1.1rem; }
.btn-link { height: 44px; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #B6460A; text-decoration: none; }
.btn-link:hover { color: #8A3407; text-decoration: underline; }
.btn-retry, .btn-home, .btn-hist {
    flex: 1; max-width: 300px; border-radius: 26px; height: 52px; padding: 0 16px;
    font-family: inherit; font-size: .95rem; font-weight: 800;
    cursor: pointer; border: none; transition: background .15s;
    text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;
}
.btn-retry { background: #6D4FE0; color: #fff; flex: 2; max-width: 420px; }
.btn-retry:hover { background: #5A3DD0; color: #fff; }
.btn-hist { background: #fff; color: #1D1A17; border: 2px solid #F1E3D3 !important; }
.btn-hist:hover { background: #FFF4EA; color: #1D1A17; }
.btn-home { background: #F5EFE8; color: #1D1A17; flex: 0 0 52px; padding: 0; }
.btn-home:hover { background: #EADFD3; }

</style>
</head>
<body>

@php
    $correct = $test->correct_count;
    $total   = $test->total_questions;
    $pct     = $total > 0 ? round($correct / $total * 100) : 0;
    $stars   = $pct >= 90 ? 5 : ($pct >= 70 ? 4 : ($pct >= 50 ? 3 : ($pct >= 30 ? 2 : 1)));
@endphp

@php
    $__name   = auth()->user()->name;
    $__title  = $pct >= 70 ? 'ყოჩაღ, ' . $__name . '!' : ($pct >= 50 ? 'კარგია, ' . $__name . '!' : 'კიდევ სცადე!');
    $__ring   = $pct >= 70 ? '#1E8A4C' : ($pct >= 50 ? '#F5B301' : '#E5484D');
    $__label  = $pct >= 90 ? 'გამოჩენილი შედეგი!' : ($pct >= 70 ? 'ძალიან კარგი შედეგი' : ($pct >= 50 ? 'გააგრძელე ვარჯიში' : 'ივარჯიშე და გამოგივა'));
    $__lblBg  = $pct >= 70 ? '#DFF5E3;color:#14612F' : ($pct >= 50 ? '#FFF1C2;color:#6B4A00' : '#FDE2E2;color:#A12626');
    $__newAch = $achievement_result['new_achievements'] ?? [];
    $__lvl    = $achievement_result['level_change'] ?? null;
@endphp

<header class="rs-head">
    <div class="rs-blob rs-blob-1" aria-hidden="true"></div>
    <div class="rs-blob rs-blob-2" aria-hidden="true"></div>
    <div class="rs-head-in">
        <div class="rs-head-txt">
            <span class="rs-eyebrow">{{ $test->is_olympiad ? 'ოლიმპიადა დასრულდა' : 'ტესტი დასრულდა' }}</span>
            <h1 class="rs-title">{{ $__title }}</h1>
        </div>
        <div class="rs-fox" aria-hidden="true">@include('partials.ks-fox', ['size' => 150, 'color' => '#E2590F', 'smile' => true])</div>
    </div>
</header>

<main class="rs-main">
    <section class="rs-score" aria-label="შედეგი">
        <div class="rs-ring" style="background: conic-gradient({{ $__ring }} 0 {{ $pct }}%, #EFE3D6 {{ $pct }}% 100%);">
            <div class="rs-ring-in"><span>{{ $pct }}%</span></div>
        </div>
        <div class="rs-score-txt">
            <span class="rs-score-big">{{ $correct }} / {{ $total }} სწორი</span>
            <span class="rs-score-sub">
                {{ str_repeat('⭐', $stars) }}
                @if($test->theme) · {{ $test->theme->icon }} {{ $test->theme->name }} @endif
            </span>
            <span class="rs-chip" style="background:{{ $__lblBg }};">{{ $__label }}</span>
        </div>
    </section>

    @if($achievement_result)
    <section class="rs-won">
        <span class="rs-h">შენ მოიგე</span>
        <div class="rs-won-grid">
            <div class="rs-tile" style="background:#FFF1C2;">
                <svg width="34" height="34" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#F5B301"/><circle cx="12" cy="12" r="6.5" fill="none" stroke="#FFE08A" stroke-width="2"/></svg>
                <span class="rs-tile-t"><b>+{{ $achievement_result['coins'] }}</b><small style="color:#6B4A00;">მონეტა · სულ {{ $achievement_result['total_coins'] }}</small></span>
            </div>
            @if(count($__newAch))
            <a href="{{ route('achievements') }}" class="rs-tile" style="background:#EEE8FF;">
                @if($__newAch[0]['image'])
                <img src="{{ $__newAch[0]['image'] }}" alt="" style="width:34px;height:34px;object-fit:contain;">
                @else
                <span style="font-size:28px;line-height:1;">🏅</span>
                @endif
                <span class="rs-tile-t"><b style="font-size:14px;line-height:1.25;">{{ count($__newAch) > 1 ? count($__newAch) . ' ახალი მედალი' : 'ახალი მედალი' }}</b><small style="color:#4A3A99;">{{ $__newAch[0]['name'] }}</small></span>
            </a>
            @else
            <a href="{{ route('achievements') }}" class="rs-tile" style="background:#EEE8FF;">
                <span style="font-size:28px;line-height:1;">🏅</span>
                <span class="rs-tile-t"><b style="font-size:14px;line-height:1.25;">ჩემი მედლები</b><small style="color:#4A3A99;">ნახე ყველა →</small></span>
            </a>
            @endif
        </div>
        @if(count($__newAch) > 1)
        <div class="ach-unlocked">
            @foreach(array_slice($__newAch, 1) as $ach)
            <span class="ach-pill">
                @if($ach['image'])<img src="{{ $ach['image'] }}" style="width:18px;height:18px;object-fit:contain;" alt="">@else 🏅 @endif
                {{ $ach['name'] }}
            </span>
            @endforeach
        </div>
        @endif
        @if($__lvl === 'up')
        <div class="rs-level" style="background:#DFF5E3;color:#14612F;">🚀 დონე აიწია! ახლა დონე {{ $achievement_result['difficulty'] }}-ზე ხარ</div>
        @elseif($__lvl === 'down')
        <div class="rs-level" style="background:#FFF1C2;color:#6B4A00;">💪 დონე შეიცვალა {{ $achievement_result['difficulty'] }}-ზე — ივარჯიშე და დაბრუნდები!</div>
        @endif
    </section>
    @endif
</main>

<div style="height:150px;"></div>
<div class="bottom-btns">
    <a href="{{ route('dashboard') }}" class="btn-retry">მთავარზე დაბრუნება <i class="bi bi-arrow-right"></i></a>
    <a href="{{ route('my.test.show', $test) }}" class="btn-link">შეცდომების ნახვა</a>
</div>

</body>
</html>
