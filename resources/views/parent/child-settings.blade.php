@extends('layouts.app')
@section('content')
<style>
    body { background: #f5f5f5 !important; padding: 0 !important; }
    .wrap {
        max-width: 520px; margin: 0 auto;
        padding: 36px 20px 80px;
        font-family: 'Goldman', monospace;
    }
    .back {
        font-size: 0.68rem; color: #bbb; letter-spacing: 0.08em;
        text-decoration: none; margin-bottom: 24px; display: inline-block;
        transition: color 0.2s;
    }
    .back:hover { color: #555; }
    .child-title {
        font-size: clamp(1rem, 4vw, 1.3rem);
        color: #111; letter-spacing: 0.06em; margin-bottom: 4px;
    }
    .child-sub { font-size: 0.65rem; color: #bbb; letter-spacing: 0.1em; margin-bottom: 32px; }

    .section { margin-bottom: 28px; }
    .slabel {
        font-size: 0.62rem; color: #aaa; letter-spacing: 0.14em;
        text-transform: uppercase; margin-bottom: 10px;
    }

    /* Grade selector */
    .grade-grid { display: flex; flex-wrap: wrap; gap: 7px; }
    .grade-btn {
        background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
        padding: 8px 14px; font-family: 'Goldman', monospace;
        font-size: 0.72rem; color: #999; cursor: pointer;
        transition: all 0.18s; letter-spacing: 0.04em;
    }
    .grade-btn:hover { border-color: #bbb; color: #555; }
    .grade-btn.sel { border-color: #111; color: #111; background: #f8f8f8; }

    /* Difficulty */
    .diff-row { display: flex; gap: 8px; }
    .diff-btn {
        flex: 1; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
        padding: 12px 0; font-family: 'Goldman', monospace;
        font-size: 0.85rem; color: #ccc; cursor: pointer;
        transition: all 0.18s; text-align: center;
    }
    .diff-btn:hover { border-color: #bbb; color: #555; }
    .diff-btn.sel { border-color: #111; color: #111; font-size: 1rem; }
    .diff-label {
        font-size: 0.58rem; color: #bbb; letter-spacing: 0.08em;
        text-align: center; margin-top: 4px;
    }

    /* Frequency */
    .freq-row { display: flex; gap: 8px; }
    .freq-btn {
        flex: 1; background: #fff; border: 1px solid #e0e0e0; border-radius: 6px;
        padding: 10px 0; font-family: 'Goldman', monospace;
        font-size: 0.78rem; color: #aaa; cursor: pointer;
        transition: all 0.18s; text-align: center;
    }
    .freq-btn:hover { border-color: #bbb; color: #555; }
    .freq-btn.sel { border-color: #111; color: #111; }

    /* Themes & Topics checkboxes */
    .check-grid { display: flex; flex-wrap: wrap; gap: 8px; }
    .check-item { position: relative; }
    .check-item input { position: absolute; opacity: 0; width: 0; height: 0; }
    .check-item label {
        display: flex; align-items: center; gap: 7px;
        background: #fff; border: 1px solid #e0e0e0; border-radius: 7px;
        padding: 8px 14px; font-family: 'Goldman', monospace;
        font-size: 0.72rem; color: #999; cursor: pointer;
        transition: all 0.18s; letter-spacing: 0.04em;
        user-select: none;
    }
    .check-item label:hover { border-color: #bbb; color: #555; }
    .check-item input:checked + label { border-color: #111; color: #111; background: #f8f8f8; }
    .check-hint { font-size: 0.62rem; color: #bbb; margin-top: 7px; letter-spacing: 0.06em; }

    /* Topics filtered by grade */
    .topic-grade-group { width: 100%; }
    .tgg-label { font-size: 0.6rem; color: #ddd; letter-spacing: 0.1em; text-transform: uppercase; margin: 8px 0 6px; }

    .save-btn {
        width: 100%; background: #111; border: none; border-radius: 8px;
        color: #fff; font-family: 'Goldman', monospace; font-size: 0.85rem;
        letter-spacing: 0.08em; padding: 15px; cursor: pointer;
        transition: background 0.2s; margin-top: 8px;
    }
    .save-btn:hover { background: #222; }
    .err { font-size: 0.7rem; color: #e74c3c; margin-top: -4px; margin-bottom: 8px; }
</style>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back">← dashboard</a>

    <div class="child-title">{{ $child->name }}</div>
    <div class="child-sub">პარამეტრების რედაქტირება</div>

    <form method="POST" action="{{ route('child.settings.update', $child) }}">
        @csrf @method('PUT')

        {{-- კლასი --}}
        <div class="section">
            <div class="slabel">კლასი</div>
            <div class="grade-grid" id="gradeGrid">
                @foreach($grades as $grade)
                <button type="button" class="grade-btn {{ $setting->grade_id == $grade->id ? 'sel' : '' }}"
                    onclick="selectGrade({{ $grade->id }}, this)">
                    {{ $grade->number }}
                </button>
                @endforeach
            </div>
            <input type="hidden" name="grade_id" id="gradeInput" value="{{ old('grade_id', $setting->grade_id) }}">
            @error('grade_id')<div class="err">{{ $message }}</div>@enderror
        </div>

        {{-- სირთულის დონე --}}
        <div class="section">
            <div class="slabel">სირთულის დონე</div>
            <div class="diff-row">
                @php $diffLabels = ['', 'ძალიან მარტივი', 'მარტივი', 'საშუალო', 'რთული', 'ძალიან რთული']; @endphp
                @for($i=1; $i<=5; $i++)
                <div>
                    <button type="button"
                        class="diff-btn {{ old('difficulty', $setting->difficulty) == $i ? 'sel' : '' }}"
                        onclick="setDiff({{ $i }}, this)">{{ $i }}</button>
                    <div class="diff-label">{{ $diffLabels[$i] }}</div>
                </div>
                @endfor
            </div>
            <input type="hidden" name="difficulty" id="diffInput" value="{{ old('difficulty', $setting->difficulty) }}">
        </div>

        {{-- კვირაში რამდენჯერ --}}
        <div class="section">
            <div class="slabel">ტესტი დღეში</div>
            <div class="freq-row">
                @foreach([1,2,3,4,5,6,7] as $f)
                <button type="button"
                    class="freq-btn {{ old('tests_per_week', $setting->tests_per_week) == $f ? 'sel' : '' }}"
                    onclick="setFreq({{ $f }}, this)">{{ $f }}×</button>
                @endforeach
            </div>
            <input type="hidden" name="tests_per_week" id="freqInput" value="{{ old('tests_per_week', $setting->tests_per_week) }}">
        </div>

        {{-- თემები --}}
        <div class="section">
            <div class="slabel">საყვარელი თემები</div>
            <div class="check-grid">
                @forelse($themes as $theme)
                <div class="check-item">
                    <input type="checkbox" name="theme_ids[]" id="theme_{{ $theme->id }}"
                        value="{{ $theme->id }}"
                        {{ in_array($theme->id, $selectedThemes) ? 'checked' : '' }}>
                    <label for="theme_{{ $theme->id }}">
                        <span>{{ $theme->icon }}</span> {{ $theme->name }}
                    </label>
                </div>
                @empty
                <div style="font-size:0.72rem;color:#ccc;">თემა არ არის — ადმინმა უნდა დაამატოს</div>
                @endforelse
            </div>
        </div>

        {{-- თოპიქები --}}
        <div class="section">
            <div class="slabel">თემატიკა (ცარიელი = ყველა)</div>
            <div class="check-grid" id="topicsGrid">
                @php
                    $topicsByGrade = $topics->groupBy('grade_id');
                @endphp
                @forelse($topics as $topic)
                <div class="check-item" data-grade="{{ $topic->grade_id }}">
                    <input type="checkbox" name="topic_ids[]" id="topic_{{ $topic->id }}"
                        value="{{ $topic->id }}"
                        {{ in_array($topic->id, $selectedTopics) ? 'checked' : '' }}>
                    <label for="topic_{{ $topic->id }}">
                        {{ $topic->grade->name }} / {{ $topic->name }}
                    </label>
                </div>
                @empty
                <div style="font-size:0.72rem;color:#ccc;">თოპიქი არ არის — ადმინმა უნდა დაამატოს</div>
                @endforelse
            </div>
            <div class="check-hint">არჩეული არ არის → ყველა თოპიქიდან კითხვები</div>
        </div>

        <button type="submit" class="save-btn">შენახვა</button>
    </form>
</div>

<script>
function selectGrade(id, el) {
    document.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('gradeInput').value = id;
}

function setDiff(n, el) {
    document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('diffInput').value = n;
}

function setFreq(n, el) {
    document.querySelectorAll('.freq-btn').forEach(b => b.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById('freqInput').value = n;
}
</script>
@endsection
