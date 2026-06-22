<?php
$pdo = getDB();

// Ambil lagu teratas (Top Songs)
$stmt = $pdo->query("SELECT * FROM v_top_songs LIMIT 6");
$topSongs = $stmt->fetchAll();

// Ambil rilis terbaru (Album)
$stmt2 = $pdo->query("SELECT a.id, a.title, a.slug, a.cover, ar.name as artist_name 
                      FROM albums a 
                      JOIN artists ar ON a.artist_id = ar.id 
                      WHERE a.is_published = 1 
                      ORDER BY a.release_date DESC LIMIT 6");
$recentAlbums = $stmt2->fetchAll();

// Ambil artis populer
$stmt3 = $pdo->query("SELECT * FROM artists ORDER BY created_at DESC, monthly_listeners DESC LIMIT 12");
$topArtists = $stmt3->fetchAll();
?>

<!-- Hero Banner -->
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-greeting">
            <?php 
                $h = date('H');
                if ($h < 12) echo "Selamat Pagi";
                elseif ($h < 15) echo "Selamat Siang";
                elseif ($h < 18) echo "Selamat Sore";
                else echo "Selamat Malam";
            ?>
        </h1>
        <p class="hero-sub">Temukan jutaan lagu eksklusif dengan kualitas premium.</p>
    </div>
</section>

<!-- Quick Play (Top Songs) -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title">Paling Sering Diputar</h2>
    </div>
    <div class="quick-grid">
        <?php foreach($topSongs as $song): ?>
            <?php $playArgs = "{$song['id']}, '" . addslashes($song['title']) . "', '" . addslashes($song['artist_name']) . "', '" . getCoverUrl($song['cover'] ?? $song['album_cover'] ?? null) . "', '" . BASE_URL . "/uploads/" . $song['file_path'] . "'"; ?>
            <div class="quick-card" id="song-card-<?= $song['id'] ?>" onclick="playSong(<?= $playArgs ?>)">
                <div class="quick-card-cover">
                    <img src="<?= getCoverUrl($song['cover'] ?? $song['album_cover'] ?? null) ?>" alt="<?= htmlspecialchars($song['title']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                </div>
                <div class="quick-card-name" title="<?= htmlspecialchars($song['title']) ?>"><?= htmlspecialchars($song['title']) ?></div>
                <div class="card-play-btn" style="position: static; margin-right: 12px; width: 36px; height: 36px; opacity: 1; transform: none; box-shadow: none;">
                    <i class="fa-solid fa-play"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Rilis Terbaru -->
<section class="section fade-in" style="animation-delay: 0.1s;">
    <div class="section-header">
        <h2 class="section-title">Rilis Terbaru</h2>
        <a href="<?= BASE_URL ?>/search" class="section-link">Lihat Semua</a>
    </div>
    <div class="cards-grid">
        <?php foreach($recentAlbums as $album): ?>
            <div class="card" onclick="navigateTo('<?= BASE_URL ?>/album?id=<?= $album['id'] ?>')">
                <div class="card-cover">
                    <img src="<?= getCoverUrl($album['cover']) ?>" alt="<?= htmlspecialchars($album['title']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    <div class="card-play-btn">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
                <div class="card-title" title="<?= htmlspecialchars($album['title']) ?>"><?= htmlspecialchars($album['title']) ?></div>
                <div class="card-sub"><?= htmlspecialchars($album['artist_name']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Artis Populer -->
<section class="section fade-in" style="animation-delay: 0.2s;">
    <div class="section-header">
        <h2 class="section-title">Artis Populer</h2>
    </div>
    <div class="cards-grid cards-grid-lg">
        <?php foreach($topArtists as $artist): ?>
            <div class="card" onclick="navigateTo('<?= BASE_URL ?>/artist?id=<?= $artist['id'] ?>')">
                <div class="card-cover" style="border-radius: 50%; box-shadow: 0 8px 24px rgba(0,0,0,.5);">
                    <img src="<?= getCoverUrl($artist['photo'] ?? null) ?>" alt="<?= htmlspecialchars($artist['name']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    <div class="card-play-btn" style="bottom: 8px; right: 8px;">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
                <div class="card-title" style="text-align: center; margin-bottom: 2px; font-size: 1rem;"><?= htmlspecialchars($artist['name']) ?></div>
                <div class="card-sub" style="text-align: center;">Artis</div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
