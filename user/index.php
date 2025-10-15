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
    <link rel="stylesheet" href="../assets/css/LandingPage.css" />
  </head>
  <body>

    <!-- TOP NAV (transparent) -->
    <header class="lp-header container-xl">
      <nav class="lp-nav">
        <a href="index.php" class="lp-brand">
          <img src="../assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
        </a>

        <ul class="lp-menu">
          <li><a href="#" class="lp-link">About</a></li>
          <li><a href="#" class="lp-link">Support</a></li>
          <li><a href="#" class="lp-link">Meet the Devs!</a></li>
          <li><a href="#" class="lp-link">Discover</a></li>
        </ul>
      </nav>
    </header>

    <!-- HERO -->
    <main class="lp-hero container-xl">
      <div class="lp-grid">
        <!-- Panda -->
        <div class="lp-panda">
          <img src="../assets/img/LandingPagePanda.png" alt="Panda mascot" class="lp-panda-img">
        </div>

        <!-- Copy + CTAs -->  
        <div class="lp-copy">
          <h1 class="lp-title">Read One of the Most Trusted Reviews Online!</h1>
          <p class="lp-sub">
            EXPoints is THE Gamer Forum, from properly rated reviews that ensures each Gamer has played through each game
            they’re reviewing to making sure the community is non-toxic, we also encourage interactivity with our StarUp system!
          </p>

          <div class="lp-ctas">
            <a href="./login.php" class="btn lp-btn lp-btn-light">LOGIN!</a>
            <a href="./register.php" class="btn lp-btn lp-btn-brand">REGISTER!</a>
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
      <img class="strip" src="../assets/img/BoxArt Strip - Landing Page.png" alt="Popular games strip A">
      <!-- B -->
      <img class="strip" src="../assets/img/BoxArt Strip - Landing Page 2.png" alt="Popular games strip B">
      <!-- A clone (for seamless loop) -->
      <img class="strip" src="../assets/img/BoxArt Strip - Landing Page.png" alt="" aria-hidden="true">
    </div>
  </aside>


  </body>
  </html>
