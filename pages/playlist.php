<?php
$pdo = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM v_playlist_detail WHERE id = ?");
$stmt->execute([$id]);
$playlist = $stmt->fetch();

if (!$playlist) {
    echo "<div style='text-align: center; margin-top: 100px;'><h1>Playlist tidak ditemukan</h1><a href='".BASE_URL."' class='btn-login' style='margin-top:20px; display:inline-block;'>Kembali</a></div>";
    exit;
}

// Cek privasi playlist
if (!$playlist['is_public'] && (!isLoggedIn() || $_SESSION['user_id'] != $playlist['user_id'])) {
     echo "<div style='text-align: center; margin-top: 100px;'><h1>Playlist ini bersifat privat</h1></div>";
     exit;
}

// Ambil lagu-lagu di dalam playlist via Procedure (diperlukan cursor reset)
$stmt2 = $pdo->prepare("CALL sp_get_playlist_songs(?)");
$stmt2->execute([$id]);
$songs = $stmt2->fetchAll();
$stmt2->closeCursor(); // Bersihkan cursor MySQL sebelum query berikutnya bila ada
?>

</div> 
<div class="playlist-hero fade-in">
    <div class="playlist-cover-lg">
        <img src="<?= getCoverUrl($playlist['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
    </div>
    <div class="playlist-hero-info">
        <div class="ph-type"><?= $playlist['is_public'] ? 'Playlist Publik' : 'Playlist Privat' ?></div>
        <div class="ph-title"><?= htmlspecialchars($playlist['name']) ?></div>
        <?php if (!empty($playlist['description'])): ?>
            <div class="ph-meta" style="margin-top: 12px; font-size: 0.95rem;">
                <?= htmlspecialchars($playlist['description']) ?>
            </div>
        <?php endif; ?>
        <div class="ph-meta" style="margin-top: 8px;">
            <span style="font-weight: 700; color: #fff;"><?= htmlspecialchars($playlist['owner_name']) ?></span> • 
            <?= count($songs) ?> lagu, sekitar <?= formatDuration($playlist['total_duration']) ?> menit
        </div>
    </div>
</div>
<div class="page-content" style="margin-top: 20px;">

<div style="display: flex; gap: 24px; margin-bottom: 30px; align-items: center;" class="fade-in">
    <button class="play-btn" style="width: 56px; height: 56px; font-size: 1.3rem;" onclick="document.querySelector('.song-list tr').click()">
        <i class="fa-solid fa-play"></i>
    </button>
    <button class="player-like-btn" style="font-size: 2rem; margin: 0;"><i class="fa-regular fa-heart"></i></button>
    <i class="fa-solid fa-ellipsis" style="font-size: 1.5rem; color: var(--text-muted); cursor: pointer;" title="Opsi"></i>
</div>

<table class="song-list fade-in" style="animation-delay: 0.1s;">
    <thead>
        <tr>
            <th width="40">#</th>
            <th>Judul</th>
            <th style="text-align: right;"><i class="fa-regular fa-clock"></i></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($songs as $index => $song): ?>
            <?php $playArgs = "{$song['song_id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover']) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
            <tr id="song-row-<?= $song['song_id'] ?>" onclick="playSong(<?= $playArgs ?>)">
                <td class="song-num"><?= $index + 1 ?></td>
                <td>
                    <div class="song-info">
                        <div class="song-thumb">
                            <img src="<?= getCoverUrl($song['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                        </div>
                        <div>
                            <div class="song-name"><?= htmlspecialchars($song['title']) ?></div>
                            <div class="song-artist"><?= htmlspecialchars($song['artist_name']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="song-duration"><?= formatDuration($song['duration']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($songs)): ?>
            <tr>
                <td colspan="3" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Playlist ini belum memiliki lagu.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
