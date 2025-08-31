<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Home</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

  <!-- Top bar -->
  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="index.html" class="lp-brand" aria-label="+EXPoints home">
        <img src="assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>

      <form class="search" role="search">
        <input type="text" placeholder="Search for a Review, a Game, Anything" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>

      <div class="right">
        <button class="icon" title="Filter"><i class="bi bi-funnel"></i></button>
        <div class="settings-dropdown">
          <button class="icon settings-btn" title="Settings"><i class="bi bi-gear"></i></button>
          <div class="dropdown-menu">
            <button class="dropdown-item logout-btn">
              <i class="bi bi-box-arrow-right"></i>
              Logout
            </button>
          </div>
        </div>
        <button class="icon" title="Notifications"><i class="bi bi-bell"></i></button>
        <div class="avatar-nav"></div>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <!-- Post -->
    <article class="card-post">
      <div class="row gap-3 align-items-start">
        <div class="col-auto"><div class="avatar-lg"></div></div>
        <div class="col">
          <h2 class="title mb-1">Elden Ring Shadow of The Erdtree is BAD</h2>
          <div class="handle mb-3">@BethesdaFan321</div>
          <p class="mb-3">I give this DLC a 7/10. Quantity doesn’t mean quality and I think many people highly rated the DLC just because of the amount of additional ER content.</p>
          <p class="mb-0">Even if I'm more positive than you, I quite felt the same way. My biggest disappointment regarding the build up of some bosses was the pink boss (I forgot her name). I loved that boss and overall I think most major bosses are spectacular, but she appeared and disappeared randomly within a region mostly unrelated to her. I don’t think she even talked during the fight.</p>
        </div>
      </div>
      <div class="actions">
        <span class="a"><i class="bi bi-star"></i><b>50</b></span>
        <span class="a"><i class="bi bi-chat-left-text"></i><b>4</b></span>
        <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
      </div>
    </article>

    <!-- Reply -->
    <article class="card-reply">
      <div class="row g-3 align-items-center">
        <div class="col-auto"><div class="avatar-sm"></div></div>
        <div class="col">
          <div class="author fw-semibold">Kenji Parilla</div>
          <div class="reply">Sounds like a skill issue ngl</div>
        </div>
        <div class="col-auto">
          <div class="actions">
            <span class="a"><i class="bi bi-star"></i><b>4</b></span>
            <span class="a"><i class="bi bi-chat-left-text"></i></span>
            <button class="icon more" aria-label="More"><i class="bi bi-three-dots-vertical"></i></button>
          </div>
        </div>
      </div>
    </article>

    <!-- Comment -->
    <section class="card-input">
      <div class="row g-3 align-items-center">
        <div class="col-auto"><div class="avatar-us"></div></div>
        <div class="col">
          <input class="comment" placeholder="Write a Comment on this post!" />
        </div>
      </div>
    </section>
  </main>

  <!-- Slide-in sidebar (inside the body) -->
  <aside class="side">
    <span class="side-hotspot"></span>
    <div class="side-inner">
      <div class="side-box">
        <button class="side-btn" title="Home"><i class="bi bi-house"></i></button>
        <button class="side-btn" title="Bookmarks"><i class="bi bi-bookmark"></i></button>
        <button class="side-btn" title="Games"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="side-btn" title="Popular"><i class="bi bi-compass"></i></button>
        <button class="side-btn side-bottom" title="Newest"><i class="bi bi-star-fill"></i></button>
      </div>
    </div>
  </aside>

  <script>
    // Settings dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
      const settingsBtn = document.querySelector('.settings-btn');
      const dropdownMenu = document.querySelector('.dropdown-menu');
      const logoutBtn = document.querySelector('.logout-btn');
      
      // Toggle dropdown when settings button is clicked
      settingsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.settings-dropdown')) {
          dropdownMenu.classList.remove('show');
        }
      });
      
      // Handle logout button click
      logoutBtn.addEventListener('click', function() {
        // Optional: Add logout logic here (clear session storage, etc.)
        
        // Redirect to landing page
        window.location.href = 'index.php';
      });
    });
  </script>

</body>
</html>
