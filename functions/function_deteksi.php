<?php
// ============================================================
// FILE: function_deteksi.php
// TUJUAN: Memproses foto daun padi yang dikirim pengguna,
//         mengirimkannya ke model AI (Flask), dan menyimpan hasilnya.
//
// Ibarat "petugas laboratorium" — menerima sampel foto daun,
// mengirim ke mesin analisis (API Flask), mencatat hasilnya
// ke buku catatan (database), lalu melaporkan hasilnya ke
// layar pengguna.
//
// FILE INI BERISI DUA BAGIAN BESAR:
//   [1] btn_hapusdeteksi  → Hapus satu baris riwayat deteksi
//   [2] btn_upload_daun   → Proses upload foto, deteksi AI, simpan ke DB
//
// ALUR KERJA UTAMA (btn_upload_daun):
// (1) Pengguna klik "Upload & Deteksi" di mulai_deteksi.php
// (2) File foto dikirim ke sini via POST multipart
// (3) Validasi: format file, ukuran file (maks 2MB)
// (4) Simpan foto ke folder uploads/deteksi/ dengan nama unik
// (5) Kirim foto ke API Flask (/predict) menggunakan cURL
// (6) Terima hasil JSON: label penyakit + confidence
// (7) Simpan hasil ke tabel 'hasil_deteksi' di database
// (8) Simpan hasil ke $_SESSION lalu redirect ke mulai_deteksi.php
// (9) mulai_deteksi.php membaca $_SESSION dan menampilkan hasilnya
// ============================================================

// Mulai sesi untuk akses $_SESSION (id user, dll.)
session_start();

// Koneksi ke database MySQL
require_once 'koneksi.php';

// ============================================================
// KONFIGURASI MODE API
// ============================================================
// Ubah ke "local" saat testing di komputer sendiri agar PHP
// mengirim foto ke Flask yang berjalan di localhost:5000
// (bukan ke server Render.com yang mungkin butuh waktu boot).
//
// Saat sudah siap production, kembalikan ke "online".
// ============================================================
define('API_MODE', 'online'); // 'online' = Render.com | 'local' = localhost:5000

if (API_MODE === 'online') {
    define('API_URL', 'https://app-deteksi.onrender.com/predict'); // URL produksi di Render.com
} else {
    define('API_URL', 'http://127.0.0.1:5000/predict');            // URL testing di komputer sendiri
}

// ============================================================
// MAPPING LABEL MODEL → NAMA TAMPILAN
// ============================================================
// Model AI menyebut penyakit dengan nama folder dataset-nya
// (tanpa spasi, huruf besar di awal), contoh: "Bacterialblight".
// Array ini menerjemahkannya ke nama yang lebih rapi untuk ditampilkan
// di layar pengguna.
//
// Catatan:
// - Database menyimpan label ASLI (contoh: 'Bacterialblight')
//   agar query SQL tetap konsisten.
// - Tampilan di layar menggunakan label yang sudah diterjemahkan
//   (contoh: 'Bacterial Blight').
//
// PENTING: Urutan dan nama label HARUS sama persis dengan
//          CLASS_NAMES di api_flask.py!
// ============================================================
define('LABEL_MAP', [
    'Healthy'         => 'Healthy (Daun Sehat)', // Index 0 — daun normal, tidak sakit
    'Bacterialblight' => 'Bacterial Blight',     // Index 1 — hawar bakteri
    'Blast'           => 'Blast',                // Index 2 — blas/busuk leher
    'Brownspot'       => 'Brown Spot',           // Index 3 — bercak cokelat
    'Tungro'          => 'Tungro',               // Index 4 — virus tungro
]);

/**
 * Fungsi label_display() — Ubah label model mentah → nama tampilan rapi.
 *
 * Contoh: label_display('Bacterialblight') → 'Bacterial Blight'
 * Jika label tidak dikenal (tidak ada di LABEL_MAP), kembalikan apa adanya.
 *
 * @param string $label  Label asli dari model AI
 * @return string        Nama tampilan yang sudah diterjemahkan
 */
function label_display(string $label): string
{
    return LABEL_MAP[$label] ?? $label; // Jika ada di peta → tampilkan terjemahan. Jika tidak → kembalikan asli
}

