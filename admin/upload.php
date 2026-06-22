<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $artist_id = $_POST['artist_id'];
    $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
    $genre = !empty($_POST['genre']) ? $_POST['genre'] : null;
    $lyrics = !empty($_POST['lyrics']) ? $_POST['lyrics'] : null;
    
    // Upload Cover (Opsional)
    $coverPathDb = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $tmpCover = $_FILES['cover']['tmp_name'];
        $coverName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['cover']['name']));
        $coverDir = __DIR__ . '/../uploads/covers/';
        if (!is_dir($coverDir)) mkdir($coverDir, 0777, true);
        if (move_uploaded_file($tmpCover, $coverDir . $coverName)) {
            $coverPathDb = 'covers/' . $coverName;
        }
    }
    
    // Upload File Audio
    if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['song_file']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['song_file']['name']);
        $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName); // sanitize
        
        $uploadDir = __DIR__ . '/../uploads/songs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $destination = $uploadDir . $fileName;
        
        if (move_uploaded_file($tmpName, $destination)) {
            $filePath = 'songs/' . $fileName;
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
            $slug = $slug . '-' . time(); // ensure uniqueness
            
            // Simpan ke database (Duration hardcode ke 200 untuk simpel)
            $stmt = $pdo->prepare("INSERT INTO songs (title, slug, artist_id, album_id, file_path, cover, genre, lyrics, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 200)");
            $stmt->execute([$title, $slug, $artist_id, $album_id, $filePath, $coverPathDb, $genre, $lyrics]);
            
            $msg = "<div class='alert alert-success'>Sukses! Lagu berhasil diunggah dan ditambahkan.</div>";
        } else {
            $msg = "<div class='alert alert-error'>Gagal memindahkan file yang diunggah.</div>";
        }
    } else {
        $msg = "<div class='alert alert-error'>Harap pilih file audio.</div>";
    }
}

$artists = $pdo->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
$albums = $pdo->query("SELECT * FROM albums ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Lagu - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Upload Lagu Baru</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm" style="padding: 8px 16px;"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        
        <div class="admin-body">
            <div class="auth-card" style="margin: 0 auto; max-width: 600px;">
                <h2 style="margin-bottom: 24px; text-align: center;">Form Upload Lagu</h2>
                <?= $msg ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Judul Lagu</label>
                        <input type="text" name="title" required placeholder="Contoh: Blinding Lights">
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Artis</label>
                        <select name="artist_id" required style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius); color: #fff;">
                            <option value="">-- Pilih Artis --</option>
                            <?php foreach($artists as $art): ?>
                                <option value="<?= $art['id'] ?>"><?= htmlspecialchars($art['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Album (Opsional)</label>
                        <select name="album_id" style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius); color: #fff;">
                            <option value="">-- Single (Tanpa Album) --</option>
                            <?php foreach($albums as $alb): ?>
                                <option value="<?= $alb['id'] ?>"><?= htmlspecialchars($alb['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Genre</label>
                        <select name="genre" style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius); color: #fff;">
                            <option value="">-- Pilih Genre (Opsional) --</option>
                            <option value="Pop">Pop</option>
                            <option value="Rock">Rock</option>
                            <option value="Hip-Hop">Hip-Hop</option>
                            <option value="R&B">R&B</option>
                            <option value="Jazz">Jazz</option>
                            <option value="Electronic">Electronic</option>
                            <option value="Dangdut">Dangdut</option>
                            <option value="Indie">Indie</option>
                            <option value="K-Pop">K-Pop</option>
                            <option value="Metal">Metal</option>
                            <option value="Reggae">Reggae</option>
                            <option value="Classical">Classical</option>
                            <option value="Country">Country</option>
                            <option value="Acoustic">Acoustic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cover Lagu (Opsional - Jika single/berbeda dari album)</label>
                        <input type="file" name="cover" accept="image/*" style="padding: 10px; height: 48px; display: flex; align-items: center; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius);">
                    </div>
                    
                    <div class="form-group">
                        <label>Lirik Lagu (Opsional)</label>
                        <textarea name="lyrics" rows="5" placeholder="Masukkan lirik lagu di sini..." style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: var(--radius); color: #fff; font-family: inherit; resize: vertical;"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>File Audio (.mp3)</label>
                        <input type="file" name="song_file" accept="audio/mpeg, audio/mp3" required style="padding: 10px;">
                    </div>
                    
                    <button type="submit" class="btn-primary-full"><i class="fa-solid fa-upload" style="margin-right: 8px;"></i> Upload Lagu</button>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
