<?php
session_start();
require_once 'config/database.php';

$page = $_GET['page'] ?? 'home';
if (empty($page)) $page = 'home';

// Routing untuk halaman admin
if (strpos($page, 'admin') === 0) {
    require_once 'admin/dashboard.php'; 
    exit;
}

// Daftar rute halaman yang valid
$valid_pages = [
    'home'      => 'pages/home.php',
    'search'    => 'pages/search.php',
    'library'   => 'pages/library.php',
    'playlist'  => 'pages/playlist.php',
    'artist'    => 'pages/artist.php',
    'profile'   => 'pages/profile.php',
    'favorites' => 'pages/favorites.php',
    'album'     => 'pages/album.php',
];

// Fallback 404 (Untuk saat ini redirect ke home)
if (array_key_exists($page, $valid_pages)) {
    $file_to_include = $valid_pages[$page];
} else {
    $file_to_include = 'pages/home.php';
    $page = 'home';
}

require_once 'includes/header.php';
require_once $file_to_include;
require_once 'includes/footer.php';
