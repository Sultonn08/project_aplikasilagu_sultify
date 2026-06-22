<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
requireAdmin();
$pdo = getDB();

// Handle toggle status (Suspend / Activate)
if (isset($_GET['toggle_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle_id']]);
    redirect('/admin/users.php');
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna - Sultify Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-topbar">
            <h1>Kelola Pengguna</h1>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </header>
        
        <div class="admin-body">
            <div class="admin-table-card fade-in">
                <div class="admin-table-header">
                    <h3>Daftar Pengguna</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td style="color: var(--text-muted);">#<?= $u['id'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem; background: #333; color: #fff;">
                                            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                        </div>
                                        <div style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($u['full_name']) ?></div>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if($u['is_active']): ?>
                                        <span class="badge badge-green"><i class="fa-solid fa-check" style="margin-right: 4px;"></i> Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-red"><i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Suspend</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a href="?toggle_id=<?= $u['id'] ?>" class="btn-edit btn-sm" style="background: <?= $u['is_active'] ? 'rgba(244,63,94,.1)' : 'rgba(34,197,94,.1)' ?>; color: <?= $u['is_active'] ? '#F43F5E' : '#22C55E' ?>; border: none;">
                                        <?= $u['is_active'] ? 'Suspend' : 'Aktifkan' ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
