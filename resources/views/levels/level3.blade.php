@extends('levels.layout')
 
@if($userLevel == $level)
@section('content')
<style>
 body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f2f2f2;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden; /* ვაცილებთ scrollbar-ებს */
  }

  .marquee-container {
    width: 100%;        /* სრული ეკრანი */
    overflow: hidden;   /* ტექსტი გადმოსვლას მალავს */
    white-space: nowrap;
    box-sizing: border-box;
    border: 2px solid #ccc; /* სურვილისამებრ ჩარჩო */
    background-color: #fff;
    padding: 10px;
  }

  .marquee-text {
    display: inline-block;
    padding-left: 100%; /* დასაწყისი ეკრანის მიღმა */
    animation: marquee 15s linear infinite;
    font-size: 1.5em;
    font-weight: bold;
    color: #000;
  }

  @keyframes marquee {
    0%   { transform: translateX(0%); }
    100% { transform: translateX(-100%); }
  }
</style>


<div class="marquee-container">
  <div class="marquee-text">
    რუსეთი ოკუპანტია და საერთოდ არ აქვს მნიშვნელობა სად შეგხვდება საპირისპირო — ეს ყველგან უნდა გასწორდეს. სამწუხაროდ სანამ შეცდომა არ გასწორდება თამაშს ვერ გავაგრძელებთ.
  </div>
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