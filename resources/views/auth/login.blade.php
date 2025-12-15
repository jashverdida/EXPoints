<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EXPoints - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">
    <style>
        .loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #0a1a4d 0%, #1b378d 50%, #0a1a4d 100%);
            display: none; justify-content: center; align-items: center; z-index: 9999; flex-direction: column;
        }
        .loading-overlay.active { display: flex; }
        .loading-stars-container { position: relative; width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; }
        .loading-star-main { font-size: 5rem; animation: starRotateGlow 2s ease-in-out infinite; filter: drop-shadow(0 0 30px rgba(255, 215, 0, 1)); }
        .star-halo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border: 2px solid rgba(255, 215, 0, 0.4); border-radius: 50%; animation: haloExpand 2s ease-out infinite; }
        .star-halo:nth-child(1) { width: 120px; height: 120px; animation-delay: 0s; }
        .star-halo:nth-child(2) { width: 160px; height: 160px; animation-delay: 0.5s; }
        .star-halo:nth-child(3) { width: 200px; height: 200px; animation-delay: 1s; }
        @keyframes starRotateGlow { 0% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(180deg) scale(1.2); } 100% { transform: rotate(360deg) scale(1); } }
        @keyframes haloExpand { 0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; } 50% { opacity: 0.6; } 100% { transform: translate(-50%, -50%) scale(1.3); opacity: 0; } }
        .loading-text { margin-top: 3rem; color: white; font-size: 1.5rem; font-weight: 600; animation: textFade 2s ease-in-out infinite; }
        @keyframes textFade { 0%, 100% { opacity: 0.7; } 50% { opacity: 1; } }
        .progress-container { width: 400px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; margin-top: 2rem; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700); border-radius: 10px; width: 0%; transition: width 0.1s ease-out; }
        .progress-text { color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-stars-container">
            <div class="star-halo"></div>
            <div class="star-halo"></div>
            <div class="star-halo"></div>
            <div class="loading-star-main">⭐</div>
        </div>
        <div class="loading-text">Loading your dashboard...</div>
        <div class="progress-container"><div class="progress-bar"></div></div>
        <div class="progress-text">Please wait...</div>
    </div>

    @if(session('error'))
    <div class="custom-alert alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="custom-alert alert-success alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="split-screen-container">
        <div class="left-side">
            <a href="{{ route('home') }}" class="back-button"><i class="bi bi-arrow-left"></i> Back to Home</a>
            <div class="logo-container">
                <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="EXPoints Logo" class="top-logo">
            </div>
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome to EXPoints!</h1>
                <p class="welcome-subtitle" id="rotatingText"></p>
                <img src="{{ asset('assets/img/Login Panda Controller.png') }}" alt="Login Panda" class="panda-mascot">
            </div>
        </div>

        <div class="right-side">
            <div class="login-form-container">
                <h2 class="login-title">Login to Your Account</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control input-glass" id="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control input-glass" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn btn-brand w-100 mb-3" id="loginBtn">LOGIN</button>
                    <div class="text-center mb-3">
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>
                    <div class="auth-divider my-3">OR</div>
                    <button type="button" class="btn btn-google w-100">
                        <span class="g-logo">G</span> Login with Google
                    </button>
                </form>
                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Register here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const welcomeTexts = [
            "Ready to earn more XP? Jump back in and keep leveling up!",
            "Your next achievement awaits! log in and continue your grind!",
            "Welcome back, gamer! The community's waiting for your next review.",
            "Every login brings you closer to the top. Let's see what you've got!",
            "Log in. Level up. Let's play."
        ];
        document.getElementById('rotatingText').textContent = welcomeTexts[Math.floor(Math.random() * welcomeTexts.length)];

        // Login with loading bar that redirects when complete
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.querySelector('.progress-text');
            const loadingText = document.querySelector('.loading-text');

            if (!email || !password) return;

            // Show loading overlay
            document.getElementById('loadingOverlay').classList.add('active');
            loginBtn.disabled = true;

            // Track state
            let loginResponse = null;
            let loginError = null;
            let serverResponded = false;
            let progressInterval = null;

            // Start progress bar animation - slow and steady
            let progress = 0;
            progressInterval = setInterval(() => {
                if (progress < 70) {
                    progress += 0.7; // Reach 70% in ~10 seconds
                } else if (progress < 95) {
                    progress += 0.1; // Very slow crawl from 70-95%
                }
                progressBar.style.width = progress + '%';
            }, 100);

            // Make AJAX login request
            try {
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                loginResponse = await response.json();
                serverResponded = true;

                if (!loginResponse.success) {
                    loginError = loginResponse.error || 'Login failed';
                }
            } catch (error) {
                serverResponded = true;
                loginError = 'Connection error. Please try again.';
            }

            // Server responded - stop the progress bar and handle result
            clearInterval(progressInterval);

            if (loginError) {
                // Show error
                document.getElementById('loadingOverlay').classList.remove('active');
                loginBtn.disabled = false;
                progressBar.style.width = '0%';

                // Create and show error alert
                const existingAlert = document.querySelector('.custom-alert');
                if (existingAlert) existingAlert.remove();

                const alertHtml = `
                    <div class="custom-alert alert-dismissible fade show" role="alert">
                        <div class="alert-content">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>${loginError}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('afterbegin', alertHtml);
            } else if (loginResponse && loginResponse.success) {
                // Success - complete the bar with smooth animation then redirect
                progressBar.style.transition = 'width 0.5s ease-out';
                progressBar.style.width = '100%';
                loadingText.textContent = 'Welcome back!';
                progressText.textContent = 'Loading dashboard...';

                // Redirect after bar completes
                setTimeout(() => {
                    window.location.href = loginResponse.redirect || '{{ route("dashboard") }}';
                }, 600);
            }
        });
    </script>
</body>
</html>
