<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EXPoints - Gaming Review Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/landingpage.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="EXPoints" height="40">
            </a>
            <div class="nav-buttons">
                <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
            </div>
        </div>
    </nav>

    <main class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Level Up Your Gaming Experience</h1>
                    <p class="hero-subtitle">Join the ultimate gaming community. Share reviews, earn EXP, and connect with gamers worldwide.</p>
                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-lg btn-primary">Get Started</a>
                        <a href="#features" class="btn btn-lg btn-outline-light">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="{{ asset('assets/img/LandingPagePanda.png') }}" alt="Gaming Panda" class="hero-image">
                </div>
            </div>
        </div>
    </main>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Why EXPoints?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-star-fill"></i>
                        <h3>Earn EXP</h3>
                        <p>Post reviews and earn experience points to level up your profile.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-people-fill"></i>
                        <h3>Community</h3>
                        <p>Connect with gamers who share your passion for gaming.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-controller"></i>
                        <h3>Game Reviews</h3>
                        <p>Share your honest opinions about the games you love.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 EXPoints. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
