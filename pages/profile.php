<?php
requireLogin();
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$playlistCount = $stmt2->fetchColumn();

// Ambil riwayat pemutaran terakhir (History)
$stmt3 = $pdo->prepare("CALL sp_get_user_history(?)");
$stmt3->execute([$_SESSION['user_id']]);
$histories = $stmt3->fetchAll();
$stmt3->closeCursor();
?>

<div class="profile-header fade-in" style="background: linear-gradient(180deg, #333 0%, rgba(18,18,18,0) 100%);">
    <div class="profile-avatar-lg">
        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
    </div>
    <div class="profile-info">
        <div class="profile-label">Profil</div>
        <h1 class="profile-name"><?= htmlspecialchars($user['full_name']) ?></h1>
        <div class="profile-stats">
            <?= $playlistCount ?> Playlist Publik • Mengikuti 0
        </div>
    </div>
</div>

<div class="page-content" style="margin-top: 24px;">
    <div class="section fade-in" style="animation-delay: 0.1s;">
        <h2 class="section-title">Baru Saja Diputar</h2>
        
        <?php if (empty($histories)): ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Belum ada aktivitas pemutaran. Putar lagu sekarang dan cek kembali ke sini.</p>
        <?php else: ?>
            <table class="song-list">
                <thead>
                    <tr>
                        <th>Lagu</th>
                        <th>Waktu Diputar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Hanya tampilkan 10 riwayat terakhir
                    $limit = 10;
                    $count = 0;
                    foreach($histories as $idx => $hist): 
                        if ($count >= $limit) break;
                    ?>
                        <tr>
                            <td>
                                <div class="song-info">
                                    <div class="song-thumb">
                                        <img src="<?= getCoverUrl($hist['cover']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                                    </div>
                                    <div>
                                        <div class="song-name"><?= htmlspecialchars($hist['title']) ?></div>
                                        <div class="song-artist"><?= htmlspecialchars($hist['artist_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem; vertical-align: middle;">
                                <?php 
                                    $diff = time() - strtotime($hist['played_at']);
                                    if ($diff < 3600) echo floor($diff/60) . ' menit yang lalu';
                                    elseif ($diff < 86400) echo floor($diff/3600) . ' jam yang lalu';
                                    else echo date('d M Y', strtotime($hist['played_at']));
                                ?>
                            </td>
                        </tr>
                    <?php 
                        $count++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
