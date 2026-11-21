@extends('layouts.app')
{{-- მასწავლებლის კითხვის ფორმა: მარტივი ნაბიჯები + „ასე ნახავს ბავშვი“ --}}
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
    .tq-form { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 18px; align-items: start; }
    @media (max-width: 1100px) { .tq-form { grid-template-columns: 1fr; } }
    .tq-steps { display: flex; flex-direction: column; gap: 14px; }
    .tq-step { display: flex; flex-direction: column; gap: 12px; }
    .tq-step-h { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 900; color: #2B2622; margin: 0; }
    .tq-step-h b { width: 30px; height: 30px; border-radius: 50%; background: #FFF3EA; color: #C24A0A; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .tq-help { font-size: 14px; color: #6B625A; margin: 0; line-height: 1.5; }
    .tq-lbl { font-size: 14px; font-weight: 800; color: #4F473F; margin: 0 0 6px; display: block; }
    .tq-in, .tq-ta, .tq-sel { width: 100%; border: 2px solid #ECE9E4; border-radius: 14px; padding: 12px 14px; font-size: 16px; font-family: inherit; color: #2B2622; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; }
    .tq-in:focus, .tq-ta:focus, .tq-sel:focus { border-color: #E2590F; box-shadow: 0 0 0 4px rgba(226, 89, 15, .12); }
    .tq-ta { min-height: 130px; line-height: 1.6; resize: vertical; }
    .tq-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .tq-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .tq-chip span { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 12px; border: 2px solid #ECE9E4; background: #fff; font-weight: 800; font-size: 15px; cursor: pointer; color: #4F473F; }
    .tq-chip input:checked + span { border-color: #E2590F; background: #FFF3EA; color: #C24A0A; }
    .tq-chip input:focus-visible + span { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .tq-modes { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 560px) { .tq-modes { grid-template-columns: 1fr; } }
    .tq-mode { position: relative; cursor: pointer; margin: 0; }
    .tq-mode input { position: absolute; opacity: 0; pointer-events: none; }
    .tq-mode-box { display: flex; flex-direction: column; gap: 8px; height: 100%; border: 2.5px solid #ECE9E4; border-radius: 18px; padding: 14px; background: #fff; transition: border-color .15s, background .15s; }
    .tq-mode input:checked + .tq-mode-box { border-color: #E2590F; background: #FFF8F2; }
    .tq-mode input:focus-visible + .tq-mode-box { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .tq-mode-t { font-weight: 900; font-size: 16px; color: #2B2622; display: flex; align-items: center; gap: 8px; }
    .tq-mode-d { font-size: 13px; color: #6B625A; }
    .tq-mini { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-top: 2px; }
    .tq-mini i { display: block; height: 18px; border-radius: 6px; background: #F1EFEB; }
    .tq-mini i:first-child { background: #DFF5E3; }
    .tq-mini-in { height: 22px; width: 70px; border-radius: 6px; border: 2px dashed #FFC49A; margin: 2px auto 0; }
    .tq-wrong { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    @media (max-width: 560px) { .tq-wrong { grid-template-columns: 1fr; } }
    .tq-switch { display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0; }
    .tq-switch input { width: 0; height: 0; opacity: 0; position: absolute; }
    .tq-switch .tq-sw { width: 50px; height: 28px; border-radius: 99px; background: #D9D3CB; position: relative; flex-shrink: 0; transition: background .15s; }
    .tq-switch .tq-sw::after { content: ''; position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; transition: left .15s; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
    .tq-switch input:checked + .tq-sw { background: #E2590F; }
    .tq-switch input:checked + .tq-sw::after { left: 25px; }
    .tq-switch input:focus-visible + .tq-sw { outline: 3px solid #FFC49A; outline-offset: 2px; }
    .tq-side { position: sticky; top: 16px; display: flex; flex-direction: column; gap: 12px; }
    @media (max-width: 1100px) { .tq-side { position: static; } }
    .tq-var-list { display: flex; flex-direction: column; gap: 12px; }
    .tq-comment { border-radius: 16px; padding: 14px 16px; background: #FFF1C2; color: #5A3E00; display: flex; gap: 10px; }
    .tq-comment b { display: block; margin-bottom: 4px; }
    .tq-submit { position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: flex-end; background: rgba(247, 246, 243, .94); backdrop-filter: blur(6px); padding: 12px 0; }
    .tq-note { font-size: 13px; border-radius: 12px; padding: 10px 12px; background: #FFF1C2; color: #6B4A00; }
    .tq-err { font-size: 14px; border-radius: 12px; padding: 10px 12px; background: #FDE2E2; color: #A12626; font-weight: 700; }
    /* ოსტატის შეთავაზება */
    .wz { display: flex; flex-direction: column; gap: 12px; border: 2px solid #FFD9BF; }
    .wz-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 900; color: #C24A0A; margin: 0; }
    .wz-sec { display: flex; flex-direction: column; gap: 8px; border-top: 1px solid #F1EFEB; padding-top: 10px; }
    .wz-h { font-size: 13px; font-weight: 900; color: #4F473F; }
    .wz-tpl { font-size: 14px; line-height: 1.9; color: #2B2622; background: #F7F6F3; border-radius: 12px; padding: 10px 12px; white-space: pre-line; }
    .wz-v { display: inline-flex; align-items: center; gap: 4px; background: #E2590F; color: #fff; border-radius: 8px; padding: 0 7px; font-weight: 800; font-size: 13px; line-height: 1.6; }
    .wz-v small { font-weight: 600; opacity: .85; font-size: 11px; }
    .wz-t { display: inline-block; background: #F1EFEB; color: #4F473F; border: 1px dashed #D9D3CB; border-radius: 8px; padding: 0 7px; font-weight: 700; font-size: 13px; line-height: 1.6; }
    .wz-f { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .wz-f input { flex: 1 1 160px; min-width: 0; border: 2px solid #ECE9E4; border-radius: 12px; padding: 9px 12px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 15px; font-weight: 800; color: #2B2622; }
    .wz-f input:focus { outline: none; border-color: #E2590F; box-shadow: 0 0 0 4px rgba(226, 89, 15, .12); }
    .wz-res { font-size: 13px; font-weight: 800; }
    .wz-res.ok { color: #14612F; } .wz-res.no { color: #A12626; }
    .wz-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .wz-chip { border: 1px solid #ECE9E4; background: #fff; border-radius: 999px; padding: 3px 10px; font-size: 13px; cursor: pointer; color: #2B2622; font-family: inherit; }
    .wz-chip b { color: #C24A0A; }
    .wz-chip.op { font-weight: 900; min-width: 32px; }
    .wz-chip.alt { font-family: ui-monospace, Menlo, Consolas, monospace; background: #F7F6F3; }
    .wz-chip:hover { border-color: #E2590F; }
    .wz-row { display: grid; grid-template-columns: auto 1fr auto auto auto auto; gap: 6px; align-items: center; font-size: 13px; color: #6B625A; }
    .wz-row input { width: 64px; border: 2px solid #ECE9E4; border-radius: 10px; padding: 5px 6px; font-size: 14px; font-weight: 800; text-align: center; color: #2B2622; }
    .wz-row input:focus { outline: none; border-color: #E2590F; }
    .wz-acts { display: flex; gap: 8px; flex-wrap: wrap; }
    .wz-changed { font-size: 12px; font-weight: 800; color: #C24A0A; }
    details.tq-more summary { cursor: pointer; font-weight: 800; color: #6B625A; font-size: 14px; }
    @media (max-width: 900px) { .tq-submit { bottom: 64px; } }
</style>

<div class="tq-page">
    <div class="tq-head">
        <div>
            <h1>{{ $__edit ? 'კითხვის შესწორება' : 'ახალი კითხვა' }}</h1>
            <p>შეავსე ნაბიჯები — მარჯვნივ ხედავ, როგორ გამოჩნდება კითხვა ბავშვთან.</p>
        </div>
        <a href="{{ route('teacher.questions.index') }}" class="tq-btn tq-btn-ghost">← ჩემი კითხვები</a>
    </div>

    @if($__edit && $question->review_status === 'rejected' && $question->review_comment)
    <div class="tq-comment" role="note">
        <i class="bi bi-chat-left-text" style="font-size:20px;"></i>
        <div><b>ადმინის კომენტარი:</b>{{ $question->review_comment }}</div>
    </div>
    @endif
    @if($errors->any())<div class="tq-flash err" role="alert">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ $__edit ? route('teacher.questions.update', $question) : route('teacher.questions.store') }}" id="tqForm">
        @csrf
        @if($__edit) @method('PUT') @endif
        <div class="tq-form">
            <div class="tq-steps">
                {{-- 1. სად --}}
                <section class="tq-card tq-step">
                    <h2 class="tq-step-h"><b>1</b>რომელი კლასისთვის?</h2>
                    <div class="tq-chips" role="radiogroup" aria-label="კლასი">
                        @foreach($grades as $__g)
                        <label class="tq-chip"><input type="radio" name="grade_id" value="{{ $__g->id }}" @checked($__gradeId == $__g->id)><span>{{ $__g->name }}</span></label>
                        @endforeach
                    </div>
                    <div>
                        <label class="tq-lbl" for="tqTopic">თემა</label>
                        <select id="tqTopic" name="topic_id" class="tq-sel" required data-selected="{{ $__topicId }}"></select>
                    </div>
                    <div>
                        <span class="tq-lbl">სირთულე</span>
                        <div class="tq-chips" id="tqLevels" role="radiogroup" aria-label="სირთულე" data-selected="{{ old('difficulty', $question->difficulty ?: 1) }}"></div>
                    </div>
                    <label class="tq-switch">
                        <input type="checkbox" name="is_olympiad" value="1" @checked(old('is_olympiad', $question->is_olympiad))>
                        <span class="tq-sw"></span>
                        <span><b>ოლიმპიადის კითხვაა</b><br><span class="tq-help">გამოჩნდება მხოლოდ ოლიმპიადაში.</span></span>
                    </label>
                </section>

                {{-- 2. კითხვა --}}
                <section class="tq-card tq-step">
                    <h2 class="tq-step-h"><b>2</b>დაწერე კითხვა</h2>
                    <p class="tq-help">ჩაწერე ისე, როგორც ბავშვს ეტყოდი. რიცხვები შეგიძლია ციფრით ან სიტყვით.</p>
                    <textarea name="text" id="tqText" class="tq-ta" required maxlength="2000" placeholder="მაგ.: ნიკას ჰქონდა 15 ვაშლი. 6 ვაშლი შეჭამა. რამდენი ვაშლი დარჩა?">{{ old('text', $td['text'] ?? $question->template_text) }}</textarea>
                </section>

                {{-- 3. როგორ პასუხობს --}}
                <section class="tq-card tq-step">
                    <h2 class="tq-step-h"><b>3</b>როგორ უპასუხებს ბავშვი?</h2>
                    <div class="tq-modes" role="radiogroup" aria-label="პასუხის ტიპი">
                        <label class="tq-mode">
                            <input type="radio" name="mode" value="choice" @checked($__mode === 'choice')>
                            <span class="tq-mode-box">
                                <span class="tq-mode-t"><i class="bi bi-ui-radios-grid"></i>ასარჩევი</span>
                                <span class="tq-mode-d">ბავშვი 4 ვარიანტიდან ირჩევს სწორს.</span>
                                <span class="tq-mini"><i></i><i></i><i></i><i></i></span>
                            </span>
                        </label>
                        <label class="tq-mode">
                            <input type="radio" name="mode" value="input" @checked($__mode === 'input')>
                            <span class="tq-mode-box">
                                <span class="tq-mode-t"><i class="bi bi-input-cursor-text"></i>ჩასაწერი</span>
                                <span class="tq-mode-d">ბავშვი თვითონ წერს პასუხს (მხოლოდ რიცხვი).</span>
                                <span class="tq-mini-in"></span>
                            </span>
                        </label>
                    </div>
                </section>

                {{-- 4. პასუხი --}}
                <section class="tq-card tq-step">
                    <h2 class="tq-step-h"><b>4</b>პასუხი</h2>
                    <div>
                        <label class="tq-lbl" for="tqAnswer">სწორი პასუხი</label>
                        <input id="tqAnswer" name="answer" class="tq-in" required maxlength="200" value="{{ old('answer', $td['answer'] ?? '') }}" placeholder="მაგ. 9" style="max-width:260px;">
                    </div>
                    <div id="tqWrongBox">
                        <span class="tq-lbl">არასწორი პასუხები <span class="tq-help" style="font-weight:600;">— თუ არ ჩაწერ, სისტემა თვითონ შეარჩევს</span></span>
                        <div class="tq-wrong">
                            @for($__i = 0; $__i < 3; $__i++)
                            <input name="wrong[]" class="tq-in tq-wrong-in" maxlength="200" value="{{ $__wrong[$__i] ?? '' }}" placeholder="არასწორი {{ $__i + 1 }}" aria-label="არასწორი პასუხი {{ $__i + 1 }}">
                            @endfor
                        </div>
                    </div>
                    <div>
                        <label class="tq-lbl" for="tqHint">მინიშნება <span class="tq-help" style="font-weight:600;">— არასავალდებულო</span></label>
                        <input id="tqHint" name="hint" class="tq-in" maxlength="500" value="{{ old('hint', $td['hint'] ?? $question->hint_text) }}" placeholder="მაგ.: ჯერ გაიგე, რამდენი შეჭამა.">
                    </div>
                </section>

                {{-- 5. რიცხვები --}}
                <section class="tq-card tq-step">
                    <h2 class="tq-step-h"><b>5</b>ყოველ ჯერზე ახალი რიცხვები?</h2>
                    <label class="tq-switch">
                        <input type="hidden" name="auto" value="0">
                        <input type="checkbox" name="auto" value="1" id="tqAuto" @checked($__auto)>
                        <span class="tq-sw"></span>
                        <span><b>დიახ, რიცხვები შეიცვალოს</b><br><span class="tq-help">ბავშვი ამ კითხვას ყოველ ჯერზე სხვა რიცხვებით ნახავს, სახელებიც შეიძლება შეიცვალოს. სისტემა თვითონ გაარკვევს, როგორ ითვლება პასუხი. თუ გამორთავ, ბავშვი ზუსტად შენს ტექსტს ნახავს.</span></span>
                    </label>
                    <details class="tq-more">
                        <summary>დამატებით: სახელები და საგნები</summary>
                        <div style="margin-top:10px;">
                            <label class="tq-lbl" for="tqTheme">თემატიკა</label>
                            <select id="tqTheme" name="theme_id" class="tq-sel" style="max-width:320px;">
                                @foreach($themes as $__th)<option value="{{ $__th->id }}" @selected(old('theme_id', $question->theme_id ?? $defaultThemeId) == $__th->id)>{{ $__th->icon }} {{ $__th->name }}</option>@endforeach
                            </select>
                        </div>
                    </details>
                </section>
            </div>

            {{-- ასე ნახავს ბავშვი --}}
            <aside class="tq-side" aria-label="ასე ნახავს ბავშვი">
                <div class="tq-kid">
                    <span class="tq-kid-lbl">ასე ნახავს ბავშვი</span>
                    <div id="tqLive"></div>
                </div>
                <section class="tq-card wz" id="tqWiz" hidden aria-label="ოსტატის შეთავაზება">
                    <h3 class="wz-title"><i class="bi bi-magic"></i> ოსტატის შეთავაზება <span class="wz-changed" id="wzChanged" hidden>· შენ შეცვალე</span></h3>
                    <p class="tq-help" style="font-size:13px;">სისტემამ შენი კითხვა ასე დაამუშავა. თუ რამე არასწორია, აქვე შეასწორე — შენახვისას შენი ვარიანტი გამოიყენება.</p>
                    <div class="wz-sec">
                        <span class="wz-h">1. კითხვა ცვლადებით</span>
                        <div class="wz-tpl" id="wzTpl"></div>
                        <span class="tq-help" style="font-size:12px;"><span class="wz-v">A</span> — რიცხვი, რომელიც იცვლება · <span class="wz-t">სახელი</span> — სახელი/საგანი თემატიკიდან</span>
                    </div>
                    <div class="wz-sec">
                        <span class="wz-h">2. როგორ ითვლება სწორი პასუხი</span>
                        <span class="tq-help" style="font-size:12px;" id="wzFvNote" hidden></span>
                        <div class="wz-f">
                            <input id="wzF" autocomplete="off" spellcheck="false" aria-label="სწორი პასუხის ფორმულა">
                            <span class="wz-res" id="wzRes" aria-live="polite"></span>
                        </div>
                        <div class="wz-chips" id="wzVarChips"></div>
                        <div class="wz-chips" id="wzAlts"></div>
                    </div>
                    <div class="wz-sec" id="wzRangeSec">
                        <span class="wz-h">3. რა საზღვრებში იცვლება რიცხვები</span>
                        <div style="display:flex;flex-direction:column;gap:6px;" id="wzRanges"></div>
                    </div>
                    <div class="wz-acts">
                        <button type="button" class="tq-btn tq-btn-main tq-btn-sm" id="wzApply"><i class="bi bi-check2"></i> შემოწმება</button>
                        <button type="button" class="tq-btn tq-btn-ghost tq-btn-sm" id="wzReset" hidden><i class="bi bi-arrow-counterclockwise"></i> ოსტატის ვარიანტზე დაბრუნება</button>
                    </div>
                </section>
                <div id="tqOv" hidden></div>
                <div class="tq-card" style="display:flex;flex-direction:column;gap:10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <b style="font-size:15px;">სხვა ვარიანტები</b>
                        <button type="button" class="tq-btn tq-btn-soft tq-btn-sm" id="tqMore"><i class="bi bi-arrow-repeat"></i> განახლება</button>
                    </div>
                    <p class="tq-help" id="tqMoreHelp" style="font-size:13px;">აქ გამოჩნდება, როგორ შეიცვლება კითხვა სხვა ბავშვებთან.</p>
                    <div class="tq-var-list" id="tqVariants" aria-live="polite"></div>
                </div>
            </aside>
        </div>

        <div class="tq-submit">
            @if($__edit)<span class="tq-help" style="margin-right:auto;">შენახვისას კითხვა ხელახლა გაიგზავნება ადმინთან.</span>@endif
            <a href="{{ route('teacher.questions.index') }}" class="tq-btn tq-btn-ghost">გაუქმება</a>
            <button type="submit" class="tq-btn tq-btn-main"><i class="bi bi-send"></i> გაგზავნა ადმინთან</button>
        </div>
    </form>
</div>

<script>
(function () {
    var GRADES = @json($__gradeData);
    var form = document.getElementById('tqForm');
    var topicSel = document.getElementById('tqTopic'), levels = document.getElementById('tqLevels');
    var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); };

    // ── კლასი → თემები და სირთულის დონეები ──
    function gradeId() { var r = form.querySelector('input[name="grade_id"]:checked'); return r ? r.value : null; }
    function fillGrade(first) {
        var g = GRADES[gradeId()] || { max: 3, topics: [] };
        var sel = first ? topicSel.getAttribute('data-selected') : topicSel.value;
        topicSel.innerHTML = '<option value="">— აირჩიე თემა —</option>' + g.topics.map(function (t) {
            return '<option value="' + t.id + '"' + (String(t.id) === String(sel) ? ' selected' : '') + '>' + esc(t.name) + '</option>';
        }).join('');
        var lv = first ? levels.getAttribute('data-selected') : ((levels.querySelector('input:checked') || {}).value || 1);
        if (+lv > g.max) lv = g.max;
        var names = { 1: 'მარტივი', 2: 'საშუალო', 3: 'რთული', 4: 'ძალიან რთული', 5: 'ექსპერტი' };
        var h = '';
        for (var i = 1; i <= g.max; i++) h += '<label class="tq-chip"><input type="radio" name="difficulty" value="' + i + '"' + (String(i) === String(lv) ? ' checked' : '') + '><span>' + i + ' · ' + names[i] + '</span></label>';
        levels.innerHTML = h;
    }
    form.querySelectorAll('input[name="grade_id"]').forEach(function (r) { r.addEventListener('change', function () { fillGrade(false); schedule(); }); });
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
    function renderWiz(w) {
        wzData = w;
        if (!w) { wiz.hidden = true; return; }
        wiz.hidden = false;
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
        if (!fd.get('text') || !fd.get('answer')) { variants.innerHTML = ''; help.textContent = 'ჩაწერე კითხვა და სწორი პასუხი — აქ ნახავ სხვა ვარიანტებს.'; return; }
        var my = ++reqNo;
        help.textContent = 'ვამოწმებ…';
        fetch(@json(route('teacher.questions.preview')), { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (my !== reqNo) return;
                if (!res.ok) { variants.innerHTML = ''; renderWiz(res.j.wizard || null); help.innerHTML = '<span class="tq-err" style="display:block;">' + esc(res.j.error || (res.j.errors ? Object.values(res.j.errors)[0][0] : 'შეცდომა')) + '</span>'; return; }
                var j = res.j;
                renderWiz(j.auto_used ? j.wizard : null);
                help.innerHTML = j.auto_used ? '✓ რიცხვები ყოველ ჯერზე შეიცვლება — აი, 3 მაგალითი:' : 'ბავშვი ზუსტად ამ კითხვას ნახავს.';
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
