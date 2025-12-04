<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

// Ambil jumlah notifikasi admin yang belum dibaca
$unread_notifications_query = "SELECT COUNT(id) as unread_count FROM admin_notifications WHERE is_read = 0";
$unread_result = $koneksi->query($unread_notifications_query);
$unread_count = $unread_result->fetch_assoc()['unread_count'];

include 'header.php';

// Ambil daftar produk untuk ditampilkan di form
$products_query = "SELECT id, name, price FROM products ORDER BY name ASC";
$products_result = $koneksi->query($products_query);
$all_products = $products_result->fetch_all(MYSQLI_ASSOC);

// Ambil daftar topping untuk ditampilkan di form
$toppings_query = "SELECT topping_id, name, price FROM toppings ORDER BY name ASC";
$toppings_result = $koneksi->query($toppings_query);
$all_toppings = $toppings_result->fetch_all(MYSQLI_ASSOC);

?>

<main class="admin-page">
    <div class="container">
        <h1 class="text-center">Tambah Pesanan Manual</h1>
        <p class="text-center">Gunakan form ini untuk mencatat pesanan yang diterima via telepon, WhatsApp, atau media lainnya.</p>

        <form action="proses_tambah_manual.php" method="POST" class="manual-order-form">
            <div class="form-section">
                <h2>Informasi Pelanggan</h2>
                <div class="form-group">
                    <label for="customer_name">Nama Pelanggan:</label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="customer_id">ID Pelanggan (Opsional, untuk pengguna terdaftar):</label>
                    <input type="number" id="customer_id" name="customer_id" min="1">
                </div>
                <div class="form-group">
                    <label for="no_telepon">Nomor Telepon:</label>
                    <input type="tel" id="no_telepon" name="no_telepon" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap:</label>
                    <textarea id="alamat" name="alamat" rows="3" required></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Detail Pesanan</h2>
                <div id="order-items-wrapper">
                    <!-- Item Pesanan Awal -->
                    <div class="order-item-row">
                        <div class="form-group">
                            <label>Produk:</label>
                            <select name="items[0][product_id]" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach($all_products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kuantitas:</label>
                            <input type="number" name="items[0][quantity]" value="1" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Level Gula (%):</label>
                            <input type="number" name="items[0][sugar_level]" value="100" min="0" max="100" step="25" required>
                        </div>
                        <div class="form-group">
                            <label>Level Es:</label>
                            <input type="text" name="items[0][ice_level]" value="Normal" required>
                        </div>
                        <div class="form-group">
                            <label>Suhu:</label>
                            <select name="items[0][suhu]" required>
                                <option value="Dingin">Dingin</option>
                                <option value="Panas">Panas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Topping:</label>
                            <div class="topping-options">
                                <?php foreach ($all_toppings as $topping): ?>
                                    <input type="checkbox" name="items[0][toppings][]" value="<?php echo $topping['topping_id']; ?>" id="topping-<?php echo $topping['topping_id']; ?>-0">
                                    <label for="topping-<?php echo $topping['topping_id']; ?>-0"><?php echo htmlspecialchars($topping['name']); ?> (+Rp <?php echo number_format($topping['price'], 0, ',', '.'); ?>)</label><br>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-item-btn" class="cta-button secondary-button">+ Tambah Item Lain</button>
            </div>

            <div class="form-section">
                <button type="submit" class="cta-button">Simpan Pesanan</button>
            </div>
        </form>
    </div>
</main>

<script>
const products = <?php echo json_encode($all_products); ?>;
const toppings = <?php echo json_encode($all_toppings); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('order-items-wrapper');
    const addItemBtn = document.getElementById('add-item-btn');
    let itemIndex = 1;

    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.classList.add('order-item-row');
        
        let productOptions = '<option value="">-- Pilih Produk --</option>';
        products.forEach(product => {
            productOptions += `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`;
        });

        let toppingOptions = '';
        toppings.forEach(topping => {
            toppingOptions += `<input type="checkbox" name="items[${itemIndex}][toppings][]" value="${topping.topping_id}" id="topping-${topping.topping_id}-${itemIndex}">
                             <label for="topping-${topping.topping_id}-${itemIndex}">${topping.name} (+Rp ${new Intl.NumberFormat('id-ID').format(topping.price)})</label><br>`;
        });

        newRow.innerHTML = `
            <div class="form-group">
                <label>Produk:</label>
                <select name="items[${itemIndex}][product_id]" required>
                    ${productOptions}
                </select>
            </div>
            <div class="form-group">
                <label>Kuantitas:</label>
                <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" required>
            </div>
            <div class="form-group">
                <label>Level Gula (%):</label>
                <input type="number" name="items[${itemIndex}][sugar_level]" value="100" min="0" max="100" step="25" required>
            </div>
            <div class="form-group">
                <label>Level Es:</label>
                <input type="text" name="items[${itemIndex}][ice_level]" value="Normal" required>
            </div>
            <div class="form-group">
                <label>Suhu:</label>
                <select name="items[${itemIndex}][suhu]" required>
                    <option value="Dingin">Dingin</option>
                    <option value="Panas">Panas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Topping:</label>
                <div class="topping-options">
                    ${toppingOptions}
                </div>
            </div>
        `;
        wrapper.appendChild(newRow);
        itemIndex++;
    });
});
</script>

<?php include 'footer.php'; ?>