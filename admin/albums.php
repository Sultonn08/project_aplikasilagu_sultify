<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

$msg = '';
// Handle Create Album
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = $_POST['title'];
    $artistId = $_POST['artist_id'];
    $coverFile = 'default_cover.jpg';
    // Upload cover image if provided
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['cover']['tmp_name'];
        $coverFile = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['cover']['name']));
        $uploadDir = __DIR__ . '/../uploads/albums/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($tmp, $uploadDir . $coverFile);
    }
    $stmt = $pdo->prepare("INSERT INTO albums (title, artist_id, cover) VALUES (?, ?, ?)");
    $stmt->execute([$title, $artistId, 'albums/' . $coverFile]);
    $msg = "<div class='alert alert-success'>Album berhasil ditambahkan!</div>";
}
// Handle Delete Album
if (isset($_GET['delete_id'])) {
    $delId = $_GET['delete_id'];
    // Fetch cover path before deletion
    $stmtCover = $pdo->prepare("SELECT cover FROM albums WHERE id = ?");
    $stmtCover->execute([$delId]);
    $coverRow = $stmtCover->fetch(PDO::FETCH_ASSOC);
    if ($coverRow && $coverRow['cover'] && strpos($coverRow['cover'], 'albums/') === 0) {
        $coverPath = __DIR__ . '/../uploads/' . $coverRow['cover'];
        if (is_file($coverPath)) {
            @unlink($coverPath);
        }
    }
    // Delete favorites and history for songs of this album
    $stmtFav = $pdo->prepare("DELETE FROM favorites WHERE song_id IN (SELECT id FROM songs WHERE album_id = ?)");
    $stmtFav->execute([$delId]);
    $stmtHist = $pdo->prepare("DELETE FROM history WHERE song_id IN (SELECT id FROM songs WHERE album_id = ?)");
    $stmtHist->execute([$delId]);
    // Fetch song files for cleanup
    $stmtSongFiles = $pdo->prepare("SELECT file FROM songs WHERE album_id = ?");
    $stmtSongFiles->execute([$delId]);
    $songFiles = $stmtSongFiles->fetchAll(PDO::FETCH_COLUMN);
    foreach ($songFiles as $songFile) {
        if ($songFile && strpos($songFile, 'songs/') === 0) {
            $songPath = __DIR__ . '/../uploads/' . $songFile;
            if (is_file($songPath)) {
                @unlink($songPath);
            }
        }
    }
    // Delete song records for this album
    $stmtDeleteSongs = $pdo->prepare("DELETE FROM songs WHERE album_id = ?");
    $stmtDeleteSongs->execute([$delId]);
    // Delete orphaned playlist entries (songs already removed)
    $stmtOrphan = $pdo->prepare("DELETE ps FROM playlist_songs ps LEFT JOIN songs s ON ps.song_id = s.id WHERE s.id IS NULL");
    $stmtOrphan->execute();
    // Delete the album record itself
    $stmtDel = $pdo->prepare("DELETE FROM albums WHERE id = ?");
    $stmtDel->execute([$delId]);
    $_SESSION['msg'] = "<div class='alert alert-success'>Album dihapus.</div>";
    header('Location: ' . BASE_URL . '/admin/albums.php');
    exit;
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// Fetch albums and artists for list
$albums = $pdo->query("SELECT a.id, a.title, a.cover, a.created_at, ar.name AS artist_name FROM albums a LEFT JOIN artists ar ON a.artist_id = ar.id ORDER BY a.created_at DESC")->fetchAll();
$artists = $pdo->query("SELECT id, name FROM artists ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Kelola Album - Sultify Admin</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='<?= BASE_URL ?>/assets/css/style.css'>
</head>
<body>
<div class='admin-layout'>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class='admin-main'>
        <header class='admin-topbar'>
            <h1>Kelola Album</h1>
            <a href='<?= BASE_URL ?>/auth/logout.php' class='btn-danger btn-sm'><i class='fa-solid fa-power-off'></i> Keluar</a>
        </header>
        <div class='admin-body'>
            <?= $msg ?>
            <!-- Add Album Form -->
            <div class='auth-card fade-in' style='max-width:100%;margin-bottom:30px;padding:24px;border-radius:var(--radius-lg);'>
                <h3 style='margin-bottom:16px;'>Tambah Album Baru</h3>
                <form method='POST' enctype='multipart/form-data' style='display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;'>
                    <input type='hidden' name='action' value='create'>
                    <div class='form-group' style='flex:2;min-width:250px;'>
                        <label>Judul Album</label>
                        <input type='text' name='title' required placeholder='Contoh: After Hours'>
                    </div>
                    <div class='form-group' style='flex:2;min-width:250px;'>
                        <label>Artis</label>
                        <select name='artist_id' required>
                            <option value=''>-- Pilih Artis --</option>
                            <?php foreach ($artists as $art): ?>
                                <option value='<?= $art['id'] ?>'><?= htmlspecialchars($art['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class='form-group' style='flex:1;min-width:200px;'>
                        <label>Cover (opsional)</label>
                        <input type='file' name='cover' accept='image/*'>
                    </div>
                    <button type='submit' class='btn-signup' style='height:48px;'><i class='fa-solid fa-plus' style='margin-right:6px;'></i> Simpan</button>
                </form>
            </div>
            <!-- Album Table -->
            <div class='admin-table-card fade-in' style='animation-delay:0.1s;'>
                <div class='admin-table-header'><h3>Daftar Album</h3></div>
                <div style='overflow-x:auto;'>
                    <table class='admin-table'>
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Judul</th>
                                <th>Artis</th>
                                <th>Ditambahkan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($albums as $al): ?>
                                <tr>
                                    <td><img src='<?= getCoverUrl($al['cover']) ?>' style='width:44px;height:44px;border-radius:8px;object-fit:cover;' onerror="this.src='<?= BASE_URL ?>/assets/images/default_cover.svg'"></td>
                                    <td><?= htmlspecialchars($al['title']) ?></td>
                                    <td><?= htmlspecialchars($al['artist_name'] ?? 'Tidak diketahui') ?></td>
                                    <td><?php echo $al['created_at'] ? date('d M Y', strtotime($al['created_at'])) : '-'; ?></td>
                                    <td>
                                        <a href='<?= BASE_URL ?>/admin/albums_edit.php?id=<?= $al['id'] ?>' class='btn btn-sm btn-primary' style='margin-right:5px;'><i class='fa-solid fa-pen'></i> Edit</a>
                                        <a href='<?= BASE_URL ?>/admin/albums.php?delete_id=<?= $al['id'] ?>' class='btn btn-sm btn-danger' onclick="return confirm('Yakin hapus album ini?');"><i class='fa-solid fa-trash'></i> Hapus</a>
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
