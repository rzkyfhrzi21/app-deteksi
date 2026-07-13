<?php
// FILE: ping_render.php
// TUJUAN: Ping API Render dari sisi server (PHP) untuk menghindari masalah CORS di browser.

require_once 'function_deteksi.php'; // untuk load konstanta API_URL

header('Content-Type: application/json');

// Gunakan URL dasar (hapus endpoint /predict untuk dijadikan /health)
$healthUrl = str_replace('/predict', '/health', API_URL);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $healthUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120, // tunggu max 2 menit untuk cold start
    CURLOPT_HEADER         => false
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error    = curl_error($curl);
curl_close($curl);

if ($error) {
    echo json_encode([
        'status' => 'error',
        'message' => $error
    ]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode([
        'status' => 'success',
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'http_code' => $httpCode,
        'message' => 'Server merespon dengan kode ' . $httpCode
    ]);
}
