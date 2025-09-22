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
  <link rel="stylesheet" href="/EXPoints/assets/css/register.css" />
</head>
<body>
<main class="container py-5 app-container">
  <h1 class="auth-hero text-center mb-4">Register! And Discuss your favorite Games!</h1>

  <section class="auth-wrap">
    <div class="auth-card p-4 p-md-5">
      <!-- Header -->
      <div class="auth-head mb-3 position-relative">
        <!-- Back button -->
        <a class="btn btn-back" href="index.php" aria-label="Back">
          <i class="bi bi-chevron-left"></i>
        </a>

        <!-- Centered logo -->
        <div class="auth-logo">
          <img src="/EXPoints/assets/img/EXPoints Logo.png" alt="EXPoints Logo">
        </div>

        <!-- Panda mascot -->
        <img class="auth-panda-register" src="/EXPoints/assets/img/registerpanda.png" alt="Register Panda">
      </div>

      <!-- Register Form -->
      <form action="dashboard.php" method="get" novalidate>
        <div class="row g-2 align-items-center mb-3">
          <label for="firstName" class="col-12 col-md-5 col-form-label field-label">First Name</label>
          <div class="col-12 col-md-7">
            <input id="firstName" class="form-control input-pill" placeholder="Enter First Name..." required>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
          <label for="middleName" class="col-12 col-md-5 col-form-label field-label">Middle Name</label>
          <div class="col-12 col-md-7">
            <input id="middleName" class="form-control input-pill" placeholder="Enter Middle Name...">
          </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
          <label for="lastName" class="col-12 col-md-5 col-form-label field-label">Last Name</label>
          <div class="col-12 col-md-7">
            <input id="lastName" class="form-control input-pill" placeholder="Enter Last Name..." required>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
          <label for="suffix" class="col-12 col-md-5 col-form-label field-label">Suffix</label>
          <div class="col-12 col-md-7">
            <input id="suffix" class="form-control input-pill" placeholder="Enter Suffix (optional)">
          </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
          <label for="regEmail" class="col-12 col-md-5 col-form-label field-label">Email or Phone No.</label>
          <div class="col-12 col-md-7">
            <input id="regEmail" type="email" class="form-control input-pill" placeholder="Enter Valid Email or Phone No." required>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
          <label for="regPass" class="col-12 col-md-5 col-form-label field-label">Create Password</label>
          <div class="col-12 col-md-7">
            <input id="regPass" type="password" class="form-control input-pill" placeholder="Enter Password" required>
          </div>
        </div>

        <div class="row g-2 align-items-center mb-4">
          <label for="regPass2" class="col-12 col-md-5 col-form-label field-label">Confirm Password</label>
          <div class="col-12 col-md-7">
            <input id="regPass2" type="password" class="form-control input-pill" placeholder="Re-enter Password" required>
          </div>
        </div>

        <!-- CONTINUE button -->
        <button type="submit" class="btn btn-brand w-100 mb-3">CONTINUE</button>

        <div class="auth-divider my-3">OR</div>

        <button type="button" class="btn btn-google w-100">
          <span class="g-logo">G</span> 
          Register with Google
        </button>
      </form>
    </div>
  </section>
</main>

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
