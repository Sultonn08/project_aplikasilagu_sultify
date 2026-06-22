<?php
requireLogin();
$pdo = getDB();

$stmt = $pdo->prepare("SELECT s.*, a.name as artist_name, al.title as album_title, al.cover as album_cover, f.created_at as added_at
                       FROM favorites f 
                       JOIN songs s ON f.song_id = s.id 
                       JOIN artists a ON s.artist_id = a.id 
                       LEFT JOIN albums al ON s.album_id = al.id 
                       WHERE f.user_id = ? 
                       ORDER BY f.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$songs = $stmt->fetchAll();

$stmtU = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmtU->execute([$_SESSION['user_id']]);
$user = $stmtU->fetch();
?>

<!-- Playlist Hero Header -->
</div> 
<div class="playlist-hero fade-in" style="background: linear-gradient(180deg, #450af5 0%, rgba(18,18,18,0) 100%);">
    <div class="playlist-cover-lg" style="background: linear-gradient(135deg, #450af5, #c4efd9); color: white;">
        <i class="fa-solid fa-heart" style="font-size: 6rem;"></i>
    </div>
    <div class="playlist-hero-info">
        <div class="ph-type">Playlist</div>
        <div class="ph-title" style="font-size: 4rem;">Lagu yang Disukai</div>
        <div class="ph-meta">
            <span style="font-weight: 700; color: #fff;"><?= htmlspecialchars($user['full_name']) ?></span> • <?= count($songs) ?> lagu
        </div>
    </div>
</div>
<div class="page-content" style="margin-top: 20px;">

<div style="display: flex; gap: 24px; margin-bottom: 30px; align-items: center;" class="fade-in">
    <button class="play-btn" style="width: 56px; height: 56px; font-size: 1.3rem;" onclick="document.querySelector('.song-list tr').click()">
        <i class="fa-solid fa-play"></i>
    </button>
</div>

<?php if (empty($songs)): ?>
    <div style="text-align: center; color: var(--text-muted); margin-top: 60px;">
        <i class="fa-regular fa-heart" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;"></i>
        <h3>Lagu yang kamu sukai akan muncul di sini</h3>
        <p style="font-size: 0.9rem; margin-top: 8px;">Simpan lagu dengan mengetuk ikon hati.</p>
        <button class="btn-signup" style="margin-top: 24px;" onclick="navigateTo('<?= BASE_URL ?>/search')">Cari Lagu</button>
    </div>
<?php else: ?>
    <table class="song-list fade-in" style="animation-delay: 0.1s;">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>Judul</th>
                <th>Album</th>
                <th>Tanggal Ditambahkan</th>
                <th style="text-align: right;"><i class="fa-regular fa-clock"></i></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($songs as $index => $song): ?>
                <?php $playArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover'] ?? $song['album_cover'] ?? null) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
                <tr id="song-row-<?= $song['id'] ?>" onclick="playSong(<?= $playArgs ?>)">
                    <td class="song-num"><?= $index + 1 ?></td>
                    <td>
                        <div class="song-info">
                            <div class="song-thumb">
                                <img src="<?= getCoverUrl($song['cover'] ?? $song['album_cover'] ?? null) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                            </div>
                            <div>
                                <div class="song-name" style="color: var(--primary);"><?= htmlspecialchars($song['title']) ?></div>
                                <div class="song-artist"><?= htmlspecialchars($song['artist_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="color: var(--text-muted); font-size: .875rem;"><?= htmlspecialchars($song['album_title'] ?? 'Single') ?></td>
                    <td style="color: var(--text-muted); font-size: .875rem;"><?= date('M j, Y', strtotime($song['added_at'])) ?></td>
                    <td class="song-duration"><?= formatDuration($song['duration']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
