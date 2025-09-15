<?php
// --- demo data (replace via backend later) ---
$user = $user ?? [
  'display_name'   => 'Club Ejay',
  'full_name'      => 'Ejay Beligaño',
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

          <!-- Edit controls -->
          <div class="edit-controls">
            <button id="btnEdit"         class="btn btn-sm btn-outline-light">Edit Profile</button>
            <button id="btnToggleName"   class="btn btn-sm btn-outline-light d-none">Toggle Name</button>
            <button id="btnSave"         class="btn btn-sm btn-success d-none">Save Changes</button>
            <button id="btnCancel"       class="btn btn-sm btn-danger d-none">Cancel</button>
          </div>

          <!-- Avatar -->
          <div class="avatar-wrap">
            <img id="avatar" src="<?= h($user['avatar_url']) ?>" alt="Avatar" class="avatar-xl" data-edit="img" />
          </div>

          <!-- Header -->
          <div class="content-shift">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h2 id="display_name" class="profile-name mb-0" data-edit="text"
                data-fullname="<?= h($user['full_name']) ?>"><?= h($user['display_name']) ?></h2>
              <span id="handle" class="profile-handle" data-edit="text"><?= h($user['handle']) ?></span>
            </div>

            <!-- Stats -->
            <div class="d-flex align-items-center gap-3 mt-2 stats-row">
              <span><i class="bi bi-star-fill"></i><span id="stars"><?= (int)$stats['stars'] ?></span></span>
              <span><i class="bi bi-book"></i><span id="reviews"><?= (int)$stats['reviews'] ?></span></span>
            </div>

            <!-- Level (not editable) -->
            <div class="d-flex align-items-center gap-3 mt-2 level-wrap">
              <span class="lvl-pill">LVL <span id="level_num"><?= (int)$user['level'] ?></span></span>
              <div class="progress level-bar flex-grow-1">
                <div id="level_bar" class="progress-bar" style="width: <?= (int)$user['level_progress'] ?>%"></div>
              </div>
            </div>

            <!-- Bio -->
            <p id="bio" class="profile-bio mt-3" data-edit="textarea"><?= nl2br(h($user['bio'])) ?></p>
          </div>

          <!-- Info grid -->
          <div class="row g-3 align-items-stretch mt-2">
            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Date Started</div>
                <div id="date_started" class="mini-value" data-edit="date" data-value="<?= h($user['date_started']) ?>">
                  <?= h($started_fmt) ?>
                </div>
              </div>
            </div>

            <div class="col-sm-4">
              <a id="game_url" class="mini-card h-100 text-decoration-none" href="<?= h($favorites['game']['url']) ?>" data-edit="url">
                <div class="mini-title">Favorite Game</div>
                <div class="thumb-box">
                  <img id="game_img" src="<?= h($favorites['game']['image']) ?>" alt="<?= h($favorites['game']['title']) ?>" data-edit="img" />
                </div>
              </a>
            </div>

            <div class="col-sm-4">
              <div class="mini-card h-100">
                <div class="mini-title">Favorite Genres</div>
                <img id="genre_img" src="<?= h($favorites['genres']['image']) ?>" alt="<?= h($favorites['genres']['label']) ?>" class="genre-badge" data-edit="img" />
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
          <!-- Hidden until Edit -->
          <button id="btnEditShowcase" class="btn btn-sm btn-light-subtle mt-2 w-100 d-none">Edit Selection</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Save confirmation -->
  <div id="saveModal" class="modal-backdrop d-none">
    <div class="modal-card">
      <h5 class="mb-2 text-center">Save Changes?</h5>
      <p class="text-center mb-3">All changes here will reflect on your profile</p>
      <div class="d-flex justify-content-center gap-2">
        <button id="confirmYes" class="btn btn-success btn-sm px-4">YES</button>
        <button id="confirmNo"  class="btn btn-danger  btn-sm px-4">NO</button>
      </div>
    </div>
  </div>

  <!-- Hidden form for backend -->
  <form id="profileForm" class="d-none" method="post" action="profile_save.php" enctype="multipart/form-data">
    <input type="hidden" name="display_name">
    <input type="hidden" name="handle">
    <input type="hidden" name="bio">
    <input type="hidden" name="date_started">
    <input type="hidden" name="avatar_url">
    <input type="hidden" name="favorites[game][image]">
    <input type="hidden" name="favorites[game][url]">
    <input type="hidden" name="favorites[genres][image]">
    <input type="hidden" name="use_full_name">
  </form>

  <script>
    (function () {
  const $  = (s, c=document) => c.querySelector(s);
  const $$ = (s, c=document) => Array.from(c.querySelectorAll(s));

  const btnEdit   = $('#btnEdit');
  const btnSave   = $('#btnSave');
  const btnCancel = $('#btnCancel');
  const btnToggleName = $('#btnToggleName');
  const btnShowcase = $('#btnEditShowcase');
  const saveModal = $('#saveModal');
  const yesBtn    = $('#confirmYes');
  const noBtn     = $('#confirmNo');
  const form      = $('#profileForm');

  let useFullName = false;
  const originals = new Map();

  function toInput(el){
    const type = el.dataset.edit;
    originals.set(el.id || el, el.cloneNode(true));

    if (type === 'text') {
      const i = document.createElement('input');
      i.className = 'form-control form-control-sm';
      i.value = el.textContent.trim();
      el.replaceWith(i); i.id = el.id;
      return i;
    }
    if (type === 'textarea') {
      const t = document.createElement('textarea');
      t.className = 'form-control';
      t.rows = 4; 
      t.value = el.innerText.trim();
      el.replaceWith(t); t.id = el.id;
      return t;
    }
    if (type === 'date') {
      const i = document.createElement('input');
      i.type = 'date'; 
      i.className = 'form-control form-control-sm';
      i.value = el.dataset.value || '';
      el.replaceWith(i); i.id = el.id;
      return i;
    }

    // IMAGE: file chooser + live preview (NO url textbox)
    if (type === 'img') {
      const wrap = document.createElement('div');
      wrap.className = 'img-edit-wrap';
      //Choose img
      const file = document.createElement('input');
      file.type = 'file'; file.accept = 'image/*';
      file.className = 'form-control form-control-sm mb-1';
      
      const prev = el.cloneNode();
      prev.style.maxWidth = '100%'; 
      prev.style.borderRadius = '8px';
      
        // map which hidden form field name to use
      if (el.id === 'avatar') file.dataset.formName = 'avatar_file';
      if (el.id === 'game_img') file.dataset.formName = 'favorites_game_file';
      if (el.id === 'genre_img') file.dataset.formName = 'favorites_genres_file';
      
       file.addEventListener('change', () => {
        const f = file.files[0];
        if (f) prev.src = URL.createObjectURL(f);
     });

      wrap.append(file, prev); 
      el.replaceWith(wrap); 
      wrap.id = el.id;
      return wrap;
    }
  }

  function enterEdit(){
    $$('.card-glass [data-edit]').forEach(toInput);
    document.querySelector('.card-glass')?.classList.add('editing');
    
    btnEdit.classList.add('d-none');
    btnSave.classList.remove('d-none');
    btnCancel.classList.remove('d-none');
    btnToggleName.classList.remove('d-none');
    btnShowcase.classList.remove('d-none');
  }

  function restoreOriginals(){
    originals.forEach((node, key) => {
      const current = (typeof key === 'string') ? document.getElementById(key) : key;
      current.replaceWith(node);
    });
    originals.clear();
  }

  function exitEdit(restore){
  if (restore) {
    // remove any inline temp editors we inserted
    document.querySelectorAll('.temp-edit').forEach(n => n.remove());
    originals.forEach((node, key) => {
      const current = (typeof key === 'string') ? document.getElementById(key) : key;
      if (current) current.replaceWith(node);
    });
    originals.clear();
  }
  document.querySelector('.card-glass')?.classList.remove('editing');

  btnEdit.classList.remove('d-none');
  btnSave.classList.add('d-none');
  btnCancel.classList.add('d-none');
  btnToggleName.classList.add('d-none');
  btnShowcase.classList.add('d-none');
  }

  // --- Events
  btnEdit?.addEventListener('click', enterEdit);
  btnCancel?.addEventListener('click', () => exitEdit(true));

  btnSave?.addEventListener('click', () => saveModal.classList.remove('d-none'));
  noBtn?.addEventListener('click', () => saveModal.classList.add('d-none'));

  yesBtn?.addEventListener('click', () => {
  saveModal.classList.add('d-none');

  // text values (if still inputs, read value; else read text)
  const getVal = (id, isRich=false) => {
    const el = document.getElementById(id);
    if (!el) return '';
    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') return el.value;
    return isRich ? el.innerText.trim() : (el.textContent || '').trim();
  };

  form.elements['display_name'].value = getVal('display_name');
  form.elements['handle'].value       = getVal('handle');
  form.elements['bio'].value          = getVal('bio', true);
  form.elements['date_started'].value = (document.getElementById('date_started')?.value) || (document.getElementById('date_started')?.dataset.value || '');

  // current srcs (if editing, preview <img> sits as second child in our wrap)
  const readImgSrc = (id) => {
    const el = document.getElementById(id);
    if (!el) return '';
    if (el.classList && el.classList.contains('img-edit-wrap')) {
      const img = el.querySelector('img');
      return img ? img.src : '';
    }
    return el.getAttribute('src') || '';
  };
  form.elements['avatar_url'].value               = readImgSrc('avatar');
  form.elements['favorites[game][image]'].value   = readImgSrc('game_img');
  form.elements['favorites[genres][image]'].value = readImgSrc('genre_img');

  // Favorite Game link input we inserted
  const gameUrlInput = document.getElementById('game_url_input');
  const gameAnchor   = document.getElementById('game_url');
  form.elements['favorites[game][url]'].value = gameUrlInput ? gameUrlInput.value : (gameAnchor?.getAttribute('href') || '');

  // Toggle-name flag
  form.elements['use_full_name'].value = useFullName ? '1' : '0';

  // Move any temp file inputs into the form before submit (so files upload)
  document.querySelectorAll('.img-edit-wrap input[type="file"]').forEach(file => {
    if (!file.name && file.dataset.formName) file.name = file.dataset.formName;
    form.appendChild(file); // moving keeps the selected File intact
  });

  form.submit();
  exitEdit(false);
});


  btnToggleName?.addEventListener('click', () => {
  useFullName = !useFullName;
  const el = document.getElementById('display_name');
  const full = el.dataset.fullname || '';
  const customOriginal = originals.get('display_name')?.textContent?.trim() || '';

  if (el.tagName === 'INPUT') {
    el.value = useFullName ? full : customOriginal;
  } else {
    el.textContent = useFullName ? full : customOriginal;
  }
});

})();
  </script>
</body>
</html>
