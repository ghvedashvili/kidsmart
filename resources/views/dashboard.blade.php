@extends('layouts.app')

@section('content')
<style>
    body {
        background: transparent !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        overflow-x: hidden;
        min-height: 100dvh;
    }
    .dash-hero {
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        position: relative;
        overflow-x: hidden;
        padding: 40px 24px 60px;
        gap: 20px;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        inset: -100%;
        background-image: radial-gradient(rgba(0,0,0,0.07) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: gridMove 18s linear infinite;
        pointer-events: none;
    }
    @keyframes gridMove {
        0%   { transform: translate(0,0); }
        100% { transform: translate(28px,28px); }
    }
    .dash-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        width: 100%;
        max-width: 480px;
        text-align: center;
    }
    .dash-greeting {
        font-family: 'Goldman', monospace;
        font-size: clamp(1rem, 4vw, 1.4rem);
        color: #111;
        letter-spacing: 0.06em;
    }



    .children-section { width: 100%; }
    .section-label {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 12px; text-align: left;
    }
    .child-card {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 16px 18px; margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        text-decoration: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .child-card:hover { border-color: #bbb; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .child-info { flex: 1; text-align: left; }
    .child-name { font-family: 'Goldman', monospace; font-size: 0.88rem; color: #111; letter-spacing: 0.04em; margin-bottom: 5px; }
    .child-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .ctag {
        font-family: 'Goldman', monospace; font-size: 0.6rem; color: #ccc;
        border: 1px solid #ebebeb; border-radius: 3px; padding: 2px 7px; letter-spacing: 0.04em;
    }
    .ctag.set { color: #555; border-color: #ccc; }
    .child-arrow { color: #ccc; font-size: 0.9rem; }
    .add-child-btn {
        width: 100%; background: #fff; border: 1px dashed #d0d0d0; border-radius: 10px;
        font-family: 'Goldman', monospace; font-size: 0.75rem; color: #aaa;
        padding: 13px; cursor: pointer; letter-spacing: 0.06em;
        transition: all 0.2s; margin-bottom: 12px;
    }
    .add-child-btn:hover { border-color: #999; color: #555; background: #fafafa; }

    /* Modal */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        backdrop-filter: blur(4px); z-index: 100;
        display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .mbox {
        background: #fff; border-radius: 16px; padding: 28px 24px;
        width: 100%; max-width: 440px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        animation: modalIn 0.25s cubic-bezier(0.175,0.885,0.32,1.275);
    }
    @keyframes modalIn { from { transform: scale(0.92); opacity:0; } to { transform: scale(1); opacity:1; } }
    .modal-title {
        font-family: 'Goldman', monospace; font-size: 0.9rem; color: #111;
        letter-spacing: 0.08em; margin-bottom: 22px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-close { background: none; border: none; color: #bbb; font-size: 1.1rem; cursor: pointer; padding: 0; }
    .modal-close:hover { color: #555; }
    .mlbl {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #999;
        letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px;
    }
    .mlbl span { color: #e74c3c; margin-left: 2px; }
    .minput {
        width: 100%; background: #fafafa; border: 1px solid #e8e8e8; border-radius: 8px;
        font-family: 'Goldman', monospace; font-size: 0.82rem; color: #111;
        padding: 10px 14px; outline: none; margin-bottom: 16px; box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .minput:focus { border-color: #aaa; background: #fff; }
    .mrow { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .mchip {
        background: #f5f5f5; border: 1.5px solid #e8e8e8; border-radius: 6px;
        font-family: 'Goldman', monospace; font-size: 0.7rem; color: #888;
        padding: 6px 14px; cursor: pointer; transition: all 0.15s; user-select: none;
    }
    .mchip:hover { border-color: #bbb; color: #444; }
    .mchip.sel { background: #111; border-color: #111; color: #fff; }
    .mchip.sel-green { background: #e8f5e9; border-color: #81c784; color: #2e7d32; }
    .msave {
        width: 100%; background: #111; border: none; border-radius: 8px;
        font-family: 'Goldman', monospace; font-size: 0.82rem; color: #fff;
        padding: 13px; cursor: pointer; letter-spacing: 0.06em; margin-top: 6px;
        transition: background 0.2s;
    }
    .msave:hover { background: #333; }
    .msave-danger {
        background: transparent; border: 1px solid #ddd; color: #888;
    }
    .msave-danger:hover { border-color: #e74c3c; color: #e74c3c; background: transparent; }
    .merr { font-family: 'Goldman', monospace; font-size: 0.65rem; color: #e74c3c; margin-top: -12px; margin-bottom: 10px; }
    .child-code-badge {
        font-family: 'Goldman', monospace; font-size: 0.75rem; color: #111;
        background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 4px;
        padding: 2px 10px; letter-spacing: 0.14em; cursor: pointer; transition: background 0.2s;
    }
    .child-code-badge:hover { background: #ebebeb; }
    .child-actions { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
    .caction {
        font-family: 'Goldman', monospace; font-size: 0.62rem; color: #bbb;
        border: 1px solid #ebebeb; border-radius: 4px; padding: 5px 10px;
        text-decoration: none; letter-spacing: 0.04em; transition: all 0.2s; white-space: nowrap;
    }
    .caction:hover { color: #333; border-color: #aaa; }
    .caction.primary { color: #4f46e5; border-color: #c7d2fe; background: #eef2ff; }
    .caction.primary:hover { background: #e0e7ff; border-color: #a5b4fc; }
    .no-children {
        font-family: 'Goldman', monospace; font-size: 0.72rem; color: #ccc;
        text-align: center; padding: 20px;
        border: 1px dashed #e0e0e0; border-radius: 8px; letter-spacing: 0.06em;
    }

    .notif-btn {
        display: inline-flex; align-items: center; gap: 10px; padding: 11px 28px;
        font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em;
        color: #888; background: transparent; border: 1px solid #ddd; border-radius: 4px;
        cursor: pointer; transition: color 0.2s, border-color 0.2s;
    }
    .notif-btn:hover { color: #333; border-color: #aaa; }
    .notif-btn.on { color: #111; border-color: #111; }
    .flash { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #2ecc71; letter-spacing: 0.06em; }
    .flash-err { font-family: 'Goldman', monospace; font-size: 0.72rem; color: #e74c3c; letter-spacing: 0.06em; }
    .test-btn {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 16px 36px; border-radius: 10px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white; font-family: 'Goldman', monospace;
        font-size: 0.88rem; letter-spacing: 0.08em;
        text-decoration: none; transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(79,70,229,0.3);
    }
    .test-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,0.4); }
    .test-pending {
        background: #fff; border: 1px solid #e8e8e8; border-radius: 10px;
        padding: 18px 20px; width: 100%; text-align: center;
    }
    .test-pending-label { font-family: 'Goldman', monospace; font-size: 0.65rem; color: #bbb; letter-spacing: 0.1em; margin-bottom: 10px; }
    .test-pending-score { font-family: 'Goldman', monospace; font-size: 1.4rem; color: #111; letter-spacing: 0.06em; }
    .test-pending-sub { font-family: 'Goldman', monospace; font-size: 0.62rem; color: #ccc; margin-top: 4px; }
</style>

<div class="dash-hero">
    <div class="dash-inner">

        @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
        @endif
        @if(session('test_error'))
        <div class="flash-err">{{ session('test_error') }}</div>
        @endif
        @if(session('test_done'))
        <div class="flash">{{ session('test_done') }}</div>
        @endif

        <div class="dash-greeting">გამარჯობა, {{ auth()->user()->name }}</div>

        {{-- მშობლის ხედი --}}
        @if(auth()->user()->role === 'parent')
        @php $children = auth()->user()->children()->with(['childSetting.grade','themes','topics'])->get(); @endphp



        @if(session('child_added'))
        <div class="flash">{{ session('child_added') }}</div>
        @endif

        <div class="children-section">
            <div class="section-label">შვილები · {{ $children->count() }}</div>

            <button type="button" class="add-child-btn" onclick="document.getElementById('addChildModal').classList.add('open')">
                + შვილის დამატება
            </button>
            @forelse($children as $child)
            @php
                $s = $child->childSetting;
                $todayDone = $child->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
            @endphp
            <div class="child-card" style="cursor:default;">
                <div class="child-info">
                    <div class="child-name">{{ $child->name }}</div>
                    <div class="child-tags">
                        @if($s?->grade)
                            <span class="ctag set">{{ $s->grade->name }}</span>
                        @else
                            <span class="ctag">კლასი —</span>
                        @endif
                        @if($s)
                            <span class="ctag set">დონე {{ $s->difficulty }}</span>
                            <span class="ctag set">დღეს {{ $todayDone }}/{{ $s->tests_per_week }}</span>
                        @endif
                        @foreach($child->themes->take(2) as $theme)
                            <span class="ctag set">{{ $theme->icon }} {{ $theme->name }}</span>
                        @endforeach
                        @if($child->themes->count() > 2)
                            <span class="ctag">+{{ $child->themes->count() - 2 }}</span>
                        @endif
                    </div>
                </div>
                <div class="child-actions">
                    @if($child->child_code)
                    <span class="child-code-badge" onclick="copyChildCode(this, '{{ $child->child_code }}')"
                        title="კოდის კოპირება">{{ $child->child_code }}</span>
                    @endif
                    <a href="{{ route('child.stats', $child) }}" class="caction primary">სტატისტიკა</a>
                    <button type="button" class="caction" onclick="document.getElementById('editChildModal{{ $child->id }}').classList.add('open')">⚙</button>
                </div>
            </div>
            @empty
            <div class="no-children">
                ბავშვი ჯერ არ დარეგისტრირებულა<br>
                <span style="font-size:0.62rem;color:#ccc;margin-top:4px;display:block;">კოდი გაუზიარე შვილს</span>
            </div>
            @endforelse
        </div>

        {{-- Edit modals (one per child) --}}
        @foreach($children as $child)
        @php
            $es = $child->childSetting;
            $eThemeIds = $child->themes->pluck('id')->toArray();
            $eTopicIds = $child->topics->pluck('id')->toArray();
            $eGroupedTopics = $topics->groupBy(fn($t) => $t->grade?->name ?? '—');
        @endphp
        <div id="editChildModal{{ $child->id }}" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
            <div class="mbox">
                <form method="POST" action="{{ route('child.settings.update', $child) }}">
                    @csrf @method('PUT')
                    <div class="modal-title">
                        {{ $child->name }}-ის პარამეტრები
                        <button type="button" class="modal-close" onclick="document.getElementById('editChildModal{{ $child->id }}').classList.remove('open')">✕</button>
                    </div>

                    <div class="mlbl">სახელი</div>
                    <input type="text" name="name" class="minput" value="{{ $child->name }}" maxlength="50" autocomplete="off">

                    <div class="mlbl">კლასი</div>
                    <div class="mrow">
                        @foreach($grades as $grade)
                        <label class="mchip {{ $es?->grade_id == $grade->id ? 'sel' : '' }}"
                            onclick="chipSingle(this,'egid{{ $child->id }}','{{ $grade->id }}')">{{ $grade->name }}</label>
                        @endforeach
                    </div>
                    <input type="hidden" name="grade_id" id="egid{{ $child->id }}" value="{{ $es?->grade_id }}">

                    <div class="mlbl">სირთულე</div>
                    <div class="mrow">
                        @for($i=1; $i<=5; $i++)
                        <label class="mchip {{ ($es?->difficulty ?? 1) == $i ? 'sel' : '' }}"
                            onclick="chipSingle(this,'edif{{ $child->id }}','{{ $i }}')">{{ $i }}</label>
                        @endfor
                    </div>
                    <input type="hidden" name="difficulty" id="edif{{ $child->id }}" value="{{ $es?->difficulty ?? 1 }}">

                    <div class="mlbl">ტესტი დღეში</div>
                    <div class="mrow">
                        @for($i=1; $i<=7; $i++)
                        <label class="mchip {{ ($es?->tests_per_week ?? 1) == $i ? 'sel' : '' }}"
                            onclick="chipSingle(this,'etpw{{ $child->id }}','{{ $i }}')">{{ $i }}</label>
                        @endfor
                    </div>
                    <input type="hidden" name="tests_per_week" id="etpw{{ $child->id }}" value="{{ $es?->tests_per_week ?? 1 }}">

                    @if($themes->count())
                    <div class="mlbl">თემატიკა</div>
                    <div class="mrow">
                        @foreach($themes as $theme)
                        <label class="mchip {{ in_array($theme->id, $eThemeIds) ? 'sel' : '' }}"
                            onclick="chipMulti(this,'theme_ids[]','{{ $theme->id }}')">{{ $theme->icon }} {{ $theme->name }}</label>
                        @endforeach
                    </div>
                    @foreach($themes as $theme)
                        @if(in_array($theme->id, $eThemeIds))
                        <input type="hidden" name="theme_ids[]" value="{{ $theme->id }}">
                        @endif
                    @endforeach
                    @endif

                    @if($topics->count())
                    <div class="mlbl">საყვარელი თემები</div>
                    @foreach($eGroupedTopics as $gradeName => $gradeTopics)
                    <div style="font-family:'Goldman',monospace;font-size:0.58rem;color:#bbb;letter-spacing:0.1em;margin-bottom:4px;margin-top:4px;">{{ $gradeName }}</div>
                    <div class="mrow" style="margin-bottom:8px;">
                        @foreach($gradeTopics as $topic)
                        <label class="mchip {{ in_array($topic->id, $eTopicIds) ? 'sel' : '' }}"
                            onclick="chipMulti(this,'topic_ids[]','{{ $topic->id }}')">{{ $topic->name }}</label>
                        @endforeach
                    </div>
                    @foreach($gradeTopics as $topic)
                        @if(in_array($topic->id, $eTopicIds))
                        <input type="hidden" name="topic_ids[]" value="{{ $topic->id }}">
                        @endif
                    @endforeach
                    @endforeach
                    @endif

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px;">
                        <button type="submit" class="msave" style="margin-top:0;">შენახვა</button>
                        <button type="button" class="msave msave-danger" style="margin-top:0;"
                            onclick="confirmDeleteChild({{ $child->id }}, '{{ addslashes($child->name) }}')">
                            წაშლა
                        </button>
                    </div>
                </form>

                <form id="deleteChildForm{{ $child->id }}" method="POST"
                    action="{{ route('child.destroy', $child) }}" style="display:none;">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
        @endforeach

        @endif

        {{-- ბავშვის ხედი --}}
        @if(auth()->user()->role === 'child')
        @php
            $activeTest    = auth()->user()->tests()->whereNull('completed_at')->latest()->first();
            $lastCompleted = auth()->user()->tests()->whereNotNull('completed_at')->latest()->first();
            $setting       = auth()->user()->childSetting;
            $required      = $setting?->tests_per_week ?? 0;
            $todayCount    = auth()->user()->tests()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
            $doneToday     = $required > 0 && $todayCount >= $required && !$activeTest;
        @endphp

        @if($activeTest)
        <a href="{{ route('test.show', $activeTest) }}" class="test-btn">
            📝 ტესტი გელოდება →
        </a>
        @elseif(!$setting || !$setting->grade_id)
        <div style="text-align:center;padding:20px;">
            <div style="font-family:'Goldman',monospace;font-size:1.6rem;margin-bottom:10px;">⏳</div>
            <div style="font-family:'Goldman',monospace;font-size:0.8rem;color:#555;letter-spacing:0.06em;line-height:1.8;">მშობელს ჯერ<br>დავალება არ დაუყენებია</div>
        </div>
        @elseif($doneToday)
        <div style="text-align:center;">
            <div style="font-family:'Goldman',monospace;font-size:2rem;margin-bottom:8px;">✓</div>
            <div style="font-family:'Goldman',monospace;font-size:0.85rem;color:#111;letter-spacing:0.06em;">დღე დასრულდა!</div>
            <div style="font-family:'Goldman',monospace;font-size:0.62rem;color:#bbb;margin-top:4px;letter-spacing:0.06em;">{{ $todayCount }} / {{ $required }} ტესტი შეასრულე</div>
        </div>
        @else
        <div style="text-align:center;">
            @if($required > 0)
            <div style="font-family:'Goldman',monospace;font-size:0.62rem;color:#bbb;letter-spacing:0.1em;margin-bottom:10px;">
                დღეს: {{ $todayCount }} / {{ $required }}
            </div>
            @endif
            <a href="{{ route('test.start') }}" class="test-btn">
                ▶ ტესტის დაწყება
            </a>
        </div>
        @endif

        @if($lastCompleted)
        <div class="test-pending">
            <div class="test-pending-label">ბოლო ტესტი</div>
            <div class="test-pending-score">
                {{ $lastCompleted->correct_count }} / {{ $lastCompleted->total_questions }}
                @php $pct = round($lastCompleted->correct_count / $lastCompleted->total_questions * 100); @endphp
                <span style="font-size:0.8rem;color:#bbb;"> · {{ $pct }}%</span>
            </div>
            <div class="test-pending-sub">{{ $lastCompleted->completed_at->diffForHumans() }}</div>
        </div>
        @endif
        @endif


        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.panel') }}" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;">
            admin →
        </a>
        @endif


    </div>
</div>

{{-- Add Child Modal --}}
@if(auth()->user()->role === 'parent')
<div id="addChildModal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mbox">
        <form method="POST" action="{{ route('child.store') }}">
            @csrf
            <div class="modal-title">
                შვილის დამატება
                <button type="button" class="modal-close" onclick="document.getElementById('addChildModal').classList.remove('open')">✕</button>
            </div>

            {{-- Name --}}
            <div class="mlbl">სახელი <span>*</span></div>
            <input type="text" name="name" class="minput" placeholder="სახელი"
                value="{{ old('name') }}" required maxlength="50" autocomplete="off">
            @error('name')<div class="merr">{{ $message }}</div>@enderror

            {{-- Grade --}}
            <div class="mlbl">კლასი <span>*</span></div>
            <div class="mrow" id="gradeRow">
                @foreach($grades as $grade)
                <label class="mchip {{ old('grade_id') == $grade->id ? 'sel' : '' }}"
                    onclick="chipSingle(this,'grade_id_input','{{ $grade->id }}')">{{ $grade->name }}</label>
                @endforeach
            </div>
            <input type="hidden" name="grade_id" id="grade_id_input" value="{{ old('grade_id') }}">
            @error('grade_id')<div class="merr">{{ $message }}</div>@enderror

            {{-- Difficulty --}}
            <div class="mlbl">სირთულე</div>
            <div class="mrow" id="diffRow">
                @for($i=1; $i<=5; $i++)
                <label class="mchip {{ old('difficulty', 1) == $i ? 'sel' : '' }}"
                    onclick="chipSingle(this,'difficulty_input','{{ $i }}')">{{ $i }}</label>
                @endfor
            </div>
            <input type="hidden" name="difficulty" id="difficulty_input" value="{{ old('difficulty', 1) }}">

            {{-- Tests per week --}}
            <div class="mlbl">ტესტი დღეში</div>
            <div class="mrow" id="tpwRow">
                @for($i=1; $i<=7; $i++)
                <label class="mchip {{ old('tests_per_week', 1) == $i ? 'sel' : '' }}"
                    onclick="chipSingle(this,'tpw_input','{{ $i }}')">{{ $i }}</label>
                @endfor
            </div>
            <input type="hidden" name="tests_per_week" id="tpw_input" value="{{ old('tests_per_week', 1) }}">

            {{-- Themes --}}
            @if($themes->count())
            <div class="mlbl">თემატიკა</div>
            <div class="mrow">
                @foreach($themes as $theme)
                @php $themeOld = old('theme_ids', []); @endphp
                <label class="mchip {{ in_array($theme->id, $themeOld) ? 'sel' : '' }}"
                    onclick="chipMulti(this,'theme_ids[]','{{ $theme->id }}')">{{ $theme->icon }} {{ $theme->name }}</label>
                @endforeach
            </div>
            @foreach($themes as $theme)
                @if(in_array($theme->id, old('theme_ids', [])))
                <input type="hidden" name="theme_ids[]" value="{{ $theme->id }}" class="theme-hidden">
                @endif
            @endforeach
            @endif

            {{-- Topics --}}
            @if($topics->count())
            <div class="mlbl">საყვარელი თემები</div>
            @php $groupedTopics = $topics->groupBy(fn($t) => $t->grade?->name ?? '—'); @endphp
            @foreach($groupedTopics as $gradeName => $gradeTopics)
            <div style="font-family:'Goldman',monospace;font-size:0.58rem;color:#bbb;letter-spacing:0.1em;margin-bottom:4px;margin-top:4px;">{{ $gradeName }}</div>
            <div class="mrow" style="margin-bottom:8px;">
                @foreach($gradeTopics as $topic)
                @php $topicOld = old('topic_ids', []); @endphp
                <label class="mchip {{ in_array($topic->id, $topicOld) ? 'sel' : '' }}"
                    onclick="chipMulti(this,'topic_ids[]','{{ $topic->id }}')">{{ $topic->name }}</label>
                @endforeach
            </div>
            @foreach($gradeTopics as $topic)
                @if(in_array($topic->id, old('topic_ids', [])))
                <input type="hidden" name="topic_ids[]" value="{{ $topic->id }}" class="topic-hidden">
                @endif
            @endforeach
            @endforeach
            @endif

            <button type="submit" class="msave">+ შვილის შენახვა</button>
        </form>
    </div>
</div>
@endif

<script>


function confirmDeleteChild(childId, childName) {
    Swal.fire({
        title: childName + '-ის წაშლა?',
        html: '<span style="color:#c0392b;font-size:0.9rem;">⚠️ წაიშლება ბავშვის მთელი ისტორია — ყველა ტესტი, შედეგი და პარამეტრი.</span><br><span style="font-size:0.82rem;color:#888;">ეს მოქმედება ვერ გაუქმდება.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'დიახ, წავშალო',
        cancelButtonText: 'გაუქმება',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deleteChildForm' + childId).submit();
        }
    });
}

function copyChildCode(el, code) {
    navigator.clipboard.writeText(code).then(() => {
        const orig = el.textContent;
        el.textContent = '✓';
        setTimeout(() => el.textContent = orig, 1500);
    });
}

function chipSingle(el, inputId, value) {
    el.closest('.mrow').querySelectorAll('.mchip').forEach(c => c.classList.remove('sel'));
    el.classList.add('sel');
    document.getElementById(inputId).value = value;
}

function chipMulti(el, name, value) {
    el.classList.toggle('sel');
    const existing = el.closest('form').querySelector('input[type="hidden"][name="' + name + '"][value="' + value + '"]');
    if (el.classList.contains('sel')) {
        if (!existing) {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name; inp.value = value;
            el.closest('form').appendChild(inp);
        }
    } else {
        if (existing) existing.remove();
    }
}

@if($errors->hasAny(['name','grade_id','difficulty','tests_per_week','theme_ids','topic_ids']))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addChildModal').classList.add('open');
});
@endif
</script>
@endsection
