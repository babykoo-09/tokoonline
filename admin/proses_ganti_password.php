<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika user belum login, redirect ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    $username = $_SESSION['admin_username'];

    // Validasi input
    if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
        header('Location: ganti_password.php?error=Semua field harus diisi');
        exit();
    }

    if ($new_password !== $confirm_new_password) {
        header('Location: ganti_password.php?error=Password baru tidak cocok');
        exit();
    }

    // Ambil password saat ini dari database
    $stmt = $koneksi->prepare("SELECT password FROM admin_accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Verifikasi password lama
        if (password_verify($old_password, $hashed_password)) {
            // Hash password baru
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password di database
            $update_stmt = $koneksi->prepare("UPDATE admin_accounts SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $new_hashed_password, $username);

            if ($update_stmt->execute()) {
                header('Location: ganti_password.php?status=sukses');
                exit();
            } else {
                header('Location: ganti_password.php?error=Gagal mengubah password');
                exit();
            }
        } else {
            header('Location: ganti_password.php?error=Password lama salah');
            exit();
        }
    } else {
        header('Location: ganti_password.php?error=User tidak ditemukan');
        exit();
    }

    $stmt->close();
    $koneksi->close();
} else {
    header('Location: ganti_password.php');
    exit();
}
?>
