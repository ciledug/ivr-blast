<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            @if (Route::currentRouteName() === 'dashboard')
            <a class="nav-link" href="{{ url('/dashboard') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/dashboard') }}">
            @endif
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
    
        <li class="nav-item">
            @if (Route::currentRouteName() === 'campaign')
            <a class="nav-link" href="{{ url('/campaign') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/campaign') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Campaign</span>
            </a>
        </li>
    
        <li class="nav-item">
            @if (Route::currentRouteName() === 'user')
            <a class="nav-link" href="{{ url('/user') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/user') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>Users</span>
            </a>
        </li>
    
        <li class="nav-item">
            @if (Route::currentRouteName() === 'account')
            <a class="nav-link" href="{{ url('/account') }}">
            @else
            <a class="nav-link collapsed" href="{{ url('/account') }}">
            @endif
                <i class="bi bi-person"></i>
                <span>My Account</span>
            </a>
        </li>
    
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