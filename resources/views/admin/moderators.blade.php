<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Manage Administrators</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap");

    body {
      font-family: "Poppins", sans-serif !important;
      background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3d 50%, #0d1b3a 100%);
      min-height: 100vh;
      color: #f6f9ff;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background:
        radial-gradient(circle at 20% 30%, rgba(220, 38, 38, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(239, 68, 68, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(248, 113, 113, 0.2) 0%, transparent 60%);
      animation: floatGlow 15s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }

    @keyframes floatGlow {
      0%, 100% {
        transform: translate(0, 0) scale(1) rotate(0deg);
        opacity: 1;
      }
      50% {
        transform: translate(-3%, -3%) scale(1.1) rotate(2deg);
        opacity: 0.85;
      }
    }

    * {
      position: relative;
      z-index: 1;
    }

    .admin-header {
      background: linear-gradient(135deg, rgba(220, 38, 38, 0.3), rgba(153, 27, 27, 0.25));
      backdrop-filter: blur(20px);
      border: 2px solid rgba(220, 38, 38, 0.5);
      border-radius: 1.5rem;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 15px 50px rgba(220, 38, 38, 0.4),
                  0 0 40px rgba(220, 38, 38, 0.2) inset;
    }

    .create-admin-card {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.35), rgba(37, 99, 235, 0.3));
      backdrop-filter: blur(15px);
      border: 3px solid rgba(59, 130, 246, 0.5);
      border-radius: 1.5rem;
      padding: 2.5rem;
      margin-bottom: 2.5rem;
      box-shadow: 0 20px 60px rgba(59, 130, 246, 0.5),
                  0 0 50px rgba(59, 130, 246, 0.15) inset;
      transition: all 0.4s ease;
    }

    .create-admin-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 80px rgba(59, 130, 246, 0.7),
                  0 0 60px rgba(59, 130, 246, 0.25) inset;
      border-color: rgba(59, 130, 246, 0.8);
    }

    .admin-card {
      background: linear-gradient(135deg, rgba(220, 38, 38, 0.25), rgba(153, 27, 27, 0.2));
      backdrop-filter: blur(15px);
      border: 3px solid rgba(220, 38, 38, 0.4);
      border-radius: 1.5rem;
      padding: 2rem;
      margin-bottom: 1.5rem;
      transition: all 0.4s ease;
      box-shadow: 0 15px 50px rgba(220, 38, 38, 0.4);
    }

    .admin-card:hover {
      transform: translateY(-8px) scale(1.02);
      border-color: rgba(220, 38, 38, 0.8);
      box-shadow: 0 25px 70px rgba(220, 38, 38, 0.7),
                  0 0 50px rgba(220, 38, 38, 0.3) inset;
    }

    .admin-card.disabled {
      opacity: 0.6;
      border-color: rgba(234, 88, 12, 0.5);
      background: linear-gradient(135deg, rgba(234, 88, 12, 0.2), rgba(194, 65, 12, 0.15));
    }

    .admin-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid rgba(220, 38, 38, 0.7);
      box-shadow: 0 8px 25px rgba(220, 38, 38, 0.6);
      transition: all 0.3s ease;
    }

    .admin-avatar:hover {
      transform: scale(1.1) rotate(5deg);
      border-color: rgba(248, 113, 113, 1);
    }

    .badge-admin {
      background: linear-gradient(135deg, #dc2626, #7f1d1d);
      color: white;
      padding: 0.5rem 1.5rem;
      border-radius: 2rem;
      font-size: 1rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 8px 20px rgba(220, 38, 38, 0.5);
    }

    .badge-disabled {
      background: linear-gradient(135deg, #ea580c, #9a3412);
      box-shadow: 0 8px 20px rgba(234, 88, 12, 0.5);
    }

    .form-control, .form-select {
      background: rgba(30, 58, 138, 0.3);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
      color: white;
      padding: 0.875rem 1.25rem;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
      outline: none;
      background: rgba(30, 58, 138, 0.5);
      border-color: rgba(59, 130, 246, 0.9);
      box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
      color: white;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }

    .btn-create-admin {
      background: linear-gradient(135deg, #16a34a, #15803d, #166534);
      color: white;
      border: none;
      padding: 1rem 2.5rem;
      border-radius: 1rem;
      font-weight: 700;
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: all 0.4s ease;
      box-shadow: 0 10px 30px rgba(22, 163, 74, 0.5);
    }

    .btn-create-admin:hover {
      transform: scale(1.08) translateY(-3px);
      box-shadow: 0 15px 40px rgba(22, 163, 74, 0.8);
      background: linear-gradient(135deg, #22c55e, #16a34a, #15803d);
    }

    .btn-disable {
      background: linear-gradient(135deg, #ea580c, #c2410c);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(234, 88, 12, 0.4);
    }

    .btn-disable:hover {
      transform: scale(1.1);
      box-shadow: 0 12px 30px rgba(234, 88, 12, 0.7);
    }

    .btn-enable {
      background: linear-gradient(135deg, #0891b2, #0e7490);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(8, 145, 178, 0.4);
    }

    .btn-enable:hover {
      transform: scale(1.1);
      box-shadow: 0 12px 30px rgba(8, 145, 178, 0.7);
    }

    h1 {
      font-size: 3rem;
      font-weight: 900;
      text-shadow: 0 8px 30px rgba(220, 38, 38, 0.8);
    }

    h4 {
      font-size: 1.8rem;
      font-weight: 700;
      text-shadow: 0 4px 20px rgba(59, 130, 246, 0.6);
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <!-- Header -->
    <div class="admin-header text-center">
      <h1 class="text-white mb-2">
        <i class="bi bi-shield-fill-exclamation"></i> 
        Administrator Management
      </h1>
      <p class="text-white-50 mb-3 fs-5">Total Administrators: <strong class="text-danger">{{ count($moderators) }}</strong></p>
      <div class="d-flex gap-3 justify-content-center">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-lg">
          <i class="bi bi-arrow-left-circle-fill"></i> Dashboard
        </a>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-lg">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>

    <!-- Create Admin Section -->
    <div class="create-admin-card">
      <h4 class="text-white mb-4 text-center">
        <i class="bi bi-person-plus-fill"></i> Create New Administrator
      </h4>
      <form id="createAdminForm" class="row g-4">
        @csrf
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Email Address</label>
          <input type="email" class="form-control" id="adminEmail" placeholder="admin@expoints.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Username</label>
          <input type="text" class="form-control" id="adminUsername" placeholder="admin_username" required>
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Password</label>
          <input type="password" class="form-control" id="adminPassword" placeholder="Enter strong password" required minlength="8">
        </div>
        <div class="col-md-6">
          <label class="form-label text-white fw-bold">Confirm Password</label>
          <input type="password" class="form-control" id="adminPasswordConfirm" placeholder="Confirm password" required minlength="8">
        </div>
        <div class="col-12 text-center mt-4">
          <button type="submit" class="btn-create-admin">
            <i class="bi bi-shield-fill-plus"></i> Create Administrator
          </button>
        </div>
      </form>
    </div>

    <!-- Error Message -->
    @if(isset($error))
      <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <!-- Admins List -->
    @if(empty($moderators))
      <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 5rem; opacity: 0.3;"></i>
        <p class="text-white-50 mt-4 fs-4">No administrators found</p>
      </div>
    @else
      <h4 class="text-white mb-4 text-center">
        <i class="bi bi-people-fill"></i> Current Administrators
      </h4>
      @foreach($moderators as $admin)
        <div class="admin-card {{ ($admin['is_disabled'] ?? false) ? 'disabled' : '' }}">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-4">
                <img src="{{ $admin['profile_picture'] ?? '/assets/img/cat1.jpg' }}" alt="Avatar" class="admin-avatar">
                <div>
                  <h3 class="mb-1 text-white fw-bold">{{ $admin['username'] ?? $admin['email'] }}</h3>
                  <p class="mb-1 text-white-50">{{ $admin['email'] }}</p>
                  <p class="mb-0 text-white-50 small">ID: {{ $admin['id'] }} • Created: {{ \Carbon\Carbon::parse($admin['created_at'])->format('M d, Y') }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-2 text-center">
              @if($admin['is_disabled'] ?? false)
                <span class="badge-admin badge-disabled">
                  <i class="bi bi-slash-circle-fill"></i> DISABLED
                </span>
              @else
                <span class="badge-admin">
                  <i class="bi bi-shield-fill-check"></i> ACTIVE
                </span>
              @endif
            </div>
            <div class="col-md-3 text-end">
              @if($admin['is_disabled'] ?? false)
                <button type="button" class="btn-enable" onclick="enableAdmin({{ $admin['id'] }}, '{{ $admin['username'] ?? $admin['email'] }}')">
                  <i class="bi bi-check-circle-fill"></i> Enable
                </button>
              @else
                <button type="button" class="btn-disable" onclick="disableAdmin({{ $admin['id'] }}, '{{ $admin['username'] ?? $admin['email'] }}')">
                  <i class="bi bi-slash-circle-fill"></i> Disable
                </button>
              @endif
            </div>
          </div>
          
          @if($admin['is_disabled'] ?? false)
            <div class="mt-3 p-3 bg-danger bg-opacity-25 border border-danger rounded-3">
              <p class="mb-0 text-danger fw-bold">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Disabled Reason: {{ $admin['disabled_reason'] ?? 'No reason provided' }}
              </p>
            </div>
          @endif
        </div>
      @endforeach
    @endif
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Create new admin
    document.getElementById('createAdminForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const email = document.getElementById('adminEmail').value;
      const username = document.getElementById('adminUsername').value;
      const password = document.getElementById('adminPassword').value;
      const passwordConfirm = document.getElementById('adminPasswordConfirm').value;
      
      if (password !== passwordConfirm) {
        alert('Passwords do not match!');
        return;
      }

      fetch('/admin/create-admin', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          email,
          username,
          password
        })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(err => Promise.reject(err));
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          alert('Admin created successfully!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to create admin'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + (error.error || error.message || 'Failed to create admin'));
      });
    });

    function disableAdmin(userId, username) {
      const reason = prompt(`Enter reason for disabling ${username}:`);
      if (!reason) return;

      fetch(`/admin/users/${userId}/disable`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ reason })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Administrator disabled successfully!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to disable admin'));
        }
      })
      .catch(error => {
        alert('Error: ' + error.message);
      });
    }

    function enableAdmin(userId, username) {
      if (!confirm(`Enable ${username}?`)) return;

      fetch(`/admin/users/${userId}/enable`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Administrator enabled successfully!');
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Failed to enable admin'));
        }
      })
      .catch(error => {
        alert('Error: ' + error.message);
      });
    }
  </script>
</body>
</html>