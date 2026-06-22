<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

$msg = '';
// Handle Delete Song
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmtSong = $pdo->prepare("SELECT file_path, cover FROM songs WHERE id = ?");
    $stmtSong->execute([$delId]);
    $songRow = $stmtSong->fetch(PDO::FETCH_ASSOC);
    if ($songRow) {
        if ($songRow['file_path'] && strpos($songRow['file_path'], 'songs/') === 0) {
            $songPath = __DIR__ . '/../uploads/' . $songRow['file_path'];
            if (is_file($songPath)) @unlink($songPath);
        }
        if ($songRow['cover'] && strpos($songRow['cover'], 'covers/') === 0) {
            $coverPath = __DIR__ . '/../uploads/' . $songRow['cover'];
            if (is_file($coverPath)) @unlink($coverPath);
        }
    }
    $pdo->prepare("DELETE FROM favorites WHERE song_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM history WHERE song_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM playlist_songs WHERE song_id = ?")->execute([$delId]);
    $pdo->prepare("DELETE FROM songs WHERE id = ?")->execute([$delId]);
    $_SESSION['msg'] = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> Lagu berhasil dihapus.</div>";
    header('Location: ' . BASE_URL . '/admin/songs.php');
    exit;
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// Filters
$search   = trim($_GET['search'] ?? '');
$genreFilter = $_GET['genre'] ?? '';
$artistFilter = $_GET['artist_id'] ?? '';

