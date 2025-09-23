<?php
// --- demo data (replace via backend later) ---
$user = $user ?? [
  'display_name'   => 'Club Ejay',
  'full_name'      => 'Eijay Beligaño',
  'handle'         => '@ejaywashere',
  'avatar_url'     => '/EXPoints/assets/img/cat1.jpg',
  'level'          => 2,
  'level_progress' => 72,
  'bio'            => "Leveling up my gaming knowledge one JRPG at a time!\nJoin me on this epic quest to uncover hidden gems and share all things RPG! #GameReviewer #JRPGFan",
  'date_started'   => '2025-03-15',
];
$stats = $stats ?? ['stars'=>50,'reviews'=>4];
$favorites = $favorites ?? [
  'game' => [
    'title' => 'Devil May Cry 5',
    'image' => '/EXPoints/assets/img/Favorite Game.png',
    'url'   => '#',
  ],
  'genres' => [
    'label' => 'JRPG',
    'image' => '/EXPoints/assets/img/Favorite%20Genre.png',
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
  <link rel="stylesheet" href="/EXPoints/assets/css/profile.css" />
</head>
<body class="bg-exp">

  <div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <img id="brand_logo" src="/EXPoints/assets/img/EXPoints Logo.png" alt="EXPoints" class="brand-logo" />
    </div>

    <div class="row g-4">
      <!-- MAIN -->
      <div class="col-md-8">
        <div class="card card-glass p-3 p-md-4 position-relative overflow-visible">

          <!-- Edit controls -->
          <div class="edit-controls">
            <button id="btnToggleName"   class="btn btn-sm btn-outline-light">Toggle Name</button>
            <button id="btnEdit"         class="btn btn-sm btn-outline-light">Edit Profile</button>
            <button id="btnCancel"       class="btn btn-sm btn-danger d-none">Cancel</button>
            <button id="btnSave"         class="btn btn-sm btn-success d-none">Save Changes</button>
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
    <input type="hidden" name="full_name">
    <input type="hidden" name="handle">
    <input type="hidden" name="bio">
    <input type="hidden" name="date_started">
    <input type="hidden" name="avatar_url">
    <input type="hidden" name="favorites[game][image]">
    <input type="hidden" name="favorites[game][url]">
    <input type="hidden" name="favorites[genres][image]">
    <input type="hidden" name="use_full_name">
  </form>

    <!-- Single Best-Post editor (hidden by default) -->
    <div id="postEditor" class="modal-backdrop d-none">
      <div class="modal-card">
        <h5 class="mb-3">Edit Post</h5>
        
        <div class="mb-2">
          <label class="form-label small mb-1">Title</label>
          <input id="pe_title" type="text" class="form-control form-control-sm" />
        </div>

        <div class="mb-3">
          <label class ="form-label small mb-1">Comments</label>
          <textarea id="pe_comments" class="form-control" rows="8"
              placeholder="Write your thoughts here..."></textarea>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
          <button id="pe_cancel" class="btn btn-sm btn-danger">Cancel</button>
          <button id="pe_save"   class="btn btn-sm btn-success">Save</button>
        </div>
      </div>
    </div>

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

  // --- image helpers (add once) ---
const fileToDataURL = (file) =>
  new Promise((resolve, reject) => {
    const fr = new FileReader();
    fr.onload = () => resolve(fr.result);
    fr.onerror = reject;
    fr.readAsDataURL(file);
  });

// If editing, converts the chosen file to a DataURL; else returns current <img> src
async function getImageData(id) {
  const el = document.getElementById(id);
  if (!el) return '';
  if (el.classList && el.classList.contains('img-edit-wrap')) {
    const f = el.querySelector('input[type="file"]')?.files?.[0];
    if (f) return await fileToDataURL(f);   // new file chosen → DataURL
    const img = el.querySelector('img');
    return img ? img.src : '';
  }
  return el.getAttribute('src') || '';
}

// ---- storage + backend switch ----
const LS_KEY = 'expoints_profile_v1';
const BACKEND_MODE = false; // flip to true when profile_save.php is ready

  // --- Name toggle state (A = display name, B = full name) ---
const nameEl = document.getElementById('display_name');
let nameA = (nameEl?.textContent || '').trim();      // current shown text
let nameB = (nameEl?.dataset.fullname || '').trim(); // from data-fullname
let nameVariant = 'A';

function paintName(){
  const v = (nameVariant === 'A') ? nameA : nameB;
  const el = document.getElementById('display_name');
  if (!el) return;
  if (el.tagName === 'INPUT') el.value = v; else el.textContent = v;
  useFullName = (nameVariant === 'B'); // keep hidden form flag in sync
}

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

    const file = document.createElement('input');
    file.type = 'file'; file.accept = 'image/*';
    file.className = 'form-control form-control-sm mb-1';

    const prev = el.cloneNode();
    prev.style.maxWidth = '100%';
    prev.style.borderRadius = '50%';
    prev.style.objectFit = 'cover';

    // map which hidden form field name to use
    if (el.id === 'avatar') file.dataset.formName = 'avatar_file';
    if (el.id === 'game_img') file.dataset.formName = 'favorites_game_file';
    if (el.id === 'genre_img') file.dataset.formName = 'favorites_genres_file';

    file.addEventListener('change', () => {
      const f = file.files[0];
      if (f) prev.src = URL.createObjectURL(f);
    });

    // put button UNDER the avatar for the main profile image
    if (el.id === 'avatar') {
      wrap.style.display = 'flex';
      wrap.style.flexDirection = 'column';
      wrap.style.alignItems = 'center';

      prev.style.width = '98px';
      prev.style.height = '98px';
      prev.style.display = 'block';
      prev.style.borderRadius = '50%';
      prev.style.objectFit = 'cover';

      wrap.append(prev, file);        // image first, then file below
    } else {
      wrap.append(file, prev);        // other images keep old order
    }

    el.replaceWith(wrap);             // replace original <img>
    wrap.id = el.id;                  // keep same id
    return wrap;                      // return the editor node
  }
}

