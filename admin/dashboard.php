<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin(); // Pastikan hanya admin yang bisa akses

$pdo = getDB();

// Fetch summary metrics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSongs = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$totalArtists = $pdo->query("SELECT COUNT(*) FROM artists")->fetchColumn();
$totalPlaylists = $pdo->query("SELECT COUNT(*) FROM playlists")->fetchColumn();

// Fetch recently registered users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
// Handle song deletion
if (isset($_GET['delete_song'])) {
    $delId = $_GET['delete_song'];
    // Fetch song file path
    $stmtFile = $pdo->prepare("SELECT file_path FROM songs WHERE id = ?");
    $stmtFile->execute([$delId]);
    $songRow = $stmtFile->fetch(PDO::FETCH_ASSOC);
    if ($songRow && $songRow['file_path']) {
        $songPath = __DIR__ . '/../' . $songRow['file_path'];
        if (is_file($songPath)) {
            @unlink($songPath);
        }
    }
    // Delete related records
    $pdo->prepare("DELETE FROM favorites WHERE song_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM history WHERE song_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM playlist_songs WHERE song_id = ?")->execute([$delId]);
    // Delete song record
    $pdo->prepare("DELETE FROM songs WHERE id = ?")->execute([$delId]);
    $_SESSION['msg'] = "<div class='alert alert-success'>Lagu berhasil dihapus.</div>";
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// Fetch recent songs for admin view (limit 10)
$songs = $pdo->query("SELECT s.id, s.title, s.created_at, a.name AS artist_name FROM songs s LEFT JOIN artists a ON s.artist_id = a.id ORDER BY s.created_at DESC LIMIT 10")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sultify Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Dashboard Overview</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm" style="padding: 8px 16px; font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        
        <div class="admin-body">
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="stat-num"><?= number_format($totalUsers) ?></div>
                        <div class="stat-label">Total Pengguna</div>
                    </div>
                </div>
                <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                    <div class="stat-icon green"><i class="fa-solid fa-music"></i></div>
                    <div>
                        <div class="stat-num"><?= number_format($totalSongs) ?></div>
                        <div class="stat-label">Total Lagu</div>
                    </div>
                </div>
                <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                    <div class="stat-icon purple"><i class="fa-solid fa-microphone"></i></div>
                    <div>
                        <div class="stat-num"><?= number_format($totalArtists) ?></div>
                        <div class="stat-label">Total Artis</div>
                    </div>
                </div>
                <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                    <div class="stat-icon orange"><i class="fa-solid fa-list"></i></div>
                    <div>
                        <div class="stat-num"><?= number_format($totalPlaylists) ?></div>
                        <div class="stat-label">Total Playlist</div>
                    </div>
                </div>
            </div>
            
            <!-- Table Recent Users -->
            <div class="admin-table-card fade-in" style="animation-delay: 0.4s;">
                <div class="admin-table-header">
                    <h3>Pendaftar Terbaru</h3>
                    <button class="btn-edit btn-sm">Lihat Semua</button>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Terdaftar Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentUsers as $u): ?>
                            <tr>
                                <td style="color: var(--text-muted);">#<?= $u['id'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem; background: #333; color: #fff;">
                                            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                        </div>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($u['full_name']) ?></div>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if($u['is_active']): ?>
                                        <span class="badge badge-green"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-red"><i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Suspend</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<!-- Song Management Table -->
<div class="admin-table-card fade-in" style="animation-delay:0.5s;">
    <div class="admin-table-header"><h3>Kelola Lagu</h3></div>
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Artis</th>
                    <th>Ditambahkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($songs as $song): ?>
                <tr>
                    <td><?= htmlspecialchars($song['title']) ?></td>
                    <td><?= htmlspecialchars($song['artist_name'] ?? 'Tidak diketahui') ?></td>
                    <td><?= $song['created_at'] ? date('d M Y', strtotime($song['created_at'])) : '-' ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/admin/songs_edit.php?id=<?= $song['id'] ?>" class="btn btn-sm btn-primary" style="margin-right:5px;">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php?delete_song=<?= $song['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus lagu ini?');">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
        </div>
    </main>
</div>

</body>
</html>
