@extends('layouts.app')
{{-- redesign-kidsmart: თემატიკის ცვლადები (დიზაინი A6) --}}
@section('content')
<style>
    .ks-vtabs { display: inline-flex; gap: 4px; background: #F1E8DE; border-radius: 12px; padding: 4px; align-self: flex-start; }
    .ks-vtab { min-height: 38px; padding: 0 14px; border-radius: 9px; border: none; background: transparent; color: var(--ks-ink-2); font-family: var(--ks-font); font-size: 13px; font-weight: 700; cursor: pointer; }
    .ks-vtab.act { background: #fff; color: var(--ks-ink); }
    .ks-vpanel { display: none; }
    .ks-vpanel.act { display: block; }
    .ks-pool-tag { display: inline-flex; font-size: 13px; font-weight: 700; background: #fff; border: 1px solid var(--ks-line); border-radius: 999px; padding: 5px 12px; margin: 0 6px 6px 0; }
    .ks-slot { font-family: var(--ks-mono); font-size: 13px; font-weight: 800; background: var(--ks-purple-soft); color: var(--ks-purple-dark); border-radius: 8px; padding: 4px 8px; }
    .ks-icon-btn { min-width: 30px; min-height: 30px; border-radius: 8px; border: none; background: transparent; cursor: pointer; font-size: 13px; font-weight: 800; color: var(--ks-muted); }
    .ks-icon-btn:hover { background: #F1E8DE; color: var(--ks-ink); }
    .ks-icon-btn.del:hover { background: var(--ks-red-soft); color: var(--ks-red-dark); }
</style>

<header class="ks-page-head">
    <div class="ks-page-title">
        <nav aria-label="ნავიგაცია" class="ks-eyebrow"><a href="{{ route('admin.themes.index') }}" style="color:var(--ks-orange-dark);font-weight:700;">თემატიკა</a> / ცვლადები</nav>
        <h1 class="ks-display ks-h1">{{ $theme->icon }} {{ $theme->name }}</h1>
    </div>
</header>

@if(session('success'))
<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>
@endif

<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
    <div style="flex:3 1 460px;min-width:0;display:flex;flex-direction:column;gap:14px;">

        {{-- ჯგუფები --}}
        <div class="ks-card" style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                <h2 class="ks-h2" style="flex-grow:1;">ჯგუფები · {{ $theme->varGroups->count() }}</h2>
                <span class="ks-muted" style="font-size:12px;">ჯგუფი = სიტყვების საერთო მარაგი; სლოტები ერთ კითხვაში არ მეორდება</span>
            </div>
            @forelse($theme->varGroups as $group)
            <div style="background:var(--ks-bg-admin);border-radius:16px;padding:14px;display:flex;flex-direction:column;gap:10px;">
                <div id="grp-hdr-{{ $group->id }}" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                    <span class="ks-mono" style="font-size:15px;font-weight:800;background:var(--ks-ink);color:#fff;border-radius:8px;padding:4px 10px;">{{ $group->name }}</span>
                    <span style="flex-grow:1;"></span>
                    <button type="button" class="ks-btn ks-btn-soft ks-btn-sm" onclick="toggleGrpEdit({{ $group->id }})">რედ.</button>
                    <form method="POST" action="{{ route('admin.themes.groups.destroy', $group) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('ჯგუფი და მისი ყველა ცვლადი წაიშალოს?')" aria-label="ჯგუფის წაშლა">×</button>
                    </form>
                </div>

                <form id="grp-edit-{{ $group->id }}" method="POST" action="{{ route('admin.themes.groups.update', $group) }}" style="display:none;flex-direction:column;gap:8px;background:#fff;border-radius:12px;padding:12px;">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:minmax(120px,180px) 1fr;gap:8px;">
                        <div class="ks-field"><label class="ks-label" for="gn{{ $group->id }}">სახელი</label><input type="text" id="gn{{ $group->id }}" name="group_name" class="ks-input ks-mono" value="{{ $group->name }}" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" maxlength="50" required></div>
                        <div class="ks-field"><label class="ks-label" for="gp{{ $group->id }}">მნიშვნელობები (მძიმით)</label><textarea id="gp{{ $group->id }}" name="pool" class="ks-textarea" rows="2" required>{{ implode(', ', $group->values ?? []) }}</textarea></div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm">შენახვა</button>
                        <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" onclick="toggleGrpEdit({{ $group->id }})">გაუქმება</button>
                    </div>
                </form>

                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;">
                    <span class="ks-muted" style="font-size:12px;font-weight:700;">სლოტები:</span>
                    @forelse($group->variables as $v)
                    <span id="slot-view-{{ $v->id }}" style="display:inline-flex;align-items:center;gap:2px;">
                        <span class="ks-slot">&#123;&#123;{{ $v->variable_name }}&#125;&#125;</span>
                        <button type="button" class="ks-icon-btn" onclick="toggleSlotEdit({{ $v->id }})" aria-label="სლოტის რედაქტირება">✎</button>
                        <form method="POST" action="{{ route('admin.themes.variables.destroy', $v) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="ks-icon-btn del" onclick="return confirm('სლოტი წაიშალოს?')" aria-label="სლოტის წაშლა">×</button>
                        </form>
                    </span>
                    <form method="POST" action="{{ route('admin.themes.variables.update', $v) }}" id="slot-edit-{{ $v->id }}" style="display:none;align-items:center;gap:4px;">
                        @csrf @method('PUT')
                        <input type="text" name="variable_name" class="ks-input ks-mono" aria-label="სლოტის სახელი" style="width:110px;min-height:34px;" value="{{ $v->variable_name }}" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" maxlength="50" required>
                        <button type="submit" class="ks-btn ks-btn-primary ks-btn-sm" aria-label="შენახვა">✓</button>
                        <button type="button" class="ks-icon-btn" onclick="toggleSlotEdit({{ $v->id }})" aria-label="გაუქმება">×</button>
                    </form>
                    @empty
                    <span class="ks-muted" style="font-size:12px;">სლოტი არ არის</span>
                    @endforelse
                    <form method="POST" action="{{ route('admin.themes.groups.addSlot', $group) }}" style="display:flex;gap:4px;align-items:center;margin-left:4px;">
                        @csrf
                        <input type="text" name="slot_name" class="ks-input ks-mono" aria-label="ახალი სლოტი" style="width:110px;min-height:34px;" placeholder="NAME2" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" maxlength="50" required>
                        <button type="submit" class="ks-btn ks-btn-ghost ks-btn-sm" style="border-style:dashed;">+ სლოტი</button>
                    </form>
                </div>
                <div>
                    @foreach($group->values as $val)
                    <span class="ks-pool-tag">{{ $val }}</span>
                    @endforeach
                </div>
            </div>
            @empty
            <span class="ks-muted" style="font-size:14px;">ჯგუფი ჯერ არ არის — დაამატე მარჯვნივ.</span>
            @endforelse
        </div>

        {{-- ერთეული ცვლადები --}}
        @php $standalone = $theme->variables->filter(fn($v) => is_null($v->group_id)); @endphp
        <div class="ks-card" style="display:flex;flex-direction:column;gap:8px;">
            <h2 class="ks-h2">ერთეული ცვლადები · {{ $standalone->count() }}</h2>
            @forelse($standalone as $var)
            <div style="display:flex;flex-wrap:wrap;align-items:flex-start;gap:10px;border-top:1px solid var(--ks-line-soft);padding-top:10px;">
                <span class="ks-mono" style="font-size:14px;font-weight:800;background:var(--ks-blue-soft);color:var(--ks-blue-dark);border-radius:8px;padding:4px 10px;">&#123;&#123;{{ $var->variable_name }}&#125;&#125;</span>
                <span style="flex:1 1 200px;">
                    @foreach($var->values ?? [] as $v)
                    <span class="ks-pool-tag">{{ $v }}</span>
                    @endforeach
                </span>
                <form method="POST" action="{{ route('admin.themes.variables.destroy', $var) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="ks-btn ks-btn-danger ks-btn-sm" onclick="return confirm('წაიშალოს?')" aria-label="ცვლადის წაშლა">×</button>
                </form>
            </div>
            @empty
            <span class="ks-muted" style="font-size:14px;">ერთეული ცვლადი ჯერ არ არის.</span>
            @endforelse
        </div>
    </div>

    {{-- დამატება --}}
    <div class="ks-card" style="flex:1 1 300px;display:flex;flex-direction:column;gap:12px;position:sticky;top:24px;">
        <h2 class="ks-h2">დამატება</h2>
        <div class="ks-vtabs" role="tablist">
            <button type="button" role="tab" class="ks-vtab act" onclick="showTab('grp',this)">ჯგუფი (pool)</button>
            <button type="button" role="tab" class="ks-vtab" onclick="showTab('single',this)">ერთეული ცვლადი</button>
        </div>

        <div class="ks-vpanel act" id="tab-grp">
            <form method="POST" action="{{ route('admin.themes.groups.store', $theme) }}" style="display:flex;flex-direction:column;gap:10px;">
                @csrf
                <div class="ks-field">
                    <label class="ks-label" for="nGroup">ჯგუფის სახელი <span class="ks-muted" style="font-weight:500;">— მხოლოდ ლათინური</span></label>
                    <input type="text" id="nGroup" name="group_name" class="ks-input ks-mono" placeholder="NAMES" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" maxlength="50" required value="{{ old('group_name') }}">
                    @error('group_name')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
                </div>
                <div class="ks-field">
                    <label class="ks-label" for="nSlots">სლოტები (მძიმით)</label>
                    <input type="text" id="nSlots" name="slots" class="ks-input ks-mono" placeholder="NAME1, NAME2, NAME3" maxlength="200" required value="{{ old('slots') }}">
                    @error('slots')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
                </div>
                <div class="ks-field">
                    <label class="ks-label" for="nPool">მნიშვნელობები (მძიმით)</label>
                    <textarea id="nPool" name="pool" class="ks-textarea" rows="3" placeholder="ანა, გიო, ლუკა, მარი, ბეკა, ნინო" required>{{ old('pool') }}</textarea>
                    @error('pool')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="ks-btn ks-btn-primary">+ ჯგუფის შენახვა</button>
            </form>
        </div>

        <div class="ks-vpanel" id="tab-single">
            <form method="POST" action="{{ route('admin.themes.variables.store', $theme) }}" style="display:flex;flex-direction:column;gap:10px;">
                @csrf
                <div class="ks-field">
                    <label class="ks-label" for="nVar">ცვლადის სახელი <span class="ks-muted" style="font-weight:500;">— მხოლოდ ლათინური</span></label>
                    <input type="text" id="nVar" name="variable_name" class="ks-input ks-mono" placeholder="CITY" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9_]/g,'')" maxlength="50" required value="{{ old('variable_name') }}">
                    @error('variable_name')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
                </div>
                <div class="ks-field">
                    <label class="ks-label" for="nVals">მნიშვნელობები (მძიმით)</label>
                    <textarea id="nVals" name="values" class="ks-textarea" rows="3" placeholder="თბილისი, ბათუმი, ქუთაისი" required>{{ old('values') }}</textarea>
                    @error('values')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="ks-btn ks-btn-primary">+ ცვლადის შენახვა</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleGrpEdit(id) {
    const el = document.getElementById('grp-edit-' + id);
    el.style.display = el.style.display === 'flex' ? 'none' : 'flex';
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
    document.querySelectorAll('.ks-vtab').forEach(t => t.classList.remove('act'));
    document.querySelectorAll('.ks-vpanel').forEach(p => p.classList.remove('act'));
    el.classList.add('act');
    document.getElementById('tab-' + id).classList.add('act');
}
@if($errors->has('group_name') || $errors->has('pool') || $errors->has('slots'))
showTab('grp', document.querySelectorAll('.ks-vtab')[0]);
@elseif($errors->has('variable_name') || $errors->has('values'))
showTab('single', document.querySelectorAll('.ks-vtab')[1]);
@endif
</script>
@endsection
