<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'liked' => false]);
    exit;
}

$songId = $_GET['id'] ?? null;
if (!$songId) {
    echo json_encode(['success' => false, 'liked' => false]);
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND song_id = ?");
$stmt->execute([$_SESSION['user_id'], $songId]);
$exists = $stmt->fetch();

echo json_encode(['success' => true, 'liked' => $exists ? true : false]);
