@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 860px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #64748b; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #1e293b; border-color: #94a3b8; }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 22px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .msg-ok  { font-size: 0.75rem; color: #059669; margin-bottom: 14px; }
    .msg-err { font-size: 0.75rem; color: #dc2626; margin-bottom: 14px; }
    .err-list { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 0.72rem; color: #dc2626; }
    .err-list li { margin-bottom: 2px; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.78rem; padding: 7px 11px; outline: none; box-sizing: border-box; width: 100%; }
    .fc:focus { border-color: #94a3b8; }
    .lbl { font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 4px; margin-top: 10px; display: block; }
    .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
    .btn-save { background: #1e293b; border: none; color: #fff; font-family: 'Goldman', monospace; font-size: 0.75rem; padding: 8px 22px; border-radius: 4px; cursor: pointer; margin-top: 14px; }
    .btn-save:hover { background: #334155; }
    .btn-del { background: none; border: none; color: #cbd5e1; font-size: 0.72rem; cursor: pointer; padding: 2px 6px; }
    .btn-del:hover { color: #ef4444; }
    .pkg-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 16px 14px; margin-bottom: 10px; position: relative; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    .pkg-name { font-size: 0.88rem; font-weight: 600; color: #1e293b; margin-bottom: 3px; }
    .pkg-slug { font-size: 0.62rem; color: #94a3b8; margin-bottom: 9px; }
    .pkg-meta { display: flex; gap: 14px; flex-wrap: wrap; font-size: 0.7rem; color: #64748b; }
    .badge-free { background: #dcfce7; color: #166534; font-size: 0.6rem; padding: 1px 7px; border-radius: 8px; }
    .badge-paid { background: #dbeafe; color: #1e40af; font-size: 0.6rem; padding: 1px 7px; border-radius: 8px; }
    .pkg-actions { position: absolute; top: 12px; right: 12px; display: flex; gap: 6px; align-items: center; }
    .toggle-edit { background: none; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.65rem; padding: 3px 10px; border-radius: 3px; cursor: pointer; }
    .toggle-edit:hover { border-color: #94a3b8; color: #1e293b; }
    .edit-form { display: none; margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 14px; }
    .edit-form.open { display: block; }
    .check-row { display: flex; gap: 16px; margin-top: 10px; }
    .check-row label { display: flex; align-items: center; gap: 6px; font-size: 0.72rem; color: #374151; cursor: pointer; }
    input[type=checkbox] { accent-color: #374151; width: 14px; height: 14px; }
    .sec-title { font-size: 0.65rem; color: #94a3b8; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 16px; }
    input[type=number].fc { -moz-appearance: textfield; }
</style>

<div class="aw">
    <div class="atitle">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
        <a href="{{ route('admin.users.index') }}">მომხმარებლები</a>
        <a href="{{ route('admin.permissions.index') }}">ნებართვები</a>
        <a href="{{ route('admin.packages.index') }}" class="active">პაკეტები</a>
    </nav>

    @if(session('success'))<div class="msg-ok">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="msg-err">{{ session('error') }}</div>@endif

    @if($errors->any())
    <ul class="err-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
    @endif

    {{-- არსებული პაკეტები --}}
    @forelse($packages as $pkg)
    <div class="pkg-card">
        <div class="pkg-actions">
            <button type="button" class="toggle-edit" onclick="toggleEdit({{ $pkg->id }})">✎ რედ.</button>
            <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
        </div>

        <div class="pkg-name">
            {{ $pkg->name }}
            @if($pkg->is_free)<span class="badge-free" style="margin-left:6px;">FREE</span>
            @else<span class="badge-paid" style="margin-left:6px;">PAID</span>@endif
            @if(!$pkg->is_active)<span style="font-size:0.6rem;color:#94a3b8;margin-left:4px;">(გათიშული)</span>@endif
        </div>
        <div class="pkg-slug">slug: {{ $pkg->slug }} · id: {{ $pkg->id }}</div>
        <div class="pkg-meta">
            <span>💰 {{ $pkg->price_monthly > 0 ? number_format($pkg->price_monthly, 2).'₾/თვე' : 'უფასო' }}</span>
            @if($pkg->price_yearly > 0)<span>📅 {{ number_format($pkg->price_yearly, 2) }}₾/წელი</span>@endif
            <span>👶 {{ $pkg->maxChildrenLabel() }} ბავშვი</span>
            <span>⭐ სირთ. ≤ {{ $pkg->max_difficulty }}</span>
            @if($pkg->description)<span style="color:#94a3b8;">{{ $pkg->description }}</span>@endif
        </div>

        {{-- Edit form --}}
        <div class="edit-form" id="edit-{{ $pkg->id }}">
            <form method="POST" action="{{ route('admin.packages.update', $pkg) }}">
                @csrf @method('PUT')
                <div class="row2">
                    <div>
                        <label class="lbl">სახელი</label>
                        <input name="name" class="fc" value="{{ old('name', $pkg->name) }}" required>
                    </div>
                    <div>
                        <label class="lbl">Slug (a-z, 0-9, - _)</label>
                        <input name="slug" class="fc" value="{{ old('slug', $pkg->slug) }}" required>
                    </div>
                </div>
                <div class="row3">
                    <div>
                        <label class="lbl">ფასი / თვე (₾)</label>
                        <input type="number" step="0.01" min="0" name="price_monthly" class="fc" value="{{ old('price_monthly', $pkg->price_monthly) }}">
                    </div>
                    <div>
                        <label class="lbl">ფასი / წელი (₾)</label>
                        <input type="number" step="0.01" min="0" name="price_yearly" class="fc" value="{{ old('price_yearly', $pkg->price_yearly) }}">
                    </div>
                    <div>
                        <label class="lbl">რიგი</label>
                        <input type="number" min="0" name="sort_order" class="fc" value="{{ old('sort_order', $pkg->sort_order) }}">
                    </div>
                </div>
                <div class="row2">
                    <div>
                        <label class="lbl">მაქს. ბავშვი (0 = შეუზღ.)</label>
                        <input type="number" min="0" name="max_children" class="fc" value="{{ old('max_children', $pkg->max_children) }}">
                    </div>
                    <div>
                        <label class="lbl">მაქს. სირთულე (1–5)</label>
                        <input type="number" min="1" max="5" name="max_difficulty" class="fc" value="{{ old('max_difficulty', $pkg->max_difficulty) }}">
                    </div>
                </div>
                <div>
                    <label class="lbl">აღწერა</label>
                    <input name="description" class="fc" value="{{ old('description', $pkg->description) }}" placeholder="მოკლე აღწერა...">
                </div>
                <div class="check-row">
                    <label><input type="checkbox" name="is_free" value="1" {{ $pkg->is_free ? 'checked' : '' }}>უფასო პაკეტი</label>
                    <label><input type="checkbox" name="is_active" value="1" {{ $pkg->is_active ? 'checked' : '' }}>აქტიური</label>
                </div>
                <button type="submit" class="btn-save">✓ შენახვა</button>
                <button type="button" onclick="toggleEdit({{ $pkg->id }})" style="background:none;border:none;color:#94a3b8;font-size:0.72rem;cursor:pointer;margin-left:10px;">გაუქმება</button>
            </form>
        </div>
    </div>
    @empty
    <div style="font-size:0.78rem;color:#94a3b8;margin-bottom:16px;">პაკეტი ჯერ არ არის დამატებული.</div>
    @endforelse

    {{-- ახალი პაკეტი --}}
    <div class="card">
        <div class="sec-title">+ ახალი პაკეტი</div>
        <form method="POST" action="{{ route('admin.packages.store') }}">
            @csrf
            <div class="row2">
                <div>
                    <label class="lbl">სახელი *</label>
                    <input name="name" class="fc" placeholder="Standard" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label class="lbl">Slug * (a-z, 0-9, - _)</label>
                    <input name="slug" class="fc" placeholder="standard" value="{{ old('slug') }}" required>
                </div>
            </div>
            <div class="row3">
                <div>
                    <label class="lbl">ფასი / თვე (₾)</label>
                    <input type="number" step="0.01" min="0" name="price_monthly" class="fc" value="{{ old('price_monthly', '0') }}">
                </div>
                <div>
                    <label class="lbl">ფასი / წელი (₾)</label>
                    <input type="number" step="0.01" min="0" name="price_yearly" class="fc" value="{{ old('price_yearly', '0') }}">
                </div>
                <div>
                    <label class="lbl">რიგი</label>
                    <input type="number" min="0" name="sort_order" class="fc" value="{{ old('sort_order', $packages->count()) }}">
                </div>
            </div>
            <div class="row2">
                <div>
                    <label class="lbl">მაქს. ბავშვი (0 = შეუზღ.)</label>
                    <input type="number" min="0" name="max_children" class="fc" value="{{ old('max_children', '1') }}">
                </div>
                <div>
                    <label class="lbl">მაქს. სირთულე (1–5)</label>
                    <input type="number" min="1" max="5" name="max_difficulty" class="fc" value="{{ old('max_difficulty', '5') }}">
                </div>
            </div>
            <div>
                <label class="lbl">აღწერა</label>
                <input name="description" class="fc" placeholder="მოკლე აღწერა..." value="{{ old('description') }}">
            </div>
            <div class="check-row">
                <label><input type="checkbox" name="is_free" value="1" {{ old('is_free') ? 'checked' : '' }}>უფასო პაკეტი</label>
                <label><input type="checkbox" name="is_active" value="1" checked {{ old('is_active', '1') ? 'checked' : '' }}>აქტიური</label>
            </div>
            <button type="submit" class="btn-save">+ დამატება</button>
        </form>
    </div>
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.classList.toggle('open');
}
</script>
@endsection
