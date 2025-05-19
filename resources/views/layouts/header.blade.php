<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav mr-auto-navbav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">خانه</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('dashboard') }}" class="nav-link">داشبورد</a>
        </li>
        <!-- User profile dropdown in header -->
        @auth
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="headerProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::user()->profile_photo_url ?? asset('img/user.png') }}" class="rounded-circle" alt="User Image" width="32" height="32">
                <span class="ms-2">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="headerProfileDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit me-2"></i> ویرایش پروفایل
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}#update-password-form">
                        <i class="fas fa-key me-2"></i> تغییر رمز عبور
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-image me-2"></i> تغییر تصویر کاربر
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="header-logout-form">
                        @csrf
                        <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault();document.getElementById('header-logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> خروج
                        </a>
                    </form>
                </li>
            </ul>
        </li>
        @endauth
    </ul>
</nav>
