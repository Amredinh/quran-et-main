/**
 * Reading Page JavaScript
 * Handles per-ayah audio, settings, copy, detail modal, favorites, scroll to ayah
 */

let readingAudio = null;
let activeAyah = null;
let isAyahPlaying = false;
let ayahPlayMode = 'continuous'; // 'continuous' | 'single'

function initReadingPage(surahIndex) {
  readingAudio = new Audio();
  readingAudio.addEventListener('ended', onAyahEnded);
  
  // Load saved preferences
  const savedReciter = localStorage.getItem('everyayah_reciter') || 'AbdulSamad_64kbps_QuranExplorer.Com';
  const savedMode = localStorage.getItem('reading_play_mode') || 'continuous';
  ayahPlayMode = savedMode;
  
  const reciterSelect = document.getElementById('ayah-reciter-select');
  const modeSelect = document.getElementById('ayah-mode-select');
  if (reciterSelect) reciterSelect.value = savedReciter;
  if (modeSelect) modeSelect.value = savedMode;
  
  // Restore paper mode
  const savedPaperMode = localStorage.getItem('reading_mode');
  if (savedPaperMode === 'paper') {
    setReadingMode('paper');
  }
  
  // Scroll to ayah from URL param
  const params = new URLSearchParams(window.location.search);
  const ayaParam = params.get('aya');
  if (ayaParam) {
    setTimeout(function() {
      const idx = parseInt(ayaParam) - 1;
      const el = document.getElementById('ayah-' + idx);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        highlightAyah(idx);
      }
    }, 500);
  }
  
  // Save last read
  const surahName = document.getElementById('surah-name-display');
  if (surahName) {
    const name = surahName.textContent;
    setCookie('lastRead', JSON.stringify({ surahId: surahIndex, name: name }), 365);
  }
}

