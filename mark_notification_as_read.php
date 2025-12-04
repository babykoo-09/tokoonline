<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("location: profil.php?error=ID Notifikasi tidak ditemukan.");
    exit;
}

$notification_id = intval($_GET['id']);
$customer_id = $_SESSION['customer_id'];

// Update notification status to read
$update_query = "UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND customer_id = ?";
$stmt = $koneksi->prepare($update_query);
$stmt->bind_param("ii", $notification_id, $customer_id);

if ($stmt->execute()) {
    header("location: profil.php?message=Notifikasi berhasil ditandai sudah dibaca.&type=success");
} else {
    header("location: profil.php?error=Gagal menandai notifikasi sudah dibaca.&type=error");
}

$stmt->close();
$koneksi->close();
exit();
?>