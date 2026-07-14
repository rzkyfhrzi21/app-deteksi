<?php
// ============================================================
// FILE: function_auth.php
// TUJUAN: Memproses formulir LOGIN dan REGISTRASI pengguna
// ============================================================

session_start(); // Hidupkan sistem sesi
include 'koneksi.php'; // Sambungkan ke database (dan muat fungsi keamanan)

// ============================================================
// BAGIAN A: PROSES LOGIN
// ============================================================
if (isset($_POST['btn_login'])) {
    // 1. Validasi CSRF Token (Pencegahan Pemalsuan Form)
    $csrf_token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($csrf_token);

    // 2. Ambil input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 3. Pencarian User dengan Prepared Statements (Mencegah SQL Injection)
    $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data_user = $result->fetch_assoc();
        
        // 4. Verifikasi Password BCRYPT (Mencegah peretasan MD5)
        if (password_verify($password, $data_user['password'])) {
            
            // 5. Cegah Session Fixation
            session_regenerate_id(true);

            // Simpan data sesi
            $_SESSION['sesi_id']       = $data_user['id_user'];
            $_SESSION['sesi_username'] = $data_user['username'];
            $_SESSION['sesi_nama']     = $data_user['nama_user'];
            $_SESSION['sesi_email']    = $data_user['email'];

            require_once 'log_akses.php'; // Rekam log

            if (empty($data_user['img_user'])) {
                header('Location: ../dashboard/admin?page=profile&id=' . urlencode($data_user['id_user']));
            } else {
                header('Location: ../dashboard/admin');
            }
            exit;
        } else {
            // Password salah
            header("Location: ../auth/login?action=login&status=error");
            exit;
        }
    } else {
        // Username tidak ditemukan
        header("Location: ../auth/login?action=login&status=error");
        exit;
    }
}

// ============================================================
// BAGIAN B: PROSES REGISTRASI (DAFTAR AKUN BARU)
// ============================================================
if (isset($_POST['btn_register'])) {
    // 1. Validasi CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($csrf_token);

    // 2. Ambil dan bersihkan input teks
    $nama_user           = htmlspecialchars($_POST['nama_user'], ENT_QUOTES, 'UTF-8');
    $username            = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    
    // Jangan hash/escape password di awal, cukup ambil mentahannya dulu
    $password            = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if ($password !== $konfirmasi_password) {
        header("Location: ../auth/register?action=passwordnotsame&status=warning&username=" . urlencode($username) . '&nama_user=' . urlencode($nama_user));
        exit;
    }

    // 3. Cek ketersediaan username dengan Prepared Statements
    $stmt = $koneksi->prepare("SELECT id_user FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        header("Location: ../auth/register?action=userexist&status=warning&nama_user=" . urlencode($nama_user));
        exit;
    }

    // 4. Hash password dengan Algoritma BCRYPT yang aman
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 5. Buat ID pengguna (USER001, dst)
    $id_userprefix = 'USER';
    $query_last_id  = "SELECT id_user FROM users WHERE id_user LIKE 'USER%' ORDER BY id_user DESC LIMIT 1";
    $result_last_id = mysqli_query($koneksi, $query_last_id);

    if (mysqli_num_rows($result_last_id) > 0) {
        $last_id = mysqli_fetch_array($result_last_id);
        $last_number = (int)substr($last_id['id_user'], strlen($id_userprefix));
        $new_number  = $last_number + 1;
    } else {
        $new_number = 1;
    }
    $id_user = $id_userprefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

    // 6. Simpan pengguna baru dengan Prepared Statements
    $stmt_insert = $koneksi->prepare("INSERT INTO users (username, id_user, nama_user, password) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", $username, $id_user, $nama_user, $hashed_password);
    
    if ($stmt_insert->execute()) {
        header("Location: ../auth/login?action=registered&status=success");
    } else {
        header("Location: ../auth/register");
    }
    exit;
}

// ============================================================
// PENGAMANAN AKSES LANGSUNG (Direct Access Prevention)
// Jika seseorang mencoba membuka file ini langsung dari URL
// tanpa mengirim form (tanpa menekan tombol login atau register),
// maka akan langsung ditendang kembali ke halaman login.
// Ini menghindari blank page atau pesan error dari server.
// ============================================================
header("Location: ../auth/login");
exit;
