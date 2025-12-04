<?php
session_start();
include 'koneksi.php';

// Hanya proses jika metodenya POST dan ada order_id
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    
    $order_id = $_POST['order_id'];

    // 1. Update status pesanan di database
    // Set payment_status menjadi 'Menunggu Konfirmasi' untuk verifikasi manual oleh admin
    $update_query = "UPDATE orders SET payment_status = 'Menunggu Konfirmasi', status = 'Menunggu Konfirmasi' WHERE order_id = ?";
    $stmt = $koneksi->prepare($update_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // 2. Kosongkan keranjang belanja dari session
    unset($_SESSION['cart']);

    // 3. Arahkan ke halaman sukses dengan status untuk menampilkan pesan yang sesuai
    header('Location: pesanan_sukses.php?order_id=' . $order_id . '&status=pending_confirmation');
    exit();

} else {
    // Jika akses tidak sah, kembalikan ke halaman utama
    header('Location: index.php');
    exit();
}
?>
