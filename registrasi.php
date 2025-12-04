
<?php
session_start();
include 'koneksi.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $default_address = $_POST['default_address'];

    // Cek jika email sudah terdaftar
    $stmt = $koneksi->prepare("SELECT email FROM customer_accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO customer_accounts (nama, email, password, phone, default_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nama, $email, $password, $phone, $default_address);
        
        if ($stmt->execute()) {
            // Automatically log in the user after successful registration
            $_SESSION['loggedin'] = true;
            $_SESSION['customer_id'] = $koneksi->insert_id;
            $_SESSION['nama'] = $nama;
            header("location: index.php");
            exit;
        } else {
            $error = "Registrasi gagal, silakan coba lagi.";
        }
    }
    $stmt->close();
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - The Premium Bubble</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="login-box">
            <h2>Registrasi Akun</h2>
            <form action="registrasi.php" method="post">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="phone">Nomor Telepon</label>
                    <input type="text" name="phone" id="phone" required>
                </div>
                <div class="form-group">
                    <label for="default_address">Alamat</label>
                    <textarea name="default_address" id="default_address" rows="4" required></textarea>
                </div>
                <?php if($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <button type="submit" class="btn">Registrasi</button>
            </form>
            <p>Sudah punya akun? <a href="login.php">Login di sini</a>.</p>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
