/**
 * Audio Page JavaScript
 * Handles reciter selection, surah filtering, and play functionality
 */

let autoPlayTimer = null;

function initAudioPage() {
  // Check for auto-play from navigation state
  const autoPlayData = document.getElementById('auto-play-data');
  if (autoPlayData) {
    const surahId = parseInt(autoPlayData.dataset.surahId);
    const surahName = autoPlayData.dataset.surahName;
    if (surahId) {
      autoPlayTimer = setTimeout(function() {
        playSurahAudioFromPage(surahId, surahName);
      }, 500);
    }
  }
}

function filterSurahs(val) {
  const filter = val.toLowerCase();
  document.querySelectorAll('.surah-item').forEach(function(item) {
    const name = (item.dataset.name || '').toLowerCase();
    const id = item.dataset.id;
    const match = name.includes(filter) || id === filter;
    item.classList.toggle('hidden', !match);
  });
  
  // Show/hide "no results"
  const visible = document.querySelectorAll('.surah-item:not(.hidden)').length;
  const noResults = document.getElementById('no-results');
  if (noResults) noResults.classList.toggle('hidden', visible > 0);
}

function changeReciter(name) {
  const select = document.getElementById('reciter-select');
  if (select) select.value = name;
}

function playSurahAudioFromPage(surahId, surahName) {
  const select = document.getElementById('reciter-select');
  if (!select) return;
  
  const reciterName = select.value;
  const selectedOption = select.options[select.selectedIndex];
  const subfolder = selectedOption ? selectedOption.dataset.subfolder : '';
  
  if (!subfolder) return;
  
  const url = getFullSurahAudioUrl(subfolder, surahId);
  playSurahAudio(url, surahName, reciterName, surahId, subfolder);
}
