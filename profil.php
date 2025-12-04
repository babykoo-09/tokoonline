
<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include 'koneksi.php';

$customer_id = $_SESSION['customer_id'];

// Ambil data pelanggan
$stmt = $koneksi->prepare("SELECT nama, email, phone, default_address FROM customer_accounts WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($nama, $email, $phone, $default_address);
$stmt->fetch();
$stmt->close();

// Ambil riwayat pesanan
$order_history = [];
$stmt = $koneksi->prepare("SELECT order_id, order_date, total_price, status, payment_status FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $order_history[] = $row;
}
    $stmt->close();

    // Ambil notifikasi pengguna
    $notifications = [];
    $stmt = $koneksi->prepare("SELECT notification_id, message, created_at FROM notifications WHERE customer_id = ? AND is_read = FALSE ORDER BY created_at DESC");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    $koneksi->close();
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Profil Saya - The Premium Bubble</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <?php include 'header.php'; ?>

        <div class="container">
            <h2>Profil Saya</h2>
                    <div class="profil-info">
                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Telepon:</strong> <?php echo htmlspecialchars($phone); ?></p>
                        <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($default_address)); ?></p>
                    </div>
            
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
            <h3>Notifikasi Anda</h3>
            <?php if (count($notifications) > 0): ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small><?php echo date("d M Y, H:i", strtotime($notification['created_at'])); ?></small>
                            <a href="mark_notification_as_read.php?id=<?php echo $notification['notification_id']; ?>" class="btn-small">Tandai Sudah Dibaca</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Tidak ada notifikasi baru.</p>
            <?php endif; ?>

            <h3>Riwayat Pesanan</h3>
            <?php if (count($order_history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_history as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo date("d M Y, H:i", strtotime($order['order_date'])); ?></td>
                                <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                                <td><a href="reorder.php?order_id=<?php echo $order['order_id']; ?>" class="btn-small">Pesan Ulang</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Anda belum memiliki riwayat pesanan.</p>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <?php include 'footer.php'; ?>
    </body>
    </html>
