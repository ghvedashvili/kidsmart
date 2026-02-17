@section('scripts') 
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('answerForm');
    if (!form) return;

    form.addEventListener('submit', function(e){
        e.preventDefault();

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

                    // თუ დააჭირა Next Level-ს
                    if (result.isConfirmed) {
                        window.location.href = "/levels/" + data.nextLevel;
                    }

                    // თუ დააჭირა OK-ს → უბრალოდ დარჩება იმავე გვერდზე
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Wrong answer 😕',
                    text: 'Think again and try once more.',
                    confirmButtonText: 'Retry'
                });
            }
        });

    });
});
</script>
@endsection
