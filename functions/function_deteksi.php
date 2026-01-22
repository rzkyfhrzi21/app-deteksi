<?php
// Mulai sesi untuk akses $_SESSION (id user, dll)
session_start();

// Koneksi ke database
require_once 'koneksi.php';

// =====================================
// MODE API (LOCAL / ONLINE)
// =====================================
// ubah ke "local" jika testing localhost
define('API_MODE', 'online');

if (API_MODE === 'online') {
    define('API_URL', 'https://app-deteksi.onrender.com/predict');
} else {
    define('API_URL', 'http://127.0.0.1:5000/predict');
}

/**
 * =====================================
 * BAGIAN 1: HAPUS RIWAYAT DETEKSI
 * =====================================
 * Dipanggil ketika tombol "Hapus" di riwayat deteksi diklik.
 * Form hapus mengirim:
 *  - id_deteksi
 *  - file_path (path file gambar di server, mis: uploads/deteksi/xxx.jpg)
 */
if (isset($_POST['btn_hapusdeteksi'])) {
    // Pastikan id_deteksi berupa integer
    $id_deteksi = (int) ($_POST['id_deteksi'] ?? 0);
    $file_path  = $_POST['file_path'] ?? '';

    // 1. Hapus file gambar dari folder uploads jika ada
    if (!empty($file_path)) {
        // __DIR__ = folder functions/
        // ../ = naik ke root project, lalu ditambah path relatif file
        $full_path = __DIR__ . '/../' . $file_path;
        if (file_exists($full_path)) {
            @unlink($full_path); // @ untuk supress warning jika gagal
        }
    }

    // 2. Hapus baris riwayat dari tabel hasil_deteksi
    if ($id_deteksi > 0 && isset($koneksi) && $koneksi) {
        $stmt = $koneksi->prepare("DELETE FROM hasil_deteksi WHERE id_deteksi = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id_deteksi); // i = integer
            $stmt->execute();
            $stmt->close();
        }
    }

    // Kembali ke halaman riwayat deteksi
    header('Location: ../dashboard/admin?page=riwayat deteksi');
    exit;
}

/**
 * =====================================
 * BAGIAN 2: FUNGSI PANGGIL API FLASK
 * =====================================
 * Fungsi ini:
 *  - mengirim file gambar ke Flask (endpoint /predict)
 *  - menerima hasil prediksi berupa JSON
 *  - mengembalikan array status, label, confidence, dll.
 */
function deteksi_penyakit($filePath)
{
    $apiUrl = API_URL;


    // Cek apakah file benar-benar ada di server
    if (!file_exists($filePath)) {
        return [
            "status"  => "error",
            "message" => "File tidak ditemukan: $filePath"
        ];
    }

    // Inisialisasi CURL
    $curl = curl_init();

    // Buat objek file untuk dikirim via multipart/form-data
    $cfile = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
    $postFields = [
        "image" => $cfile
    ];

    // Atur opsi CURL untuk POST ke API Flask
    curl_setopt_array($curl, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,   // agar hasil dikembalikan sebagai string
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_TIMEOUT        => 30,     // batas waktu 30 detik
    ]);

    // Eksekusi request ke Flask
    $response = curl_exec($curl);
    $error    = curl_error($curl);
    curl_close($curl);

    // Jika ada error jaringan / koneksi
    if ($error) {
        return [
            "status"  => "error",
            "message" => "Gagal menghubungi API Flask: $error"
        ];
    }

    // Decode JSON dari Flask
    $result = json_decode($response, true);

    // Validasi respon
    if (!$result || isset($result["error"])) {
        return [
            "status"  => "error",
            "message" => $result["error"] ?? "Respon Flask tidak valid."
        ];
    }

    // Kembalikan hasil yang dibutuhkan
    return [
        "status"     => "success",
        "label"      => $result["label"] ?? 'Tidak diketahui',
        "confidence" => $result["confidence"] ?? 0,
        "probs"      => $result["probs"] ?? null,
    ];
}

/**
 * =====================================
 * BAGIAN 3: HANDLE FORM UPLOAD DETEKSI
 * =====================================
 * Dipanggil ketika form upload di halaman "mulai deteksi" disubmit.
 * Name tombol submit: btn_upload_daun
 */

