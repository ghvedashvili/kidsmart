@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 1020px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; }
    .fc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; color: #374151; font-family: 'Goldman', monospace; font-size: 0.78rem; padding: 7px 11px; outline: none; box-sizing: border-box; }
    .fc:focus { border-color: #94a3b8; }
    .btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 7px 18px; border-radius: 4px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
    .btn:hover { border-color: #94a3b8; color: #1e293b; }
    .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; align-items: center; }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .u-table { width: 100%; border-collapse: collapse; }
    .u-table th { font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.1em; text-transform: uppercase; padding: 9px 12px; border-bottom: 1px solid #f1f5f9; text-align: left; background: #f8fafc; font-weight: 400; }
    .u-table td { padding: 9px 12px; border-bottom: 1px solid #f8fafc; font-size: 0.76rem; color: #374151; vertical-align: middle; }
    .u-table tr:last-child td { border-bottom: none; }
    .u-table tr:hover td { background: #fafafa; }
    .role-sel { font-family: 'Goldman', monospace; font-size: 0.7rem; border: 1px solid #e2e8f0; border-radius: 3px; padding: 3px 6px; background: #f8fafc; color: #374151; cursor: pointer; outline: none; max-width: 100px; }
    .role-sel:focus { border-color: #94a3b8; }
    .save-icon { color: #059669; font-size: 0.7rem; display: none; margin-left: 4px; }
    .pkg-badge { display: inline-block; font-size: 0.6rem; padding: 2px 7px; border-radius: 8px; font-weight: 500; white-space: nowrap; }
    .pkg-free    { background: #dcfce7; color: #166534; }
    .pkg-paid    { background: #dbeafe; color: #1e40af; }
    .pkg-expired { background: #fee2e2; color: #991b1b; }
    .sub-btn { background: none; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.62rem; padding: 2px 8px; border-radius: 3px; cursor: pointer; white-space: nowrap; text-decoration: none; display: inline-block; }
    .sub-btn:hover { border-color: #94a3b8; color: #1e293b; }
    .pagination-wrap { margin-top: 16px; display: flex; gap: 6px; flex-wrap: wrap; }
    .pagination-wrap a, .pagination-wrap span { font-family: 'Goldman', monospace; font-size: 0.68rem; color: #64748b; border: 1px solid #e2e8f0; border-radius: 3px; padding: 4px 10px; text-decoration: none; }
    .pagination-wrap a:hover { color: #1e293b; border-color: #94a3b8; }
    /* Modal */
    .modal-bg { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 200; flex-direction: column; align-items: center; justify-content: flex-start; overflow-y: auto; padding: 80px 16px 48px; }
    .modal-bg.open { display: flex; }
    .sub-box { background: #fff; border-radius: 10px; padding: 26px; width: 360px; max-width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.18); font-family: 'Goldman', monospace; flex-shrink: 0; }
    .sub-box-title { font-size: 0.8rem; font-weight: 600; color: #1e293b; margin-bottom: 18px; }
    .lbl { font-size: 0.6rem; color: #94a3b8; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 4px; margin-top: 10px; }
    .sub-box select.fc, .sub-box input.fc { width: 100%; }
    .sub-box-actions { display: flex; gap: 8px; margin-top: 18px; }
    .btn-primary { background: #1e293b; border: none; color: #fff; font-family: 'Goldman', monospace; font-size: 0.75rem; padding: 8px 20px; border-radius: 4px; cursor: pointer; }
    .btn-primary:hover { background: #334155; }
    .btn-cancel { background: none; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Goldman', monospace; font-size: 0.75rem; padding: 8px 14px; border-radius: 4px; cursor: pointer; }
    .history-list { margin-top: 14px; font-size: 0.68rem; max-height: 160px; overflow-y: auto; }
    .history-item { padding: 5px 0; border-bottom: 1px solid #f1f5f9; color: #374151; }
    .history-item:last-child { border-bottom: none; }
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .filter-banner { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 8px 12px; font-size: 0.74rem; color: #3730a3; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .filter-banner a { color: #3730a3; text-decoration: underline; }
    .cm-diff-row { display: flex; gap: 6px; margin: 2px 0 4px; }
    .cm-diff-btn { background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 6px 12px; border-radius: 4px; cursor: pointer; transition: all 0.15s; }
    .cm-diff-btn.sel { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }
    .cm-unlimited-row { margin-top: 12px; display: flex; align-items: center; gap: 8px; }
    .cm-unlimited-row label { font-size: 0.72rem; color: #374151; cursor: pointer; }
    .cm-err { font-size: 0.72rem; color: #ef4444; margin-top: 8px; display: none; }
    .cm-reset-wrap { margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 12px; }
    .btn-warn { background: #fffbeb; border: 1px solid #fde68a; color: #b45309; font-family: 'Goldman', monospace; font-size: 0.74rem; padding: 8px 14px; border-radius: 4px; cursor: pointer; width: 100%; }
    .btn-warn:hover { border-color: #d97706; }
    @media (max-width: 640px) {
        .aw { padding: 14px 10px 48px; }
        .filters { flex-direction: column; align-items: stretch; }
        .filters input, .filters select { width: 100%; }
        .filters > span { display: none; }
        .u-table { font-size: 0.68rem; min-width: 560px; }
        .u-table th, .u-table td { padding: 7px 8px; }
        .role-sel { font-size: 0.65rem; max-width: 80px; }
        .sub-box { width: calc(100vw - 32px); padding: 18px 14px; }
    }
</style>

<div class="aw">
    <a href="javascript:history.back()" style="font-family:'Goldman',monospace;font-size:0.72rem;color:#999;letter-spacing:0.06em;text-decoration:none;display:inline-block;margin-bottom:24px;">← back</a>

    @if(session('success'))<div class="msg">{{ session('success') }}</div>@endif

    @if($filterParentsOf)
    <div class="filter-banner">
        <span>👪 ვაჩვენებ <b>{{ $filterParentsOf->name }}</b>-ის მშობლებს</span>
        <a href="{{ route('admin.users.index') }}">გასუფთავება ×</a>
    </div>
    @elseif($filterChildrenOf)
    <div class="filter-banner">
        <span>🧒 ვაჩვენებ <b>{{ $filterChildrenOf->name }}</b>-ის შვილებს</span>
        <a href="{{ route('admin.users.index') }}">გასუფთავება ×</a>
    </div>
    @endif

    <form method="GET" class="filters">
        <input type="text" name="search" class="fc" placeholder="სახელი ან email" value="{{ $filters['search'] ?? '' }}" style="width:210px;">
        <select name="role" class="fc" onchange="this.form.submit()">
            <option value="">ყველა როლი</option>
            @foreach($roles as $r)
            <option value="{{ $r }}" {{ ($filters['role'] ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn">ძება</button>
        @if(array_filter($filters))
        <a href="{{ route('admin.users.index') }}" style="color:#555;font-size:0.72rem;text-decoration:none;padding:7px 0;">× გასუფთავება</a>
        @endif
        <span style="margin-left:auto;font-size:0.7rem;color:#94a3b8;">სულ: {{ $users->total() }}</span>
    </form>

    <div class="card-dark">
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
                <td>{{ $user->name }}</td>
                <td style="color:#64748b;font-size:0.7rem;">{{ $user->email }}</td>
                <td>
                    <select class="role-sel" data-id="{{ $user->id }}" data-orig="{{ $user->role }}">
                        @foreach($roles as $r)
                        <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ $r }}</option>
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
                <td style="font-size:0.68rem;color:#94a3b8;">
                    @if($sub?->expires_at)
                        {{ $sub->expires_at->format('d.m.Y') }}
                    @elseif($sub)
                        უვადო
                    @else
                        —
                    @endif
                </td>
                <td style="color:#94a3b8;font-size:0.68rem;">{{ $user->created_at->format('d.m.Y') }}</td>
                <td style="white-space:nowrap;">
                    <button type="button" class="sub-btn"
                        onclick="openSubModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                        📦 პაკეტი
                    </button>
                    @if($user->role === 'child')
                    <button type="button" class="sub-btn"
                        onclick="openChildModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->childSetting?->grade_id ?? 'null' }}, {{ $user->childSetting?->difficulty ?? 1 }}, {{ $user->childSetting?->tests_per_week ?? 3 }}, {{ $user->childSetting?->unlimited_tests ? 'true' : 'false' }}, {{ $user->themes->pluck('id') }})">
                        ⚙ მართვა
                    </button>
                    @if($user->parents_count)
                    <a href="{{ route('admin.users.index', ['parents_of' => $user->id]) }}" class="sub-btn">
                        👪 მშობლები ({{ $user->parents_count }})
                    </a>
                    @endif
                    @elseif($user->role === 'parent' && $user->children_count)
                    <a href="{{ route('admin.users.index', ['children_of' => $user->id]) }}" class="sub-btn">
                        🧒 შვილები ({{ $user->children_count }})
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#94a3b8;font-size:0.75rem;">მომხმარებელი არ მოიძებნა</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="pagination-wrap">
        {{ $users->withQueryString()->links('admin.pagination') }}
    </div>
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
        <div id="cmMaxHint" style="font-size:0.62rem;color:#94a3b8;margin:-2px 0 8px;display:none;"></div>

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
    el.innerHTML = '<span style="color:#94a3b8;font-size:0.65rem;">ისტორია...</span>';
    const res  = await fetch(`/admin/users/${userId}/subscriptions`);
    const data = await res.json();
    if (!data.length) { el.innerHTML = '<span style="color:#94a3b8;font-size:0.65rem;">ისტორია ცარიელია</span>'; return; }
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
</script>
@endsection
