<div class="player">
    <div class="player-left">
        <div class="player-song-info" id="player-info" style="display: none;">
            <div class="player-cover">
                <img id="player-cover-img" src="" alt="Cover" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
            </div>
            <div>
                <div class="player-song-name" id="player-title">Judul Lagu</div>
                <div class="player-song-artist" id="player-artist">Nama Artis</div>
            </div>
            <button class="player-like-btn" id="player-like" onclick="toggleLike()"><i class="fa-regular fa-heart"></i></button>
            <button class="player-like-btn" id="player-add-to-playlist" onclick="addCurrentSongToPlaylist()" title="Tambah ke Playlist" style="margin-left: 12px; color: var(--text-muted);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'"><i class="fa-solid fa-circle-plus"></i></button>
        </div>
    </div>

    <div class="player-center">
        <div class="player-controls">
            <div class="player-btns">
                <button class="ctrl-btn" id="btn-shuffle"><i class="fa-solid fa-shuffle"></i></button>
                <button class="ctrl-btn" id="btn-prev"><i class="fa-solid fa-backward-step"></i></button>
                <button class="play-btn" id="btn-play"><i class="fa-solid fa-play"></i></button>
                <button class="ctrl-btn" id="btn-next"><i class="fa-solid fa-forward-step"></i></button>
                <button class="ctrl-btn" id="btn-repeat"><i class="fa-solid fa-repeat"></i></button>
            </div>
            <div class="progress-bar-wrap">
                <span class="progress-time" id="time-current">0:00</span>
                <div class="progress-track" id="progress-track">
                    <div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
                </div>
                <span class="progress-time" id="time-total">0:00</span>
            </div>
        </div>
    </div>

    <div class="player-right">
        <button class="queue-btn" title="Lirik" id="btn-lyrics" onclick="toggleLyrics()"><i class="fa-solid fa-microphone-lines"></i></button>
        <button class="queue-btn" title="Antrean"><i class="fa-solid fa-list-ul"></i></button>
        <i class="fa-solid fa-volume-high" style="color: var(--text-muted); font-size: .9rem;"></i>
        <input type="range" class="vol-slider" id="vol-slider" min="0" max="1" step="0.01" value="1">
    </div>

    <!-- Elemen Audio Native (Tersembunyi) -->
    <audio id="audio-element" src=""></audio>
</div>

<!-- Modal Lirik (Fullscreen Synced) -->
<div id="lyrics-modal" class="lyrics-overlay">
    <div class="lyrics-overlay-header">
        <button class="lyrics-close-btn" onclick="toggleLyrics()"><i class="fa-solid fa-chevron-down"></i></button>
    </div>
    <div class="lyrics-split">
        <div class="lyrics-cover-col">
            <img id="lyrics-cover-img" src="" alt="Album Cover">
        </div>
        <div id="lyrics-container" class="lyrics-container">
            <div id="lyrics-content">
                <div class="lyric-line active" style="font-size: 1.5rem; text-align: center;">Memuat lirik...</div>
            </div>
        </div>
    </div>
</div>
