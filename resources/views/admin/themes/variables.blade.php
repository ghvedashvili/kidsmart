@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 760px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 22px 24px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.65rem; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 14px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.8rem; padding: 8px 12px; width: 100%; outline: none; margin-bottom: 8px; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    .fc::placeholder { color: #cbd5e1; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.76rem; letter-spacing: 0.08em; padding: 8px 20px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-del:hover { color: #ef4444; }
    .btn-edit { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 0 4px; transition: color 0.2s; }
    .btn-edit:hover { color: #64748b; }
    .hint { font-size: 0.62rem; color: #94a3b8; margin-top: -4px; margin-bottom: 8px; line-height: 1.5; }
    .err { color: #ef4444; font-size: 0.66rem; margin-bottom: 6px; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .tag { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 3px; padding: 2px 8px; font-size: 0.66rem; color: #64748b; margin: 2px; display: inline-block; }
    .slot-tag { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 3px; padding: 2px 8px; font-size: 0.66rem; color: #059669; margin: 2px; display: inline-block; font-family: monospace; }
    /* group card */
    .grp-card { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 10px; overflow: hidden; }
    .grp-hdr { background: #f8fafc; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .grp-name { font-size: 0.72rem; color: #374151; letter-spacing: 0.1em; font-weight: 600; }
    .grp-body { padding: 10px 14px 12px; }
    .grp-section { font-size: 0.56rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 4px; }
    /* tabs */
    .tab-row { display: flex; gap: 2px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
    .tab { font-family: 'Goldman', monospace; font-size: 0.68rem; letter-spacing: 0.06em; color: #94a3b8; padding: 7px 16px; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; }
    .tab.act { color: #374151; border-bottom-color: #374151; }
    .tab-panel { display: none; }
    .tab-panel.act { display: block; }
    /* two-col grid */
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media(max-width:560px) { .grid2 { grid-template-columns: 1fr; } }
</style>

<div class="aw">
    <a href="{{ route('admin.themes.index') }}" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <span style="font-size:1.6rem;">{{ $theme->icon }}</span>
        <div>
            <div style="color:#555;font-size:0.92rem;">{{ $theme->name }}</div>
            <div style="color:#aaa;font-size:0.66rem;letter-spacing:0.08em;">ცვლადების მართვა</div>
        </div>
    </div>

    {{-- ── ADD SECTION ── --}}
    <div class="card-dark">
        <div class="tab-row">
            <div class="tab act" onclick="showTab('grp',this)">⊞ ჯგუფი (pool)</div>
            <div class="tab" onclick="showTab('single',this)">◻ ერთეული ცვლადი</div>
        </div>

        {{-- Group form --}}
        <div class="tab-panel act" id="tab-grp">
            <form method="POST" action="{{ route('admin.themes.groups.store', $theme) }}">
                @csrf
                <div class="grid2">
                    <div>
                        <div class="hint" style="margin:0 0 4px;">ჯგუფის სახელი</div>
                        <input type="text" name="group_name" class="fc" placeholder="NAMES"
                            oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                            maxlength="50" required value="{{ old('group_name') }}">
                        <div class="hint">მხოლოდ ლათინური: NAMES, PLAYERS, CITIES</div>
                        @error('group_name')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <div class="hint" style="margin:0 0 4px;">სლოტები (ცვლადები)</div>
                        <input type="text" name="slots" class="fc" placeholder="NAME1, NAME2, NAME3, NAME4"
                            maxlength="200" required value="{{ old('slots') }}">
                        <div class="hint">მძიმით გამოყოფილი სახელები: NAME1, NAME2 ...</div>
                        @error('slots')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="hint" style="margin:4px 0 4px;">Pool — მნიშვნელობები (მძიმით)</div>
                <textarea name="pool" class="fc" rows="2"
                    placeholder="ანა, გიო, ლუკა, მარი, ბეკა, ნინო, სოფო, დათო" required>{{ old('pool') }}</textarea>
                @error('pool')<div class="err">{{ $message }}</div>@enderror
                <button type="submit" class="btn">+ ჯგუფის შენახვა</button>
            </form>
        </div>

        {{-- Standalone form --}}
        <div class="tab-panel" id="tab-single">
            <form method="POST" action="{{ route('admin.themes.variables.store', $theme) }}">
                @csrf
                <input type="text" name="variable_name" class="fc" placeholder="ცვლადის სახელი, მაგ: CITY"
                    oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                    maxlength="50" required value="{{ old('variable_name') }}">
                <div class="hint">მხოლოდ ლათინური: CITY, TEAM, OBJECT</div>
                <textarea name="values" class="fc" rows="2"
                    placeholder="მნიშვნელობები მძიმით: თბილისი, ბათუმი, ქუთაისი" required>{{ old('values') }}</textarea>
                @error('variable_name')<div class="err">{{ $message }}</div>@enderror
                @error('values')<div class="err">{{ $message }}</div>@enderror
                <button type="submit" class="btn">+ ცვლადის შენახვა</button>
            </form>
        </div>
    </div>

    {{-- ── GROUPS LIST ── --}}
    @if($theme->varGroups->isNotEmpty())
    <div class="card-dark">
        <div class="card-label">ჯგუფები · {{ $theme->varGroups->count() }}</div>
        @foreach($theme->varGroups as $group)
        <div class="grp-card">
            {{-- View mode header --}}
            <div class="grp-hdr" id="grp-hdr-{{ $group->id }}">
                <span class="grp-name">{{ $group->name }}</span>
                <div style="display:flex;gap:6px;align-items:center;">
                    <button type="button" class="btn-edit" onclick="toggleGrpEdit({{ $group->id }})">✎</button>
                    <form method="POST" action="{{ route('admin.themes.groups.destroy', $group) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-del" onclick="return confirm('ჯგუფი და მისი ყველა ცვლადი წაიშალოს?')">✕</button>
                    </form>
                </div>
            </div>

            {{-- Edit mode form --}}
            <div id="grp-edit-{{ $group->id }}" style="display:none;padding:12px 14px;border-bottom:1px solid #f1f5f9;background:#fafafa;">
                <form method="POST" action="{{ route('admin.themes.groups.update', $group) }}">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:160px 1fr;gap:8px;align-items:end;margin-bottom:8px;">
                        <div>
                            <div style="font-size:0.56rem;color:#94a3b8;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:3px;">სახელი</div>
                            <input type="text" name="group_name" class="fc" style="margin:0;"
                                value="{{ $group->name }}"
                                oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                                maxlength="50" required>
                        </div>
                        <div>
                            <div style="font-size:0.56rem;color:#94a3b8;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:3px;">Pool (მძიმით)</div>
                            <textarea name="pool" class="fc" rows="2" style="margin:0;resize:none;" required>{{ implode(', ', $group->values ?? []) }}</textarea>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#059669;font-family:'Goldman',monospace;font-size:0.7rem;padding:5px 14px;border-radius:4px;cursor:pointer;">შენახვა</button>
                        <button type="button" onclick="toggleGrpEdit({{ $group->id }})" style="background:none;border:none;color:#94a3b8;font-family:'Goldman',monospace;font-size:0.68rem;cursor:pointer;">გაუქმება</button>
                    </div>
                </form>
            </div>

            <div class="grp-body">
                <div class="grp-section">სლოტები</div>
                <div style="margin-bottom:8px;display:flex;align-items:center;flex-wrap:wrap;gap:4px;">
                    @forelse($group->variables as $v)
                    {{-- view --}}
                    <span class="slot-tag-wrap" id="slot-view-{{ $v->id }}" style="display:inline-flex;align-items:center;gap:2px;">
                        <span class="slot-tag">&#123;&#123;{{ $v->variable_name }}&#125;&#125;</span>
                        <button type="button" class="btn-edit" style="font-size:0.6rem;padding:0 2px;" onclick="toggleSlotEdit({{ $v->id }})">✎</button>
                        <form method="POST" action="{{ route('admin.themes.variables.destroy', $v) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-del" style="font-size:0.6rem;padding:0 2px;" onclick="return confirm('სლოტი წაიშალოს?')">✕</button>
                        </form>
                    </span>
                    {{-- edit --}}
                    <form method="POST" action="{{ route('admin.themes.variables.update', $v) }}"
                          id="slot-edit-{{ $v->id }}"
                          style="display:none;align-items:center;gap:3px;">
                        @csrf @method('PUT')
                        <input type="text" name="variable_name" class="fc"
                               style="width:90px;margin:0;padding:3px 6px;font-size:0.68rem;"
                               value="{{ $v->variable_name }}"
                               oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                               maxlength="50" required>
                        <button type="submit" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#059669;font-family:'Goldman',monospace;font-size:0.6rem;padding:3px 7px;border-radius:3px;cursor:pointer;">✓</button>
                        <button type="button" onclick="toggleSlotEdit({{ $v->id }})" style="background:none;border:none;color:#94a3b8;font-size:0.68rem;cursor:pointer;">✕</button>
                    </form>
                    @empty
                    <span style="font-size:0.66rem;color:#94a3b8;">სლოტი არ არის</span>
                    @endforelse
                    <form method="POST" action="{{ route('admin.themes.groups.addSlot', $group) }}"
                          style="display:flex;gap:4px;align-items:center;margin-left:4px;">
                        @csrf
                        <input type="text" name="slot_name" class="fc"
                               style="width:100px;margin:0;padding:3px 7px;font-size:0.68rem;"
                               placeholder="NAME2"
                               oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')"
                               maxlength="50" required>
                        <button type="submit" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#059669;font-family:'Goldman',monospace;font-size:0.62rem;padding:3px 9px;border-radius:3px;cursor:pointer;white-space:nowrap;">+ სლოტი</button>
                    </form>
                </div>
                <div class="grp-section">Pool</div>
                <div>
                    @foreach($group->values as $val)
                    <span class="tag">{{ $val }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── STANDALONE VARS LIST ── --}}
    @php $standalone = $theme->variables->filter(fn($v) => is_null($v->group_id)); @endphp
    @if($standalone->isNotEmpty())
    <div class="card-dark">
        <div class="card-label">ერთეული ცვლადები · {{ $standalone->count() }}</div>
        @foreach($standalone as $var)
        <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;gap:12px;">
            <div style="flex:1;">
                <div style="color:#888;margin-bottom:5px;font-size:0.72rem;font-family:monospace;">
                    &#123;&#123;{{ $var->variable_name }}&#125;&#125;
                </div>
                @foreach($var->values ?? [] as $v)
                <span class="tag">{{ $v }}</span>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.themes.variables.destroy', $var) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function toggleGrpEdit(id) {
    const el = document.getElementById('grp-edit-' + id);
    el.style.display = el.style.display === 'none' ? '' : 'none';
}
function toggleSlotEdit(id) {
    const view = document.getElementById('slot-view-' + id);
    const edit = document.getElementById('slot-edit-' + id);
    const show = edit.style.display === 'none';
    view.style.display = show ? 'none' : 'inline-flex';
    edit.style.display = show ? 'inline-flex' : 'none';
    if (show) edit.querySelector('input').focus();
}
function showTab(id, el) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('act'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('act'));
    el.classList.add('act');
    document.getElementById('tab-' + id).classList.add('act');
}
@if($errors->has('group_name') || $errors->has('pool') || $errors->has('slots'))
showTab('grp', document.querySelector('.tab'));
@elseif($errors->has('variable_name') || $errors->has('values'))
showTab('single', document.querySelectorAll('.tab')[1]);
@endif
</script>
@endsection
