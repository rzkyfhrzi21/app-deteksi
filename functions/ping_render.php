<?php
// ============================================================
// FILE: ping_render.php
// TUJUAN: "Membangunkan" server Flask di Render.com agar siap
//         menerima foto sebelum pengguna melakukan deteksi.
//
// Ibarat "bel pintu" — sebelum mengirim paket (foto daun padi),
// kita dulu pencet bel untuk memastikan si penerima (server Flask)
// sudah bangun dan siap menerima.
//
// KENAPA DIBUTUHKAN?
//   Server Flask di Render.com (free tier) otomatis "tidur" setelah
//   15 menit tidak ada request. Saat tidur, request pertama akan
//   menunggu 30-60 detik (cold start). Agar pengguna tidak frustrasi
//   menunggu lama saat upload foto, sebaiknya server dibangunkan
//   lebih dulu dengan menekan tombol "Tes Koneksi" di halaman deteksi.
//
// KENAPA FILE PHP (BUKAN LANGSUNG DARI BROWSER)?
//   Jika browser langsung memanggil URL Render.com, akan terjadi error
//   CORS (Cross-Origin Resource Sharing) — browser memblokir request
//   ke domain berbeda karena alasan keamanan.
//   Solusinya: browser memanggil file PHP lokal ini (domain sama),
//   lalu PHP yang menghubungi Render.com menggunakan cURL (sisi server,
//   tidak ada batasan CORS).
//
// DIPANGGIL OLEH:
//   JavaScript fetch() di mulai_deteksi.php → /functions/ping_render.php
//
// RESPONS JSON YANG DIKEMBALIKAN:
//   Sukses : {"status": "success", "http_code": 200}
//   Gagal  : {"status": "error",   "http_code": 503, "message": "HTTP 503"}
//   Error  : {"status": "error",   "message": "cURL error: ..."}
// ============================================================

// Beritahu browser bahwa respons yang dikembalikan adalah format JSON
header('Content-Type: application/json');

// ============================================================
// URL ENDPOINT HEALTH CHECK
// Endpoint /health di Flask hanya mengembalikan status server —
// tidak memproses foto, jadi prosesnya cepat dan ringan.
// Hardcode di sini agar tidak perlu include file lain
// (mencegah konflik atau error jika file lain bermasalah).
// ============================================================
$healthUrl = 'https://app-deteksi.onrender.com/health';

// ============================================================
// KIRIM REQUEST KE SERVER FLASK MENGGUNAKAN cURL
//
// cURL (Client URL) adalah cara PHP berkomunikasi dengan
// server lain melalui internet — seperti browser, tapi di sisi server.
//
// Opsi yang dipakai:
//   CURLOPT_URL            → alamat yang dituju
//   CURLOPT_RETURNTRANSFER → simpan hasil sebagai string (bukan langsung print)
//   CURLOPT_TIMEOUT        → batas waktu tunggu: 120 detik
//                            (lebih lama dari cold start normal ~60 detik)
//   CURLOPT_HEADER         → false = jangan sertakan header HTTP di respons
// ============================================================
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $healthUrl,
    CURLOPT_RETURNTRANSFER => true,  // Simpan respons ke variabel $response
    CURLOPT_TIMEOUT        => 120,   // Tunggu maksimal 120 detik (cold start bisa ~60 detik)
    CURLOPT_HEADER         => false, // Jangan sertakan header HTTP dalam respons
]);

$response = curl_exec($curl);                              // Jalankan request
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);       // Baca kode status HTTP (200, 503, dll.)
$error    = curl_error($curl);                             // Ambil pesan error jika ada
curl_close($curl);                                         // Tutup koneksi cURL untuk hemat memori

// ============================================================
// KEMBALIKAN HASIL SEBAGAI JSON KE JAVASCRIPT
// ============================================================

// Jika terjadi error koneksi (server tidak bisa dicapai sama sekali)
if ($error) {
    echo json_encode(['status' => 'error', 'message' => $error]);
    exit;
}

// Jika server membalas dengan kode sukses (200-299)
if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['status' => 'success', 'http_code' => $httpCode]);
} else {
    // Server membalas tapi dengan kode error (503 = server tidur, 500 = error internal, dll.)
    echo json_encode(['status' => 'error', 'http_code' => $httpCode, 'message' => 'HTTP ' . $httpCode]);
}
