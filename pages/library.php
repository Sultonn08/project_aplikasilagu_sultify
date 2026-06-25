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
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h1 class="section-title" style="font-size: 2rem; margin:0;">Koleksi Kamu</h1>
        <?php if (isLoggedIn()): ?>
        <button onclick="openCreatePlaylistModal()" style="display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:30px;border:none;background:linear-gradient(135deg,var(--primary),#a855f7);color:#fff;font-weight:700;cursor:pointer;font-size:0.9rem;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
            <i class="fa-solid fa-plus"></i> Buat Playlist
        </button>
        <?php endif; ?>
    </div>
    
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
        <?php if (empty($playlists)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-muted);">
                <i class="fa-solid fa-list-music" style="font-size:3rem;opacity:0.2;display:block;margin-bottom:16px;"></i>
                <p style="font-size:0.95rem;">Belum ada playlist. <button onclick="openCreatePlaylistModal()" style="background:none;border:none;color:var(--primary);font-weight:700;cursor:pointer;">Buat playlist pertamamu!</button></p>
            </div>
        <?php endif; ?>
        <?php foreach($playlists as $idx => $pl): ?>
            <div class="card fade-in" style="position:relative;animation-delay: <?= ($idx + 1) * 0.1 ?>s;" onclick="navigateTo('<?= BASE_URL ?>/playlist?id=<?= $pl['id'] ?>')">
                <div class="card-cover">
                    <img src="<?= getCoverUrl($pl['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    <div class="card-play-btn">
                        <i class="fa-solid fa-play"></i>
                    </div>
                </div>
                <div class="card-title"><?= htmlspecialchars($pl['name']) ?></div>
                <div class="card-sub">Oleh Anda • <?= $pl['total_songs_counted'] ?> lagu</div>
                <!-- Delete button -->
                <div onclick="event.stopPropagation(); deletePlaylist(<?= $pl['id'] ?>)"
                     title="Hapus Playlist"
                     style="position:absolute;top:8px;right:8px;width:28px;height:28px;border-radius:50%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transition:opacity 0.2s;color:#ff7070;font-size:0.75rem;"
                     class="playlist-del-btn">
                    <i class="fa-solid fa-xmark"></i>
                </div>
            </div>
        <?php endforeach; ?>
        
    </div>
</div>
