<?php
$pdo = getDB();
$q = $_GET['q'] ?? '';

$songs = [];
$artists = [];
$genres = [];

if ($q) {
    // Cari Lagu
    $stmt = $pdo->prepare("
        SELECT s.id, s.title, s.cover, s.file_path, s.duration, s.genre, 
               a.name AS artist_name, al.title AS album_title 
        FROM songs s 
        LEFT JOIN artists a ON s.artist_id = a.id 
        LEFT JOIN albums al ON s.album_id = al.id 
        WHERE s.title LIKE ? OR a.name LIKE ? OR s.genre LIKE ?
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    $songs = $stmt->fetchAll();
    
    // Cari Artis
    $stmt2 = $pdo->prepare("SELECT * FROM artists WHERE name LIKE ?");
    $stmt2->execute(["%$q%"]);
    $artists = $stmt2->fetchAll();
} else {
    // Jika tidak ada query, ambil daftar genre yang tersedia
    $stmt = $pdo->query("SELECT DISTINCT genre FROM songs WHERE genre IS NOT NULL AND genre != ''");
    $genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="search-page" style="padding-top: 24px;">
    <!-- Search Bar Form -->
    <div class="search-bar-wrap" style="max-width: 500px; margin-bottom: 40px;">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <form action="" method="GET" onsubmit="event.preventDefault(); navigateTo('<?= BASE_URL ?>/search?q=' + encodeURIComponent(this.q.value));">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" class="search-input live-search-input" placeholder="Artis, lagu, atau genre apa yang ingin kamu putar?" value="<?= htmlspecialchars($q) ?>" autofocus autocomplete="off">
        </form>
        <div id="search-suggestions" class="search-suggestions"></div>
    </div>

    <?php if ($q): ?>
        <h2 class="section-title">Hasil untuk "<?= htmlspecialchars($q) ?>"</h2>
        
        <?php if (empty($songs) && empty($artists)): ?>
            <div style="text-align: center; color: var(--text-muted); margin-top: 60px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.5;"></i>
                <h3>Tidak ditemukan hasil untuk "<?= htmlspecialchars($q) ?>"</h3>
                <p style="font-size: 0.9rem; margin-top: 8px;">Coba periksa ejaanmu atau gunakan kata kunci lain.</p>
            </div>
        <?php endif; ?>

        <!-- Hasil Artis -->
        <?php if (!empty($artists)): ?>
        <section class="section fade-in">
            <h3 style="margin-bottom: 16px; font-size: 1.2rem;">Artis</h3>
            <div class="cards-grid">
                <?php foreach($artists as $art): ?>
                    <div class="card" onclick="navigateTo('<?= BASE_URL ?>/artist?id=<?= $art['id'] ?>')">
                        <div class="card-cover round" style="box-shadow: 0 8px 24px rgba(0,0,0,.5);">
                            <img src="<?= getCoverUrl($art['photo']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                        </div>
                        <div class="card-title" style="text-align: center; font-size: 1.1rem; margin-bottom: 4px;"><?= htmlspecialchars($art['name']) ?></div>
                        <div class="card-sub" style="text-align: center;">Artis</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Hasil Lagu -->
        <?php if (!empty($songs)): ?>
        <section class="section fade-in" style="animation-delay: .1s;">
            <h3 style="margin-bottom: 16px; font-size: 1.2rem;">Lagu</h3>
            <table class="song-list">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Judul</th>
                        <th>Album</th>
                        <th style="text-align: right;"><i class="fa-regular fa-clock"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($songs as $index => $song): ?>
                        <?php $playArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover']) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
                        <tr id="song-row-<?= $song['id'] ?>" onclick="playSong(<?= $playArgs ?>)">
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
                            <td style="color: var(--text-muted); font-size: .875rem;"><?= htmlspecialchars($song['album_title'] ?? 'Single') ?></td>
                            <td class="song-duration"><?= formatDuration($song['duration']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

    <?php else: ?>
        <!-- Jelajahi Semua Genre -->
        <section class="section fade-in">
            <h2 class="section-title">Jelajahi Semua Genre</h2>
            <div class="genre-grid">
                <?php 
                $colors = ['#E13300', '#7358FF', '#1E3264', '#E8115B', '#8D67AB', '#148A08', '#F59B23', '#537AA1'];
                foreach($genres as $i => $genre): 
                    $color = $colors[$i % count($colors)];
                ?>
                    <div class="genre-chip" style="background-color: <?= $color ?>;" onclick="navigateTo('<?= BASE_URL ?>/search?q=<?= urlencode($genre) ?>')">
                        <?= htmlspecialchars($genre) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
