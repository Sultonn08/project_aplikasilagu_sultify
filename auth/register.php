<?php
session_start();
require_once '../config/database.php';

if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $pdo = getDB();
        
        // Cek email & username unique
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Email atau Username sudah digunakan.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, username, email, password, is_active, is_verified) VALUES (?, ?, ?, ?, 1, 1)"); // Otomatis verified untuk kemudahan demo
            
            if ($stmtInsert->execute([$fullname, $username, $email, $hashed])) {
                $success = 'Pendaftaran berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan sistem.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sultify</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card fade-in" style="max-width: 480px;">
    <div class="auth-logo" style="margin-bottom: 24px;">
        <img src="<?= BASE_URL ?>/assets/images/sultify-logo.png" alt="Sultify Logo" style="height: 200px; display: block; margin: -50px auto -20px;">
    </div>
    
    <h2 class="auth-title">Daftar Akun Baru</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="fullname" placeholder="Nama anda" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username unik" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Alamat email aktif" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Pass</label>
                <input type="password" name="password_confirm" placeholder="Ulangi password" required>
            </div>
        </div>
        
        <button type="submit" class="btn-primary-full">Daftar Sekarang</button>
    </form>
    
    <div class="auth-footer">
        Sudah punya akun? <a href="login.php">Masuk disini</a>
    </div>
</div>

</body>
</html>
