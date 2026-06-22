<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

// Get album ID
if (!isset($_GET['id'])) {
    header('Location: albums.php');
    exit;
}
$albumId = $_GET['id'];

// Fetch album data
$stmt = $pdo->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->execute([$albumId]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);
$msg = '';
if (!$album) {
    $msg = "<div class='alert alert-danger'>Album tidak ditemukan.</div>";
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $title = $_POST['title'];
    $artistId = $_POST['artist_id'];
    $coverFile = $album['cover']; // existing cover
    // Upload new cover if provided
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        // Delete old cover file
        if ($coverFile && $coverFile !== 'default_cover.jpg' && strpos($coverFile, 'albums/') === 0) {
            $oldPath = __DIR__ . '/../uploads/' . $coverFile;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
        $tmp = $_FILES['cover']['tmp_name'];
        $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['cover']['name']));
        $uploadDir = __DIR__ . '/../uploads/albums/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($tmp, $uploadDir . $newFileName);
        $coverFile = 'albums/' . $newFileName;
    }
    $stmtUpdate = $pdo->prepare("UPDATE albums SET title = ?, artist_id = ?, cover = ? WHERE id = ?");
    $stmtUpdate->execute([$title, $artistId, $coverFile, $albumId]);
    $_SESSION['msg'] = "<div class='alert alert-success'>Album berhasil diperbarui.</div>";
    header('Location: ' . BASE_URL . '/admin/albums.php');
    exit;
}

// Fetch artists for dropdown
$artists = $pdo->query("SELECT id, name FROM artists ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Album - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Edit Album</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        <div class="admin-body">
            <?= $msg ?>
            <div class="auth-card fade-in" style="max-width:600px;margin:auto;padding:24px;border-radius:var(--radius-lg);">
                <h3 style="margin-bottom:16px;">Perbarui Album</h3>
                <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $albumId ?>">
                    <div class="form-group">
                        <label>Judul Album</label>
                        <input type="text" name="title" required value="<?= htmlspecialchars($album['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Artis</label>
                        <select name="artist_id" required>
                            <option value="">-- Pilih Artis --</option>
                            <?php foreach ($artists as $art): ?>
                                <option value="<?= $art['id'] ?>" <?= ($art['id'] == $album['artist_id']) ? 'selected' : '' ?>><?= htmlspecialchars($art['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cover (opsional)</label>
                        <input type="file" name="cover" accept="image/*">
                        <?php if ($album['cover']): ?>
                            <p style="margin-top:4px;font-size:0.9rem;">Cover saat ini: <img src="<?= getCoverUrl($album['cover']) ?>" style="height:40px;width:40px;border-radius:4px;object-fit:cover;" alt="cover"></p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-signup" style="height:48px;"><i class="fa-solid fa-save" style="margin-right:6px;"></i> Simpan</button>
                </form>
                <a href="<?= BASE_URL ?>/admin/albums.php" class="btn btn-secondary" style="margin-top:12px;display:inline-block;"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
