<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Dapatkan nama file gambar sebelum menghapus record
    $query_select = "SELECT image FROM products WHERE id = ?";
    $stmt_select = $koneksi->prepare($query_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($row = $result->fetch_assoc()) {
        $image_to_delete = '../uploads/' . $row['image'];
    }
    $stmt_select->close();

    // 2. Hapus record dari database
    $query_delete = "DELETE FROM products WHERE id = ?";
    $stmt_delete = $koneksi->prepare($query_delete);
    $stmt_delete->bind_param("i", $id);
    
    if ($stmt_delete->execute()) {
        // 3. Jika record berhasil dihapus, hapus file gambar
        if (isset($image_to_delete) && file_exists($image_to_delete)) {
            unlink($image_to_delete);
        }
        header('Location: index.php?status=hapus_sukses');
        exit();
    } else {
        header('Location: index.php?error=Gagal menghapus menu.');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>
