<?php
include 'header.php';
include 'koneksi.php';

if (!isset($_GET['product_id'])) {
    header('Location: menu.php');
    exit();
}

$product_id = intval($_GET['product_id']);
$stmt = $koneksi->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: menu.php');
    exit();
}

$sugar_level = isset($_GET['sugar_level']) ? htmlspecialchars($_GET['sugar_level']) : '100';
$ice_level = isset($_GET['ice_level']) ? htmlspecialchars($_GET['ice_level']) : 'normal';
?>

<main class="order-form-page">
    <div class="container">
        <h1>Form Pemesanan</h1>
        <div class="order-summary">
            <h2>Pesanan Anda</h2>
            <div class="order-item">
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Harga: Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
            <p>Level Gula: <?php echo $sugar_level; ?>%</p>
            <p>Level Es: <?php echo $ice_level; ?></p>
            <p>Suhu: <?php echo isset($_GET['suhu']) ? htmlspecialchars($_GET['suhu']) : 'Dingin'; ?></p>
            <?php
            $selected_toppings = isset($_GET['toppings']) ? $_GET['toppings'] : [];
            if (!empty($selected_toppings)) {
                $topping_ids = array_map('intval', $selected_toppings);
                $placeholders = implode(',', array_fill(0, count($topping_ids), '?'));
                $types = str_repeat('i', count($topping_ids));
                $toppings_query = "SELECT name FROM toppings WHERE topping_id IN ($placeholders)";
                $stmt_toppings = $koneksi->prepare($toppings_query);
                $stmt_toppings->bind_param($types, ...$topping_ids);
                $stmt_toppings->execute();
                $toppings_result = $stmt_toppings->get_result();
                $topping_names = [];
                while ($topping = $toppings_result->fetch_assoc()) {
                    $topping_names[] = $topping['name'];
                }
                if (!empty($topping_names)) {
                    echo '<p>Topping: ' . htmlspecialchars(implode(', ', $topping_names)) . '</p>';
                }
            }
            ?>
        </div>
    </div>

    <form action="proses_pesanan_langsung.php" method="POST" class="checkout-form">
        <h2>Data Diri Anda</h2>
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="sugar_level" value="<?php echo $sugar_level; ?>">
        <input type="hidden" name="ice_level" value="<?php echo $ice_level; ?>">
        <input type="hidden" name="suhu" value="<?php echo isset($_GET['suhu']) ? htmlspecialchars($_GET['suhu']) : 'Dingin'; ?>">
        <?php if (!empty($selected_toppings)): ?>
            <?php foreach ($selected_toppings as $topping_id): ?>
                <input type="hidden" name="toppings[]" value="<?php echo htmlspecialchars($topping_id); ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        <input type="hidden" name="price" value="<?php echo $product['price']; ?>">

        <div class="form-group">
            <label for="customer_name">Nama Lengkap</label>
            <input type="text" id="customer_name" name="customer_name" required>
        </div>
        <div class="form-group">
            <label for="no_telepon">Nomor Telepon</label>
            <input type="tel" id="no_telepon" name="no_telepon" required>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat Pengiriman</label>
            <textarea id="alamat" name="alamat" rows="4" required></textarea>
        </div>
        <button type="submit" class="cta-button">Konfirmasi Pesanan</button>
    </form>
    </div>
</main>

<?php include 'footer.php'; ?>
