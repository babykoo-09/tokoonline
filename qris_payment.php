<?php
session_start();
include 'koneksi.php';

// Pastikan ada order_id di URL
if (!isset($_GET['order_id'])) {
    header('Location: checkout.php');
    exit();
}

$order_id = $_GET['order_id'];

// Ambil detail pesanan dari database
$stmt = $koneksi->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

// Jika pesanan tidak ditemukan, kembalikan
if (!$order) {
    header('Location: index.php');
    exit();
}

// Jika pesanan sudah lunas, arahkan ke halaman sukses
if ($order['payment_status'] == 'Lunas') {
    header('Location: pesanan_sukses.php?order_id=' . $order_id);
    exit();
}

$total_price = $order['total_price'];

// URL untuk gambar QRIS statis (rekayasa)
// Anda bisa mengganti URL ini dengan gambar QRIS asli Anda
$qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=https://example.com/pay?order_id=' . $order_id;

include 'header.php';
?>

<main class="qris-payment-page-section">
    <div class="container">
        <h1>Pembayaran QRIS</h1>
        <div class="payment-details">
            <p>Silakan scan QR Code di bawah ini untuk menyelesaikan pembayaran Anda.</p>
            <p>Order ID: <strong>#<?php echo $order_id; ?></strong></p>
            
            <div class="qr-code-container">
                <img src="<?php echo htmlspecialchars($qr_image_url); ?>" alt="QRIS Code">
            </div>
            
            <p class="total-amount">Total yang harus dibayar: <strong>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></p>
            
            <p class="note">Setelah Anda melakukan pembayaran, klik tombol di bawah ini untuk konfirmasi.</p>

            <form action="proses_qris_payment.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <button type="submit" class="cta-button">Saya Sudah Membayar</button>
            </form>
        </div>
    </div>
</main>

<style>
.payment-details {
    text-align: center;
    max-width: 400px;
    margin: 2rem auto;
    padding: 2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}
.qr-code-container {
    margin: 1.5rem 0;
}
.qr-code-container img {
    max-width: 100%;
    height: auto;
    border: 5px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.total-amount {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333;
}
.note {
    font-size: 0.9rem;
    color: #666;
    margin-top: 1rem;
}
</style>

<?php include 'footer.php'; ?>