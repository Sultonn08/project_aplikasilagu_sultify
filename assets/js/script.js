// ============================================================
// Sultify - Player & Main UI Logic
// ============================================================

const BASE = window.BASE_URL || (window.location.origin + (window.location.pathname.startsWith('/my_vibe') ? '/my_vibe' : ''));

const audio = document.getElementById('audio-element');
const playBtn = document.getElementById('btn-play');
const progressFill = document.getElementById('progress-fill');
const progressTrack = document.getElementById('progress-track');
const timeCurrent = document.getElementById('time-current');
const timeTotal = document.getElementById('time-total');
const volSlider = document.getElementById('vol-slider');
const likeBtn = document.getElementById('player-like');

let isPlaying = false;
let currentSongId = null;

// Playlist Queue State
let playlistQueue = [];
let originalQueue = [];
let queueIndex = -1;
let isSystemPlay = false;
let isShuffle = false;
let repeatMode = 0; // 0: off, 1: all, 2: one

// Format seconds into m:ss
function formatTime(seconds) {
    if (isNaN(seconds)) return "0:00";
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s < 10 ? '0' : ''}${s}`;
}

// Init Audio Player Listeners
if (audio) {
    playBtn.addEventListener('click', () => {
        if (!audio.src || audio.src === window.location.href) return;
        if (isPlaying) {
            audio.pause();
        } else {
            audio.play();
        }
    });

    audio.addEventListener('play', () => {
        isPlaying = true;
        playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
        document.querySelectorAll('.playing-bars').forEach(el => el.style.display = 'flex');
    });

    audio.addEventListener('pause', () => {
        isPlaying = false;
        playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        document.querySelectorAll('.playing-bars').forEach(el => el.style.display = 'none');
    });

    audio.addEventListener('timeupdate', () => {
        timeCurrent.innerText = formatTime(audio.currentTime);
        const percent = (audio.currentTime / audio.duration) * 100;
        progressFill.style.width = `${percent || 0}%`;
    });

    audio.addEventListener('loadedmetadata', () => {
        timeTotal.innerText = formatTime(audio.duration);
    });

    progressTrack.addEventListener('click', (e) => {
        if (!audio.src || audio.src === window.location.href) return;
        const rect = progressTrack.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        audio.currentTime = pos * audio.duration;
    });

    volSlider.addEventListener('input', (e) => {
        audio.volume = e.target.value;
    });
    
    // Default volume
    audio.volume = 1;

    // Audio ended listener for queue progression
    audio.addEventListener('ended', () => {
        if (repeatMode === 2) {
            audio.currentTime = 0;
            audio.play();
        } else {
            playNext();
        }
    });
}

// Queue Management Functions
function updateQueue(id) {
    // Find all elements that trigger playSong
    const elements = Array.from(document.querySelectorAll('[onclick*="playSong"]'));
    
    // Find the exact element for this song id using regex
    let el = elements.find(e => {
        const attr = e.getAttribute('onclick');
        const regex = new RegExp(`playSong\\(\\s*${id}\\s*,`);
        return regex.test(attr);
    });
    
    if (el && el.parentElement) {
        const container = el.parentElement;
        const siblings = container.querySelectorAll('[onclick*="playSong"]');
        originalQueue = Array.from(siblings);
        
        if (isShuffle) {
            const others = originalQueue.filter(item => item !== el);
            others.sort(() => Math.random() - 0.5);
            playlistQueue = [el, ...others];
            queueIndex = 0;
        } else {
            playlistQueue = [...originalQueue];
            queueIndex = playlistQueue.indexOf(el);
        }
    } else {
        playlistQueue = [];
        originalQueue = [];
        queueIndex = -1;
    }
}

function playNext() {
    if (playlistQueue.length === 0 || queueIndex === -1) return;
    
    let nextIndex = queueIndex + 1;
    if (nextIndex >= playlistQueue.length) {
        if (repeatMode === 1) {
            nextIndex = 0;
        } else {
            return; // End of queue
        }
    }
    
    isSystemPlay = true;
    queueIndex = nextIndex;
    playlistQueue[queueIndex].click();
}

function playPrev() {
    if (playlistQueue.length === 0 || queueIndex === -1) return;
    
    if (audio.currentTime > 3) {
        audio.currentTime = 0;
        return;
    }
    
    let prevIndex = queueIndex - 1;
    if (prevIndex < 0) {
        if (repeatMode === 1) {
            prevIndex = playlistQueue.length - 1;
        } else {
            prevIndex = 0;
            audio.currentTime = 0;
            return;
        }
    }
    
    isSystemPlay = true;
    queueIndex = prevIndex;
    playlistQueue[queueIndex].click();
}

// Control Buttons Binding
const btnNext = document.getElementById('btn-next');
const btnPrev = document.getElementById('btn-prev');
const btnShuffle = document.getElementById('btn-shuffle');
const btnRepeat = document.getElementById('btn-repeat');

if (btnNext) btnNext.addEventListener('click', playNext);
if (btnPrev) btnPrev.addEventListener('click', playPrev);

if (btnShuffle) {
    btnShuffle.addEventListener('click', () => {
        isShuffle = !isShuffle;
        btnShuffle.style.color = isShuffle ? 'var(--primary)' : 'inherit';
        
        if (playlistQueue.length > 0 && queueIndex !== -1) {
            const currentEl = playlistQueue[queueIndex];
            if (isShuffle) {
                const others = originalQueue.filter(el => el !== currentEl);
                others.sort(() => Math.random() - 0.5);
                playlistQueue = [currentEl, ...others];
                queueIndex = 0;
            } else {
                playlistQueue = [...originalQueue];
                queueIndex = playlistQueue.indexOf(currentEl);
            }
        }
    });
}

if (btnRepeat) {
    btnRepeat.addEventListener('click', () => {
        repeatMode = (repeatMode + 1) % 3;
        
        // btnRepeat needs position relative for the badge
        btnRepeat.style.position = 'relative';
        
        if (repeatMode === 0) {
            btnRepeat.style.color = 'inherit';
            btnRepeat.innerHTML = '<i class="fa-solid fa-repeat"></i>';
        } else if (repeatMode === 1) {
            btnRepeat.style.color = 'var(--primary)';
            btnRepeat.innerHTML = '<i class="fa-solid fa-repeat"></i>';
        } else if (repeatMode === 2) {
            btnRepeat.style.color = 'var(--primary)';
            btnRepeat.innerHTML = '<i class="fa-solid fa-repeat"></i><span style="font-size: 0.55rem; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-weight: bold; color: var(--bg-dark);">1</span>';
        }
    });
}

// Global Play Function
function playSong(id, title, artist, coverUrl, audioUrl) {
    document.getElementById('player-info').style.display = 'flex';
    document.getElementById('player-title').innerText = title;
    document.getElementById('player-artist').innerText = artist;
    document.getElementById('player-cover-img').src = coverUrl;
    
    audio.src = audioUrl;
    audio.play().catch(e => {
        showToast("Gagal memutar lagu: Format tidak didukung atau file hilang", "error");
        console.error(e);
    });
    
    currentSongId = id;
    
    if (!isSystemPlay) {
        updateQueue(id);
    }
    isSystemPlay = false; // reset
    
    // Update styling baris list lagu yang sedang aktif
    document.querySelectorAll('.song-list tr').forEach(tr => tr.classList.remove('playing'));
    document.querySelectorAll('.quick-card').forEach(card => card.classList.remove('playing'));
    
    const row = document.getElementById(`song-row-${id}`);
    if (row) row.classList.add('playing');
    
    const card = document.getElementById(`song-card-${id}`);
    if (card) card.classList.add('playing');
    
    // Add to history and update play count via API
    fetch(`${BASE}/api/play.php?id=${id}`)
        .catch(err => console.error("Gagal mencatat pemutaran:", err));
        
    // Check if song is liked
    fetch(`${BASE}/api/check_favorite.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            const icon = document.getElementById('player-like').querySelector('i');
            const likeBtnEl = document.getElementById('player-like');
            if (data.liked) {
                likeBtnEl.classList.add('liked');
                icon.className = 'fa-solid fa-heart';
            } else {
                likeBtnEl.classList.remove('liked');
                icon.className = 'fa-regular fa-heart';
            }
        })
        .catch(err => console.error("Gagal cek favorit:", err));

    // Update lyrics dynamically if the lyrics overlay is currently active
    const lyricsModal = document.getElementById('lyrics-modal');
    if (lyricsModal && lyricsModal.style.display === 'flex') {
        document.getElementById('lyrics-cover-img').src = coverUrl;
        fetchLyrics(id);
    }
}

