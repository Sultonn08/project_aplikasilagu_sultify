<?php
requireLogin();
$pdo = getDB();

// Ambil Playlist User
$stmt = $pdo->prepare("SELECT p.*, COUNT(ps.id) as total_songs_counted 
                       FROM playlists p 
                       LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id 
                       WHERE p.user_id = ? 
                       GROUP BY p.id ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$playlists = $stmt->fetchAll();

// Hitung total lagu disukai
$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$totalLiked = $stmt2->fetchColumn();

// Ambil beberapa lagu disukai terakhir untuk text cover
$stmt3 = $pdo->prepare("SELECT s.title, a.name as artist_name 
                        FROM favorites f 
                        JOIN songs s ON f.song_id = s.id 
                        JOIN artists a ON s.artist_id = a.id 
                        WHERE f.user_id = ? 
                        ORDER BY f.created_at DESC LIMIT 3");
$stmt3->execute([$_SESSION['user_id']]);
$recentLiked = $stmt3->fetchAll();
?>

<div class="search-page" style="padding-top: 24px;">
    <h1 class="section-title" style="margin-bottom: 24px; font-size: 2rem;">Koleksi Kamu</h1>
    
    <div class="cards-grid cards-grid-lg">
        
        <!-- Card Lagu Disukai Spesial (Ukuran Double) -->
        <div class="card fade-in" style="background: linear-gradient(135deg, #450af5, #c4efd9); grid-column: span 2; display: flex; flex-direction: column; justify-content: space-between; padding: 24px;" onclick="navigateTo('<?= BASE_URL ?>/favorites')">
            <div style="font-size: 1rem; margin-bottom: 30px;">
                <?php if ($recentLiked): ?>
                    <?php foreach($recentLiked as $idx => $rl): ?>
                        <span style="font-weight: 600;"><?= htmlspecialchars($rl['artist_name']) ?></span> 
                        <span style="opacity: 0.8;"><?= htmlspecialchars($rl['title']) ?></span><?= $idx < count($recentLiked)-1 ? ' • ' : '' ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span style="opacity: 0.8;">Belum ada lagu yang kamu sukai. Cari dan temukan musik favoritmu.</span>
                <?php endif; ?>
            </div>
            <div>
                <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 8px;">Lagu yang Disukai</h2>
                <div style="font-weight: 600; opacity: 0.9;"><?= $totalLiked ?> lagu</div>
            </div>
            <div class="card-play-btn" style="bottom: 24px; right: 24px; background: #1DB954; width: 56px; height: 56px; font-size: 1.2rem;">
                <i class="fa-solid fa-play"></i>
            </div>
        </div>

        <!-- Render Card Playlist Reguler -->
        <?php foreach($playlists as $idx => $pl): ?>
            <div class="card fade-in" style="animation-delay: <?= ($idx + 1) * 0.1 ?>s;" onclick="navigateTo('<?= BASE_URL ?>/playlist?id=<?= $pl['id'] ?>')">
                <div class="card-cover">
                    <img src="<?= getCoverUrl($pl['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    <div class="card-play-btn">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
                <div class="card-title"><?= htmlspecialchars($pl['name']) ?></div>
                <div class="card-sub">Oleh Anda • <?= $pl['total_songs_counted'] ?> lagu</div>
            </div>
        <?php endforeach; ?>
        
    </div>
</div>
