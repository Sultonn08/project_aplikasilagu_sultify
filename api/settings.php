<?php
// api/settings.php — Settings AJAX handler
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak didukung.']);
    exit;
}

$pdo    = getDB();
$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
    exit;
}

// ─── UPDATE PROFILE ────────────────────────────────────────────────
if ($action === 'update_profile') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $gender   = trim($_POST['gender'] ?? '');
    $dob      = trim($_POST['dob'] ?? '');

    if (empty($fullname) || empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nama Lengkap, Username, dan Email wajib diisi.']);
        exit;
    }

    if ($gender !== '' && !in_array($gender, ['male', 'female', 'other'])) {
        echo json_encode(['success' => false, 'message' => 'Pilihan jenis kelamin tidak valid.']);
        exit;
    }

    // Check unique username / email
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmtCheck->execute([$username, $email, $userId]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username atau Email sudah digunakan oleh pengguna lain.']);
        exit;
    }

    // Handle avatar upload
    $avatarName = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath  = $_FILES['avatar']['tmp_name'];
        $fileName     = $_FILES['avatar']['name'];
        $fileSize     = $_FILES['avatar']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt   = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExtension, $allowedExt)) {
            echo json_encode(['success' => false, 'message' => 'Format file avatar tidak valid (JPG, PNG, WEBP).']);
            exit;
        }
        if ($fileSize > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran avatar maksimal 2MB.']);
            exit;
        }

        $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $destPath    = __DIR__ . '/../assets/images/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Delete old custom avatar
            if ($user['avatar'] !== 'default_avatar.png' && file_exists(__DIR__ . '/../assets/images/' . $user['avatar'])) {
                @unlink(__DIR__ . '/../assets/images/' . $user['avatar']);
            }
            $avatarName = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file avatar ke server.']);
            exit;
        }
    }

    $genderVal = ($gender === '') ? null : $gender;
    $dobVal    = ($dob === '')    ? null : $dob;
    $bioVal    = ($bio === '')    ? null : $bio;

    $stmtUpdate = $pdo->prepare(
        "UPDATE users SET full_name = ?, username = ?, email = ?, bio = ?, gender = ?, date_of_birth = ?, avatar = ? WHERE id = ?"
    );
    if ($stmtUpdate->execute([$fullname, $username, $email, $bioVal, $genderVal, $dobVal, $avatarName, $userId])) {
        echo json_encode([
            'success'   => true,
            'message'   => 'Profil berhasil diperbarui.',
            'avatar'    => $avatarName,
            'full_name' => $fullname,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil di database.']);
    }
    exit;
}

// ─── CHANGE PASSWORD ────────────────────────────────────────────────
if ($action === 'change_password') {
    $oldPass     = $_POST['old_password']     ?? '';
    $newPass     = $_POST['new_password']     ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
        echo json_encode(['success' => false, 'message' => 'Semua field password wajib diisi.']);
        exit;
    }
    if ($newPass !== $confirmPass) {
        echo json_encode(['success' => false, 'message' => 'Konfirmasi password baru tidak cocok.']);
        exit;
    }
    if (strlen($newPass) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password baru minimal 6 karakter.']);
        exit;
    }
    if (!password_verify($oldPass, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password saat ini salah.']);
        exit;
    }

    $newHashed = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmtPass  = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmtPass->execute([$newHashed, $userId])) {
        echo json_encode(['success' => true, 'message' => 'Password berhasil diubah.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah password di database.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
