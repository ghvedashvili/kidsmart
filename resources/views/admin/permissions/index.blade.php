@extends('layouts.app')
{{-- redesign-kidsmart: წვდომა როლების მიხედვით (დიზაინი A9) --}}
@section('content')
@include('admin.partials.settings-tabs', ['active' => 'perms'])

@php
    $__roleNames = ['parent' => 'მშობელი', 'child' => 'ბავშვი', 'teacher' => 'მასწავლებელი', 'staff' => 'სტაფი', 'admin' => 'ადმინი'];
@endphp

<div class="ks-note">ადმინს ყოველთვის სრული წვდომა აქვს. თუ როლს არცერთი გვერდი არ აქვს მონიშნული, შეზღუდვა არ მოქმედებს და ყველა გვერდი ღიაა.</div>

<form method="POST" action="{{ route('admin.permissions.update') }}" class="ks-card ks-card-tight" style="display:flex;flex-direction:column;gap:12px;">
    @csrf
    <div class="ks-table-wrap">
        <table class="ks-table" style="min-width:560px;">
            <thead>
                <tr>
                    <th>გვერდი</th>
                    @foreach($roles as $role)
                    <th style="text-align:center;">{{ $__roleNames[$role] ?? $role }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $adminGroupShown = false; @endphp
                @foreach($pages as $pageKey => $pageLabel)
                @php $isAdminPage = str_starts_with($pageKey, 'admin.'); @endphp
                @if($isAdminPage && !$adminGroupShown)
                    @php $adminGroupShown = true; @endphp
                    <tr><td colspan="{{ count($roles) + 1 }}" style="padding-top:18px;"><span class="ks-chip ks-chip-dark">ადმინ გვერდები</span></td></tr>
                @endif
                <tr>
                    <td style="font-weight:700;{{ $isAdminPage ? 'padding-left:24px;' : '' }}">{{ $pageLabel }}</td>
                    @foreach($roles as $role)
                    <td style="text-align:center;">
                        <input type="checkbox" name="perms[{{ $role }}][]" value="{{ $pageKey }}"
                               aria-label="{{ $pageLabel }} — {{ $__roleNames[$role] ?? $role }}"
                               style="width:20px;height:20px;cursor:pointer;accent-color:#C24A0A;"
                               {{ in_array($pageKey, $perms[$role] ?? []) ? 'checked' : '' }}>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="display:flex;align-items:center;gap:12px;padding:0 6px 4px;">
        <button type="submit" class="ks-btn ks-btn-primary">შენახვა</button>
        <span class="ks-muted" style="font-size:12px;">ცვლილება მაშინვე ამოქმედდება</span>
    </div>
</form>
@endsection
