<?php
// ============================================================
// FILE: log_akses.php
// TUJUAN: Mencatat rekam jejak setiap kali pengguna berhasil login
//         ke dalam tabel 'rekam_akses_web' di database.
//
// Ibarat "buku tamu digital" — setiap pengguna yang masuk
// dicatat namanya, kapan datang, pakai browser apa, dari
// perangkat apa (HP/komputer), dan dari alamat IP mana.
//
// File ini TIDAK dipanggil oleh pengguna secara langsung.
// Ia di-include (require_once) oleh function_auth.php
// tepat setelah proses login berhasil.
//
// INFORMASI YANG DICATAT KE TABEL 'rekam_akses_web':
//   - id_user       → siapa yang login (dari $_SESSION['sesi_id'])
//   - alamat_ip     → alamat IP komputer pengguna
//   - agen_pengguna → string User-Agent browser (mentah)
//   - browser       → nama browser (Chrome, Firefox, Safari, dll.)
//   - sistem_operasi → Windows, Android, iOS, MacOS, Linux
//   - perangkat     → Mobile atau Desktop
//   - tanggal_akses → tanggal login (Y-m-d)
//   - waktu_akses   → jam login (H:i:s)
//
// FITUR ANTI DUPLIKAT:
//   Dalam satu sesi login, log hanya dicatat SEKALI meski halaman
//   di-refresh berkali-kali. Caranya: setelah berhasil catat,
//   simpan flag $_SESSION['akses_dicatat'] = true. Jika flag sudah ada,
//   lewati pencatatan.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Buka akses ke data sesi (jika belum dibuka)
}

require_once 'koneksi.php'; // Sambungkan ke database (variabel $koneksi tersedia)

// ============================================================
// FUNGSI-FUNGSI PEMBANTU DETEKSI PERANGKAT
// Fungsi-fungsi ini membaca string User-Agent (informasi browser
// yang dikirim oleh browser ke server) dan mengekstrak informasi
// jenis browser, sistem operasi, dan jenis perangkat.
//
// Contoh User-Agent browser Chrome di Windows:
// "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36
//  (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36"
// ============================================================

/**
 * Deteksi nama browser dari User-Agent string.
 * Catatan: urutan pengecekan penting! Edge mengandung kata "Chrome",
 * jadi Edge harus dicek sebelum Chrome.
 *
 * @param string $ua  User-Agent string dari browser
 * @return string     Nama browser: Firefox, Chrome, Safari, Edge, Opera, atau Lainnya
 */
function deteksiBrowser($ua)
{
    if (stripos($ua, 'Firefox') !== false) return 'Firefox';
    if (stripos($ua, 'Chrome')  !== false) return 'Chrome';  // Termasuk Edge berbasis Chromium
    if (stripos($ua, 'Safari')  !== false) return 'Safari';
    if (stripos($ua, 'Edge')    !== false) return 'Edge';
    if (stripos($ua, 'Opera')   !== false) return 'Opera';
    return 'Lainnya';
}

/**
 * Deteksi sistem operasi dari User-Agent string.
 *
 * @param string $ua  User-Agent string dari browser
 * @return string     Nama OS: Windows, Android, iOS, MacOS, Linux, atau Lainnya
 */
function deteksiOS($ua)
{
    if (preg_match('/windows/i',    $ua)) return 'Windows';
    if (preg_match('/android/i',    $ua)) return 'Android';
    if (preg_match('/iphone|ipad/i',$ua)) return 'iOS';
    if (preg_match('/mac/i',        $ua)) return 'MacOS';
    if (preg_match('/linux/i',      $ua)) return 'Linux';
    return 'Lainnya';
}

/**
 * Deteksi apakah pengguna mengakses dari HP (Mobile) atau Komputer (Desktop).
 * Cukup cari kata kunci umum yang ada di User-Agent perangkat mobile.
 *
 * @param string $ua  User-Agent string dari browser
 * @return string     'Mobile' atau 'Desktop'
 */
function deteksiPerangkat($ua)
{
    return preg_match('/mobile|android|iphone|ipad/i', $ua)
        ? 'Mobile'
        : 'Desktop';
}

// ============================================================
// ANTI DOUBLE LOGGING
// Cegah pencatatan berulang dalam satu sesi login yang sama.
// Jika flag 'akses_dicatat' sudah ada di $_SESSION,
// berarti sudah dicatat sebelumnya — lewati (return) saja.
// ============================================================
if (!empty($_SESSION['akses_dicatat'])) {
    return; // Sudah dicatat di sesi ini, tidak perlu catat lagi
}

// ============================================================
// KUMPULKAN DATA AKSES PENGGUNA
// Ambil semua informasi yang perlu dicatat ke database
// ============================================================
$id_user    = $_SESSION['sesi_id']              ?? null;            // ID pengguna dari sesi login (contoh: USER001)
$ip_address = $_SERVER['REMOTE_ADDR']           ?? 'Tidak diketahui'; // Alamat IP pengunjung
$user_agent = $_SERVER['HTTP_USER_AGENT']       ?? 'Tidak diketahui'; // String browser mentah

$browser  = deteksiBrowser($user_agent); // Nama browser (Chrome, Firefox, dll.)
$sistem_os = deteksiOS($user_agent);     // Nama sistem operasi (Windows, Android, dll.)
$perangkat = deteksiPerangkat($user_agent); // Jenis perangkat (Mobile / Desktop)

$tanggal = date('Y-m-d'); // Tanggal akses, contoh: 2025-07-05
$waktu   = date('H:i:s'); // Waktu akses, contoh: 14:30:22

// ============================================================
// SIMPAN KE DATABASE (tabel 'rekam_akses_web')
//
// Kolom yang diisi:
//   id_user         → VARCHAR — dari sesi login (contoh: USER001)
//   alamat_ip       → VARCHAR — IP address pengguna
//   agen_pengguna   → TEXT    — User-Agent string mentah (untuk analisis)
//   browser         → VARCHAR — nama browser
//   sistem_operasi  → VARCHAR — nama OS
//   perangkat       → VARCHAR — Mobile atau Desktop
//   tanggal_akses   → DATE    — tanggal login
//   waktu_akses     → TIME    — jam login
//
// Menggunakan Prepared Statement agar aman dari SQL Injection.
// 'ssssssss' = 8 parameter string (s = string)
// ============================================================
if ($id_user && isset($koneksi)) {
    $stmt = $koneksi->prepare("
        INSERT INTO rekam_akses_web
        (id_user, alamat_ip, agen_pengguna, browser, sistem_operasi, perangkat, tanggal_akses, waktu_akses)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            'ssssssss',   // 8 parameter, semuanya string
            $id_user,     // s → id_user (contoh: "USER001")
            $ip_address,  // s → alamat_ip
            $user_agent,  // s → agen_pengguna (User-Agent mentah)
            $browser,     // s → browser (contoh: "Chrome")
            $sistem_os,   // s → sistem_operasi (contoh: "Windows")
            $perangkat,   // s → perangkat (contoh: "Desktop")
            $tanggal,     // s → tanggal_akses
            $waktu        // s → waktu_akses
        );

        $stmt->execute();
        $stmt->close();

        // Tandai di sesi bahwa log sudah dicatat — mencegah pencatatan ganda
        // jika halaman di-refresh atau pengguna navigasi ke halaman lain
        $_SESSION['akses_dicatat'] = true;
    }
}
