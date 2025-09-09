  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EXPoints • Landing</title>

    <!-- (Optional) Bootstrap; not strictly required for this page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Landing-only stylesheet -->
    <link rel="stylesheet" href="assets/css/LandingPage.css" />
  </head>
  <body>

    <!-- TOP NAV (transparent) -->
    <header class="lp-header container-xl">
      <nav class="lp-nav">
        <a href="index.php" class="lp-brand">
          <img src="assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
        </a>

        <ul class="lp-menu">
          <li><a href="#about" class="lp-link">About</a></li>
          <li><a href="#support" class="lp-link">Support</a></li>
          <li><a href="#devs" class="lp-link">Meet the Devs</a></li>
          <li><a href="#discover" class="lp-link">Discover</a></li>
        </ul>
      </nav>
    </header>

    <!-- HERO -->
    <main class="lp-hero container-xl">
      <div class="lp-grid">
        <!-- Panda -->
        <div class="lp-panda">
          <img src="assets/img/LandingPagePanda.png" alt="Panda mascot" class="lp-panda-img">
        </div>

        <!-- Copy + CTAs -->
        <div class="lp-copy">
          <h1 class="lp-title">Read One of the Most Trusted Reviews Online!</h1>
          <p class="lp-sub">
            EXPoints is THE Gamer Forum, from properly rated reviews that ensures each Gamer has played through each game
            they’re reviewing to making sure the community is non-toxic, we also encourage interactivity with our StarUp system!
          </p>

          <div class="lp-ctas">
            <a href="login.php" class="btn lp-btn lp-btn-light">LOGIN!</a>
            <a href="register.php" class="btn lp-btn lp-btn-brand">REGISTER!</a>
          </div>
        </div>
      </div>

      <!-- Floating glyphs (decor only) -->
      <span class="glyph g-circle"  style="--x:12%; --y:72%"></span>
      <span class="glyph g-square"  style="--x:35%; --y:49%"></span>
      <span class="glyph g-cross"   style="--x:57%; --y:80%"></span>
      <span class="glyph g-cross"   style="--x:68%; --y:39%"></span>
      <span class="glyph g-circle"  style="--x:43%; --y:92%"></span>
      <span class="glyph g-square"  style="--x:75%; --y:91%"></span>
    </main>

  <!-- Posters strip (two vertical strips inside one merged frame) -->
  <aside class="posters">
    <div class="posters-track">
      <!-- A -->
      <img class="strip" src="assets/img/BoxArt Strip - Landing Page.png" alt="Popular games strip A">
      <!-- B -->
      <img class="strip" src="assets/img/BoxArt Strip - Landing Page 2.png" alt="Popular games strip B">
      <!-- A clone (for seamless loop) -->
      <img class="strip" src="assets/img/BoxArt Strip - Landing Page.png" alt="" aria-hidden="true">
    </div>
  </aside>

  <!-- Sections -->
  <section id="about" class="lp-section container-xl">
    <div class="section-inner">
      <h2 class="section-title">About EXPoints</h2>
      <p class="section-sub">A gamer-first community for honest reviews, non-toxic discussions, and discovery.</p>
      <div class="section-grid">
        <div class="feature">
          <h3>Verified Reviews</h3>
          <p>We spotlight reviews from players who actually finished the game. No fluff.</p>
        </div>
        <div class="feature">
          <h3>Healthy Community</h3>
          <p>Tools and moderation designed to keep conversations helpful and welcoming.</p>
        </div>
        <div class="feature">
          <h3>StarUp System</h3>
          <p>Earn recognition by contributing quality posts, guides, and insights.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="support" class="lp-section container-xl">
    <div class="section-inner">
      <h2 class="section-title">Support</h2>
      <p class="section-sub">Need help? We’ve got you. Choose a topic below to get started.</p>
      <div class="cards">
        <a class="card-link" href="login.php">
          <span class="card-heading">Account & Login</span>
          <span class="card-desc">Password resets, sign-in issues, verification</span>
        </a>
        <a class="card-link" href="register.php">
          <span class="card-heading">Getting Started</span>
          <span class="card-desc">Create an account and set up your profile</span>
        </a>
        <a class="card-link" href="#support">
          <span class="card-heading">Report a Problem</span>
          <span class="card-desc">Flag content or contact the team</span>
        </a>
      </div>
    </div>
  </section>

  <section id="devs" class="lp-section container-xl">
    <div class="section-inner">
      <h2 class="section-title">Meet the Devs</h2>
      <p class="section-sub">The team behind EXPoints. Passionate gamers and builders.</p>
      <div class="devs">
        <div class="dev">
          <div class="dev-avatar" style="background-image:url('assets/img/LandingPagePanda.png')"></div>
          <div class="dev-name">Panda</div>
          <div class="dev-role">Mascot & Hype</div>
        </div>
        <div class="dev">
          <div class="dev-avatar" style="background-image:url('assets/img/cat1.jpg')"></div>
          <div class="dev-name">Cat Dev</div>
          <div class="dev-role">Frontend</div>
        </div>
        <div class="dev">
          <div class="dev-avatar" style="background-image:url('assets/img/beluga.jpg')"></div>
          <div class="dev-name">Beluga</div>
          <div class="dev-role">Backend</div>
        </div>
      </div>
    </div>
  </section>

  <section id="discover" class="lp-section container-xl">
    <div class="section-inner">
      <h2 class="section-title">Discover</h2>
      <p class="section-sub">Jump into trending posts and popular games curated for you.</p>
      <div class="discover-row">
        <a class="discover-tile" href="dashboard.php">
          <span class="tile-title">Trending Posts</span>
          <span class="tile-sub">See what's hot right now</span>
        </a>
        <a class="discover-tile" href="dashboard.php">
          <span class="tile-title">Popular Games</span>
          <span class="tile-sub">Explore most discussed titles</span>
        </a>
        <a class="discover-tile" href="dashboard.php">
          <span class="tile-title">New Reviews</span>
          <span class="tile-sub">Fresh takes from players</span>
        </a>
      </div>
    </div>
  </section>

  </body>
  </html>
