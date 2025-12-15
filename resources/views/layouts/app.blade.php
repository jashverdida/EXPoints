<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EXPoints - @yield('title', 'Home')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Top bar -->
    <div class="container-xl mt-3">
        <header class="topbar">
            <a href="{{ route('dashboard') }}" class="lp-brand" aria-label="+EXPoints home">
                <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="+EXPoints" class="lp-brand-img">
            </a>

            <form class="search" role="search" action="{{ route('dashboard') }}" method="GET">
                <input type="text" name="search" placeholder="Search for a Review, a Game, Anything" value="{{ request('search') }}">
                <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </form>

            <div class="right">
                <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
                <div class="settings-dropdown">
                    <button class="icon settings-btn" title="Settings"><i class="bi bi-gear"></i></button>
                    <div class="dropdown-menu">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
                <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
                <a href="{{ route('profile.show') }}" class="avatar-nav">
                    <img src="{{ $userProfilePicture ?? asset('assets/img/cat1.jpg') }}" alt="Profile" class="avatar-img">
                </a>
            </div>
        </header>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <ul class="nav-list">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house"></i> Home</a></li>
            <li><a href="{{ route('popular') }}" class="{{ request()->routeIs('popular') ? 'active' : '' }}"><i class="bi bi-fire"></i> Popular</a></li>
            <li><a href="{{ route('newest') }}" class="{{ request()->routeIs('newest') ? 'active' : '' }}"><i class="bi bi-clock"></i> Newest</a></li>
            <li><a href="{{ route('bookmarks') }}" class="{{ request()->routeIs('bookmarks') ? 'active' : '' }}"><i class="bi bi-bookmark"></i> Bookmarks</a></li>
            <li><a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="bi bi-person"></i> Profile</a></li>
            @if(session('user_role') === 'admin')
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}"><i class="bi bi-shield"></i> Admin</a></li>
            @endif
            @if(session('user_role') === 'mod')
            <li><a href="{{ route('mod.dashboard') }}" class="{{ request()->routeIs('mod.*') ? 'active' : '' }}"><i class="bi bi-shield-check"></i> Moderator</a></li>
            @endif
        </ul>
    </nav>

    <main class="container-xl py-4 main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.csrfToken = '{{ csrf_token() }}';
        document.querySelector('.settings-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.nextElementSibling.classList.toggle('show');
        });
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        });
    </script>
    @stack('scripts')
</body>
</html>
