@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 680px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card-label { font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    .lbl { font-size: 0.62rem; color: #94a3b8; letter-spacing: 0.08em; margin: 10px 0 6px; text-transform: uppercase; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.82rem; padding: 9px 13px; width: 100%; outline: none; margin-bottom: 4px; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    .err { font-size: 0.72rem; color: #e74c3c; margin: 4px 0 8px; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 9px 22px; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .btn-save { background:#eef2ff; border-color:#c7d2fe; color:#4338ca; }
    .check-row { display:flex; align-items:center; gap:8px; font-size:0.78rem; color:#374151; margin:12px 0; }

    .tier-row { border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:10px; position:relative; }
    .tier-num { font-size:0.6rem; color:#94a3b8; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:8px; }
    .tier-grid { display:grid; grid-template-columns:1fr 110px; gap:8px; }
    .tier-remove { position:absolute; top:10px; right:10px; background:none; border:none; color:#cbd5e1; cursor:pointer; font-size:0.8rem; }
    .tier-remove:hover { color:#ef4444; }
    .tier-img-row { display:flex; align-items:center; gap:10px; margin-top:8px; }
    .tier-img-preview { width:44px; height:44px; object-fit:contain; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; }
    .tier-add { background:none; border:1px dashed #cbd5e1; color:#94a3b8; font-family:'Goldman',monospace; font-size:0.68rem; letter-spacing:0.06em; padding:8px 16px; border-radius:6px; cursor:pointer; width:100%; }
    .tier-add:hover { border-color:#818cf8; color:#4338ca; }
    .hint { font-size: 0.66rem; color: #94a3b8; margin: -2px 0 10px; line-height: 1.5; }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    <form method="POST"
        action="{{ $achievement ? route('admin.achievements.update', $achievement) : route('admin.achievements.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if($achievement) @method('PUT') @endif

        <div class="card-dark">
            <div class="card-label">{{ $achievement ? 'მიღწევის რედაქტირება' : 'ახალი მიღწევა' }}</div>

            <div class="lbl">სახელი</div>
            <input type="text" name="name" id="achName" class="fc" value="{{ old('name', $achievement?->name) }}" required>
            @error('name')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">Slug (უნიკალური იდენტიფიკატორი)</div>
            <input type="text" name="slug" id="achSlug" class="fc" value="{{ old('slug', $achievement?->slug) }}" required>
            @error('slug')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">თემატიკა <span style="color:#cbd5e1;">(თუ მონიშნავ, მედალი ჩანს/მოქმედებს მხოლოდ ამ თემატიკის ბავშვებისთვის)</span></div>
            <select name="theme_id" class="fc">
                <option value="">ნებისმიერი თემატიკა</option>
                @foreach($themes as $theme)
                <option value="{{ $theme->id }}" {{ old('theme_id', $achievement?->theme_id) == $theme->id ? 'selected' : '' }}>{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
                @endforeach
            </select>
            @error('theme_id')<div class="err">{{ $message }}</div>@enderror

            <div class="lbl">აღწერა <span style="color:#cbd5e1;">({n} შეიცვლება დონის ზღვრით)</span></div>
            <input type="text" name="description" class="fc" value="{{ old('description', $achievement?->description) }}" placeholder="მაგ: დაწერე {n} ტესტი">

            <div class="lbl">პირობის ტიპი</div>
            <select name="condition_type" id="achConditionType" class="fc" onchange="achOnConditionChange()">
                @foreach($conditionTypes as $key => $label)
                <option value="{{ $key }}" {{ old('condition_type', $achievement?->condition_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('condition_type')<div class="err">{{ $message }}</div>@enderror

            <div id="timeOfDayFields" style="display:none;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <div class="lbl">საათამდე (მაგ: 09:00)</div>
                        <input type="time" name="condition_before" class="fc" value="{{ old('condition_before', $achievement?->condition_config['before'] ?? '') }}">
                    </div>
                    <div>
                        <div class="lbl">საათის შემდეგ (მაგ: 22:30)</div>
                        <input type="time" name="condition_after" class="fc" value="{{ old('condition_after', $achievement?->condition_config['after'] ?? '') }}">
                    </div>
                </div>
                <div class="hint">შეავსე მხოლოდ ერთ-ერთი — ან „საათამდე“ ან „საათის შემდეგ“.</div>
            </div>

            <label class="check-row">
                <input type="checkbox" name="daily_limit" value="1" {{ old('daily_limit', $achievement?->daily_limit) ? 'checked' : '' }}>
                დღეში მხოლოდ 1 ახალი მიღწევის გახსნა შესაძლებელია (იზიარებს ლიმიტს სხვა ასეთ მიღწევებთან)
            </label>
            <label class="check-row">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $achievement?->is_active ?? true) ? 'checked' : '' }}>
                აქტიური
            </label>
        </div>

        <div class="card-dark">
            <div class="card-label">დონეები</div>
            <div class="hint">ერთი დონე = უბრალო მიღწევა. რამდენიმე დონე = პროგრესირებადი (მაგ. ბრინჯაო/ვერცხლი/ოქრო) — ბავშვი თანმიმდევრობით გადადის დონეებზე.</div>

            <div id="tiersWrap">
                @php $existingTiers = old('tiers', $achievement?->tiers->map(fn($t) => ['label' => $t->label, 'threshold' => $t->threshold])->toArray() ?? [['label' => '', 'threshold' => '']]); @endphp
                @foreach($existingTiers as $i => $tier)
                <div class="tier-row" data-tier-index="{{ $i }}">
                    @if($i > 0)<button type="button" class="tier-remove" onclick="achRemoveTier(this)">✕</button>@endif
                    <div class="tier-num">დონე {{ $i + 1 }}</div>
                    <div class="tier-grid">
                        <input type="text" name="tiers[{{ $i }}][label]" class="fc" placeholder="სახელი, მაგ: ბრინჯაო" value="{{ $tier['label'] ?? '' }}" required>
                        <input type="number" name="tiers[{{ $i }}][threshold]" class="fc tier-threshold" placeholder="ზღვარი" min="1" value="{{ $tier['threshold'] ?? '' }}">
                    </div>
                    <div class="tier-img-row">
                        @if($achievement && ($achievement->tiers[$i]->image_path ?? null))
                        <img src="{{ $achievement->tiers[$i]->imageUrl() }}" class="tier-img-preview">
                        @endif
                        <input type="file" name="tiers[{{ $i }}][image]" accept="image/*" class="fc" style="margin-bottom:0;">
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="tier-add" onclick="achAddTier()">+ დონის დამატება</button>
        </div>

        <button type="submit" class="btn btn-save">შენახვა</button>
    </form>
</div>
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

function achOnConditionChange() {
    const type = document.getElementById('achConditionType').value;
    document.getElementById('timeOfDayFields').style.display = type === 'time_of_day' ? 'block' : 'none';
    const isBinary = BINARY_TYPES.includes(type);
    document.querySelectorAll('.tier-threshold').forEach(inp => {
        inp.style.display = isBinary ? 'none' : '';
        inp.disabled = isBinary;
    });
}

function achAddTier() {
    const wrap = document.getElementById('tiersWrap');
    const i = tierIndex++;
    const div = document.createElement('div');
    div.className = 'tier-row';
    div.innerHTML = `
        <button type="button" class="tier-remove" onclick="achRemoveTier(this)">✕</button>
        <div class="tier-num">დონე ${i + 1}</div>
        <div class="tier-grid">
            <input type="text" name="tiers[${i}][label]" class="fc" placeholder="სახელი, მაგ: ვერცხლი" required>
            <input type="number" name="tiers[${i}][threshold]" class="fc tier-threshold" placeholder="ზღვარი" min="1">
        </div>
        <div class="tier-img-row">
            <input type="file" name="tiers[${i}][image]" accept="image/*" class="fc" style="margin-bottom:0;">
        </div>
    `;
    wrap.appendChild(div);
    achOnConditionChange();
}

function achRemoveTier(btn) {
    btn.closest('.tier-row').remove();
    document.querySelectorAll('#tiersWrap .tier-row').forEach((row, idx) => {
        row.querySelector('.tier-num').textContent = 'დონე ' + (idx + 1);
    });
}

achOnConditionChange();
</script>
@endsection