function enterEdit(){
  $$('.card-glass [data-edit]').forEach(toInput);
  document.querySelector('.card-glass')?.classList.add('editing');

  btnEdit.classList.add('d-none');
  btnSave.classList.remove('d-none');
  btnCancel.classList.remove('d-none');
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
  btnShowcase.classList.add('d-none');
}

/* ---------- DEV LOAD (localStorage) ---------- */
function loadFromStorage(){
  const raw = localStorage.getItem(LS_KEY);
  if (!raw) return;
  const d = JSON.parse(raw);

  // names + which one to show
  if (typeof d.display_name === 'string') nameA = d.display_name;
  if (typeof d.full_name === 'string')    nameB = d.full_name;
  if (d.use_full_name === 1 || d.use_full_name === '1') nameVariant = 'B';
  paintName();

  // handle
  if (d.handle) document.getElementById('handle').textContent = d.handle;

  // bio (plain text)
  if (d.bio) document.getElementById('bio').innerText = d.bio;

  // date (ISO -> M/D/YY)
  if (d.date_started) {
    const ds = document.getElementById('date_started');
    ds.dataset.value = d.date_started;
    const dt = new Date(d.date_started);
    if (!Number.isNaN(dt.getTime())) {
      const m = dt.getMonth()+1, day = dt.getDate(), y = String(dt.getFullYear()).slice(-2);
      ds.textContent = `${m}/${day}/${y}`;
    }
  }

  // favorite game link
  if (d.fav_game_url) {
    const a = document.getElementById('game_url');
    if (a) a.setAttribute('href', d.fav_game_url);
  }

  // optional image persistence (if base64 was saved)
  if (d.avatar_url_base64)  document.getElementById('avatar').src   = d.avatar_url_base64;
  if (d.game_img_base64)    document.getElementById('game_img').src = d.game_img_base64;
  if (d.genre_img_base64)   document.getElementById('genre_img').src= d.genre_img_base64;

  // best posts (optional in storage)
  if (Array.isArray(d.best_posts)) applyBestPosts(d.best_posts);

}

loadFromStorage();

// Apply best_posts from storage (title + url)
function applyBestPosts(posts) {
  const list = document.getElementById('best_posts_list');
  if (!list || !Array.isArray(posts)) return;
  list.innerHTML = '';
  posts.forEach(p => {
    const a = document.createElement('a');
    a.className = 'best-post d-block text-decoration-none mb-2';
    a.href = p.url || '#';
    a.innerHTML = `
      <div class="small fw-semibold text-truncate">${p.title || ''}</div>
      <div class="d-flex align-items-center gap-3 small mt-1">
        <span><i class="bi bi-star-fill me-1"></i>${p.likes ?? 0}</span>
        <span><i class="bi bi-chat-fill me-1"></i>${p.comments ?? 0}</span>
      </div>`;
    list.appendChild(a);
  });
}

