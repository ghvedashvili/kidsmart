@extends('layouts.app')

@section('content')
<style>
    body { background: #0d0d0d !important; }
    .admin-wrap {
        max-width: 720px;
        margin: 0 auto;
        padding: 32px 16px 64px;
        font-family: 'Goldman', monospace;
    }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 28px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #555; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #222; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #bbb; border-color: #444; }
    .admin-title {
        font-size: 0.75rem;
        color: #555;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin-bottom: 32px;
    }
    .card-dark {
        background: #111;
        border: 1px solid #222;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .card-label {
        font-size: 0.72rem;
        color: #555;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }
    .form-ctrl {
        background: #0d0d0d;
        border: 1px solid #2a2a2a;
        border-radius: 5px;
        color: #ccc;
        font-family: 'Goldman', monospace;
        font-size: 0.82rem;
        padding: 10px 14px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
        margin-bottom: 10px;
    }
    .form-ctrl:focus { border-color: #444; }
    .form-ctrl::placeholder { color: #444; }
    .btn-send {
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        color: #bbb;
        font-family: 'Goldman', monospace;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        padding: 10px 28px;
        border-radius: 5px;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s;
    }
    .btn-send:hover { border-color: #444; color: #fff; }
    .result-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 0.75rem;
        color: #666;
    }
    .result-row:last-child { border-bottom: none; }
    .ok  { color: #2ecc71; }
    .err { color: #e74c3c; }
    .user-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #1a1a1a;
        font-size: 0.78rem;
    }
    .user-row:last-child { border-bottom: none; }
    .sub-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .sub-on  { background: #2ecc71; }
    .sub-off { background: #333; }
    select.form-ctrl { cursor: pointer; }
</style>

<div class="admin-wrap">
    <div class="admin-title">Admin Panel</div>
    <nav class="anav">
        <a href="{{ route('admin.panel') }}" class="active">Push</a>
        <a href="{{ route('admin.grades.index') }}">კლასები</a>
        <a href="{{ route('admin.themes.index') }}">თემები</a>
        <a href="{{ route('admin.topics.index') }}">თოპიქები</a>
        <a href="{{ route('admin.questions.index') }}">კითხვები</a>
    </nav>

    {{-- Push Send Form --}}
    <div class="card-dark">
        <div class="card-label">Push Notification</div>
        <form id="pushForm">
            @csrf
            <input type="text" class="form-ctrl" id="pTitle" placeholder="სათაური" required>
            <textarea class="form-ctrl" id="pBody" rows="2" placeholder="ტექსტი" required></textarea>
            <input type="text" class="form-ctrl" id="pUrl" placeholder="URL (არასავალდებულო)">
            <div style="display:flex;align-items:center;gap:12px;margin-top:4px;">
                <select class="form-ctrl" id="pUser" style="width:auto;flex:1;margin-bottom:0;">
                    <option value="">ყველა მომხმარებელი</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-send">გაგზავნა →</button>
            </div>
        </form>
        <div id="pushResults" style="margin-top:16px;"></div>
    </div>

    {{-- Subscriptions --}}
    <div class="card-dark">
        <div class="card-label">Subscriptions · {{ $subscriptions->count() }}</div>
        @forelse($subscriptions as $sub)
        <div class="result-row">
            <span class="sub-dot sub-on"></span>
            <span style="color:#888;">{{ $sub->user?->name ?? '—' }}</span>
            <span style="color:#555;font-size:0.7rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($sub->endpoint, 60) }}</span>
        </div>
        @empty
        <div style="color:#444;font-size:0.78rem;">Subscription არ არის</div>
        @endforelse
    </div>

    {{-- Users --}}
    <div class="card-dark">
        <div class="card-label">Users · {{ $users->count() }}</div>
        @foreach($users as $u)
        @php $hasSub = $subscriptions->where('user_id', $u->id)->count() > 0; @endphp
        <div class="user-row">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="sub-dot {{ $hasSub ? 'sub-on' : 'sub-off' }}" title="{{ $hasSub ? 'subscribed' : 'not subscribed' }}"></span>
                <div>
                    <div style="color:#aaa;">{{ $u->name }}</div>
                    <div style="color:#555;font-size:0.7rem;">{{ $u->email }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:#555;font-size:0.7rem;" id="role-{{ $u->id }}">{{ $u->role }}</span>
                @if($u->id !== auth()->id())
                <button onclick="toggleRole({{ $u->id }}, '{{ $u->role }}')"
                    style="background:none;border:1px solid #2a2a2a;color:#666;font-family:'Goldman',monospace;font-size:0.65rem;padding:3px 8px;border-radius:3px;cursor:pointer;"
                    id="role-btn-{{ $u->id }}">
                    {{ $u->role === 'admin' ? '→ user' : '→ admin' }}
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
document.getElementById('pushForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const res = document.getElementById('pushResults');
    res.innerHTML = '<div style="color:#555;font-size:0.78rem;">გაგზავნა…</div>';

    const resp = await fetch('{{ route("push.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            title:   document.getElementById('pTitle').value,
            body:    document.getElementById('pBody').value,
            url:     document.getElementById('pUrl').value || '/',
            user_id: document.getElementById('pUser').value || null,
        })
    });

    const data = await resp.json();
    if (!data.success) { res.innerHTML = '<div style="color:#e74c3c;font-size:0.78rem;">შეცდომა</div>'; return; }

    if (!data.results || data.results.length === 0) {
        res.innerHTML = '<div style="color:#555;font-size:0.78rem;">Subscriber არ არის</div>';
        return;
    }

    const okCount  = data.results.filter(r => r.success).length;
    const errCount = data.results.filter(r => !r.success).length;
    res.innerHTML = `
        <div style="font-size:0.78rem;margin-bottom:8px;color:#777;">
            <span class="ok">✓ ${okCount} წარმატება</span>
            ${errCount > 0 ? `&nbsp;<span class="err">✗ ${errCount} შეცდომა</span>` : ''}
        </div>
        ${data.results.map(r => `
        <div class="result-row">
            <span class="${r.success ? 'ok' : 'err'}">${r.success ? '✓' : '✗'}</span>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.endpoint}</span>
        </div>`).join('')}
    `;
});

async function toggleRole(userId, currentRole) {
    const newRole = currentRole === 'admin' ? 'child' : 'admin';
    const btn = document.getElementById('role-btn-' + userId);
    btn.disabled = true;
    const resp = await fetch(`/admin/users/${userId}/role`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ role: newRole })
    });
    const data = await resp.json();
    if (data.success) {
        document.getElementById('role-' + userId).textContent = data.role;
        btn.textContent = data.role === 'admin' ? '→ gamer' : '→ admin';
    }
    btn.disabled = false;
}
</script>
@endsection
