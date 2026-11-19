@extends('layouts.app')
{{-- redesign-kidsmart: მიღწევის ფორმა (დიზაინი A8) --}}
@section('content')
<style>
    .tier-row { display: flex; flex-direction: column; gap: 8px; background: var(--ks-bg-admin); border-radius: 14px; padding: 12px; position: relative; }
    .tier-num { font-size: 13px; font-weight: 800; }
    .tier-grid { display: grid; grid-template-columns: 1fr 140px; gap: 8px; }
    .tier-img-row { display: flex; align-items: center; gap: 10px; }
    .tier-img-preview { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
    .tier-remove { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--ks-red-soft); color: var(--ks-red-dark); font-weight: 800; cursor: pointer; }
    @media (max-width: 520px) { .tier-grid { grid-template-columns: 1fr; } }
</style>

<header class="ks-page-head">
    <div class="ks-page-title">
        <nav aria-label="ნავიგაცია" class="ks-eyebrow"><a href="{{ route('admin.achievements.index') }}" style="color:var(--ks-orange-dark);font-weight:700;">მიღწევები</a> / {{ $achievement ? 'რედაქტირება' : 'ახალი' }}</nav>
        <h1 class="ks-display ks-h1">{{ $achievement ? $achievement->name : 'ახალი მიღწევა' }}</h1>
    </div>
</header>

