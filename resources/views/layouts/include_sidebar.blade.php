<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <!-- dashboard -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'dashboard'))
            <a class="nav-link" href="{{ url('/dashboard') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/dashboard') }}">
            @endif
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
    
        @if (Auth::user()->username !== 'sadmin')
        <!-- campaign -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'campaigns'))
            <a class="nav-link" href="{{ url('/campaigns') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/campaigns') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Campaigns</span>
            </a>
        </li>
        @endif

        <!-- calllogs -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'calllogs'))
            <a class="nav-link" href="{{ route('calllogs.list') }}">
            @else
            <a class="nav-link collapsed" href="{{ route('calllogs.list') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Call Logs</span>
            </a>
        </li>

        <!-- users -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'users'))
            <a class="nav-link" href="{{ url('/users') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/users') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Users</span>
            </a>
        </li>
    
        <!-- account -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'account'))
            <a class="nav-link" href="{{ url('/account') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/account') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>My Account</span>
            </a>
        </li>

        @if (Auth::user()->username === 'sadmin')
        <!-- template -->
        <li class="nav-item">
            @if (str_contains(Route::currentRouteName(), 'templates'))
            <a class="nav-link" href="{{ url('/templates') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/templates') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Templates</span>
            </a>
        </li>
        @endif
    
        {{-- logout --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" onclick="submitLogout(event)">
                <i class="bi bi-person"></i>
                <span>Sign Out</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                {{ csrf_field() }}
            </form>
        </li>
    </ul>
</aside>