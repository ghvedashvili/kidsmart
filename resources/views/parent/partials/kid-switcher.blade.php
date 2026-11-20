{{-- შვილებს შორის გადამრთველი — გვერდის ზედა ბარათის ქვეშ (ჩანს, როცა 1-ზე მეტი შვილია) --}}
@php
    $__swKids  = auth()->user()->children()->withTimestamps()->orderByPivot('created_at', 'asc')->get();
    $__swRoute = request()->route()?->getName() ?? 'child.hub';
@endphp
@if($__swKids->count() > 1)
<div class="ks-pkids-top ks-pkids-page" role="tablist" aria-label="შვილი">
    @foreach($__swKids as $__k)
    <a href="{{ route($__swRoute, $__k) }}" role="tab" aria-selected="{{ $__k->id === $child->id ? 'true' : 'false' }}" class="ks-pkid {{ $__k->id === $child->id ? 'on' : '' }}">
        <span class="ks-pkid-av {{ $__k->avatar }}">{{ $__k->avatar === 'boy' ? '👦' : ($__k->avatar === 'girl' ? '👧' : '🙂') }}</span>{{ $__k->name }}
    </a>
    @endforeach
</div>
@endif
