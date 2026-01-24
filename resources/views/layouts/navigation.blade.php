@php
    use App\Models\Question;

    $levels = Question::select('level')->distinct()->orderBy('level')->get();
    $activeLevel = auth()->user() ? auth()->user()->level + 1 : 1; // აქტიური ლეველი მოთამაშისთვის
@endphp

<nav class="navbar navbar-expand-lg bg-dark border-bottom border-body fixed-top" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="{{ url('/') }}">GameVeravart</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

        @auth
          <!-- შესული მომხმარებელი -->
          <li class="nav-item">
            <span class="nav-link text-white">
              👤 {{ auth()->user()->name }}
            </span>
          </li>

          <!-- Levels Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown"
               aria-expanded="false">
              Levels
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" style="max-height: 300px; overflow-y: auto;">
                <li>
                    <a class="dropdown-item {{ $activeLevel == 1 ? 'fw-bold text-warning' : '' }}"
                       href="#introduction">
                        Introduction
                        @if($activeLevel == 1) (Current) @endif
                    </a>
                </li>
                @foreach($levels as $level)
                    @php
                        $isActive = $level->level == $activeLevel;
                        $isDisabled = $level->level > $activeLevel;
                    @endphp
                    <li>
                        <a class="dropdown-item {{ $isDisabled ? 'disabled text-muted' : '' }} {{ $isActive ? 'fw-bold text-warning' : '' }}"
                           href="{{ $isDisabled ? '#' : '#level' . $level->level }}">
                            Level {{ $level->level }}
                            @if($isActive) (Current) @endif
                        </a>
                    </li>
                @endforeach
            </ul>
          </li>

          <!-- Logout -->
          <li class="nav-item">
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <a href="#"
                   class="nav-link text-white"
                   onclick="event.preventDefault(); this.closest('form').submit();">
                    Logout
                </a>
            </form>
          </li>

        @else
          <!-- სტუმარი → Modal -->
          <li class="nav-item">
            <a class="nav-link text-white"
               href="#"
               data-bs-toggle="modal"
               data-bs-target="#loginModal">
              Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white"
               href="#"
               data-bs-toggle="modal"
               data-bs-target="#registerModal">
              Register
            </a>
          </li>
        @endauth

      </ul>
    </div>
  </div>
</nav>
