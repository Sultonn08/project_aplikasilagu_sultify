<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-logo" style="margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <img src="<?= BASE_URL ?>/assets/images/sultify-logo.png" alt="Sultify Logo" style="height: 120px; margin: -30px 0;">
        <div style="background: linear-gradient(135deg, #fff 40%, #1e90ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; font-size: 1.2rem;">Admin</div>
    </div>
    
    <nav class="sidebar-nav admin-nav">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie nav-icon"></i> Dashboard Overview</a>
        <a href="<?= BASE_URL ?>/admin/upload.php" class="nav-item <?= $currentPage == 'upload.php' ? 'active' : '' ?>"><i class="fa-solid fa-music nav-icon"></i> Tambah Lagu</a>
        <a href="<?= BASE_URL ?>/admin/songs.php" class="nav-item <?= $currentPage == 'songs.php' ? 'active' : '' ?>"><i class="fa-solid fa-list-music nav-icon"></i> Kelola Lagu</a>
        <a href="<?= BASE_URL ?>/admin/users.php" class="nav-item <?= $currentPage == 'users.php' ? 'active' : '' ?>"><i class="fa-solid fa-users nav-icon"></i> Kelola Pengguna</a>
        <a href="<?= BASE_URL ?>/admin/artists.php" class="nav-item <?= $currentPage == 'artists.php' ? 'active' : '' ?>"><i class="fa-solid fa-microphone nav-icon"></i> Kelola Artis</a>
        <a href="<?= BASE_URL ?>/admin/albums.php" class="nav-item <?= $currentPage == 'albums.php' ? 'active' : '' ?>"><i class="fa-solid fa-compact-disc nav-icon"></i> Kelola Album</a>
    </nav>
</aside>
