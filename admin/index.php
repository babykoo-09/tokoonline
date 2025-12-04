<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika user belum login, redirect ke halaman login
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

?>

<main class="menu-page-section">
    <div class="container">
        <h1 class="text-center">Kelola Menu</h1>

        <?php
       if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        echo '<p class="notification success show">Menu berhasil ditambahkan!</p>';
    } elseif ($_GET['status'] == 'sukses_edit') {
        echo '<p class="notification success show">Menu berhasil diperbarui!</p>';
    } elseif (isset($_GET['error'])) {
        echo '<p class="notification error show">' . htmlspecialchars($_GET['error']) . '</p>';
    }
}
  
        ?>

        <!-- Form Tambah Menu -->
        <div class="checkout-form form-section-spacing">
            <h2>Tambah Menu Baru</h2>
            <form action="tambah_menu.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama Menu</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input type="number" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="Classic Boba Series">Classic Boba Series</option>
                        <option value="Fresh Milk Series">Fresh Milk Series</option>
                        <option value="Seasonal Specials">Seasonal Specials</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="cta-button">Tambah Menu</button>
            </form>
        </div>

        <!-- Daftar Menu yang Ada -->
        <h2>Daftar Menu Saat Ini</h2>
        <div class="admin-menu-list">
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM products ORDER BY category, name";
                    $result = $koneksi->query($query);
                    while($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" width="80"></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <a href="edit_menu.php?id=<?php echo $row['id']; ?>" class="edit-link">Edit</a>
                            <a href="hapus_menu.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')" class="delete-link">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>