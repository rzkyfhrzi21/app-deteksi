<?php

/**
 * FILE: log_akses.php
 * Fungsi:
 * - Mencatat akses login ke database
 * - Anti double logging (1 IP per session)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

/* ===============================
   HELPER DETEKSI
================================ */
function deteksiBrowser($ua)
{
    if (stripos($ua, 'Firefox') !== false) return 'Firefox';
    if (stripos($ua, 'Chrome') !== false) return 'Chrome';
    if (stripos($ua, 'Safari') !== false) return 'Safari';
    if (stripos($ua, 'Edge') !== false) return 'Edge';
    if (stripos($ua, 'Opera') !== false) return 'Opera';
    return 'Lainnya';
}

function deteksiOS($ua)
{
    if (preg_match('/windows/i', $ua)) return 'Windows';
    if (preg_match('/android/i', $ua)) return 'Android';
    if (preg_match('/iphone|ipad/i', $ua)) return 'iOS';
    if (preg_match('/mac/i', $ua)) return 'MacOS';
    if (preg_match('/linux/i', $ua)) return 'Linux';
    return 'Lainnya';
}

function deteksiPerangkat($ua)
{
    return preg_match('/mobile|android|iphone|ipad/i', $ua)
        ? 'Mobile'
        : 'Desktop';
}

/* ===============================
   ANTI DOUBLE LOGGING
================================ */
/**
 * Cegah pencatatan ulang:
 * - 1 session
 * - 1 IP
 */
if (!empty($_SESSION['akses_dicatat'])) {
    return; // Sudah dicatat di session ini
}

/* ===============================
   AMBIL DATA AKSES
================================ */
$id_user     = $_SESSION['sesi_id'] ?? null;
$ip_address  = $_SERVER['REMOTE_ADDR'] ?? 'Tidak diketahui';
$user_agent  = $_SERVER['HTTP_USER_AGENT'] ?? 'Tidak diketahui';

$browser     = deteksiBrowser($user_agent);
$sistem_os   = deteksiOS($user_agent);
$perangkat   = deteksiPerangkat($user_agent);

$tanggal     = date('Y-m-d');
$waktu       = date('H:i:s');

/* ===============================
   SIMPAN KE DATABASE
================================ */
if ($id_user && isset($koneksi)) {
    $stmt = $koneksi->prepare("
        INSERT INTO rekam_akses_web
        (id_user, alamat_ip, agen_pengguna, browser, sistem_operasi, perangkat, tanggal_akses, waktu_akses)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            'ssssssss',
            $id_user,
            $ip_address,
            $user_agent,
            $browser,
            $sistem_os,
            $perangkat,
            $tanggal,
            $waktu
        );

        $stmt->execute();
        $stmt->close();

        // Tandai session sudah dicatat
        $_SESSION['akses_dicatat'] = true;
    }
}
