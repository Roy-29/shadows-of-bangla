/**
 * SHADOWS OF BANGLA — Main Client Controller
 * Audio Synthesizer, Atmospheric Canvas, Bookmarks & Global Navigation
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initParticleCanvas();
  initAmbientAudio();
  initFavoritesSystem();
  initRecentlyViewed();
  initStatCounters();
  initSearchModal();
});

/* ----------------------------------------------------
 * 1. Navbar & Mobile Menu
 * ---------------------------------------------------- */
function initNavbar() {
  const navbar = document.querySelector('.site-navbar');
  const toggleBtn = document.querySelector('.mobile-nav-toggle');
  const navMenu = document.querySelector('.nav-menu');

  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }, { passive: true });
  }

  if (toggleBtn && navMenu) {
    toggleBtn.addEventListener('click', () => {
      const isActive = navMenu.classList.toggle('active');
      toggleBtn.innerHTML = isActive 
        ? '<i class="fa-solid fa-xmark"></i>' 
        : '<i class="fa-solid fa-bars"></i>';
      toggleBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
    });

    // Close on link click
    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        toggleBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
      });
    });
  }
}

/* ----------------------------------------------------
 * 2. Atmospheric Particle & Fog Canvas
 * ---------------------------------------------------- */
function initParticleCanvas() {
  const canvas = document.getElementById('particle-canvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let width = (canvas.width = window.innerWidth);
  let height = (canvas.height = window.innerHeight);

  window.addEventListener('resize', () => {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
  });

  const particles = [];
  const particleCount = Math.min(width > 768 ? 45 : 20, 50);

  for (let i = 0; i < particleCount; i++) {
    particles.push({
      x: Math.random() * width,
      y: Math.random() * height,
      radius: Math.random() * 2 + 0.6,
      vx: (Math.random() - 0.5) * 0.35,
      vy: -(Math.random() * 0.4 + 0.15), // gently rising like embers/dust
      alpha: Math.random() * 0.5 + 0.1,
      color: Math.random() > 0.4 ? 'rgba(166, 166, 173, ' : 'rgba(158, 27, 50, '
    });
  }

  function render() {
    ctx.clearRect(0, 0, width, height);

    for (let p of particles) {
      p.x += p.vx;
      p.y += p.vy;

      if (p.y < 0) {
        p.y = height + 5;
        p.x = Math.random() * width;
      }
      if (p.x < 0) p.x = width;
      if (p.x > width) p.x = 0;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
      ctx.fillStyle = p.color + p.alpha + ')';
      ctx.fill();
    }

    requestAnimationFrame(render);
  }

  render();
}

/* ----------------------------------------------------
 * 3. Atmospheric Horror Soundscape (Web Audio API)
 * Built-in Synthesizer: 100% Offline, Zero Bandwidth, No Broken Files
 * ---------------------------------------------------- */
let audioCtx = null;
let soundNodes = null;
let isAudioPlaying = false;
let globalVolume = 0.4;

function initAmbientAudio() {
  const audioToggle = document.getElementById('ambient-sound-toggle');
  const volumeSlider = document.getElementById('ambient-volume');
  const volumeWrapper = document.querySelector('.volume-slider-wrapper');

  if (!audioToggle) return;

  // Restore saved volume
  const savedVol = localStorage.getItem('shadows_volume');
  if (savedVol !== null) {
    globalVolume = parseFloat(savedVol);
    if (volumeSlider) volumeSlider.value = globalVolume;
  }

  // Toggle ambient audio on click (OFF by default)
  audioToggle.addEventListener('click', (e) => {
    // If clicking directly on slider wrapper, ignore toggle
    if (e.target.closest('.volume-slider-wrapper')) return;

    if (!isAudioPlaying) {
      startAmbientSoundscape();
      audioToggle.classList.add('playing');
      audioToggle.querySelector('.pill-text').textContent = 'Atmosphere ON';
      isAudioPlaying = true;
    } else {
      stopAmbientSoundscape();
      audioToggle.classList.remove('playing');
      audioToggle.querySelector('.pill-text').textContent = 'Atmosphere OFF';
      isAudioPlaying = false;
    }
  });

  // Long press / right click to toggle volume slider
  audioToggle.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    if (volumeWrapper) volumeWrapper.classList.toggle('active');
  });

  // Hover or click on volume slider
  if (volumeSlider) {
    volumeSlider.addEventListener('input', (e) => {
      globalVolume = parseFloat(e.target.value);
      localStorage.setItem('shadows_volume', globalVolume);
      if (soundNodes && soundNodes.masterGain) {
        soundNodes.masterGain.gain.setValueAtTime(globalVolume, audioCtx.currentTime);
      }
    });
  }
}

