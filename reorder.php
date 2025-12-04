<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $customer_id = $_SESSION['customer_id'];

    // Verify that the order belongs to the logged-in user
    $verify_order_query = "SELECT customer_id FROM orders WHERE order_id = ? AND customer_id = ?";
    $stmt_verify = $koneksi->prepare($verify_order_query);
    $stmt_verify->bind_param("ii", $order_id, $customer_id);
    $stmt_verify->execute();
    $verify_result = $stmt_verify->get_result();

    if ($verify_result->num_rows === 0) {
        // Order not found or does not belong to the user
        header("location: profil.php?error=Pesanan tidak ditemukan atau bukan milik Anda.");
        exit;
    }
    $stmt_verify->close();

    // Fetch order items
    $items_query = "SELECT product_id, quantity, sugar_level, ice_level, suhu, topping_id FROM order_items WHERE order_id = ?";
    $stmt_items = $koneksi->prepare($items_query);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    while ($item = $items_result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $sugar_level = $item['sugar_level'];
        $ice_level = $item['ice_level'];
        $suhu = $item['suhu'];
        $topping_ids_string = $item['topping_id'];

        $toppings = [];
        if (!empty($topping_ids_string)) {
            $toppings = array_map('intval', explode(',', $topping_ids_string));
        }

        // Create a unique cart item ID
        $cart_item_id = md5($product_id . $sugar_level . $ice_level . $suhu . implode(',', $toppings));

        if (isset($_SESSION['cart'][$cart_item_id])) {
            $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cart_item_id] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'sugar_level' => $sugar_level,
                'ice_level' => $ice_level,
                'suhu' => $suhu,
                'toppings' => $toppings
            ];
        }
    }
    $stmt_items->close();
    $koneksi->close();

    header("location: keranjang.php?message=Item dari pesanan #$order_id berhasil ditambahkan ke keranjang.&type=success");
    exit;
} else {
    header("location: profil.php?error=ID Pesanan tidak valid.");
    exit;
}
?>