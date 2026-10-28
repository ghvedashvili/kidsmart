{{--
    One template's card body — checkbox + title/preview/meta/actions. Shared by
    a standalone .q-row and by each tab's member wrapper inside a merged
    .q-group-card (see index.blade.php), so every per-template action (edit,
    delete, reassign, sequence, live-preview) stays identical in both places.
--}}
@unless($showTrashed)
<input type="checkbox" class="q-check q-item" value="{{ $tpl->id }}"
    data-group-id="{{ $tpl->question_group_id ?? '' }}"
    data-difficulty="{{ $tpl->difficulty }}"
    style="margin-top:3px;" onchange="updateBar()">
@endunless
<div style="flex:1;min-width:0;">
@if($tpl->title)
<div class="q-title">★ {{ $tpl->title }}</div>
@endif
<div class="q-text q-preview"
    data-tpl="{{ $tpl->template_text }}"
    data-nc='@json($tpl->num_config ?? [])'>{{ $tpl->template_text }}</div>

<div class="q-meta">
    <span class="q-tag muted">#{{ $tpl->id }}</span>
    <span class="q-tag">{{ $tpl->topic->grade->name }}</span>
    <span class="q-tag">{{ $tpl->topic->name }}</span>
    @if($tpl->theme)
    <span class="q-tag theme">{{ $tpl->theme->icon ?? '' }} {{ $tpl->theme->name }}</span>
    @endif
    @if($tpl->is_olympiad)
    <span class="q-tag oly">🏆 ოლიმპიადა</span>
    @else
    <span class="q-tag">დონე {{ $tpl->difficulty }}</span>
    @endif
    <span class="q-tag muted">= {{ $tpl->correct_formula }}</span>
</div>

<div class="q-actions">
    <div class="q-actions-left">
        @unless($showTrashed)
        <input type="number" class="q-seq" min="1" placeholder="—" value="{{ $tpl->sequence }}"
            data-tpl-id="{{ $tpl->id }}" onchange="saveQSequence(this)" title="რიგითობა თემაში">
        @endunless
        <button type="button" class="q-action-btn" onclick="regenPreview(this)" title="ახალი მაგალითი">↺</button>
        <button type="button" class="q-action-btn" onclick="openLivePreview({{ $tpl->id }})" title="ლაივ პრევიუ">👁</button>
        @unless($showTrashed)
        @php
            $rsData = [
                'data-id'         => $tpl->id,
                'data-grade'      => $tpl->topic->grade_id,
                'data-topic'      => $tpl->topic_id,
                'data-theme'      => $tpl->theme_id,
                'data-difficulty' => $tpl->difficulty,
                'data-qtype'      => $tpl->question_type,
                'data-olympiad'   => $tpl->is_olympiad ? 1 : 0,
            ];
        @endphp
        <button type="button" class="q-action-btn" @foreach($rsData as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            onclick="openReassign(this,'move')" title="კლასის/თემის/თემატიკის/დონის შეცვლა">⇄</button>
        <button type="button" class="q-action-btn" @foreach($rsData as $k => $v) {{ $k }}="{{ $v }}" @endforeach
            onclick="openReassign(this,'copy')" title="დუბლირება სხვა კლასში/თემაში">⎘</button>
        @endunless
    </div>
    <div class="q-actions-right">
        @if($showTrashed)
        <form method="POST" action="{{ route('admin.questions.restore', $tpl->id) }}">
            @csrf
            <button type="submit" class="q-status-btn restore">↺ აღდგენა</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.questions.toggle-active', $tpl) }}">
            @csrf @method('PATCH')
            <button type="submit" class="q-status-btn {{ $tpl->is_active ? 'active' : 'inactive' }}" title="{{ $tpl->is_active ? 'გაჩერება' : 'გააქტიურება' }}">{{ $tpl->is_active ? '✓ აქტიური' : '⏸ გაჩერებული' }}</button>
        </form>
        <a href="{{ route('admin.questions.edit', $tpl) }}" class="q-status-btn">✎ რედ.</a>
        <form method="POST" action="{{ route('admin.questions.destroy', $tpl) }}">
            @csrf @method('DELETE')
            <button type="button" class="q-status-btn danger" onclick="confirmDeleteQuestion(this)">✕</button>
        </form>
        @endif
    </div>
</div>
</div>