// Toast Notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Like/Unlike Feature
function toggleLike() {
    if (!currentSongId) return;
    
    fetch(`${BASE}/api/toggle_favorite.php?id=${currentSongId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = likeBtn.querySelector('i');
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    icon.className = 'fa-solid fa-heart';
                    showToast('Ditambahkan ke Lagu Disukai');
                } else {
                    likeBtn.classList.remove('liked');
                    icon.className = 'fa-regular fa-heart';
                    showToast('Dihapus dari Lagu Disukai');
                    
                    // If we are currently on the favorites page, we can hide the row for immediate feedback
                    const row = document.getElementById(`song-row-${currentSongId}`);
                    if (row && window.location.pathname.includes('/favorites')) {
                        row.style.opacity = '0.5';
                    }
                }
            } else {
                showToast(data.message || 'Gagal mengubah status favorit', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Terjadi kesalahan jaringan', 'error');
        });
}

// Lyrics Feature (Spotify-style Synced)
let parsedLyrics = [];
let currentLyricIndex = -1;

function toggleLyrics() {
    if (!currentSongId) {
        showToast("Pilih lagu terlebih dahulu", "error");
        return;
    }
    
    const modal = document.getElementById('lyrics-modal');
    if (modal.style.display === 'flex') {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // restore scrolling
    } else {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // prevent background scrolling
        
        // Set cover image for the lyrics modal
        const currentCover = document.getElementById('player-cover-img').src;
        document.getElementById('lyrics-cover-img').src = currentCover;

        // If we haven't fetched lyrics for this song yet, fetch them
        if (modal.dataset.songId !== currentSongId) {
            fetchLyrics(currentSongId);
        } else {
            // Re-sync immediately on open
            syncLyrics();
        }
    }
}

function fetchLyrics(songId) {
    const content = document.getElementById('lyrics-content');
    content.innerHTML = '<div class="lyric-line active" style="font-size: 1.5rem; text-align: center;">Memuat lirik...</div>';
    parsedLyrics = [];
    currentLyricIndex = -1;
    document.getElementById('lyrics-modal').dataset.songId = songId;
    
    fetch(`${BASE}/api/lyrics.php?id=${songId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.lyrics) {
                parseLRC(data.lyrics);
                renderLyrics();
            } else {
                content.innerHTML = `<div class="lyric-line active" style="font-size: 1.5rem; text-align: center;">${data.message || "Lirik tidak tersedia."}</div>`;
            }
        })
        .catch(err => {
            console.error("Gagal memuat lirik:", err);
            content.innerHTML = '<div class="lyric-line active" style="font-size: 1.5rem; text-align: center;">Terjadi kesalahan saat memuat lirik.</div>';
        });
}

