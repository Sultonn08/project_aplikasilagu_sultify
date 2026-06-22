<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Harap login terlebih dahulu']);
    exit;
}

$songId = $_GET['id'] ?? null;
if (!$songId) {
    echo json_encode(['success' => false, 'message' => 'ID lagu tidak valid']);
    exit;
}

$pdo = getDB();

// Cek apakah sudah disukai
$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND song_id = ?");
$stmt->execute([$_SESSION['user_id'], $songId]);
$exists = $stmt->fetch();

if ($exists) {
    // Hapus dari favorit
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND song_id = ?");
    $stmt->execute([$_SESSION['user_id'], $songId]);
    echo json_encode(['success' => true, 'liked' => false]);
} else {
    // Tambah ke favorit
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, song_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $songId]);
    echo json_encode(['success' => true, 'liked' => true]);
}
