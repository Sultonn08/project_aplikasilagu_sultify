<?php
// api/playlist.php — Unified playlist API
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$pdo    = getDB();
$uid    = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── CREATE PLAYLIST ──────────────────────────────────────────
if ($action === 'create') {
    $name     = trim($_POST['name'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nama playlist tidak boleh kosong.']);
        exit;
    }

    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')) . '-' . time();
    $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name, slug, description, is_public, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$uid, $name, $slug, $desc, $is_public]);
    $newId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'id' => $newId, 'name' => $name]);
    exit;
}

// ─── DELETE PLAYLIST ─────────────────────────────────────────
if ($action === 'delete') {
    $pid = (int)($_POST['playlist_id'] ?? 0);
    // Pastikan milik user
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$pid, $uid]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Playlist tidak ditemukan.']);
        exit;
    }
    $pdo->prepare("DELETE FROM playlist_songs WHERE playlist_id = ?")->execute([$pid]);
    $pdo->prepare("DELETE FROM playlists WHERE id = ?")->execute([$pid]);
    echo json_encode(['success' => true]);
    exit;
}

// ─── ADD SONG TO PLAYLIST ────────────────────────────────────
if ($action === 'add_song') {
    $pid  = (int)($_POST['playlist_id'] ?? 0);
    $sid  = (int)($_POST['song_id'] ?? 0);

    // Pastikan playlist milik user
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$pid, $uid]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Playlist tidak ditemukan.']);
        exit;
    }

    // Cek duplikat
    $dup = $pdo->prepare("SELECT id FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
    $dup->execute([$pid, $sid]);
    if ($dup->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Lagu sudah ada di playlist ini.']);
        exit;
    }

    $pdo->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)")->execute([$pid, $sid]);
    echo json_encode(['success' => true, 'message' => 'Lagu ditambahkan ke playlist!']);
    exit;
}

// ─── REMOVE SONG FROM PLAYLIST ───────────────────────────────
if ($action === 'remove_song') {
    $pid = (int)($_POST['playlist_id'] ?? 0);
    $sid = (int)($_POST['song_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$pid, $uid]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Playlist tidak ditemukan.']);
        exit;
    }

    $pdo->prepare("DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?")->execute([$pid, $sid]);
    echo json_encode(['success' => true]);
    exit;
}

// ─── GET USER PLAYLISTS (for dropdown) ───────────────────────
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'playlists' => $stmt->fetchAll()]);
    exit;
}

// ─── UPLOAD COVER ─────────────────────────────────────────────
if ($action === 'upload_cover') {
    $pid = (int)($_POST['playlist_id'] ?? 0);
    
    // Pastikan playlist milik user
    $stmt = $pdo->prepare("SELECT id, cover FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$pid, $uid]);
    $playlist = $stmt->fetch();
    if (!$playlist) {
        echo json_encode(['success' => false, 'message' => 'Playlist tidak ditemukan atau Anda tidak memiliki akses.']);
        exit;
    }

    if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Berkas cover wajib diunggah.']);
        exit;
    }

    $file = $_FILES['cover'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    
    // Verify file type using mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.']);
        exit;
    }

    // Limit to 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 5MB.']);
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($ext)) {
        $ext = 'jpg';
    }
    $fileName = 'playlist_' . $pid . '_' . time() . '.' . $ext;
    $targetPath = UPLOAD_PATH . $fileName;

    // Ensure uploads directory exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Hapus cover lama jika bukan default
        if ($playlist['cover'] && $playlist['cover'] !== 'default_playlist.png' && file_exists(UPLOAD_PATH . $playlist['cover'])) {
            @unlink(UPLOAD_PATH . $playlist['cover']);
        }

        $stmt = $pdo->prepare("UPDATE playlists SET cover = ? WHERE id = ?");
        $stmt->execute([$fileName, $pid]);

        echo json_encode(['success' => true, 'cover_url' => getCoverUrl($fileName)]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file cover ke server.']);
        exit;
    }
}

// ─── RENAME PLAYLIST ──────────────────────────────────────────
if ($action === 'rename') {
    $pid  = (int)($_POST['playlist_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong.']);
        exit;
    }
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')) . '-' . time();
    $stmt = $pdo->prepare("UPDATE playlists SET name = ?, slug = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $slug, $pid, $uid]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action tidak dikenal.']);