function parseLRC(lrcText) {
    parsedLyrics = [];
    const lines = lrcText.split('\n');
    // Regex supports [mm:ss], [mm:ss.x], [mm:ss.xx], [mm:ss.xxx]
    const timeRegEx = /\[(\d{2}):(\d{2})(?:\.(\d{1,3}))?\]/;
    
    let hasTimeTags = false;

    lines.forEach(line => {
        const match = timeRegEx.exec(line);
        if (match) {
            hasTimeTags = true;
            const minutes = parseInt(match[1], 10);
            const seconds = parseInt(match[2], 10);
            
            let ms = 0;
            if (match[3]) {
                if (match[3].length === 1) ms = parseInt(match[3], 10) * 100;
                else if (match[3].length === 2) ms = parseInt(match[3], 10) * 10;
                else ms = parseInt(match[3], 10);
            }
            
            // Calculate total time in seconds
            const time = minutes * 60 + seconds + (ms / 1000);
            const text = line.replace(timeRegEx, '').trim();
            
            if (text) {
                parsedLyrics.push({ time, text });
            }
        }
    });

    // Sort by time just in case
    parsedLyrics.sort((a, b) => a.time - b.time);
    
    // Fallback if no LRC format is found, treat as plain text
    if (!hasTimeTags) {
        parsedLyrics = lines.filter(l => l.trim() !== '').map(text => ({ time: -1, text }));
    }
}

