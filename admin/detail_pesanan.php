<?php
// Memulai sesi dan memeriksa apakah admin sudah login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

// Ambil jumlah notifikasi admin yang belum dibaca
$unread_notifications_query = "SELECT COUNT(id) as unread_count FROM admin_notifications WHERE is_read = 0";
$unread_result = $koneksi->query($unread_notifications_query);
$unread_count = $unread_result->fetch_assoc()['unread_count'];

include 'header.php';

// Pastikan ID pesanan ada di URL
if (!isset($_GET['id'])) {
    header('Location: lihat_pesanan.php');
    exit();
}

$order_id = $_GET['id'];

// Ambil data pesanan utama
$order_query = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $koneksi->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
if ($order_result->num_rows === 0) {
    echo "<p>Pesanan tidak ditemukan.</p>";
    include 'footer.php';
    exit();
}
$order = $order_result->fetch_assoc();

// Ambil item-item yang terkait dengan pesanan
$items_query = "SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$stmt = $koneksi->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>

<main class="admin-page">
    <div class="container">
        <a href="lihat_pesanan.php" class="back-link">&larr; Kembali ke Daftar Pesanan</a>
        <h1 class="text-center">Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>

        <div class="order-details-card">
            <h2>Informasi Utama</h2>
            <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($order['no_telepon']); ?></p>
            <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($order['alamat'])); ?></p>
            <p><strong>Tanggal Pesan:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Total Harga:</strong> Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
            <p><strong>Status:</strong> <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>
            <p><strong>Status Pembayaran:</strong> <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['payment_status'])); ?>"><?php echo htmlspecialchars($order['payment_status']); ?></span></p>
        </div>

        <div class="order-items-card">
            <h2>Item Pesanan</h2>
            <?php if ($items_result->num_rows > 0): ?>
                <ul>
                    <?php while($item = $items_result->fetch_assoc()): 
                        $topping_names = [];
                        if (!empty($item['topping_id'])) {
                            $topping_ids = explode(',', $item['topping_id']);
                            $topping_placeholders = implode(',', array_fill(0, count($topping_ids), '?'));
                            $topping_types = str_repeat('i', count($topping_ids));
                            $topping_query = "SELECT name FROM toppings WHERE topping_id IN ($topping_placeholders)";
                            $topping_stmt = $koneksi->prepare($topping_query);
                            $topping_stmt->bind_param($topping_types, ...$topping_ids);
                            $topping_stmt->execute();
                            $topping_result = $topping_stmt->get_result();
                            while($topping_row = $topping_result->fetch_assoc()) {
                                $topping_names[] = $topping_row['name'];
                            }
                            $topping_stmt->close();
                        }
                    ?>
                        <li>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            (x<?php echo htmlspecialchars($item['quantity']); ?>)
                            <br>
                            <small>Gula: <?php echo htmlspecialchars($item['sugar_level']); ?>%, Es: <?php echo htmlspecialchars($item['ice_level']); ?>, Suhu: <?php echo htmlspecialchars($item['suhu']); ?></small>
                            <?php if (!empty($topping_names)): ?>
                                <br><small>Topping: <?php echo htmlspecialchars(implode(', ', $topping_names)); ?></small>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Tidak ada item dalam pesanan ini.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>