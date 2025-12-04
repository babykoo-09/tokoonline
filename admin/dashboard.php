<?php
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

// Total Pendapatan
$total_revenue = 0;
$total_revenue_query = "SELECT SUM(total_price) as total FROM orders WHERE status = ?";
$stmt = $koneksi->prepare($total_revenue_query);
$status_completed = 'Selesai';
$stmt->bind_param("s", $status_completed);
$stmt->execute();
$total_revenue_result = $stmt->get_result();
if ($row = $total_revenue_result->fetch_assoc()) {
    $total_revenue = $row['total'];
}
$stmt->close();

// Total Pesanan
$total_orders = 0;
$total_orders_query = "SELECT COUNT(order_id) as total FROM orders";
$stmt = $koneksi->prepare($total_orders_query);
$stmt->execute();
$total_orders_result = $stmt->get_result();
if ($row = $total_orders_result->fetch_assoc()) {
    $total_orders = $row['total'];
}
$stmt->close();

// Produk Terlaris
$top_products_query = "SELECT p.name, COUNT(oi.product_id) as total_sales FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY total_sales DESC LIMIT 5";
$stmt = $koneksi->prepare($top_products_query);
$stmt->execute();
$top_products_result = $stmt->get_result();

// Data Penjualan untuk Grafik (contoh data 7 hari terakhir)
$sales_data_query = "SELECT DATE(order_date) as date, SUM(total_price) as daily_total FROM orders WHERE order_date >= CURDATE() - INTERVAL 7 DAY AND status = ? GROUP BY DATE(order_date) ORDER BY date ASC";
$stmt = $koneksi->prepare($sales_data_query);
$status_completed = 'Selesai';
$stmt->bind_param("s", $status_completed);
$stmt->execute();
$sales_data_result = $stmt->get_result();
$sales_labels = [];
$sales_values = [];
while($row = $sales_data_result->fetch_assoc()) {
    $sales_labels[] = date("d M", strtotime($row['date']));
    $sales_values[] = $row['daily_total'];
}
$stmt->close();

?>

<main class="admin-dashboard">
    <div class="container">
        <h1>Dashboard Admin</h1>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Pendapatan</h3>
                <p>Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <p><?php echo $total_orders; ?></p>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Produk Terlaris</h2>
            <ol>
                <?php while($product = $top_products_result->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($product['name']); ?> (<?php echo $product['total_sales']; ?> terjual)</li>
                <?php endwhile; ?>
            </ol>
        </div>

        <div class="dashboard-section">
            <h2>Grafik Penjualan (7 Hari Terakhir)</h2>
            <div class="chart-container">
                <div class="chart">
                    <?php 
                    $max_value = !empty($sales_values) ? max($sales_values) : 0;
                    foreach ($sales_values as $index => $value):
                        $height_percentage = $max_value > 0 ? ($value / $max_value) * 100 : 0;
                    ?>
                        <div class="bar" style="height: <?php echo $height_percentage; ?>%;">
                            <span class="tooltip">Rp <?php echo number_format($value, 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-labels">
                    <?php foreach ($sales_labels as $label):
                    ?>
                        <div class="label"><?php echo $label; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>