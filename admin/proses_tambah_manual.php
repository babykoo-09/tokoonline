<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi dasar
    if (empty($_POST['customer_name']) || empty($_POST['no_telepon']) || empty($_POST['alamat']) || empty($_POST['items'])) {
        die('Error: Semua informasi pelanggan dan setidaknya satu item harus diisi.');
    }

    $customer_name = $koneksi->real_escape_string($_POST['customer_name']);
    $no_telepon = $koneksi->real_escape_string($_POST['no_telepon']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $items = $_POST['items'];
    $total_price = 0;

    // Ambil semua harga produk yang relevan dalam satu query untuk efisiensi
    $product_ids = array_column($items, 'product_id');
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    $price_query = "SELECT id, price FROM products WHERE id IN ($placeholders)";
    $price_stmt = $koneksi->prepare($price_query);
    $price_stmt->bind_param($types, ...$product_ids);
    $price_stmt->execute();
    $price_result = $price_stmt->get_result();
    $prices = array_column($price_result->fetch_all(MYSQLI_ASSOC), 'price', 'id');

    // Hitung total harga
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        if (isset($prices[$product_id])) {
            $total_price += $prices[$product_id] * $quantity;
        }
    }

    // 1. Masukkan ke tabel `orders`
    $status = 'Baru'; 
    $insert_order_query = "INSERT INTO orders (customer_id, customer_name, no_telepon, alamat, total_price, status) VALUES (?, ?, ?, ?, ?, ?)";
    $order_stmt = $koneksi->prepare($insert_order_query);
    $order_stmt->bind_param("isssds", $customer_id, $customer_name, $no_telepon, $alamat, $total_price, $status);
    $order_stmt->execute();
    $order_id = $koneksi->insert_id;
    $order_stmt->close();

    // 2. Masukkan ke tabel `order_items`
    $insert_item_query = "INSERT INTO order_items (order_id, product_id, quantity, sugar_level, ice_level, suhu, topping_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $item_stmt = $koneksi->prepare($insert_item_query);

    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $sugar_level = $item['sugar_level'];
        $ice_level = $item['ice_level'];
        $suhu = $item['suhu'] ?? 'Dingin'; // Default to Dingin if not provided
        $toppings_array = $item['toppings'] ?? [];
        $topping_ids_string = !empty($toppings_array) ? implode(',', array_map('intval', $toppings_array)) : null;

        $item_stmt->bind_param("iiissss", $order_id, $product_id, $quantity, $sugar_level, $ice_level, $suhu, $topping_ids_string);
        $item_stmt->execute();
    }
    $item_stmt->close();

    // 3. Redirect ke halaman daftar pesanan dengan pesan sukses
    header('Location: lihat_pesanan.php?status=add_success');
    exit();

} else {
    header('Location: index.php');
    exit();
}
?>
