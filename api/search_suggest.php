<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';

if (empty($q)) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$pdo = getDB();

// Search for songs
$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.cover, s.file_path, s.duration, 
           a.name AS artist_name 
    FROM songs s 
    LEFT JOIN artists a ON s.artist_id = a.id 
    WHERE s.title LIKE ? OR a.name LIKE ?
    LIMIT 5
");
$stmt->execute(["%$q%", "%$q%"]);
$songs = $stmt->fetchAll();

$results = [];
foreach ($songs as $song) {
    $results[] = [
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist_name'] ?? 'Unknown',
        'cover' => getCoverUrl($song['cover']),
        'file_path' => BASE_URL . '/uploads/' . $song['file_path']
    ];
}

echo json_encode(['success' => true, 'results' => $results]);