// --- Best Posts Editor (simple modal) ---
function readBestPostsFromDOM() {
  const items = Array.from(document.querySelectorAll('#best_posts_list .best-post'));
  return items.map(a => {
    const title = a.querySelector('.fw-semibold')?.textContent?.trim() || '';
    const href = a.getAttribute('href') || '#';
    // read current counts (kept as-is)
    const nums = a.querySelectorAll('.small.mt-1 span');
    const likes = parseInt(nums[0]?.textContent?.replace(/\D+/g,'') || '0', 10);
    const comments = parseInt(nums[1]?.textContent?.replace(/\D+/g,'') || '0', 10);
    return { title, url: href, likes, comments };
  });
}

/* ---------- Events ---------- */
btnEdit?.addEventListener('click', enterEdit);
btnCancel?.addEventListener('click', () => exitEdit(true));
btnSave?.addEventListener('click', () => saveModal.classList.remove('d-none'));
noBtn?.addEventListener('click', () => saveModal.classList.add('d-none'));
yesBtn?.addEventListener('click', async () => {
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
  form.elements['date_started'].value =
    (document.getElementById('date_started')?.value) ||
    (document.getElementById('date_started')?.dataset.value || '');

  // --- Name A/B: track which one the user is showing/using
  const editedName = getVal('display_name', false);
  if (editedName) {
    if (nameVariant === 'A') nameA = editedName;
    else                     nameB = editedName;
  }
  form.elements['display_name'].value  = nameA;                 // A
  form.elements['full_name'].value     = nameB;                 // B
  form.elements['use_full_name'].value = (nameVariant === 'B') ? '1' : '0';

  // ---- Decide where to save ----
if (BACKEND_MODE) {
  // include best_posts[] for the server
  appendBestPostsToFormForBackend();   // ← this is the line you asked about

  // move any picked files into the form
  document.querySelectorAll('.img-edit-wrap input[type="file"]').forEach(file => {
    if (!file.name && file.dataset.formName) file.name = file.dataset.formName;
    form.appendChild(file);
  });

  form.submit();
  exitEdit(false);
} else {
  // (unchanged) FRONTEND: persist to localStorage
  saveBestPostsToLocalStorage(); // keep best posts in LS too

  // Safely capture image data (DataURL if a new file was picked)
  const avatarData = await getImageData('avatar');
  const gameData   = await getImageData('game_img');
  const genreData  = await getImageData('genre_img');

  const gameUrlInput = document.getElementById('game_url_input');
  const gameAnchor   = document.getElementById('game_url');

  const payload = {
    display_name:  nameA,
    full_name:     nameB,
    use_full_name: (nameVariant === 'B') ? 1 : 0,
    handle:        getVal('handle'),
    bio:           getVal('bio', true),
    date_started:  (document.getElementById('date_started')?.value)
                    || (document.getElementById('date_started')?.dataset.value || ''),
    fav_game_url:  gameUrlInput ? gameUrlInput.value : (gameAnchor?.getAttribute('href') || ''),
    avatar_url_base64: avatarData,
    game_img_base64:   gameData,
    genre_img_base64:  genreData,
    best_posts: readBestPostsFromDOM(),
  };

  localStorage.setItem(LS_KEY, JSON.stringify(payload));

  // reflect changes back to the original nodes then leave edit mode
  const mdy = (iso) => {
    const dt = new Date(iso);
    if (Number.isNaN(dt.getTime())) return '';
    const m = dt.getMonth()+1, d = dt.getDate(), y = String(dt.getFullYear()).slice(-2);
    return `${m}/${d}/${y}`;
  };

  const origDisplay = originals.get('display_name');
  if (origDisplay)
    origDisplay.textContent = (nameVariant === 'A') ? payload.display_name : payload.full_name;

  const origHandle = originals.get('handle');
  if (origHandle) origHandle.textContent = payload.handle;

  const origBio = originals.get('bio');
  if (origBio) origBio.innerText = payload.bio;

  const origDate = originals.get('date_started');
  if (origDate) {
    origDate.dataset.value = payload.date_started || '';
    origDate.textContent = payload.date_started ? mdy(payload.date_started) : '';
  }

  const origAvatar = originals.get('avatar');
  if (origAvatar && avatarData) origAvatar.src = avatarData;

  const origGame = originals.get('game_img');
  if (origGame && gameData) origGame.src = gameData;

  const origGenre = originals.get('genre_img');
  if (origGenre && genreData) origGenre.src = genreData;

  if (gameAnchor && payload.fav_game_url) gameAnchor.setAttribute('href', payload.fav_game_url);

  exitEdit(true);
} 

});

