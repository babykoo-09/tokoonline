<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Premium Bubble</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="container nav-container">
            <a href="dashboard.php" class="nav-brand">Admin Panel</a>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="index.php">Kelola Menu</a></li>
                <li><a href="lihat_pesanan.php">Manajemen Pesanan</a></li>
                <li><a href="notifikasi.php">Notifikasi <?php if (isset($unread_count) && $unread_count > 0): ?><span class="notification-badge">(<?php echo $unread_count; ?>)</span><?php endif; ?></a></li>
                <li><a href="tambah_pesanan_manual.php">Tambah Pesanan Manual</a></li>
                <li><a href="ganti_password.php">Ganti Password</a></li>
                <li><a href="logout.php">Keluar</a></li>
            </ul>
        </div>
    </nav>