// ============================================================
// BAGIAN 1: HAPUS RIWAYAT DETEKSI
// ============================================================
// Dipanggil ketika pengguna mengklik tombol "Hapus" di halaman
// riwayat_deteksi.php.
//
// Data yang dikirim via POST:
//   - btn_hapusdeteksi  → penanda bahwa aksi ini adalah HAPUS
//   - id_deteksi        → nomor baris di tabel 'hasil_deteksi' yang dihapus
//   - file_path         → path file gambar di server (untuk dihapus dari hardisk)
//
// ALUR KERJA:
// (1) Ambil id_deteksi (cast ke integer agar aman) dan file_path
// (2) Hapus file gambar fisik dari folder uploads/deteksi/ agar hardisk tidak penuh
// (3) Hapus baris riwayat dari tabel 'hasil_deteksi' berdasarkan id_deteksi
// (4) Redirect kembali ke halaman riwayat
// ============================================================
if (isset($_POST['btn_hapusdeteksi'])) {
    // 0. Validasi CSRF Token
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $id_deteksi = (int) ($_POST['id_deteksi'] ?? 0);

    if ($id_deteksi > 0 && isset($koneksi) && $koneksi) {
        // 1. Ambil path file langsung dari database (Mencegah Path Traversal)
        $stmt_select = $koneksi->prepare("SELECT file_path FROM hasil_deteksi WHERE id_deteksi = ?");
        if ($stmt_select) {
            $stmt_select->bind_param('i', $id_deteksi);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            if ($row = $result->fetch_assoc()) {
                $file_path = $row['file_path'];
                
                // 2. Hapus file fisik
                if (!empty($file_path)) {
                    $full_path = __DIR__ . '/../' . $file_path;
                    if (file_exists($full_path)) {
                        @unlink($full_path);
                    }
                }
                
                // 3. Hapus baris dari database
                $stmt_delete = $koneksi->prepare("DELETE FROM hasil_deteksi WHERE id_deteksi = ?");
                if ($stmt_delete) {
                    $stmt_delete->bind_param('i', $id_deteksi);
                    $stmt_delete->execute();
                    $stmt_delete->close();
                }
            }
            $stmt_select->close();
        }
    }

    // 4. Redirect kembali ke halaman riwayat deteksi
    header('Location: ../dashboard/admin?page=riwayat deteksi');
    exit;
}

