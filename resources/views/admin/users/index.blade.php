@extends('layouts.app')
{{-- redesign-kidsmart: მომხმარებლები (დიზაინი A5) --}}
@section('content')
<style>
    /* redesign-kidsmart: მომხმარებლები (დიზაინი A5) — იგივე კლასები, რასაც გვერდის JS იყენებს */
    .u-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .u-table th { text-align: left; font-size: 12px; font-weight: 700; color: var(--ks-muted); padding: 10px 12px; white-space: nowrap; }
    .u-table td { padding: 10px 12px; border-top: 1px solid var(--ks-line-soft); vertical-align: middle; }
    .u-table tr:hover td { background: #F7F6F3; }
    .table-scroll { overflow-x: auto; }
    .role-sel { min-height: 34px; border-radius: 999px; border: 1px solid var(--ks-line); background: #fff; padding: 0 10px; font-family: var(--ks-font); font-size: 12px; font-weight: 800; cursor: pointer; }
    .role-sel:focus { border-color: var(--ks-orange); outline: none; }
    .save-icon { display: none; margin-left: 4px; color: var(--ks-green); font-weight: 800; }
    .pkg-badge { display: inline-flex; font-size: 12px; font-weight: 800; border-radius: 999px; padding: 4px 10px; }
    .pkg-paid { background: var(--ks-purple-soft); color: var(--ks-purple-dark); }
    .pkg-free { background: #F1EFEB; color: var(--ks-ink-2); }
    .pkg-expired { background: var(--ks-red-soft); color: var(--ks-red-dark); }
    .sub-btn { display: inline-flex; align-items: center; min-height: 32px; padding: 0 10px; border-radius: 10px; border: none; background: #F1EFEB; color: var(--ks-ink); font-family: var(--ks-font); font-size: 12px; font-weight: 800; cursor: pointer; text-decoration: none; white-space: nowrap; }
    .sub-btn:hover { background: #E9DDD0; color: var(--ks-ink); }
    .filter-banner { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--ks-blue-soft); color: var(--ks-blue-dark); border-radius: 14px; padding: 10px 14px; font-size: 14px; }
    .filter-banner a { font-weight: 800; color: var(--ks-blue-dark); }
    .pagination-wrap { display: flex; justify-content: center; }
    .pagination-wrap a, .pagination-wrap span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; min-height: 40px; padding: 0 10px; margin: 0 3px; border-radius: 12px; border: 1px solid var(--ks-line); background: #fff; color: var(--ks-ink); text-decoration: none; font-weight: 700; font-family: var(--ks-font); }
    .pagination-wrap a:hover { border-color: #D9D4CC; }
    /* მოდალები */
    .modal-bg { position: fixed; inset: 0; z-index: 2000; background: rgba(29, 26, 23, .45); display: none; align-items: center; justify-content: center; padding: 16px; }
    .modal-bg.open { display: flex; }
    .sub-box { width: 100%; max-width: 480px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 22px; padding: 20px; display: flex; flex-direction: column; gap: 6px; font-family: var(--ks-font); }
    .sub-box-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; }
    .lbl { font-size: 13px; font-weight: 700; color: var(--ks-ink-2); margin-top: 6px; }
    .fc { width: 100%; min-height: 44px; box-sizing: border-box; border-radius: 12px; border: 1px solid var(--ks-line); background: var(--ks-bg-admin); padding: 0 12px; font-family: var(--ks-font); font-size: 14px; color: var(--ks-ink); outline: none; }
    textarea.fc { padding: 10px 12px; }
    .fc:focus { border-color: var(--ks-orange); box-shadow: 0 0 0 3px rgba(226, 89, 15, .15); }
    .sub-box-actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 16px; border-radius: 12px; border: 1px solid var(--ks-line); background: #fff; color: var(--ks-ink); font-family: var(--ks-font); font-size: 14px; font-weight: 800; cursor: pointer; }
    .btn:hover { border-color: #D9D4CC; }
    .btn-primary { background: var(--ks-orange-strong) !important; border-color: var(--ks-orange-strong) !important; color: #fff !important; }
    .btn-primary:hover { background: #A63E07 !important; }
    .btn-cancel { background: #fff; }
    .btn-warn { background: #fff; border-color: #F3B4B4; color: var(--ks-red-dark); }
    .btn-warn:hover { background: var(--ks-red-soft); }
    .history-list { display: flex; flex-direction: column; gap: 4px; margin-top: 6px; }
    .history-item { font-size: 13px; padding: 8px 10px; border-radius: 10px; background: var(--ks-bg-admin); }
    .cm-diff-row { display: flex; gap: 6px; flex-wrap: wrap; }
    .cm-diff-btn { min-width: 44px; min-height: 40px; border-radius: 10px; border: 1px solid #FFE3CC; background: #fff; color: var(--ks-purple-dark); font-family: var(--ks-font); font-size: 14px; font-weight: 800; cursor: pointer; }
    .cm-diff-btn.sel { background: var(--ks-purple); border-color: var(--ks-purple); color: #fff; }
    .cm-unlimited-row { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    .cm-unlimited-row label { font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .cm-reset-wrap { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--ks-line-soft); }
    .cm-err { font-size: 12px; font-weight: 700; color: var(--ks-red-dark); margin-top: 4px; }
    .rel-chip-list { display: flex; flex-wrap: wrap; gap: 6px; margin: 4px 0; }
    .rel-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 700; background: var(--ks-purple-soft); color: var(--ks-purple-dark); border-radius: 999px; padding: 4px 6px 4px 12px; }
    .rel-chip button { width: 24px; height: 24px; border-radius: 50%; border: none; background: transparent; color: inherit; font-weight: 800; cursor: pointer; }
    .rel-chip button:hover { background: rgba(0,0,0,.08); }
    .rel-empty, .rel-search-empty { font-size: 13px; color: var(--ks-muted); }
    .rel-search-wrap { position: relative; }
    .rel-search-results { margin-top: 4px; border: 1px solid var(--ks-line); border-radius: 12px; overflow: hidden; }
    .rel-search-item { padding: 10px 12px; font-size: 14px; cursor: pointer; border-bottom: 1px solid var(--ks-line-soft); }
    .rel-search-item:last-child { border-bottom: none; }
    .rel-search-item:hover { background: #F7F6F3; }
    .msg { font-weight: 700; }
</style>

@php $__roleNames = ['parent' => 'მშობელი', 'child' => 'ბავშვი', 'teacher' => 'მასწავლებელი', 'staff' => 'სტაფი', 'admin' => 'ადმინი']; @endphp
<header class="ks-page-head">
    <div class="ks-page-title">
        <span class="ks-eyebrow">სულ: {{ $users->total() }} მომხმარებელი</span>
        <h1 class="ks-display ks-h1">მომხმარებლები</h1>
    </div>
    <form method="GET" style="display:flex;gap:8px;flex:0 1 420px;">
        @if(!empty($filters['role']))<input type="hidden" name="role" value="{{ $filters['role'] }}">@endif
        <label for="uSearch" style="flex-grow:1;min-height:46px;border-radius:14px;background:#fff;border:1px solid var(--ks-line);display:flex;align-items:center;gap:8px;padding:0 12px;color:var(--ks-muted);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="text" id="uSearch" name="search" placeholder="სახელი ან ელფოსტა" value="{{ $filters['search'] ?? '' }}" style="flex-grow:1;min-width:0;border:none;outline:none;background:transparent;font-family:var(--ks-font);font-size:14px;color:var(--ks-ink);">
        </label>
        <button type="submit" class="ks-btn ks-btn-dark">ძებნა</button>
    </form>
</header>

@if(session('success'))<div class="ks-note" style="background:var(--ks-green-soft);color:var(--ks-green-dark);font-weight:700;">{{ session('success') }}</div>@endif

@if($filterParentsOf)
<div class="filter-banner">
    <span>ნაჩვენებია <b>{{ $filterParentsOf->name }}</b>-ის მშობლები</span>
    <a href="{{ route('admin.users.index') }}">გასუფთავება ×</a>
</div>
@elseif($filterChildrenOf)
<div class="filter-banner">
    <span>ნაჩვენებია <b>{{ $filterChildrenOf->name }}</b>-ის შვილები</span>
    <a href="{{ route('admin.users.index') }}">გასუფთავება ×</a>
</div>
@endif

<nav aria-label="როლი" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
    @php $__curRole = $filters['role'] ?? ''; @endphp
    <a href="{{ route('admin.users.index', array_filter(['search' => $filters['search'] ?? null])) }}" class="ks-btn ks-btn-sm {{ $__curRole === '' ? 'ks-btn-dark' : 'ks-btn-ghost' }}">ყველა</a>
    @foreach($roles as $r)
    <a href="{{ route('admin.users.index', array_filter(['role' => $r, 'search' => $filters['search'] ?? null])) }}" class="ks-btn ks-btn-sm {{ $__curRole === $r ? 'ks-btn-dark' : 'ks-btn-ghost' }}">{{ $__roleNames[$r] ?? $r }}</a>
    @endforeach
    @if(array_filter($filters))
    <a href="{{ route('admin.users.index') }}" style="margin-left:6px;font-size:13px;font-weight:700;color:var(--ks-orange-dark);">× ფილტრის გასუფთავება</a>
    @endif
</nav>

<div class="ks-card ks-card-tight">
        <div class="table-scroll">
        <table class="u-table">
            <thead>
                <tr>
                    <th>სახელი</th>
                    <th>Email</th>
                    <th>როლი</th>
                    <th>პაკეტი</th>
                    <th>ვადა</th>
                    <th>რეგ.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
            @php
                $sub  = $user->activeSubscription();
                $pkg  = $sub?->package;
                $expired = !$sub && $user->subscriptions()->latest()->first()?->expires_at?->isPast();
            @endphp
            <tr>
                <td>
                    {{ $user->name }}
                    @if($user->role === 'child' && $user->child_code)
                    <div class="ks-mono" style="font-size:12px;color:var(--ks-muted);margin-top:2px;">კოდი: {{ $user->child_code }}</div>
                    @endif
                </td>
                <td style="color:var(--ks-muted);font-size:13px;">{{ $user->email }}</td>
                <td>
                    <select class="role-sel" data-id="{{ $user->id }}" data-orig="{{ $user->role }}">
                        @foreach($roles as $r)
                        <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ $__roleNames[$r] ?? $r }}</option>
                        @endforeach
                    </select>
                    <span class="save-icon" id="saved-{{ $user->id }}">✓</span>
                </td>
                <td>
                    @if($pkg)
                        <span class="pkg-badge pkg-paid">{{ $pkg->name }}</span>
                    @elseif($expired)
                        <span class="pkg-badge pkg-expired">ვადაგასული</span>
                    @else
                        <span class="pkg-badge pkg-free">Free</span>
                    @endif
                </td>
                <td style="font-size:0.68rem;color:#8A8078;">
                    @if($sub?->expires_at)
                        {{ $sub->expires_at->format('d.m.Y') }}
                    @elseif($sub)
                        უვადო
                    @else
                        —
                    @endif
                </td>
                <td style="color:#8A8078;font-size:0.68rem;">{{ $user->created_at->format('d.m.Y') }}</td>
                <td>
                    <div style="display:grid;grid-template-columns:repeat(2, auto);gap:4px;width:max-content;">
                    <button type="button" class="sub-btn"
                        onclick="openSubModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                        პაკეტი
                    </button>
                    @if($user->role === 'child')
                    <button type="button" class="sub-btn"
                        onclick="openChildModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->childSetting?->grade_id ?? 'null' }}, {{ $user->childSetting?->difficulty ?? 1 }}, {{ $user->childSetting?->tests_per_week ?? 3 }}, {{ $user->childSetting?->unlimited_tests ? 'true' : 'false' }}, {{ $user->themes->pluck('id') }})">
                        მართვა
                    </button>
                    <button type="button" class="sub-btn"
                        onclick="openRelModal({{ $user->id }}, '{{ addslashes($user->name) }}', 'parent', {{ $user->parents->map->only(['id', 'name']) }})">
                        მშობლების რედ.
                    </button>
                    @if($user->parents_count)
                    <a href="{{ route('admin.users.index', ['parents_of' => $user->id]) }}" class="sub-btn">
                        მშობლები ({{ $user->parents_count }})
                    </a>
                    @endif
                    @elseif($user->role === 'parent')
                    <button type="button" class="sub-btn"
                        onclick="openRelModal({{ $user->id }}, '{{ addslashes($user->name) }}', 'child', {{ $user->children->map->only(['id', 'name']) }})">
                        შვილების რედ.
                    </button>
                    @if($user->children_count)
                    <a href="{{ route('admin.users.index', ['children_of' => $user->id]) }}" class="sub-btn">
                        შვილები ({{ $user->children_count }})
                    </a>
                    @endif
                    @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#8A8078;font-size:0.75rem;">მომხმარებელი არ მოიძებნა</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

<div class="pagination-wrap">
    {{ $users->withQueryString()->links('admin.pagination') }}
</div>

{{-- Subscription modal --}}
<div class="modal-bg" id="subModal">
    <div class="sub-box">
        <div class="sub-box-title" id="modalTitle">პაკეტის მინიჭება</div>

        <div class="lbl">პაკეტი</div>
        <select class="fc" id="mPackage" onchange="onPackageChange()">
            @foreach($packages as $p)
            <option value="{{ $p->id }}" data-free="{{ $p->is_free ? '1' : '0' }}">{{ $p->name }} @if(!$p->is_free)({{ $p->price_monthly }}₾/თვე)@endif</option>
            @endforeach
        </select>

        <div id="mCycleRow">
            <div class="lbl">გამოწერის ტიპი</div>
            <select class="fc" id="mCycle" onchange="updateDates()">
                <option value="monthly">ყოველთვიური</option>
                <option value="yearly">ყოველწლიური</option>
                <option value="lifetime">უვადო</option>
            </select>
        </div>

        <div class="lbl">დაწყება</div>
        <input type="date" class="fc" id="mStart" value="{{ date('Y-m-d') }}" oninput="updateDates()">

        <div id="mExpireRow">
            <div class="lbl">დასრულება</div>
            <input type="date" class="fc" id="mExpire">
        </div>

        <div class="lbl">შენიშვნა (სურვილისამებრ)</div>
        <input type="text" class="fc" id="mNote" placeholder="გადახდის №, კომენტარი...">

        <div class="history-list" id="mHistory"></div>

        <div class="sub-box-actions">
            <button class="btn-primary" onclick="saveSubscription()">✓ შენახვა</button>
            <button class="btn-cancel" onclick="closeModal()">გაუქმება</button>
        </div>
    </div>
</div>

{{-- Child management modal (admin-only) --}}
<div class="modal-bg" id="childModal">
    <div class="sub-box">
        <div class="sub-box-title" id="childModalTitle">ბავშვის მართვა</div>

        <div class="lbl">კლასი</div>
        <select class="fc" id="cmGrade" onchange="onCmGradeChange()">
            <option value="">— კლასი არ არის —</option>
            @foreach($grades as $grade)
            <option value="{{ $grade->id }}" data-max-level="{{ $grade->max_level }}">{{ $grade->name }}</option>
            @endforeach
        </select>

        <div class="lbl">დონე</div>
        <div class="cm-diff-row" id="cmDiffRow">
            @for($i = 1; $i <= 5; $i++)
            <button type="button" class="cm-diff-btn" data-d="{{ $i }}" onclick="setCmDiff({{ $i }})">{{ $i }}</button>
            @endfor
        </div>
        <div id="cmMaxHint" style="font-size:0.62rem;color:#8A8078;margin:-2px 0 8px;display:none;"></div>

        <div class="lbl">თემატიკები</div>
        <select class="fc" id="cmThemes" multiple style="height:96px;">
            @foreach($themes as $theme)
            <option value="{{ $theme->id }}">{{ $theme->icon ?? '' }} {{ $theme->name }}</option>
            @endforeach
        </select>

        <div class="lbl">ტესტი დღეში (მაქს.)</div>
        <input type="number" class="fc" id="cmTestsPerWeek" min="1" max="7">

        <div class="cm-unlimited-row">
            <input type="checkbox" id="cmUnlimited" style="width:16px;height:16px;cursor:pointer;">
            <label for="cmUnlimited">♾ ულიმიტო ტესტი დღეში (ტესტირებისთვის)</label>
        </div>

        <div class="cm-err" id="cmErr"></div>

        <div class="sub-box-actions">
            <button class="btn-primary" onclick="saveChildSettings()">✓ შენახვა</button>
            <button class="btn-cancel" onclick="closeChildModal()">გაუქმება</button>
        </div>

        <div class="cm-reset-wrap">
            <button type="button" class="btn-warn" onclick="confirmResetProgress()">🔄 პროგრესის განულება</button>
        </div>
    </div>
</div>

{{-- Relationship management modal (shared: parents-of-a-child or children-of-a-parent) --}}
<div class="modal-bg" id="relModal">
    <div class="sub-box">
        <div class="sub-box-title" id="relModalTitle">მშობლების მართვა</div>

        <div class="lbl" id="relListLbl" style="margin-top:0;">მშობლები</div>
        <div id="relList" class="rel-chip-list"></div>

        <div class="rel-search-wrap">
            <input type="text" id="relSearchInput" class="fc" style="width:100%;" autocomplete="off"
                placeholder="🔍 დამატება სახელით/email/კოდით..." oninput="onRelSearchInput()"
                onfocus="onRelSearchInput()" onblur="setTimeout(hideRelResults, 150)">
            <div id="relResults" class="rel-search-results"></div>
        </div>

        <div class="sub-box-actions">
            <button class="btn-cancel" onclick="closeRelModal()">დახურვა</button>
        </div>
    </div>
</div>

<script>
let currentUserId = null;

function openSubModal(userId, userName) {
    currentUserId = userId;
    document.getElementById('modalTitle').textContent = 'პაკეტი — ' + userName;
    document.getElementById('mNote').value = '';
    onPackageChange();
    loadHistory(userId);
    document.getElementById('subModal').classList.add('open');
}

function closeModal() {
    document.getElementById('subModal').classList.remove('open');
    currentUserId = null;
}

function onPackageChange() {
    const sel  = document.getElementById('mPackage');
    const opt  = sel.options[sel.selectedIndex];
    if (!opt) return;
    const free = opt.dataset.free === '1';
    const cycleRow  = document.getElementById('mCycleRow');
    const cyclesSel = document.getElementById('mCycle');

    if (free) {
        cyclesSel.value       = 'lifetime';
        cycleRow.style.display = 'none';
    } else {
        cycleRow.style.display = '';
        if (cyclesSel.value === 'lifetime') cyclesSel.value = 'monthly';
    }
    updateDates();
}

function updateDates() {
    const cycle = document.getElementById('mCycle').value;
    const start = document.getElementById('mStart').value;
    const row   = document.getElementById('mExpireRow');
    const exp   = document.getElementById('mExpire');

    if (cycle === 'lifetime') { row.style.display = 'none'; return; }
    row.style.display = '';

    if (start) {
        const d = new Date(start);
        if (cycle === 'monthly') d.setMonth(d.getMonth() + 1);
        else d.setFullYear(d.getFullYear() + 1);
        exp.value = d.toISOString().slice(0, 10);
    }
}

async function saveSubscription() {
    const body = {
        package_id:    document.getElementById('mPackage').value,
        billing_cycle: document.getElementById('mCycle').value,
        starts_at:     document.getElementById('mStart').value,
        expires_at:    document.getElementById('mCycle').value === 'lifetime' ? null : document.getElementById('mExpire').value,
        note:          document.getElementById('mNote').value,
    };
    const res = await fetch(`/admin/users/${currentUserId}/subscribe`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body),
    });
    if (res.ok) { closeModal(); location.reload(); }
}

async function loadHistory(userId) {
    const el = document.getElementById('mHistory');
    el.innerHTML = '<span style="color:#8A8078;font-size:0.65rem;">ისტორია...</span>';
    const res  = await fetch(`/admin/users/${userId}/subscriptions`);
    const data = await res.json();
    if (!data.length) { el.innerHTML = '<span style="color:#8A8078;font-size:0.65rem;">ისტორია ცარიელია</span>'; return; }
    el.innerHTML = data.map(s => {
        const exp = s.expires_at ? s.expires_at : 'უვადო';
        const active = !s.expires_at || new Date(s.expires_at) > new Date();
        return `<div class="history-item">${active ? '✅' : '❌'} <b>${s.package?.name}</b> · ${s.billing_cycle} · ${s.starts_at} → ${exp}</div>`;
    }).join('');
}

document.getElementById('subModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.querySelectorAll('.role-sel').forEach(sel => {
    sel.addEventListener('change', async function () {
        const id   = this.dataset.id;
        const role = this.value;
        const icon = document.getElementById('saved-' + id);
        try {
            const res = await fetch(`/admin/users/${id}/role`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ role }),
            });
            if (res.ok) {
                icon.style.display = 'inline';
                this.dataset.orig = role;
                setTimeout(() => icon.style.display = 'none', 2000);
            }
        } catch (e) { this.value = this.dataset.orig; }
    });
});

// ── Child management modal (admin-only) ────────────────────────────────────
let currentChildId = null, cmDiff = 1;

function openChildModal(id, name, gradeId, difficulty, testsPerWeek, unlimited, themeIds) {
    currentChildId = id;
    document.getElementById('childModalTitle').textContent = 'ბავშვის მართვა — ' + name;
    document.getElementById('cmErr').style.display = 'none';

    const gradeSel = document.getElementById('cmGrade');
    gradeSel.value = gradeId ?? '';
    applyCmMaxLevel(gradeId);
    setCmDiff(difficulty || 1);

    document.getElementById('cmTestsPerWeek').value = testsPerWeek || 3;
    document.getElementById('cmUnlimited').checked = !!unlimited;

    const themeSel = document.getElementById('cmThemes');
    Array.from(themeSel.options).forEach(o => { o.selected = themeIds.includes(+o.value); });

    document.getElementById('childModal').classList.add('open');
}
function closeChildModal() {
    document.getElementById('childModal').classList.remove('open');
    currentChildId = null;
}
function onCmGradeChange() {
    applyCmMaxLevel(document.getElementById('cmGrade').value);
}
function applyCmMaxLevel(gradeId) {
    const opt = document.querySelector('#cmGrade option[value="' + gradeId + '"]');
    const max = (gradeId && opt) ? (+opt.dataset.maxLevel || 5) : 5;
    document.querySelectorAll('#cmDiffRow .cm-diff-btn').forEach(b => {
        b.style.display = (+b.dataset.d) <= max ? '' : 'none';
    });
    const hint = document.getElementById('cmMaxHint');
    if (gradeId && max < 5) {
        hint.textContent = 'ამ კლასს მაქსიმუმ ' + max + ' დონე აქვს';
        hint.style.display = 'block';
    } else {
        hint.style.display = 'none';
    }
    if (cmDiff > max) setCmDiff(max);
}
function setCmDiff(d) {
    cmDiff = d;
    document.querySelectorAll('#cmDiffRow .cm-diff-btn').forEach(b => {
        b.classList.toggle('sel', +b.dataset.d === d);
    });
}
async function saveChildSettings() {
    const errEl = document.getElementById('cmErr');
    errEl.style.display = 'none';
    const themeIds = Array.from(document.getElementById('cmThemes').selectedOptions).map(o => +o.value);
    try {
        const res = await fetch(`/admin/users/${currentChildId}/child-settings`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({
                grade_id:         document.getElementById('cmGrade').value || null,
                difficulty:       cmDiff,
                tests_per_week:   +document.getElementById('cmTestsPerWeek').value || 3,
                unlimited_tests:  document.getElementById('cmUnlimited').checked,
                theme_ids:        themeIds,
            }),
        });
        const data = await res.json();
        if (!res.ok) {
            errEl.textContent = data.error || 'შეცდომა — არ შეინახა';
            errEl.style.display = 'block';
            return;
        }
        location.reload();
    } catch (e) {
        errEl.textContent = 'დაკავშირების შეცდომა';
        errEl.style.display = 'block';
    }
}
function confirmResetProgress() {
    Swal.fire({
        title: 'პროგრესის განულება?',
        html: '<span style="font-size:0.9rem;color:#555;">დონე დაუბრუნდება 1-ზე, მონეტები და ტესტების მთვლელი განულდება.<br>ტესტების/ვარჯიშის ისტორია არ წაიშლება.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'დიახ, განულდეს',
        cancelButtonText: 'გაუქმება',
    }).then(result => {
        if (result.isConfirmed) resetProgress();
    });
}
async function resetProgress() {
    const res = await fetch(`/admin/users/${currentChildId}/reset-progress`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    });
    if (res.ok) location.reload();
}
document.getElementById('childModal').addEventListener('click', function(e) {
    if (e.target === this) closeChildModal();
});

// ── Relationship management modal (shared for both directions) ─────────────
// relDirection === 'parent' means: relUserId is a CHILD, and we're managing
// its PARENTS. relDirection === 'child' means: relUserId is a PARENT, and
// we're managing its CHILDREN.
let relUserId = null, relDirection = 'parent', relItems = [];

function openRelModal(userId, userName, direction, items) {
    relUserId = userId;
    relDirection = direction;
    relItems = items.slice();

    document.getElementById('relModalTitle').textContent =
        (direction === 'parent' ? 'მშობლების მართვა — ' : 'შვილების მართვა — ') + userName;
    document.getElementById('relListLbl').textContent = direction === 'parent' ? 'მშობლები' : 'შვილები';
    document.getElementById('relSearchInput').value = '';
    hideRelResults();
    renderRelList();
    document.getElementById('relModal').classList.add('open');
}
function closeRelModal() {
    document.getElementById('relModal').classList.remove('open');
    relUserId = null;
}
function renderRelList() {
    const box = document.getElementById('relList');
    box.innerHTML = '';
    if (!relItems.length) {
        const span = document.createElement('span');
        span.className = 'rel-empty';
        span.textContent = 'არცერთი არ არის';
        box.appendChild(span);
        return;
    }
    relItems.forEach(item => {
        const chip = document.createElement('span');
        chip.className = 'rel-chip';
        chip.append(document.createTextNode(item.name));
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.title = 'წაშლა';
        btn.textContent = '✕';
        btn.onclick = () => detachRel(item.id);
        chip.appendChild(btn);
        box.appendChild(chip);
    });
}
async function onRelSearchInput() {
    const q = document.getElementById('relSearchInput').value.trim();
    const box = document.getElementById('relResults');
    if (!q) { box.style.display = 'none'; box.innerHTML = ''; return; }

    const role = relDirection === 'parent' ? 'parent' : 'child';
    let data;
    try {
        const res = await fetch(`/admin/users/search?role=${role}&q=${encodeURIComponent(q)}`);
        data = await res.json();
    } catch (e) { return; }

    const existingIds = new Set(relItems.map(i => i.id));
    const matches = data.filter(u => !existingIds.has(u.id));

    box.innerHTML = '';
    if (!matches.length) {
        const div = document.createElement('div');
        div.className = 'rel-search-empty';
        div.textContent = 'არაფერი ვერ მოიძებნა';
        box.appendChild(div);
    } else {
        matches.forEach(u => {
            const div = document.createElement('div');
            div.className = 'rel-search-item';
            div.textContent = u.name + ' (' + (u.email || u.child_code || '') + ')';
            div.onclick = () => attachRel(u.id, u.name);
            box.appendChild(div);
        });
    }
    box.style.display = 'block';
}
function hideRelResults() {
    document.getElementById('relResults').style.display = 'none';
}
async function attachRel(targetId, targetName) {
    try {
        const res = await fetch(`/admin/users/${relUserId}/relations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ action: 'attach', direction: relDirection, target_id: targetId }),
        });
        if (!res.ok) { alert('შეცდომა — არ დაემატა'); return; }
        relItems.push({ id: targetId, name: targetName });
        renderRelList();
        document.getElementById('relSearchInput').value = '';
        hideRelResults();
    } catch (e) { alert('დაკავშირების შეცდომა'); }
}
async function detachRel(targetId) {
    try {
        const res = await fetch(`/admin/users/${relUserId}/relations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ action: 'detach', direction: relDirection, target_id: targetId }),
        });
        if (!res.ok) { alert('შეცდომა — არ წაიშალა'); return; }
        relItems = relItems.filter(i => i.id !== targetId);
        renderRelList();
    } catch (e) { alert('დაკავშირების შეცდომა'); }
}
document.getElementById('relModal').addEventListener('click', function(e) {
    if (e.target === this) closeRelModal();
});
</script>
@endsection
