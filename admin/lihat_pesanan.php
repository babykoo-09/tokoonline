<?php
// Memulai sesi dan memeriksa apakah admin sudah login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php'; // Koneksi ke database ada di folder induk

// Ambil jumlah notifikasi admin yang belum dibaca
$unread_notifications_query = "SELECT COUNT(id) as unread_count FROM admin_notifications WHERE is_read = 0";
$unread_result = $koneksi->query($unread_notifications_query);
$unread_count = $unread_result->fetch_assoc()['unread_count'];

include 'header.php';

$filter_status = $_GET['status_filter'] ?? '';
$search_term = $_GET['search'] ?? '';

// Query untuk menghitung pesanan baru
$new_orders_count = 0;
$new_orders_query = "SELECT COUNT(order_id) as count FROM orders WHERE status = 'Baru' AND payment_status = 'Pending'";
$stmt_new_orders = $koneksi->prepare($new_orders_query);
$stmt_new_orders->execute();
$new_orders_result = $stmt_new_orders->get_result()->fetch_assoc();
$new_orders_count = $new_orders_result['count'];
$stmt_new_orders->close();

$payment_status_filter = $_GET['payment_status_filter'] ?? '';

$query = "SELECT order_id, customer_name, no_telepon, alamat, order_date, total_price, status, payment_status FROM orders WHERE 1=1";
$params = [];
$types = '';

if (!empty($filter_status)) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($payment_status_filter)) {
    $query .= " AND payment_status = ?";
    $params[] = $payment_status_filter;
    $types .= 's';
}

if (!empty($search_term)) {
    $query .= " AND (customer_name LIKE ? OR order_id LIKE ?)";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
    $types .= 'ss';
}

$query .= " ORDER BY order_date DESC";

$stmt = $koneksi->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="admin-page">
    <div class="container">
        <h1 class="text-center">Manajemen Pesanan</h1>

        <?php if ($new_orders_count > 0): ?>
            <div class="notification success show">
                Anda memiliki <strong><?php echo $new_orders_count; ?></strong> pesanan baru yang menunggu diproses!
            </div>
        <?php endif; ?>

        <div id="notification" class="notification"></div>

        <?php if (isset($_GET['message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const notification = document.getElementById('notification');
                    notification.textContent = "<?php echo htmlspecialchars($_GET['message']); ?>";
                    notification.classList.add('show');
                    if ("<?php echo htmlspecialchars($_GET['type']); ?>" === 'success') {
                        notification.classList.add('success');
                    } else {
                        notification.classList.add('error');
                    }
                    setTimeout(() => {
                        notification.classList.remove('show');
                    }, 3000);
                });
            </script>
        <?php endif; ?>

        <div class="filter-form">
            <form action="" method="GET">
                <select name="status_filter">
                    <option value="" <?php if(empty($filter_status)) echo 'selected'; ?>>Semua Status</option>
                    <option value="Baru" <?php if($filter_status == 'Baru') echo 'selected'; ?>>Baru</option>
                    <option value="Sedang Dibuat" <?php if($filter_status == 'Sedang Dibuat') echo 'selected'; ?>>Sedang Dibuat</option>
                    <option value="Dikirim" <?php if($filter_status == 'Dikirim') echo 'selected'; ?>>Dikirim</option>
                    <option value="Selesai" <?php if($filter_status == 'Selesai') echo 'selected'; ?>>Selesai</option>
                    <option value="Kadaluarsa" <?php if($filter_status == 'Kadaluarsa') echo 'selected'; ?>>Kadaluarsa</option>
                </select>
                <select name="payment_status_filter">
                    <option value="" <?php if(empty($_GET['payment_status_filter'])) echo 'selected'; ?>>Semua Status Pembayaran</option>
                    <option value="Pending" <?php if(($_GET['payment_status_filter'] ?? '') == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Lunas" <?php if(($_GET['payment_status_filter'] ?? '') == 'Lunas') echo 'selected'; ?>>Lunas</option>
                </select>
                <input type="text" name="search" placeholder="Cari Nama/ID Pesanan..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Alamat</th>
                        <th>Tanggal</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="<?php echo ($row['status'] == 'Baru' && $row['payment_status'] == 'Pending') ? 'new-order-row' : ''; ?>">
                                <td data-label="ID Pesanan"><a href="detail_pesanan.php?id=<?php echo $row['order_id']; ?>"><?php echo htmlspecialchars($row['order_id']); ?></a></td>
                                <td data-label="Nama Pelanggan"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td data-label="No. Telepon"><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                                <td data-label="Alamat"><?php echo htmlspecialchars($row['alamat']); ?></td>
                                <td data-label="Tanggal"><?php echo date('d M Y, H:i', strtotime($row['order_date'])); ?></td>
                                <td data-label="Total Harga">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></td>
                                <td data-label="Status"><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td data-label="Status Pembayaran"><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['payment_status'])); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                                <td data-label="Aksi" class="action-links">
                                    <a href="detail_pesanan.php?id=<?php echo $row['order_id']; ?>">Detail</a>
                                    <a href="cetak_struk.php?id=<?php echo $row['order_id']; ?>" target="_blank">Cetak Struk</a>
                                    <form action="update_status.php" method="POST" class="inline-form">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <select name="new_status">
                                            <option value="Baru" <?php if($row['status'] == 'Baru') echo 'selected'; ?>>Baru</option>
                                            <option value="Sedang Dibuat" <?php if($row['status'] == 'Sedang Dibuat') echo 'selected'; ?>>Sedang Dibuat</option>
                                            <option value="Dikirim" <?php if($row['status'] == 'Dikirim') echo 'selected'; ?>>Dikirim</option>
                                            <option value="Selesai" <?php if($row['status'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                                            <option value="Kadaluarsa" <?php if($row['status'] == 'Kadaluarsa') echo 'selected'; ?>>Kadaluarsa</option>
                                        </select>
                                        <button type="submit">Update Status</button>
                                    </form>
                                    <form action="update_payment_status.php" method="POST" class="inline-form">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <select name="new_payment_status">
                                            <option value="Pending" <?php if($row['payment_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Lunas" <?php if($row['payment_status'] == 'Lunas') echo 'selected'; ?>>Lunas</option>
                                        </select>
                                        <button type="submit">Update Payment</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center no-orders-message">Belum ada pesanan yang masuk atau tidak ada hasil yang cocok dengan filter Anda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

<?php include 'footer.php'; ?>