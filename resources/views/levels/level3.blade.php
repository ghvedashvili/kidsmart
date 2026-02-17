@extends('levels.layout')
 
@if($userLevel == $level)
@section('content')
<div class="container text-center mt-5">

    <h2>Level 003</h2>

    <p class="mt-3">
        👁️ ყურადღებით დააკვირდი მისამართის ზოლს ზემოთ
    </p>

</div>


@if($completed)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Correct',
    text: 'Truth matters.',
    showCancelButton: true,
    confirmButtonText: 'Next Level',
    cancelButtonText: 'OK',
    reverseButtons: true
}).then((result) => {
    if (result.isConfirmed) {
        fetch('/levels/3/complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
            // body არ გჭირდება
        })
        .then(res => {
            if (!res.ok) throw new Error('POST failed');
            return res.json();
        })
        .then(data => {
            console.log('LEVEL UPDATED:', data);
            window.location.href = '/levels/4';
        })
        .catch(err => {
            alert('Level update failed');
            console.error(err);
        });
    }
});
</script>
@endif



@endsection
@else
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">level{{ $level }}</h5>
                    @if($userLevel > $level)
                        <div class="alert alert-success">დიახ რუსეთი ოკუპანტია!!! </div>
                    @else
                        <div class="alert alert-warning">⚠️ ეს დონე ჯერ არ არის ხელმისაწვდომი</div>
                    @endif
                    <a href="{{ route('levels.show', ['level' => $userLevel]) }}" class="btn btn-primary">გადადით მიმდინარე დონეზე</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif