<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Admin Reporting</title>
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
        <input type="text" placeholder="Search reports by user, post, reason" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>
      <div class="right">
        <a href="index.php" class="icon" title="Dashboard"><i class="bi bi-speedometer"></i></a>
        <a class="icon" title="Reporting"><i class="bi bi-flag-fill"></i></a>
        <a href="moderators.php" class="icon" title="Moderators"><i class="bi bi-people"></i></a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <section class="admin-card">
      <h2 class="section-title">Open Reports</h2>

      <article class="card-post report">
        <div class="row gap-3 align-items-start">
          <div class="col-auto"><div class="avatar-lg"></div></div>
          <div class="col">
            <h3 class="title mb-1">Post #421 • Suspected Spam</h3>
            <div class="handle mb-3">by @spammy-user</div>
            <p class="mb-3">Buy cheap coins at spam.example.com...</p>
          </div>
        </div>
        <div class="actions">
          <span class="a"><i class="bi bi-flag"></i><b>5</b></span>
          <button class="icon more" aria-label="More" data-menu="menu-421"><i class="bi bi-three-dots-vertical"></i></button>
        </div>
        <div class="admin-menu" id="menu-421">
          <button data-action="remove" data-post="421"><i class="bi bi-trash"></i> Remove post</button>
          <button data-action="ban" data-user="spammy-user"><i class="bi bi-person-x"></i> Ban user</button>
        </div>
      </article>

      <article class="card-post report">
        <div class="row gap-3 align-items-start">
          <div class="col-auto"><div class="avatar-lg"></div></div>
          <div class="col">
            <h3 class="title mb-1">Post #389 • Harassment</h3>
            <div class="handle mb-3">by @toxic-dude</div>
            <p class="mb-3">…</p>
          </div>
        </div>
        <div class="actions">
          <span class="a"><i class="bi bi-flag"></i><b>3</b></span>
          <button class="icon more" aria-label="More" data-menu="menu-389"><i class="bi bi-three-dots-vertical"></i></button>
        </div>
        <div class="admin-menu" id="menu-389">
          <button data-action="remove" data-post="389"><i class="bi bi-trash"></i> Remove post</button>
          <button data-action="ban" data-user="toxic-dude"><i class="bi bi-person-x"></i> Ban user</button>
        </div>
      </article>

    </section>
  </main>

  <script>
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.more');
      if(btn){
        e.stopPropagation();
        const id = btn.getAttribute('data-menu');
        document.querySelectorAll('.admin-menu').forEach(m => m.classList.remove('show'));
        document.getElementById(id)?.classList.add('show');
      } else if(!e.target.closest('.admin-menu')){
        document.querySelectorAll('.admin-menu').forEach(m => m.classList.remove('show'));
      }

      const actionBtn = e.target.closest('.admin-menu button');
      if(actionBtn){
        const action = actionBtn.getAttribute('data-action');
        const post = actionBtn.getAttribute('data-post');
        const user = actionBtn.getAttribute('data-user');
        if(action === 'remove'){
          alert(`Post #${post} removed (demo).`);
        } else if(action === 'ban'){
          alert(`User @${user} banned (demo).`);
        }
        document.querySelectorAll('.admin-menu').forEach(m => m.classList.remove('show'));
      }
    });
  </script>

</body>
</html>