function renderLyrics() {
    const content = document.getElementById('lyrics-content');
    content.innerHTML = '';
    
    if (parsedLyrics.length === 0) {
        content.innerHTML = '<div class="lyric-line active" style="font-size: 1.5rem; text-align: center;">Lirik kosong.</div>';
        return;
    }
    
    // Append blank space at the top so first line is centered
    const topSpacer = document.createElement('div');
    topSpacer.style.height = '30vh';
    content.appendChild(topSpacer);

    parsedLyrics.forEach((lyric, index) => {
        const div = document.createElement('div');
        div.className = 'lyric-line';
        div.id = `lyric-${index}`;
        div.innerText = lyric.text;
        
        // If it's plain text (no time tags), just show them all
        if (lyric.time === -1) {
            div.classList.add('active');
            div.style.fontSize = '1.8rem';
            div.style.opacity = '0.8';
        } else {
            // Click to seek
            div.style.cursor = 'pointer';
            div.onclick = () => {
                audio.currentTime = lyric.time;
                audio.play();
            };
        }
        
        content.appendChild(div);
    });
    
    syncLyrics(); // Sync initially
}

function syncLyrics() {
    const modal = document.getElementById('lyrics-modal');
    if (modal.style.display !== 'flex' || parsedLyrics.length === 0 || parsedLyrics[0].time === -1) return;
    
    const currentTime = audio.currentTime;
    let newIndex = -1;
    
    // Find the current lyric index
    for (let i = 0; i < parsedLyrics.length; i++) {
        if (currentTime >= parsedLyrics[i].time) {
            newIndex = i;
        } else {
            break;
        }
    }
    
    if (newIndex !== currentLyricIndex) {
        // Remove active class from previous
        if (currentLyricIndex !== -1) {
            const prevEl = document.getElementById(`lyric-${currentLyricIndex}`);
            if (prevEl) prevEl.classList.remove('active');
        }
        
        // Add active class to new
        if (newIndex !== -1) {
            const newEl = document.getElementById(`lyric-${newIndex}`);
            if (newEl) {
                newEl.classList.add('active');
                
                // Auto-scroll logic: scroll the container so the active line is near the middle/top
                const container = document.getElementById('lyrics-container');
                const scrollPos = newEl.offsetTop - (container.clientHeight / 2) + 60;
                
                container.scrollTo({
                    top: scrollPos > 0 ? scrollPos : 0,
                    behavior: 'smooth'
                });
            }
        }
        
        currentLyricIndex = newIndex;
    }
}

// Add timeupdate listener for lyrics sync if audio exists
if (audio) {
    audio.addEventListener('timeupdate', syncLyrics);
}

