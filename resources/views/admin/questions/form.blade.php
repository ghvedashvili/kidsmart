@extends('layouts.app')
@section('content')
<style>
    body { background: #0d0d0d !important; }
    .aw { max-width: 700px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #555; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #555; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #222; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #bbb; border-color: #444; }
    .card-dark { background: #111; border: 1px solid #1e1e1e; border-radius: 8px; padding: 28px; margin-bottom: 20px; }
    .lbl { font-size: 0.68rem; color: #555; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px; }
    .hint { font-size: 0.67rem; color: #444; margin-top: -4px; margin-bottom: 12px; line-height: 1.5; }
    .fc { background: #0d0d0d; border: 1px solid #2a2a2a; border-radius: 4px; color: #ccc; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 12px; box-sizing: border-box; }
    .fc:focus { border-color: #444; }
    .fc::placeholder { color: #3a3a3a; }
    textarea.fc { resize: vertical; min-height: 90px; line-height: 1.6; }
    select.fc { cursor: pointer; }
    .btn { background: #1a1a1a; border: 1px solid #2a2a2a; color: #bbb; font-family: 'Goldman', monospace; font-size: 0.8rem; letter-spacing: 0.08em; padding: 10px 28px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #444; color: #fff; }
    .err { color: #e74c3c; font-size: 0.72rem; margin-top: -8px; margin-bottom: 10px; }
    .diff-btns { display: flex; gap: 6px; margin-bottom: 12px; }
    .diff-btn { background: none; border: 1px solid #222; color: #444; font-family: 'Goldman', monospace; font-size: 0.72rem; padding: 6px 14px; border-radius: 3px; cursor: pointer; transition: all 0.2s; }
    .diff-btn.sel { border-color: #555; color: #bbb; background: #1a1a1a; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}" class="active">კითხვები</a>
    </nav>

    <form method="POST" action="{{ $template ? route('admin.questions.update', $template) : route('admin.questions.store') }}">
        @csrf
        @if($template) @method('PUT') @endif

        <div class="card-dark">
            <div class="lbl">თოპიქი</div>
            <select name="topic_id" class="fc" required>
                <option value="">— აირჩიე —</option>
                @foreach($topics as $topic)
                <option value="{{ $topic->id }}"
                    {{ old('topic_id', $template?->topic_id) == $topic->id ? 'selected' : '' }}>
                    {{ $topic->grade->name }} / {{ $topic->name }}
                </option>
                @endforeach
            </select>
            @error('topic_id')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">სირთულის დონე</div>
            <div class="diff-btns">
                @for($i=1;$i<=5;$i++)
                <button type="button" class="diff-btn {{ old('difficulty', $template?->difficulty ?? 1) == $i ? 'sel' : '' }}"
                    onclick="setDiff({{ $i }})">{{ $i }}</button>
                @endfor
            </div>
            <input type="hidden" name="difficulty" id="diffInput" value="{{ old('difficulty', $template?->difficulty ?? 1) }}">

            <div class="lbl">შაბლონის ტექსტი</div>
            <textarea name="template_text" class="fc"
                placeholder="@{{TEAM}}-მა გაიტანა @{{N1}} გოლი პირველ ტაიმში და @{{N2}} მეორეში. სულ რამდენი გოლი?"
                required>{{ old('template_text', $template?->template_text) }}</textarea>
            <div class="hint">
                ცვლადები: @{{TEAM}}, @{{PLAYER}} — თემიდან იღებს მნიშვნელობას<br>
                რიცხვები: @{{N1}}, @{{N2}} — num_config-ში განსაზღვრული დიაპაზონიდან
            </div>
            @error('template_text')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">სწორი პასუხის ფორმულა</div>
            <input type="text" name="correct_formula" class="fc"
                placeholder="N1+N2"
                value="{{ old('correct_formula', $template?->correct_formula) }}" required>
            <div class="hint">PHP გამოთვლება: N1+N2, N1*N2, N1-N2 და ა.შ.</div>
            @error('correct_formula')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">რიცხვების კონფიგი (JSON)</div>
            <textarea name="num_config" class="fc" rows="4"
                placeholder='{"N1":{"min":1,"max":9},"N2":{"min":1,"max":9}}'>{{ old('num_config', $template ? json_encode($template->num_config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            <div class="hint">თითოეული N ცვლადისთვის min/max დიაპაზონი</div>
            @error('num_config')<div class="err">{{ $message }}</div>@enderror

            <div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
                <button type="submit" class="btn">{{ $template ? 'განახლება' : 'შენახვა' }}</button>
                <a href="{{ route('admin.questions.index') }}"
                    style="color:#444;font-size:0.72rem;text-decoration:none;">გაუქმება</a>
            </div>
        </div>
    </form>
</div>

<script>
function setDiff(n) {
    document.getElementById('diffInput').value = n;
    document.querySelectorAll('.diff-btn').forEach((b, i) => {
        b.classList.toggle('sel', i + 1 === n);
    });
}
</script>
@endsection
