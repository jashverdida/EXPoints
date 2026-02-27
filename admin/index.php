<?php
session_start();
$isLoggedIn = isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Admin') : 'Admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../Assets/css/index.css">
  <link rel="stylesheet" href="../Assets/css/admin.css">
</head>
<body>

  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="../dashboard.php" class="lp-brand" aria-label="Dashboard">
        <img src="../Assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>
      <form class="search" role="search">
        <input type="text" placeholder="Search users, posts, reports" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>
      <div class="right">
        <a href="index.php" class="icon" title="Dashboard"><i class="bi bi-speedometer"></i></a>
        <a href="reporting.php" class="icon" title="Reporting"><i class="bi bi-flag"></i></a>
        <a href="moderators.php" class="icon" title="Moderators"><i class="bi bi-people"></i></a>
        <div class="avatar-nav" title="<?php echo htmlspecialchars($userName); ?>"></div>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <div class="admin-grid">
      <section class="admin-card">
        <h2 class="section-title">Overview</h2>
        <div class="metrics">
          <div class="metric"><span class="m-num">1,248</span><span class="m-label">Active Users</span></div>
          <div class="metric"><span class="m-num">3,502</span><span class="m-label">Posts</span></div>
          <div class="metric"><span class="m-num">82</span><span class="m-label">Open Reports</span></div>
          <div class="metric"><span class="m-num">14</span><span class="m-label">Banned</span></div>
        </div>
      </section>

      <section class="admin-card">
        <h2 class="section-title">Recent Activity</h2>
        <ul class="activity">
          <li><i class="bi bi-person-check"></i> New user verified: <b>@nova</b></li>
          <li><i class="bi bi-flag"></i> Report opened on post <b>#421</b></li>
          <li><i class="bi bi-shield-check"></i> Moderator <b>@mod-kai</b> resolved 3 reports</li>
        </ul>
      </section>

      <section class="admin-card">
        <h2 class="section-title">Quick Links</h2>
        <div class="links">
          <a class="quick" href="reporting.php"><i class="bi bi-flag"></i> Review Reports</a>
          <a class="quick" href="moderators.php"><i class="bi bi-person-plus"></i> Add Moderator</a>
          <a class="quick" href="../dashboard.php"><i class="bi bi-compass"></i> View Feed</a>
        </div>
      </section>
    </div>
  </main>

</body>
</html>




