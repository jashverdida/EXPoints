<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EXPoints - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/register.css') }}">
    <style>
        .back-button-register { position: absolute; top: 2rem; right: 2rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, rgba(56, 160, 255, 0.15), rgba(12, 31, 111, 0.1)); backdrop-filter: blur(10px); border: 2px solid rgba(56, 160, 255, 0.3); border-radius: 50px; color: #0c1f6f; text-decoration: none; font-weight: 600; z-index: 10; }
        .back-button-register:hover { background: linear-gradient(135deg, rgba(56, 160, 255, 0.25), rgba(12, 31, 111, 0.15)); border-color: #38a0ff; color: #38a0ff; transform: translateX(5px); }
        .username-modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(8, 18, 46, 0.95); backdrop-filter: blur(10px); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .username-modal-backdrop.show { display: flex; }
        .username-modal { background: white; border-radius: 1.5rem; padding: 3rem; max-width: 500px; width: 90%; text-align: center; }
        .username-modal h3 { font-weight: 700; color: #0c1f6f; margin-bottom: 1rem; }
        .username-modal .form-control { padding: 0.875rem 1.25rem; border-radius: 2rem; border: 2px solid #e0e0e0; }
        .username-modal .btn-complete { background: linear-gradient(135deg, #38a0ff 0%, #0c1f6f 100%); color: white; border: none; padding: 0.875rem 2rem; border-radius: 2rem; font-weight: 600; margin-top: 1.5rem; width: 100%; }
        .username-error { color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: none; }
        .username-success { color: #28a745; font-size: 0.875rem; margin-top: 0.5rem; display: none; }
    </style>
</head>
<body>
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="split-screen-container">
        <div class="left-side">
            <a href="{{ route('login') }}" class="back-button-register"><span>Back to Login</span> <i class="bi bi-arrow-right"></i></a>
            <div class="register-form-container">
                <h2 class="register-title">Create Your Account</h2>
                <p class="register-subtitle">Join the gaming community today</p>

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input id="firstName" name="first_name" class="form-control input-pill" placeholder="First Name" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input id="middleName" name="middle_name" class="form-control input-pill" placeholder="Middle Name (optional)" value="{{ old('middle_name') }}">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input id="lastName" name="last_name" class="form-control input-pill" placeholder="Last Name" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="suffix" class="form-label">Suffix</label>
                            <input id="suffix" name="suffix" class="form-control input-pill" placeholder="Suffix (optional)" value="{{ old('suffix') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" name="username" class="form-control input-pill" placeholder="Choose a username" value="{{ old('username') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="regEmail" class="form-label">Email</label>
                        <input id="regEmail" name="email" type="email" class="form-control input-pill" placeholder="Enter your email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="regPass" class="form-label">Create Password</label>
                        <input id="regPass" name="password" type="password" class="form-control input-pill" placeholder="Enter password (min 6 characters)" required>
                    </div>
                    <div class="mb-4">
                        <label for="regPass2" class="form-label">Confirm Password</label>
                        <input id="regPass2" name="password_confirmation" type="password" class="form-control input-pill" placeholder="Re-enter password" required>
                    </div>
                    <button type="submit" class="btn btn-brand w-100 mb-3">REGISTER</button>
                    <div class="auth-divider my-3">OR</div>
                    <button type="button" class="btn btn-google w-100"><span class="g-logo">G</span> Register with Google</button>
                </form>
                <div class="login-link">Already have an account? <a href="{{ route('login') }}">Login here</a></div>
            </div>
        </div>
        <div class="right-side">
            <div class="welcome-content">
                <h1 class="welcome-title">Join the EXPoints Community!</h1>
                <p class="welcome-subtitle">Connect with gamers worldwide and share your gaming experiences</p>
                <img src="{{ asset('assets/img/registerpanda.png') }}" alt="Register Panda" class="panda-mascot">
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
