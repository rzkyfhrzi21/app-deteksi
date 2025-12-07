<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['btn_hapusdeteksi'])) {
    $id_deteksi = (int) ($_POST['id_deteksi'] ?? 0);
    $file_path  = $_POST['file_path'] ?? '';

    // Hapus file gambar di folder uploads jika ada
    if (!empty($file_path)) {
        $full_path = __DIR__ . '/../' . $file_path; // karena file_path dari root project (uploads/...)
        if (file_exists($full_path)) {
            @unlink($full_path);
        }
    }

    // Hapus data di database
    if ($id_deteksi > 0) {
        $stmt = $koneksi->prepare("DELETE FROM hasil_deteksi WHERE id_deteksi = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id_deteksi);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: ../dashboard/admin?page=riwayat deteksi');
    exit;
}

// kalau akses langsung file ini tanpa post, redirect saja
header('Location: ../dashboard/admin?page=riwayat deteksi');
exit;
