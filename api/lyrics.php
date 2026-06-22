<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No song ID provided.']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT lyrics FROM songs WHERE id = ?");
$stmt->execute([$_GET['id']]);
$song = $stmt->fetch(PDO::FETCH_ASSOC);

if ($song) {
    if (!empty($song['lyrics'])) {
        echo json_encode(['success' => true, 'lyrics' => $song['lyrics']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lirik belum tersedia untuk lagu ini.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Lagu tidak ditemukan.']);
}
