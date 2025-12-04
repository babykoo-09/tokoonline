<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $koneksi->prepare("SELECT password FROM admin_accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: index.php');
                exit();
            } else {
                header('Location: login.php?error=Username atau password salah');
                exit();
            }
        } else {
            header('Location: login.php?error=Username atau password salah');
            exit();
        }
        $stmt->close();
    } else {
        header('Location: login.php?error=Mohon isi semua field');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>