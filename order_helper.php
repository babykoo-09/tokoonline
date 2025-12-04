<?php

// Fungsi untuk membuat pesanan dan memasukkannya ke database
function createOrder($koneksi, $post_data, $cart_items, $initial_payment_status) {
    $customer_id = $post_data['customer_id'] ?? null;
    // Jika customer_id tidak ada (guest), gunakan nama dari form
    $customer_name = $customer_id ? null : ($post_data['customer_name'] ?? 'Guest');
    
    // Jika customer_id ada, ambil nama dari database
    if ($customer_id) {
        $stmt = $koneksi->prepare("SELECT nama FROM customer_accounts WHERE id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $customer_name = $user['nama'];
        }
        $stmt->close();
    }

    $no_telepon = $post_data['no_telepon'];
    $alamat = $post_data['alamat'];

    $total_price = 0;
    $order_items_data = [];

    foreach ($cart_items as $cart_item_id => $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        $product_query = "SELECT price FROM products WHERE id = ?";
        $stmt = $koneksi->prepare($product_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_result = $stmt->get_result();
        $product = $product_result->fetch_assoc();
        $stmt->close();

        if (!$product) {
            continue; 
        }

        $toppings_price = 0;
        if (!empty($item['toppings'])) {
            $toppings_ids = implode(',', array_map('intval', $item['toppings']));
            $toppings_query = "SELECT SUM(price) as total_topping_price FROM toppings WHERE topping_id IN ($toppings_ids)";
            $toppings_result = $koneksi->query($toppings_query);
            $topping_price_row = $toppings_result->fetch_assoc();
            $toppings_price = $topping_price_row['total_topping_price'];
        }
        
        $item_price = ($product['price'] + $toppings_price) * $quantity;
        $total_price += $item_price;
        $order_items_data[] = array_merge($item, ['item_price' => $item_price]);
    }

    // 1. Masukkan ke tabel `orders`
    $insert_order_query = "INSERT INTO orders (customer_id, customer_name, no_telepon, alamat, total_price, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $koneksi->prepare($insert_order_query);
    $order_status = 'Baru'; // Status awal pesanan
    $order_stmt->bind_param("isssdis", $customer_id, $customer_name, $no_telepon, $alamat, $total_price, $order_status, $initial_payment_status);
    $order_stmt->execute();
    $order_id = $koneksi->insert_id;
    $order_stmt->close();

    // Buat notifikasi untuk admin
    $admin_message = "Pesanan baru telah diterima dengan ID #{$order_id}";
    $insert_admin_notification_query = "INSERT INTO admin_notifications (order_id, message) VALUES (?, ?)";
    $admin_notification_stmt = $koneksi->prepare($insert_admin_notification_query);
    $admin_notification_stmt->bind_param("is", $order_id, $admin_message);
    $admin_notification_stmt->execute();
    $admin_notification_stmt->close();

    // 2. Masukkan ke tabel `order_items`
    $insert_item_query = "INSERT INTO order_items (order_id, product_id, quantity, item_price, sugar_level, ice_level, suhu, topping_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($insert_item_query);

    foreach ($order_items_data as $order_item) {
        $product_id = $order_item['product_id'];
        $quantity = $order_item['quantity'];
        $item_price = $order_item['item_price'];
        $sugar_level = $order_item['sugar_level'];
        $ice_level = $order_item['ice_level'];
        $suhu = $order_item['suhu'];
        $toppings = $order_item['toppings'];

        $topping_ids_string = !empty($toppings) ? implode(',', $toppings) : null;

        $stmt->bind_param("iiidssss", $order_id, $product_id, $quantity, $item_price, $sugar_level, $ice_level, $suhu, $topping_ids_string);
        $stmt->execute();
    }
    $stmt->close();

    return $order_id;
}
