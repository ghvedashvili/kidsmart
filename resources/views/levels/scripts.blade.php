@section('scripts') 
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('answerForm');
    if (!form) return;

    form.addEventListener('submit', function(e){
        e.preventDefault();

        /* 🔴 Loader გამოჩნდეს მაშინვე */
        Swal.fire({
            // title: 'Checking...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            background:'transparent',
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ route('levels.check', $level) }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                answer: document.getElementById('answer').value
            })
        })
        .then(r => r.json())
        .then(data => {

            Swal.close(); // ⛔ Loader დავხუროთ
console.log(data);
            if(data.status === 'correct') {

                Swal.fire({
                    icon: 'success',
                    title: 'Correct! 🎉',
                    text: 'What would you like to do?',
                    showCancelButton: true,
                    confirmButtonText: 'Next Level 🚀',
                    cancelButtonText: 'OK',
                    reverseButtons: true
                }).then((result) => {

                    if (result.isConfirmed) {
                        window.location.href = "/levels/" + data.nextLevel;
                    }

                });

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Wrong answer 😕',
                    text: 'Think again and try once more.',
                    confirmButtonText: 'Retry'
                });

            }
        })
        .catch(() => {

            Swal.close();

            Swal.fire({
                icon: 'error',
                title: 'Server error ⚠️',
                text: 'Something went wrong.'
            });

        });

    });
});

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
