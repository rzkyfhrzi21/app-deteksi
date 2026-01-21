<?php
include 'koneksi.php';

/* ===============================
   SET WAKTU DASAR
================================ */
$tanggal_sekarang = date('Y-m-d');
$bulan_sekarang   = date('m');
$tahun_sekarang   = date('Y');
$pukul            = date('H:i');

/* ===============================
   HELPER FUNCTION (ANTI DOUBLE)
================================ */
if (!function_exists('getCount')) {
    function getCount($table, $where = '')
    {
        global $koneksi;

        $sql = "SELECT COUNT(*) AS total FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        $query = mysqli_query($koneksi, $sql);
        if (!$query) return 0;

        $data = mysqli_fetch_assoc($query);
        return (int) ($data['total'] ?? 0);
    }
}

/* ===============================
   USER STAT
================================ */
$totalPengguna = getCount('users');

$totalPenggunaBaru = getCount(
    'users',
    "MONTH(created_at) = '$bulan_sekarang' 
     AND YEAR(created_at) = '$tahun_sekarang'"
);

/* ===============================
   VISITOR STAT
================================ */
$totalPengunjung = getCount('rekam_akses_web');

$pengunjungHariIni = getCount(
    'rekam_akses_web',
    "tanggal_akses = '$tanggal_sekarang'"
);

/* ===============================
   DETEKSI STAT (INTI SISTEM)
================================ */
$totalDeteksi = getCount('hasil_deteksi');

$deteksiHariIni = getCount(
    'hasil_deteksi',
    "DATE(created_at) = '$tanggal_sekarang'"
);

/* ===============================
   RATA-RATA CONFIDENCE
================================ */
$avgConfidence = 0;
$sqlAvg = mysqli_query(
    $koneksi,
    "SELECT AVG(confidence) AS avg_conf FROM hasil_deteksi"
);

if ($sqlAvg) {
    $row = mysqli_fetch_assoc($sqlAvg);
    $avgConfidence = round((float) ($row['avg_conf'] ?? 0), 4);
}

/* ===============================
   DISTRIBUSI PENYAKIT
================================ */
$distribusiPenyakit = [];

$sqlDistribusi = mysqli_query(
    $koneksi,
    "SELECT label_penyakit, COUNT(*) AS total 
     FROM hasil_deteksi 
     GROUP BY label_penyakit"
);

if ($sqlDistribusi) {
    while ($row = mysqli_fetch_assoc($sqlDistribusi)) {
        $distribusiPenyakit[$row['label_penyakit']] = (int) $row['total'];
    }
}