// Build query
$where = ["1=1"];
$params = [];
if ($search) {
    $where[] = "(s.title LIKE ? OR a.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($genreFilter) {
    $where[] = "s.genre = ?";
    $params[] = $genreFilter;
}
if ($artistFilter) {
    $where[] = "s.artist_id = ?";
    $params[] = $artistFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.cover, s.genre, s.duration, s.created_at, 
           a.name AS artist_name, al.title AS album_title
    FROM songs s
    LEFT JOIN artists a ON s.artist_id = a.id
    LEFT JOIN albums al ON s.album_id = al.id
    WHERE $whereSql
    ORDER BY s.created_at DESC
");
$stmt->execute($params);
$songs = $stmt->fetchAll();

$totalSongs  = $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn();
$totalActive = $pdo->query("SELECT COUNT(DISTINCT genre) FROM songs WHERE genre IS NOT NULL AND genre != ''")->fetchColumn();

// All artists for filter dropdown
$artists = $pdo->query("SELECT id, name FROM artists ORDER BY name ASC")->fetchAll();
// All genres for filter dropdown
$stmtGenres = $pdo->query("SELECT DISTINCT genre FROM songs WHERE genre IS NOT NULL AND genre != '' ORDER BY genre ASC");
$genres = $stmtGenres->fetchAll(PDO::FETCH_COLUMN);

$genreColors = [
    'Pop' => '#E13300', 'Rock' => '#7358FF', 'Hip-Hop' => '#1E3264',
    'R&B' => '#E8115B', 'Jazz' => '#8D67AB', 'Electronic' => '#148A08',
    'Dangdut' => '#F59B23', 'Indie' => '#537AA1', 'K-Pop' => '#c44dff',
    'Metal' => '#555', 'Reggae' => '#1db954', 'Classical' => '#c0a040',
    'Country' => '#a0522d', 'Acoustic' => '#5b8a8b',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lagu - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        /* Song management specific styles */
        .songs-toolbar {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 16px 20px; background: rgba(255,255,255,0.03);
            border-bottom: 1px solid var(--border);
        }
        .songs-toolbar input[type="text"] {
            background: rgba(0,0,0,0.25); border: 1px solid var(--border);
            border-radius: 8px; color: #fff; padding: 10px 16px;
            font-size: 0.9rem; flex: 1; min-width: 200px; outline: none;
            transition: border-color 0.2s;
        }
        .songs-toolbar input[type="text"]:focus { border-color: var(--primary); }
        .songs-toolbar select {
            background: rgba(0,0,0,0.25); border: 1px solid var(--border);
            border-radius: 8px; color: #fff; padding: 10px 14px;
            font-size: 0.9rem; cursor: pointer; outline: none;
        }
        .songs-toolbar select:focus { border-color: var(--primary); }
        .songs-toolbar .btn-filter {
            padding: 10px 18px; border-radius: 8px; border: none;
            background: var(--primary); color: #fff; font-weight: 600;
            cursor: pointer; font-size: 0.9rem; transition: opacity 0.2s;
        }
        .songs-toolbar .btn-filter:hover { opacity: 0.85; }
        .songs-toolbar .btn-reset {
            padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border);
            background: transparent; color: var(--text-muted); cursor: pointer;
            font-size: 0.9rem; transition: all 0.2s;
        }
        .songs-toolbar .btn-reset:hover { border-color: #fff; color: #fff; }

        .song-row-thumb { width: 44px; height: 44px; border-radius: 6px; object-fit: cover; }
        .song-title-cell { display: flex; align-items: center; gap: 12px; }
        .song-title-text { font-weight: 600; font-size: 0.95rem; }
        .song-artist-text { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }

        .genre-badge {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px;
            color: #fff; white-space: nowrap;
        }
        .songs-count-badge {
            background: rgba(255,255,255,0.06); border-radius: 20px;
            padding: 4px 14px; font-size: 0.85rem; color: var(--text-muted);
            font-weight: 500;
        }
        .no-songs-placeholder {
            text-align: center; padding: 60px 20px; color: var(--text-muted);
        }
        .no-songs-placeholder i { font-size: 3.5rem; opacity: 0.3; margin-bottom: 16px; }
        .no-songs-placeholder p { font-size: 0.95rem; }

        .action-btns { display: flex; gap: 8px; }
        .btn-edit-sm {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 7px; font-size: 0.8rem;
            font-weight: 600; background: rgba(99,91,255,0.15); color: #a89cff;
            border: 1px solid rgba(99,91,255,0.3); transition: all 0.2s; text-decoration: none;
        }
        .btn-edit-sm:hover { background: rgba(99,91,255,0.3); color: #fff; }
        .btn-del-sm {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 7px; font-size: 0.8rem;
            font-weight: 600; background: rgba(255,60,60,0.1); color: #ff7070;
            border: 1px solid rgba(255,60,60,0.25); transition: all 0.2s; text-decoration: none;
            cursor: pointer;
        }
        .btn-del-sm:hover { background: rgba(255,60,60,0.25); color: #fff; }

        .upload-cta {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, var(--primary), #a855f7);
            color: #fff; padding: 10px 20px; border-radius: 10px;
            font-weight: 700; font-size: 0.9rem; text-decoration: none;
            transition: opacity 0.2s; white-space: nowrap;
        }
        .upload-cta:hover { opacity: 0.85; }

        .songs-stats-row {
            display: flex; gap: 16px; padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,0.02);
        }
        .songs-stat-mini {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem; color: var(--text-muted);
        }
        .songs-stat-mini strong { color: var(--text); font-size: 1rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <i class="fa-solid fa-music" style="color:var(--primary);font-size:1.2rem;"></i>
                <h1>Kelola Lagu</h1>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="<?= BASE_URL ?>/admin/upload.php" class="upload-cta">
                    <i class="fa-solid fa-upload"></i> Upload Lagu
                </a>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm" style="padding:8px 14px;">
                    <i class="fa-solid fa-power-off"></i> Keluar
                </a>
            </div>
        </header>

        <div class="admin-body">
            <?= $msg ?>

            <div class="admin-table-card fade-in">

                <!-- Stats Row -->
                <div class="songs-stats-row">
                    <div class="songs-stat-mini">
                        <i class="fa-solid fa-compact-disc" style="color:var(--primary)"></i>
                        <span>Total Lagu: <strong><?= $totalSongs ?></strong></span>
                    </div>
                    <div class="songs-stat-mini">
                        <i class="fa-solid fa-tags" style="color:#a855f7"></i>
                        <span>Genre Tersedia: <strong><?= $totalActive ?></strong></span>
                    </div>
                    <div class="songs-stat-mini">
                        <i class="fa-solid fa-filter" style="color:#3b82f6"></i>
                        <span>Menampilkan: <strong><?= count($songs) ?></strong> lagu</span>
                    </div>
                </div>

                <!-- Toolbar / Filters -->
                <form method="GET" action="">
                    <div class="songs-toolbar">
                        <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted)"></i>
                        <input type="text" name="search" placeholder="Cari judul atau artis..." value="<?= htmlspecialchars($search) ?>">
                        
                        <select name="genre">
                            <option value="">— Semua Genre —</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>" <?= $genreFilter === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select name="artist_id">
                            <option value="">— Semua Artis —</option>
                            <?php foreach ($artists as $art): ?>
                                <option value="<?= $art['id'] ?>" <?= $artistFilter == $art['id'] ? 'selected' : '' ?>><?= htmlspecialchars($art['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-filter"><i class="fa-solid fa-search"></i> Filter</button>
                        <?php if ($search || $genreFilter || $artistFilter): ?>
                            <a href="<?= BASE_URL ?>/admin/songs.php" class="btn-reset"><i class="fa-solid fa-xmark"></i> Reset</a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Table -->
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:44px">#</th>
                                <th>Lagu</th>
                                <th>Album</th>
                                <th>Genre</th>
                                <th>Ditambahkan</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($songs)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="no-songs-placeholder">
                                            <i class="fa-solid fa-music"></i>
                                            <p>Tidak ada lagu yang ditemukan.<br>
                                            <?php if ($search || $genreFilter || $artistFilter): ?>
                                                Coba ubah filter pencarian Anda.
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>/admin/upload.php" style="color:var(--primary);">Upload lagu pertama Anda!</a>
                                            <?php endif; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($songs as $i => $song): 
                                    $genreColor = $genreColors[$song['genre']] ?? '#444';
                                ?>
                                <tr>
                                    <td style="color:var(--text-muted);font-size:0.85rem;"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="song-title-cell">
                                            <img src="<?= getCoverUrl($song['cover']) ?>" 
                                                 class="song-row-thumb"
                                                 onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                                            <div>
                                                <div class="song-title-text"><?= htmlspecialchars($song['title']) ?></div>
                                                <div class="song-artist-text"><?= htmlspecialchars($song['artist_name'] ?? '—') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:0.85rem;">
                                        <?= htmlspecialchars($song['album_title'] ?? '—') ?>
                                    </td>
                                    <td>
                                        <?php if ($song['genre']): ?>
                                            <span class="genre-badge" style="background-color:<?= $genreColor ?>;">
                                                <?= htmlspecialchars($song['genre']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);font-size:0.8rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:0.85rem;">
                                        <?= $song['created_at'] ? date('d M Y', strtotime($song['created_at'])) : '—' ?>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content:flex-end;">
                                            <a href="<?= BASE_URL ?>/admin/songs_edit.php?id=<?= $song['id'] ?>" class="btn-edit-sm">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/songs.php?delete_id=<?= $song['id'] ?>" 
                                               class="btn-del-sm"
                                               onclick="return confirm('Yakin ingin menghapus lagu \'<?= addslashes(htmlspecialchars($song['title'])) ?>\'? Tindakan ini tidak dapat dibatalkan.')">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>
</div>
</body>
</html>
