
<?php
session_start();
include 'koneksi.php';

if (empty($_SESSION['cart'])) {
    header("location: menu.php");
    exit;
}

$customer_data = null;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $koneksi->prepare("SELECT nama, email, phone, default_address FROM customer_accounts WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $customer_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$total_price = 0;

// Calculate total price
foreach ($_SESSION['cart'] as $item) {
    $product_id = $item['product_id'];
    $product_query = "SELECT price FROM products WHERE id = ?";
    $stmt = $koneksi->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();

    // Check if product exists before accessing its properties
    if (!$product) {
        // Skip this item or handle the error appropriately
        continue; 
    }

    $toppings_price = 0;
    if (!empty($item['toppings'])) {
        // Ensure toppings is an array before imploding
        $toppings_ids = implode(',', array_map('intval', $item['toppings']));
        if (!empty($toppings_ids)) {
            $toppings_query = "SELECT SUM(price) as total_topping_price FROM toppings WHERE topping_id IN ($toppings_ids)";
            $toppings_result = $koneksi->query($toppings_query);
            $topping_price_row = $toppings_result->fetch_assoc();
            $toppings_price = $topping_price_row['total_topping_price'];
        }
    }

    $total_price += ($product['price'] + $toppings_price) * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - The Premium Bubble</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Checkout</h1>
        <div class="checkout-layout">
            <div class="customer-details">
                <h3>Data Pelanggan</h3>
                <form id="checkoutForm" action="" method="POST">
                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                    <?php if ($customer_data): ?>
                        <p>Selamat datang kembali, <strong><?php echo htmlspecialchars($customer_data['nama']); ?></strong>!</p>
                        <input type="hidden" name="customer_id" value="<?php echo $_SESSION['customer_id']; ?>">
                        <div class="form-group">
                            <label>Alamat Pengiriman</label>
                            <textarea name="alamat" rows="4" required><?php echo htmlspecialchars($customer_data['default_address']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($customer_data['phone']); ?>" required>
                        </div>
                    <?php else: ?>
                        <p>Silakan isi data diri Anda atau <a href="login.php">login</a> untuk proses yang lebih cepat.</p>
                        <div class="form-group">
                            <label for="customer_name">Nama Anda:</label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="no_telepon">Nomor Telepon:</label>
                            <input type="tel" id="no_telepon" name="no_telepon" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat Lengkap:</label>
                            <textarea id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                    <?php endif; ?>

                    <h3>Metode Pembayaran</h3>
                    <div class="payment-methods">
                        <input type="radio" name="payment_method" value="cod" id="cod" checked>
                        <label for="cod">Bayar di Tempat (COD)</label><br>
                        <input type="radio" name="payment_method" value="qris" id="qris">
                        <label for="qris">QRIS</label><br>
                    </div>

                    <button type="submit" class="cta-button">Selesaikan Pesanan</button>
                </form>
            </div>
            <div class="order-summary">
                <h3>Ringkasan Pesanan</h3>
                <p>Total Harga: <strong>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');

    checkoutForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        let selectedPaymentMethod = '';
        for (const radio of paymentMethodRadios) {
            if (radio.checked) {
                selectedPaymentMethod = radio.value;
                break;
            }
        }

        if (selectedPaymentMethod === 'cod') {
            checkoutForm.action = 'proses_cod_order.php';
        } else if (selectedPaymentMethod === 'qris') {
            checkoutForm.action = 'proses_order.php'; // proses_order.php now handles QRIS redirect
        }
        
        checkoutForm.submit(); // Submit the form with the updated action
    });
});
</script>


