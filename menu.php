<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include 'header.php'; ?>
<?php include 'koneksi.php'; ?>

<main class="menu-page-section">
    <div class="container">
        <h1>Pilihan Menu Kami</h1>

        <?php
        $categories_query = "SELECT DISTINCT category FROM products";
        $categories_result = $koneksi->query($categories_query);

        while ($category_row = $categories_result->fetch_assoc()):
            $category = $category_row['category'];
        ?>
            <section class="menu-category">
                <h2><?php echo htmlspecialchars($category); ?></h2>
                <div class="menu-list">
                    <?php
                    $products_query = "SELECT * FROM products WHERE category = ?";
                    $stmt = $koneksi->prepare($products_query);
                    $stmt->bind_param("s", $category);
                    $stmt->execute();
                    $products_result = $stmt->get_result();
                    while ($product = $products_result->fetch_assoc()):
                    ?>
                    <div class="menu-list-item">
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="menu-item-details">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                            
                            <form class="customization" action="keranjang.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <div class="option-group">
                                    <label>Level Gula:</label>
                                    <input type="radio" name="sugar_level" value="100" checked> 100%
                                    <input type="radio" name="sugar_level" value="75"> 75%
                                    <input type="radio" name="sugar_level" value="50"> 50%
                                </div>
                                <div class="option-group">
                                    <label>Level Es:</label>
                                    <input type="radio" name="ice_level" value="normal" checked> Normal
                                    <input type="radio" name="ice_level" value="less"> Less
                                </div>
                                <button type="submit" class="add-to-cart-btn">Tambah ke Keranjang</button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endwhile; ?>

    </div>
</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');

    if (message && type) {
        showNotification(message, type);
        // Optionally, remove the message from the URL after displaying
        history.replaceState(null, '', window.location.pathname);
    }
});
</script>
