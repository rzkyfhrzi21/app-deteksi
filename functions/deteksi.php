<?php
session_start();

require_once 'koneksi.php'; // pakai koneksi DB

function deteksi_penyakit($filePath)
{
    $apiUrl = "http://127.0.0.1:5000/predict";

    if (!file_exists($filePath)) {
        return [
            "status"  => "error",
            "message" => "File tidak ditemukan: $filePath"
        ];
    }

    $curl = curl_init();

    $cfile = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
    $postFields = [
        "image" => $cfile
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($curl);
    $error    = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return [
            "status"  => "error",
            "message" => "Gagal menghubungi API Flask: $error"
        ];
    }

    $result = json_decode($response, true);

    if (!$result || isset($result["error"])) {
        return [
            "status"  => "error",
            "message" => $result["error"] ?? "Respon Flask tidak valid."
        ];
    }

    return [
        "status"     => "success",
        "label"      => $result["label"] ?? 'Tidak diketahui',
        "confidence" => $result["confidence"] ?? 0,
        "probs"      => $result["probs"] ?? null,
    ];
}

// ===================
// HANDLE FORM UPLOAD
// ===================

if (!isset($_POST['btn_upload_daun'])) {
    header('Location: ../dashboard/admin?page=deteksi');
    exit;
}

if (!isset($_FILES['gambar_daun']) || $_FILES['gambar_daun']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Gambar gagal diupload.'
    ];
    header('Location: ../dashboard/admin?page=deteksi');
    exit;
}

$fileTmpPath = $_FILES['gambar_daun']['tmp_name'];
$fileName    = $_FILES['gambar_daun']['name'];
$fileSize    = $_FILES['gambar_daun']['size'];

$allowedExtensions = ['jpg', 'jpeg', 'png'];
$fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Format file tidak didukung. Gunakan JPG/PNG.'
    ];
    header('Location: ../dashboard/admin?page=deteksi');
    exit;
}

if ($fileSize > 2 * 1024 * 1024) { // 2MB
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Ukuran file maksimal 2MB.'
    ];
    header('Location: ../dashboard/admin?page=deteksi');
    exit;
}

// simpan file ke folder uploads/deteksi
$uploadDir = __DIR__ . '/../uploads/deteksi/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$nama_file_baru = 'padi_' . time() . '.' . $fileExtension;
$destPath       = $uploadDir . $nama_file_baru;

if (!move_uploaded_file($fileTmpPath, $destPath)) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Terjadi kesalahan saat menyimpan file.'
    ];
    header('Location: ../dashboard/admin?page=deteksi');
    exit;
}

// path untuk ditampilkan di <img> dari sudut pandang /dashboard/
$file_public = '../uploads/deteksi/' . $nama_file_baru;

// path yang disimpan di database (relatif dari root project)
$file_db_path = 'uploads/deteksi/' . $nama_file_baru;

// catatan dari form
$catatan = $_POST['catatan'] ?? null;

// panggil API Flask
$hasil_api = deteksi_penyakit($destPath);

// siapkan data untuk DB
$id_user     = $_SESSION['sesi_id'] ?? null;
$label_db    = $hasil_api['label'] ?? 'Tidak diketahui';
$conf_db     = $hasil_api['confidence'] ?? 0.0;

// ====== SIMPAN KE DATABASE: tabel hasil_deteksi ======
if (isset($koneksi) && $koneksi) {
    $stmt = $koneksi->prepare("
        INSERT INTO hasil_deteksi (id_user, file_path, label_penyakit, confidence, catatan, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    if ($stmt) {
        $stmt->bind_param(
            'issds',
            $id_user,
            $file_db_path,
            $label_db,
            $conf_db,
            $catatan
        );
        $stmt->execute();
        $stmt->close();
    }
}

// ====== SIAPKAN DATA UNTUK DITAMPILKAN DI HALAMAN ======
if ($hasil_api['status'] === 'success') {
    $_SESSION['hasil_deteksi'] = [
        'label'       => $hasil_api['label'],
        'confidence'  => $hasil_api['confidence'],
        'file_public' => $file_public,
        'message'     => null
    ];
} else {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => $file_public,
        'message'     => $hasil_api['message'] ?? 'Terjadi kesalahan.'
    ];
}

header('Location: ../dashboard/admin?page=deteksi');
exit;
