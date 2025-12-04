<?php
include '../koneksi.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, redirect ke dashboard admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - The Premium Bubble</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container admin-login-container">
        <div class="login-box admin-login-box">
            <h2 class="text-center">Admin Login</h2>
            <p class="text-center">Silakan masuk untuk mengelola menu.</p>
            
            <?php
            if (isset($_GET['error'])) {
                echo '<p class="error-message text-center">' . htmlspecialchars($_GET['error']) . '</p>';
            }
            ?>

            <form action="proses_login.php" method="POST">
                <div class="form-group admin-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group admin-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="cta-button full-width-button">Masuk</button>
            </form>
            
            
        </div>
    </div>
</body>
</html>