// ============================================================
// SPA / PJAX Router for Continuous Playback
// ============================================================
async function navigateTo(url, push = true) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract main content and sidebar
        const newMain = doc.querySelector('.main-content');
        const newSidebar = doc.querySelector('.sidebar');
        
        if (newMain) {
            document.querySelector('.main-content').innerHTML = newMain.innerHTML;
            
            if (newSidebar) {
                document.querySelector('.sidebar').innerHTML = newSidebar.innerHTML;
            }
            
            // Push state
            if (push) {
                window.history.pushState({}, '', url);
            }
            
            // Scroll to top
            document.querySelector('.main-content').scrollTop = 0;
            
            // Update greeting if on home page
            updateGreeting();
            
            // Re-apply highlight to currently playing song if it's on this page
            if (currentSongId) {
                const row = document.getElementById(`song-row-${currentSongId}`);
                if (row) row.classList.add('playing');
                const card = document.getElementById(`song-card-${currentSongId}`);
                if (card) card.classList.add('playing');
            }
        } else {
            // Fallback for non-PJAX compatible pages
            window.location.href = url;
        }
    } catch (e) {
        console.error('SPA Navigation Error:', e);
        window.location.href = url; // Fallback
    }
}

// Handle back/forward buttons natively
window.addEventListener('popstate', () => {
    navigateTo(window.location.href, false);
});

// Intercept all internal link clicks
document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (a && a.href && a.href.startsWith(window.location.origin) && !a.hasAttribute('target')) {
        e.preventDefault();
        navigateTo(a.href);
    }
});

// Dynamic Greeting based on Local Time
function updateGreeting() {
    const greetingEl = document.querySelector('.hero-greeting');
    if (greetingEl) {
        const hour = new Date().getHours();
        let text = "Selamat Malam";
        if (hour >= 0 && hour < 12) text = "Selamat Pagi";
        else if (hour >= 12 && hour < 15) text = "Selamat Siang";
        else if (hour >= 15 && hour < 18) text = "Selamat Sore";
        greetingEl.innerText = text;
    }
}

// Run on initial load
document.addEventListener('DOMContentLoaded', updateGreeting);
updateGreeting(); // Run immediately in case DOM is already loaded

// Live Search Suggestions
let searchSuggestTimeout;
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('live-search-input')) {
        const query = e.target.value.trim();
        const suggestionsBox = document.getElementById('search-suggestions');
        
        clearTimeout(searchSuggestTimeout);
        
        if (query.length === 0) {
            if (suggestionsBox) suggestionsBox.style.display = 'none';
            return;
        }
        
        searchSuggestTimeout = setTimeout(() => {
            const baseUrlJS = BASE;
            fetch(baseUrlJS + '/api/search_suggest.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(song => {
                            const titleStr = song.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            const artistStr = song.artist.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            const playArgs = `${song.id}, '${titleStr}', '${artistStr}', '${song.cover}', '${song.file_path}'`;
                            html += `
                                <div class="suggestion-item" onclick="playSong(${playArgs}); document.getElementById('search-suggestions').style.display='none';">
                                    <img src="${song.cover}" class="suggestion-thumb" onerror="this.src='${baseUrlJS}/assets/images/default_cover.svg'">
                                    <div class="suggestion-info">
                                        <div class="suggestion-title">${song.title}</div>
                                        <div class="suggestion-sub">${song.artist}</div>
                                    </div>
                                    <i class="fa-solid fa-play" style="color: var(--primary); font-size: 0.8rem; opacity: 0.5;"></i>
                                </div>
                            `;
                        });
                        if (suggestionsBox) {
                            suggestionsBox.innerHTML = html;
                            suggestionsBox.style.display = 'flex';
                        }
                    } else {
                        if (suggestionsBox) suggestionsBox.style.display = 'none';
                    }
                })
                .catch(err => console.error("Search suggest error:", err));
        }, 300); // 300ms debounce
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-bar-wrap')) {
        const suggestionsBox = document.getElementById('search-suggestions');
        if (suggestionsBox) suggestionsBox.style.display = 'none';
    }
});

// ============================================================
// Playlist Feature — Create / Add-to-Playlist / Remove
// ============================================================


// ── Create Playlist Modal ────────────────────────────────────
function openCreatePlaylistModal() {
    const modal = document.getElementById('create-playlist-modal');
    if (!modal) return;
    document.getElementById('cp-name').value = '';
    document.getElementById('cp-desc').value = '';
    document.getElementById('cp-public').checked = false;
    document.getElementById('cp-error').style.display = 'none';
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('cp-name').focus(), 100);
}
window.openCreatePlaylistModal = openCreatePlaylistModal;

