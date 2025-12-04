<?php 
include 'koneksi.php';
include 'header.php'; 

// Ambil parameter dari URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$method = isset($_GET['method']) ? htmlspecialchars($_GET['method']) : '';
$ongkir = isset($_GET['ongkir']) ? floatval($_GET['ongkir']) : 0;

$message = '';
$page_title = 'Pesanan Berhasil Dibuat!';
$page_content = 'Terima kasih! Pesanan Anda ' . ($order_id ? 'dengan nomor <strong>#' . $order_id . '</strong>' : '') . ' telah kami terima dan akan segera diproses.';
$redirect_to_menu = true;

// Logika untuk pesanan COD
if ($method === 'cod' && $order_id > 0) {
    $stmt = $koneksi->prepare("SELECT total_price FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $total_price = $order['total_price'];
        $page_title = 'Pesanan COD Berhasil Dibuat!';
        $page_content = 'Terima kasih! Pesanan Anda dengan nomor <strong>#' . $order_id . '</strong> akan segera kami proses. Silakan siapkan pembayaran tunai sebesar <strong>Rp ' . number_format($total_price, 0, ',', '.') . '</strong> (termasuk ongkir Rp ' . number_format($ongkir, 0, ',', '.') . ') untuk dibayarkan kepada kurir saat pesanan tiba.';
        $message = 'Pesanan COD #' . $order_id . ' berhasil dibuat!';
        $redirect_to_menu = false; // Jangan redirect untuk COD
    }
}
?>

<main class="page-section">
    <div class="container text-center">
        <h1><?php echo $page_title; ?></h1>
        <p><?php echo $page_content; ?></p>
        <br>
        <?php if (!$redirect_to_menu): ?>
            <a href="profil.php" class="cta-button" style="margin-right: 10px;">Lacak Pesanan</a>
            <a href="menu.php" class="cta-button secondary-button">Kembali ke Menu</a>
        <?php else: ?>
            <p>Anda akan diarahkan kembali ke halaman menu dalam beberapa detik...</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shouldRedirect = <?php echo json_encode($redirect_to_menu); ?>;
        
        if (shouldRedirect) {
            const message = "<?php echo addslashes($message); ?>";
            // Redirect setelah 5 detik
            setTimeout(function() {
                window.location.replace('menu.php?message=' + encodeURIComponent(message) + '&type=success');
            }, 5000);
        }
    });
</script>
