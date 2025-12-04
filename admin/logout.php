<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua variabel session
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php?status=logout");
exit;
?>