// ============================================================
// BAGIAN 2: FUNGSI KOMUNIKASI DENGAN API FLASK
// ============================================================
// Fungsi deteksi_penyakit() adalah "kurir" antara PHP dan Flask.
// Ia mengambil foto yang sudah tersimpan di server, mengirimkannya
// ke API Flask menggunakan cURL, dan mengembalikan hasil prediksinya.
//
// Mengapa cURL? Karena PHP tidak bisa langsung menjalankan model AI.
// cURL memungkinkan PHP berkomunikasi dengan server lain (Flask)
// layaknya browser mengirim request HTTP.
//
// PARAMETER:
//   $filePath → path absolut foto yang sudah tersimpan di server
//               (contoh: /var/www/html/app-deteksi/uploads/deteksi/padi_xxx.jpg)
//
// RETURN (array):
//   Sukses : ['status' => 'success', 'label' => '...', 'confidence' => 0.93, 'probs' => [...]]
//   Gagal  : ['status' => 'error',   'message' => 'Pesan kesalahan']
// ============================================================
function deteksi_penyakit($filePath)
{
    $apiUrl = API_URL; // Ambil URL API dari konstanta yang sudah didefinisikan di atas

    // (1) Pastikan file benar-benar ada sebelum mencoba mengirimnya
    if (!file_exists($filePath)) {
        return [
            "status"  => "error",
            "message" => "File tidak ditemukan: $filePath"
        ];
    }

    // (2) Inisialisasi sesi cURL — ibarat 'membuka koneksi telepon' ke Flask
    $curl = curl_init();

    // (3) Buat objek CURLFile yang berisi informasi foto yang akan dikirim:
    //     - path file   → lokasi file di server
    //     - MIME type   → jenis file (misal: image/jpeg)
    //     - nama file   → nama yang dikirim ke Flask
    $cfile = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
    $postFields = [
        "image" => $cfile // Field 'image' — harus sama dengan yang dicek di api_flask.py!
    ];

    // (4) Atur konfigurasi cURL untuk POST ke API Flask
    // Keamanan (OWASP Top 10): Menyisipkan 'kata sandi rahasia' (X-API-KEY)
    // agar server Flask mengenali bahwa kiriman ini resmi dari website kita, bukan dari hacker.
    curl_setopt_array($curl, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_POST           => true,            // Kirim sebagai POST request
        CURLOPT_RETURNTRANSFER => true,            // Simpan respons Flask ke variabel (jangan langsung print)
        CURLOPT_POSTFIELDS     => $postFields,     // Isi body POST: file gambar
        CURLOPT_TIMEOUT        => 60,              // Tunggu maksimal 60 detik (antisipasi cold start Render.com)
        CURLOPT_HTTPHEADER     => [                // Sisipkan Kunci Rahasia API
            'X-API-KEY: SistemPakarDeteksiDaunPadi_2026_Aman'
        ],
    ]);

    // (5) Eksekusi request — PHP mengirim foto ke Flask dan menunggu balasan
    $response = curl_exec($curl);
    $error    = curl_error($curl); // Tangkap pesan error jika koneksi gagal
    curl_close($curl);             // Tutup koneksi cURL

    // (6) Jika terjadi error jaringan (Flask tidak bisa dicapai)
    if ($error) {
        return [
            "status"  => "error",
            "message" => "Gagal menghubungi API Flask: $error"
        ];
    }

    // (7) Ubah respons JSON dari Flask menjadi array PHP
    $result = json_decode($response, true);

    // (8) Validasi: pastikan Flask mengembalikan data yang valid
    if (!$result || isset($result["error"])) {
        return [
            "status"  => "error",
            "message" => $result["error"] ?? "Respon Flask tidak valid."
        ];
    }

    // (9) Kembalikan hasil prediksi yang dibutuhkan
    return [
        "status"     => "success",
        "label"      => $result["label"]      ?? 'Tidak diketahui', // Nama penyakit (contoh: 'Bacterialblight')
        "confidence" => $result["confidence"] ?? 0,                 // Tingkat keyakinan 0.0-1.0
        "probs"      => $result["probs"]      ?? null,              // Probabilitas semua 5 kelas
    ];
}

// ============================================================
// BAGIAN 3: PROSES FORM UPLOAD & DETEKSI FOTO DAUN PADI
// ============================================================
// Dipanggil ketika pengguna menekan tombol "Upload & Deteksi"
// di halaman mulai_deteksi.php.
//
// Nama tombol submit: btn_upload_daun
// (Dikirim sebagai hidden input agar tetap terbaca meski button di-disable)
// ============================================================

