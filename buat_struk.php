<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = $_GET['order_id'];

// Ambil data pesanan dari database
$stmt = $koneksi->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran - The Premium Bubble</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .receipt {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="receipt">
            <h2>Struk Pembayaran</h2>
            <p><strong>ID Pesanan:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Total Bayar:</strong> Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
            <p><strong>Status Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_status']); ?></p>
            <p><strong>Status Pesanan:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <hr>
            <p class="text-center">Terima kasih telah berbelanja!</p>
        </div>
        <div class="text-center">
            <a href="index.php" class="cta-button">Kembali ke Beranda</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
