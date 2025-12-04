<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input dasar
    if (!isset($_POST['product_id'], $_POST['customer_name'], $_POST['no_telepon'], $_POST['alamat'], $_POST['price'])) {
        // Jika ada data yang kurang, kembali ke menu
        header('Location: menu.php');
        exit();
    }

    // Ambil dan bersihkan data dari form
    $product_id = intval($_POST['product_id']);
    $customer_name = $koneksi->real_escape_string($_POST['customer_name']);
    $no_telepon = $koneksi->real_escape_string($_POST['no_telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $base_price = floatval($_POST['price']); // Base price of the product
    $sugar_level = $koneksi->real_escape_string($_POST['sugar_level']);
    $ice_level = $koneksi->real_escape_string($_POST['ice_level']);
    $suhu = $koneksi->real_escape_string($_POST['suhu']);
    $toppings_array = isset($_POST['toppings']) ? $_POST['toppings'] : [];
    $quantity = 1; // Kuantitas diatur 1 untuk pemesanan langsung

    $total_price = $base_price;
    $topping_ids_string = null;

    // Calculate topping price and create topping_ids_string
    if (!empty($toppings_array)) {
        $toppings_ids = array_map('intval', $toppings_array);
        $placeholders = implode(',', array_fill(0, count($toppings_ids), '?'));
        $types = str_repeat('i', count($toppings_ids));
        $toppings_query = "SELECT SUM(price) as total_topping_price FROM toppings WHERE topping_id IN ($placeholders)";
        $stmt_toppings = $koneksi->prepare($toppings_query);
        $stmt_toppings->bind_param($types, ...$toppings_ids);
        $stmt_toppings->execute();
        $toppings_result = $stmt_toppings->get_result();
        $topping_price_row = $toppings_result->fetch_assoc();
        $total_price += $topping_price_row['total_topping_price'];
        $topping_ids_string = implode(',', $toppings_array);
    }

    // 1. Masukkan data pesanan utama ke tabel `orders`
    $insert_order_query = "INSERT INTO orders (customer_name, no_telepon, alamat, total_price, status) VALUES (?, ?, ?, ?, 'Baru')";
    $order_stmt = $koneksi->prepare($insert_order_query);
    $order_stmt->bind_param("sssd", $customer_name, $no_telepon, $alamat, $total_price);
    
    if ($order_stmt->execute()) {
        $order_id = $koneksi->insert_id;
        $order_stmt->close();

        // 2. Masukkan detail item pesanan ke tabel `order_items`
        $insert_item_query = "INSERT INTO order_items (order_id, product_id, quantity, sugar_level, ice_level, suhu, topping_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $item_stmt = $koneksi->prepare($insert_item_query);
        $item_stmt->bind_param("iiissss", $order_id, $product_id, $quantity, $sugar_level, $ice_level, $suhu, $topping_ids_string);
        
        if ($item_stmt->execute()) {
            $item_stmt->close();
            // 3. Arahkan ke halaman sukses
            header('Location: pesanan_sukses.php?order_id=' . $order_id);
            exit();
        } else {
            error_log("Error saving order item: " . $item_stmt->error);
            echo "Error: Gagal menyimpan item pesanan. Silakan coba lagi.";
            $koneksi->query("DELETE FROM orders WHERE order_id = $order_id"); // Rollback order
        }
    } else {
        error_log("Error creating order: " . $order_stmt->error);
        echo "Error: Gagal membuat pesanan. Silakan coba lagi.";
    }

} else {
    // Jika bukan metode POST, arahkan kembali ke halaman utama
    header('Location: index.php');
    exit();
}
?>