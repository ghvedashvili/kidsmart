{{-- ბავშვის ავატარი: არჩეული სურათი (public/avatars) ან ემოჯი. გამოყენება: @include('partials.kid-av', ['k' => $child]) --}}
@php $__kavUrl = $k->avatarUrl(); @endphp
@if($__kavUrl)<img src="{{ $__kavUrl }}" alt="" class="ks-avimg" loading="lazy">@else{{ $k->avatar === 'boy' ? '👦' : ($k->avatar === 'girl' ? '👧' : '🙂') }}@endif
