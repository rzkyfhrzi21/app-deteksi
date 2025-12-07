<?php

// PANEL URL SERVERMIKRO
// $panel_url  = 'https://sgdirect.servermikro.my.id:2222';
// $username   = 'zfkwrvad';
// $password   = 'N(1EeA3@6vkf';

$host = $_SERVER['HTTP_HOST'];

// Memeriksa apakah link adalah localhost
if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
    // UNTUK PENGGUNAAN LOCALHOST
    $server     = 'localhost';
    $username   = 'root';
    $password   = '';
    $database   = 'app_deteksi';
} else {
    // UNTUK PENGGUNAAN HOSTING SERVERMIKRO
    $server     = 'localhost';
    $username   = 'mhjvvvci_Deteksi';
    $password   = 'v449yvBvbKXCmrMutSg6';
    $database   = 'mhjvvvci_Deteksi';
}

$koneksi    = mysqli_connect($server, $username, $password, $database);

if (!$koneksi) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');

$pukul = date('H:i A');

if (!function_exists('formatTanggalIndonesia')) {
    function formatTanggalIndonesia($tanggalInggris)
    {
        $namaHari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];

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

        $date = new DateTime($tanggalInggris);
        $hariInggris = $date->format('l');
        $bulanInggris = $date->format('F');

        $hariIndonesia = $namaHari[$hariInggris];
        $bulanIndonesia = $namaBulan[$bulanInggris];

        return $hariIndonesia . ', ' . $date->format('d') . ' ' . $bulanIndonesia . ' ' . $date->format('Y');
    }
}
