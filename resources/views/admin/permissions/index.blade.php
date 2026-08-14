@extends('layouts.app')
@section('content')
<style>
    body { background: transparent !important; }
    .aw { max-width: 760px; margin: 0 auto; padding: 32px 16px 64px; font-family: 'Goldman', monospace; }
    .atitle { font-size: 0.75rem; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 8px; }
    .anav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .anav a { font-family: 'Goldman', monospace; font-size: 0.7rem; color: #64748b; letter-spacing: 0.08em; text-decoration: none; padding: 4px 12px; border: 1px solid #e2e8f0; border-radius: 3px; transition: color 0.2s, border-color 0.2s; }
    .anav a:hover, .anav a.active { color: #1e293b; border-color: #94a3b8; }
    .card-dark { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .msg { font-size: 0.75rem; color: #059669; margin-bottom: 16px; }
    .perm-table { width: 100%; border-collapse: collapse; }
    .perm-table th { font-size: 0.62rem; color: #94a3b8; letter-spacing: 0.1em; text-transform: uppercase; padding: 8px 16px; text-align: center; font-weight: 400; border-bottom: 1px solid #f1f5f9; }
    .perm-table th.page-col { text-align: left; }
    .perm-table td { padding: 10px 16px; border-bottom: 1px solid #f8fafc; text-align: center; vertical-align: middle; }
    .perm-table td.page-name { text-align: left; font-size: 0.8rem; color: #374151; }
    .perm-table tr:last-child td { border-bottom: none; }
    .perm-table tr:hover td { background: #fafafa; }
    .perm-cb { width: 16px; height: 16px; cursor: pointer; accent-color: #374151; }
    .role-hdr { font-size: 0.72rem; font-weight: 500; }
    .r-admin   { color: #92400e; }
    .r-parent  { color: #1e40af; }
    .r-child   { color: #065f46; }
    .r-teacher { color: #5b21b6; }
    .r-staff   { color: #991b1b; }
    .note { font-size: 0.65rem; color: #94a3b8; margin-top: 4px; }
    .btn-save { background: #1e293b; border: none; color: #fff; font-family: 'Goldman', monospace; font-size: 0.78rem; letter-spacing: 0.08em; padding: 10px 28px; border-radius: 4px; cursor: pointer; transition: background 0.2s; }
    .btn-save:hover { background: #334155; }
    .col-check { padding: 0 8px; }
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
        <a href="{{ route('admin.permissions.index') }}" class="active">ნებართვები</a>
    </nav>

    @if(session('success'))
    <div class="msg">{{ session('success') }}</div>
    @endif

    <div class="card-dark">
        <div style="font-size:0.75rem;color:#374151;margin-bottom:4px;">გვერდების წვდომა როლების მიხედვით</div>
        <div class="note" style="margin-bottom:20px;">admin-ს ყოველთვის აქვს სრული წვდომა. თუ როლისთვის არცერთი გვერდი არ არის მონიშნული — შეზღუდვა არ მუშაობს (ნებას რთავს ყველაზე).</div>

        <form method="POST" action="{{ route('admin.permissions.update') }}">
            @csrf
            <table class="perm-table">
                <thead>
                    <tr>
                        <th class="page-col">გვერდი</th>
                        @foreach($roles as $role)
                        <th class="col-check"><span class="role-hdr r-{{ $role }}">{{ $role }}</span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $pageKey => $pageLabel)
                    <tr>
                        <td class="page-name">{{ $pageLabel }}</td>
                        @foreach($roles as $role)
                        <td class="col-check">
                            <input type="checkbox" class="perm-cb"
                                name="perms[{{ $role }}][]"
                                value="{{ $pageKey }}"
                                {{ in_array($pageKey, $perms[$role] ?? []) ? 'checked' : '' }}>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:20px;display:flex;align-items:center;gap:16px;">
                <button type="submit" class="btn-save">✓ შენახვა</button>
                <span style="font-size:0.65rem;color:#94a3b8;">ცვლილება დაუყოვნებლივ ამოქმედდება</span>
            </div>
        </form>
    </div>
</div>
@endsection
