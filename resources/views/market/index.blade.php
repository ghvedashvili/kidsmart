@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 800px; margin: 0 auto; padding: 28px 16px 80px; font-family: 'Goldman', monospace; }
    .card  { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:22px; margin-bottom:18px; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
    .sec   { font-size:0.62rem; color:#94a3b8; letter-spacing:0.14em; text-transform:uppercase; margin-bottom:14px; }
    .fc    { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; color:#374151; font-family:'Goldman',monospace; font-size:0.8rem; padding:8px 12px; outline:none; box-sizing:border-box; }
    .fc:focus { border-color:#94a3b8; }
    .btn   { background:#f8fafc; border:1px solid #e2e8f0; color:#64748b; font-family:'Goldman',monospace; font-size:0.76rem; letter-spacing:0.06em; padding:8px 18px; border-radius:6px; cursor:pointer; transition:all 0.2s; white-space:nowrap; }
    .btn:hover { border-color:#94a3b8; color:#1e293b; }
    .btn-sm { padding:4px 12px; font-size:0.68rem; }
    .btn-ok { background:#ecfdf5; border-color:#6ee7b7; color:#065f46; }
    .btn-ok:hover { background:#d1fae5; }
    .btn-del { background:none; border:none; color:#cbd5e1; font-size:0.72rem; cursor:pointer; padding:0 4px; transition:color 0.2s; }
    .btn-del:hover { color:#ef4444; }
    .msg   { font-size:0.72rem; color:#059669; margin-bottom:12px; }
    .merr  { font-size:0.72rem; color:#ef4444; margin-bottom:12px; }
    .item-row { display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px solid #f1f5f9; }
    .item-row:last-child { border-bottom:none; }
    .item-icon { font-size:1.4rem; width:36px; text-align:center; flex-shrink:0; }
    .item-title { flex:1; font-size:0.8rem; color:#374151; }
    .item-cat   { font-size:0.6rem; color:#aaa; margin-top:1px; }
    .cost-badge { font-size:0.7rem; background:#fef9c3; border:1px solid #fde68a; color:#92400e; border-radius:20px; padding:2px 10px; white-space:nowrap; }
    .toggle-pill { font-size:0.6rem; border-radius:20px; padding:3px 9px; border:none; cursor:pointer; font-family:'Goldman',monospace; }
    .toggle-pill.on  { background:#dcfce7; color:#15803d; }
    .toggle-pill.off { background:#f1f5f9; color:#94a3b8; }

    /* template grid */
    .tpl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:8px; margin-top:10px; }
    .tpl-card { border:1px dashed #e2e8f0; border-radius:8px; padding:8px 10px; cursor:pointer; transition:all 0.15s; display:flex; align-items:center; gap:8px; }
    .tpl-card:hover { border-color:#94a3b8; background:#f8fafc; }
    .tpl-card.added { border-color:#6ee7b7; background:#ecfdf5; opacity:0.6; pointer-events:none; }
    .tpl-icon { font-size:1.2rem; }
    .tpl-info { flex:1; min-width:0; }
    .tpl-name { font-size:0.7rem; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .tpl-cost { font-size:0.6rem; color:#f59e0b; font-weight:700; }
    .cat-lbl { font-size:0.6rem; color:#94a3b8; letter-spacing:0.1em; text-transform:uppercase; margin:14px 0 6px; }

    /* pending cards */
    .pend-card { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px 14px; margin-bottom:10px; display:flex; align-items:center; gap:12px; }
    .pend-icon { font-size:1.6rem; flex-shrink:0; }
    .pend-info { flex:1; }
    .pend-title { font-size:0.82rem; color:#1e293b; }
    .pend-meta  { font-size:0.62rem; color:#92400e; margin-top:2px; }
    .pend-actions { display:flex; gap:6px; flex-shrink:0; }

    /* approved list */
    .appr-row { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f1f5f9; font-size:0.76rem; color:#374151; }
    .appr-row:last-child { border-bottom:none; }
    .appr-date { font-size:0.6rem; color:#aaa; margin-left:auto; white-space:nowrap; }

    @media(max-width:640px) { .aw { padding:12px 10px 60px; } .card { padding:14px; } .tpl-grid { grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); } }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:20px;">← back</a>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
        <span style="font-size:1.6rem;">🛒</span>
        <div>
            <div style="font-family:'Goldman',monospace;font-size:1rem;color:#1e293b;letter-spacing:0.04em;">{{ $child->name }}-ის მარკეტი</div>
            <div style="font-family:'Goldman',monospace;font-size:0.62rem;color:#94a3b8;letter-spacing:0.08em;">მონეტები: 💰 {{ $child->childSetting?->coins ?? 0 }}</div>
        </div>
    </div>

    @if(session('success'))<div class="msg">✓ {{ session('success') }}</div>@endif

    {{-- Pending requests --}}
    @if($pending->count())
    <div class="card" style="border-color:#fde68a;">
        <div class="sec" style="color:#92400e;">⏳ ელოდება პასუხს · {{ $pending->count() }}</div>
        @foreach($pending as $p)
        <div class="pend-card">
            <div class="pend-icon">{{ $p->item->icon }}</div>
            <div class="pend-info">
                <div class="pend-title">{{ $p->item->title }}</div>
                <div class="pend-meta">💰 {{ $p->coins_spent }} მონეტა · {{ $p->created_at->diffForHumans() }}</div>
            </div>
            <div class="pend-actions">
                <form method="POST" action="{{ route('market.approve', $p) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-ok">✓ დადასტურება</button>
                </form>
                <form method="POST" action="{{ route('market.cancel', $p) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm" onclick="return confirm('გაუქმდეს? მონეტები დაბრუნდება.')">✕ გაუქმება</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Current market items --}}
    <div class="card">
        <div class="sec">მარკეტის პროდუქტები · {{ $items->count() }}</div>
        @forelse($items as $item)
        <div class="item-row">
            <div class="item-icon" style="{{ !$item->is_active ? 'opacity:0.35;' : '' }}">{{ $item->icon }}</div>
            <div style="flex:1;">
                <div class="item-title" style="{{ !$item->is_active ? 'color:#aaa;' : '' }}">{{ $item->title }}</div>
                @if($item->category)<div class="item-cat">{{ $item->category }}</div>@endif
            </div>
            <span class="cost-badge">💰 {{ $item->coin_cost }}</span>
            <form method="POST" action="{{ route('market.item.toggle', $item) }}">
                @csrf @method('PATCH')
                <button type="submit" class="toggle-pill {{ $item->is_active ? 'on' : 'off' }}">
                    {{ $item->is_active ? '● ჩართ.' : '○ გამრ.' }}
                </button>
            </form>
            <form method="POST" action="{{ route('market.item.destroy', $item) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del" onclick="return confirm('წაიშალოს?')">✕</button>
            </form>
        </div>
        @empty
        <div style="color:#aaa;font-size:0.76rem;text-align:center;padding:14px 0;">მარკეტი ცარიელია — დაამატე შაბლონიდან ან ხელით</div>
        @endforelse
    </div>

    {{-- Add custom item --}}
    <div class="card">
        <div class="sec">ხელით დამატება</div>
        <form method="POST" action="{{ route('market.store', $child) }}">
            @csrf
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                <input type="text" name="icon" class="fc" placeholder="🎁" maxlength="5" required style="width:60px;text-align:center;font-size:1.2rem;">
                <input type="text" name="title" class="fc" placeholder="სახელი" required style="flex:1;min-width:140px;">
                <input type="text" name="category" class="fc" placeholder="კატეგორია (სურ.)" style="width:140px;">
                <input type="number" name="coin_cost" class="fc" placeholder="💰" min="1" max="9999" required style="width:80px;">
                <button type="submit" class="btn">+ დამატება</button>
            </div>
        </form>
    </div>

    {{-- Templates --}}
    <div class="card">
        <div class="sec">შაბლონებიდან სწრაფი დამატება</div>
        @php $existingTitles = $items->pluck('title')->flip()->toArray(); @endphp
        @foreach($templates as $catName => $tplItems)
        <div class="cat-lbl">{{ $catName }}</div>
        <div class="tpl-grid">
            @foreach($tplItems as $tpl)
            @php $alreadyAdded = isset($existingTitles[$tpl['title']]); @endphp
            @if(!$alreadyAdded)
            <form method="POST" action="{{ route('market.store', $child) }}" style="display:contents;">
                @csrf
                <input type="hidden" name="title"     value="{{ $tpl['title'] }}">
                <input type="hidden" name="icon"      value="{{ $tpl['icon'] }}">
                <input type="hidden" name="category"  value="{{ $catName }}">
                <input type="hidden" name="coin_cost" value="{{ $tpl['cost'] }}">
                <button type="submit" class="tpl-card">
                    <span class="tpl-icon">{{ $tpl['icon'] }}</span>
                    <span class="tpl-info">
                        <span class="tpl-name">{{ $tpl['title'] }}</span>
                        <span class="tpl-cost" style="display:block;">💰 {{ $tpl['cost'] }}</span>
                    </span>
                </button>
            </form>
            @else
            <div class="tpl-card added">
                <span class="tpl-icon">{{ $tpl['icon'] }}</span>
                <span class="tpl-info">
                    <span class="tpl-name">{{ $tpl['title'] }}</span>
                    <span class="tpl-cost" style="display:block;">✓ დამატებულია</span>
                </span>
            </div>
            @endif
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Approved history --}}
    @if($approved->count())
    <div class="card">
        <div class="sec">✓ დადასტურებული · ბოლო {{ $approved->count() }}</div>
        @foreach($approved as $ap)
        <div class="appr-row">
            <span>{{ $ap->item->icon }}</span>
            <span>{{ $ap->item->title }}</span>
            <span style="font-size:0.62rem;color:#f59e0b;">💰 {{ $ap->coins_spent }}</span>
            <span class="appr-date">{{ $ap->updated_at->format('d.m.Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
