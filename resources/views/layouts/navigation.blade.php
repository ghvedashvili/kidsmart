@php
    use App\Models\Question;
    use App\Models\User;

    $levels = Question::select('level')->distinct()->orderBy('level')->get();
    $activeLevel = auth()->user() ? auth()->user()->level : 1;

    if (auth()->check()) {
        $myLevel      = auth()->user()->level;
        $myId         = auth()->user()->id;
        $totalPlayers = User::count();
        $aheadCount   = User::where('level', '>', $myLevel)->count();
        $sameCount    = User::where('level', $myLevel)->where('id', '!=', $myId)->count();
        $belowCount   = User::where('level', '<', $myLevel)->count();
        $myHints      = auth()->user()->hints ?? 0;
    }
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
          <!-- Hints counter -->
          <li class="nav-item d-flex align-items-center me-1">
              <span class="nav-link text-warning pe-none" style="font-size:0.85rem;cursor:default;" title="Hints დარჩენილი">
                  💡 {{ $myHints }}
              </span>
          </li>

          <!-- Username → stats Swal -->
          <li class="nav-item d-flex align-items-center">
              <a class="nav-link text-white" href="#" onclick="showPlayerStats(event)">
                  👤 {{ \Illuminate\Support\Str::limit(auth()->user()->nickname, 10, '...') }}
              </a>
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

          @if(auth()->user()->isAdmin())
          <li class="nav-item">
              <a class="nav-link text-danger fw-bold" href="{{ route('admin.panel') }}">
                  <i class="bi bi-shield-lock-fill"></i> Admin
              </a>
          </li>
          @endif

          <!-- Logout -->
          <li class="nav-item d-flex align-items-center">
    <form method="POST"
          action="{{ route('logout') }}"
          data-loader
          data-loader-text="Signing out…"
          class="m-0">
        @csrf
        <button type="submit"
                class="nav-link btn btn-link text-danger p-0">
            Logout
        </button>
    </form>
</li>


        @else
  <!-- სტუმარი → Modal / Google Login -->
  <li class="nav-item">
    <a class="nav-link text-white d-flex align-items-center gap-2"
       href="{{ route('google.login') }}"
       data-loader
       data-loader-text="Signing in with Google…">
       
       <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18">
       Login with Google
    </a>
</li>


 
          <!-- სტუმარი → Modal -->
          <!-- <li class="nav-item">
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
          </li> -->
        
@endauth


      </ul>
    </div>
  </div>
</nav>
<script>
function showPlayerStats(e) {
    e.preventDefault();
    Swal.fire({
        title: '📊 შენი სტატისტიკა',
        html: `
            <div style="text-align:left;line-height:2.2;font-size:0.92rem;">
                <div>👥 სულ მოთამაშე: <b>{{ $totalPlayers }}</b></div>
                <hr style="margin:6px 0;border-color:#eee;">
                <div>🔼 ჩემზე წინ: <b>{{ $aheadCount }}</b></div>
                <div>🟡 ჩემს ტურში: <b>{{ $sameCount }}</b></div>
                <div>🔽 ჩემზე დაბლა: <b>{{ $belowCount }}</b></div>
                <hr style="margin:6px 0;border-color:#eee;">
                <div>💡 დარჩენილი hints: <b>{{ $myHints }}</b></div>
                <div>🏁 გავლილი ლეველი: <b>{{ auth()->user()->level - 1 }}</b></div>
            </div>
        `,
        confirmButtonText: 'დახურვა',
        confirmButtonColor: '#111',
        width: 320,
    });
}
</script>
<script>
    document.addEventListener('submit', e => {
    const form = e.target.closest('form[data-loader]');
    if (form) {
        AppLoader.show(form.dataset.loaderText || 'Loading…');
    }
});

    </script>
    <script>
document.addEventListener('click', function (event) {
    const navbar = document.getElementById('navbarContent');
    const toggler = document.querySelector('.navbar-toggler');

    if (
        navbar.classList.contains('show') &&
        !navbar.contains(event.target) &&
        !toggler.contains(event.target)
    ) {
        bootstrap.Collapse.getOrCreateInstance(navbar).hide();
    }
});
</script>