@extends('layouts.app')
{{-- მასწავლებლის კითხვის ფორმა — კომპაქტური: მარცხნივ ფორმა, მარჯვნივ „ასე ნახავს ბავშვი“ --}}
@section('content')
@include('teacher.partials.styles')
@php
    $__edit = $question->exists;
    $__topicId = old('topic_id', $question->topic_id);
    $__gradeId = old('grade_id', optional($grades->first(fn($g) => $g->topics->contains('id', $__topicId)))->id ?? $grades->first()?->id);
    $__mode = old('mode', $td['mode'] ?? ($question->answer_type === 'numeric_input' ? 'input' : 'choice'));
    $__wrong = old('wrong', $td['wrong'] ?? []);
    $__auto = old('auto', $__edit ? (int) ($td['auto'] ?? 1) : 1);
    $__gradeData = $grades->mapWithKeys(fn($g) => [$g->id => ['max' => (int) ($g->max_level ?: 3), 'topics' => $g->topics->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()]]);
@endphp
<style>
    .tqf { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 18px; align-items: start; }
    @media (max-width: 1050px) { .tqf { grid-template-columns: 1fr; } .tqf-side { order: -1; } }
    .tqf-main { padding: 0; overflow: hidden; }
    .tqf-sec { padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; }
    .tqf-sec + .tqf-sec { border-top: 1px solid #F1EFEB; }
    .tqf-h { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .tqf-h > b { font-size: 15px; font-weight: 800; color: #2B2622; display: flex; align-items: center; gap: 8px; }
    .tqf-h > b i { color: #E2590F; }
    .tqf-opt { font-weight: 600; font-size: 12px; color: #8A8178; }
    .tqf-lbl { display: block; font-size: 12px; font-weight: 700; color: #6B625A; margin: 0 0 4px; }
    .tqf-in, .tqf-ta, .tqf-sel { width: 100%; border: 1.5px solid #E6E2DC; border-radius: 12px; padding: 9px 12px; font-size: 15px; font-family: inherit; color: #2B2622; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; }
    .tqf-in:focus, .tqf-ta:focus, .tqf-sel:focus { border-color: #E2590F; box-shadow: 0 0 0 3px rgba(226, 89, 15, .12); }
    .tqf-ta { min-height: 104px; line-height: 1.55; resize: vertical; font-size: 16px; }
    .tqf-ta.sm { min-height: 60px; font-size: 14px; }
    .tqf-ans { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tqf-ans .tqf-in { width: 160px; font-weight: 800; }
    .tqf-row4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    @media (max-width: 760px) { .tqf-row4 { grid-template-columns: 1fr 1fr; } }
    .tqf-seg { display: inline-flex; background: #F4F2EE; border-radius: 12px; padding: 3px; gap: 3px; flex-wrap: wrap; }
    .tqf-seg label { margin: 0; cursor: pointer; }
    .tqf-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .tqf-seg span { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 9px; font-size: 13px; font-weight: 800; color: #6B625A; }
    .tqf-seg input:checked + span { background: #fff; color: #C24A0A; box-shadow: 0 1px 3px rgba(43, 38, 34, .12); }
    .tqf-seg input:focus-visible + span { outline: 2px solid #FFC49A; }
    .tqf-wrong { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    @media (max-width: 560px) { .tqf-wrong { grid-template-columns: 1fr; } }
    .tqf-wrong .tqf-in { font-size: 14px; }
    .tqf-check { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #4F473F; cursor: pointer; margin: 0; }
    .tqf-check input { width: 16px; height: 16px; accent-color: #E2590F; }
    .tqf-mini-sw { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #6B625A; cursor: pointer; margin: 0; }
    .tqf-mini-sw input { position: absolute; opacity: 0; width: 0; height: 0; }
    .tqf-mini-sw i { width: 34px; height: 20px; border-radius: 99px; background: #D9D3CB; position: relative; transition: background .15s; font-style: normal; }
    .tqf-mini-sw i::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: left .15s; }
    .tqf-mini-sw input:checked + i { background: #E2590F; }
    .tqf-mini-sw input:checked + i::after { left: 16px; }
    .tqf-mini-sw input:focus-visible + i { outline: 2px solid #FFC49A; outline-offset: 2px; }
    .tqf-side { position: sticky; top: 14px; display: flex; flex-direction: column; gap: 12px; }
    @media (max-width: 1050px) { .tqf-side { position: static; } }
    .tqf-side .tq-kid { padding: 22px 12px 12px; }
    .tqf-side .tq-ktext { font-size: 15px; }
    .tqf-side .tq-kopt { min-height: 44px; font-size: 16px; border-width: 2px; }
    .tqf-vars { display: flex; flex-direction: column; gap: 10px; }
    .tqf-vars .tq-kcard { padding: 12px; }
    .tqf-vars .tq-ktext { font-size: 13px; }
    .tqf-vars .tq-kopt { min-height: 34px; font-size: 14px; border-width: 1.5px; border-radius: 12px; }
    .tqf-comment { border-radius: 14px; padding: 12px 14px; background: #FFF1C2; color: #5A3E00; display: flex; gap: 10px; font-size: 14px; }
    .tqf-submit { position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-end; background: rgba(247, 246, 243, .94); backdrop-filter: blur(6px); padding: 10px 0; }
    @media (max-width: 900px) { .tqf-submit { bottom: 64px; } }
    .tq-note { font-size: 12px; border-radius: 10px; padding: 8px 10px; background: #FFF1C2; color: #6B4A00; }
    .tq-err { font-size: 13px; border-radius: 10px; padding: 8px 10px; background: #FDE2E2; color: #A12626; font-weight: 700; }
    /* ოსტატი (კომპაქტური) */
    .wz-empty { font-size: 13px; color: #8A8178; background: #F7F6F3; border-radius: 12px; padding: 10px 12px; }
    .wz { display: flex; flex-direction: column; gap: 8px; }
    .wz-tpl { font-size: 13px; line-height: 1.9; color: #4F473F; background: #F7F6F3; border-radius: 10px; padding: 8px 10px; white-space: pre-line; }
    .wz-v { display: inline-flex; align-items: center; gap: 3px; background: #E2590F; color: #fff; border-radius: 7px; padding: 0 6px; font-weight: 800; font-size: 12px; line-height: 1.6; }
    .wz-v small { font-weight: 600; opacity: .85; font-size: 10px; }
    .wz-t { display: inline-block; background: #fff; color: #4F473F; border: 1px dashed #D9D3CB; border-radius: 7px; padding: 0 6px; font-weight: 700; font-size: 12px; line-height: 1.6; }
    .wz-f { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .wz-f input { flex: 1 1 160px; min-width: 0; border: 1.5px solid #E6E2DC; border-radius: 10px; padding: 8px 10px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 15px; font-weight: 800; color: #2B2622; }
    .wz-f input:focus { outline: none; border-color: #E2590F; box-shadow: 0 0 0 3px rgba(226, 89, 15, .12); }
    .wz-res { font-size: 12px; font-weight: 800; }
    .wz-res.ok { color: #14612F; } .wz-res.no { color: #A12626; }
    .wz-chips { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
    .wz-chip { border: 1px solid #ECE9E4; background: #fff; border-radius: 999px; padding: 2px 9px; font-size: 12px; cursor: pointer; color: #2B2622; font-family: inherit; }
    .wz-chip b { color: #C24A0A; }
    .wz-chip.op { font-weight: 900; min-width: 28px; }
    .wz-chip.alt { font-family: ui-monospace, Menlo, Consolas, monospace; background: #F7F6F3; }
    .wz-chip:hover { border-color: #E2590F; }
    .wz-row { display: grid; grid-template-columns: auto 1fr auto auto auto auto; gap: 6px; align-items: center; font-size: 12px; color: #6B625A; }
    .wz-row input { width: 60px; border: 1.5px solid #E6E2DC; border-radius: 8px; padding: 4px 6px; font-size: 13px; font-weight: 800; text-align: center; color: #2B2622; }
    .wz-row input:focus { outline: none; border-color: #E2590F; }
    .wz-changed { font-size: 11px; font-weight: 800; color: #C24A0A; }
    details.wz-more summary { cursor: pointer; font-size: 12px; font-weight: 800; color: #6B625A; }
</style>

<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>{{ $__edit ? 'კითხვის შესწორება' : 'ახალი კითხვა' }}</h1>
            <p>დაწერე კითხვა და პასუხი — დანარჩენს ოსტატი შეგითავაზებს.</p>
        </div>
        <a href="{{ route('teacher.questions.index') }}" class="tq-btn tq-btn-ghost tq-btn-sm">← ჩემი კითხვები</a>
    </div>

    @if($__edit && $question->review_status === 'rejected' && $question->review_comment)
    <div class="tqf-comment" role="note">
        <i class="bi bi-chat-left-text" style="font-size:18px;"></i>
        <div><b>ადმინის კომენტარი:</b> {{ $question->review_comment }}</div>
    </div>
    @endif
    @if($errors->any())<div class="tq-flash err" role="alert">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ $__edit ? route('teacher.questions.update', $question) : route('teacher.questions.store') }}" id="tqForm">
        @csrf
        @if($__edit) @method('PUT') @endif
        <div class="tqf">
            <div class="tq-card tqf-main">
                {{-- 1. კითხვა + სწორი პასუხი --}}
                <section class="tqf-sec">
                    <div class="tqf-h"><b><i class="bi bi-pencil"></i>კითხვა</b><span class="tqf-opt">რიცხვები — ციფრით ან სიტყვით</span></div>
                    <textarea name="text" id="tqText" class="tqf-ta" required maxlength="2000" aria-label="კითხვის ტექსტი" placeholder="მაგ.: ნიკას ჰქონდა 15 ვაშლი. 6 ვაშლი შეჭამა. რამდენი ვაშლი დარჩა?">{{ old('text', $td['text'] ?? $question->template_text) }}</textarea>
                    <div class="tqf-ans">
                        <label class="tqf-lbl" for="tqAnswer" style="margin:0;">სწორი პასუხი</label>
                        <input id="tqAnswer" name="answer" class="tqf-in" required maxlength="200" value="{{ old('answer', $td['answer'] ?? '') }}" placeholder="მაგ. 9">
                    </div>
                </section>

                {{-- 2. ოსტატის ფორმულა --}}
                <section class="tqf-sec">
                    <div class="tqf-h">
                        <b><i class="bi bi-magic"></i>სწორი პასუხის ფორმულა <span class="wz-changed" id="wzChanged" hidden>· შენ შეცვალე</span></b>
                        <label class="tqf-mini-sw" title="ბავშვი კითხვას ყოველ ჯერზე სხვა რიცხვებით ნახავს">
                            <input type="hidden" name="auto" value="0">
                            <input type="checkbox" name="auto" value="1" id="tqAuto" @checked($__auto)><i></i>რიცხვები ყოველ ჯერზე იცვლება
                        </label>
                    </div>
                    <div class="wz-empty" id="wzEmpty">ჩაწერე კითხვა და სწორი პასუხი — ოსტატი ფორმულას თვითონ დათვლის.</div>
                    <div class="wz" id="tqWiz" hidden aria-label="ოსტატის შეთავაზება">
                        <div class="wz-tpl" id="wzTpl"></div>
                        <span class="tqf-opt" id="wzFvNote" hidden></span>
                        <div class="wz-f">
                            <input id="wzF" autocomplete="off" spellcheck="false" aria-label="სწორი პასუხის ფორმულა">
                            <span class="wz-res" id="wzRes" aria-live="polite"></span>
                        </div>
                        <div class="wz-chips" id="wzVarChips"></div>
                        <div class="wz-chips" id="wzAlts"></div>
                        <details class="wz-more" id="wzRangeSec">
                            <summary>რა საზღვრებში იცვლება რიცხვები</summary>
                            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;" id="wzRanges"></div>
                        </details>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" class="tq-btn tq-btn-soft tq-btn-sm" id="wzApply"><i class="bi bi-check2"></i> შემოწმება</button>
                            <button type="button" class="tq-btn tq-btn-ghost tq-btn-sm" id="wzReset" hidden><i class="bi bi-arrow-counterclockwise"></i> ოსტატის ვარიანტი</button>
                        </div>
                    </div>
                    <div id="tqOv" hidden></div>
                </section>

                {{-- 3. როგორ უპასუხებს ბავშვი --}}
                <section class="tqf-sec">
                    <div class="tqf-h">
                        <b><i class="bi bi-ui-radios-grid"></i>როგორ უპასუხებს ბავშვი</b>
                        <div class="tqf-seg" role="radiogroup" aria-label="პასუხის ტიპი">
                            <label><input type="radio" name="mode" value="choice" @checked($__mode === 'choice')><span><i class="bi bi-grid"></i>აირჩევს 4 პასუხიდან</span></label>
                            <label><input type="radio" name="mode" value="input" @checked($__mode === 'input')><span><i class="bi bi-input-cursor-text"></i>ჩაწერს პასუხს</span></label>
                        </div>
                    </div>
                    <div id="tqWrongBox">
                        <span class="tqf-lbl">არასწორი პასუხები <span class="tqf-opt">— არასავალდებულო, სისტემა თვითონაც შეარჩევს</span></span>
                        <div class="tqf-wrong">
                            @for($__i = 0; $__i < 3; $__i++)
                            <input name="wrong[]" class="tqf-in tq-wrong-in" maxlength="200" value="{{ $__wrong[$__i] ?? '' }}" placeholder="არასწორი {{ $__i + 1 }}" aria-label="არასწორი პასუხი {{ $__i + 1 }}">
                            @endfor
                        </div>
                    </div>
                </section>

                {{-- 4. კლასი · თემა · დონე · თემატიკა --}}
                <section class="tqf-sec">
                    <div class="tqf-row4">
                        <div>
                            <label class="tqf-lbl" for="tqGrade">კლასი</label>
                            <select id="tqGrade" name="grade_id" class="tqf-sel">
                                @foreach($grades as $__g)<option value="{{ $__g->id }}" @selected($__gradeId == $__g->id)>{{ $__g->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="tqf-lbl" for="tqTopic">თემა</label>
                            <select id="tqTopic" name="topic_id" class="tqf-sel" required data-selected="{{ $__topicId }}"></select>
                        </div>
                        <div>
                            <label class="tqf-lbl" for="tqLevel">დონე</label>
                            <select id="tqLevel" name="difficulty" class="tqf-sel" data-selected="{{ old('difficulty', $question->difficulty ?: 1) }}"></select>
                        </div>
                        <div>
                            <label class="tqf-lbl" for="tqTheme">თემატიკა</label>
                            <select id="tqTheme" name="theme_id" class="tqf-sel">
                                @foreach($themes as $__th)<option value="{{ $__th->id }}" @selected(old('theme_id', $question->theme_id ?? $defaultThemeId) == $__th->id)>{{ $__th->icon }} {{ $__th->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <label class="tqf-check"><input type="checkbox" name="is_olympiad" value="1" @checked(old('is_olympiad', $question->is_olympiad))> ოლიმპიადის კითხვაა</label>
                </section>

                {{-- 5. მინიშნება --}}
                <section class="tqf-sec">
                    <label class="tqf-lbl" for="tqHint" style="margin:0;">მინიშნება <span class="tqf-opt">— არასავალდებულო, ბავშვი კითხვის ქვემოთ ნახავს</span></label>
                    <input id="tqHint" name="hint" class="tqf-in" maxlength="500" value="{{ old('hint', $td['hint'] ?? $question->hint_text) }}" placeholder="მაგ.: ჯერ გაიგე, რამდენი შეჭამა.">
                </section>

                {{-- 6. კომენტარი ადმინისთვის --}}
                <section class="tqf-sec">
                    <label class="tqf-lbl" for="tqComment" style="margin:0;">კომენტარი ადმინისთვის <span class="tqf-opt">— არასავალდებულო{{ $__edit && $question->review_status === 'rejected' ? ', მაგ. რა შეასწორე' : '' }}</span></label>
                    <textarea id="tqComment" name="teacher_comment" class="tqf-ta sm" maxlength="1000" placeholder="{{ $__edit && $question->review_status === 'rejected' ? 'მაგ.: პასუხი შევასწორე, როგორც მითხარით.' : 'მაგ.: ეს კითხვა ოლიმპიადის ტიპისაა, სურათის გარეშე.' }}">{{ old('teacher_comment') }}</textarea>
                </section>
            </div>

            {{-- ასე ნახავს ბავშვი --}}
            <aside class="tqf-side" aria-label="ასე ნახავს ბავშვი">
                <div class="tq-kid" style="display:flex;flex-direction:column;gap:8px;">
                    <span class="tq-kid-lbl">ასე ნახავს ბავშვი</span>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <p class="tqf-opt" id="tqMoreHelp" style="margin:0;font-size:12px;">ჩაწერე კითხვა და სწორი პასუხი — აქ გამოჩნდება.</p>
                        <button type="button" class="tq-btn tq-btn-ghost tq-btn-sm" id="tqMore" aria-label="განახლება" title="სხვა ვარიანტები"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                    <div class="tqf-vars" id="tqVariants" aria-live="polite"></div>
                </div>
            </aside>
        </div>

        <div class="tqf-submit">
            @if($__edit)<span class="tqf-opt" style="margin-right:auto;">შენახვისას კითხვა ხელახლა გაიგზავნება ადმინთან.</span>@endif
            <a href="{{ route('teacher.questions.index') }}" class="tq-btn tq-btn-ghost">გაუქმება</a>
            <button type="submit" class="tq-btn tq-btn-main"><i class="bi bi-send"></i> გაგზავნა ადმინთან</button>
        </div>
    </form>
</div>

<script>
(function () {
    var GRADES = @json($__gradeData);
    var form = document.getElementById('tqForm');
    var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };

    // ── კლასი → თემები და დონეები ──
    var gradeSel = document.getElementById('tqGrade'), topicSel = document.getElementById('tqTopic'), levelSel = document.getElementById('tqLevel');
    function fillGrade(first) {
        var g = GRADES[gradeSel.value] || { max: 3, topics: [] };
        var sel = first ? topicSel.getAttribute('data-selected') : topicSel.value;
        topicSel.innerHTML = '<option value="">— აირჩიე —</option>' + g.topics.map(function (t) {
            return '<option value="' + t.id + '"' + (String(t.id) === String(sel) ? ' selected' : '') + '>' + esc(t.name) + '</option>';
        }).join('');
        var lv = first ? levelSel.getAttribute('data-selected') : (levelSel.value || 1);
        if (+lv > g.max) lv = g.max;
        var names = { 1: 'მარტივი', 2: 'საშუალო', 3: 'რთული', 4: 'ძალიან რთული', 5: 'ექსპერტი' };
        var h = '';
        for (var i = 1; i <= g.max; i++) h += '<option value="' + i + '"' + (String(i) === String(lv) ? ' selected' : '') + '>' + i + ' · ' + names[i] + '</option>';
        levelSel.innerHTML = h;
    }
    gradeSel.addEventListener('change', function () { fillGrade(false); });
    fillGrade(true);

    // ── ასარჩევი / ჩასაწერი ──
    var wrongBox = document.getElementById('tqWrongBox');
    function mode() { return (form.querySelector('input[name="mode"]:checked') || {}).value || 'choice'; }
    function syncMode() { wrongBox.style.display = mode() === 'choice' ? '' : 'none'; }
    syncMode();

    // ── ცოცხალი წინასწარი ხედი (შენი რიცხვებით) ──
    var live = document.getElementById('tqLive');
    function renderCard(s, showAnswer) {
        var h = '<div class="tq-kcard"><div class="tq-ktext">' + (s.text ? esc(s.text) : '<span style="color:#B3A99F;">აქ გამოჩნდება კითხვის ტექსტი…</span>') + '</div>';
        if (s.hint) h += '<div class="tq-khint">💡 ' + esc(s.hint) + '</div>';
        if (s.input) h += '<div class="tq-kinput"><span>' + (showAnswer && s.correct ? esc(s.correct) : '?') + '</span></div>';
        else h += '<div class="tq-kopts">' + s.options.map(function (o) { return '<div class="tq-kopt' + (showAnswer && String(o) === String(s.correct) ? ' ok' : '') + '">' + esc(o || '…') + '</div>'; }).join('') + '</div>';
        return h + '</div>';
    }
    function liveRender() {
        if (!live) return; // ცალკე „ცოცხალი“ ბარათი აღარ გვაქვს — ქვემოთ სერვერის ვარიანტები ჩანს
        var ans = document.getElementById('tqAnswer').value.trim();
        var wrong = Array.prototype.map.call(form.querySelectorAll('.tq-wrong-in'), function (i) { return i.value.trim(); }).filter(Boolean);
        var opts = [ans || '…'].concat(wrong);
        while (opts.length < 4) opts.push(/^\d+$/.test(ans) ? '·' : '…');
        live.innerHTML = renderCard({ text: document.getElementById('tqText').value.trim(), hint: document.getElementById('tqHint').value.trim(), input: mode() === 'input', options: opts.slice(0, 4), correct: ans }, true)
            + '<div class="tq-kmeta">მწვანე — სწორი პასუხი (ბავშვი ვარიანტებს არეულად ნახავს)</div>';
    }

    // ── ოსტატის შეთავაზება: ფორმულა და საზღვრები (მასწავლებლის შესწორებები → ov) ──
    var wiz = document.getElementById('tqWiz'), ovBox = document.getElementById('tqOv');
    var ov = @json(['formula' => $td['formula'] ?? null, 'ranges' => (object) ($td['ranges'] ?? [])]);
    var wzData = null;
    var MF = {
        round: Math.round, floor: Math.floor, ceil: Math.ceil, abs: Math.abs, min: Math.min, max: Math.max, pow: Math.pow, sqrt: Math.sqrt,
        topsum: function (k) { var a = [].slice.call(arguments, 1).sort(function (x, y) { return y - x; }); return a.slice(0, k).reduce(function (s, x) { return s + x; }, 0); },
        botsum: function (k) { var a = [].slice.call(arguments, 1).sort(function (x, y) { return x - y; }); return a.slice(0, k).reduce(function (s, x) { return s + x; }, 0); },
        kmax: function (k) { var a = [].slice.call(arguments, 1).sort(function (x, y) { return y - x; }); return a[k - 1] || 0; },
        kmin: function (k) { var a = [].slice.call(arguments, 1).sort(function (x, y) { return x - y; }); return a[k - 1] || 0; },
        median: function () { var a = [].slice.call(arguments).sort(function (x, y) { return x - y; }), n = a.length; return n ? (n % 2 ? a[(n - 1) / 2] : Math.floor((a[n / 2 - 1] + a[n / 2]) / 2)) : 0; }
    };
    function fxEval(f, vals) {
        var bad = false;
        var e = String(f).replace(/[A-Za-z_][A-Za-z0-9_]*/g, function (w) {
            if (Object.prototype.hasOwnProperty.call(vals, w)) return '(' + Number(vals[w]) + ')';
            if (Object.prototype.hasOwnProperty.call(MF, w)) return 'MF.' + w;
            bad = w; return '0';
        });
        if (bad) return { err: '„' + bad + '“ — ასეთი ცვლადი არ არის' };
        try { var v = Function('MF', 'return (' + e + ');')(MF); return Number.isFinite(v) ? { v: v } : { err: 'ფორმულა დაუმთავრებელია' }; }
        catch (_) { return { err: 'ფორმულა დაუმთავრებელია' }; }
    }
    function ovActive() { return !!(ov.formula || Object.keys(ov.ranges || {}).length); }
    function writeOv() {
        var h = '';
        if (ov.formula) h += '<input type="hidden" name="formula" value="' + esc(ov.formula) + '">';
        Object.keys(ov.ranges || {}).forEach(function (k) {
            var r = ov.ranges[k];
            h += '<input type="hidden" name="ranges[' + esc(k) + '][min]" value="' + esc(r.min) + '"><input type="hidden" name="ranges[' + esc(k) + '][max]" value="' + esc(r.max) + '">';
        });
        ovBox.innerHTML = h;
        document.getElementById('wzChanged').hidden = !ovActive();
        document.getElementById('wzReset').hidden = !ovActive();
    }
    function resetOv() { ov = { formula: null, ranges: {} }; writeOv(); }
    function tplHtml(t) {
        return esc(t).replace(/\{\{([A-Za-z0-9_]+)\}\}/g, function (_, n) {
            if (/^[A-Z][0-9]?$/.test(n) && wzData && Object.prototype.hasOwnProperty.call(wzData.values, n)) return '<span class="wz-v">' + n + ' <small>' + esc(wzData.values[n]) + '</small></span>';
            return '<span class="wz-t">' + (n === 'NAME' ? 'სახელი' : esc(n.toLowerCase())) + '</span>';
        });
    }
    function wzResult() {
        var r = document.getElementById('wzRes'), f = document.getElementById('wzF').value;
        if (!wzData) return;
        var x = fxEval(f, wzData.values);
        if (x.err) { r.className = 'wz-res no'; r.textContent = x.err; return; }
        var v = x.v, t = wzData.target;
        if (Math.floor(v) !== v || v <= 0) { r.className = 'wz-res no'; r.textContent = '= ' + (Math.round(v * 100) / 100) + ' — მთელი დადებითი უნდა იყოს (⌊ ⌋ დამრგვალება?)'; return; }
        if (t === null || t === undefined) { r.className = 'wz-res ok'; r.textContent = '= ' + v; return; }
        r.className = 'wz-res ' + (v === t ? 'ok' : 'no');
        r.textContent = '= ' + v + (v === t ? ' ✓ ემთხვევა შენს პასუხს' : ' ✗ შენი პასუხია ' + t);
    }
    var wzEmpty = document.getElementById('wzEmpty');
    function renderWiz(w) {
        wzData = w;
        if (!w) {
            wiz.hidden = true; wzEmpty.hidden = false;
            wzEmpty.textContent = document.getElementById('tqAuto').checked
                ? 'ჩაწერე კითხვა და სწორი პასუხი — ოსტატი ფორმულას თვითონ დათვლის.'
                : 'რიცხვები არ იცვლება — ბავშვი ზუსტად შენს კითხვას ნახავს.';
            return;
        }
        wiz.hidden = false; wzEmpty.hidden = true;
        document.getElementById('wzTpl').innerHTML = tplHtml(w.template);
        var fin = document.getElementById('wzF');
        if (document.activeElement !== fin) fin.value = ov.formula || w.formula;
        var note = document.getElementById('wzFvNote');
        note.hidden = !w.formula_var;
        if (w.formula_var) note.innerHTML = 'პასუხი იქნება „' + tplHtml(w.answer_tpl).replace(/wz-t/g, 'wz-v') + '“, სადაც <b>' + esc(w.formula_var) + '</b> =';
        var chips = Object.keys(w.values).map(function (k) { return '<button type="button" class="wz-chip" data-ins="' + esc(k) + '"><b>' + esc(k) + '</b> = ' + esc(w.values[k]) + '</button>'; });
        [['+', '+'], ['-', '−'], ['*', '×'], ['/', '÷'], ['(', '('], [')', ')'], ['floor(', '⌊ ⌋']].forEach(function (o) { chips.push('<button type="button" class="wz-chip op" data-ins="' + o[0] + '" title="' + (o[0] === 'floor(' ? 'დამრგვალება ქვემოთ (მაგ. რამდენის ყიდვა შეუძლია)' : '') + '">' + o[1] + '</button>'); });
        document.getElementById('wzVarChips').innerHTML = chips.join('');
        document.getElementById('wzAlts').innerHTML = (w.alts || []).length ? '<span class="tq-help" style="font-size:12px;">სხვა შესაძლო:</span>' + w.alts.map(function (a) { return '<button type="button" class="wz-chip alt" data-set="' + esc(a.e) + '">' + esc(a.e) + ' = ' + esc(a.v) + '</button>'; }).join('') : '';
        var rows = (w.vars || []).map(function (v) {
            if (v.kind === 'list') return '<div class="wz-row"><span class="wz-v">' + esc(v.name) + '</span><span>სიიდან: ' + esc(v.list.join(', ')) + '</span></div>';
            var r = (ov.ranges || {})[v.name] || {};
            return '<div class="wz-row"><span class="wz-v">' + esc(v.name) + '</span><span>შენთან: <b>' + esc(v.value) + '</b></span><span>დან</span><input type="number" min="0" data-rng="' + esc(v.name) + '" data-side="min" value="' + esc(r.min ?? v.min) + '" aria-label="' + esc(v.name) + ' — დან"><span>მდე</span><input type="number" min="0" data-rng="' + esc(v.name) + '" data-side="max" value="' + esc(r.max ?? v.max) + '" aria-label="' + esc(v.name) + ' — მდე"></div>';
        });
        document.getElementById('wzRanges').innerHTML = rows.join('');
        document.getElementById('wzRangeSec').hidden = !rows.length;
        wzResult();
    }
    wiz.addEventListener('click', function (e) {
        var b = e.target.closest('[data-ins],[data-set]'), fin = document.getElementById('wzF');
        if (!b) return;
        if (b.hasAttribute('data-set')) fin.value = b.getAttribute('data-set');
        else {
            var t = b.getAttribute('data-ins'), a = fin.selectionStart ?? fin.value.length, z = fin.selectionEnd ?? fin.value.length;
            if (t.length === 1 && '+-*/'.indexOf(t) !== -1) t = ' ' + t + ' ';
            fin.value = fin.value.slice(0, a) + t + fin.value.slice(z);
            fin.selectionStart = fin.selectionEnd = a + t.length;
        }
        fin.focus();
        ov.formula = fin.value.trim(); writeOv(); wzResult();
        clearTimeout(timer); timer = setTimeout(loadVariants, 900);
    });
    wiz.addEventListener('input', function (e) {
        e.stopPropagation();
        if (e.target.id === 'wzF') { ov.formula = e.target.value.trim() || null; wzResult(); }
        var rk = e.target.getAttribute('data-rng');
        if (rk) {
            ov.ranges = ov.ranges || {};
            var row = wiz.querySelectorAll('[data-rng="' + rk + '"]');
            ov.ranges[rk] = { min: row[0].value, max: row[1].value };
        }
        writeOv();
        clearTimeout(timer); timer = setTimeout(loadVariants, 1100);
    });
    wiz.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); loadVariants(); } });
    document.getElementById('wzApply').addEventListener('click', loadVariants);
    document.getElementById('wzReset').addEventListener('click', function () { resetOv(); loadVariants(); });
    writeOv();

    // ── სერვერიდან: როგორ შეიცვლება კითხვა ──
    var variants = document.getElementById('tqVariants'), help = document.getElementById('tqMoreHelp'), timer = null, reqNo = 0;
    function loadVariants() {
        writeOv();
        var fd = new FormData(form); fd.delete('_method');
        if (!fd.get('text') || !fd.get('answer')) { variants.innerHTML = ''; help.textContent = 'ჩაწერე კითხვა და სწორი პასუხი — აქ გამოჩნდება.'; return; }
        var my = ++reqNo;
        help.textContent = 'ვამოწმებ…';
        fetch(@json(route('teacher.questions.preview')), { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (my !== reqNo) return;
                if (!res.ok) { variants.innerHTML = ''; renderWiz(res.j.wizard || null); help.innerHTML = '<span class="tq-err" style="display:block;">' + esc(res.j.error || (res.j.errors ? Object.values(res.j.errors)[0][0] : 'შეცდომა')) + '</span>'; return; }
                var j = res.j;
                renderWiz(j.auto_used ? j.wizard : null);
                help.innerHTML = j.auto_used ? 'ყოველ ბავშვს სხვა რიცხვები შეხვდება · მწვანე — სწორი პასუხი' : 'ბავშვი ზუსტად ამ კითხვას ნახავს · მწვანე — სწორი პასუხი';
                (j.notes || []).forEach(function (n) { help.innerHTML += '<div class="tq-note" style="margin-top:8px;">' + esc(n) + '</div>'; });
                variants.innerHTML = (j.samples || []).slice(0, j.auto_used ? 3 : 1).map(function (s) { return renderCard(s, true); }).join('');
            })
            .catch(function () { if (my === reqNo) help.textContent = 'ვერ შემოწმდა — სცადე „განახლება“.'; });
    }
    function schedule() { liveRender(); clearTimeout(timer); timer = setTimeout(loadVariants, 900); }
    // კითხვა/პასუხი შეიცვალა → ოსტატი თავიდან ამუშავებს (ძველი შესწორებები აღარ ერგება)
    var RESET_ON = ['text', 'answer', 'wrong[]', 'mode', 'auto'];
    form.addEventListener('input', function (e) { if (RESET_ON.indexOf(e.target.name) !== -1 && ovActive()) resetOv(); schedule(); });
    form.addEventListener('change', function (e) { if (e.target.closest && e.target.closest('#tqWiz')) return; if (e.target.name === 'mode') syncMode(); if (RESET_ON.indexOf(e.target.name) !== -1 && ovActive()) resetOv(); schedule(); });
    form.addEventListener('submit', writeOv);
    document.getElementById('tqMore').addEventListener('click', loadVariants);
    liveRender(); loadVariants();
})();
</script>
@endsection
