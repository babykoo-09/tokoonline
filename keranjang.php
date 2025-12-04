<?php include 'header.php'; // This will call session_start() and include koneksi.php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $sugar_level = $_POST['sugar_level'] ?? '100'; // Default value since removed from menu.php
    $ice_level = $_POST['ice_level'] ?? 'normal';   // Default value since removed from menu.php
    $suhu = 'Dingin'; // Always default for user-facing menu after customization options removed
    $toppings = []; // Always empty array for user-facing menu after customization options removed
    $quantity = 1; // Default quantity

    // Buat ID unik untuk item keranjang berdasarkan produk dan kustomisasinya
    $cart_item_id = md5($product_id . $sugar_level . $ice_level . $suhu . implode(',', $toppings));

    // Cek apakah item dengan konfigurasi yang sama sudah ada di keranjang
    if (isset($_SESSION['cart'][$cart_item_id])) {
        // Jika ada, tambahkan kuantitasnya
        $_SESSION['cart'][$cart_item_id]['quantity']++;
    } else {
        // Jika tidak ada, tambahkan sebagai item baru
        $_SESSION['cart'][$cart_item_id] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'sugar_level' => $sugar_level,
            'ice_level' => $ice_level,
            'suhu' => $suhu,
            'toppings' => $toppings
        ];
    }

    // Redirect back to menu.php with a success message
    header('Location: menu.php?message=Produk berhasil ditambahkan ke keranjang!&type=success');
    exit();
}

?>

<main class="menu-page-section">
    <div class="container">
        <h1 class="text-center">Keranjang Belanja Anda</h1>

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

        <?php
        if (empty($_SESSION['cart'])):
        ?>
            <p style="text-align: center;">Keranjang Anda kosong. Silakan <a href="menu.php">pilih menu</a> kami.</p>
        <?php
        else:
            $total_price = 0;
        ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $cart_item_id => $item): ?>
                    <?php
                    $product_id = $item['product_id'];
                    $product_query = "SELECT * FROM products WHERE id = ?";
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
                    $toppings_names = [];
                    if (!empty($item['toppings'])) {
                        // Ensure toppings is an array before imploding
                        $toppings_ids = array_map('intval', $item['toppings']);
                        $placeholders = implode(',', array_fill(0, count($toppings_ids), '?'));
                        $types = str_repeat('i', count($toppings_ids));
                        $toppings_query = "SELECT * FROM toppings WHERE topping_id IN ($placeholders)";
                        $stmt_toppings = $koneksi->prepare($toppings_query);
                        $stmt_toppings->bind_param($types, ...$toppings_ids);
                        $stmt_toppings->execute();
                        $toppings_result = $stmt_toppings->get_result();
                        while ($topping = $toppings_result->fetch_assoc()) {
                            $toppings_price += $topping['price'];
                            $toppings_names[] = $topping['name'];
                        }
                    }

                    $item_total_price = ($product['price'] + $toppings_price) * $item['quantity'];
                    $total_price += $item_total_price;
                    ?>
                    <div class="cart-item">
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>Kustomisasi: Gula <?php echo htmlspecialchars($item['sugar_level']); ?>%, Es <?php echo htmlspecialchars($item['ice_level']); ?>, Suhu <?php echo htmlspecialchars($item['suhu']); ?></p>
                            <?php if (!empty($toppings_names)): ?>
                                <p>Topping: <?php echo implode(', ', $toppings_names); ?></p>
                            <?php endif; ?>
                            <p>Kuantitas: <?php echo htmlspecialchars($item['quantity']); ?></p>
                            <p class="price">Rp <?php echo number_format($item_total_price, 0, ',', '.'); ?></p>
                        </div>
                        <a href="hapus_item.php?id=<?php echo htmlspecialchars($cart_item_id); ?>" class="remove-item-btn">Hapus</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-total">
                <h3>Total Belanja: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></h3>
            </div>

            <div class="checkout-form">
                <a href="checkout.php" class="cta-button">Lanjutkan ke Pembayaran</a>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
