<?php
session_start();
include 'koneksi.php';
include 'order_helper.php'; // To access the createOrder function

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_SESSION['cart'])) {
        header('Location: keranjang.php');
        exit();
    }

    // Buat pesanan dengan status pembayaran 'Pending'
    $order_id = createOrder($koneksi, $_POST, $_SESSION['cart'], 'Pending');

    // Tambahkan ongkir untuk COD
    $ongkir = 10000; // Contoh ongkir tetap
    $update_stmt = $koneksi->prepare("UPDATE orders SET total_price = total_price + ? WHERE order_id = ?");
    $update_stmt->bind_param("di", $ongkir, $order_id);
    $update_stmt->execute();
    $update_stmt->close();

    unset($_SESSION['cart']);
    header('Location: pesanan_sukses.php?order_id=' . $order_id . '&method=cod&ongkir=' . $ongkir);
    exit();

} else {
    header('Location: index.php');
    exit();
}
?>