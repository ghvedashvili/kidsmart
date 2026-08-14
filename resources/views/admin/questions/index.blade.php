@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 860px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #64748b; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #1e293b; border-color: #94a3b8; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    select.fc { cursor: pointer; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .q-row { padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .q-row:last-child { border-bottom: none; }
    .q-text { color: #374151; font-size: 0.8rem; line-height: 1.5; margin-bottom: 6px; }
    .var-num { color: #2563eb; font-weight: 700; background: #eff6ff; border-radius: 3px; padding: 0 4px; }
    .var-str { color: #7c3aed; }
    .q-regen { background: none; border: none; color: #cbd5e1; font-size: 0.7rem; cursor: pointer; padding: 0; margin-left: 4px; transition: color 0.15s; }
    .q-regen:hover { color: #64748b; }
    .q-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .q-tag { font-size: 0.65rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 2px; padding: 1px 7px; }
    .diff { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .pagination-wrap { margin-top: 20px; display: flex; gap: 6px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { font-family: 'Goldman', monospace; font-size: 0.68rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 3px; padding: 4px 10px; text-decoration: none; }
    .pagination-wrap a:hover { color: #1e293b; border-color: #94a3b8; }
    .pagination-wrap span.active-page { color: #1e293b; border-color: #94a3b8; }
    .q-check { width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; accent-color: #374151; }
    .pdf-bar { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1e293b; border-radius: 10px; padding: 12px 20px; display: none; align-items: center; gap: 14px; box-shadow: 0 8px 32px rgba(0,0,0,0.25); z-index: 100; white-space: nowrap; }
    .pdf-bar.show { display: flex; }
    .pdf-count { font-family: 'Goldman', monospace; font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.06em; }
    .pdf-btn { background: #fff; border: none; border-radius: 6px; color: #1e293b; font-family: 'Goldman', monospace; font-size: 0.75rem; letter-spacing: 0.06em; padding: 8px 18px; cursor: pointer; }
    .pdf-btn:hover { background: #f1f5f9; }
    .pdf-clear { background: none; border: none; color: #64748b; font-size: 0.8rem; cursor: pointer; padding: 0; }
    .pdf-clear:hover { color: #fff; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .atitle { display: none; }
        .anav { gap: 3px; margin-bottom: 14px; }
        .anav a { font-size: 0.6rem; padding: 3px 7px; }
        .filters { gap: 6px; }
        .filters select, .filters input { font-size: 0.7rem; padding: 6px 8px; }
        .card-dark { padding: 14px; }
        .q-text { font-size: 0.75rem; }
        .pdf-bar { width: calc(100% - 24px); left: 12px; transform: none; bottom: 70px; flex-wrap: wrap; gap: 8px; padding: 10px 14px; }
    }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემატიკა</a>
        <a href="{{ route('admin.topics.index') }}">თემები</a>
        <a href="{{ route('admin.questions.index') }}" class="active">კითხვები</a>
        <a href="{{ route('admin.users.index') }}">მომხმარებლები</a>
        <a href="{{ route('admin.permissions.index') }}">ნებართვები</a>
        <a href="{{ route('admin.packages.index') }}">პაკეტები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" id="checkAll" class="q-check" title="ყველა მონიშვნა">
            <div style="color:#555;font-size:0.78rem;">სულ: {{ $templates->total() }} შაბლონი</div>
        </div>
        <a href="{{ route('admin.questions.create') }}" class="btn">+ ახალი შაბლონი</a>
    </div>

    <form method="GET" class="filters">
        <select name="grade_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა კლასი</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>
                {{ $grade->name }}
            </option>
            @endforeach
        </select>
        <select name="topic_id" class="fc" onchange="this.form.submit()">
            <option value="">ყველა თემა</option>
            @foreach($topics as $topic)
            <option value="{{ $topic->id }}" {{ ($filters['topic_id'] ?? '') == $topic->id ? 'selected' : '' }}>
                {{ $topic->grade->name }} / {{ $topic->name }}
            </option>
            @endforeach
        </select>
        <select name="difficulty" class="fc" onchange="this.form.submit()">
            <option value="">ყველა დონე</option>
            @for($i=1;$i<=5;$i++)
            <option value="{{ $i }}" {{ ($filters['difficulty'] ?? '') == $i ? 'selected' : '' }}>დონე {{ $i }}</option>
            @endfor
        </select>
        @if(array_filter($filters))
        <a href="{{ route('admin.questions.index') }}" style="color:#555;font-size:0.72rem;text-decoration:none;padding:9px 0;">× გასუფთავება</a>
        @endif
    </form>

    <div class="card-dark">
        @forelse($templates as $tpl)
        <div class="q-row" style="display:flex;gap:10px;align-items:flex-start;">
            <input type="checkbox" class="q-check q-item" value="{{ $tpl->id }}" style="margin-top:3px;" onchange="updateBar()">
            <div style="flex:1;">
            <div class="q-text q-preview"
                data-tpl="{{ $tpl->template_text }}"
                data-nc='@json($tpl->num_config ?? [])'>{{ $tpl->template_text }}</div>
            <div class="q-meta">
                <span class="q-tag">{{ $tpl->topic->grade->name }}</span>
                <span class="q-tag">{{ $tpl->topic->name }}</span>
                <span class="q-tag">დონე {{ $tpl->difficulty }}</span>
                <span class="q-tag" style="color:#888;">= {{ $tpl->correct_formula }}</span>
                <button type="button" class="q-regen" onclick="regenPreview(this)" title="ახალი მაგალითი">↺</button>
                <div style="flex:1;"></div>
                <a href="{{ route('admin.questions.edit', $tpl) }}" style="color:#555;font-size:0.7rem;text-decoration:none;padding:2px 8px;border:1px solid #222;border-radius:2px;">რედ.</a>
                <form method="POST" action="{{ route('admin.questions.destroy', $tpl) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
                </form>
            </div>
            </div>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">შაბლონი არ არის</div>
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
    <button class="pdf-clear" onclick="clearAll()" title="გასუფთავება">✕</button>
</div>

<script>
const THEME_VARS = @json($themeVarMap);
@verbatim
function genVars(nc) {
    const vars = {};
    for (const [name, conf] of Object.entries(nc)) {
        const step = Math.max(1, conf.step || 1);
        const steps = Math.floor((conf.max - conf.min) / step);
        vars[name] = conf.min + Math.floor(Math.random() * (steps + 1)) * step;
    }
    return vars;
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

document.querySelectorAll('.q-preview').forEach(renderPreview);
@endverbatim

const bar      = document.getElementById('pdfBar');
const countEl  = document.getElementById('pdfCount');
const inputs   = document.getElementById('pdfInputs');
const checkAll = document.getElementById('checkAll');

function updateBar() {
    const checked = [...document.querySelectorAll('.q-item:checked')];
    bar.classList.toggle('show', checked.length > 0);
    countEl.textContent = checked.length + ' მონიშნული';
    inputs.innerHTML = checked.map(c => `<input type="hidden" name="ids[]" value="${c.value}">`).join('');
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
</script>
@endsection
