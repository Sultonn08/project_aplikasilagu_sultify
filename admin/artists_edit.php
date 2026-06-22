<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

$artistId = $_GET['id'] ?? null;
if (!$artistId) {
    die('ID artis tidak diberikan.');
}

// Fetch existing artist
$stmt = $pdo->prepare('SELECT * FROM artists WHERE id = ?');
$stmt->execute([$artistId]);
$artist = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$artist) {
    die('Artis tidak ditemukan.');
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $fileName = $artist['photo']; // keep existing

    // Handle new photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old photo
        if ($fileName && $fileName !== 'default_artist.jpg' && strpos($fileName, 'artists/') === 0) {
            $oldPath = __DIR__ . '/../uploads/' . $fileName;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
        $tmpName = $_FILES['photo']['tmp_name'];
        $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['photo']['name']));
        $uploadDir = __DIR__ . '/../uploads/artists/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($tmpName, $uploadDir . $newFileName);
        $fileName = 'artists/' . $newFileName;
    }

    $stmt = $pdo->prepare('UPDATE artists SET name = ?, bio = ?, photo = ? WHERE id = ?');
    $stmt->execute([$name, $bio, $fileName, $artistId]);
    $msg = "<div class='alert alert-success'>Data artis berhasil diupdate.</div>";
    // Refresh data
    $artist['name'] = $name;
    $artist['bio'] = $bio;
    $artist['photo'] = $fileName;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Artis - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Edit Artis</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        <div class="admin-body">
            <?= $msg ?>
            <div class="auth-card fade-in" style="max-width: 600px; margin:auto; padding:24px; border-radius:var(--radius-lg);">
                <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:16px;">
                    <div class="form-group">
                        <label>Nama Artis</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($artist['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Bio</label>
                        <input type="text" name="bio" value="<?= htmlspecialchars($artist['bio']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Foto Saat Ini</label><br>
                        <img src="<?= getCoverUrl($artist['photo']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'">
                    </div>
                    <div class="form-group">
                        <label>Ganti Foto (opsional)</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                    <button type="submit" class="btn-signup"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
                </form>
                <a href="<?= BASE_URL ?>/admin/artists.php" class="btn btn-secondary" style="margin-top:12px; display:inline-block;"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
