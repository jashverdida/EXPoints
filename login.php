<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body>

<main class="container app-container">

  <h1 class="auth-hero text-center">Welcome back Gamer! Ready to Login?</h1>

  <section class="auth-wrap">
    <div class="auth-card">
      <img class="auth-mascot" src="assets/img/Login Panda Controller.png" alt="Panda mascot" />

      <a class="btn btn-play" href="index.php" aria-label="Back to Landing">
        <i class="bi bi-play-fill"></i>
      </a>

      <div class="auth-logo-wrap">
        <img class="auth-logo-img" src="assets/img/EXPoints Logo.png" alt="+EXPoints" />
      </div>

      <!-- Form -->
      <form id="loginForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input id="email" type="email" class="form-control input-glass" placeholder="Enter Valid Email" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Password</label>
          <input id="password" type="password" class="form-control input-glass" placeholder="Enter Password" required>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 small">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember">
            <label class="form-check-label" for="remember">Remember Me</label>
          </div>
          <a href="forgot.php" class="link-light text-decoration-none">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-brand w-100 mb-3">LOGIN</button>

        <div class="auth-divider my-3">OR</div>

        <button type="button" class="btn btn-google w-100">
          <span class="g-logo">G</span>
          Log in with Google
        </button>
      </form>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- Firebase Login Script with Firestore Synchronization -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
  import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

  const firebaseConfig = {
    apiKey: "AIzaSyAX8oYh-i_9Qe2RU8qNUidmx0OWrIJZPFY",
    authDomain: "expoints-d6461.firebaseapp.com",
    projectId: "expoints-d6461",
    storageBucket: "expoints-d6461.firebasestorage.app",
    messagingSenderId: "798336813425",
    appId: "1:798336813425:web:38cd94cc67234738a00ed0",
    measurementId: "G-EV96R3ZL8D"
  };

  const app = initializeApp(firebaseConfig);
  const auth = getAuth(app);

  document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!email || !password) {
      alert('Please enter both email and password');
      return;
    }

    try {
      // Step 1: Authenticate with Firebase Auth
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      const user = userCredential.user;

      // Step 2: Get Firebase ID Token
      const idToken = await user.getIdToken();

      // Step 3: Verify and sync with Firestore users collection
      const response = await fetch("verify_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ 
          idToken: idToken,
          email: user.email,
          uid: user.uid
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Successfully authenticated and synchronized
        console.log('Login successful - Firebase Auth synced with Firestore');
        window.location.href = "dashboard.php";
      } else {
        alert("Login verification failed: " + data.error);
      }

    } catch (error) {
      console.error('Login error:', error);
      alert("Login failed: " + error.message);
    }
  });
</script>
</body>
</html>