function startAmbientSoundscape() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!audioCtx) {
      audioCtx = new AudioContext();
    }
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }

    const masterGain = audioCtx.createGain();
    masterGain.gain.setValueAtTime(globalVolume * 0.35, audioCtx.currentTime);
    masterGain.connect(audioCtx.destination);

    // Node 1: Deep Eerie Drone Oscillator (65Hz Sub-Bass)
    const osc1 = audioCtx.createOscillator();
    osc1.type = 'sawtooth';
    osc1.frequency.setValueAtTime(55, audioCtx.currentTime); // A1 note

    const filter1 = audioCtx.createBiquadFilter();
    filter1.type = 'lowpass';
    filter1.frequency.setValueAtTime(140, audioCtx.currentTime);

    const gain1 = audioCtx.createGain();
    gain1.gain.setValueAtTime(0.4, audioCtx.currentTime);

    osc1.connect(filter1);
    filter1.connect(gain1);
    gain1.connect(masterGain);
    osc1.start();

    // Node 2: Sub-harmonic resonant drone (110Hz with slight detune)
    const osc2 = audioCtx.createOscillator();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(112, audioCtx.currentTime); // eerie 2Hz beat frequency

    const gain2 = audioCtx.createGain();
    gain2.gain.setValueAtTime(0.25, audioCtx.currentTime);

    osc2.connect(gain2);
    gain2.connect(masterGain);
    osc2.start();

    // Node 3: Wind-like Noise Generator using Buffer
    const bufferSize = audioCtx.sampleRate * 2;
    const noiseBuffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
    const output = noiseBuffer.getChannelData(0);
    for (let i = 0; i < bufferSize; i++) {
      output[i] = Math.random() * 2 - 1;
    }

    const whiteNoise = audioCtx.createBufferSource();
    whiteNoise.buffer = noiseBuffer;
    whiteNoise.loop = true;

    const noiseFilter = audioCtx.createBiquadFilter();
    noiseFilter.type = 'bandpass';
    noiseFilter.frequency.setValueAtTime(320, audioCtx.currentTime);
    noiseFilter.Q.setValueAtTime(2.5, audioCtx.currentTime);

    // LFO to slowly sweep wind filter frequency
    const lfo = audioCtx.createOscillator();
    lfo.type = 'sine';
    lfo.frequency.setValueAtTime(0.12, audioCtx.currentTime); // slow 8-second cycle
    const lfoGain = audioCtx.createGain();
    lfoGain.gain.setValueAtTime(160, audioCtx.currentTime);
    lfo.connect(noiseFilter.frequency);
    lfo.start();

    const noiseGain = audioCtx.createGain();
    noiseGain.gain.setValueAtTime(0.15, audioCtx.currentTime);

    whiteNoise.connect(noiseFilter);
    noiseFilter.connect(noiseGain);
    noiseGain.connect(masterGain);
    whiteNoise.start();

    soundNodes = { masterGain, osc1, osc2, whiteNoise, lfo };
  } catch (err) {
    console.warn('Audio Synthesis Note: User interaction required or AudioContext unavailable.', err);
  }
}

function stopAmbientSoundscape() {
  if (soundNodes && audioCtx) {
    try {
      soundNodes.masterGain.gain.linearRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
      setTimeout(() => {
        if (soundNodes.osc1) soundNodes.osc1.stop();
        if (soundNodes.osc2) soundNodes.osc2.stop();
        if (soundNodes.whiteNoise) soundNodes.whiteNoise.stop();
        if (soundNodes.lfo) soundNodes.lfo.stop();
        soundNodes = null;
      }, 550);
    } catch (e) {
      soundNodes = null;
    }
  }
}

