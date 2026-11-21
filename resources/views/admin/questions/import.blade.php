@extends('layouts.app')
{{-- კითხვების JSON იმპორტი: AI-სთვის ინსტრუქცია → JSON → შემოწმება → შენახვა --}}
@section('content')
<style>
    .qi-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
    .qi-step { display: flex; gap: 12px; align-items: flex-start; }
    .qi-step b.qi-n { width: 32px; height: 32px; border-radius: 50%; background: #FFF3EA; color: #C24A0A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 900; }
    .qi-step span { font-size: 14px; line-height: 1.45; color: #4F473F; }
    .qi-prompt { width: 100%; min-height: 160px; max-height: 360px; font-family: ui-monospace, Menlo, Consolas, monospace !important; font-size: 12px !important; line-height: 1.5; }
    .qi-json { width: 100%; min-height: 220px; font-family: ui-monospace, Menlo, Consolas, monospace !important; font-size: 13px !important; }
    .qi-sum { display: flex; flex-wrap: wrap; gap: 8px; }
    .qi-pill { border-radius: 999px; padding: 6px 12px; font-size: 13px; font-weight: 800; }
    .qi-pill.ok { background: #DFF5E3; color: #14612F; } .qi-pill.warn { background: #FFF1C2; color: #6B4A00; }
    .qi-pill.error { background: #FDE2E2; color: #A12626; } .qi-pill.saved { background: #F1EFEB; color: #4F473F; }
    .qi-item { display: flex; flex-direction: column; gap: 12px; border-left: 0; }
    .qi-item.error { box-shadow: 0 0 0 2px #F5B5B5; } .qi-item.warn { box-shadow: 0 0 0 2px #F5D77A; } .qi-item.ok { box-shadow: 0 0 0 2px #A9E2B8; }
    .qi-item.saved { opacity: .6; }
    .qi-head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .qi-head label { display: flex; align-items: center; gap: 10px; flex: 1 1 260px; min-width: 0; margin: 0; cursor: pointer; }
    .qi-head input[type=checkbox] { width: 22px; height: 22px; accent-color: #C24A0A; flex-shrink: 0; }
    .qi-title { font-size: 16px; font-weight: 800; }
    .qi-meta { font-size: 12px; color: #6B625A; }
    .qi-tpl { background: #F7F6F3; border-radius: 14px; padding: 12px 14px; font-size: 14px; line-height: 1.5; }
    .qi-tpl code { background: #FFE3CC; color: #8A3407; border-radius: 6px; padding: 1px 5px; font-size: 12px; }
    .qi-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 6px; font-size: 13px; line-height: 1.45; }
    .qi-list li { border-radius: 10px; padding: 8px 10px; }
    .qi-list .e { background: #FDE2E2; color: #A12626; } .qi-list .w { background: #FFF1C2; color: #6B4A00; } .qi-list .n { background: #F1EFEB; color: #4F473F; }
    .qi-samples { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
    .qi-sample { border: 1px solid #ECE9E4; border-radius: 14px; padding: 12px; display: flex; flex-direction: column; gap: 8px; font-size: 14px; }
    .qi-opts { display: flex; flex-wrap: wrap; gap: 6px; }
    .qi-opts span { border-radius: 10px; padding: 4px 10px; background: #F1EFEB; font-weight: 700; font-size: 13px; }
    .qi-opts span.c { background: #DFF5E3; color: #14612F; }
    .qi-bar { position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    .qi-fx { display: flex; flex-direction: column; gap: 8px; border: 1.5px dashed #FFC49A; border-radius: 14px; padding: 12px; background: #FFFBF7; }
    .qi-fx-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .qi-fx-in { flex: 1 1 240px; min-width: 0; font-family: ui-monospace, Menlo, Consolas, monospace !important; font-size: 15px !important; font-weight: 700; }
    .qi-fx-res { font-weight: 800; font-size: 14px; min-width: 90px; }
    .qi-fx-res.ok { color: #14612F; } .qi-fx-res.no { color: #A12626; } .qi-fx-res.mid { color: #6B4A00; }
    .qi-fx-chips { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .qi-chip { border: 1px solid #ECE9E4; background: #fff; border-radius: 999px; padding: 4px 10px; font-size: 13px; cursor: pointer; color: #2B2622; }
    .qi-chip b { color: #C24A0A; }
    .qi-chip.op { font-weight: 800; min-width: 34px; }
    .qi-chip.alt { font-family: ui-monospace, Menlo, Consolas, monospace; background: #F7F6F3; }
    .qi-chip:hover { border-color: #E2590F; }
    .qi-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .qi-tab { display: flex; align-items: center; gap: 8px; border: 1.5px solid #ECE9E4; background: #fff; border-radius: 14px; padding: 10px 16px; font-weight: 800; font-size: 15px; color: #2B2622; cursor: pointer; }
    .qi-tab small { font-weight: 600; font-size: 12px; color: #6B625A; }
    .qi-tab.on { border-color: #E2590F; background: #FFF3EA; color: #C24A0A; }
    .qi-panel[data-qi-panel="ai"] { gap: 16px; }
    details.qi-raw summary { cursor: pointer; font-size: 13px; font-weight: 700; color: #6B625A; }
    details.qi-raw pre { margin: 8px 0 0; font-size: 12px; background: #F7F6F3; border-radius: 12px; padding: 10px; white-space: pre-wrap; word-break: break-word; }
</style>

<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">ტექსტიდან ან AI-ით · შემოწმება შენახვამდე</span>
        <h1 class="ks-display ks-h1">კითხვების იმპორტი</h1>
    </div>
    <a href="{{ route('admin.questions.index') }}" class="ks-btn ks-btn-ghost">← კითხვები</a>
</header>

@if(session('success'))
<div class="ks-note" style="background:#DFF5E3;color:#14612F;font-weight:700;">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="ks-note" style="background:#FDE2E2;color:#A12626;font-weight:700;">{{ $errors->first() }}</div>
@endif

@php $__mode = old('text') !== null || $text !== '' || $json === '' ? 'text' : 'ai'; @endphp
<div class="qi-tabs" role="tablist">
    <button type="button" role="tab" class="qi-tab {{ $__mode === 'text' ? 'on' : '' }}" data-qi-tab="text"><i class="bi bi-magic"></i> ტექსტიდან <small>უფასო · AI-ს გარეშე</small></button>
    <button type="button" role="tab" class="qi-tab {{ $__mode === 'ai' ? 'on' : '' }}" data-qi-tab="ai"><i class="bi bi-stars"></i> AI-ით <small>JSON</small></button>
</div>

{{-- ── ტექსტიდან: უფასო ოსტატი ── --}}
<form method="POST" action="{{ route('admin.questions.import.draft') }}" class="ks-card qi-panel" data-qi-panel="text" style="display:{{ $__mode === 'text' ? 'flex' : 'none' }};flex-direction:column;gap:10px;">
    @csrf
    <h2 class="ks-h2" style="margin:0;">ჩასვი ამოცანები</h2>
    <p class="ks-muted" style="margin:0;font-size:13px;line-height:1.5;">
        თითოეული ამოცანა ცალკე აბზაცად ან „ამოცანა №1“ სათაურით. თუ პასუხის ვარიანტები გაქვს (<b>ა) … ბ) … გ) …</b>) ან ჩაწერ <b>„პასუხი: 9“</b>,
        სისტემა თვითონ იპოვის ფორმულას. რიცხვები (ციფრით და სიტყვით: „ოთხი“, „ორ-ორი“, „ხუთლარიანი“) ცვლადებად იქცევა, სახელები — თემატიკის ცვლადებად.
        შედეგს ქვემოთ JSON-ად ნახავ და შეგიძლია შეცვალო.
    </p>
    <textarea name="text" class="ks-input qi-json" style="min-height:200px;font-family:inherit !important;font-size:14px !important;" placeholder="ამოცანა №1&#10;ეზოში თამაშობდა 12 ბიჭი. … რამდენი ბიჭი თამაშობს ახლა ეზოში?&#10;ა) ოთხი   ბ) ათი   გ) თვრამეტი   დ) თექვსმეტი">{{ old('text', $text) }}</textarea>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;">
        <div class="ks-field">
            <label class="ks-label" for="qiTopicT">თემა (კლასი)</label>
            <select id="qiTopicT" name="default_topic_id" class="ks-input" required>
                <option value="">— აირჩიე —</option>
                @foreach($topics as $__t)<option value="{{ $__t->id }}" @selected(old('default_topic_id', $defaults['topic_id'] ?? null) == $__t->id)>{{ $__t->grade?->name }} · {{ $__t->name }}</option>@endforeach
            </select>
        </div>
        <div class="ks-field">
            <label class="ks-label" for="qiThemeT">თემატიკა (სახელები, საგნები)</label>
            <select id="qiThemeT" name="default_theme_id" class="ks-input">
                <option value="">— არცერთი —</option>
                @foreach($themes as $__th)<option value="{{ $__th->id }}" @selected(old('default_theme_id', $defaults['theme_id'] ?? null) == $__th->id)>{{ $__th->icon }} {{ $__th->name }}</option>@endforeach
            </select>
        </div>
    </div>
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;justify-content:flex-end;">
        <span class="ks-muted" style="font-size:12px;flex:1;min-width:200px;">ფორმულა გამოცნობილია — თუ რამდენიმე შესაძლებელია, ყველას გაჩვენებს. შენახვამდე ყოველთვის გადახედე.</span>
        <button type="submit" class="ks-btn ks-btn-primary"><i class="bi bi-magic"></i> შაბლონების შედგენა</button>
    </div>
</form>

{{-- ── AI-ით ── --}}
<div class="qi-panel" data-qi-panel="ai" style="display:{{ $__mode === 'ai' ? 'flex' : 'none' }};flex-direction:column;gap:inherit;">
<div class="ks-card qi-steps">
    <div class="qi-step"><b class="qi-n">1</b><span>დააკოპირე ინსტრუქცია და ჩასვი ნებისმიერ AI ჩატში (Claude, ChatGPT…). ბოლოში ჩაუმატე შენი ამოცანები.</span></div>
    <div class="qi-step"><b class="qi-n">2</b><span>AI დაგიბრუნებს JSON-ს. დააკოპირე მთლიანად და ჩასვი ქვემოთ (ან შეინახე .json ფაილად და ატვირთე).</span></div>
    <div class="qi-step"><b class="qi-n">3</b><span>სისტემა თითოეულ კითხვას შეამოწმებს და 3 ვარიანტს გაჩვენებს. შეინახე მხოლოდ ის, რაც მოგეწონა.</span></div>
</div>

<div class="ks-card" style="display:flex;flex-direction:column;gap:10px;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
        <h2 class="ks-h2" style="margin:0;">1. ინსტრუქცია AI-სთვის</h2>
        <button type="button" class="ks-btn ks-btn-primary" id="qiCopy"><i class="bi bi-clipboard"></i> კოპირება</button>
    </div>
    <p class="ks-muted" style="margin:0;font-size:13px;">შეიცავს JSON ფორმატს, წესებს და შენს ამჟამინდელ კლასებს, თემებს და თემატიკის ცვლადებს — ბაზის ცვლილებისას ავტომატურად განახლდება.</p>
    <textarea id="qiPrompt" class="ks-input qi-prompt" readonly aria-label="ინსტრუქცია AI-სთვის">{{ $prompt }}</textarea>
</div>

</div>

<form method="POST" action="{{ route('admin.questions.import.check') }}" enctype="multipart/form-data" class="ks-card" style="display:flex;flex-direction:column;gap:10px;">
    @csrf
    <h2 class="ks-h2" style="margin:0;">JSON <span class="ks-muted" style="font-size:13px;font-weight:600;">— AI-ს პასუხი ან ოსტატის მონახაზი (შეგიძლია შეცვალო და თავიდან შეამოწმო)</span></h2>
    <label class="ks-label" for="qiJson">ჩასვი JSON</label>
    <textarea id="qiJson" name="json" class="ks-input qi-json" placeholder='{"questions": [ ... ]}'>{{ old('json', $json) }}</textarea>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;">
        <div class="ks-field">
            <label class="ks-label" for="qiTopic">ნაგულისხმევი თემა (თუ JSON-ში არ წერია)</label>
            <select id="qiTopic" name="default_topic_id" class="ks-input">
                <option value="">— JSON-იდან —</option>
                @foreach($topics as $__t)<option value="{{ $__t->id }}" @selected(($defaults['topic_id'] ?? null) == $__t->id)>{{ $__t->grade?->name }} · {{ $__t->name }}</option>@endforeach
            </select>
        </div>
        <div class="ks-field">
            <label class="ks-label" for="qiTheme">ნაგულისხმევი თემატიკა</label>
            <select id="qiTheme" name="default_theme_id" class="ks-input">
                <option value="">— JSON-იდან —</option>
                @foreach($themes as $__th)<option value="{{ $__th->id }}" @selected(($defaults['theme_id'] ?? null) == $__th->id)>{{ $__th->icon }} {{ $__th->name }}</option>@endforeach
            </select>
        </div>
    </div>
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
        <label class="ks-label" for="qiFile" style="margin:0;">ან ატვირთე ფაილი:</label>
        <input type="file" id="qiFile" name="file" accept=".json,.txt,application/json,text/plain" class="ks-input" style="max-width:320px;">
        <span style="flex:1;"></span>
        @if($result)<a href="{{ route('admin.questions.import.clear') }}" class="ks-btn ks-btn-ghost">გასუფთავება</a>@endif
        <button type="submit" class="ks-btn ks-btn-primary"><i class="bi bi-check2-circle"></i> შემოწმება</button>
    </div>
</form>

@if($result)
<section id="results" style="display:flex;flex-direction:column;gap:12px;">
    @if($result['fatal'])
        <div class="ks-note" style="background:#FDE2E2;color:#A12626;font-weight:700;">{{ $result['fatal'] }}</div>
    @else
    @php
        $__items = collect($result['items']);
        $__cnt = fn($s) => $__items->where('status', $s)->count();
    @endphp
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
        <h2 class="ks-h2" style="margin:0;">3. შედეგი · {{ $__items->count() }} კითხვა</h2>
        <div class="qi-sum">
            @if($__cnt('ok'))<span class="qi-pill ok">✓ მზადაა: {{ $__cnt('ok') }}</span>@endif
            @if($__cnt('warn'))<span class="qi-pill warn">⚠ გაფრთხილებით: {{ $__cnt('warn') }}</span>@endif
            @if($__cnt('error'))<span class="qi-pill error">✕ შეცდომით: {{ $__cnt('error') }}</span>@endif
            @if($__cnt('saved'))<span class="qi-pill saved">შენახულია: {{ $__cnt('saved') }}</span>@endif
        </div>
    </div>

    @if(!empty($result['skipped']))
    <div class="ks-card" style="display:flex;flex-direction:column;gap:8px;">
        <b>AI-მ შაბლონად ვერ აქცია ({{ count($result['skipped']) }}):</b>
        <ul class="qi-list">
            @foreach($result['skipped'] as $__sk)<li class="n"><b>{{ \Illuminate\Support\Str::limit($__sk['text'], 120) }}</b> — {{ $__sk['reason'] }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.questions.import.save') }}" style="display:flex;flex-direction:column;gap:12px;" id="qiSaveForm">
        @csrf
        @foreach($result['items'] as $__it)
        @php
            $__st = $__it['status'];
            $__tpl = e($__it['attrs']['template_text']);
            $__tpl = preg_replace('/\{\{\s*(\w+)\s*\}\}/u', '<code>$1</code>', $__tpl);
        @endphp
        <article class="ks-card qi-item {{ $__st }}" id="qi-item-{{ $__it['index'] }}">
            <div class="qi-head">
                <label>
                    <input type="checkbox" name="pick[]" value="{{ $__it['index'] }}" @checked($__st === 'ok') @disabled(in_array($__st, ['error', 'saved']))>
                    <span style="display:flex;flex-direction:column;min-width:0;">
                        <span class="qi-title">{{ $__it['label'] }}</span>
                        <span class="qi-meta">{{ $__it['grade'] ?? 'კლასი ?' }} · {{ $__it['topic'] ?? 'თემა ?' }}{{ $__it['theme'] ? ' · ' . $__it['theme'] : '' }} · დონე {{ $__it['attrs']['difficulty'] ?: '?' }} · {{ ['numeric' => '4 ვარიანტი', 'numeric_input' => 'ჩაწერა', 'text' => 'ტექსტური'][$__it['attrs']['answer_type']] ?? $__it['attrs']['answer_type'] }}{{ $__it['attrs']['is_olympiad'] ? ' · ოლიმპიადა' : '' }}</span>
                    </span>
                </label>
                @if($__st === 'saved')
                    <a href="{{ route('admin.questions.edit', $__it['saved_id']) }}" class="qi-pill saved" style="text-decoration:none;">შენახულია #{{ $__it['saved_id'] }} →</a>
                @elseif($__st === 'error')
                    <span class="qi-pill error">✕ შეცდომა</span>
                @else
                    <span class="qi-pill {{ $__st }}">{{ $__st === 'ok' ? '✓ მზადაა' : '⚠ გადახედე' }}</span>
                    <button type="submit" name="edit" value="{{ $__it['index'] }}" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="qiOnly({{ $__it['index'] }})">შენახვა და რედაქტირება</button>
                @endif
            </div>

            @if($__it['original'])<div class="qi-meta"><b>ორიგინალი:</b> {{ $__it['original'] }}</div>@endif
            <div class="qi-tpl">{!! $__tpl !!}<div class="qi-meta" style="margin-top:6px;">ფორმულა: <b>{{ $__it['attrs']['correct_formula'] }}</b>@if($__it['stats']) · გენერაცია {{ $__it['stats']['ok'] }}/{{ $__it['stats']['runs'] }} · {{ $__it['stats']['distinct'] }} განსხვავებული პასუხი @endif</div></div>

            @if($__st !== 'saved')
            @php
                $__q = $questions[$__it['index']] ?? [];
                $__vals = (array) ($__q['original']['values'] ?? []);
                $__dr = (array) ($__q['_draft'] ?? []);
                $__opts = array_values((array) ($__dr['options'] ?? []));
                $__alts = (array) ($__dr['alts'] ?? []);
                $__fv = $__dr['formula_var'] ?? null; // ტექსტური პასუხი: ვასწორებთ X-ის ფორმულას
                $__fxVal = $__fv ? ($__q['variables'][$__fv]['derived'] ?? '') : $__it['attrs']['correct_formula'];
            @endphp
            <div class="qi-fx" data-vals='@json((object) $__vals)' data-opts='@json($__opts)'>
                <label class="ks-label" for="qiFx{{ $__it['index'] }}" style="margin:0;">@if($__fv)ფორმულა {{ $__fv }}-ისთვის <span class="qi-meta">· პასუხი: {{ $__it['attrs']['correct_formula'] }}</span>@else სწორი ფორმულა @endif</label>
                <div class="qi-fx-row">
                    <input id="qiFx{{ $__it['index'] }}" name="formula" form="qiF{{ $__it['index'] }}" class="ks-input qi-fx-in" value="{{ $__fxVal }}" autocomplete="off" spellcheck="false" placeholder="მაგ. A + C - D">
                    <span class="qi-fx-res" aria-live="polite"></span>
                    <button type="submit" form="qiF{{ $__it['index'] }}" class="ks-btn ks-btn-primary ks-btn-sm"><i class="bi bi-arrow-repeat"></i> გადათვლა</button>
                </div>
                @if($__vals)
                <div class="qi-fx-chips">
                    <span class="qi-meta">ცვლადები:</span>
                    @foreach($__vals as $__k => $__v)<button type="button" class="qi-chip" data-ins="{{ $__k }}"><b>{{ $__k }}</b> = {{ $__v }}</button>@endforeach
                    @foreach(['+', '-', '*', '/', '(', ')', 'floor('] as $__op)<button type="button" class="qi-chip op" data-ins=" {{ $__op }} ">{{ ['*' => '×', '/' => '÷', '-' => '−', 'floor(' => '⌊ ⌋ დამრგვალება'][$__op] ?? $__op }}</button>@endforeach
                </div>
                @endif
                @if($__alts)
                <div class="qi-fx-chips">
                    <span class="qi-meta">სხვა შესაძლო:</span>
                    @foreach($__alts as $__a)<button type="button" class="qi-chip alt" data-set="{{ $__a['e'] }}">{{ $__a['e'] }} = {{ $__a['v'] }}</button>@endforeach
                </div>
                @endif
                @if($__opts)<div class="qi-meta">პასუხის ვარიანტები: {{ implode(' · ', $__opts) }}</div>@endif
            </div>
            @endif

            @if($__it['errors'] || $__it['warnings'] || $__it['notes'])
            <ul class="qi-list">
                @foreach($__it['errors'] as $__m)<li class="e">✕ {{ $__m }}</li>@endforeach
                @foreach($__it['warnings'] as $__m)<li class="w">⚠ {{ $__m }}</li>@endforeach
                @foreach($__it['notes'] as $__m)<li class="n">{{ $__m }}</li>@endforeach
            </ul>
            @endif

            @if($__it['samples'])
            <div class="qi-samples">
                @foreach($__it['samples'] as $__s)
                <div class="qi-sample">
                    <span>{{ $__s['question_text'] }}</span>
                    @if(!empty($__s['options']))
                    <div class="qi-opts">@foreach($__s['options'] as $__o)<span class="{{ (string) $__o === (string) $__s['correct_answer'] ? 'c' : '' }}">{{ $__o }}</span>@endforeach</div>
                    @else
                    <div class="qi-opts"><span class="c">პასუხი: {{ $__s['correct_answer'] }}</span></div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <details class="qi-raw"><summary>ტექნიკური დეტალები</summary><pre>{{ json_encode(\Illuminate\Support\Arr::only($__it['attrs'], ['num_config', 'conditions', 'distractors']), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre></details>
        </article>
        @endforeach

        <div class="ks-card qi-bar">
            <label style="display:flex;align-items:center;gap:8px;margin:0;font-weight:700;font-size:14px;cursor:pointer;">
                <input type="hidden" name="activate" value="0">
                <input type="checkbox" name="activate" value="1" checked style="width:20px;height:20px;accent-color:#C24A0A;"> შენახვისთანავე გააქტიურება
            </label>
            <span style="flex:1;"></span>
            <span class="ks-muted" style="font-size:13px;" id="qiCount"></span>
            <button type="submit" class="ks-btn ks-btn-primary"><i class="bi bi-save"></i> მონიშნულების შენახვა</button>
        </div>
    </form>
    @foreach($result['items'] as $__it)
        @if($__it['status'] !== 'saved')
        <form id="qiF{{ $__it['index'] }}" method="POST" action="{{ route('admin.questions.import.formula') }}" hidden>@csrf<input type="hidden" name="index" value="{{ $__it['index'] }}"></form>
        @endif
    @endforeach
    @endif
</section>
@endif

<script>
(function () {
    var btn = document.getElementById('qiCopy'), ta = document.getElementById('qiPrompt');
    if (btn && ta) btn.onclick = function () {
        var done = function () { btn.innerHTML = '<i class="bi bi-check2"></i> დაკოპირდა'; setTimeout(function () { btn.innerHTML = '<i class="bi bi-clipboard"></i> კოპირება'; }, 2000); };
        if (navigator.clipboard) navigator.clipboard.writeText(ta.value).then(done, function () { ta.select(); document.execCommand('copy'); done(); });
        else { ta.select(); document.execCommand('copy'); done(); }
    };
    var form = document.getElementById('qiSaveForm'), cnt = document.getElementById('qiCount');
    function upd() { if (cnt && form) cnt.textContent = 'მონიშნულია: ' + form.querySelectorAll('input[name="pick[]"]:checked').length; }
    if (form) { form.addEventListener('change', upd); upd(); }
    document.querySelectorAll('[data-qi-tab]').forEach(function (b) {
        b.onclick = function () {
            document.querySelectorAll('[data-qi-tab]').forEach(function (x) { x.classList.toggle('on', x === b); });
            document.querySelectorAll('[data-qi-panel]').forEach(function (p) { p.style.display = p.getAttribute('data-qi-panel') === b.getAttribute('data-qi-tab') ? 'flex' : 'none'; });
        };
    });
    // ფორმულის რედაქტორი: ცოცხალი გადათვლა ორიგინალი რიცხვებით
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
        var e = f.replace(/[A-Za-z_][A-Za-z0-9_]*/g, function (w) {
            if (Object.prototype.hasOwnProperty.call(vals, w)) return '(' + Number(vals[w]) + ')';
            if (Object.prototype.hasOwnProperty.call(MF, w)) return 'MF.' + w;
            bad = w; return '0';
        });
        if (bad) return { err: '„' + bad + '“ უცნობია' };
        if (/[^0-9+\-*\/().,\sMF.a-z]/.test(e)) return { err: 'დაუშვებელი სიმბოლო' };
        try { var v = Function('MF', 'return (' + e + ');')(MF); return Number.isFinite(v) ? { v: v } : { err: 'შეცდომა' }; }
        catch (_) { return { err: 'ფორმულა დაუმთავრებელია' }; }
    }
    document.querySelectorAll('.qi-fx').forEach(function (box) {
        var inp = box.querySelector('.qi-fx-in'), res = box.querySelector('.qi-fx-res');
        var vals = {}, opts = [];
        try { vals = JSON.parse(box.getAttribute('data-vals') || '{}'); opts = JSON.parse(box.getAttribute('data-opts') || '[]'); } catch (_) {}
        function show() {
            if (!Object.keys(vals).length) { res.textContent = ''; return; }
            var r = fxEval(inp.value, vals);
            if (r.err) { res.className = 'qi-fx-res no'; res.textContent = r.err; return; }
            var v = r.v, whole = Math.floor(v) === v;
            if (!whole || v <= 0) { res.className = 'qi-fx-res no'; res.textContent = '= ' + (whole ? v : v.toFixed(2)) + ' (მთელი დადებითი უნდა იყოს)'; return; }
            if (opts.length) {
                var hit = opts.indexOf(v) !== -1;
                res.className = 'qi-fx-res ' + (hit ? 'ok' : 'mid');
                res.textContent = '= ' + v + (hit ? ' ✓ ვარიანტებშია' : ' — ვარიანტებში არ არის');
            } else { res.className = 'qi-fx-res ok'; res.textContent = '= ' + v; }
        }
        inp.addEventListener('input', show); show();
        box.querySelectorAll('[data-ins]').forEach(function (b) {
            b.onclick = function () {
                var t = b.getAttribute('data-ins'), s0 = inp.selectionStart ?? inp.value.length, s1 = inp.selectionEnd ?? inp.value.length;
                inp.value = inp.value.slice(0, s0) + t + inp.value.slice(s1);
                inp.focus(); inp.selectionStart = inp.selectionEnd = s0 + t.length; show();
            };
        });
        box.querySelectorAll('[data-set]').forEach(function (b) { b.onclick = function () { inp.value = b.getAttribute('data-set'); inp.focus(); show(); }; });
        inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); inp.form.requestSubmit ? inp.form.requestSubmit() : inp.form.submit(); } });
    });
    // „შენახვა და რედაქტირება“ — მხოლოდ ეს ერთი კითხვა
    window.qiOnly = function (i) {
        form.querySelectorAll('input[name="pick[]"]').forEach(function (c) { c.checked = String(c.value) === String(i); });
    };
})();
</script>
@endsection
