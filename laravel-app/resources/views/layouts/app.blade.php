<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EXPoints - Game Review Community')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Your existing CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
    
    @stack('styles')
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    @if(auth()->check())
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/img/expoints-logo.png') }}" alt="EXPoints" style="height: 50px;">
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->userInfo->profile_picture ?? asset('assets/img/cat1.jpg') }}" 
                             alt="Profile" 
                             class="rounded-circle" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                        <span class="ms-2">{{ auth()->user()->userInfo->username }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <aside class="side">
        <span class="side-hotspot"></span>
        <div class="side-inner">
            <div class="side-box">
                <button class="side-btn" onclick="window.location.href='{{ route('dashboard') }}'" title="Home">
                    <i class="bi bi-house"></i>
                </button>
                <button class="side-btn" onclick="window.location.href='{{ route('posts.bookmarks') }}'" title="Bookmarks">
                    <i class="bi bi-bookmark"></i>
                </button>
                <button class="side-btn" onclick="window.location.href='{{ route('games') }}'" title="Games">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button class="side-btn" onclick="window.location.href='{{ route('posts.popular') }}'" title="Popular">
                    <i class="bi bi-compass"></i>
                </button>
                <button class="side-btn" onclick="window.location.href='{{ route('posts.newest') }}'" title="Newest">
                    <i class="bi bi-star-fill"></i>
                </button>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="side-btn side-bottom logout-btn-sidebar" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endif
    
    <!-- Main Content -->
    <main class="container-xl py-4">
        <!-- Success/Error Messages -->
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
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Set CSRF token for AJAX requests -->
    <script>
        // Set up CSRF token for all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Configure default headers for fetch requests
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        };
    </script>
    
    @stack('scripts')
</body>
</html>
