<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Admin Moderators</title>
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
        <input type="text" placeholder="Search moderators" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>
      <div class="right">
        <a href="index.php" class="icon" title="Dashboard"><i class="bi bi-speedometer"></i></a>
        <a href="reporting.php" class="icon" title="Reporting"><i class="bi bi-flag"></i></a>
        <a class="icon" title="Moderators"><i class="bi bi-people-fill"></i></a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <section class="admin-card">
      <h2 class="section-title">Moderators</h2>
      <form class="row g-2 add-mod">
        <div class="col-12 col-md-5">
          <input type="text" class="form-control input-pill" placeholder="@username" required />
        </div>
        <div class="col-12 col-md-5">
          <input type="email" class="form-control input-pill" placeholder="email@example.com" required />
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button class="btn btn-primary btn-brand" type="submit"><i class="bi bi-person-plus"></i> Add</button>
        </div>
      </form>

      <div class="table-responsive mt-3">
        <table class="table table-dark table-striped align-middle mb-0 admin-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Since</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>@mod-kai</td>
              <td>kai@example.com</td>
              <td>2024-06-01</td>
              <td class="text-end"><button class="btn btn-sm btn-outline-light"><i class="bi bi-person-dash"></i> Remove</button></td>
            </tr>
            <tr>
              <td>@mod-rin</td>
              <td>rin@example.com</td>
              <td>2024-07-21</td>
              <td class="text-end"><button class="btn btn-sm btn-outline-light"><i class="bi bi-person-dash"></i> Remove</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script>
    document.querySelector('.add-mod')?.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('Moderator added (demo).');
      e.target.reset();
    });
  </script>

</body>
</html>



