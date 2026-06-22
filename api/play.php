<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    $pdo = getDB();
    
    // 1. Tambahkan Play Count memanggil Stored Procedure
    $stmt = $pdo->prepare("CALL sp_increment_play_count(?)");
    $stmt->execute([$id]);
    
    // 2. Log History jika user login
    if (isLoggedIn()) {
        $stmt2 = $pdo->prepare("INSERT INTO history (user_id, song_id, duration_played) VALUES (?, ?, 0)");
        $stmt2->execute([$_SESSION['user_id'], $id]);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
