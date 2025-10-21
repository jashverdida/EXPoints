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
  <style>
    /* Back Button for Register Page */
    .back-button-register {
      position: absolute;
      top: 2rem;
      right: 2rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      background: linear-gradient(135deg, rgba(56, 160, 255, 0.15), rgba(12, 31, 111, 0.1));
      backdrop-filter: blur(10px);
      border: 2px solid rgba(56, 160, 255, 0.3);
      border-radius: 50px;
      color: #0c1f6f;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      z-index: 10;
      box-shadow: 0 4px 15px rgba(56, 160, 255, 0.1);
    }

    .back-button-register:hover {
      background: linear-gradient(135deg, rgba(56, 160, 255, 0.25), rgba(12, 31, 111, 0.15));
      border-color: #38a0ff;
      color: #38a0ff;
      transform: translateX(5px);
      box-shadow: 0 6px 20px rgba(56, 160, 255, 0.3);
    }

    .back-button-register i {
      font-size: 1.1rem;
      transition: transform 0.3s ease;
      order: 2;
    }

    .back-button-register:hover i {
      transform: translateX(3px);
    }

    .back-button-register span {
      order: 1;
    }

    /* Responsive for back button */
    @media (max-width: 768px) {
      .back-button-register {
        top: 1.5rem;
        right: 1.5rem;
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
      }
    }

    @media (max-width: 576px) {
      .back-button-register {
        top: 1rem;
        right: 1rem;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
      }
      
      .back-button-register span {
        display: none;
      }
      
      .back-button-register i {
        margin: 0;
      }
    }
    
    /* Username Modal Styles */
    .username-modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(8, 18, 46, 0.95);
      backdrop-filter: blur(10px);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }
    
    .username-modal-backdrop.show {
      display: flex;
    }
    
    .username-modal {
      background: white;
      border-radius: 1.5rem;
      padding: 3rem;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
      animation: slideUp 0.4s ease;
      text-align: center;
    }
    
    .username-modal h3 {
      font-family: "Poppins", sans-serif;
      font-weight: 700;
      color: #0c1f6f;
      margin-bottom: 1rem;
      font-size: 1.75rem;
    }
    
    .username-modal p {
      color: #6c757d;
      margin-bottom: 2rem;
      font-size: 1rem;
    }
    
    .username-modal .form-control {
      padding: 0.875rem 1.25rem;
      border-radius: 2rem;
      border: 2px solid #e0e0e0;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    .username-modal .form-control:focus {
      border-color: #38a0ff;
      box-shadow: 0 0 0 0.2rem rgba(56, 160, 255, 0.25);
    }
    
    .username-modal .btn-complete {
      background: linear-gradient(135deg, #38a0ff 0%, #0c1f6f 100%);
      color: white;
      border: none;
      padding: 0.875rem 2rem;
      border-radius: 2rem;
      font-weight: 600;
      font-size: 1rem;
      margin-top: 1.5rem;
      transition: all 0.3s;
      width: 100%;
    }
    
    .username-modal .btn-complete:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(56, 160, 255, 0.4);
    }
    
    .username-modal .btn-complete:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .username-error {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.5rem;
      display: none;
    }
    
    .username-success {
      color: #28a745;
      font-size: 0.875rem;
      margin-top: 0.5rem;
      display: none;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .alert-custom {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 10000;
      min-width: 300px;
      animation: slideInRight 0.3s ease;
    }
    
    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
  </style>
</head>
<body>
  <div class="split-screen-container">
    <!-- LEFT SIDE - White Register Form -->
    <div class="left-side">
      <div class="register-form-container">
        <!-- Back button at top right -->
        <a href="../index.php" class="back-button-register">
          <span>Back to Home</span>
          <i class="bi bi-arrow-right"></i>
        </a>
        
        <!-- Logo -->
        <div class="logo-container">
          <img src="../assets/img/EXPoints Logo.png" alt="EXPoints Logo" class="top-logo">
        </div>

        <h2 class="register-title">Create Your Account</h2>
        <p class="register-subtitle">Join EXPoints and start discussing your favorite games!</p>

        <!-- Register Form -->
        <form id="registerForm" novalidate>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="firstName" class="form-label">First Name</label>
              <input id="firstName" name="firstName" class="form-control input-pill" placeholder="First Name" required>
            </div>
            <div class="col-md-6">
              <label for="middleName" class="form-label">Middle Name</label>
              <input id="middleName" name="middleName" class="form-control input-pill" placeholder="Middle Name (optional)">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="lastName" class="form-label">Last Name</label>
              <input id="lastName" name="lastName" class="form-control input-pill" placeholder="Last Name" required>
            </div>
            <div class="col-md-6">
              <label for="suffix" class="form-label">Suffix</label>
              <input id="suffix" name="suffix" class="form-control input-pill" placeholder="Suffix (optional)">
            </div>
          </div>

          <div class="mb-3">
            <label for="regEmail" class="form-label">Email</label>
            <input id="regEmail" name="email" type="email" class="form-control input-pill" placeholder="Enter your email" required>
          </div>

          <div class="mb-3">
            <label for="regPass" class="form-label">Create Password</label>
            <input id="regPass" name="password" type="password" class="form-control input-pill" placeholder="Enter password (min 6 characters)" required>
          </div>

          <div class="mb-4">
            <label for="regPass2" class="form-label">Confirm Password</label>
            <input id="regPass2" name="confirmPassword" type="password" class="form-control input-pill" placeholder="Re-enter password" required>
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

  <!-- Username Modal -->
  <div class="username-modal-backdrop" id="usernameModal">
    <div class="username-modal">
      <h3>Choose Your Username</h3>
      <p>Pick a unique username that other gamers will see</p>
      <input type="text" id="usernameInput" class="form-control" placeholder="Enter username" maxlength="20">
      <div class="username-error" id="usernameError"></div>
      <div class="username-success" id="usernameSuccess">✓ Username is available!</div>
      <button type="button" class="btn btn-complete" id="completeRegistration">
        Complete Registration
      </button>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Store form data temporarily
  let tempFormData = {};

  // Handle initial form submission
  document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Get form values
    const firstName = document.getElementById('firstName').value.trim();
    const middleName = document.getElementById('middleName').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    const suffix = document.getElementById('suffix').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPass').value.trim();
    const confirmPassword = document.getElementById('regPass2').value.trim();

    // Basic validation
    if (!firstName || !lastName || !email || !password) {
      showAlert('Please fill in all required fields', 'danger');
      return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showAlert('Please enter a valid email address', 'danger');
      return;
    }

    // Password validation
    if (password.length < 6) {
      showAlert('Password must be at least 6 characters long', 'danger');
      return;
    }

    if (password !== confirmPassword) {
      showAlert('Passwords do not match', 'danger');
      return;
    }

    // Store form data temporarily
    tempFormData = {
      firstName: firstName,
      middleName: middleName,
      lastName: lastName,
      suffix: suffix,
      email: email,
      password: password
    };

    // Show username modal
    document.getElementById('usernameModal').classList.add('show');
    document.getElementById('usernameInput').focus();
  });

  // Username input validation
  const usernameInput = document.getElementById('usernameInput');
  const usernameError = document.getElementById('usernameError');
  const usernameSuccess = document.getElementById('usernameSuccess');

  usernameInput.addEventListener('input', function() {
    const username = this.value.trim();
    
    if (username.length === 0) {
      usernameError.style.display = 'none';
      usernameSuccess.style.display = 'none';
      return;
    }

    // Validate username format (alphanumeric and underscore only, 3-20 chars)
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    
    if (!usernameRegex.test(username)) {
      usernameError.textContent = 'Username must be 3-20 characters (letters, numbers, underscore only)';
      usernameError.style.display = 'block';
      usernameSuccess.style.display = 'none';
    } else {
      usernameError.style.display = 'none';
      usernameSuccess.style.display = 'block';
    }
  });

  // Complete registration
  document.getElementById('completeRegistration').addEventListener('click', async function() {
    const username = usernameInput.value.trim();
    const btn = this;

    // Validate username
    if (username.length < 3 || username.length > 20) {
      showAlert('Username must be 3-20 characters', 'danger');
      return;
    }

    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    if (!usernameRegex.test(username)) {
      showAlert('Username can only contain letters, numbers, and underscores', 'danger');
      return;
    }

    // Disable button
    btn.disabled = true;
    btn.textContent = 'Creating account...';

    // Prepare registration data
    const registrationData = {
      ...tempFormData,
      username: username
    };

    try {
      // Send to backend
      const response = await fetch('../process_register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(registrationData)
      });

      const data = await response.json();

      if (data.success) {
        showAlert('Registration successful! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = 'dashboard.php';
        }, 1500);
      } else {
        btn.disabled = false;
        btn.textContent = 'Complete Registration';
        showAlert(data.error || 'Registration failed', 'danger');
        
        // If username is taken, keep modal open
        if (data.error && data.error.toLowerCase().includes('username')) {
          usernameError.textContent = data.error;
          usernameError.style.display = 'block';
          usernameSuccess.style.display = 'none';
        } else {
          // Close modal for other errors
          document.getElementById('usernameModal').classList.remove('show');
        }
      }
    } catch (error) {
      btn.disabled = false;
      btn.textContent = 'Complete Registration';
      showAlert('Registration failed: ' + error.message, 'danger');
      console.error('Error:', error);
    }
  });

  // Helper function to show alerts
  function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
      alertDiv.remove();
    }, 5000);
  }

  // Allow Enter key to submit username
  usernameInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      document.getElementById('completeRegistration').click();
    }
  });
</script>
</body>
</html>
