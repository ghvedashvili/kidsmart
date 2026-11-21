@extends('layouts.app')
@section('content')
<style>
    .aw { max-width: 1100px; margin: 0; padding: 0 0 72px; font-family: 'Noto Sans Georgian', system-ui, sans-serif; color: var(--ks-ink,#2B2622); }
    .card-label { font-size: .72rem; font-weight: 800; color: var(--ks-muted,#6B625A); letter-spacing: .08em; text-transform: uppercase; margin-bottom: 14px; }
    .fc { background: #fff; border: 1.5px solid #E6E2DC; border-radius: 12px; color: #2B2622; font-family: inherit; font-size: .88rem; padding: 10px 14px; outline: none; box-sizing: border-box; min-height: 42px; }
    .fc:focus { border-color: #E2590F; box-shadow: 0 0 0 3px rgba(226,89,15,.15); }
    select.fc { cursor: pointer; }
    .btn { background: #fff; border: 1.5px solid #E6E2DC; color: #2B2622; font-family: inherit; font-size: .84rem; font-weight: 700; padding: 9px 18px; border-radius: 999px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: border-color .15s, background .15s; white-space: nowrap; }
    .btn:hover { border-color: #2B2622; color: #2B2622; }
    .btn.btn-primary-ks { background: #C24A0A; border-color: #C24A0A; color: #fff; }
    .btn.btn-primary-ks:hover { background: #A53E08; border-color: #A53E08; color: #fff; }
    .btn-del { background: none; border: none; color: #B5AAA0; font-size: .8rem; cursor: pointer; padding: 0 4px; }
    .btn-del:hover { color: #C62828; }
    .q-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .q-toolbar-left { display: flex; align-items: center; gap: 10px; color: #6B625A; font-size: .84rem; font-weight: 600; }
    .q-views { display: flex; gap: 6px; flex-wrap: wrap; }
    .q-list { display: flex; flex-direction: column; gap: 12px; }
    .topic-group { border: 1.5px solid #ECE9E4; border-radius: 18px; overflow: hidden; background: #fff; }
    .topic-group-header { display: flex; align-items: center; gap: 10px; padding: 13px 16px; background: #F7F6F3; cursor: pointer; user-select: none; }
    .topic-group-header:hover { background: #FDEFE3; }
    .topic-group-arrow { font-size: .7rem; color: #C24A0A; transition: transform .15s; flex-shrink: 0; }
    .topic-group.collapsed .topic-group-arrow { transform: rotate(-90deg); }
    .topic-group-title { font-size: .92rem; font-weight: 800; color: #2B2622; flex: 1; min-width: 0; }
    .topic-group-count { font-size: .74rem; font-weight: 800; color: #C24A0A; background: #FDE3D2; border-radius: 99px; padding: 2px 10px; flex-shrink: 0; }
    .topic-group-body { display: flex; flex-direction: column; gap: 10px; padding: 12px; }
    .topic-group.collapsed .topic-group-body { display: none; }
    .q-row { padding: 16px; border-radius: 16px; background: #fff; border: 1.5px solid #ECE9E4; transition: border-color .15s, box-shadow .15s; }
    .q-row:hover { border-color: #DDD8D1; box-shadow: 0 6px 18px rgba(60,40,20,.06); }
    .q-row.trashed-row { background: #FBF7F3; }
    .q-row.trashed-row:hover { border-color: #ECE9E4; box-shadow: none; }
    .q-title { font-weight: 800; color: #2B2622; font-size: .92rem; margin-bottom: 4px; }
    .q-text { color: #3A332D; font-size: .9rem; line-height: 1.55; margin-bottom: 0; }
    .var-num { color: #C24A0A; font-weight: 800; background: #FFF3EA; border-radius: 6px; padding: 0 5px; }
    .var-str { color: #C24A0A; font-weight: 700; }
    .q-meta { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
    .q-tag { font-size: .74rem; font-weight: 700; color: #6B625A; background: #F1EFEB; border-radius: 999px; padding: 3px 10px; }
    .q-tag.muted { color: #8A8078; background: #F8F4EF; }
    .q-tag.theme { color: #C24A0A; background: #FFF3EA; }
    .q-tag.oly { color: #8A5A00; background: #FFF1C9; }
    .q-seq { width: 46px; height: 30px; background: #F7F6F3; border: 1.5px solid #E6E2DC; border-radius: 8px; color: #C24A0A; font-family: inherit; font-weight: 800; font-size: .78rem; padding: 2px 4px; text-align: center; outline: none; }
    .q-seq:focus { border-color: #E2590F; }
    .q-seq.saved { border-color: #2E9E5B; background: #E8F7EE; }
    .q-actions { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ECE9E4; flex-wrap: wrap; }
    .q-actions-left, .q-actions-right { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .q-action-btn { background: #fff; border: 1.5px solid #E6E2DC; color: #6B625A; font-size: .82rem; width: 32px; height: 32px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0; transition: all .15s; }
    .q-action-btn:hover { border-color: #2B2622; color: #2B2622; background: #F7F6F3; }
    .q-status-btn { font-family: inherit; font-size: .76rem; font-weight: 700; background: #fff; border: 1.5px solid #E6E2DC; border-radius: 999px; padding: 6px 12px; cursor: pointer; color: #6B625A; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .q-status-btn:hover { border-color: #2B2622; color: #2B2622; }
    .q-status-btn.active { color: #1E7A45; border-color: #BFE6CD; background: #E8F7EE; }
    .q-status-btn.active:hover { border-color: #1E7A45; }
    .q-status-btn.inactive { color: #B42318; border-color: #F6C9C4; background: #FDECEA; }
    .q-status-btn.danger { color: #B5AAA0; border: none; background: none; padding: 6px 7px; }
    .q-status-btn.danger:hover { color: #C62828; }
    .q-status-btn.restore { color: #1E7A45; border-color: #9FD9B5; background: #E8F7EE; }
    .diff { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .q-group-card { display: block; border-left: 4px solid #F07A2E; }
    .q-group-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #ECE9E4; }
    .q-group-name { font-weight: 800; color: #2B2622; font-size: .92rem; }
    .q-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
    .q-tab { background: #fff; border: 1.5px solid #E6E2DC; color: #6B625A; font-family: inherit; font-weight: 700; font-size: .76rem; padding: 5px 12px; border-radius: 99px; cursor: pointer; text-decoration: none; transition: all .15s; }
    .q-tab:hover { border-color: #F07A2E; color: #C24A0A; }
    .q-tab.active { border-color: #E2590F; color: #fff; background: #E2590F; }
    .q-group-member + .q-group-member { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ECE9E4; }
    .msg { font-size: .88rem; font-weight: 700; color: #1E7A45; background: #E8F7EE; border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; align-items: center; background: #fff; border: 1.5px solid #ECE9E4; border-radius: 18px; padding: 12px; }
    .filters .clear-link { color: #6B625A; font-size: .82rem; font-weight: 700; text-decoration: none; padding: 9px 4px; }
    .filters .clear-link:hover { color: #C24A0A; }
    .q-expand { margin-bottom: 10px; display: flex; gap: 6px; }
    .q-expand .btn { font-size: .76rem; padding: 6px 13px; }
    .pagination-wrap { margin-top: 22px; display: flex; gap: 6px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { font-family: inherit; font-size: .82rem; font-weight: 700; color: #6B625A; background: #fff; border: 1.5px solid #E6E2DC; border-radius: 10px; padding: 6px 12px; text-decoration: none; }
    .pagination-wrap a:hover { color: #2B2622; border-color: #2B2622; }
    .pagination-wrap span.active-page { color: #fff; background: #C24A0A; border-color: #2B2622; }
    .q-check { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; accent-color: #C24A0A; }
    .pdf-bar { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #C24A0A; border-radius: 999px; padding: 10px 12px 10px 22px; display: none; align-items: center; gap: 12px; box-shadow: 0 12px 32px rgba(0,0,0,.25); z-index: 9000; font-family: 'Noto Sans Georgian', sans-serif; }
    .pdf-bar.show { display: flex; }
    .pdf-bar form { margin: 0; }
    .pdf-count { font-size: .84rem; font-weight: 700; color: #E6E2DC; }
    .pdf-btn { background: #fff; border: none; border-radius: 999px; color: #2B2622; font-family: inherit; font-size: .82rem; font-weight: 800; padding: 8px 16px; cursor: pointer; }
    .pdf-btn:hover { background: #FFE9DA; }
    .pdf-btn:disabled { opacity: .4; cursor: not-allowed; }
    .pdf-btn:disabled:hover { background: #fff; }
    .pdf-clear { background: none; border: none; color: #B5AAA0; font-size: .95rem; cursor: pointer; padding: 0 6px; }
    .pdf-clear:hover { color: #fff; }

    /* live preview modal */
    .lp-overlay { position:fixed; inset:0; background:rgba(29,26,23,.45); display:flex; align-items:center; justify-content:center; z-index:100000; opacity:0; pointer-events:none; transition:opacity .15s; }
    .lp-overlay.open { opacity:1; pointer-events:all; }
    .lp-box { background:#fff; border-radius:22px; width:100%; max-width:460px; max-height:88vh; margin:0 16px; box-shadow:0 20px 50px rgba(0,0,0,.2); transform:translateY(6px); transition:transform .15s; display:flex; flex-direction:column; font-family:'Noto Sans Georgian', sans-serif; }
    .lp-overlay.open .lp-box { transform:translateY(0); }
    .lp-head { display:flex; justify-content:space-between; align-items:center; padding:18px 20px 12px; font-size:.78rem; font-weight:800; color:#6B625A; letter-spacing:.06em; text-transform:uppercase; flex-shrink:0; }
    .lp-close { background:#F1EFEB; border:none; color:#2B2622; font-size:.9rem; width:32px; height:32px; border-radius:50%; cursor:pointer; }
    .lp-close:hover { background:#E6E2DC; }
    .lp-body { padding:0 20px 18px; overflow-y:auto; font-family:inherit; }
    .lp-foot { padding:14px 20px 20px; border-top:1px solid #F1EAE2; flex-shrink:0; }
    .lp-loading, .lp-err { font-size:.86rem; color:#6B625A; padding:20px 0; text-align:center; }
    .lp-err { color:#B42318; }
    .lp-q { font-size:1rem; font-weight:600; color:#2B2622; line-height:1.55; margin-bottom:14px; }

    /* reassign modal */
    .rs-lbl { font-size:.72rem; font-weight:800; color:#6B625A; letter-spacing:.06em; text-transform:uppercase; margin-bottom:6px; }
    .rs-diff-row { display:flex; gap:6px; margin-bottom:6px; }
    .rs-diff-btn { background:#fff; border:1.5px solid #E6E2DC; color:#6B625A; font-family:inherit; font-weight:800; font-size:.88rem; width:42px; height:42px; border-radius:12px; cursor:pointer; transition:all .15s; }
    .rs-diff-btn.sel { border-color:#C24A0A; color:#fff; background:#C24A0A; }
    .rs-err { font-size:.8rem; color:#B42318; margin-bottom:8px; display:none; }
    .rs-hint { font-size:.74rem; color:#6B625A; margin:-2px 0 10px; display:none; }
    .lp-hint { font-size:.82rem; color:#6B625A; border-left:3px solid #FDE3D2; padding-left:10px; margin-bottom:12px; }
    .lp-opts { display:flex; flex-wrap:wrap; gap:8px; }
    .lp-opt { font-size:.88rem; font-weight:700; padding:8px 16px; border-radius:12px; border:1.5px solid #E6E2DC; color:#2B2622; background:#fff; }
    .lp-opt.correct { background:#E8F7EE; border-color:#7FCB9C; color:#14532D; }
    .lp-answer { font-size:.9rem; font-weight:700; color:#14532D; }
    .lp-pyr-row { display:flex; gap:6px; justify-content:center; margin-bottom:6px; }
    .lp-pyr-cell { width:42px; height:42px; border-radius:10px; background:#E2590F; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.9rem; font-weight:800; }
    .lp-pyr-cell.empty { background:#FFF3EA; color:#C24A0A; border:2px dashed #FFE3CC; }
    .lp-code-eqs { background:#FFF6DC; border-radius:12px; padding:8px 12px; margin-bottom:10px; border:1.5px dashed #F2CF6B; font-size:.86rem; color:#2B2622; }
    .lp-code-target { display:flex; gap:8px; flex-wrap:wrap; }
    .lp-code-sym { text-align:center; font-size:1.1rem; }
    .lp-code-sym b { font-size:.8rem; color:#1E7A45; }
    .lp-cw { font-size:.86rem; color:#2B2622; line-height:1.7; }
    .lp-scale-row { display:flex; align-items:flex-start; justify-content:center; gap:8px; flex-wrap:nowrap; margin-bottom:8px; padding:0 4px; }
    .lp-scale-pan { display:flex; flex-wrap:nowrap; gap:2px; align-items:flex-end; justify-content:center; min-width:60px; min-height:34px; overflow:visible; }
    .lp-scale-emoji { font-size:1.15rem; line-height:1; flex-shrink:0; }
    .lp-scale-emoji.lp-scale-blank { border:2px dashed #B5AAA0; border-radius:7px; padding:2px 4px; text-align:center; }
    .lp-scale-emoji.lp-scale-blank span { font-size:.5rem; color:#8A8078; display:block; line-height:1; }
    .lp-scale-wplate { width:26px; height:26px; border-radius:7px; background:#E2590F; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.7rem; flex-shrink:0; }
    .lp-scale-wplate.lp-scale-blank { background:#F7F6F3; border:2px dashed #B5AAA0; color:#8A8078; }
    .lp-scale-cond { background:#FFF3EA; border:1.5px dashed #FFE3CC; border-radius:10px; padding:6px 10px; text-align:center; font-size:.8rem; font-weight:800; color:#A33E08; margin-bottom:10px; }
    @media (max-width: 640px) {
        .filters { gap: 6px; padding: 10px; }
        .filters select, .filters input { font-size: .8rem; padding: 7px 10px; min-height: 38px; flex: 1 1 140px; }
        .q-row { padding: 12px; }
        .q-text { font-size: .84rem; }
        .pdf-bar { width: calc(100% - 24px); left: 12px; transform: none; bottom: 70px; flex-wrap: wrap; gap: 8px; padding: 10px 14px; border-radius: 18px; }
    }
</style>

<div class="aw">
    <div class="ks-page-head">
        <div>
            <div class="ks-eyebrow">კონტენტი · სულ {{ $templates->total() }} შაბლონი</div>
            <h1 class="ks-page-title">{{ $showTrashed ? 'წაშლილი კითხვები' : 'კითხვები' }}</h1>
        </div>
        @unless($showTrashed)
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <a href="{{ route('admin.questions.import.index') }}" class="btn btn-primary-ks" style="background:#fff !important;color:#C24A0A !important;border:2px solid #FFD9BF !important;"><i class="bi bi-stars"></i> AI იმპორტი</a>
        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary-ks">+ ახალი შაბლონი</a>
        </div>
        @endunless
    </div>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    @php $__rvPending = auth()->user()->isAdmin() ? \App\Models\QuestionTemplate::where('review_status', 'pending')->count() : 0; @endphp
    @if($__rvPending && ! $showTrashed)
    <a href="{{ route('admin.questions.review.index') }}" style="display:flex;align-items:center;gap:10px;background:#FFF3EA;border:2px solid #FFD9BF;color:#C24A0A;border-radius:16px;padding:12px 16px;font-weight:800;text-decoration:none;margin-bottom:12px;">
        <i class="bi bi-person-badge" style="font-size:18px;"></i>
        <span style="flex:1;">მასწავლებლების {{ $__rvPending }} კითხვა ელოდება განხილვას</span>
        <span>ნახვა →</span>
    </a>
    @endif

    <div class="q-toolbar">
        <div class="q-toolbar-left">
            @unless($showTrashed)
            <input type="checkbox" id="checkAll" class="q-check" title="ყველა მონიშვნა">
            <label for="checkAll" style="cursor:pointer;margin:0;">ყველას მონიშვნა</label>
            @endunless
        </div>
        <div class="q-views">
            @if($showTrashed)
            <a href="{{ route('admin.questions.index', request()->except('trashed', 'page')) }}" class="btn">← აქტიურები</a>
            @else
            @if($flatView)
            <a href="{{ route('admin.questions.index', request()->except('flat', 'page')) }}" class="btn"><i class="bi bi-collection"></i> ჯგუფებით</a>
            @else
            <a href="{{ route('admin.questions.index', array_merge(request()->except('page'), ['flat' => 1])) }}" class="btn"><i class="bi bi-list-ul"></i> ძველი სია</a>
            @endif
            <a href="{{ route('admin.questions.index', array_merge(request()->except('page'), ['trashed' => 1])) }}" class="btn"><i class="bi bi-trash3"></i> წაშლილები</a>
            @endif
        </div>
    </div>

    <form method="GET" class="filters" id="filterForm">
        <input type="text" name="search" class="fc" placeholder="🔍 ძებნა — ტექსტში, სათაურში, ფორმულაში..."
            value="{{ $filters['search'] ?? '' }}" style="min-width:220px;">
        <select name="grade_id" class="fc" id="gradeFilter" onchange="onGradeChange(this.value)">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                {{ $grade->name }}
            </option>
            @endforeach
        </select>
        <select name="topic_id" class="fc" id="topicFilter" onchange="this.form.submit()">
            <option value="">ყველა თემა</option>
            @foreach($topics as $topic)
            <option value="{{ $topic->id }}" {{ ($filters['topic_id'] ?? '') == $topic->id ? 'selected' : '' }}>
                {{ $topic->grade->name }} / {{ $topic->name }}
            </option>
            @endforeach
        </select>
        <select name="theme_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა თემატიკა</option>
            @foreach($themes as $theme)
            <option value="{{ $theme->id }}" {{ ($filters['theme_id'] ?? '') == $theme->id ? 'selected' : '' }}>
                {{ $theme->icon ?? '' }} {{ $theme->name }}
            </option>
            @endforeach
        </select>
        <select name="difficulty" class="fc" onchange="this.form.submit()">
            <option value="">ყველა დონე</option>
            @for($i=1;$i<=5;$i++)
            <option value="{{ $i }}" {{ ($filters['difficulty'] ?? '') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
            @endfor
            <option value="olympiad" {{ ($filters['difficulty'] ?? '') === 'olympiad' ? 'selected' : '' }}>🏆 ოლიმპიადა</option>
        </select>
        <button type="submit" class="btn">ძებნა</button>
        @if(array_filter($filters))
        <a href="{{ route('admin.questions.index') }}" class="clear-link">× გასუფთავება</a>
        @endif
    </form>

    @php
        // Groups the CURRENT PAGE's results by topic -- the controller already
        // orders the query by topic (grade, sequence, name) so each group's
        // rows land together within a page. Grouping is skipped (flat list, one
        // unlabeled "group" holding everything) ONLY when a specific topic is
        // already selected, where a header for that one topic would just be
        // noise -- NOT based on how many distinct topics happen to land on the
        // current page, which made the grouping flicker on/off unpredictably
        // whenever a filter (grade, theme, difficulty, search...) happened to
        // narrow a page down to a single topic's worth of results.
        $showGroups = $templates->isNotEmpty() && empty($filters['topic_id']);
        $sections   = $showGroups ? $templates->getCollection()->groupBy('topic_id') : collect(['_all' => $templates->getCollection()]);
    @endphp
    @if($showGroups)
    <div class="q-expand">
        <button type="button" class="btn" onclick="toggleAllTopicGroups(true)">▾ ყველას გაშლა</button>
        <button type="button" class="btn" onclick="toggleAllTopicGroups(false)">▸ ყველას ჩაკეცვა</button>
    </div>
    @endif
    <div class="q-list">
        @forelse($sections as $section)
        @if($showGroups)
        <div class="topic-group">
            <div class="topic-group-header" onclick="this.parentElement.classList.toggle('collapsed')">
                <span class="topic-group-arrow">▾</span>
                <span class="topic-group-title">{{ $section->first()->topic->grade->name }} / {{ $section->first()->topic->name }}</span>
                <span class="topic-group-count">{{ $section->count() }}</span>
            </div>
            <div class="topic-group-body">
        @endif
        @php
            // Ungrouped templates render exactly as before, one per card.
            // Grouped templates (question_group_id set) cluster into one
            // merged card per group, members ordered by difficulty, with the
            // whole card list re-sorted by each card's newest member id so
            // a merged group slots in where its most recent member would
            // have, instead of jumping to the top/bottom of the section.
            // $flatView ("ძველი სია") bypasses all of this — every template,
            // grouped or not, renders as its own card, exactly like before
            // groups existed — for admins who'd rather filter the old way.
            $cards = [];
            if ($flatView) {
                foreach ($section as $t) {
                    $cards[] = ['type' => 'single', 'sort_id' => $t->id, 'tpl' => $t];
                }
            } else {
                foreach ($section->whereNull('question_group_id') as $t) {
                    $cards[] = ['type' => 'single', 'sort_id' => $t->id, 'tpl' => $t];
                }
                foreach ($section->whereNotNull('question_group_id')->groupBy('question_group_id') as $members) {
                    $cards[] = [
                        'type'    => 'group',
                        'sort_id' => $members->max('id'),
                        'group'   => $members->first()->group,
                        'members' => $members->sortBy('difficulty')->values(),
                    ];
                }
            }
            usort($cards, fn($a, $b) => $b['sort_id'] <=> $a['sort_id']);
        @endphp
        @foreach($cards as $card)
        @if($card['type'] === 'single')
        @php $tpl = $card['tpl']; @endphp
        <div class="q-row {{ $showTrashed ? 'trashed-row' : '' }}" style="display:flex;gap:10px;align-items:flex-start;{{ (!$showTrashed && !$tpl->is_active) ? 'opacity:0.5;' : '' }}">
            @include('admin.questions._row-body', ['tpl' => $tpl])
            @unless($showTrashed)
            <a href="{{ route('admin.questions.create', ['base_id' => $tpl->id]) }}" class="q-action-btn" title="ახალი დონის დამატება — ჯგუფად გაერთიანდება ამ კითხვასთან">+</a>
            @endunless
        </div>
        @else
        <div class="q-row q-group-card">
            <div class="q-group-head">
                <span class="q-group-name">🔗 {{ $card['group']->name }}</span>
                <div class="q-tabs">
                    @foreach($card['members'] as $i => $m)
                    <button type="button" class="q-tab {{ $i === 0 ? 'active' : '' }}"
                        data-group-tab-id="{{ $m->id }}" onclick="switchGroupTab(this)">დონე {{ $m->difficulty }}</button>
                    @endforeach
                    @unless($showTrashed)
                    @if($card['members']->count() < 5)
                    <a href="{{ route('admin.questions.create', ['group_id' => $card['group']->id]) }}" class="q-tab" title="ახალი დონის დამატება ამ ჯგუფში">+ დონე</a>
                    @endif
                    @endunless
                </div>
            </div>
            @foreach($card['members'] as $i => $m)
            <div class="q-group-member" data-group-member-id="{{ $m->id }}"
                style="display:{{ $i === 0 ? 'flex' : 'none' }};gap:10px;align-items:flex-start;{{ (!$showTrashed && !$m->is_active) ? 'opacity:0.5;' : '' }}">
                @include('admin.questions._row-body', ['tpl' => $m])
                @unless($showTrashed)
                <button type="button" class="q-action-btn" onclick="ungroupTemplate({{ $m->id }})" title="ჯგუფიდან მოხსნა">✂</button>
                @endunless
            </div>
            @endforeach
        </div>
        @endif
        @endforeach
        @if($showGroups)
            </div>
        </div>
        @endif
        @empty
        <div class="q-row" style="color:#6B625A;font-size:.9rem;text-align:center;padding:32px;">შაბლონი არ არის</div>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $templates->withQueryString()->links('admin.pagination') }}
    </div>
</div>

{{-- Floating PDF bar --}}
<div class="pdf-bar" id="pdfBar">
    <span class="pdf-count" id="pdfCount">0 მონიშნული</span>
    <form method="POST" action="{{ route('admin.questions.pdf') }}" target="_blank" id="pdfForm">
        @csrf
        <div id="pdfInputs"></div>
        <button type="submit" class="pdf-btn">📄 PDF-ში</button>
    </form>
    <button type="button" class="pdf-btn" id="mergeBtn" style="display:none;" onclick="openMergeModal()">🔗 დააჯგუფე</button>
    <button class="pdf-clear" onclick="clearAll()" title="გასუფთავება">✕</button>
</div>

{{-- Live preview modal --}}
<div class="lp-overlay" id="lpOverlay" onclick="lpOverlayClick(event)">
    <div class="lp-box">
        <div class="lp-head">
            <span>ლაივ პრევიუ</span>
            <button type="button" class="lp-close" onclick="closeLivePreview()">✕</button>
        </div>
        <div class="lp-body" id="lpBody"></div>
        <div class="lp-foot">
            <button type="button" class="btn" onclick="regenLivePreview()">↺ ახალი თაობა</button>
        </div>
    </div>
</div>

{{-- Reassign modal: change grade/topic/theme/difficulty without opening the full edit form --}}
<div class="lp-overlay" id="rsOverlay" onclick="rsOverlayClick(event)">
    <div class="lp-box">
        <div class="lp-head">
            <span id="rsTitle">კლასი · თემა · თემატიკა · დონე</span>
            <button type="button" class="lp-close" onclick="closeReassign()">✕</button>
        </div>
        <div class="lp-body">
            <div class="rs-lbl">კლასი</div>
            <select id="rsGrade" class="fc" style="margin-bottom:10px;width:100%;" onchange="onRsGradeChange()">
                @foreach($grades as $grade)
                <option value="{{ $grade->id }}" data-max-level="{{ $grade->max_level }}">{{ $grade->name }}</option>
                @endforeach
            </select>
            <div class="rs-lbl">თემა</div>
            <select id="rsTopic" class="fc" style="margin-bottom:10px;width:100%;"></select>
            <div class="rs-lbl" id="rsThemeWrap">თემატიკა</div>
            <select id="rsTheme" class="fc" style="margin-bottom:10px;width:100%;">
                <option value="">— არავითარი —</option>
                @foreach($themes as $theme)
                <option value="{{ $theme->id }}">{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                @endforeach
            </select>
            <div class="rs-lbl">დონე</div>
            <div class="rs-diff-row" id="rsDiffRow">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" class="rs-diff-btn" data-d="{{ $i }}" onclick="setRsDiff({{ $i }})">{{ $i }}</button>
                @endfor
            </div>
            <div class="rs-hint" id="rsMaxHint"></div>
            <div class="rs-err" id="rsErr"></div>
        </div>
        <div class="lp-foot" style="display:flex;gap:8px;">
            <button type="button" class="btn" id="rsSaveBtn" style="background:#C24A0A;border-color:#C24A0A;color:#fff;" onclick="saveReassign()">შენახვა</button>
            <button type="button" class="btn" onclick="closeReassign()">გაუქმება</button>
        </div>
    </div>
</div>

{{-- Merge modal: cluster >=2 checked templates into one named group --}}
<div class="lp-overlay" id="mgOverlay" onclick="mgOverlayClick(event)">
    <div class="lp-box">
        <div class="lp-head">
            <span>🔗 კითხვების დაჯგუფება</span>
            <button type="button" class="lp-close" onclick="closeMergeModal()">✕</button>
        </div>
        <div class="lp-body">
            <div class="rs-lbl">ჯგუფის სახელი</div>
            <input type="text" id="mgName" class="fc" style="width:100%;" maxlength="150" placeholder="მაგ. შეკრება 1-დან 10-მდე">
            <div class="rs-err" id="mgErr"></div>
        </div>
        <div class="lp-foot" style="display:flex;gap:8px;">
            <button type="button" class="btn" onclick="saveMerge()">დაჯგუფება</button>
            <button type="button" class="btn" onclick="closeMergeModal()">გაუქმება</button>
        </div>
    </div>
</div>

<script>
// grade → topic filtering
const _allTopics = @json($topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'grade_id' => $t->grade_id]));
const _topicsByGrade = {};
_allTopics.forEach(t => { (_topicsByGrade[t.grade_id] = _topicsByGrade[t.grade_id] || []).push(t); });
const _selectedTopic = {{ (int)($filters['topic_id'] ?? 0) }};

function onGradeChange(gradeId) {
    const sel = document.getElementById('topicFilter');
    const prev = sel.value;
    sel.innerHTML = '<option value="">ყველა თემა</option>';
    const list = gradeId ? (_topicsByGrade[gradeId] || []) : _allTopics;
    list.forEach(t => {
        const opt = new Option(t.name, t.id);
        if (String(t.id) === String(prev) || t.id === _selectedTopic) opt.selected = true;
        sel.add(opt);
    });
    document.getElementById('filterForm').submit();
}

(function () {
    const gradeId = document.getElementById('gradeFilter').value;
    if (!gradeId) return;
    const sel = document.getElementById('topicFilter');
    sel.innerHTML = '<option value="">ყველა თემა</option>';
    (_topicsByGrade[gradeId] || []).forEach(t => {
        const opt = new Option(t.name, t.id);
        if (t.id === _selectedTopic) opt.selected = true;
        sel.add(opt);
    });
})();

const THEME_VARS = @json($themeVarMap);
@verbatim
// Mirrors OPERATOR_LABELS / resolveTextFormula / findTopLevelChar in
// QuestionTemplate.php and the admin form's JS twins — this listing page's
// "↺" preview is a separate, lighter re-implementation (no access to
// `conditions`, so no retry-until-satisfied loop), but still needs to handle
// all 4 num_config variable modes or derived/operator/conditional vars render
// as NaN/undefined.
const OPERATOR_LABELS_LITE = { '+': 'ით მეტი', '-': 'ით ნაკლები', '*': 'ჯერ მეტი', '/': 'ჯერ ნაკლები' };

function evalExprLite(expr, vars) {
    let e = String(expr);
    Object.entries(vars).forEach(([k, v]) => { e = e.replaceAll(k, String(v)); });
    e = e.replace(/[^0-9+\-*/()\s%]/g, '');
    try {
        const r = Function('return (' + e + ')')();
        return Number.isFinite(r) ? Math.floor(r) : 0;
    } catch (err) { return 0; }
}
function stripWrappingParensLite(s) {
    while (s.length >= 2 && s[0] === '(' && s[s.length - 1] === ')') {
        let depth = 0, wrapsWhole = true;
        for (let i = 0; i < s.length; i++) {
            if (s[i] === '(') depth++;
            else if (s[i] === ')') { depth--; if (depth === 0 && i !== s.length - 1) { wrapsWhole = false; break; } }
        }
        if (!wrapsWhole) break;
        s = s.slice(1, -1).trim();
    }
    return s;
}
function findTopLevelCharLite(str, char) {
    let depth = 0;
    for (let i = 0; i < str.length; i++) {
        if (str[i] === '(') depth++;
        else if (str[i] === ')') depth--;
        else if (depth === 0 && str[i] === char) return i;
    }
    return null;
}
function splitConditionExprLite(cond) {
    for (const op of ['>=', '<=', '==', '!=']) { const p = cond.indexOf(op); if (p !== -1) return [cond.slice(0, p).trim(), op, cond.slice(p + 2).trim()]; }
    for (const op of ['>', '<']) { const p = cond.indexOf(op); if (p !== -1) return [cond.slice(0, p).trim(), op, cond.slice(p + 1).trim()]; }
    return null;
}
function resolveConditionalLite(formula, vars) {
    formula = stripWrappingParensLite((formula || '').trim());
    const qPos = findTopLevelCharLite(formula, '?');
    if (qPos === null) {
        if (formula in vars) return vars[formula];
        if (formula.includes('{{')) return formula.replace(/\{\{(\w+)\}\}/g, (m, n) => (n in vars) ? vars[n] : m);
        return formula || '?';
    }
    const condStr = formula.slice(0, qPos).trim();
    const rest = formula.slice(qPos + 1);
    const cPos = findTopLevelCharLite(rest, ':');
    if (cPos === null) return '?';
    const ifTrue = rest.slice(0, cPos).trim(), ifFalse = rest.slice(cPos + 1).trim();
    const parts = splitConditionExprLite(condStr);
    if (!parts) return '?';
    const [l, op, r] = parts;
    const lv = evalExprLite(l, vars), rv = evalExprLite(r, vars);
    let ok;
    switch (op) {
        case '>': ok = lv > rv; break;
        case '<': ok = lv < rv; break;
        case '>=': ok = lv >= rv; break;
        case '<=': ok = lv <= rv; break;
        case '==': ok = lv === rv; break;
        default: ok = lv !== rv;
    }
    return resolveConditionalLite(ok ? ifTrue : ifFalse, vars);
}

function genVars(nc) {
    const vars = {};
    const randomEntries = [], operatorEntries = [], derivedEntries = [], conditionalEntries = [];
    for (const [name, conf] of Object.entries(nc)) {
        if (name.startsWith('_') || typeof conf !== 'object' || conf === null) continue;
        if (conf.operator) operatorEntries.push([name, conf]);
        else if ('derived' in conf) derivedEntries.push([name, conf]);
        else if ('conditional' in conf) conditionalEntries.push([name, conf]);
        else randomEntries.push([name, conf]);
    }
    randomEntries.forEach(([name, conf]) => {
        const step = Math.max(1, conf.step || 1);
        const steps = Math.floor((conf.max - conf.min) / step);
        vars[name] = conf.min + Math.floor(Math.random() * (steps + 1)) * step;
    });
    operatorEntries.forEach(([name, conf]) => {
        const opts = (conf.options && conf.options.length) ? conf.options : ['+'];
        vars[name] = opts[Math.floor(Math.random() * opts.length)];
    });
    derivedEntries.forEach(([name, conf]) => {
        vars[name] = evalExprLite(conf.derived || '0', vars);
    });
    conditionalEntries.forEach(([name, conf]) => {
        vars[name] = resolveConditionalLite(conf.conditional || '', vars);
    });

    // Display values for the rendered text — an operator variable shows its
    // Georgian label ("ით მეტი") here instead of the raw symbol ("+") that
    // was used above for any derived/conditional formula that referenced it.
    const displayVars = { ...vars };
    operatorEntries.forEach(([name]) => {
        displayVars[name] = OPERATOR_LABELS_LITE[displayVars[name]] ?? displayVars[name];
    });
    return displayVars;
}

function renderPreview(el) {
    const nc = JSON.parse(el.dataset.nc || '{}');
    const vars = genVars(nc);
    el.innerHTML = el.dataset.tpl.replace(/\{\{(\w+)\}\}/g, (_, name) => {
        if (vars[name] !== undefined) return `<span class="var-num">${vars[name]}</span>`;
        const list = THEME_VARS[name];
        if (list && list.length) return `<span class="var-str">${list[Math.floor(Math.random() * list.length)]}</span>`;
        return `<span class="var-str">${name}</span>`;
    });
}

function regenPreview(btn) {
    const el = btn.closest('.q-row').querySelector('.q-preview');
    if (el) renderPreview(el);
}

function saveQSequence(input) {
    const id = input.dataset.tplId;
    const val = input.value.trim();
    input.classList.remove('saved');
    fetch(`/admin/questions/${id}/sequence`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ sequence: val === '' ? null : val }),
    })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(() => { input.classList.add('saved'); setTimeout(() => input.classList.remove('saved'), 1200); })
        .catch(() => { alert('შეცდომა — რიგითობა არ შეინახა'); });
}

function confirmDeleteQuestion(btn) {
    const form = btn.closest('form');
    Swal.fire({
        title: 'შაბლონის წაშლა?',
        html: '<span style="font-size:0.9rem;color:#555;">აღდგენა შესაძლებელია „წაშლილების" სიიდან.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'დიახ, წავშალო',
        cancelButtonText: 'გაუქმება',
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
}

// ── Live preview modal — fetches the REAL server-side generate() result for
// this exact template id, so it's never out of sync with what a child gets.
let lpCurrentId = null;

function openLivePreview(id) {
    lpCurrentId = id;
    document.getElementById('lpOverlay').classList.add('open');
    regenLivePreview();
}
function closeLivePreview() {
    document.getElementById('lpOverlay').classList.remove('open');
}
function lpOverlayClick(e) {
    if (e.target === document.getElementById('lpOverlay')) closeLivePreview();
}

// ── Reassign modal — change grade (via topic)/topic/theme/difficulty from the
// list without opening the full edit form. Reuses _topicsByGrade already built
// above for the filter bar's grade→topic cascade.
let rsId = null, rsDiff = 1, rsIsOlympiad = false, rsQType = 'multiple_choice', rsMode = 'move';

function openReassign(btn, mode) {
    rsId         = btn.dataset.id;
    rsMode       = mode || 'move';
    rsDiff       = +btn.dataset.difficulty || 1;
    rsIsOlympiad = btn.dataset.olympiad === '1';
    rsQType      = btn.dataset.qtype;
    const gradeId = btn.dataset.grade;
    const topicId = btn.dataset.topic;
    const themeId = btn.dataset.theme;

    document.getElementById('rsGrade').value = gradeId;
    rebuildRsTopics(gradeId, topicId);
    applyRsMaxLevel(gradeId);
    setRsDiff(rsDiff);

    const themeSel = document.getElementById('rsTheme');
    const isSpecial = ['pyramid', 'code', 'crossword', 'scale', 'data_table'].includes(rsQType);
    themeSel.disabled = isSpecial;
    document.getElementById('rsThemeWrap').style.opacity = isSpecial ? '0.4' : '1';
    themeSel.value = isSpecial ? '' : (themeId || '');

    document.getElementById('rsTitle').textContent = rsMode === 'copy'
        ? '⎘ დუბლირება სხვაგან'
        : '⇄ კლასი · თემა · თემატიკა · დონე';
    document.getElementById('rsSaveBtn').textContent = rsMode === 'copy' ? 'დუბლირება' : 'შენახვა';

    document.getElementById('rsErr').style.display = 'none';
    document.getElementById('rsOverlay').classList.add('open');
}
function closeReassign() {
    document.getElementById('rsOverlay').classList.remove('open');
}
function rsOverlayClick(e) {
    if (e.target === document.getElementById('rsOverlay')) closeReassign();
}
function onRsGradeChange() {
    const gradeId = document.getElementById('rsGrade').value;
    rebuildRsTopics(gradeId, null);
    applyRsMaxLevel(gradeId);
}
function rebuildRsTopics(gradeId, keepTopicId) {
    const sel = document.getElementById('rsTopic');
    sel.innerHTML = '';
    (_topicsByGrade[gradeId] || []).forEach(t => {
        const opt = new Option(t.name, t.id);
        if (keepTopicId && String(t.id) === String(keepTopicId)) opt.selected = true;
        sel.add(opt);
    });
}
function applyRsMaxLevel(gradeId) {
    const opt = document.querySelector('#rsGrade option[value="' + gradeId + '"]');
    const max = rsIsOlympiad ? 5 : (opt ? (+opt.dataset.maxLevel || 5) : 5);
    document.querySelectorAll('#rsDiffRow .rs-diff-btn').forEach(b => {
        b.style.display = (+b.dataset.d) <= max ? '' : 'none';
    });
    const hint = document.getElementById('rsMaxHint');
    if (!rsIsOlympiad && max < 5) {
        hint.textContent = 'ამ კლასს მაქსიმუმ ' + max + ' დონე აქვს';
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
    if (rsDiff > max) setRsDiff(max);
}
function setRsDiff(d) {
    rsDiff = d;
    document.querySelectorAll('#rsDiffRow .rs-diff-btn').forEach(b => {
        b.classList.toggle('sel', +b.dataset.d === d);
    });
}
async function saveReassign() {
    const topicId = document.getElementById('rsTopic').value;
    const themeVal = document.getElementById('rsTheme').value;
    const errEl = document.getElementById('rsErr');
    errEl.style.display = 'none';
    if (!topicId) {
        errEl.textContent = 'აირჩიე თემა';
        errEl.style.display = 'block';
        return;
    }
    try {
        const url = rsMode === 'copy'
            ? `/admin/questions/${rsId}/duplicate`
            : `/admin/questions/${rsId}/reassign`;
        const r = await fetch(url, {
            method: rsMode === 'copy' ? 'POST' : 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ topic_id: topicId, theme_id: themeVal || null, difficulty: rsDiff }),
        });
        const data = await r.json();
        if (!r.ok) {
            errEl.textContent = data.error || 'შეცდომა — არ შეინახა';
            errEl.style.display = 'block';
            return;
        }
        window.location.reload();
    } catch (e) {
        errEl.textContent = 'დაკავშირების შეცდომა';
        errEl.style.display = 'block';
    }
}

async function regenLivePreview() {
    if (!lpCurrentId) return;
    const body = document.getElementById('lpBody');
    body.innerHTML = '<div class="lp-loading">იტვირთება...</div>';
    try {
        const r = await fetch(`/admin/questions/${lpCurrentId}/preview`, { headers: { 'Accept': 'application/json' } });
        const data = await r.json();
        if (!data.ok) {
            body.innerHTML = `<div class="lp-err">⚠ ${escHtmlLp(data.error || 'შეცდომა')}</div>`;
            return;
        }
        body.innerHTML = renderLivePreview(data);
    } catch (e) {
        body.innerHTML = '<div class="lp-err">⚠ დაკავშირების შეცდომა</div>';
    }
}

function escHtmlLp(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function renderLivePreview(data) {
    const qt = data.question_type || 'multiple_choice';
    if (qt === 'pyramid')   return renderLpPyramid(data);
    if (qt === 'code')      return renderLpCode(data);
    if (qt === 'crossword') return renderLpCrossword(data);
    if (qt === 'scale')     return renderLpScale(data);
    if (qt === 'data_table') return renderLpDataTable(data);
    return renderLpMc(data);
}

function renderLpMc(data) {
    let html = `<div class="lp-q">${escHtmlLp(data.question_text)}</div>`;
    if (data.hint_text) html += `<div class="lp-hint">${escHtmlLp(data.hint_text)}</div>`;
    if (Array.isArray(data.options) && data.options.length) {
        html += '<div class="lp-opts">' + data.options.map(o =>
            `<div class="lp-opt ${o === data.correct_answer ? 'correct' : ''}">${escHtmlLp(o)}</div>`
        ).join('') + '</div>';
    } else {
        html += `<div class="lp-answer">✍️ პასუხი: <b>${escHtmlLp(data.correct_answer)}</b></div>`;
    }
    return html;
}

function renderLpPyramid(data) {
    let rows, sols;
    try { rows = JSON.parse(data.question_text); sols = JSON.parse(data.correct_answer); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    let html = '<div class="lp-q">🔺 პირამიდა</div>';
    rows.forEach((row, r) => {
        html += '<div class="lp-pyr-row">';
        row.forEach((val, c) => {
            if (val === null) {
                const sol = sols[`${r},${c}`];
                html += `<div class="lp-pyr-cell empty">${sol ?? '?'}</div>`;
            } else {
                html += `<div class="lp-pyr-cell">${val}</div>`;
            }
        });
        html += '</div>';
    });
    return html;
}

function renderLpCode(data) {
    let q, ans;
    try { q = JSON.parse(data.question_text); ans = JSON.parse(data.correct_answer); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    let html = '<div class="lp-q">🕵️ კოდი</div>';
    html += '<div class="lp-code-eqs">' + (q.equations || []).map(eq => `<div>${escHtmlLp(eq)}</div>`).join('') + '</div>';
    html += '<div class="lp-code-target">' + (q.target || []).map((sym, i) =>
        `<div class="lp-code-sym">${sym}<br><b>${ans[i] ?? '?'}</b></div>`
    ).join('') + '</div>';
    return html;
}

function renderLpCrossword(data) {
    let q;
    try { q = JSON.parse(data.question_text); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }
    return `<div class="lp-q">🧮 კროსვორდი</div><div class="lp-cw">` +
        `რიგები (${q.rows}×${q.cols})<br>` +
        `სტრიქონების ოპერატორები: ${(q.row_ops || []).join(', ')} · შედეგები: ${(q.row_results || []).join(', ')}<br>` +
        `სვეტების ოპერატორები: ${(q.col_ops || []).join(', ')} · შედეგები: ${(q.col_results || []).join(', ')}` +
        `</div>`;
}

// Mirrors ScaleService::scaleSvg() (PHP) — same static, level triangle+beam frame.
// Mirrors ScaleService::scaleSvg() (PHP) — beam tilts toward the heavier side
// around a fixed triangle stand; no platform/tray.
// Mirrors ScaleService::scaleSvg() (PHP) — straight, level beam resting on a
// fixed triangle stand; items render right above it, sitting on top of the beam.
function scaleSvgHtmlLp() {
    return '<svg viewBox="0 0 280 60" style="display:block;width:100%;height:auto;overflow:visible;">'
        + '<line x1="10" y1="10" x2="270" y2="10" stroke="#1a7a3c" stroke-width="5" stroke-linecap="round"/>'
        + '<circle cx="140" cy="10" r="6" fill="#1a7a3c"/>'
        + '<polygon points="118,58 162,58 140,16" fill="#1a7a3c"/>'
        + '</svg>';
}

// Every occurrence gets its OWN chip (never a single text blob with the symbol
// repeated N times) — lets the nowrap row size each one individually instead of
// risking the browser line-wrapping a multi-emoji string.
function renderLpScalePan(items) {
    let html = '';
    (items || []).forEach(it => {
        const count = Math.max(1, it.count || 1);
        for (let i = 0; i < count; i++) {
            html += it.kind === 'weight'
                ? `<div class="lp-scale-wplate">${escHtmlLp(String(it.value ?? '?'))}</div>`
                : `<div class="lp-scale-emoji">${it.symbol || '❓'}</div>`;
        }
    });
    return html;
}

function renderLpScaleBlank(kind, symbol) {
    // MC mode never names the answer symbol in advance (null symbol) -- same
    // as a weight-kind blank, a single "?" plate is the whole clue. Falling
    // back to the generic "❓" emoji AND still appending the small "how
    // many?" subscript below it prints two question marks.
    if (kind === 'weight' || !symbol) return `<div class="lp-scale-wplate lp-scale-blank">?</div>`;
    return `<div class="lp-scale-emoji lp-scale-blank">${escHtmlLp(symbol)}<span>?</span></div>`;
}

function renderLpScale(data) {
    let q;
    try { q = JSON.parse(data.question_text); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }

    const blankChip = renderLpScaleBlank(q.blank_kind, q.blank_symbol);
    const leftHtml  = renderLpScalePan(q.left)  + (q.blank_side === 'left'  ? blankChip : '');
    const rightHtml = renderLpScalePan(q.right) + (q.blank_side === 'right' ? blankChip : '');

    let html = `<div class="lp-q">⚖️ ${escHtmlLp(q.prompt || '')}</div>`;
    html += `<div style="max-width:260px;margin:0 auto 8px;"><div class="lp-scale-row" style="justify-content:space-between;align-items:flex-end;margin-bottom:2px;">`
        + `<div class="lp-scale-pan" style="flex:1;">${leftHtml}</div>`
        + `<div class="lp-scale-pan" style="flex:1;">${rightHtml}</div>`
        + `</div>${scaleSvgHtmlLp()}</div>`;

    if (Array.isArray(data.options) && data.options.length) {
        html += '<div class="lp-opts">' + data.options.map(o =>
            `<div class="lp-opt ${o === data.correct_answer ? 'correct' : ''}">${escHtmlLp(o)}</div>`
        ).join('') + '</div>';
    } else {
        html += `<div class="lp-answer">✍️ პასუხი: <b>${escHtmlLp(data.correct_answer)}</b></div>`;
    }
    return html;
}

function renderLpDataTable(data) {
    let q;
    try { q = JSON.parse(data.question_text); }
    catch (e) { return '<div class="lp-err">პარსინგის შეცდომა</div>'; }

    let html = `<div class="lp-q">📊 ${escHtmlLp(q.prompt || '')}</div>`;
    html += '<div style="overflow-x:auto;"><table style="border-collapse:collapse;margin:0 auto;font-size:0.85rem;">';
    html += '<tr><th style="border:1px solid #D9D4CC;background:#F1EFEB;padding:5px 8px;"></th>'
        + (q.col_headers || []).map(h => `<th style="border:1px solid #D9D4CC;background:#F1EFEB;padding:5px 8px;">${escHtmlLp(h)}</th>`).join('')
        + '</tr>';
    (q.data || []).forEach((row, i) => {
        html += `<tr><th style="border:1px solid #D9D4CC;background:#F7F6F3;padding:5px 8px;text-align:left;">${escHtmlLp((q.row_labels || [])[i] ?? '')}</th>`
            + row.map(v => `<td style="border:1px solid #D9D4CC;padding:5px 8px;text-align:center;">${escHtmlLp(String(v))}</td>`).join('')
            + '</tr>';
    });
    html += '</table></div>';

    if (Array.isArray(data.options) && data.options.length) {
        html += '<div class="lp-opts">' + data.options.map(o =>
            `<div class="lp-opt ${o === data.correct_answer ? 'correct' : ''}">${escHtmlLp(o)}</div>`
        ).join('') + '</div>';
    } else {
        html += `<div class="lp-answer">✍️ პასუხი: <b>${escHtmlLp(data.correct_answer)}</b></div>`;
    }
    return html;
}

@endverbatim

const bar      = document.getElementById('pdfBar');
const countEl  = document.getElementById('pdfCount');
const inputs   = document.getElementById('pdfInputs');
const checkAll = document.getElementById('checkAll');

function hasDuplicateDifficulties(checked) {
    const diffs = checked.map(c => c.dataset.difficulty);
    return new Set(diffs).size !== diffs.length;
}

function updateBar() {
    const checked = [...document.querySelectorAll('.q-item:checked')];
    bar.classList.toggle('show', checked.length > 0);
    countEl.textContent = checked.length + ' მონიშნული';
    inputs.innerHTML = checked.map(c => `<input type="hidden" name="ids[]" value="${c.value}">`).join('');

    const mergeBtn = document.getElementById('mergeBtn');
    mergeBtn.style.display = checked.length >= 2 ? 'inline-block' : 'none';
    const dup = checked.length >= 2 && hasDuplicateDifficulties(checked);
    mergeBtn.disabled = dup;
    mergeBtn.title = dup ? 'შერჩეულებს შორის ერთი და იგივე დონე მეორდება — გაერთიანება შეუძლებელია' : '';
}

// ── Group tabs — all members' markup is already rendered, so switching tabs
// is pure show/hide, no fetch. A hidden member's checkbox keeps its checked
// state (just display:none on its wrapper), so checking boxes across
// different tabs of the same group still all land in updateBar()'s set.
function switchGroupTab(btn) {
    const card = btn.closest('.q-group-card');
    card.querySelectorAll('.q-tab').forEach(t => t.classList.toggle('active', t === btn));
    const id = btn.dataset.groupTabId;
    card.querySelectorAll('.q-group-member').forEach(m => {
        m.style.display = (m.dataset.groupMemberId === id) ? 'flex' : 'none';
    });
}

// ── Merge modal — groups >=2 checked templates. Skips the name prompt and
// merges immediately when exactly one existing group is already represented
// among the checked items (reusing it); blocks with an error when 2+
// different existing groups are checked at once, rather than guessing.
function openMergeModal() {
    const checked = [...document.querySelectorAll('.q-item:checked')];
    if (checked.length < 2) return;
    if (hasDuplicateDifficulties(checked)) {
        alert('შერჩეულებს შორის ერთი და იგივე დონე მეორდება — გაერთიანება შეუძლებელია');
        return;
    }
    const groupIds = [...new Set(checked.map(c => c.dataset.groupId).filter(Boolean))];
    if (groupIds.length > 1) {
        alert('შერჩეული კითხვები სხვადასხვა ჯგუფს ეკუთვნის — ჯერ ერთში გააერთიანეთ');
        return;
    }
    if (groupIds.length === 1) {
        doMerge(checked.map(c => c.value), null);
        return;
    }
    document.getElementById('mgName').value = '';
    document.getElementById('mgErr').style.display = 'none';
    document.getElementById('mgOverlay').classList.add('open');
}
function closeMergeModal() {
    document.getElementById('mgOverlay').classList.remove('open');
}
function mgOverlayClick(e) {
    if (e.target === document.getElementById('mgOverlay')) closeMergeModal();
}
function saveMerge() {
    const name = document.getElementById('mgName').value.trim();
    if (!name) {
        const errEl = document.getElementById('mgErr');
        errEl.textContent = 'სახელი საჭიროა';
        errEl.style.display = 'block';
        return;
    }
    doMerge([...document.querySelectorAll('.q-item:checked')].map(c => c.value), name);
}
async function doMerge(ids, name) {
    try {
        const r = await fetch('{{ route('admin.questions.merge') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids, name }),
        });
        const data = await r.json();
        if (!r.ok) {
            alert(data.error || 'შეცდომა — არ შეინახა');
            return;
        }
        window.location.reload();
    } catch (e) {
        alert('დაკავშირების შეცდომა');
    }
}
function ungroupTemplate(id) {
    if (!confirm('მოხსნა ჯგუფიდან?')) return;
    fetch(`/admin/questions/${id}/ungroup`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    }).then(() => window.location.reload());
}

function clearAll() {
    document.querySelectorAll('.q-item').forEach(c => c.checked = false);
    checkAll.checked = false;
    updateBar();
}

checkAll.addEventListener('change', function () {
    document.querySelectorAll('.q-item').forEach(c => { c.checked = this.checked; });
    updateBar();
});

function toggleAllTopicGroups(expand) {
    document.querySelectorAll('.topic-group').forEach(g => g.classList.toggle('collapsed', !expand));
}
</script>
@endsection
