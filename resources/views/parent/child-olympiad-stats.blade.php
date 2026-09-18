@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .wrap {
        max-width: 520px; margin: 0 auto;
        padding: 28px 20px 80px;
        font-family: 'Goldman', monospace;
    }
    @media (min-width: 760px)  { .wrap { max-width: 700px; } }
    @media (min-width: 1040px) { .wrap { max-width: 960px; } }

    .page-hero {
        width: 100%; box-sizing: border-box; border-radius: 20px; padding: 26px 20px;
        min-height: 120px; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden; margin-bottom: 24px;
        background-image:
            linear-gradient(90deg, rgba(255,251,235,0.94) 0%, rgba(255,251,235,0.78) 45%, rgba(255,251,235,0.08) 68%),
            url('/img/achievements-hero.jpg');
        background-size: cover; background-position: right center; background-repeat: no-repeat;
        box-shadow: 0 8px 20px rgba(217,119,6,0.18);
    }
    .page-hero-title { font-family:'Goldman', monospace; font-size:1.05rem; color:#92400e; margin-bottom:4px; letter-spacing: 0.04em; }
    .page-hero-sub { font-family:'Goldman', monospace; font-size:0.65rem; color:#b45309; letter-spacing: 0.08em; }

    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 32px; }
    .stat-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 16px 12px; text-align: center;
    }
    .stat-val { font-size: clamp(1.4rem, 5vw, 1.9rem); color: #111; letter-spacing: 0.04em; line-height: 1; }
    .stat-label { font-size: 0.58rem; color: #bbb; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 6px; }

    .section-label { font-size: 0.62rem; color: #aaa; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; }

    .test-row {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 14px 16px; margin-bottom: 8px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; transition: border-color 0.2s;
    }
    .test-row:hover { border-color: #bbb; }
    .test-icon { font-size: 1.4rem; flex-shrink: 0; }
    .test-info { flex: 1; min-width: 0; }
    .test-date { font-size: 0.62rem; color: #bbb; letter-spacing: 0.06em; margin-bottom: 3px; }
    .test-score { font-size: 0.88rem; color: #111; letter-spacing: 0.04em; }
    .test-pct {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;
        padding: 3px 10px; border-radius: 99px; flex-shrink: 0;
    }
    .pct-hi { background: #dcfce7; color: #16a34a; }
    .pct-mid { background: #fef9c3; color: #ca8a04; }
    .pct-lo { background: #fee2e2; color: #dc2626; }

    .empty { text-align: center; padding: 40px 20px; color: #ccc; font-size: 0.72rem; letter-spacing: 0.08em; }

    .collapse-card { background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; margin-bottom: 20px; overflow: hidden; }
    .collapse-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; cursor: pointer; user-select: none; transition: background 0.15s; }
    .collapse-header:hover { background: #fafafa; }
    .collapse-title { font-size: 0.62rem; color: #aaa; letter-spacing: 0.14em; text-transform: uppercase; }
    .collapse-arrow { font-size: 0.68rem; color: #ccc; transition: transform 0.2s; flex-shrink: 0; }
    .collapse-card.open .collapse-arrow { transform: rotate(90deg); }
    .collapse-body { display: none; padding: 0 12px 12px; }
    .collapse-card.open .collapse-body { display: block; }
    .collapse-body .test-row:last-child { margin-bottom: 0; }

    .old-grades-hr { border: none; border-top: 1px solid #e8e8e8; margin: 28px 0 20px; }
</style>

<div class="wrap">
    <div class="page-hero">
        <div class="page-hero-title">🏆 {{ $child->name }}-ის ოლიმპიადა</div>
        <div class="page-hero-sub">ოლიმპიადის ტესტების ისტორია და შედეგები</div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val">{{ $totalOlympiad }}</div>
            <div class="stat-label">ოლიმპიადა სულ</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $avgScore !== null ? $avgScore . '%' : '—' }}</div>
            <div class="stat-label">საშ. შედეგი</div>
        </div>
    </div>

    <div class="collapse-card open" id="secOlympiad">
        <div class="collapse-header" onclick="toggleSection('secOlympiad')">
            <span class="collapse-title">ოლიმპიადის ისტორია · {{ $totalOlympiad }}</span>
            <span class="collapse-arrow">▶</span>
        </div>
        <div class="collapse-body">
            @forelse($olympiadTests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="test-row">
                <div class="test-icon">🏆</div>
                <div class="test-info">
                    <div class="test-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</div>
                    <div class="test-score">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</div>
                </div>
                <div class="test-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">
                    {{ $pct }}%
                </div>
            </a>
            @empty
            <div class="empty">ოლიმპიადაზე ჯერ არ დაწერილა</div>
            @endforelse
        </div>
    </div>

    @if($oldGrades->isNotEmpty())
    <hr class="old-grades-hr">
    <div class="section-label">ძველი კლასები</div>

    @foreach($oldGrades as $grade)
    @php $gOlyTests = $oldGradeOlympiadTests->get($grade->id, collect()); @endphp
    <div class="collapse-card" id="secOldGrade{{ $grade->id }}">
        <div class="collapse-header" onclick="toggleSection('secOldGrade{{ $grade->id }}')">
            <span class="collapse-title">{{ $grade->name }} · {{ $gOlyTests->count() }}</span>
            <span class="collapse-arrow">▶</span>
        </div>
        <div class="collapse-body">
            @forelse($gOlyTests as $test)
            @php $pct = round($test->correct_count / max($test->total_questions, 1) * 100); @endphp
            <a href="{{ route('child.test.show', [$child, $test]) }}" class="test-row">
                <div class="test-icon">🏆</div>
                <div class="test-info">
                    <div class="test-date">{{ $test->completed_at->format('d.m.Y · H:i') }}</div>
                    <div class="test-score">{{ $test->correct_count }} / {{ $test->total_questions }} სწორი</div>
                </div>
                <div class="test-pct {{ $pct >= 80 ? 'pct-hi' : ($pct >= 50 ? 'pct-mid' : 'pct-lo') }}">
                    {{ $pct }}%
                </div>
            </a>
            @empty
            <div class="empty">ამ კლასში ოლიმპიადაზე არ დაწერილა</div>
            @endforelse
        </div>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleSection(id) {
    document.getElementById(id).classList.toggle('open');
}
</script>
@endsection
