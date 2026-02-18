@extends('layouts.app')

@section('content')
@auth
<div class="container text-center mt-5">
    <h1>33333333333333Welcome to GameVeravart ⚽</h1>
    <p>Virtual football universe</p>
</div>
<div class="container mt-4">
    <h3>Welcome back, {{ auth()->user()->name }} 👋</h3>

<a href="{{ route('levels.show', auth()->user()->level) }}"
   class="btn btn-primary mt-3 swal-loader">
    ▶ Continue Game
</a>


</div>
@else
<div class="container text-center mt-5">
    <h1>eeeeeeeeeeeeeeeeeeeeeeeeeeeWelcome to GameVeravart ⚽</h1>
    <p>eeeeeeeeeeeeeeeeeeeeeeeeeeeVirtual football universe</p>
</div>

@endauth

<script>

    document.querySelectorAll('.swal-loader').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();

        Swal.fire({
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: 'transparent',
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            window.location.href = this.href;
        }, 500);
    });
});

    </script>
    @endsection