/* ----------------------------------------------------
 * 4. Favorites & Bookmarks System (localStorage)
 * ---------------------------------------------------- */
function getFavorites() {
  try {
    return JSON.parse(localStorage.getItem('shadows_favorites')) || [];
  } catch (e) {
    return [];
  }
}

function saveFavorites(favorites) {
  localStorage.setItem('shadows_favorites', JSON.stringify(favorites));
  updateFavoritesUI();
}

function toggleFavorite(place) {
  let favorites = getFavorites();
  const index = favorites.findIndex(item => item.id == place.id || item.slug === place.slug);

  if (index > -1) {
    favorites.splice(index, 1);
  } else {
    favorites.push({
      id: place.id,
      slug: place.slug,
      name: place.name,
      district: place.district,
      division: place.division,
      cover_image: place.cover_image,
      category: place.category
    });
  }

  saveFavorites(favorites);
  return index === -1; // returns true if now bookmarked
}

function updateFavoritesUI() {
  const favorites = getFavorites();
  const badgeCounts = document.querySelectorAll('.favorites-count-badge');
  badgeCounts.forEach(el => {
    el.textContent = favorites.length;
    el.style.display = favorites.length > 0 ? 'flex' : 'none';
  });

  // Update card bookmark buttons
  document.querySelectorAll('.bookmark-btn').forEach(btn => {
    const id = btn.getAttribute('data-id');
    const slug = btn.getAttribute('data-slug');
    const isSaved = favorites.some(item => item.id == id || item.slug === slug);
    if (isSaved) {
      btn.classList.add('bookmarked');
      btn.innerHTML = '<i class="fa-solid fa-bookmark"></i>';
    } else {
      btn.classList.remove('bookmarked');
      btn.innerHTML = '<i class="fa-regular fa-bookmark"></i>';
    }
  });

  // Render modal list if modal is open
  renderFavoritesModal();
}

function initFavoritesSystem() {
  updateFavoritesUI();

  // Attach event delegation for bookmark buttons
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.bookmark-btn');
    if (!btn) return;
    e.preventDefault();

    const place = {
      id: btn.getAttribute('data-id'),
      slug: btn.getAttribute('data-slug'),
      name: btn.getAttribute('data-name'),
      district: btn.getAttribute('data-district'),
      division: btn.getAttribute('data-division'),
      cover_image: btn.getAttribute('data-image'),
      category: btn.getAttribute('data-category')
    };

    toggleFavorite(place);
  });

  // Open modal button
  const modalTrigger = document.getElementById('open-favorites-modal');
  const modalOverlay = document.getElementById('favorites-modal');
  const modalClose = document.getElementById('close-favorites-modal');

  if (modalTrigger && modalOverlay) {
    modalTrigger.addEventListener('click', () => {
      renderFavoritesModal();
      modalOverlay.classList.add('active');
    });
  }
  if (modalClose && modalOverlay) {
    modalClose.addEventListener('click', () => {
      modalOverlay.classList.remove('active');
    });
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) modalOverlay.classList.remove('active');
    });
  }
}

