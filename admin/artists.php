<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();
$msg = '';

// Handle Tambah Artis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    
    // Default gambar artis
    $fileName = 'default_artist.jpg';
    
    // Upload foto artis jika ada
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['photo']['tmp_name'];
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['photo']['name']));
        $uploadDir = __DIR__ . '/../uploads/artists/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        move_uploaded_file($tmpName, $uploadDir . $fileName);
    }
    
    // Generate slug from name
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $slug = $slug . '-' . time();
    
    $stmt = $pdo->prepare("INSERT INTO artists (name, slug, bio, photo, is_verified) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$name, $slug, $bio, 'artists/' . $fileName]);
    $msg = "<div class='alert alert-success'>Artis berhasil ditambahkan!</div>";
}

// Fetch artists list
$artists = $pdo->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
// Handle Delete Artist
if (isset($_GET['delete_id'])) {
    $delId = $_GET['delete_id'];
    
    // Delete photo file
    $stmtPhoto = $pdo->prepare("SELECT photo FROM artists WHERE id = ?");
    $stmtPhoto->execute([$delId]);
    $photoRow = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
    if ($photoRow && $photoRow['photo'] && strpos($photoRow['photo'], 'artists/') === 0) {
        $photoPath = __DIR__ . '/../uploads/' . $photoRow['photo'];
        if (is_file($photoPath)) {
            @unlink($photoPath);
        }
    }
    
    $stmtDel = $pdo->prepare("DELETE FROM artists WHERE id = ?");
    $stmtDel->execute([$delId]);
    $msg = "<div class='alert alert-success'>Artis dihapus.</div>";
    // Refresh list after deletion
    $artists = $pdo->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Artis - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Kelola Artis</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        
        <div class="admin-body">
            <?= $msg ?>
            <div class="auth-card fade-in" style="max-width: 100%; margin-bottom: 30px; padding: 24px; border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: 16px;">Tambah Artis Baru</h3>
                <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                        <label>Nama Artis</label>
                        <input type="text" name="name" required placeholder="Contoh: The Weeknd">
                    </div>
                    <div class="form-group" style="flex: 2; min-width: 300px; margin: 0;">
                        <label>Bio (Opsional)</label>
                        <input type="text" name="bio" placeholder="Penyanyi asal Kanada...">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Foto Profil (Opsional)</label>
                        <input type="file" name="photo" accept="image/*" style="padding: 10px; height: 48px; display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius);">
                    </div>
                    <button type="submit" class="btn-signup" style="margin: 0; height: 48px;"><i class="fa-solid fa-plus" style="margin-right: 6px;"></i> Simpan</button>
                </form>
            </div>
        
            <div class="admin-table-card fade-in" style="animation-delay: 0.1s;">
                <div class="admin-table-header">
                    <h3>Daftar Artis</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Artis</th>
                                <th>Bio</th>
                                <th>Tanggal Ditambahkan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($artists as $art): ?>
<tr>
    <td><img src="<?= getCoverUrl($art['photo']) ?>" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;" onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'"></td>
    <td><div style="font-weight: 600; color: var(--text); font-size: 1.05rem;"><?= htmlspecialchars($art['name']) ?></div></td>
    <td style="color: var(--text-muted); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem;"><?= htmlspecialchars($art['bio']) ?></td>
    <td style="color: var(--text-muted); font-size: 0.85rem;"><?= date('d M Y', strtotime($art['created_at'])) ?></td>
    <td>
        <a href="<?= BASE_URL ?>/admin/artists_edit.php?id=<?= $art['id'] ?>" class="btn btn-sm btn-primary" style="margin-right:5px;"><i class="fa-solid fa-pen"></i> Edit</a>
        <a href="?delete_id=<?= $art['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus artis ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
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
