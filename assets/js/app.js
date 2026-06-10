/**
 * Quran.et - Main Application JavaScript
 * Handles: dark mode, language switching, favorites, audio player, donation modal, cookies
 */

// ==================== DARK MODE ====================
function initDarkMode() {
  // Class was already set in <head> to prevent FOUC.
  // Listen for system preference changes (follow OS when no saved preference).
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
    if (!localStorage.getItem('theme')) {
      document.documentElement.classList.toggle('dark', e.matches);
    }
  });
}

function toggleDarkMode() {
  const isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// ==================== TRANSLATION / LANGUAGE ====================
function setTranslationId(id) {
  localStorage.setItem('active_lang', id);
  // Reload the page to reflect language change
  const url = new URL(window.location.href);
  // If on a reading page, update the lang param
  const pathParts = window.location.pathname.split('/').filter(Boolean);
  if (pathParts.length >= 2 && pathParts[1].match(/^[a-z]+$/) && pathParts[2]) {
    // reading page: /{lang}/{surah}
    window.location.href = '/' + id + '/' + pathParts[2] + window.location.search;
  } else {
    window.location.reload();
  }
}

// ==================== FAVORITES ====================
function getFavorites() {
  try {
    return JSON.parse(localStorage.getItem('user_favorites_list') || '[]');
  } catch(e) {
    return [];
  }
}

function saveFavorites(favs) {
  localStorage.setItem('user_favorites_list', JSON.stringify(favs));
}

function addFavorite(item) {
  const id = item.type === 'surah' ? 'sura-' + item.suraId : 'ayah-' + item.suraId + '-' + item.ayahIndex;
  const favs = getFavorites();
  if (favs.some(f => f.id === id)) return;
  favs.push({ ...item, id });
  saveFavorites(favs);
  return id;
}

function removeFavorite(id) {
  const favs = getFavorites().filter(f => f.id !== id);
  saveFavorites(favs);
}

function isFavorite(id) {
  return getFavorites().some(f => f.id === id);
}

// ==================== DONATION MODAL ====================
function initDonationModal(enabled) {
  if (!enabled) return;
  const dismissed = localStorage.getItem('donationDismissed');
  if (dismissed) return;
  
  setTimeout(() => {
    const modal = document.getElementById('donation-modal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  }, 5000);
}

function dismissDonation() {
  localStorage.setItem('donationDismissed', 'true');
  const modal = document.getElementById('donation-modal');
  if (modal) modal.classList.add('hidden');
}

// ==================== GLOBAL AUDIO PLAYER ====================
let audioPlayer = null;
let audioListenersAttached = false;

function initAudioPlayer(config) {
  if (audioListenersAttached) return;
  audioListenersAttached = true;
  
  audioPlayer = document.getElementById('global-audio');
  if (!audioPlayer) {
    audioPlayer = new Audio();
    audioPlayer.id = 'global-audio';
    document.body.appendChild(audioPlayer);
  }
}

function playSurahAudio(url, surahName, reciterName, surahId, reciterFolder) {
  const player = document.getElementById('global-audio');
  const playerEl = document.getElementById('global-audio-player');
  
  if (!player || !playerEl) return;
  
  player.src = url;
  player.play().catch(() => {});
  
  // Update UI
  document.getElementById('player-surah-id').textContent = surahId;
  document.getElementById('player-surah-name').textContent = surahName;
  document.getElementById('player-reciter-name').textContent = reciterName;
  
  playerEl.classList.remove('hidden');
  
  // Store state
  playerEl.dataset.surahId = surahId;
  playerEl.dataset.reciterFolder = reciterFolder;
  playerEl.dataset.reciterName = reciterName;
  playerEl.dataset.surahName = surahName;
  playerEl.dataset.paused = 'false';
  playerEl.dataset.repeatMode = 'none';
  
  updatePlayPauseButton(false);
}

function stopAudio() {
  const player = document.getElementById('global-audio');
  const playerEl = document.getElementById('global-audio-player');
  if (player) player.pause();
  if (playerEl) playerEl.classList.add('hidden');
}

function togglePlay() {
  const player = document.getElementById('global-audio');
  const playerEl = document.getElementById('global-audio-player');
  if (!player || !playerEl) return;
  
  if (player.paused) {
    player.play();
    playerEl.dataset.paused = 'false';
    updatePlayPauseButton(false);
  } else {
    player.pause();
    playerEl.dataset.paused = 'true';
    updatePlayPauseButton(true);
  }
}

function updatePlayPauseButton(paused) {
  const btn = document.getElementById('player-play-btn');
  if (!btn) return;
  if (paused) {
    btn.innerHTML = '<i data-lucide="play" class="w-6 h-6 ml-1 fill-current"></i>';
  } else {
    btn.innerHTML = '<i data-lucide="pause" class="w-6 h-6 fill-current"></i>';
  }
  if (window.lucide) lucide.createIcons();
}

function playerNext() {
  const playerEl = document.getElementById('global-audio-player');
  if (!playerEl) return;
  const currentId = parseInt(playerEl.dataset.surahId);
  const nextId = currentId >= 114 ? 1 : currentId + 1;
  const folder = playerEl.dataset.reciterFolder;
  const reciterName = playerEl.dataset.reciterName;
  const surahName = SURAH_NAMES_EN[nextId - 1];
  const url = getFullSurahAudioUrl(folder, nextId);
  playSurahAudio(url, surahName, reciterName, nextId, folder);
}

function playerPrev() {
  const playerEl = document.getElementById('global-audio-player');
  if (!playerEl) return;
  const currentId = parseInt(playerEl.dataset.surahId);
  const prevId = currentId <= 1 ? 114 : currentId - 1;
  const folder = playerEl.dataset.reciterFolder;
  const reciterName = playerEl.dataset.reciterName;
  const surahName = SURAH_NAMES_EN[prevId - 1];
  const url = getFullSurahAudioUrl(folder, prevId);
  playSurahAudio(url, surahName, reciterName, prevId, folder);
}

function toggleRepeatMode() {
  const playerEl = document.getElementById('global-audio-player');
  if (!playerEl) return;
  const mode = playerEl.dataset.repeatMode === 'none' ? 'one' : 'none';
  playerEl.dataset.repeatMode = mode;
  const btn = document.getElementById('player-repeat-btn');
  if (btn) {
    btn.className = 'transition ' + (mode === 'one' ? 'text-primary' : 'text-gray-400 hover:text-primary');
  }
}

function updateProgress() {
  const player = document.getElementById('global-audio');
  const progressBar = document.getElementById('player-progress');
  const currentTimeEl = document.getElementById('player-current-time');
  const durationEl = document.getElementById('player-duration');
  
  if (!player || !progressBar) return;
  
  if (player.duration) {
    const pct = (player.currentTime / player.duration) * 100;
    progressBar.value = pct || 0;
  }
  
  if (currentTimeEl) currentTimeEl.textContent = formatTime(player.currentTime);
  if (durationEl && player.duration) durationEl.textContent = formatTime(player.duration);
}

function seekAudio(val) {
  const player = document.getElementById('global-audio');
  if (player && player.duration) {
    player.currentTime = (val / 100) * player.duration;
  }
}

function setVolume(val) {
  const player = document.getElementById('global-audio');
  if (player) {
    player.volume = val / 100;
  }
}

function handleAudioEnded() {
  const player = document.getElementById('global-audio');
  const playerEl = document.getElementById('global-audio-player');
  if (!player || !playerEl) return;
  
  if (playerEl.dataset.repeatMode === 'one') {
    player.currentTime = 0;
    player.play();
  } else {
    playerNext();
  }
}

function formatTime(seconds) {
  if (!seconds || isNaN(seconds)) return "0:00";
  const min = Math.floor(seconds / 60);
  const sec = Math.floor(seconds % 60);
  return min + ":" + (sec < 10 ? "0" : "") + sec;
}

// ==================== COOKIE HELPERS ====================
function setCookie(name, value, days) {
  const d = new Date();
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = name + "=" + encodeURIComponent(value) + ";expires=" + d.toUTCString() + ";path=/";
}

function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}

// ==================== QURAN AUDIO URL HELPERS ====================
const RECITER_SERVER_MAP = {
  'basit': '7', 'maher': '12', 'afs': '8', 's_gmd': '7',
  'sds': '11', 'jhn': '13', 'mtrod': '8', 'ajm': '10',
  'husr': '13', 'abkr': '6'
};

function getFullSurahAudioUrl(folder, surahIndex) {
  const pad = String(surahIndex).padStart(3, '0');
  const server = RECITER_SERVER_MAP[folder] || '7';
  return 'https://server' + server + '.mp3quran.net/' + folder + '/' + pad + '.mp3';
}

function getAyahAudioUrl(surahIndex, ayahIndex, reciterSubfolder) {
  const padSurah = String(surahIndex).padStart(3, '0');
  const padAyah = String(ayahIndex).padStart(3, '0');
  const folder = reciterSubfolder || 'AbdulSamad_64kbps_QuranExplorer.Com';
  return 'https://everyayah.com/data/' + folder + '/' + padSurah + padAyah + '.mp3';
}

// ==================== INIT ====================
document.addEventListener('DOMContentLoaded', function() {
  initDarkMode();
  
  // Initialize Lucide icons
  if (window.lucide) lucide.createIcons();
  
  // Attach global audio event listeners
  const player = document.getElementById('global-audio');
  if (player) {
    player.addEventListener('timeupdate', updateProgress);
    player.addEventListener('ended', handleAudioEnded);
  }
});
