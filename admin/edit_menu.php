<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman
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

// Cek ID dari URL
if (!isset($_GET['id'])) {
    header('Location: index.php?error=Menu tidak ditemukan');
    exit();
}

$id = intval($_GET['id']);

// Ambil data menu dari database
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php?error=Menu tidak ditemukan');
    exit();
}

$menu = $result->fetch_assoc();
?>

<main class="menu-page-section">
    <div class="container">
        <h1 class="text-center">Edit Menu</h1>

        <div class="checkout-form form-section-spacing">
            <form action="proses_edit_menu.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                
                <div class="form-group">
                    <label for="name">Nama Menu</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($menu['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($menu['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($menu['price']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="Classic Boba Series" <?php echo ($menu['category'] == 'Classic Boba Series') ? 'selected' : ''; ?>>Classic Boba Series</option>
                        <option value="Fresh Milk Series" <?php echo ($menu['category'] == 'Fresh Milk Series') ? 'selected' : ''; ?>>Fresh Milk Series</option>
                        <option value="Seasonal Specials" <?php echo ($menu['category'] == 'Seasonal Specials') ? 'selected' : ''; ?>>Seasonal Specials</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Gambar Saat Ini</label>
                    <div>
                        <img src="../uploads/<?php echo htmlspecialchars($menu['image']); ?>" width="150">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Ganti Gambar (Opsional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
                
                <button type="submit" class="cta-button">Simpan Perubahan</button>
                <a href="index.php" class="cta-button secondary-button">Batal</a>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>