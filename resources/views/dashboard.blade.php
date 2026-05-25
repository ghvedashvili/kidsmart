@extends('layouts.app')

@section('bodyClass', 'dot-light')

@section('content')
<div class="container mt-4">
    <h3>Welcome back, {{ auth()->user()->name }} 👋</h3>

    <a href="{{ route('levels.show', auth()->user()->level) }}"
       class="btn btn-primary mt-3">
        ▶ Continue Game
    </a>
</div>
@endsection