<form method="POST"
      action="{{ $achievement ? route('admin.achievements.update', $achievement) : route('admin.achievements.store') }}"
      enctype="multipart/form-data"
      style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
    @csrf
    @if($achievement) @method('PUT') @endif

    <div class="ks-card" style="flex:3 1 440px;display:flex;flex-direction:column;gap:12px;">
        <h2 class="ks-h2">ძირითადი</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;">
            <div class="ks-field">
                <label class="ks-label" for="achName">სახელი</label>
                <input type="text" name="name" id="achName" class="ks-input" value="{{ old('name', $achievement?->name) }}" required>
                @error('name')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
            <div class="ks-field">
                <label class="ks-label" for="achSlug">Slug <span class="ks-muted" style="font-weight:500;">(უნიკალური)</span></label>
                <input type="text" name="slug" id="achSlug" class="ks-input ks-mono" value="{{ old('slug', $achievement?->slug) }}" required>
                @error('slug')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
            <div class="ks-field">
                <label class="ks-label" for="achContext">კატეგორია <span class="ks-muted" style="font-weight:500;">— რა აქტივობა ამოწმებს</span></label>
                <select name="context" id="achContext" class="ks-select" onchange="achOnContextChange()">
                    @foreach($contexts as $key => $label)
                    <option value="{{ $key }}" {{ old('context', $achievement?->context ?? 'test') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('context')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
            <div class="ks-field">
                <label class="ks-label" for="achTheme">თემატიკა <span class="ks-muted" style="font-weight:500;">— მხოლოდ ამ თემატიკის ბავშვებს</span></label>
                <select name="theme_id" id="achTheme" class="ks-select">
                    <option value="">ნებისმიერი თემატიკა</option>
                    @foreach($themes as $theme)
                    <option value="{{ $theme->id }}" {{ old('theme_id', $achievement?->theme_id) == $theme->id ? 'selected' : '' }}>{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                    @endforeach
                </select>
                @error('theme_id')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
            <div class="ks-field">
                <label class="ks-label" for="achConditionType">პირობის ტიპი</label>
                <select name="condition_type" id="achConditionType" class="ks-select" onchange="achOnConditionChange()">
                    @foreach($conditionTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('condition_type', $achievement?->condition_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('condition_type')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
            <div class="ks-field" id="achGameField" style="display:none;">
                <label class="ks-label" for="achGame">თამაში <span class="ks-muted" style="font-weight:500;">(ცარიელი = ყველა)</span></label>
                <input type="text" name="game" id="achGame" class="ks-input ks-mono" value="{{ old('game', $achievement?->game) }}" placeholder="memory, quoridor, rock_paper_scissors, wolves_and_hare">
                @error('game')<span class="ks-chip ks-chip-red">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="ks-field">
            <label class="ks-label" for="achDesc">აღწერა <span class="ks-muted" style="font-weight:500;">— {n} შეიცვლება დონის ზღვრით</span></label>
            <input type="text" name="description" id="achDesc" class="ks-input" value="{{ old('description', $achievement?->description) }}" placeholder="მაგ: დაწერე {n} ტესტი">
        </div>

        <div id="timeOfDayFields" style="display:none;flex-direction:column;gap:8px;">
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
                <div class="ks-field"><label class="ks-label" for="achBefore">საათამდე (მაგ: 09:00)</label><input type="time" id="achBefore" name="condition_before" class="ks-input" value="{{ old('condition_before', $achievement?->condition_config['before'] ?? '') }}"></div>
                <div class="ks-field"><label class="ks-label" for="achAfter">საათის შემდეგ (მაგ: 22:30)</label><input type="time" id="achAfter" name="condition_after" class="ks-input" value="{{ old('condition_after', $achievement?->condition_config['after'] ?? '') }}"></div>
            </div>
            <span class="ks-muted" style="font-size:12px;">შეავსე მხოლოდ ერთ-ერთი.</span>
        </div>

        <div class="ks-note" style="background:var(--ks-orange-soft);color:#4A2A12;">ბავშვი დაინახავს: <b id="achPreview">—</b></div>

        <label style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $achievement?->is_active ?? true) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#C24A0A;">
            აქტიური
        </label>
    </div>

    <div class="ks-card" style="flex:2 1 320px;display:flex;flex-direction:column;gap:10px;position:sticky;top:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
            <h2 class="ks-h2">დონეები</h2>
            <span class="ks-muted" style="font-size:12px;">1 დონე = უბრალო მედალი</span>
        </div>
        <span class="ks-muted" style="font-size:12px;line-height:1.5;">რამდენიმე დონე = პროგრესირებადი (ბრინჯაო → ვერცხლი → ოქრო). ბავშვი თანმიმდევრობით გადადის.</span>

        <div id="tiersWrap" style="display:flex;flex-direction:column;gap:8px;">
            @php $existingTiers = old('tiers', $achievement?->tiers->map(fn($t) => ['label' => $t->label, 'threshold' => $t->threshold])->toArray() ?? [['label' => '', 'threshold' => '']]); @endphp
            @foreach($existingTiers as $i => $tier)
            <div class="tier-row" data-tier-index="{{ $i }}">
                @if($i > 0)<button type="button" class="tier-remove" onclick="achRemoveTier(this)" aria-label="დონის წაშლა">×</button>@endif
                <div class="tier-num">დონე {{ $i + 1 }}</div>
                <div class="tier-grid">
                    <input type="text" name="tiers[{{ $i }}][label]" class="ks-input" aria-label="დონის სახელი" placeholder="სახელი, მაგ: ბრინჯაო" value="{{ $tier['label'] ?? '' }}" required>
                    <input type="number" name="tiers[{{ $i }}][threshold]" class="ks-input tier-threshold" aria-label="ზღვარი (n)" placeholder="ზღვარი n" min="1" value="{{ $tier['threshold'] ?? '' }}">
                </div>
                <div class="tier-img-row">
                    @if($achievement && ($achievement->tiers[$i]->image_path ?? null))
                    <img src="{{ $achievement->tiers[$i]->imageUrl() }}" class="tier-img-preview" alt="">
                    @endif
                    <input type="file" name="tiers[{{ $i }}][image]" accept="image/*" aria-label="მედლის სურათი" style="font-family:var(--ks-font);font-size:13px;">
                </div>
            </div>
            @endforeach
        </div>
        <button type="button" class="ks-btn ks-btn-ghost ks-btn-sm" style="align-self:flex-start;border-style:dashed;" onclick="achAddTier()">+ დონის დამატება</button>
        <button type="submit" class="ks-btn ks-btn-primary" style="margin-top:6px;">შენახვა</button>
    </div>
</form>

<script>
const BINARY_TYPES = @json(\App\Models\Achievement::BINARY_TYPES);
let tierIndex = document.querySelectorAll('.tier-row').length;
let slugTouched = {{ $achievement ? 'true' : 'false' }};

document.getElementById('achName').addEventListener('input', function() {
    if (slugTouched) return;
    document.getElementById('achSlug').value = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s_-]/g, '')
        .trim()
        .replace(/[\s-]+/g, '_');
});
document.getElementById('achSlug').addEventListener('input', function() { slugTouched = true; });

function achUpdatePreview() {
    const desc = document.getElementById('achDesc').value || document.getElementById('achName').value || '—';
    const first = document.querySelector('.tier-threshold');
    const n = first && first.value ? first.value : 'n';
    document.getElementById('achPreview').textContent = desc.split('{n}').join(n);
}
document.getElementById('achDesc').addEventListener('input', achUpdatePreview);
document.getElementById('tiersWrap').addEventListener('input', achUpdatePreview);

function achOnConditionChange() {
    const type = document.getElementById('achConditionType').value;
    document.getElementById('timeOfDayFields').style.display = type === 'time_of_day' ? 'flex' : 'none';
    const isBinary = BINARY_TYPES.includes(type);
    document.querySelectorAll('.tier-threshold').forEach(inp => {
        inp.style.display = isBinary ? 'none' : '';
        inp.disabled = isBinary;
    });
}

function achOnContextChange() {
    const context = document.getElementById('achContext').value;
    document.getElementById('achGameField').style.display = context === 'games' ? 'flex' : 'none';
}

function achAddTier() {
    const wrap = document.getElementById('tiersWrap');
    const i = tierIndex++;
    const div = document.createElement('div');
    div.className = 'tier-row';
    div.innerHTML = `
        <button type="button" class="tier-remove" onclick="achRemoveTier(this)" aria-label="დონის წაშლა">×</button>
        <div class="tier-num">დონე ${i + 1}</div>
        <div class="tier-grid">
            <input type="text" name="tiers[${i}][label]" class="ks-input" aria-label="დონის სახელი" placeholder="სახელი, მაგ: ვერცხლი" required>
            <input type="number" name="tiers[${i}][threshold]" class="ks-input tier-threshold" aria-label="ზღვარი (n)" placeholder="ზღვარი n" min="1">
        </div>
        <div class="tier-img-row">
            <input type="file" name="tiers[${i}][image]" accept="image/*" aria-label="მედლის სურათი" style="font-family:var(--ks-font);font-size:13px;">
        </div>
    `;
    wrap.appendChild(div);
    achRenumber();
    achOnConditionChange();
}

function achRemoveTier(btn) {
    btn.closest('.tier-row').remove();
    achRenumber();
    achUpdatePreview();
}

function achRenumber() {
    document.querySelectorAll('#tiersWrap .tier-row').forEach((row, idx) => {
        row.querySelector('.tier-num').textContent = 'დონე ' + (idx + 1);
    });
}

achOnConditionChange();
achOnContextChange();
achUpdatePreview();
</script>
@endsection
