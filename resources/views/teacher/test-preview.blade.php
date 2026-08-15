@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 700px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-filter { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 18px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-dark   { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }

    /* pill button groups */
    .filter-group { margin-bottom: 10px; }
    .filter-lbl { font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 5px; }
    .pill-row { display: flex; flex-wrap: wrap; gap: 5px; }
    .pill-btn { position: relative; }
    .pill-btn input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .pill-btn span {
        display: block;
        font-family: 'Goldman', monospace;
        font-size: 0.72rem;
        padding: 4px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        color: #64748b;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.14s;
        user-select: none;
        white-space: nowrap;
    }
    .pill-btn:has(input:checked) span { background: #1e293b; border-color: #1e293b; color: #fff; }
    .pill-btn span:hover { border-color: #94a3b8; color: #1e293b; }

    /* dropdowns + generate row */
    .dd-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
    .fc-sm { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 5px 10px; outline: none; cursor: pointer; }
    .fc-sm:focus { border-color: #94a3b8; }
    .btn-gen { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 5px 18px; border-radius: 4px; cursor: pointer; transition: all 0.15s; white-space: nowrap; margin-left: auto; }
    .btn-gen:hover { border-color: #94a3b8; color: #1e293b; }

    /* question cards */
    .q-card { background: #fafcff; border: 1px solid #e8eef6; border-radius: 7px; padding: 16px 18px; margin-bottom: 12px; }
    .q-num { font-size: 0.62rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 6px; }
    .q-topic { display: inline-block; font-size: 0.6rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 6px; margin-left: 6px; }
    .q-text { font-size: 0.86rem; color: #1e293b; line-height: 1.5; margin-bottom: 12px; }
    .opts { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 8px; }
    .opt { font-size: 0.78rem; padding: 6px 14px; border-radius: 4px; border: 1px solid #e2e8f0; color: #374151; background: #fff; }
    .opt.correct { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; font-weight: 600; }
    .hint { font-size: 0.7rem; color: #64748b; border-left: 2px solid #e2e8f0; padding-left: 9px; margin-top: 2px; }
    .err { font-size: 0.76rem; color: #ef4444; padding: 10px 0; }
    .result-meta { font-family:'Goldman',monospace; font-size:0.66rem; color:#94a3b8; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:12px; }
    .theme-tag { color:#374151; }

    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .card-filter { padding: 12px 14px; }
        .card-dark { padding: 14px; }
        .btn-gen { margin-left: 0; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:20px;">← back</a>

    <div class="card-filter">
        <form method="GET" action="{{ route('test.preview') }}">
            <div class="filter-group">
                <div class="filter-lbl">კლასი</div>
                <div class="pill-row">
                    @foreach($grades as $g)
                    <label class="pill-btn">
                        <input type="radio" name="grade_id" value="{{ $g->id }}"
                               {{ $selectedGradeId == $g->id ? 'checked' : '' }}
                               onchange="filterTopics(this.value)">
                        <span>{{ $g->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-group">
                <div class="filter-lbl">დონე</div>
                <div class="pill-row">
                    @for($d = 1; $d <= 5; $d++)
                    <label class="pill-btn">
                        <input type="radio" name="difficulty" value="{{ $d }}" {{ $selectedDiff == $d ? 'checked' : '' }}>
                        <span>{{ $d }}</span>
                    </label>
                    @endfor
                </div>
            </div>
            <div class="dd-row">
                <select name="topic_id" id="topic_id" class="fc-sm">
                    <option value="">— თემა —</option>
                </select>
                <select name="theme_id" class="fc-sm">
                    <option value="">— თემატიკა —</option>
                    @foreach($themes as $t)
                    <option value="{{ $t->id }}" {{ $selectedThemeId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-gen">გენერაცია →</button>
            </div>
        </form>
    </div>

    @if($error)
    <div class="err">{{ $error }}</div>
    @endif

    @if($questions)
    <div class="result-meta">
        @if($selectedTheme)<span class="theme-tag">🎨 {{ $selectedTheme->name }} · </span>@endif{{ $questions->count() }} კითხვა
    </div>
    @foreach($questions as $i => $q)
    <div class="q-card">
        <div class="q-num">{{ $i + 1 }}<span class="q-topic">{{ $q['topic_name'] }}</span></div>
        <div class="q-text">{{ $q['question_text'] }}</div>
        <div class="opts">
            @foreach($q['options'] as $opt)
            <span class="opt {{ $opt === $q['correct_answer'] ? 'correct' : '' }}">
                {{ $opt }}{{ $opt === $q['correct_answer'] ? ' ✓' : '' }}
            </span>
            @endforeach
        </div>
        @if(!empty($q['hint_text']))
        <div class="hint">{{ $q['hint_text'] }}</div>
        @endif
    </div>
    @endforeach
    @endif
</div>

<script>
const topicsByGrade = @json($topics->groupBy('grade_id'));
const preSelectedTopic = {{ $selectedTopicId ? (int)$selectedTopicId : 'null' }};

function filterTopics(gradeId) {
    const sel = document.getElementById('topic_id');
    sel.innerHTML = '<option value="">— თემა —</option>';
    if (!gradeId || !topicsByGrade[gradeId]) return;
    topicsByGrade[gradeId].forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.name;
        if (preSelectedTopic && t.id === preSelectedTopic) opt.selected = true;
        sel.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('input[name="grade_id"]:checked');
    if (checked) filterTopics(checked.value);
});
</script>
@endsection
