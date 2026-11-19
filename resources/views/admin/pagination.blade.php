@if ($paginator->hasPages())
    @php
        $__pgBase = "display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border-radius:10px;font-family:'Noto Sans Georgian',sans-serif;font-size:.84rem;font-weight:700;text-decoration:none;box-sizing:border-box;";
        $__pgLink = $__pgBase . "color:#6B625A;background:#fff;border:1.5px solid #E8DFD5;";
    @endphp
    @if ($paginator->onFirstPage())
        <span style="{{ $__pgLink }}opacity:.4;">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="{{ $__pgLink }}" aria-label="წინა">‹</a>
    @endif
    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="{{ $__pgBase }}color:#8A8078;">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active-page" aria-current="page" style="{{ $__pgBase }}color:#fff;background:#1D1A17;border:1.5px solid #1D1A17;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="{{ $__pgLink }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="{{ $__pgLink }}" aria-label="შემდეგი">›</a>
    @else
        <span style="{{ $__pgLink }}opacity:.4;">›</span>
    @endif
@endif
