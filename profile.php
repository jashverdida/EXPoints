<?php
// --- demo data (replace via backend later) ---
$user = $user ?? [
  'display_name'   => 'Club Ejay',
  'handle'         => '@ejaywashere',
  'avatar_url'     => 'assets/img/cat1.jpg',
  'level'          => 2,
  'level_progress' => 72,
  'bio'            => "Leveling up my gaming knowledge one JRPG at a time!\nJoin me on this epic quest to uncover hidden gems and share all things RPG! #GameReviewer #JRPGFan",
  'date_started'   => '2025-03-15',
];
$stats = $stats ?? ['stars'=>50,'reviews'=>4];
$favorites = $favorites ?? [
  'game' => [
    'title' => 'Devil May Cry 5',
    'image' => 'assets/img/Favorite Game.png',
    'url'   => '#',
  ],
  'genres' => [
    'label' => 'JRPG',
    'image' => 'assets/img/Favorite%20Genre.png',
  ],
];
$best_posts = $best_posts ?? [
  ['title'=>'Best Winter Levels in Video Games','likes'=>120,'comments'=>53,'url'=>'#'],
  ['title'=>'Final Fantasy X is the best game ever','likes'=>99,'comments'=>87,'url'=>'#'],
  ['title'=>'Best Battle Themes in Video Games','likes'=>22,'comments'=>22,'url'=>'#'],
];
function h(?string $s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$started_fmt = !empty($user['date_started']) ? date('n/j/y', strtotime($user['date_started'])) : '—';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/profile.css" />
</head>
<body class="bg-exp">

  <div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <img id="brand_logo" src="assets/img/EXPoints Logo.png" alt="EXPoints" class="brand-logo" />
    </div>

    <div class="row g-4">
      <!-- MAIN -->
      <div class="col-md-8">
        <div class="card card-glass p-3 p-md-4 position-relative overflow-visible">

          <!-- Avatar -->
          <div class="avatar-wrap">
            <img id="avatar" src="<?= h($user['avatar_url']) ?>" alt="Avatar" class="avatar-xl" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h2 id="display_name" class="profile-name mb-0"><?= h($user['display_name']) ?></h2>
              <span id="handle" class="profile-handle"><?= h($user['handle']) ?></span>
            </div>

            <!-- Stats -->
            <div class="d-flex align-items-center gap-3 mt-2 stats-row">
              <span><i class="bi bi-star-fill"></i><span id="stars"><?= (int)$stats['stars'] ?></span></span>
              <span><i class="bi bi-book"></i><span id="reviews"><?= (int)$stats['reviews'] ?></span></span>
            </div>

            <!-- Level -->
            <div class="d-flex align-items-center gap-3 mt-2">
              <span class="lvl-pill">LVL <span id="level_num"><?= (int)$user['level'] ?></span></span>
              <div class="progress level-bar flex-grow-1" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div id="level_bar" class="progress-bar" style="width: <?= (int)$user['level_progress'] ?>%"></div>
              </div>
            </div>

            <!-- Bio -->
            <p id="bio" class="profile-bio mt-3"><?= nl2br(h($user['bio'])) ?></p>
          </div>

          <!-- Info grid -->
          <div class="row g-3 align-items-stretch mt-2">
            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Date Started</div>
                <div id="date_started" class="mini-value"><?= h($started_fmt) ?></div>
              </div>
            </div>

            <div class="col-sm-4">
              <a id="game_url" class="mini-card h-100 text-decoration-none" href="<?= h($favorites['game']['url']) ?>">
                <div class="mini-title">Favorite Game</div>
                <div class="thumb-box">
                  <img id="game_img" src="<?= h($favorites['game']['image']) ?>" alt="<?= h($favorites['game']['title']) ?>" />
                </div>
              </a>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Genres</div>
                <img id="genre_img" src="<?= h($favorites['genres']['image']) ?>" alt="<?= h($favorites['genres']['label']) ?>" class="genre-badge" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div class="col-md-4">
        <div class="card card-pill p-3 p-md-4">
          <h3 class="best-title">Best Posts:</h3>
          <div id="best_posts_list">
            <?php foreach ($best_posts as $p): ?>
              <a class="best-post d-block text-decoration-none mb-2" href="<?= h($p['url']) ?>">
                <div class="small fw-semibold text-truncate"><?= h($p['title']) ?></div>
                <div class="d-flex align-items-center gap-3 small mt-1">
                  <span><i class="bi bi-star-fill me-1"></i><?= (int)$p['likes'] ?></span>
                  <span><i class="bi bi-chat-fill me-1"></i><?= (int)$p['comments'] ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