function renderFavoritesModal() {
  const container = document.getElementById('favorites-list-container');
  if (!container) return;

  const favorites = getFavorites();
  if (favorites.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
        <i class="fa-regular fa-bookmark" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;"></i>
        <p>No places bookmarked yet.</p>
        <p style="font-size: 0.85rem; margin-top: 0.5rem;">Click the bookmark icon on any location card to save it for your journey.</p>
      </div>
    `;
    return;
  }

  container.innerHTML = favorites.map(item => `
    <div style="display: flex; align-items: center; gap: 1rem; padding: 0.85rem 0; border-bottom: 1px solid var(--border-subtle);">
      <img src="${item.cover_image || 'assets/images/panam-city.svg'}" alt="${item.name}" style="width: 55px; height: 55px; border-radius: 6px; object-fit: cover;">
      <div style="flex-grow: 1;">
        <h4 style="font-size: 0.95rem; margin-bottom: 0.2rem;"><a href="place.php?slug=${item.slug}" style="color: #fff;">${item.name}</a></h4>
        <span style="font-size: 0.75rem; color: var(--accent-red);">${item.district}, ${item.division}</span>
      </div>
      <button class="bookmark-btn bookmarked" data-id="${item.id}" data-slug="${item.slug}" title="Remove bookmark" style="width: 28px; height: 28px;">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </div>
  `).join('');
}

/* ----------------------------------------------------
 * 5. Recently Viewed Places (localStorage)
 * ---------------------------------------------------- */
function initRecentlyViewed() {
  // If we are on a place details page, record it
  const placeRecordEl = document.getElementById('current-place-data');
  if (placeRecordEl) {
    const slug = placeRecordEl.dataset.slug;
    const name = placeRecordEl.dataset.name;
    const image = placeRecordEl.dataset.image;
    const district = placeRecordEl.dataset.district;
    const division = placeRecordEl.dataset.division;

    let history = [];
    try {
      history = JSON.parse(localStorage.getItem('shadows_history')) || [];
    } catch(e) {}

    // Remove existing if present to bump to front
    history = history.filter(item => item.slug !== slug);
    history.unshift({ slug, name, image, district, division, time: Date.now() });
    if (history.length > 6) history.pop();

    localStorage.setItem('shadows_history', JSON.stringify(history));
  }

  // Render recently viewed on container if present
  const recentContainer = document.getElementById('recently-viewed-tray');
  if (recentContainer) {
    let history = [];
    try {
      history = JSON.parse(localStorage.getItem('shadows_history')) || [];
    } catch(e) {}

    if (history.length === 0) {
      recentContainer.closest('.recently-viewed-section')?.remove();
      return;
    }

    recentContainer.innerHTML = history.map(item => `
      <a href="place.php?slug=${item.slug}" class="recent-thumb-card" style="display: flex; align-items: center; gap: 0.85rem; background: var(--bg-card); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-subtle); min-width: 240px; text-decoration: none;">
        <img src="${item.image || 'assets/images/panam-city.svg'}" alt="${item.name}" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover;">
        <div>
          <div style="font-size: 0.85rem; font-weight: 600; color: #fff; font-family: var(--font-serif);">${item.name}</div>
          <div style="font-size: 0.7rem; color: var(--accent-red);">${item.district}</div>
        </div>
      </a>
    `).join('');
  }
}

/* ----------------------------------------------------
 * 6. Animated Stat Counters
 * ---------------------------------------------------- */
function initStatCounters() {
  const counters = document.querySelectorAll('.stat-number[data-target]');
  if (counters.length === 0) return;

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = +entry.target.getAttribute('data-target');
        const duration = 1600;
        const start = 0;
        const startTime = performance.now();

        function updateCounter(currentTime) {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          // Ease out cubic
          const easeProgress = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(start + (target - start) * easeProgress);

          entry.target.textContent = current.toLocaleString();

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          } else {
            entry.target.textContent = target.toLocaleString();
          }
        }

        requestAnimationFrame(updateCounter);
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2 });

  counters.forEach(c => observer.observe(c));
}

/* ----------------------------------------------------
 * 7. Global Search Modal
 * ---------------------------------------------------- */
function initSearchModal() {
  const searchBtn = document.getElementById('open-search-modal');
  const searchModal = document.getElementById('global-search-modal');
  const searchClose = document.getElementById('close-search-modal');
  const searchInput = document.getElementById('modal-search-input');

  if (searchBtn && searchModal) {
    searchBtn.addEventListener('click', () => {
      searchModal.classList.add('active');
      setTimeout(() => searchInput?.focus(), 100);
    });
  }

  // Keyboard shortcut Ctrl+K or /
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      searchModal?.classList.add('active');
      setTimeout(() => searchInput?.focus(), 100);
    }
    if (e.key === 'Escape') {
      searchModal?.classList.remove('active');
      document.getElementById('favorites-modal')?.classList.remove('active');
    }
  });

  if (searchClose && searchModal) {
    searchClose.addEventListener('click', () => searchModal.classList.remove('active'));
    searchModal.addEventListener('click', (e) => {
      if (e.target === searchModal) searchModal.classList.remove('active');
    });
  }
}

