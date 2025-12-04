<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika user belum login, redirect ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include 'header.php';
?>

<main class="menu-page-section">
    <div class="container">
        <h1 class="text-center">Ganti Password</h1>

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'sukses') {
                echo '<p class="notification success show">Password berhasil diubah!</p>';
            } elseif (isset($_GET['error'])) {
                echo '<p class="notification error show">' . htmlspecialchars($_GET['error']) . '</p>';
            }
        }
        ?>

        <div class="checkout-form form-section-spacing">
            <form action="proses_ganti_password.php" method="POST">
                <div class="form-group">
                    <label for="old_password">Password Lama</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>
                <button type="submit" class="cta-button">Ganti Password</button>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
