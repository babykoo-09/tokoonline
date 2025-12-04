<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['new_payment_status'])) {
    $order_id = $_POST['order_id'];
    $new_payment_status = $_POST['new_payment_status'];

    // Validate new_payment_status
    if ($new_payment_status === 'Pending' || $new_payment_status === 'Lunas') {
        $stmt = $koneksi->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_payment_status, $order_id);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: lihat_pesanan.php');
exit();
?>