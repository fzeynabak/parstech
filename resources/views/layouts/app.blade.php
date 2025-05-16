<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'سیستم مدیریت فروش')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')

    <style>
        :root {
            --primary-font: 'Vazirmatn', sans-serif;
            --sidebar-width: 280px;
            --header-height: 60px;
        }

        * {
            font-family: var(--primary-font) !important;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        #sidebar {
            width: var(--sidebar-width);
            background: #1e293b;
            min-height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }

        #sidebar.collapsed {
            right: calc(-1 * var(--sidebar-width));
        }

        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            transition: all 0.3s;
        }

        .main-content.expanded {
            margin-right: 0;
        }

        .navbar {
            height: var(--header-height);
        }

        .content {
            padding: 2rem;
            margin-top: var(--header-height);
        }

        @media (max-width: 768px) {
            #sidebar {
                right: calc(-1 * var(--sidebar-width));
            }

            #sidebar.active {
                right: 0;
            }

            .main-content {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
                <div class="container-fluid">
                    <button id="sidebarCollapse" class="btn btn-link">
                        <i class="fas fa-bars"></i>
                    </button>

                    <a class="navbar-brand me-4" href="{{ route('dashboard') }}">
                        <i class="fas fa-store text-primary me-2"></i>
                        سیستم مدیریت فروش
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-home me-1"></i>
                                    داشبورد
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex align-items-center gap-3">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i>
                                    {{ Auth::user()->name }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="fas fa-user-edit me-1"></i>
                                            ویرایش پروفایل
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-sign-out-alt me-1"></i>
                                                خروج
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <main class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-4">
                <div class="container">
                    <div class="text-center">
                        <p class="mb-0">&copy; {{ date('Y') }} تمامی حقوق محفوظ است.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')

    <script>
        $(document).ready(function() {
            // تنظیمات عمومی
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // بستن alert ها بعد از 5 ثانیه
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);

            // مدیریت سایدبار
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('collapsed');
                $('#main-content').toggleClass('expanded');
            });

            // بستن سایدبار در موبایل با کلیک روی محتوا
            $('.main-content').on('click', function() {
                if (window.innerWidth <= 768) {
                    $('#sidebar').removeClass('active');
                }
            });

            // مدیریت سایدبار در موبایل
            $('#sidebarCollapse').on('click', function() {
                if (window.innerWidth <= 768) {
                    $('#sidebar').toggleClass('active');
                }
            });
        });
    </script>
</body>
</html>
