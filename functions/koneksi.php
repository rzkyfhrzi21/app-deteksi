<?php
// ============================================================
// FILE: koneksi.php
// TUJUAN: Membuat koneksi ke database MySQL dan menyiapkan
//         fungsi-fungsi pembantu yang dipakai di seluruh aplikasi.
//
// Ibarat "kabel listrik utama gedung" — file ini yang pertama
// kali di-include oleh hampir semua file PHP lain. Tanpa file
// ini, tidak ada satu pun halaman yang bisa mengambil data
// dari database.
//
// BERISI TIGA BAGIAN:
//   [A] Konfigurasi database (otomatis mendeteksi localhost vs hosting)
//   [B] Membuat koneksi ke MySQL
//   [C] Fungsi formatTanggalIndonesia() — untuk format tanggal di UI
//
// VARIABEL PENTING YANG TERSEDIA SETELAH FILE INI DI-INCLUDE:
//   $koneksi → objek koneksi MySQL, dipakai untuk semua query
//   $pukul   → jam sekarang dalam format H:i A (contoh: 14:30 PM)
// ============================================================

// ============================================================
// BAGIAN A: KONFIGURASI DATABASE (AUTO-DETECT ENVIRONMENT)
//
// Sistem otomatis mendeteksi apakah sedang berjalan di:
//   1. Localhost port 8090 (Docker / custom port)
//   2. Localhost standar (Laragon, XAMPP, WAMP)
//   3. Hosting Servermikro (production)
//
// Dengan cara ini, kode yang sama bisa berjalan di komputer
// lokal maupun di server hosting tanpa perlu diubah manual.
// ============================================================

// $panel_url  = 'https://sgdirect.servermikro.my.id:2222'; // (tidak aktif)
// $username   = 'zfkwrvad';                                // (tidak aktif)
// $password   = 'N(1EeA3@6vkf';                           // (tidak aktif)

$host = $_SERVER['HTTP_HOST']; // Baca nama domain/host yang sedang diakses pengguna

// ---------------------------------------------------
// KONDISI 1: Localhost di port khusus (Docker/custom)
// ---------------------------------------------------
if ($host === 'localhost:8090' || strpos($host, '127.0.0.1:8090') !== false) {
    // Dipakai saat menjalankan aplikasi di Docker atau port non-standar
    $server   = '127.0.0.1:3309'; // Database di port 3309 (bukan 3306 standar)
    $username = 'root';
    $password = '';
    $database = 'app-deteksi';    // Nama database lokal

// ---------------------------------------------------
// KONDISI 2: Localhost standar (Laragon, XAMPP, WAMP)
// ---------------------------------------------------
} else if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
    // Kondisi paling umum saat development di komputer sendiri
    $server   = 'localhost';     // MySQL berjalan di komputer yang sama
    $username = 'root';          // Username default Laragon/XAMPP
    $password = '';              // Password kosong (default)
    $database = 'app-deteksi';   // Nama database lokal

// ---------------------------------------------------
// KONDISI 3: Server Hosting Servermikro (Production)
// ---------------------------------------------------
} else {
    // Dipakai saat aplikasi sudah di-upload ke server hosting publik
    $server   = 'localhost';                  // Di hosting, MySQL selalu di localhost
    $username = 'uucdjd7c_lulukauliani';      // Username MySQL di hosting
    $password = 'lulukaulianilulukauliani';   // Password MySQL di hosting
    $database = 'uucdjd7c_app-deteksi';       // Nama database di hosting (dengan prefix)
}

// ============================================================
// BAGIAN B: BUAT KONEKSI KE MYSQL
// Menggunakan informasi konfigurasi di atas untuk terhubung
// ke database. Jika gagal, tampilkan pesan error dan hentikan.
// ============================================================
$koneksi = mysqli_connect($server, $username, $password, $database);

if (!$koneksi) {
    // Jika koneksi gagal (database tidak aktif, password salah, dst.)
    // → hentikan semua proses dan tampilkan pesan error
    die('Koneksi gagal: ' . mysqli_connect_error());
}

// ============================================================
// BAGIAN C: FUNGSI DAN VARIABEL PEMBANTU
// ============================================================

// Set zona waktu ke WIB (Waktu Indonesia Barat)
// agar semua fungsi date() menghasilkan waktu yang benar
date_default_timezone_set('Asia/Jakarta');

// Variabel $pukul: waktu sekarang dalam format jam:menit AM/PM
// Contoh: "14:30 PM" — ditampilkan di dashboard dan laporan
$pukul = date('H:i A');

// ============================================================
// FUNGSI: formatTanggalIndonesia()
// Tujuan: Mengubah format tanggal Inggris (Y-m-d) menjadi
//         format Indonesia yang ramah dibaca.
//
// Contoh input : "2025-07-05"
// Contoh output: "Sabtu, 05 Juli 2025"
//
// Kenapa pakai if (!function_exists(...))?
// → Karena koneksi.php sering di-include lebih dari satu kali
//   di berbagai file. Guard ini mencegah error "function already defined".
// ============================================================
if (!function_exists('formatTanggalIndonesia')) {
    function formatTanggalIndonesia($tanggalInggris)
    {
        // Tabel terjemahan nama hari: Inggris → Indonesia
        $namaHari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];

        // Tabel terjemahan nama bulan: Inggris → Indonesia
        $namaBulan = [
            'January'   => 'Januari',
            'February'  => 'Februari',
            'March'     => 'Maret',
            'April'     => 'April',
            'May'       => 'Mei',
            'June'      => 'Juni',
            'July'      => 'Juli',
            'August'    => 'Agustus',
            'September' => 'September',
            'October'   => 'Oktober',
            'November'  => 'November',
            'December'  => 'Desember'
        ];

        // Buat objek DateTime dari string tanggal yang diberikan
        $date = new DateTime($tanggalInggris);

        // Ambil nama hari dan bulan dalam Bahasa Inggris, lalu terjemahkan
        $hariInggris    = $date->format('l');            // Contoh: "Saturday"
        $bulanInggris   = $date->format('F');            // Contoh: "July"
        $hariIndonesia  = $namaHari[$hariInggris];       // Contoh: "Sabtu"
        $bulanIndonesia = $namaBulan[$bulanInggris];     // Contoh: "Juli"

        // Gabungkan menjadi format: "Sabtu, 05 Juli 2025"
        return $hariIndonesia . ', ' . $date->format('d') . ' ' . $bulanIndonesia . ' ' . $date->format('Y');
    }
}

// ============================================================
// BAGIAN D: FUNGSI KEAMANAN (CSRF)
// Tujuan: Mencegah serangan Cross-Site Request Forgery
// ============================================================
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('Keamanan: Validasi CSRF gagal. Permintaan ditolak.');
        }
        return true;
    }
}
