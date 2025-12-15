// Profile Page JavaScript
(function () {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  const btnEdit = $('#btnEdit');
  const btnSave = $('#btnSave');
  const btnCancel = $('#btnCancel');
  const btnToggleName = $('#btnToggleName');
  const btnShowcase = $('#btnEditShowcase');
  const saveModal = $('#saveModal');
  const yesBtn = $('#confirmYes');
  const noBtn = $('#confirmNo');
  const postsModal = $('#postsSelectionModal');
  const closePostsModalBtn = $('#closePostsModal');

  // Selected best posts (will be saved)
  let selectedBestPosts = [];

  // Name toggle state
  const nameEl = $('#display_name');
  let nameA = (nameEl?.textContent || '').trim();
  let nameB = (nameEl?.dataset.fullname || '').trim();
  let nameVariant = 'A';
  let useFullName = false;

  function paintName() {
    const v = (nameVariant === 'A') ? nameA : nameB;
    const el = $('#display_name');
    if (!el) return;
    if (el.tagName === 'INPUT') el.value = v;
    else el.textContent = v;
    useFullName = (nameVariant === 'B');
  }

  const originals = new Map();

  // Convert elements to editable inputs
  function toInput(el) {
    const type = el.dataset.edit;
    originals.set(el.id || el, el.cloneNode(true));

    if (type === 'text') {
      const i = document.createElement('input');
      i.className = 'form-control form-control-sm';
      i.value = el.textContent.trim();
      el.replaceWith(i);
      i.id = el.id;
      return i;
    }

    if (type === 'textarea') {
      const t = document.createElement('textarea');
      t.className = 'form-control';
      t.rows = 4;
      // Remove HTML tags and get plain text
      t.value = el.innerText.trim().replace('Enter Your Bio!', '');
      el.replaceWith(t);
      t.id = el.id;
      return t;
    }

    if (type === 'img') {
      const wrap = document.createElement('div');
      wrap.className = 'img-edit-wrap';

      const file = document.createElement('input');
      file.type = 'file';
      file.accept = 'image/*';
      file.className = 'form-control form-control-sm mb-1';
      file.dataset.formName = el.id;

      const prev = el.cloneNode();
      prev.style.maxWidth = '100%';
      if (el.id === 'avatar') {
        prev.style.borderRadius = '50%';
      }
      prev.style.objectFit = 'cover';

      file.addEventListener('change', e => {
        const f = e.target.files?.[0];
        if (f) {
          const reader = new FileReader();
          reader.onload = evt => {
            prev.src = evt.target.result;
          };
          reader.readAsDataURL(f);
        }
      });

      wrap.appendChild(file);
      wrap.appendChild(prev);
      wrap.id = el.id;
      wrap.dataset.edit = 'img';
      el.replaceWith(wrap);
      return wrap;
    }
    return el;
  }

  function restore(id) {
    const cur = document.getElementById(id);
    const orig = originals.get(id);
    if (cur && orig) {
      cur.replaceWith(orig.cloneNode(true));
      originals.delete(id);
    }
  }

  function restoreAll() {
    $$('[data-edit]').forEach(el => {
      if (el.id) restore(el.id);
    });
  }

  // Enter edit mode
  function enterEditMode() {
    $$('[data-edit]').forEach(toInput);
    btnEdit.classList.add('d-none');
    btnSave.classList.remove('d-none');
    btnCancel.classList.remove('d-none');
    btnShowcase?.classList.remove('d-none');
  }

  // Exit edit mode
  function exitEditMode() {
    restoreAll();
    btnEdit.classList.remove('d-none');
    btnSave.classList.add('d-none');
    btnCancel.classList.add('d-none');
    btnShowcase?.classList.add('d-none');
  }

  // Get image data
  async function getImageData(id) {
    const el = document.getElementById(id);
    if (!el) return '';
    
    if (el.classList && el.classList.contains('img-edit-wrap')) {
      const f = el.querySelector('input[type="file"]')?.files?.[0];
      if (f) {
        return await new Promise((resolve) => {
          const reader = new FileReader();
          reader.onload = (e) => resolve(e.target.result);
          reader.readAsDataURL(f);
        });
      }
      const img = el.querySelector('img');
      return img ? img.src : '';
    }
    return el.getAttribute('src') || '';
  }

  // Gather data to save
  async function gatherData() {
    const displayNameEl = $('#display_name');
    const bioEl = $('#bio');

    const data = {
      display_name: useFullName ? nameB : nameA,
      use_full_name: useFullName,
      bio: bioEl.tagName === 'TEXTAREA' ? bioEl.value.trim() : bioEl.innerText.trim().replace('Enter Your Bio!', ''),
      avatar: await getImageData('avatar'),
      game_img: await getImageData('game_img'),
      genre_img: await getImageData('genre_img'),
      best_posts: selectedBestPosts.length > 0 ? selectedBestPosts : getCurrentBestPosts()
    };

    return data;
  }

  // Get current best posts from DOM
  function getCurrentBestPosts() {
    const posts = [];
    $$('#best_posts_list .best-post').forEach(post => {
      const postId = post.dataset.postId;
      if (postId) {
        posts.push(parseInt(postId));
      }
    });
    return posts;
  }

  // Save to backend
  async function saveToBackend(data) {
    try {
      const response = await fetch('profile_save.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();
      if (result.success) {
        // Reload page to show updated data
        window.location.reload();
      } else {
        alert('Error saving profile: ' + result.error);
      }
    } catch (error) {
      console.error('Save error:', error);
      alert('Error saving profile. Please try again.');
    }
  }

  // Posts selection modal
  function openPostsSelectionModal() {
    postsModal.classList.remove('d-none');
    
    // Pre-select current best posts
    const currentBestPosts = getCurrentBestPosts();
    $$('.post-selection-item').forEach(item => {
      const postId = parseInt(item.dataset.postId);
      if (currentBestPosts.includes(postId)) {
        item.style.borderColor = '#38a0ff';
        item.style.background = 'rgba(56, 160, 255, 0.2)';
      }
    });
  }

  function closePostsSelectionModal() {
    postsModal.classList.add('d-none');
  }

  // Event Listeners
  btnEdit?.addEventListener('click', () => {
    enterEditMode();
  });

  btnCancel?.addEventListener('click', () => {
    exitEditMode();
    selectedBestPosts = [];
  });

  btnSave?.addEventListener('click', () => {
    saveModal.classList.remove('d-none');
  });

  btnToggleName?.addEventListener('click', () => {
    nameVariant = (nameVariant === 'A') ? 'B' : 'A';
    paintName();
  });

  yesBtn?.addEventListener('click', async () => {
    saveModal.classList.add('d-none');
    const data = await gatherData();
    await saveToBackend(data);
  });

  noBtn?.addEventListener('click', () => {
    saveModal.classList.add('d-none');
  });

  btnShowcase?.addEventListener('click', () => {
    openPostsSelectionModal();
  });

  closePostsModalBtn?.addEventListener('click', () => {
    closePostsSelectionModal();
  });

  // Handle post selection
  $$('.post-selection-item').forEach(item => {
    item.addEventListener('click', function() {
      const postId = parseInt(this.dataset.postId);
      
      // Check if already selected
      const index = selectedBestPosts.indexOf(postId);
      if (index > -1) {
        // Deselect
        selectedBestPosts.splice(index, 1);
        this.style.borderColor = 'transparent';
        this.style.background = 'rgba(255,255,255,0.05)';
      } else {
        // Select (max 3)
        if (selectedBestPosts.length >= 3) {
          alert('You can only select up to 3 posts');
          return;
        }
        selectedBestPosts.push(postId);
        this.style.borderColor = '#38a0ff';
        this.style.background = 'rgba(56, 160, 255, 0.2)';
      }

      // Update preview
      updateBestPostsPreview();
    });
  });

  function updateBestPostsPreview() {
    const container = $('#best_posts_list');
    container.innerHTML = '';

    if (selectedBestPosts.length === 0) {
      container.innerHTML = '<p class="text-muted small">Select posts from the modal</p>';
      return;
    }

    selectedBestPosts.forEach(postId => {
      const post = allPosts.find(p => p.id == postId);
      if (post) {
        const postEl = document.createElement('div');
        postEl.className = 'best-post d-block mb-2';
        postEl.dataset.postId = post.id;
        postEl.innerHTML = `
          <div class="small fw-semibold text-truncate">${escapeHtml(post.title)}</div>
          <div class="d-flex align-items-center gap-3 small mt-1">
            <span><i class="bi bi-star-fill me-1"></i>${post.likes}</span>
            <span><i class="bi bi-chat-fill me-1"></i>${post.comments}</span>
          </div>
        `;
        container.appendChild(postEl);
      }
    });

    // Close modal after selection if 3 posts selected
    if (selectedBestPosts.length === 3) {
      setTimeout(() => {
        closePostsSelectionModal();
      }, 500);
    }
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Close modal when clicking outside
  postsModal?.addEventListener('click', (e) => {
    if (e.target === postsModal) {
      closePostsSelectionModal();
    }
  });

  saveModal?.addEventListener('click', (e) => {
    if (e.target === saveModal) {
      saveModal.classList.add('d-none');
    }
  });

})();
