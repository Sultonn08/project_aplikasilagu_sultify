<?php
$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, u.full_name AS owner_name
    FROM playlists p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$playlist = $stmt->fetch();

if (!$playlist) {
    echo "<div style='text-align:center;margin-top:100px;'><h1>Playlist tidak ditemukan</h1><a href='".BASE_URL."' class='btn-login' style='margin-top:20px;display:inline-block;'>Kembali</a></div>";
    exit;
}

if (!$playlist['is_public'] && (!isLoggedIn() || $_SESSION['user_id'] != $playlist['user_id'])) {
    echo "<div style='text-align:center;margin-top:100px;'><h1>Playlist ini bersifat privat</h1></div>";
    exit;
}

// Get songs in playlist
$stmtSongs = $pdo->prepare("
    SELECT s.id AS song_id, s.title, s.cover, s.file_path, s.duration,
           a.name AS artist_name, al.title AS album_title
    FROM playlist_songs ps
    JOIN songs s ON ps.song_id = s.id
    LEFT JOIN artists a ON s.artist_id = a.id
    LEFT JOIN albums al ON s.album_id = al.id
    WHERE ps.playlist_id = ?
    ORDER BY ps.id ASC
");
$stmtSongs->execute([$id]);
$songs = $stmtSongs->fetchAll();

$totalDuration = array_sum(array_column($songs, 'duration'));
$isOwner = isLoggedIn() && $_SESSION['user_id'] == $playlist['user_id'];
?>

<div class="playlist-hero fade-in">
    <div class="playlist-cover-lg <?= $isOwner ? 'editable-cover' : '' ?>" <?= $isOwner ? 'onclick="document.getElementById(\'playlist-cover-input\').click()"' : '' ?>>
        <img id="playlist-cover-img-page" src="<?= getCoverUrl($playlist['cover'] ?? null) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
        <?php if ($isOwner): ?>
            <div class="cover-overlay">
                <i class="fa-solid fa-camera"></i>
                <span>Ubah Foto</span>
            </div>
            <input type="file" id="playlist-cover-input" accept="image/png, image/jpeg, image/webp" style="display:none;" onchange="uploadPlaylistCover(<?= $playlist['id'] ?>, this)">
        <?php endif; ?>
    </div>
    <div class="playlist-hero-info">
        <div class="ph-type"><?= $playlist['is_public'] ? 'Playlist Publik' : 'Playlist Privat' ?></div>
        <div class="ph-title"><?= htmlspecialchars($playlist['name']) ?></div>
        <?php if (!empty($playlist['description'])): ?>
            <div class="ph-meta" style="margin-top:10px;font-size:0.95rem;opacity:0.85;">
                <?= htmlspecialchars($playlist['description']) ?>
            </div>
        <?php endif; ?>
        <div class="ph-meta" style="margin-top:10px;">
            <span style="font-weight:700;color:#fff;"><?= htmlspecialchars($playlist['owner_name']) ?></span>
            &nbsp;•&nbsp; <?= count($songs) ?> lagu
            &nbsp;•&nbsp; <?= floor($totalDuration/60) ?> menit
        </div>
    </div>
</div>

<div style="display:flex;gap:16px;margin-bottom:28px;align-items:center;" class="fade-in">
    <?php if (!empty($songs)): ?>
    <button class="play-btn" style="width:56px;height:56px;font-size:1.3rem;" 
            onclick="document.querySelector('.song-list tbody tr')?.click()">
        <i class="fa-solid fa-play"></i>
    </button>
    <?php endif; ?>
    <?php if ($isOwner): ?>
    <button onclick="deletePlaylist(<?= $playlist['id'] ?>)"
            style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:30px;border:1px solid rgba(255,60,60,0.3);background:rgba(255,60,60,0.08);color:#ff7070;font-weight:600;cursor:pointer;font-size:0.9rem;transition:all 0.2s;"
            onmouseover="this.style.background='rgba(255,60,60,0.2)'" onmouseout="this.style.background='rgba(255,60,60,0.08)'">
        <i class="fa-solid fa-trash"></i> Hapus Playlist
    </button>
    <?php endif; ?>
</div>

<table class="song-list fade-in" style="animation-delay:0.1s;">
    <thead>
        <tr>
            <th width="40">#</th>
            <th>Judul</th>
            <th>Album</th>
            <th style="text-align:right;"><i class="fa-regular fa-clock"></i></th>
            <?php if ($isOwner): ?>
            <th style="width:50px;"></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($songs)): ?>
        <tr>
            <td colspan="<?= $isOwner ? 5 : 4 ?>" style="text-align:center;padding:60px;color:var(--text-muted);">
                <i class="fa-solid fa-music" style="font-size:2.5rem;opacity:0.2;display:block;margin-bottom:12px;"></i>
                Playlist ini belum memiliki lagu.
            </td>
        </tr>
        <?php else: ?>
        <?php foreach($songs as $index => $song):
            $playArgs = "{$song['song_id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover']) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'";
        ?>
        <tr id="song-row-<?= $song['song_id'] ?>" onclick="playSong(<?= $playArgs ?>)">
            <td class="song-num"><?= $index + 1 ?></td>
            <td>
                <div class="song-info">
                    <div class="song-thumb">
                        <img src="<?= getCoverUrl($song['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    </div>
                    <div>
                        <div class="song-name"><?= htmlspecialchars($song['title']) ?></div>
                        <div class="song-artist"><?= htmlspecialchars($song['artist_name'] ?? '—') ?></div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-muted);font-size:0.85rem;"><?= htmlspecialchars($song['album_title'] ?? '—') ?></td>
            <td class="song-duration"><?= formatDuration($song['duration']) ?></td>
            <?php if ($isOwner): ?>
            <td onclick="event.stopPropagation(); removeSongFromPlaylist(<?= $playlist['id'] ?>, <?= $song['song_id'] ?>, this.closest('tr'))"
                title="Hapus dari playlist"
                style="cursor:pointer;color:rgba(255,100,100,0.5);font-size:1rem;text-align:center;padding:0 12px;"
                onmouseover="this.style.color='#ff5555'" onmouseout="this.style.color='rgba(255,100,100,0.5)'">
                <i class="fa-solid fa-xmark"></i>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
