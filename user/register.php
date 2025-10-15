<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Register</title>

  <!-- Bootstrap 5.3.7 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
  <div class="split-screen-container">
    <!-- LEFT SIDE - White Register Form -->
    <div class="left-side">
      <div class="register-form-container">
        <!-- Logo at top left (overlays on white side) -->
        <div class="logo-container">
          <img src="../assets/img/EXPoints Logo.png" alt="EXPoints Logo" class="top-logo">
        </div>

        <h2 class="register-title">Create Your Account</h2>
        <p class="register-subtitle">Join EXPoints and start discussing your favorite games!</p>

        <!-- Register Form -->
        <form action="dashboard.php" method="get" novalidate>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="firstName" class="form-label">First Name</label>
              <input id="firstName" class="form-control input-pill" placeholder="First Name" required>
            </div>
            <div class="col-md-6">
              <label for="middleName" class="form-label">Middle Name</label>
              <input id="middleName" class="form-control input-pill" placeholder="Middle Name">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="lastName" class="form-label">Last Name</label>
              <input id="lastName" class="form-control input-pill" placeholder="Last Name" required>
            </div>
            <div class="col-md-6">
              <label for="suffix" class="form-label">Suffix</label>
              <input id="suffix" class="form-control input-pill" placeholder="Suffix (optional)">
            </div>
          </div>

          <div class="mb-3">
            <label for="regEmail" class="form-label">Email or Phone Number</label>
            <input id="regEmail" type="email" class="form-control input-pill" placeholder="Enter your email or phone number" required>
          </div>

          <div class="mb-3">
            <label for="regPass" class="form-label">Create Password</label>
            <input id="regPass" type="password" class="form-control input-pill" placeholder="Enter password" required>
          </div>

          <div class="mb-4">
            <label for="regPass2" class="form-label">Confirm Password</label>
            <input id="regPass2" type="password" class="form-control input-pill" placeholder="Re-enter password" required>
          </div>

          <button type="submit" class="btn btn-brand w-100 mb-3">CONTINUE</button>

          <div class="auth-divider my-3">OR</div>

          <button type="button" class="btn btn-google w-100">
            <span class="g-logo">G</span> 
            Register with Google
          </button>
        </form>

        <div class="login-link">
          Already have an account? <a href="login.php">Login here</a>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDE - Blue Welcome Section -->
    <div class="right-side">
      <div class="welcome-content">
        <h1 class="welcome-title">Join the EXPoints Community!</h1>
        <p class="welcome-subtitle">Connect with gamers worldwide and share your gaming experiences</p>
        <img src="../assets/img/registerpanda.png" alt="Register Panda" class="panda-mascot">
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- Firebase Registration Script -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
  import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-auth.js";

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

  // Handle form submission
  document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPass').value;
    const confirmPassword = document.getElementById('regPass2').value;

    // Basic validation
    if (!firstName || !lastName || !email || !password) {
      alert('Please fill in all required fields');
      return;
    }

    if (password !== confirmPassword) {
      alert('Passwords do not match');
      return;
    }

    if (password.length < 6) {
      alert('Password must be at least 6 characters long');
      return;
    }

    try {
      // Create user with Firebase
      const userCredential = await createUserWithEmailAndPassword(auth, email, password);
      const user = userCredential.user;

      // Get Firebase ID Token
      const idToken = await user.getIdToken();

      // Send user data to backend - PHP will handle Firestore sync with Admin SDK
      const userData = {
        idToken: idToken,
        firstName: firstName,
        lastName: lastName,
        email: email,
        uid: user.uid,
        password: password
      };

      fetch("register_user.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(userData)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Redirect to dashboard immediately - no alert
          window.location.href = "dashboard.php";
        } else {
          alert("Registration failed: " + data.error);
        }
      })
      .catch(error => {
        console.error('Backend error:', error);
        alert('Registration failed: ' + error.message);
      });

    } catch (error) {
      console.error('Firebase error:', error);
      alert("Registration failed: " + error.message);
    }
  });
</script>
</body>
</html>