// Pastikan yang memanggil adalah form upload (bukan akses langsung URL)
if (!isset($_POST['btn_upload_daun'])) {
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// Validasi keberadaan file upload
if (!isset($_FILES['gambar_daun']) || $_FILES['gambar_daun']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Gambar gagal diupload.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// Ambil data dasar file
$fileTmpPath = $_FILES['gambar_daun']['tmp_name'];
$fileName    = $_FILES['gambar_daun']['name'];
$fileSize    = $_FILES['gambar_daun']['size'];

// Ekstensi yang diijinkan
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Cek format file
if (!in_array($fileExtension, $allowedExtensions)) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Format file tidak didukung. Gunakan JPG/PNG.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// Cek ukuran file (maksimal 2MB)
if ($fileSize > 2 * 1024 * 1024) { // 2MB
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Ukuran file maksimal 2MB.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// =====================================
// Simpan file ke folder uploads/deteksi
// =====================================

$uploadDir = __DIR__ . '/../uploads/deteksi/';

// Jika folder belum ada, buat dulu
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Buat nama file unik agar tidak bentrok
$nama_file_baru = 'padi_' . time() . '.' . $fileExtension;

// Full path fisik di server
$destPath       = $uploadDir . $nama_file_baru;

// Pindahkan file dari tmp ke folder tujuan
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Terjadi kesalahan saat menyimpan file.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// path untuk ditampilkan di <img> dari sudut pandang /dashboard/
$file_public  = '../uploads/deteksi/' . $nama_file_baru;

// path yang disimpan di database (relatif dari root project)
$file_db_path = 'uploads/deteksi/' . $nama_file_baru;

// catatan dari form (boleh kosong)
$catatan = $_POST['catatan'] ?? null;

// =====================================
// Panggil API Flask untuk deteksi
// =====================================
$hasil_api = deteksi_penyakit($destPath);

// Siapkan data untuk disimpan ke database
$id_user    = $_SESSION['sesi_id'] ?? null;  // STRING: contoh "USER001"
$label_db   = $hasil_api['label'] ?? 'Tidak diketahui';
$conf_db    = $hasil_api['confidence'] ?? 0.0;
$created_at = date('Y-m-d H:i:s'); // waktu proses deteksi (mengikuti timezone di koneksi.php)


// =====================================
// SIMPAN KE DATABASE: tabel hasil_deteksi
// =====================================

/**
 * Pastikan:
 *  - $id_user tidak null
 *  - koneksi $koneksi tersedia
 * Tipe kolom di tabel hasil_deteksi:
 *  - id_user        → VARCHAR (sama seperti di tabel users)
 *  - file_path      → VARCHAR
 *  - label_penyakit → VARCHAR
 *  - confidence     → FLOAT/DOUBLE
 *  - catatan        → TEXT/VARCHAR (boleh null)
 *  - created_at     → DATETIME
 */
if (isset($koneksi) && $koneksi && $id_user) {
    $stmt = $koneksi->prepare("
        INSERT INTO hasil_deteksi (id_user, file_path, label_penyakit, confidence, catatan, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        // 's' = string, 'd' = double
        $stmt->bind_param(
            'sssdss',
            $id_user,       // s → string (USER001)
            $file_db_path,  // s → path file relatif
            $label_db,      // s → nama penyakit
            $conf_db,       // d → nilai confidence
            $catatan,       // s → catatan user
            $created_at     // s → datetime
        );
        $stmt->execute();
        $stmt->close();
    }
}

// =====================================
// SIAPKAN DATA UNTUK DITAMPILKAN DI HALAMAN
// =====================================

/**
 * Data ini disimpan ke $_SESSION['hasil_deteksi'],
 * supaya bisa dibaca di halaman "mulai deteksi" dan
 * menampilkan hasil prediksi + gambar + waktu.
 */

if ($hasil_api['status'] === 'success') {
    $_SESSION['hasil_deteksi'] = [
        'label'       => $hasil_api['label'],       // label penyakit
        'confidence'  => $hasil_api['confidence'],  // nilai confidence (0–1)
        'file_public' => $file_public,              // path gambar untuk <img>
        'message'     => null,                      // tidak ada error
        'waktu'       => $created_at,               // waktu deteksi (sama seperti di DB)
    ];
} else {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => $file_public,                         // masih tampilkan gambar jika perlu
        'message'     => $hasil_api['message'] ?? 'Terjadi kesalahan.', // pesan error dari Flask / lokal
        'waktu'       => $created_at,                          // tetap isi waktu proses
    ];
}

// Redirect balik ke halaman form deteksi, hasil dibaca dari session
header('Location: ../dashboard/admin?page=mulai deteksi');
exit;
