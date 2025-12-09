<?php
// Menonaktifkan pelaporan error default untuk menangani error koneksi secara manual
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'psas_db';

// Menggunakan gaya object-oriented
$koneksi = new mysqli($host, $db_user, $db_pass, $db_name);

// Memeriksa error koneksi
if ($koneksi->connect_error) {
    // Log the error instead of dying directly
    error_log("Koneksi Gagal: (" . $koneksi->connect_errno . ") " . $koneksi->connect_error);
    // Display a user-friendly message
    die("Terjadi masalah koneksi ke database. Mohon coba lagi nanti.");
}

// Mengatur charset
$koneksi->set_charset("utf8mb4");
?>