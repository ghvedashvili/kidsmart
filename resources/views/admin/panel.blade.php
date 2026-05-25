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
                        <span class="badge bg-primary rounded-pill">{{ $user->level }}</span>
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
                    <span class="badge bg-primary rounded-pill">Lvl {{ $user->level }}</span>
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

</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

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
@endsection
