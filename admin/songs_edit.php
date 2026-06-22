<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/songs.php');
    exit;
}

$songId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->execute([$songId]);
$song = $stmt->fetch(PDO::FETCH_ASSOC);

$msg = '';

if (!$song) {
    $msg = "<div class='alert alert-danger'>Lagu tidak ditemukan.</div>";
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update' && $song) {
    $title = $_POST['title'];
    $artist_id = $_POST['artist_id'];
    $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
    $genre = !empty($_POST['genre']) ? $_POST['genre'] : null;
    $lyrics = !empty($_POST['lyrics']) ? $_POST['lyrics'] : null;
    
    $coverFile = $song['cover']; // keep existing
    // Handle new cover upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        // Delete old cover
        if ($coverFile && strpos($coverFile, 'covers/') === 0) {
            $oldPath = __DIR__ . '/../uploads/' . $coverFile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
        $tmpCover = $_FILES['cover']['tmp_name'];
        $newCoverName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['cover']['name']));
        $coverDir = __DIR__ . '/../uploads/covers/';
        if (!is_dir($coverDir)) mkdir($coverDir, 0777, true);
        move_uploaded_file($tmpCover, $coverDir . $newCoverName);
        $coverFile = 'covers/' . $newCoverName;
    }

    // Generate new slug if title changed (optional, but good practice)
    $slug = $song['slug'];
    if ($title !== $song['title']) {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-')) . '-' . time();
    }
    
    $stmtUpdate = $pdo->prepare("UPDATE songs SET title = ?, slug = ?, artist_id = ?, album_id = ?, genre = ?, lyrics = ?, cover = ? WHERE id = ?");
    $stmtUpdate->execute([$title, $slug, $artist_id, $album_id, $genre, $lyrics, $coverFile, $songId]);
    
    $_SESSION['msg'] = "<div class='alert alert-success'>Lagu berhasil diperbarui.</div>";
    header('Location: ' . BASE_URL . '/admin/songs.php');
    exit;
}

$artists = $pdo->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
$albums = $pdo->query("SELECT * FROM albums ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lagu - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Edit Lagu</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        <div class="admin-body">
            <?= $msg ?>
            <?php if ($song): ?>
            <div class="auth-card fade-in" style="max-width:600px;margin:auto;padding:24px;border-radius:var(--radius-lg);">
                <h3 style="margin-bottom:16px;">Perbarui Lagu</h3>
                <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="form-group">
                        <label>Judul Lagu</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($song['title']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Artis</label>
                        <select name="artist_id" required>
                            <option value="">-- Pilih Artis --</option>
                            <?php foreach ($artists as $art): ?>
                                <option value="<?= $art['id'] ?>" <?= ($art['id'] == $song['artist_id']) ? 'selected' : '' ?>><?= htmlspecialchars($art['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Album (Opsional)</label>
                        <select name="album_id">
                            <option value="">-- Single (Tanpa Album) --</option>
                            <?php foreach ($albums as $alb): ?>
                                <option value="<?= $alb['id'] ?>" <?= ($alb['id'] == $song['album_id']) ? 'selected' : '' ?>><?= htmlspecialchars($alb['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Genre</label>
                        <select name="genre">
                            <option value="">-- Pilih Genre (Opsional) --</option>
                            <?php 
                            $genres = ["Pop", "Rock", "Hip-Hop", "R&B", "Jazz", "Electronic", "Dangdut", "Indie", "K-Pop", "Metal", "Reggae", "Classical", "Country", "Acoustic"];
                            foreach ($genres as $g): 
                            ?>
                                <option value="<?= $g ?>" <?= ($g == $song['genre']) ? 'selected' : '' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Cover Lagu Saat Ini</label><br>
                        <?php if ($song['cover']): ?>
                            <img src="<?= getCoverUrl($song['cover']) ?>" style="width:80px;height:80px;border-radius:8px;object-fit:cover;" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:0.9rem;">Tidak ada cover khusus (menggunakan cover album/artis).</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Ganti Cover Lagu (Opsional)</label>
                        <input type="file" name="cover" accept="image/*" style="padding: 10px; height: 48px; display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius);">
                    </div>
                    
                    <div class="form-group">
                        <label>Lirik Lagu (Opsional)</label>
                        <textarea name="lyrics" rows="6" style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius); color: #fff; font-family: inherit; resize: vertical;"><?= htmlspecialchars($song['lyrics'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-signup" style="height:48px;"><i class="fa-solid fa-save" style="margin-right:6px;"></i> Simpan Perubahan</button>
                </form>
                <a href="<?= BASE_URL ?>/admin/songs.php" class="btn btn-secondary" style="margin-top:12px;display:inline-block;"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