function closeCreatePlaylistModal() {
    const modal = document.getElementById('create-playlist-modal');
    if (modal) modal.style.display = 'none';
}
window.closeCreatePlaylistModal = closeCreatePlaylistModal;

function submitCreatePlaylist() {
    const name    = document.getElementById('cp-name').value.trim();
    const desc    = document.getElementById('cp-desc').value.trim();
    const isPublic = document.getElementById('cp-public').checked ? 1 : 0;
    const errEl   = document.getElementById('cp-error');
    const submitBtn = document.getElementById('cp-submit');

    if (!name) {
        errEl.textContent = 'Nama playlist tidak boleh kosong.';
        errEl.style.display = 'block';
        document.getElementById('cp-name').focus();
        return;
    }
    errEl.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat...';

    const fd = new FormData();
    fd.append('action', 'create');
    fd.append('name', name);
    fd.append('description', desc);
    if (isPublic) fd.append('is_public', '1');

    fetch(BASE + '/api/playlist.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-plus" style="margin-right:6px;"></i> Buat Playlist';
            if (data.success) {
                closeCreatePlaylistModal();
                showToast('Playlist "' + name + '" berhasil dibuat! 🎵', 'success');
                // Inject new playlist into sidebar without reload
                const container = document.querySelector('.sidebar-playlists');
                if (container) {
                    const div = document.createElement('div');
                    div.className = 'playlist-item';
                    div.onclick = () => navigateTo(BASE + '/playlist?id=' + data.id);
                    div.textContent = name;
                    container.insertBefore(div, container.firstChild);
                }
                // If on library page, navigate to refresh it
                if (window.location.search.includes('page=library') || window.location.pathname.includes('/library')) {
                    navigateTo(BASE + '/library');
                }
            } else {
                errEl.textContent = data.message || 'Terjadi kesalahan.';
                errEl.style.display = 'block';
            }
        })
        .catch(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-plus" style="margin-right:6px;"></i> Buat Playlist';
            errEl.textContent = 'Gagal terhubung ke server.';
            errEl.style.display = 'block';
        });
}
window.submitCreatePlaylist = submitCreatePlaylist;

// ── Add to Playlist Modal ────────────────────────────────────
let atpSongId = null;

function openAddToPlaylistModal(songId, songTitle, songArtist, songCover) {
    atpSongId = songId;
    document.getElementById('atp-title').textContent  = songTitle;
    document.getElementById('atp-artist').textContent = songArtist;
    document.getElementById('atp-cover').src          = songCover;

    const listEl = document.getElementById('atp-list');
    listEl.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i></div>';

    const modal = document.getElementById('add-to-playlist-modal');
    if (modal) modal.style.display = 'flex';

    fetch(BASE + '/api/playlist.php?action=list', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (!data.success || data.playlists.length === 0) {
                listEl.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:0.9rem;">Belum ada playlist.<br>Buat playlist baru di bawah.</div>';
                return;
            }
            listEl.innerHTML = '';
            data.playlists.forEach(pl => {
                const btn = document.createElement('button');
                btn.style.cssText = 'width:100%;padding:10px 14px;border-radius:9px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:#fff;font-size:0.9rem;font-weight:500;cursor:pointer;text-align:left;transition:all 0.2s;display:flex;align-items:center;gap:10px;';
                btn.innerHTML = '<i class="fa-solid fa-list-music" style="color:var(--primary);font-size:0.85rem;"></i>' + pl.name;
                btn.onmouseover = () => { btn.style.background = 'rgba(255,255,255,0.09)'; };
                btn.onmouseout  = () => { btn.style.background = 'rgba(255,255,255,0.04)'; };
                btn.onclick = () => addSongToPlaylist(pl.id, pl.name, btn);
                listEl.appendChild(btn);
            });
        });
}
window.openAddToPlaylistModal = openAddToPlaylistModal;