// Keamanan: jika tidak ada tombol upload di request, lempar balik ke halaman deteksi
// (mencegah orang mengakses file ini langsung via URL browser)
if (!isset($_POST['btn_upload_daun'])) {
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// ============================================================
// VALIDASI CSRF TOKEN
// ============================================================
verify_csrf_token($_POST['csrf_token'] ?? '');

// ============================================================
// VALIDASI FILE UPLOAD
// Cek apakah file foto berhasil diterima oleh server.
// UPLOAD_ERR_OK = 0 = tidak ada error saat upload.
// ============================================================
if (!isset($_FILES['gambar_daun']) || $_FILES['gambar_daun']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Gambar gagal diupload. Pastikan ukuran file tidak melebihi batas server.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// ============================================================
// AMBIL INFO FILE YANG DIUPLOAD
// ============================================================
$fileTmpPath = $_FILES['gambar_daun']['tmp_name']; // Path sementara file di server
$fileName    = $_FILES['gambar_daun']['name'];     // Nama asli file dari komputer pengguna
$fileSize    = $_FILES['gambar_daun']['size'];     // Ukuran file dalam bytes

// ============================================================
// VALIDASI FORMAT FILE
// Hanya izinkan foto dengan ekstensi jpg, jpeg, png.
// Periksa ekstensi file (bukan MIME type) — cukup untuk kebutuhan ini.
// ============================================================
$allowedExtensions = ['jpg', 'jpeg', 'png']; // Daftar ekstensi yang diizinkan
$fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Ambil ekstensi, jadikan huruf kecil

if (!in_array($fileExtension, $allowedExtensions)) {
    // Format tidak didukung — kembalikan dengan pesan error
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Format file tidak didukung. Gunakan JPG atau PNG.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// ============================================================
// VALIDASI UKURAN FILE
// Batasi maksimal 2MB = 2 × 1024 × 1024 bytes = 2.097.152 bytes
// Model AI bisa memproses foto lebih kecil dengan lebih cepat.
// ============================================================
if ($fileSize > 2 * 1024 * 1024) {
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Ukuran file terlalu besar. Maksimal 2MB.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// ============================================================
// SIMPAN FILE FOTO KE FOLDER uploads/deteksi/
// ============================================================
// File yang diupload pertama kali disimpan sementara oleh PHP
// di folder tmp/ sistem (path ada di $fileTmpPath).
// Kita harus memindahkannya ke folder permanen uploads/deteksi/
// sebelum bisa dikirim ke Flask.
//
// Nama file dibuat UNIK menggunakan timestamp Unix (waktu dalam detik)
// agar tidak ada dua file dengan nama yang sama, meski diupload
// pada waktu yang hampir bersamaan.
// Format: padi_[timestamp].[ekstensi], contoh: padi_1720853422.jpg
// ============================================================

$uploadDir = __DIR__ . '/../uploads/deteksi/';

// Buat folder jika belum ada (0777 = izin baca/tulis/eksekusi untuk semua pengguna)
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // true = buat folder induk jika juga belum ada
}

// Nama file baru: padi_ + waktu sekarang (detik) + ekstensi
// Contoh: padi_1720853422.jpg
$nama_file_baru = 'padi_' . time() . '.' . $fileExtension;

// Path absolut tujuan file di server (untuk move_uploaded_file)
$destPath = $uploadDir . $nama_file_baru;

// Pindahkan file dari lokasi sementara ke folder uploads/deteksi/
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    // Gagal pindahkan — biasanya masalah izin folder (permissions)
    $_SESSION['hasil_deteksi'] = [
        'label'       => 'Error',
        'confidence'  => 0,
        'file_public' => null,
        'message'     => 'Terjadi kesalahan saat menyimpan file.'
    ];
    header('Location: ../dashboard/admin?page=mulai deteksi');
    exit;
}

// Path untuk tag <img> di halaman dashboard (dari sudut pandang folder dashboard/)
// Contoh: ../uploads/deteksi/padi_1720853422.jpg
$file_public = '../uploads/deteksi/' . $nama_file_baru;

// Path yang DISIMPAN ke database (relatif dari root project, tanpa ../ di depan)
// Contoh: uploads/deteksi/padi_1720853422.jpg
// Kenapa berbeda? Karena ini dipakai untuk dihapus fisiknya via PHP,
// yang perlu tahu path dari perspektif root, bukan dari folder dashboard/.
$file_db_path = 'uploads/deteksi/' . $nama_file_baru;

// Catatan tambahan dari pengguna (opsional, bisa null jika tidak diisi)
$catatan = $_POST['catatan'] ?? null;


// ============================================================
// PANGGIL API FLASK UNTUK DETEKSI (LANGKAH KRUSIAL!)
// ============================================================
// Fungsi deteksi_penyakit() (definisi ada di Bagian 2 di atas)
// akan mengirim foto ke Flask dan mengembalikan hasil prediksinya.
//
// $destPath = path absolut file foto yang sudah tersimpan
// (contoh: D:\laragon\www\App Deteksi\uploads\deteksi\padi_xxx.jpg)
//
// Hasil yang dikembalikan:
// $hasil_api['status']     → 'success' atau 'error'
// $hasil_api['label']      → nama penyakit asli (contoh: 'Bacterialblight')
// $hasil_api['confidence'] → tingkat yakin model: 0.0 - 1.0 (contoh: 0.93)
// $hasil_api['probs']      → probabilitas semua 5 kelas (tidak dipakai di sini)
// ============================================================
$hasil_api = deteksi_penyakit($destPath);

// Siapkan variabel untuk INSERT ke database
$id_user    = $_SESSION['sesi_id'] ?? null; // ID pengguna yang sedang login (contoh: "USER001")
$label_db   = $hasil_api['label']      ?? 'Tidak diketahui'; // Label asli dari model (disimpan ke DB)
$conf_db    = $hasil_api['confidence'] ?? 0.0;               // Confidence 0.0-1.0
$created_at = date('Y-m-d H:i:s');         // Waktu deteksi, contoh: "2025-07-05 14:30:00"



// ============================================================
// SIMPAN HASIL DETEKSI KE DATABASE (tabel 'hasil_deteksi')
// ============================================================
// Semua proses deteksi dicatat ke database agar tersedia di
// halaman riwayat_deteksi.php.
//
// Mapping kolom di tabel 'hasil_deteksi':
//   id_user        → VARCHAR — siapa yang melakukan deteksi (dari sesi login)
//   file_path      → VARCHAR — path relatif foto dari root project
//   label_penyakit → VARCHAR — nama penyakit dari model (contoh: 'Bacterialblight')
//   confidence     → FLOAT   — tingkat keyakinan 0.0-1.0
//   catatan        → TEXT    — catatan opsional dari pengguna (bisa null)
//   created_at     → DATETIME— waktu deteksi dilakukan
//
// Menggunakan Prepared Statement (?) agar aman dari SQL Injection.
// 'sssdss' = urutan tipe: s=string, d=double (untuk confidence)
// ============================================================
if (isset($koneksi) && $koneksi && $id_user) {
    $stmt = $koneksi->prepare("
        INSERT INTO hasil_deteksi (id_user, file_path, label_penyakit, confidence, catatan, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            'sssdss',       // urutan tipe: s=string, s=string, s=string, d=double, s=string, s=string
            $id_user,       // s → id pengguna (contoh: "USER001")
            $file_db_path,  // s → path file relatif dari root
            $label_db,      // s → nama penyakit dari model
            $conf_db,       // d → confidence 0.0-1.0
            $catatan,       // s → catatan opsional (boleh null)
            $created_at     // s → waktu deteksi
        );
        $stmt->execute();
        $stmt->close();
    }
}

// ============================================================
// KIRIM HASIL KE SESSION DAN REDIRECT
// ============================================================
// Hasil deteksi TIDAK bisa dikirim langsung ke halaman tujuan
// (karena redirect HTTP kehilangan semua data variabel PHP).
// Solusinya: simpan hasil ke $_SESSION, lalu redirect.
// Di halaman mulai_deteksi.php, hasil dibaca dari $_SESSION
// dan ditampilkan ke pengguna.
//
// Kenapa session dan bukan URL parameter?
// → Data confidence berupa float dan mungkin ada karakter khusus
//   yang repot jika dimasukkan ke URL query string.
// → Session lebih aman: tidak terlihat di URL browser.
// ============================================================

if ($hasil_api['status'] === 'success') {
    // Deteksi BERHASIL: simpan semua hasil ke session
    $label_mentah = $hasil_api['label'];              // Label asli dari model
    $_SESSION['hasil_deteksi'] = [
        'label'         => $label_mentah,             // Label asli → untuk disimpan ke DB (sudah dilakukan di atas)
        'label_display' => label_display($label_mentah), // Label rapi → untuk ditampilkan di UI
        'confidence'    => $hasil_api['confidence'],  // Confidence 0.0-1.0
        'file_public'   => $file_public,              // Path foto untuk tag <img>
        'message'       => null,                      // Tidak ada pesan error
        'waktu'         => $created_at,               // Waktu deteksi
    ];
} else {
    // Deteksi GAGAL (Flask error, timeout, dll.)
    // Simpan pesan error ke session agar bisa ditampilkan di halaman
    $_SESSION['hasil_deteksi'] = [
        'label'         => 'Error',
        'label_display' => 'Error',
        'confidence'    => 0,
        'file_public'   => $file_public,              // Tetap tampilkan fotonya meski gagal
        'message'       => $hasil_api['message'] ?? 'Terjadi kesalahan pada server AI.',
        'waktu'         => $created_at,
    ];
}

// Kembali ke halaman form deteksi — hasil akan dibaca dari $_SESSION di sana
header('Location: ../dashboard/admin?page=mulai deteksi');
exit;
