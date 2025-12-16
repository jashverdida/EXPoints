<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Manage Users</title>
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
    }

    .user-card {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.25), rgba(37, 99, 235, 0.2));
      backdrop-filter: blur(10px);
      border: 2px solid rgba(59, 130, 246, 0.3);
      border-radius: 1rem;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
    }

    .user-card:hover {
      transform: translateY(-3px);
      border-color: rgba(59, 130, 246, 0.6);
      box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
    }

    .user-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(59, 130, 246, 0.5);
    }

    .badge-banned {
      background: linear-gradient(135deg, #dc2626, #991b1b);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
    }

    .badge-disabled {
      background: linear-gradient(135deg, #ea580c, #c2410c);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
    }

    .badge-role {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      text-transform: uppercase;
    }

    .action-btn {
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      border: none;
      font-weight: 600;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .btn-ban {
      background: linear-gradient(135deg, #dc2626, #991b1b);
      color: white;
    }

    .btn-ban:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(220, 38, 38, 0.5);
    }

    .btn-unban {
      background: linear-gradient(135deg, #16a34a, #15803d);
      color: white;
    }

    .btn-unban:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(22, 163, 74, 0.5);
    }

    .btn-disable {
      background: linear-gradient(135deg, #ea580c, #c2410c);
      color: white;
    }

    .btn-enable {
      background: linear-gradient(135deg, #0891b2, #0e7490);
      color: white;
    }

    .search-box {
      background: rgba(30, 58, 138, 0.3);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
      padding: 0.75rem 1.5rem;
      color: white;
      width: 100%;
      max-width: 500px;
    }

    .search-box:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.8);
      box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }

    /* Modal Styles */
    .modal-content {
      background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3d 50%, #0d1b3a 100%);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
      color: #f6f9ff;
    }

    .modal-header {
      border-bottom: 1px solid rgba(59, 130, 246, 0.3);
      padding: 1.5rem;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-footer {
      border-top: 1px solid rgba(59, 130, 246, 0.3);
      padding: 1rem 1.5rem;
    }

    .modal-title {
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-success .modal-header {
      border-color: rgba(22, 163, 74, 0.5);
    }

    .modal-success .modal-title {
      color: #22c55e;
    }

    .modal-error .modal-header {
      border-color: rgba(220, 38, 38, 0.5);
    }

    .modal-error .modal-title {
      color: #ef4444;
    }

    .modal-warning .modal-header {
      border-color: rgba(234, 88, 12, 0.5);
    }

    .modal-warning .modal-title {
      color: #f97316;
    }

    .modal-input {
      background: rgba(30, 58, 138, 0.3);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 0.5rem;
      color: white;
      padding: 0.75rem 1rem;
      width: 100%;
    }

    .modal-input:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.8);
      box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
    }

    .btn-modal-primary {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      color: white;
    }

    .btn-modal-primary:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }

    .btn-modal-danger {
      background: linear-gradient(135deg, #dc2626, #991b1b);
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      color: white;
    }

    .btn-modal-danger:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
    }

    .btn-modal-success {
      background: linear-gradient(135deg, #16a34a, #15803d);
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      color: white;
    }

    .btn-modal-success:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 15px rgba(22, 163, 74, 0.4);
    }

    .btn-modal-secondary {
      background: rgba(100, 116, 139, 0.5);
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      color: white;
    }

    .btn-modal-secondary:hover {
      background: rgba(100, 116, 139, 0.7);
    }
  </style>
</head>
<body>
  <div class="container-fluid py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="text-white mb-0"><i class="bi bi-people-fill"></i> Manage Users</h1>
        <p class="text-white-50 mb-0">Total users: {{ count($users) }}</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
      <form action="{{ route('admin.users') }}" method="GET">
        <div class="d-flex gap-2">
          <input type="text" name="search" class="search-box" placeholder="Search by email or username..." value="{{ $search ?? '' }}">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Search
          </button>
          @if(!empty($search))
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Clear</a>
          @endif
        </div>
      </form>
    </div>

    <!-- Users List -->
    @if(isset($error))
      <div class="alert alert-danger">{{ $error }}</div>
    @endif

    @if(empty($users))
      <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.5;"></i>
        <p class="text-white-50 mt-3">No users found</p>
      </div>
    @else
      @foreach($users as $user)
        <div class="user-card">
          <div class="row align-items-center">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-3">
                <img src="{{ $user['profile_picture'] ?? '/assets/img/cat1.jpg' }}" alt="Avatar" class="user-avatar">
                <div>
                  <h5 class="mb-0 text-white">{{ $user['username'] ?? $user['email'] }}</h5>
                  <p class="mb-0 text-white-50 small">{{ $user['email'] }}</p>
                  <p class="mb-0 text-white-50 small">ID: {{ $user['id'] }} • Joined: {{ \Carbon\Carbon::parse($user['created_at'])->format('M d, Y') }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge-role">{{ $user['role'] ?? 'user' }}</span>
                @if($user['is_banned'] ?? false)
                  <span class="badge-banned"><i class="bi bi-ban"></i> Banned</span>
                @endif
                @if($user['is_disabled'] ?? false)
                  <span class="badge-disabled"><i class="bi bi-slash-circle"></i> Disabled</span>
                @endif
              </div>
            </div>
            <div class="col-md-3 text-end">
              <div class="d-flex gap-2 justify-content-end flex-wrap">
                @if($user['is_banned'] ?? false)
                  <button type="button" class="action-btn btn-unban" onclick="unbanUser({{ $user['id'] }}, '{{ addslashes($user['username'] ?? $user['email']) }}')">
                    <i class="bi bi-check-circle"></i> Unban
                  </button>
                @else
                  <button type="button" class="action-btn btn-ban" onclick="banUser({{ $user['id'] }}, '{{ addslashes($user['username'] ?? $user['email']) }}')">
                    <i class="bi bi-ban"></i> Ban
                  </button>
                @endif

                @if($user['is_disabled'] ?? false)
                  <button type="button" class="action-btn btn-enable" onclick="enableUser({{ $user['id'] }}, '{{ addslashes($user['username'] ?? $user['email']) }}')">
                    <i class="bi bi-check-circle"></i> Enable
                  </button>
                @else
                  <button type="button" class="action-btn btn-disable" onclick="disableUser({{ $user['id'] }}, '{{ addslashes($user['username'] ?? $user['email']) }}')">
                    <i class="bi bi-slash-circle"></i> Disable
                  </button>
                @endif
              </div>
            </div>
          </div>
          
          @if($user['is_banned'] ?? false)
            <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded">
              <small class="text-danger"><strong>Ban Reason:</strong> {{ $user['ban_reason'] ?? 'No reason provided' }}</small>
            </div>
          @endif
        </div>
      @endforeach
    @endif
  </div>

  <!-- Alert Modal -->
  <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" id="alertModalContent">
        <div class="modal-header">
          <h5 class="modal-title" id="alertModalTitle">
            <i class="bi bi-info-circle" id="alertModalIcon"></i>
            <span id="alertModalTitleText">Alert</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="alertModalMessage" class="mb-0"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-primary" data-bs-dismiss="modal" id="alertModalOkBtn">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Modal -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-warning">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-triangle"></i>
            <span id="confirmModalTitle">Confirm</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="confirmModalMessage" class="mb-0"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn-modal-danger" id="confirmModalYesBtn">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Prompt Modal -->
  <div class="modal fade" id="promptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-pencil-square"></i>
            <span id="promptModalTitle">Enter Information</span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="promptModalMessage" class="mb-3"></p>
          <input type="text" class="modal-input" id="promptModalInput" placeholder="Enter reason...">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn-modal-primary" id="promptModalSubmitBtn">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Modal instances
    let alertModalInstance = null;
    let confirmModalInstance = null;
    let promptModalInstance = null;
    let alertCallback = null;
    let confirmCallback = null;
    let promptCallback = null;

    document.addEventListener('DOMContentLoaded', function() {
      alertModalInstance = new bootstrap.Modal(document.getElementById('alertModal'));
      confirmModalInstance = new bootstrap.Modal(document.getElementById('confirmModal'));
      promptModalInstance = new bootstrap.Modal(document.getElementById('promptModal'));

      // Alert modal OK button
      document.getElementById('alertModalOkBtn').addEventListener('click', function() {
        alertModalInstance.hide();
        if (alertCallback) {
          alertCallback();
          alertCallback = null;
        }
      });

      // Confirm modal Yes button
      document.getElementById('confirmModalYesBtn').addEventListener('click', function() {
        confirmModalInstance.hide();
        if (confirmCallback) {
          confirmCallback();
          confirmCallback = null;
        }
      });

      // Prompt modal Submit button
      document.getElementById('promptModalSubmitBtn').addEventListener('click', function() {
        const value = document.getElementById('promptModalInput').value.trim();
        if (value) {
          promptModalInstance.hide();
          if (promptCallback) {
            promptCallback(value);
            promptCallback = null;
          }
        }
      });

      // Enter key for prompt
      document.getElementById('promptModalInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
          document.getElementById('promptModalSubmitBtn').click();
        }
      });
    });

    // Show alert modal
    function showAlert(type, title, message, callback = null) {
      const content = document.getElementById('alertModalContent');
      const icon = document.getElementById('alertModalIcon');
      const titleText = document.getElementById('alertModalTitleText');
      const messageEl = document.getElementById('alertModalMessage');

      // Reset classes
      content.classList.remove('modal-success', 'modal-error', 'modal-warning');

      if (type === 'success') {
        content.classList.add('modal-success');
        icon.className = 'bi bi-check-circle-fill';
      } else if (type === 'error') {
        content.classList.add('modal-error');
        icon.className = 'bi bi-x-circle-fill';
      } else if (type === 'warning') {
        content.classList.add('modal-warning');
        icon.className = 'bi bi-exclamation-triangle-fill';
      } else {
        icon.className = 'bi bi-info-circle-fill';
      }

      titleText.textContent = title;
      messageEl.textContent = message;
      alertCallback = callback;
      alertModalInstance.show();
    }

    // Show confirm modal
    function showConfirm(title, message, callback) {
      document.getElementById('confirmModalTitle').textContent = title;
      document.getElementById('confirmModalMessage').textContent = message;
      confirmCallback = callback;
      confirmModalInstance.show();
    }

    // Show prompt modal
    function showPrompt(title, message, callback) {
      document.getElementById('promptModalTitle').textContent = title;
      document.getElementById('promptModalMessage').textContent = message;
      document.getElementById('promptModalInput').value = '';
      promptCallback = callback;
      promptModalInstance.show();
      setTimeout(() => document.getElementById('promptModalInput').focus(), 300);
    }

    // Ban user
    function banUser(userId, username) {
      showPrompt('Ban User', `Enter ban reason for ${username}:`, function(reason) {
        fetch(`/admin/users/${userId}/ban`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'User Banned', `${username} has been banned successfully.`, function() {
              location.reload();
            });
          } else {
            showAlert('error', 'Ban Failed', data.error || 'Failed to ban user');
          }
        })
        .catch(error => {
          showAlert('error', 'Error', error.message);
        });
      });
    }

    // Unban user
    function unbanUser(userId, username) {
      showConfirm('Unban User', `Are you sure you want to unban ${username}?`, function() {
        fetch(`/admin/users/${userId}/unban`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'User Unbanned', `${username} has been unbanned successfully.`, function() {
              location.reload();
            });
          } else {
            showAlert('error', 'Unban Failed', data.error || 'Failed to unban user');
          }
        })
        .catch(error => {
          showAlert('error', 'Error', error.message);
        });
      });
    }

    // Disable user
    function disableUser(userId, username) {
      showPrompt('Disable Account', `Enter disable reason for ${username}:`, function(reason) {
        fetch(`/admin/users/${userId}/disable`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Account Disabled', `${username}'s account has been disabled.`, function() {
              location.reload();
            });
          } else {
            showAlert('error', 'Disable Failed', data.error || 'Failed to disable account');
          }
        })
        .catch(error => {
          showAlert('error', 'Error', error.message);
        });
      });
    }

    // Enable user
    function enableUser(userId, username) {
      showConfirm('Enable Account', `Are you sure you want to enable ${username}'s account?`, function() {
        fetch(`/admin/users/${userId}/enable`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Account Enabled', `${username}'s account has been enabled.`, function() {
              location.reload();
            });
          } else {
            showAlert('error', 'Enable Failed', data.error || 'Failed to enable account');
          }
        })
        .catch(error => {
          showAlert('error', 'Error', error.message);
        });
      });
    }
  </script>
</body>
</html>
