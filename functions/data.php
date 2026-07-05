<?php
// ============================================================
// FILE: data.php
// TUJUAN: Mengambil semua data statistik dari database untuk
//         ditampilkan di halaman Dashboard
//
// Ibarat "staf riset yang mengumpulkan laporan" — file ini
// menghitung semua angka penting: berapa pengguna, berapa
// deteksi hari ini, berapa rata-rata kepercayaan model, dst.
//
// File ini di-include oleh dashboard.php dan riwayat_deteksi.php
// ============================================================
include 'koneksi.php'; // Sambungkan ke database MySQL (variabel $koneksi tersedia)

/* ===============================
   SET WAKTU DASAR
   Menyiapkan variabel tanggal dan waktu server saat ini.
   Digunakan untuk filter query "hari ini" dan "bulan ini".
================================ */
$tanggal_sekarang = date('Y-m-d'); // Tanggal hari ini dalam format: 2025-07-05
$bulan_sekarang   = date('m');     // Nomor bulan sekarang, contoh: 07 (untuk Juli)
$tahun_sekarang   = date('Y');     // Tahun sekarang, contoh: 2025
$pukul            = date('H:i');   // Jam dan menit sekarang, contoh: 14:30

/* ===============================
   MAPPING LABEL MODEL → TAMPILAN UI

   Label dari model TFLite menggunakan nama folder dataset (tanpa spasi),
   contoh: "Bacterialblight" (nama folder) → "Bacterial Blight" (tampilan di layar).

   Tujuannya agar tampilan di UI terlihat rapi dan mudah dibaca pengguna,
   sementara database tetap menyimpan label asli model agar query tetap konsisten.
================================ */
if (!defined('LABEL_MAP')) {
    define('LABEL_MAP', [
        'Healthy'          => 'Healthy (Daun Sehat)', // Daun sehat, tidak ada penyakit
        'Bacterialblight'  => 'Bacterial Blight',     // Penyakit hawar bakteri
        'Blast'            => 'Blast',                // Penyakit blas/busuk leher
        'Brownspot'        => 'Brown Spot',           // Penyakit bercak cokelat
        'Tungro'           => 'Tungro',               // Penyakit tungro (virus)
    ]);
}

/* ===============================
   FUNGSI label_display()
   Mengubah label mentah dari model AI menjadi nama tampilan yang rapi.

   Contoh: label_display('Bacterialblight') → 'Bacterial Blight'
   Jika label tidak dikenal (tidak ada di LABEL_MAP), kembalikan apa adanya.
================================ */
if (!function_exists('label_display')) {
    function label_display(string $label): string
    {
        return LABEL_MAP[$label] ?? $label; // Jika ada di peta → tampilkan nama rapi. Jika tidak → kembalikan label asli
    }
}

/* ===============================
   FUNGSI PEMBANTU: getCount()
   Menghitung jumlah baris di tabel tertentu dengan kondisi opsional.

   Parameter:
   - $table : Nama tabel yang ingin dihitung (contoh: 'users')
   - $where  : Kondisi filter opsional (contoh: "MONTH(created_at) = '07'")

   Contoh penggunaan:
   - getCount('users')                     → hitung semua pengguna
   - getCount('users', "MONTH(created_at) = '07'") → hitung pengguna yang daftar bulan Juli
================================ */
if (!function_exists('getCount')) {
    function getCount($table, $where = '')
    {
        global $koneksi; // Gunakan koneksi database yang sudah dibuat di koneksi.php

        $sql = "SELECT COUNT(*) AS total FROM $table"; // Query dasar: hitung semua baris
        if (!empty($where)) {
            $sql .= " WHERE $where"; // Tambahkan filter jika ada
        }

        $query = mysqli_query($koneksi, $sql);
        if (!$query) return 0; // Jika query gagal, kembalikan 0 agar tidak error

        $data = mysqli_fetch_assoc($query);
        return (int) ($data['total'] ?? 0); // Kembalikan angkanya sebagai bilangan bulat
    }
}

/* ===============================
   STATISTIK PENGGUNA (dari tabel 'users')
   Menghitung total pengguna terdaftar dan pengguna yang daftar bulan ini
================================ */
$totalPengguna = getCount('users'); // Jumlah semua pengguna yang pernah mendaftar

$totalPenggunaBaru = getCount(
    'users',
    "MONTH(created_at) = '$bulan_sekarang' 
     AND YEAR(created_at) = '$tahun_sekarang'" // Filter: hanya yang daftar di bulan dan tahun sekarang
);

/* ===============================
   STATISTIK PENGUNJUNG (dari tabel 'rekam_akses_web')
   Menghitung total semua pengunjung yang pernah login ke sistem
================================ */
$totalPengunjung = getCount('rekam_akses_web'); // Hitung semua baris rekaman akses

$pengunjungHariIni = getCount(
    'rekam_akses_web',
    "tanggal_akses = '$tanggal_sekarang'" // Filter: hanya akses yang terjadi hari ini
);

/* ===============================
   STATISTIK DETEKSI (INTI SISTEM) — dari tabel 'hasil_deteksi'
   Menghitung total deteksi yang pernah dilakukan dan yang dilakukan hari ini
================================ */
$totalDeteksi = getCount('hasil_deteksi'); // Total semua proses deteksi yang pernah dilakukan

$deteksiHariIni = getCount(
    'hasil_deteksi',
    "DATE(created_at) = '$tanggal_sekarang'" // Filter: hanya yang dideteksi hari ini
);

/* ===============================
   RATA-RATA CONFIDENCE (TINGKAT KEPERCAYAAN MODEL)
   Menghitung nilai rata-rata confidence dari semua hasil deteksi.
   Confidence = angka 0.0 s.d. 1.0 yang menunjukkan seberapa yakin model AI
   dalam menentukan jenis penyakit (misal: 0.95 = 95% yakin)
================================ */
$avgConfidence = 0; // Nilai default jika belum ada data deteksi

$sqlAvg = mysqli_query(
    $koneksi,
    "SELECT AVG(confidence) AS avg_conf FROM hasil_deteksi" // Hitung rata-rata kolom confidence
);

if ($sqlAvg) {
    $row           = mysqli_fetch_assoc($sqlAvg);
    $avgConfidence = round((float) ($row['avg_conf'] ?? 0), 4); // Bulatkan ke 4 angka desimal, contoh: 0.8743
}

/* ===============================
   DISTRIBUSI PENYAKIT
   Menghitung berapa kali setiap jenis penyakit terdeteksi.
   Hasilnya berupa array: ['Blast' => 15, 'Brownspot' => 8, 'Healthy' => 30, ...]
   Berguna untuk grafik batang atau pie chart di dashboard.
================================ */
$distribusiPenyakit = []; // Siapkan array kosong untuk ditampung

$sqlDistribusi = mysqli_query(
    $koneksi,
    "SELECT label_penyakit, COUNT(*) AS total 
     FROM hasil_deteksi 
     GROUP BY label_penyakit" // Kelompokkan berdasarkan nama penyakit, hitung masing-masing
);

if ($sqlDistribusi) {
    // Baca setiap baris hasil query, masukkan ke array $distribusiPenyakit
    // Sistem akan mengecek setiap jenis penyakit satu per satu dan menghitung totalnya
    while ($row = mysqli_fetch_assoc($sqlDistribusi)) {
        $distribusiPenyakit[$row['label_penyakit']] = (int) $row['total']; // Contoh: $distribusiPenyakit['Blast'] = 15
    }
}
