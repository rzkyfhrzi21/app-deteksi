<?php
// ============================================================
// FILE CONTOH KONEKSI (koneksi.example.php)
// ============================================================
// Ini adalah file contoh. File asli 'koneksi.php' TIDAK BOLEH 
// diupload ke GitHub karena berisi password database Anda.
//
// CARA PENGGUNAAN (Bagi Developer Lain yang Mengunduh Kode Ini):
// 1. Ubah nama file ini menjadi: koneksi.php
// 2. Sesuaikan username dan password di bawah dengan database Anda.

$host = "localhost";
$user = "root";
$pass = "";
$db   = "app-deteksi"; // Ganti dengan nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi generate_csrf_token() dan lainnya tetap ada di bawah sini...
// (Pastikan Anda menyalin fungsi CSRF dari repo utama)
?>
