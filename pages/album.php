<?php
$pdo = getDB();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT al.*, a.name as artist_name 
                       FROM albums al 
                       JOIN artists a ON al.artist_id = a.id 
                       WHERE al.id = ?");
$stmt->execute([$id]);
$album = $stmt->fetch();

if (!$album) {
    echo "<div style='text-align: center; margin-top: 100px;'><h1>Album tidak ditemukan</h1><a href='".BASE_URL."' class='btn-login' style='margin-top:20px; display:inline-block;'>Kembali</a></div>";
    exit;
}

$stmt2 = $pdo->prepare("SELECT * FROM songs WHERE album_id = ? ORDER BY track_number ASC, id ASC");
$stmt2->execute([$id]);
$songs = $stmt2->fetchAll();

// Total duration
$totalDuration = 0;
foreach($songs as $s) $totalDuration += $s['duration'];
?>

</div> 
<div class="playlist-hero fade-in">
    <div class="playlist-cover-lg" style="border-radius: 4px; box-shadow: 0 16px 40px rgba(0,0,0,0.6);">
        <img src="<?= getCoverUrl($album['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
    </div>
    <div class="playlist-hero-info">
        <div class="ph-type"><?= htmlspecialchars($album['album_type']) ?></div>
        <div class="ph-title"><?= htmlspecialchars($album['title']) ?></div>
        <div class="ph-meta" style="margin-top: 8px;">
            <span style="font-weight: 700; color: #fff;">
                <a href="<?= BASE_URL ?>/artist?id=<?= $album['artist_id'] ?>" style="text-decoration: underline; text-underline-offset: 4px;"><?= htmlspecialchars($album['artist_name']) ?></a>
            </span> • 
            <?= date('Y', strtotime($album['release_date'])) ?> • <?= count($songs) ?> lagu, sekitar <?= formatDuration($totalDuration) ?> menit
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
            <th width="40" style="text-align: center;">#</th>
            <th>Judul Lagu</th>
            <th style="text-align: right; padding-right: 24px;"><i class="fa-regular fa-clock"></i></th>
            <th style="width: 50px;"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($songs as $index => $song): ?>
            <?php $playArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($album['artist_name']) . "', '" . getCoverUrl($song['cover'] ?? $album['cover']) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
            <?php $addArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($album['artist_name']) . "', '" . getCoverUrl($song['cover'] ?? $album['cover']) . "'"; ?>
            <tr id="song-row-<?= $song['id'] ?>" onclick="playSong(<?= $playArgs ?>)">
                <td class="song-num" style="text-align: center;"><?= $song['track_number'] ?? ($index + 1) ?></td>
                <td>
                    <div class="song-info">
                        <div class="song-name"><?= htmlspecialchars($song['title']) ?></div>
                    </div>
                </td>
                <td class="song-duration" style="padding-right: 24px;"><?= formatDuration($song['duration']) ?></td>
                <td onclick="event.stopPropagation(); openAddToPlaylistModal(<?= $addArgs ?>)"
                    title="Tambah ke Playlist"
                    style="cursor:pointer;text-align:center;color:var(--text-muted);font-size:0.9rem;"
                    onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="fa-solid fa-circle-plus"></i>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
