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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validasi input dasar
    if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price']) || empty($_POST['category'])) {
        header('Location: index.php?error=Semua field harus diisi.');
        exit;
    }

    $id = intval($_POST['id']);
    $name = $koneksi->real_escape_string($_POST['name']);
    $description = $koneksi->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $koneksi->real_escape_string($_POST['category']);
    $new_image_name = '';

    // Proses jika ada gambar baru yang diunggah
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $new_image_name = uniqid() . '-' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $new_image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validasi gambar
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            header('Location: edit_menu.php?id=' . $id . '&error=File bukan gambar.');
            exit;
        }
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            header('Location: edit_menu.php?id=' . $id . '&error=Hanya format JPG, JPEG, PNG & GIF yang diizinkan.');
            exit;
        }

        // Hapus gambar lama sebelum upload yang baru
        $query_old_image = "SELECT image FROM products WHERE id = ?";
        $stmt_old = $koneksi->prepare($query_old_image);
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        if ($row_old = $result_old->fetch_assoc()) {
            $old_image_path = $target_dir . $row_old['image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }

        // Upload gambar baru
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            header('Location: edit_menu.php?id=' . $id . '&error=Gagal mengunggah gambar baru.');
            exit;
        }
    }

    // Siapkan dan eksekusi query UPDATE
    if (!empty($new_image_name)) {
        // Jika ada gambar baru
        $query = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssdssi", $name, $description, $price, $category, $new_image_name, $id);
    } else {
        // Jika tidak ada gambar baru
        $query = "UPDATE products SET name = ?, description = ?, price = ?, category = ? WHERE id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
    }

    if ($stmt->execute()) {
        header('Location: index.php?status=sukses_edit');
        exit();
    } else {
        header('Location: edit_menu.php?id=' . $id . '&error=Gagal memperbarui data.');
        exit();
    }

} else {
    header('Location: index.php');
    exit();
}
?>