// ----- Best Posts helpers -----
function readBestPostsFromDOM() {
  return Array.from(document.querySelectorAll('#best_posts_list a.best-post')).map(a => {
    return {
      title: a.querySelector('.small.fw-semibold').textContent.trim(),
      url:   a.getAttribute('href') || '#',
      // keep likes/comments visible-only; stored so backend can receive them unchanged if needed
      likes:    parseInt(a.querySelector('.bi-star-fill')?.parentElement?.textContent.trim() || '0', 10),
      comments: parseInt(a.querySelector('.bi-chat-fill')?.parentElement?.textContent.trim() || '0', 10),
    };
  });
}

function writeSingleBestPostToDOM(idx, data) {
  const items = Array.from(document.querySelectorAll('#best_posts_list a.best-post'));
  const a = items[idx];
  if (!a) return;

  if (typeof data.title === 'string') {
    const ttl = a.querySelector('.small.fw-semibold');
    if (ttl) ttl.textContent = data.title;
  }
  
}

function saveBestPostsToLocalStorage() {
  const raw = localStorage.getItem(LS_KEY);
  const current = raw ? JSON.parse(raw) : {};
  current.best_posts = readBestPostsFromDOM();
  localStorage.setItem(LS_KEY, JSON.stringify(current));
}

function appendBestPostsToFormForBackend() {
  if (!BACKEND_MODE) return;
  // remove previous hidden inputs if any
  [...form.querySelectorAll('input[name^="best_posts["]')].forEach(n => n.remove());
  const posts = readBestPostsFromDOM();
  posts.forEach((p, idx) => {
    ['title','url','likes','comments'].forEach(k => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `best_posts[${idx}][${k}]`;
      input.value = p[k] ?? '';
      form.appendChild(input);
    });
  });
}

// ----- “Edit Selection” = pick a post, edit only that one -----
let postEditIndex = -1;

function enterPostPickMode() {
  document.getElementById('btnEditShowcase')?.classList.add('active');
  document.querySelectorAll('#best_posts_list a.best-post').forEach((a, i) => {
    a.style.cursor = 'pointer';
    a.classList.add('border', 'border-info');
    a.addEventListener('click', onPickPostOnce, { once: true });
    function onPickPostOnce(e) {
      e.preventDefault();
      postEditIndex = i;
      openPostEditor(i);
    }
  });
}

function exitPostPickMode() {
  document.getElementById('btnEditShowcase')?.classList.remove('active');
  document.querySelectorAll('#best_posts_list a.best-post').forEach(a => {
    a.style.cursor = '';
    a.classList.remove('border', 'border-info');
  });
}

function openPostEditor(i) {
  const data = readBestPostsFromDOM()[i] || { title:'', url:'#' };
  const wrap = document.getElementById('postEditor');
  document.getElementById('pe_title').value = data.title;
  wrap.classList.remove('d-none');
}

function closePostEditor() {
  document.getElementById('postEditor').classList.add('d-none');
  postEditIndex = -1;
  exitPostPickMode();
}

// wire modal buttons
document.getElementById('pe_cancel')?.addEventListener('click', (e) => {
  e.preventDefault();
  closePostEditor();
});

document.getElementById('pe_save')?.addEventListener('click', (e) => {
  e.preventDefault();
  if (postEditIndex < 0) return;

  const updated = {
    title: document.getElementById('pe_title').value.trim(),
  };

  writeSingleBestPostToDOM(postEditIndex, updated);
  saveBestPostsToLocalStorage();   // persist for frontend dev
  closePostEditor();
});

// main button: toggle pick mode
btnEditShowcase?.addEventListener('click', (e) => {
  e.preventDefault();
  // just enter pick mode each time (simple)
  enterPostPickMode();
});

btnToggleName?.addEventListener('click', () => {
  nameVariant = (nameVariant === 'A') ? 'B' : 'A';
  paintName();
});

})();
  </script>
</body>
</html>
