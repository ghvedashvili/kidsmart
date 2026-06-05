@extends('layouts.app')

@section('bodyClass', 'dot-light')

@section('content')
<style>
    .admin-wrap {
        padding: 20px 16px 40px;
        max-width: 1000px;
        margin: 0 auto;
    }

    /* ── desktop table ── */
    .admin-table-wrap {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.08);
    }

    .admin-table { font-size: 0.875rem; }
    .admin-table thead th { font-size: 0.75rem; letter-spacing: .04em; text-transform: uppercase; }
    .admin-table code { font-size: 0.8rem; word-break: break-all; }
    .q-cell { max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* ── mobile cards ── */
    .user-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 5px rgba(0,0,0,.07);
        padding: 14px 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-card-avatar {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 700;
        color: #495057;
    }

    .user-card-body { flex: 1; min-width: 0; }

    .user-card-name {
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-card-email {
        font-size: 0.72rem;
        color: #adb5bd;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-card-nickname {
        font-size: 0.72rem;
        color: #6c757d;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-card-meta {
        display: flex;
        gap: 6px;
        margin-top: 5px;
        flex-wrap: wrap;
        align-items: center;
    }
</style>

<div class="admin-wrap" style="position:relative;z-index:1;">

    <h5 class="mb-3 fw-bold">
        <i class="bi bi-shield-lock-fill text-danger me-2"></i>Admin Panel
        <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">{{ $users->count() }} მოთამაშე</span>
    </h5>

    <ul class="nav nav-tabs mb-3" id="adminTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-users">მოთამაშეები</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-questions">კითხვები</button>
        </li>
    </ul>

    <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-users">

    {{-- DESKTOP: table (md+) --}}
    <div class="d-none d-md-block admin-table-wrap">
        <table class="table table-hover align-middle mb-0 admin-table">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3" style="width:40px;">#</th>
                    <th>მოთამაშე</th>
                    <th>Nickname</th>
                    <th class="text-center" style="width:80px;">Level</th>
                    <th class="text-center" style="width:90px;">როლი</th>
                    <th class="text-center" style="width:100px;">რეგისტრ.</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $i => $user)
                <tr id="row-{{ $user->id }}">
                    <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted" style="font-size:0.73rem;">{{ $user->email }}</div>
                    </td>
                    <td><code>{{ \Illuminate\Support\Str::limit($user->nickname ?? '—', 24) }}</code></td>
                    <td class="text-center">
                        <span class="badge bg-primary rounded-pill level-badge-{{ $user->id }}"
                              style="cursor:pointer;"
                              onclick="changeLevel({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->level }})">
                            {{ $user->level }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge role-badge-{{ $user->id }} {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="text-center text-muted" style="font-size:0.75rem;">
                        {{ $user->created_at->format('d.m.Y') }}
                    </td>
                    <td class="text-end pe-3">
                        @if($user->id !== auth()->id())
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="changeRole({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}')">
                            <i class="bi bi-person-gear"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- MOBILE: cards (< md) --}}
    <div class="d-md-none">
        @foreach($users as $i => $user)
        <div class="user-card" id="card-{{ $user->id }}">
            <div class="user-card-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="user-card-body">
                <div class="user-card-name">{{ $user->name }}</div>
                <div class="user-card-email">{{ $user->email }}</div>
                <div class="user-card-nickname">{{ $user->nickname ?? '—' }}</div>
                <div class="user-card-meta">
                    <span class="badge bg-primary rounded-pill level-badge-{{ $user->id }}"
                          style="cursor:pointer;"
                          onclick="changeLevel({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->level }})">
                        Lvl {{ $user->level }}
                    </span>
                    <span class="badge role-badge-{{ $user->id }} {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                        {{ $user->role }}
                    </span>
                    <span class="text-muted" style="font-size:0.68rem;">{{ $user->created_at->format('d.m.Y') }}</span>
                </div>
            </div>
            @if($user->id !== auth()->id())
            <button class="btn btn-sm btn-outline-secondary flex-shrink-0"
                    onclick="changeRole({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}')">
                <i class="bi bi-person-gear"></i>
            </button>
            @endif
        </div>
        @endforeach
    </div>

    </div>{{-- /tab-pane users --}}

    {{-- ── QUESTIONS TAB ── --}}
    <div class="tab-pane fade" id="tab-questions">
        <div class="mb-3">
            <button class="btn btn-sm btn-success" onclick="addQuestion()">
                <i class="bi bi-plus-lg me-1"></i>ახალი კითხვა
            </button>
        </div>
        <div class="d-none d-md-block admin-table-wrap">
            <table class="table table-hover align-middle mb-0 admin-table" style="font-size:0.8rem;">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width:44px;">Lvl</th>
                        <th style="width:80px;">Type</th>
                        <th style="width:18%;">კითხვა</th>
                        <th style="width:16%;">Rules</th>
                        <th style="width:15%;">Success Msg</th>
                        <th style="width:14%;">Answer</th>
                        <th style="width:14%;">Hints</th>
                        <th style="width:44px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $q)
                    @php
                        $answerPreview = is_array($q->answer) ? implode(', ', $q->answer) : ($q->answer ?? '—');
                        $hintsPreview  = is_array($q->hints)  ? implode(', ', $q->hints)  : ($q->hints  ?? '—');
                    @endphp
                    <tr>
                        <td class="ps-3"><span class="badge bg-primary">{{ $q->level }}</span></td>
                        <td><span class="badge bg-secondary">{{ $q->type }}</span></td>
                        <td class="q-cell">{{ \Illuminate\Support\Str::limit($q->question ?? '—', 60) }}</td>
                        <td class="q-cell">{{ \Illuminate\Support\Str::limit($q->rules ?? '—', 50) }}</td>
                        <td class="q-cell">{{ \Illuminate\Support\Str::limit($q->success_message ?? '—', 50) }}</td>
                        <td class="q-cell"><code>{{ \Illuminate\Support\Str::limit($answerPreview, 40) }}</code></td>
                        <td class="q-cell"><code>{{ \Illuminate\Support\Str::limit($hintsPreview, 40) }}</code></td>
                        <td class="pe-2">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick="editQuestion({{ $q->id }}, {{ $q->level }}, '{{ addslashes($q->type) }}', {{ json_encode($q->question) }}, {{ json_encode($q->rules) }}, {{ json_encode($q->success_message) }}, {{ json_encode($q->answer) }}, {{ json_encode($q->hints) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="d-md-none">
            @foreach($questions as $q)
            @php
                $answerPreview = is_array($q->answer) ? implode(', ', $q->answer) : ($q->answer ?? '—');
                $hintsPreview  = is_array($q->hints)  ? implode(', ', $q->hints)  : ($q->hints  ?? '—');
            @endphp
            <div class="user-card" style="flex-direction:column;align-items:flex-start;gap:6px;">
                <div style="display:flex;align-items:center;gap:10px;width:100%;">
                    <div class="user-card-avatar" style="background:#e3f0ff;color:#0d6efd;font-size:0.85rem;font-weight:700;">{{ $q->level }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="user-card-name">Level {{ $q->level }} — <span class="badge bg-secondary" style="font-size:0.65rem;">{{ $q->type }}</span></div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary flex-shrink-0"
                            onclick="editQuestion({{ $q->id }}, {{ $q->level }}, '{{ addslashes($q->type) }}', {{ json_encode($q->question) }}, {{ json_encode($q->rules) }}, {{ json_encode($q->success_message) }}, {{ json_encode($q->answer) }}, {{ json_encode($q->hints) }})">
                        <i class="bi bi-pencil"></i>
                    </button>
                </div>
                <table style="width:100%;font-size:0.72rem;border-collapse:collapse;">
                    <tr><td style="color:#6c757d;width:90px;padding:1px 0;">კითხვა</td><td>{{ \Illuminate\Support\Str::limit($q->question ?? '—', 70) }}</td></tr>
                    <tr><td style="color:#6c757d;padding:1px 0;">Rules</td><td>{{ \Illuminate\Support\Str::limit($q->rules ?? '—', 70) }}</td></tr>
                    <tr><td style="color:#6c757d;padding:1px 0;">Success Msg</td><td>{{ \Illuminate\Support\Str::limit($q->success_message ?? '—', 70) }}</td></tr>
                    <tr><td style="color:#6c757d;padding:1px 0;">Answer</td><td><code style="font-size:0.7rem;">{{ \Illuminate\Support\Str::limit($answerPreview, 60) }}</code></td></tr>
                    <tr><td style="color:#6c757d;padding:1px 0;">Hints</td><td><code style="font-size:0.7rem;">{{ \Illuminate\Support\Str::limit($hintsPreview, 60) }}</code></td></tr>
                </table>
            </div>
            @endforeach
        </div>
    </div>{{-- /tab-pane questions --}}

    </div>{{-- /tab-content --}}

</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function changeLevel(userId, userName, currentLevel) {
    Swal.fire({
        title: 'ლეველის შეცვლა',
        html: `<b>${userName}</b> — მიმდინარე ლეველი: <span class="badge bg-primary">${currentLevel}</span><br><br>
               <input id="swal-level-input" type="number" min="1" value="${currentLevel}"
                      class="swal2-input" style="max-width:120px;text-align:center;">`,
        showCancelButton: true,
        confirmButtonText: '✅ შენახვა',
        cancelButtonText: 'გაუქმება',
        confirmButtonColor: '#0d6efd',
        focusConfirm: false,
        preConfirm: () => {
            const val = parseInt(document.getElementById('swal-level-input').value);
            if (!val || val < 1) {
                Swal.showValidationMessage('ლეველი უნდა იყოს 1 ან მეტი');
                return false;
            }
            return val;
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const newLevel = result.value;

        fetch(`/admin/users/${userId}/level`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ level: newLevel }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            document.querySelectorAll(`.level-badge-${userId}`).forEach(badge => {
                badge.textContent = badge.textContent.includes('Lvl')
                    ? `Lvl ${data.level}`
                    : `${data.level}`;
            });
            Swal.fire({ icon: 'success', title: 'შეცვლილია!', text: `${userName} → ლეველი ${data.level}`, timer: 1600, showConfirmButton: false });
        });
    });
}

function addQuestion() {
    Swal.fire({
        title: 'ახალი კითხვა',
        width: Math.min(680, window.innerWidth - 32) + 'px',
        html: `
            <div style="text-align:left;font-size:0.85rem;">
                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <label class="form-label fw-semibold mb-1">Level</label>
                        <input id="eq-level" type="number" min="1" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-semibold mb-1">Type</label>
                        <select id="eq-type" class="form-select form-select-sm">
                            <option value="question">question</option>
                            <option value="action">action</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">კითხვა</label>
                    <textarea id="eq-question" class="form-control form-control-sm" rows="3"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Rules</label>
                    <textarea id="eq-rules" class="form-control form-control-sm" rows="3"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Success Message</label>
                    <textarea id="eq-success" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Answer <span class="text-muted fw-normal">(JSON ან მძიმით)</span></label>
                    <textarea id="eq-answer" class="form-control form-control-sm" rows="3" style="font-family:monospace;font-size:0.78rem;"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold mb-1">Hints <span class="text-muted fw-normal">(JSON ან მძიმით)</span></label>
                    <textarea id="eq-hints" class="form-control form-control-sm" rows="3" style="font-family:monospace;font-size:0.78rem;"></textarea>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: '✅ დამატება',
        cancelButtonText: 'გაუქმება',
        confirmButtonColor: '#198754',
        focusConfirm: false,
        preConfirm: () => {
            const lvl = parseInt(document.getElementById('eq-level').value);
            const typ = document.getElementById('eq-type').value.trim();
            if (!lvl || lvl < 1) { Swal.showValidationMessage('Level უნდა იყოს 1 ან მეტი'); return false; }
            if (!typ)            { Swal.showValidationMessage('Type ცარიელია');              return false; }
            function toJsonArray(raw) {
                raw = raw.trim();
                if (!raw) return '[]';
                if (raw.startsWith('[') || raw.startsWith('{')) { JSON.parse(raw); return raw; }
                return JSON.stringify(raw.split(',').map(s => s.trim()).filter(s => s));
            }
            let ansRaw, hintsRaw;
            try { ansRaw   = toJsonArray(document.getElementById('eq-answer').value); }
            catch(e) { Swal.showValidationMessage('Answer — JSON ფორმატი არასწორია'); return false; }
            try { hintsRaw = toJsonArray(document.getElementById('eq-hints').value); }
            catch(e) { Swal.showValidationMessage('Hints — JSON ფორმატი არასწორია');  return false; }
            return {
                level:           lvl,
                type:            typ,
                question:        document.getElementById('eq-question').value,
                rules:           document.getElementById('eq-rules').value,
                success_message: document.getElementById('eq-success').value,
                answer:          ansRaw,
                hints:           hintsRaw,
            };
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch('/admin/questions', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(result.value),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { Swal.fire({ icon: 'error', title: 'შეცდომა', text: 'ვერ დაემატა' }); return; }
            Swal.fire({ icon: 'success', title: 'დაემატა!', timer: 1400, showConfirmButton: false })
                .then(() => location.reload());
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'შეცდომა', text: 'სერვერთან კავშირი ვერ მოხერხდა' }));
    });
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function editQuestion(id, level, type, question, rules, success_message, answer, hints) {
    const answerStr = Array.isArray(answer) ? JSON.stringify(answer, null, 2) : (answer || '[]');
    const hintsStr  = Array.isArray(hints)  ? JSON.stringify(hints,  null, 2) : (hints  || '[]');

    Swal.fire({
        title: 'კითხვის რედაქტირება',
        width: Math.min(680, window.innerWidth - 32) + 'px',
        html: `
            <div style="text-align:left;font-size:0.85rem;">
                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <label class="form-label fw-semibold mb-1">Level</label>
                        <input id="eq-level" type="number" min="1" class="form-control form-control-sm" value="${escHtml(level)}">
                    </div>
                    <div class="col-8">
                        <label class="form-label fw-semibold mb-1">Type</label>
                        <select id="eq-type" class="form-select form-select-sm">
                            <option value="question" ${type === 'question' ? 'selected' : ''}>question</option>
                            <option value="action"   ${type === 'action'   ? 'selected' : ''}>action</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">კითხვა</label>
                    <textarea id="eq-question" class="form-control form-control-sm" rows="3">${escHtml(question)}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Rules</label>
                    <textarea id="eq-rules" class="form-control form-control-sm" rows="3">${escHtml(rules)}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Success Message</label>
                    <textarea id="eq-success" class="form-control form-control-sm" rows="2">${escHtml(success_message)}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold mb-1">Answer <span class="text-muted fw-normal">(JSON)</span></label>
                    <textarea id="eq-answer" class="form-control form-control-sm" rows="3" style="font-family:monospace;font-size:0.78rem;">${escHtml(answerStr)}</textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold mb-1">Hints <span class="text-muted fw-normal">(JSON)</span></label>
                    <textarea id="eq-hints" class="form-control form-control-sm" rows="3" style="font-family:monospace;font-size:0.78rem;">${escHtml(hintsStr)}</textarea>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: '✅ შენახვა',
        cancelButtonText: 'გაუქმება',
        confirmButtonColor: '#0d6efd',
        focusConfirm: false,
        preConfirm: () => {
            const lvl = parseInt(document.getElementById('eq-level').value);
            const typ = document.getElementById('eq-type').value.trim();
            if (!lvl || lvl < 1) { Swal.showValidationMessage('Level უნდა იყოს 1 ან მეტი'); return false; }
            if (!typ)            { Swal.showValidationMessage('Type ცარიელია');              return false; }
            function toJsonArray(raw) {
                raw = raw.trim();
                if (!raw) return '[]';
                if (raw.startsWith('[') || raw.startsWith('{')) {
                    JSON.parse(raw); // throws if invalid
                    return raw;
                }
                // comma-separated → JSON array
                return JSON.stringify(raw.split(',').map(s => s.trim()).filter(s => s));
            }
            let ansRaw, hintsRaw;
            try { ansRaw   = toJsonArray(document.getElementById('eq-answer').value); }
            catch(e) { Swal.showValidationMessage('Answer — JSON ფორმატი არასწორია'); return false; }
            try { hintsRaw = toJsonArray(document.getElementById('eq-hints').value); }
            catch(e) { Swal.showValidationMessage('Hints — JSON ფორმატი არასწორია');  return false; }
            return {
                level:           lvl,
                type:            typ,
                question:        document.getElementById('eq-question').value,
                rules:           document.getElementById('eq-rules').value,
                success_message: document.getElementById('eq-success').value,
                answer:          ansRaw,
                hints:           hintsRaw,
            };
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/admin/questions/${id}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(result.value),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { Swal.fire({ icon: 'error', title: 'შეცდომა', text: 'ვერ შეინახა' }); return; }
            Swal.fire({ icon: 'success', title: 'შენახულია!', timer: 1400, showConfirmButton: false })
                .then(() => location.reload());
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'შეცდომა', text: 'სერვერთან კავშირი ვერ მოხერხდა' }));
    });
}

