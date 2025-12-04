<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

// Mark all unread notifications as read
$koneksi->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");

// Fetch all notifications
$notifications_query = "SELECT id, order_id, message, created_at FROM admin_notifications ORDER BY created_at DESC";
$notifications_result = $koneksi->query($notifications_query);

// Set unread count to 0 since we just marked them all as read
$unread_count = 0;

include 'header.php';

?>

<main class="admin-page">
    <div class="container">
        <h1>Notifikasi Admin</h1>

        <div class="notifications-list">
            <?php if ($notifications_result->num_rows > 0): ?>
                <?php while($notification = $notifications_result->fetch_assoc()): ?>
                    <div class="notification-item">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small><?php echo date("d M Y, H:i", strtotime($notification['created_at'])); ?></small>
                        <?php if (!empty($notification['order_id'])): ?>
                            <a href="detail_pesanan.php?id=<?php echo $notification['order_id']; ?>" class="btn-small">Lihat Pesanan</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada notifikasi.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>