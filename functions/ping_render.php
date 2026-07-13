<?php
// FILE: functions/ping_render.php
// TUJUAN: Ping endpoint /health di API Render dari sisi server (PHP/CURL).
// Dipanggil dari JS (fetch) di mulai_deteksi.php untuk menghindari masalah CORS browser.
// Independen — tidak bergantung ke file PHP lain.

header('Content-Type: application/json');

// Hardcode URL health (supaya tidak perlu require file lain yang bisa konflik)
$healthUrl = 'https://app-deteksi.onrender.com/health';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $healthUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_HEADER         => false,
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error    = curl_error($curl);
curl_close($curl);

if ($error) {
    echo json_encode(['status' => 'error', 'message' => $error]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['status' => 'success', 'http_code' => $httpCode]);
} else {
    echo json_encode(['status' => 'error', 'http_code' => $httpCode, 'message' => 'HTTP ' . $httpCode]);
}
