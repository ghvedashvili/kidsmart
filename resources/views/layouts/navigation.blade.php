@php
    use App\Models\Question;

    $levels = Question::select('level')->distinct()->orderBy('level')->get();
    $activeLevel = auth()->user() ? auth()->user()->level: 1; // აქტიური ლეველი მოთამაშისთვის
@endphp
<style>
.google-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
   
    color: #fff;
   
    font-weight: 500;
    
    transition: all 0.2s ease;
}
.google-btn img {
    width: 18px;
}
.google-btn:hover {
   
    transform: translateY(-1px);
}
</style>

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
          <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        👤 {{ \Illuminate\Support\Str::limit(auth()->user()->nickname, 10, '...') }}
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li class="dropdown-item">
            <i class="bi bi-person-fill"></i> Nickname: {{ auth()->user()->name }}
        </li>
        <li class="dropdown-item">
            <i class="bi bi-calendar-day"></i> Days since registration: {{ now()->diffInDays(auth()->user()->created_at) }}
        </li>
        <li class="dropdown-item">
            <i class="bi bi-flag-fill"></i> Levels passed: {{ auth()->user()->level - 1 }}
        </li>
        <li class="dropdown-item">
            <i class="bi bi-star-fill"></i> XP: {{ auth()->user()->xp }}
        </li>
        <li class="dropdown-item">
            <i class="bi bi-lightbulb-fill"></i> Hints: {{ auth()->user()->hints }}
        </li>
    </ul>
</li>


          <!-- Levels Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown"
               aria-expanded="false">
              Levels
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" style="max-height: 300px; overflow-y: auto;">
    @foreach($levels as $lvl)
    @php
        $classes = '';

        if ($lvl->level == $activeLevel) {
            $classes = 'fw-bold text-warning';
        } elseif ($lvl->level < $activeLevel) {
            $classes = 'text-success';
        } elseif ($lvl->level > auth()->user()->level) {
            $classes = 'disabled';
        }
    @endphp

    <li>
        <a class="dropdown-item {{ $classes }}"
           href="{{ $lvl->level <= auth()->user()->level
                    ? route('levels.show', $lvl->level)
                    : '#' }}"
           data-loader
           data-loader-text="Loading Level {{ $lvl->level }}…">
            Level {{ $lvl->level }}

            
        </a>
    </li>
@endforeach

</ul>

          </li>

          <!-- Logout -->
          <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
            class="nav-link text-white bg-transparent border-0"
            onclick="AppLoader.show('Logging out…')">
        Logout
    </button>
</form>
          </li>

        @else
  <!-- სტუმარი → Modal / Google Login -->
  <li class="nav-item">
<a href="{{ route('google.login') }}"
   data-loader
   data-loader-text="Signing in with Google…">
   Login with Google
</a>
  </li>


 
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
