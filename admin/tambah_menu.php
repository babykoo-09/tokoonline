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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input dasar
    if (empty($_POST['name']) || empty($_POST['description']) || empty($_POST['price']) || empty($_POST['category']) || empty($_FILES['image']['name'])) {
        header('Location: index.php?error=Semua field harus diisi.');
        exit;
    }

    $name = $koneksi->real_escape_string($_POST['name']);
    $description = $koneksi->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $koneksi->real_escape_string($_POST['category']);

    // Proses upload gambar
    $target_dir = "../uploads/";
    $image_name = uniqid() . '-' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah file adalah gambar asli
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check === false) {
        header('Location: index.php?error=File yang diunggah bukan gambar.');
        exit;
    }

    // Cek ekstensi file
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        header('Location: index.php?error=Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan.');
        exit;
    }

    // Pindahkan file yang diunggah
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Masukkan data ke database
        $query = "INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $image_name);
        
        if ($stmt->execute()) {
            header('Location: index.php?status=sukses');
            exit();
        } else {
            header('Location: index.php?error=Gagal menyimpan data ke database.');
            exit();
        }
    } else {
        header('Location: index.php?error=Terjadi kesalahan saat mengunggah gambar.');
        exit();
    }

} else {
    header('Location: index.php');
    exit();
}
?>
