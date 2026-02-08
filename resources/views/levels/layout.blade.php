@extends('layouts.app')

@section('content')

{{-- ✅ წესები: 100% სიგანე, ჰორიზონტალური --}}
@if($userLevel == $level && $question->rules)
    <div class="rules-bar w-100 py-3 px-3 mb-4 text-center">
        {{ $question->rules }}
    </div>
@endif

{{-- ✅ Level-ის კონტენტი ქვეშ --}}
<div class="level-content-wrapper d-flex justify-content-center">
    @yield('level-content')
</div>

{{-- დასრულებული ლეველი --}}
@if($userLevel > $level)
    <div class="alert alert-info mt-3 text-center">
        ✅ Completed
    </div>
@endif

@endsection
