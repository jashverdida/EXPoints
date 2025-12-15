<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Ban Appeals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3d 50%, #0d1b3a 100%);
      min-height: 100vh;
      color: #f6f9ff;
    }
    .card-glass {
      background: linear-gradient(135deg, rgba(30, 58, 138, 0.2), rgba(37, 99, 235, 0.15));
      backdrop-filter: blur(15px);
      border: 2px solid rgba(59, 130, 246, 0.4);
      border-radius: 1rem;
    }
    .btn-back {
      background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      text-decoration: none;
    }
    .btn-back:hover {
      color: white;
      transform: translateY(-2px);
    }
    .user-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(110, 160, 255, 0.2);
      border-radius: 0.75rem;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .btn-unban {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
      <a href="{{ route('admin.dashboard') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
      <h1 class="mb-0" style="color: #ef4444;"><i class="bi bi-gavel"></i> Ban Appeals</h1>
    </div>

    <div class="card-glass p-4">
      @if(empty($bannedUsers) || count($bannedUsers) === 0)
        <div class="text-center py-5">
          <i class="bi bi-check-circle" style="font-size: 4rem; color: #22c55e;"></i>
          <p class="mt-3" style="font-size: 1.2rem;">No banned users at this time.</p>
        </div>
      @else
        <h5 class="mb-3">Banned Users ({{ count($bannedUsers) }})</h5>
        @foreach($bannedUsers as $user)
          <div class="user-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">{{ $user['username'] }}</h6>
                <small class="text-muted">{{ $user['email'] ?? 'N/A' }}</small>
                <p class="mb-1 mt-2"><strong>Reason:</strong> {{ $user['ban_reason'] ?? 'No reason provided' }}</p>
                <small class="text-muted">Banned: {{ $user['banned_at'] ?? 'Unknown' }}</small>
              </div>
              <form action="{{ route('admin.users.unban', $user['user_id']) }}" method="POST">
                @csrf
                <button type="submit" class="btn-unban">
                  <i class="bi bi-person-check"></i> Unban
                </button>
              </form>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