function closeAddToPlaylistModal() {
    const modal = document.getElementById('add-to-playlist-modal');
    if (modal) modal.style.display = 'none';
    atpSongId = null;
}
window.closeAddToPlaylistModal = closeAddToPlaylistModal;

function addSongToPlaylist(playlistId, playlistName, btnEl) {
    if (!atpSongId) return;
    if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="color:var(--primary)"></i> Menambahkan...'; }

    const fd = new FormData();
    fd.append('action', 'add_song');
    fd.append('playlist_id', playlistId);
    fd.append('song_id', atpSongId);

    fetch(BASE + '/api/playlist.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeAddToPlaylistModal();
                showToast('Ditambahkan ke "' + playlistName + '" 🎶', 'success');
            } else {
                if (btnEl) { btnEl.disabled = false; btnEl.innerHTML = '<i class="fa-solid fa-list-music" style="color:var(--primary);font-size:0.85rem;"></i>' + playlistName; }
                showToast(data.message || 'Gagal menambahkan lagu.', 'error');
            }
        });
}
window.addSongToPlaylist = addSongToPlaylist;

// ── Remove Song from Playlist ────────────────────────────────
function removeSongFromPlaylist(playlistId, songId, rowEl) {
    if (!confirm('Hapus lagu ini dari playlist?')) return;
    const fd = new FormData();
    fd.append('action', 'remove_song');
    fd.append('playlist_id', playlistId);
    fd.append('song_id', songId);

    fetch(BASE + '/api/playlist.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (rowEl) rowEl.remove();
                showToast('Lagu dihapus dari playlist.', 'success');
            } else {
                showToast(data.message || 'Gagal menghapus.', 'error');
            }
        });
}
window.removeSongFromPlaylist = removeSongFromPlaylist;

// ── Delete Playlist ──────────────────────────────────────────
function deletePlaylist(playlistId) {
    if (!confirm('Yakin ingin menghapus playlist ini? Semua lagu di dalamnya juga akan dihapus dari playlist.')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('playlist_id', playlistId);

    fetch(BASE + '/api/playlist.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Playlist berhasil dihapus.', 'success');
                navigateTo(BASE + '/library');
            } else {
                showToast(data.message || 'Gagal menghapus playlist.', 'error');
            }
        });
}
window.deletePlaylist = deletePlaylist;

function addCurrentSongToPlaylist() {
    if (!currentSongId) return;
    const title = document.getElementById('player-title').innerText;
    const artist = document.getElementById('player-artist').innerText;
    const cover = document.getElementById('player-cover-img').src;
    openAddToPlaylistModal(currentSongId, title, artist, cover);
}
window.addCurrentSongToPlaylist = addCurrentSongToPlaylist;

function uploadPlaylistCover(playlistId, inputEl) {
    const file = inputEl.files[0];
    if (!file) return;

    const span = document.querySelector('.cover-overlay span');
    const icon = document.querySelector('.cover-overlay i');
    const originalText = span ? span.textContent : 'Ubah Foto';
    const originalIconClass = icon ? icon.className : 'fa-solid fa-camera';

    if (span) span.textContent = 'Mengunggah...';
    if (icon) icon.className = 'fa-solid fa-spinner fa-spin';

    const fd = new FormData();
    fd.append('action', 'upload_cover');
    fd.append('playlist_id', playlistId);
    fd.append('cover', file);

    fetch(BASE + '/api/playlist.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (span) span.textContent = originalText;
            if (icon) icon.className = originalIconClass;
            inputEl.value = ''; // Reset file input

            if (data.success) {
                const img = document.getElementById('playlist-cover-img-page');
                if (img) {
                    // Append random query param to bypass cache
                    img.src = data.cover_url + '?t=' + new Date().getTime();
                }
                showToast('Cover playlist berhasil diubah! 📸', 'success');
            } else {
                showToast(data.message || 'Gagal mengubah cover.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (span) span.textContent = originalText;
            if (icon) icon.className = originalIconClass;
            inputEl.value = '';
            showToast('Gagal terhubung ke server.', 'error');
        });
}
window.uploadPlaylistCover = uploadPlaylistCover;



