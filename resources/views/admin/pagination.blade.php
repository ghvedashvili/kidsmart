@if ($paginator->hasPages())
    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="color:#444;font-family:'Goldman',monospace;font-size:0.68rem;padding:4px 6px;">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active-page" style="color:#aaa;font-family:'Goldman',monospace;font-size:0.68rem;border:1px solid #444;border-radius:3px;padding:4px 10px;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="color:#555;font-family:'Goldman',monospace;font-size:0.68rem;border:1px solid #222;border-radius:3px;padding:4px 10px;text-decoration:none;">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
@endif
