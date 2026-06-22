<?php
session_start();
require_once '../config/database.php';

if (isLoggedIn()) {
    redirect('/');
}

$error = '';
$adminMode = isset($_GET['admin']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        $pdo = getDB();
        if ($adminMode) {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                redirect('/admin/dashboard.php');
            } else {
                $error = 'Kredensial admin salah atau akun tidak aktif.';
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
                redirect('/');
            } else {
                $error = 'Email atau password salah.';
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
    <title>Masuk - Sultify</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card fade-in">
    <div class="auth-logo" style="margin-bottom: 24px;">
        <img src="<?= BASE_URL ?>/assets/images/sultify-logo.png" alt="Sultify Logo" style="height: 200px; display: block; margin: -50px auto -20px;">
    </div>
    
    <h2 class="auth-title">Masuk ke Sultify</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email anda" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        
        <button type="submit" class="btn-primary-full">Masuk</button>
    </form>
    
    <?php if (!$adminMode): ?>
        <div class="auth-footer">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
