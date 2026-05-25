@extends('layouts.app')

@section('bodyClass', 'dot-light')

@section('content')
<div class="container py-4" style="position:relative;z-index:1;">

    <h4 class="mb-4 fw-bold">
        <i class="bi bi-shield-lock-fill text-danger me-2"></i>Admin Panel
    </h4>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>მოთამაშე</th>
                        <th>Nickname</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">როლი</th>
                        <th class="text-center">რეგისტრაცია</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $i => $user)
                    <tr id="row-{{ $user->id }}">
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $user->email }}</div>
                        </td>
                        <td>
                            <code>{{ $user->nickname ?? '—' }}</code>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $user->level }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge role-badge-{{ $user->id }} {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="text-center text-muted" style="font-size:0.78rem;">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="text-end pe-3">
                            @if($user->id !== auth()->id())
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="changeRole({{ $user->id }}, '{{ $user->name }}', '{{ $user->role }}')">
                                <i class="bi bi-person-gear"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function changeRole(userId, userName, currentRole) {
    const newRole = currentRole === 'admin' ? 'gamer' : 'admin';
    const icon   = newRole === 'admin' ? '🛡️' : '🎮';

    Swal.fire({
        title: `როლის შეცვლა`,
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
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ role: newRole }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector(`.role-badge-${userId}`);
                badge.textContent = data.role;
                badge.className = `badge role-badge-${userId} ${data.role === 'admin' ? 'bg-danger' : 'bg-secondary'}`;

                Swal.fire({
                    icon: 'success',
                    title: 'შეცვლილია!',
                    text: `${userName} ახლა ${data.role}-ია`,
                    timer: 1800,
                    showConfirmButton: false,
                });
            }
        });
    });
}
</script>
@endsection
