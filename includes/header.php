<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sultify - Music Streaming</title>
    <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <div class="app-layout">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Topbar Navigasi -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="topbar-nav-btn" onclick="history.back()"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="topbar-nav-btn" onclick="history.forward()"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <div class="topbar-right">
                    <?php if (isLoggedIn()): ?>
                        <?php
                            $pdo = getDB();
                            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $currentUser = $stmt->fetch();
                            
                            if (!$currentUser) {
                                session_destroy();
                                redirect('/auth/login.php');
                            }
                        ?>
                        <div class="user-menu" onclick="navigateTo('<?= BASE_URL ?>/profile')">
                            <div class="user-avatar">
                                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Avatar" onerror="this.style.display='none'; this.parentNode.innerHTML='<?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>'">
                            </div>
                            <span class="user-menu-name"><?= htmlspecialchars($currentUser['full_name']) ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/auth/logout.php" style="color: var(--text-muted); font-size: 1.1rem; margin-left: 8px;" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/register.php" class="btn-signup">Daftar</a>
                        <a href="<?= BASE_URL ?>/auth/login.php" class="btn-login">Masuk</a>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- Mulai Konten Halaman -->
            <div class="page-content">
