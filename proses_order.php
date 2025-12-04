<?php
session_start();
include 'koneksi.php';
include 'order_helper.php';

// Logika utama untuk memproses pesanan QRIS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_SESSION['cart'])) {
        header('Location: keranjang.php');
        exit();
    }

    // Untuk QRIS, buat pesanan dengan status 'Menunggu Pembayaran'
    $order_id = createOrder($koneksi, $_POST, $_SESSION['cart'], 'Menunggu Pembayaran');

    // Hapus keranjang setelah pesanan dibuat
    unset($_SESSION['cart']);

    // Arahkan ke halaman pembayaran QRIS dengan ID pesanan
    header('Location: qris_payment.php?order_id=' . $order_id);
    exit();

} else {
    // Jika bukan POST, kembalikan ke halaman utama
    header('Location: index.php');
    exit();
}
?>