function changeRole(userId, userName, currentRole) {
    const newRole = currentRole === 'admin' ? 'gamer' : 'admin';
    const icon    = newRole === 'admin' ? '🛡️' : '🎮';

    Swal.fire({
        title: 'როლის შეცვლა',
        html: `<b>${userName}</b>-ს მიანიჭო <span class="badge ${newRole === 'admin' ? 'bg-danger' : 'bg-secondary'}">${newRole}</span> როლი?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `${icon} დიახ, შევცვალო`,
        cancelButtonText: 'გავაუქმო',
        confirmButtonColor: newRole === 'admin' ? '#dc3545' : '#6c757d',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/admin/users/${userId}/role`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ role: newRole }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            document.querySelectorAll(`.role-badge-${userId}`).forEach(badge => {
                badge.textContent = data.role;
                badge.className = `badge role-badge-${userId} ${data.role === 'admin' ? 'bg-danger' : 'bg-secondary'}`;
            });

            Swal.fire({
                icon: 'success',
                title: 'შეცვლილია!',
                text: `${userName} ახლა ${data.role}-ია`,
                timer: 1600,
                showConfirmButton: false,
            });
        });
    });
}


</script>

<div class="admin-wrap" style="position:relative;z-index:1;margin-top:0;padding-top:0;">
    <h5 class="mb-3" style="font-family:'Goldman',monospace;font-size:0.85rem;letter-spacing:0.06em;color:#555;">Push Notification</h5>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="pushForm">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-6">
                        <input type="text" id="pushTitle" class="form-control form-control-sm" placeholder="სათაური" maxlength="100" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <input type="text" id="pushUrl" class="form-control form-control-sm" placeholder="URL (სურვილისამებრ)" value="/">
                    </div>
                </div>
                <div class="mb-2">
                    <textarea id="pushBody" class="form-control form-control-sm" rows="2" placeholder="ტექსტი" maxlength="300" required></textarea>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-sm btn-dark">გაგზავნა ყველასთვის</button>
                    <span id="pushResult" class="text-muted small"></span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('pushForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const result = document.getElementById('pushResult');
    result.textContent = 'გაგზავნა...';
    const res = await fetch('{{ route("push.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            title:   document.getElementById('pushTitle').value,
            body:    document.getElementById('pushBody').value,
            url:     document.getElementById('pushUrl').value,
        }),
    });
    const data = await res.json();
    const ok = data.results?.filter(r => r.success).length ?? 0;
    result.textContent = `✅ გაიგზავნა: ${ok} / ${data.results?.length ?? 0}`;
});
</script>

@endsection
