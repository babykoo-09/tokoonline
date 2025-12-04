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

// Memastikan permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];

        // Daftar status yang valid untuk keamanan
        $valid_statuses = ['Baru', 'Sedang Dibuat', 'Dikirim', 'Selesai', 'Kadaluarsa'];

        if (in_array($new_status, $valid_statuses)) {
            // Siapkan dan jalankan query update
            $query = "UPDATE orders SET status = ? WHERE order_id = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("si", $new_status, $order_id);

            if ($stmt->execute()) {
                // Fetch customer_id for notification
                $customer_id_query = "SELECT customer_id FROM orders WHERE order_id = ?";
                $stmt_customer_id = $koneksi->prepare($customer_id_query);
                $stmt_customer_id->bind_param("i", $order_id);
                $stmt_customer_id->execute();
                $customer_id_result = $stmt_customer_id->get_result();
                $customer_row = $customer_id_result->fetch_assoc();
                $customer_id = $customer_row['customer_id'];
                $stmt_customer_id->close();

                // Create notification if status is 'Dikirim' or 'Selesai'
                if ($customer_id && ($new_status == 'Dikirim' || $new_status == 'Selesai')) {
                    $message = "Pesanan Anda #" . $order_id . " telah berstatus: " . $new_status . ".";
                    $insert_notification_query = "INSERT INTO notifications (customer_id, order_id, message) VALUES (?, ?, ?)";
                    $stmt_notification = $koneksi->prepare($insert_notification_query);
                    $stmt_notification->bind_param("iis", $customer_id, $order_id, $message);
                    $stmt_notification->execute();
                    $stmt_notification->close();
                }

                // Jika berhasil, redirect dengan status sukses
                header('Location: lihat_pesanan.php?message=Status pesanan berhasil diperbarui!&type=success');
                exit();
            } else {
                // Jika gagal, redirect dengan status error
                header('Location: lihat_pesanan.php?message=Gagal memperbarui status pesanan.&type=error');
                exit();
            }
            $stmt->close();
        }
    }
}

// Jika bukan POST atau input tidak valid, redirect ke halaman utama admin
header('Location: index.php');
exit();
?>