function playAyah(index, surahIndex) {
  if (activeAyah === index && isAyahPlaying) {
    readingAudio.pause();
    isAyahPlaying = false;
    updateAyahPlayButton(index, false);
    return;
  }
  
  activeAyah = index;
  isAyahPlaying = true;
  
  const reciterSelect = document.getElementById('ayah-reciter-select');
  const reciterFolder = reciterSelect ? reciterSelect.value : 'AbdulSamad_64kbps_QuranExplorer.Com';
  
  // Ayah index is 0-based, but audio URL uses 1-based
  const url = getAyahAudioUrl(surahIndex, index + 1, reciterFolder);
  readingAudio.src = url;
  readingAudio.play().catch(function(e) {
    console.error('Play error', e);
    isAyahPlaying = false;
    updateAyahPlayButton(index, false);
  });
  
  updateAyahPlayButton(index, true);
  highlightAyah(index);
  
  // Scroll to ayah
  const el = document.getElementById('ayah-' + index);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function onAyahEnded() {
  isAyahPlaying = false;
  if (activeAyah !== null) updateAyahPlayButton(activeAyah, false);
  
  if (ayahPlayMode === 'single') return;
  
  // Auto-play next ayah
  const totalAyahs = parseInt(document.getElementById('total-ayahs')?.value || '0');
  if (activeAyah !== null && activeAyah < totalAyahs - 1) {
    const nextIdx = activeAyah + 1;
    const surahIndex = parseInt(document.getElementById('surah-id-display')?.value || '1');
    setTimeout(function() {
      playAyah(nextIdx, surahIndex);
    }, 100);
  }
}

function updateAyahPlayButton(index, playing) {
  const btn = document.getElementById('play-btn-' + index);
  if (!btn) return;
  if (playing) {
    btn.innerHTML = '<i data-lucide="pause" class="w-5 h-5"></i>';
  } else {
    btn.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i>';
  }
  if (window.lucide) lucide.createIcons();
}

function highlightAyah(index) {
  document.querySelectorAll('[id^="ayah-"]').forEach(function(el) {
    el.classList.remove('bg-green-50', 'dark:bg-green-900/20', 'shadow-sm', 'scale-[1.01]', 'bg-black/5');
  });
  const el = document.getElementById('ayah-' + index);
  if (el) {
    const container = document.getElementById('reading-container');
    const isPaper = container && container.classList.contains('reading-paper-mode');
    if (isPaper) {
      el.classList.add('bg-green-50', 'scale-[1.01]');
    } else {
      el.classList.add('bg-green-50', 'dark:bg-green-900/20', 'shadow-sm', 'scale-[1.01]');
    }
  }
}

function changeAyahReciter(val) {
  localStorage.setItem('everyayah_reciter', val);
}

function changeAyahPlayMode(val) {
  ayahPlayMode = val;
  localStorage.setItem('reading_play_mode', val);
}

// ==================== SETTINGS PANEL ====================
function toggleSettings() {
  const panel = document.getElementById('settings-panel');
  if (panel) panel.classList.toggle('hidden');
}

function fixOldFontSize(val) {
  var v = parseFloat(val);
  // If it was a rem value (< 10), convert to px (* 16)
  if (v < 10) v = v * 16;
  return Math.round(v);
}

function setArabicFontSize(val) {
  val = fixOldFontSize(val);
  document.getElementById('arabic-font-size').textContent = val + 'px';
  var slider = document.querySelector('input[oninput="setArabicFontSize(this.value)"]');
  if (slider) slider.value = val;
  document.querySelectorAll('.ayah-arabic-text').forEach(function(el) {
    el.style.fontSize = val + 'px';
  });
  localStorage.setItem('reading_arabic_size', val);
}

function setTransFontSize(val) {
  val = fixOldFontSize(val);
  document.getElementById('trans-font-size').textContent = val + 'px';
  var slider = document.querySelector('input[oninput="setTransFontSize(this.value)"]');
  if (slider) slider.value = val;
  document.querySelectorAll('.ayah-trans-text').forEach(function(el) {
    el.style.fontSize = val + 'px';
  });
  localStorage.setItem('reading_trans_size', val);
}

function toggleArabicVisibility(show) {
  document.querySelectorAll('.ayah-arabic-text').forEach(function(el) {
    el.classList.toggle('hidden', !show);
  });
}

function toggleTransVisibility(show) {
  document.querySelectorAll('.ayah-trans-text').forEach(function(el) {
    el.classList.toggle('hidden', !show);
  });
}

function setReadingMode(mode) {
  const container = document.getElementById('reading-container');
  if (!container) return;
  
  if (mode === 'paper') {
    container.classList.add('reading-paper-mode');
    container.classList.remove('bg-white', 'dark:bg-gray-900');
  } else {
    container.classList.remove('reading-paper-mode');
    container.classList.add('bg-white', 'dark:bg-gray-900');
  }
  
  // Toggle button active states
  var modernBtn = document.getElementById('mode-modern-btn');
  var paperBtn = document.getElementById('mode-paper-btn');
  if (modernBtn && paperBtn) {
    if (mode === 'paper') {
      modernBtn.className = 'flex-1 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 font-medium text-gray-600 dark:text-gray-300';
      paperBtn.className = 'flex-1 py-1.5 text-sm rounded border bg-primary text-white border-primary font-bold';
    } else {
      modernBtn.className = 'flex-1 py-1.5 text-sm rounded border bg-primary text-white border-primary font-bold';
      paperBtn.className = 'flex-1 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 font-medium text-gray-600 dark:text-gray-300';
    }
  }
  
  localStorage.setItem('reading_mode', mode);
}

// ==================== COPY AYAH ====================
function showCopyMenu(index) {
  const menu = document.getElementById('copy-menu-' + index);
  if (menu) menu.classList.toggle('hidden');
}

function copyAyahText(index, type, arabic, translation, surahIndex) {
  let text = '';
  if (type === 'arabic') text = arabic || '';
  else if (type === 'trans') text = translation || '';
  else if (type === 'both') text = (arabic || '') + '\n\n' + (translation || '');
  
  navigator.clipboard.writeText(text);
  
  const menu = document.getElementById('copy-menu-' + index);
  if (menu) menu.classList.add('hidden');
  
  // Show brief "Copied!" feedback
  const btn = document.getElementById('copy-btn-' + index);
  if (btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="check" class="w-5 h-5 text-green-500"></i>';
    if (window.lucide) lucide.createIcons();
    setTimeout(function() { btn.innerHTML = orig; if (window.lucide) lucide.createIcons(); }, 1500);
  }
}

// ==================== AYAH DETAIL MODAL ====================
function showAyahDetail(index) {
  const modal = document.getElementById('ayah-detail-modal-' + index);
  if (modal) modal.classList.remove('hidden');
}

function hideAyahDetail(index) {
  const modal = document.getElementById('ayah-detail-modal-' + index);
  if (modal) modal.classList.add('hidden');
}

function changeDetailTranslation(val, index) {
  // Reload page with new translation
  const pathParts = window.location.pathname.split('/').filter(Boolean);
  if (pathParts.length >= 2) {
    window.location.href = '/' + val + '/' + pathParts[1] + '?aya=' + (index + 1);
  }
}

// ==================== AYAH FAVORITE TOGGLE ====================
function toggleAyahFavorite(surahId, surahName, ayahIndex, ayahNo, textTrans) {
  const key = 'ayah-' + surahId + '-' + ayahIndex;
  if (isFavorite(key)) {
    removeFavorite(key);
    updateHeartIcon(key, false);
  } else {
    addFavorite({
      type: 'ayah',
      suraId: surahId,
      suraName: surahName + ' [Aya ' + ayahNo + ']',
      ayahIndex: ayahIndex,
      ayahNo: ayahNo,
      textTrans: textTrans || ''
    });
    updateHeartIcon(key, true);
  }
}

function toggleSurahFavorite(surahId, surahName) {
  const key = 'sura-' + surahId;
  if (isFavorite(key)) {
    removeFavorite(key);
    updateHeartIcon(key, false);
  } else {
    addFavorite({
      type: 'surah',
      suraId: surahId,
      suraName: surahName || 'Surah ' + surahId
    });
    updateHeartIcon(key, true);
  }
}

function updateHeartIcon(id, filled) {
  document.querySelectorAll('[data-fav-id="' + id + '"]').forEach(function(el) {
    if (filled) {
      el.classList.add('text-red-500');
      el.querySelector('i')?.classList.add('fill-current');
    } else {
      el.classList.remove('text-red-500');
      el.querySelector('i')?.classList.remove('fill-current');
    }
  });
}
