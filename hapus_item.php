<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['id'])) {
    $cart_item_id = $_GET['id'];
    if (isset($_SESSION['cart'][$cart_item_id])) {
        unset($_SESSION['cart'][$cart_item_id]);
    }
}

header('Location: keranjang.php?message=Item berhasil dihapus dari keranjang.&type=success');
exit();
?>
