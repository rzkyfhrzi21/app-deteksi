<?php
session_start();
// ============================================================
// FILE: register.php (halaman antarmuka pendaftaran akun baru)
// TUJUAN: Menampilkan formulir pendaftaran agar calon pengguna
//         bisa membuat akun baru untuk mengakses sistem deteksi.
//
// Ibarat "meja resepsionis pendaftaran" — pengguna mengisi
// formulir nama, username, dan password, lalu diserahkan ke
// petugas (function_auth.php) untuk diproses dan disimpan.
//
// ALUR KERJA HALAMAN INI:
// (1) PHP: cek apakah ada data dari URL (username, nama_user)
//     → Fitur ini mengisi otomatis kolom jika pendaftaran
//       gagal (username sudah dipakai), agar tidak mengetik ulang
// (2) HTML: tampilkan formulir 4 kolom:
//     - Nama Lengkap
//     - Username
//     - Password
//     - Konfirmasi Password
// (3) JavaScript ParsleyJS: validasi di sisi browser
//     (cek apakah kolom sudah diisi, minimal 5 karakter, dsb.)
// (4) Saat tombol "Daftar" diklik:
//     → Data POST dikirim ke ../functions/function_auth.php
//     → function_auth.php memvalidasi, mengecek keunikan username,
//       dan menyimpan akun baru ke tabel 'users' di database
// (5) SweetAlert2: popup notifikasi berdasarkan status yang
//     dikembalikan dari function_auth.php
//
// DATA YANG DIKIRIM KE function_auth.php (method POST):
//   - nama_user          → kolom nama_user di tabel 'users'
//   - username           → kolom username di tabel 'users'
//   - password           → akan di-hash MD5 sebelum disimpan
//   - konfirmasi_password → harus sama dengan password
//   - btn_register       → penanda bahwa ini form REGISTRASI
// ============================================================

// (1) Ambil data dari URL jika ada (dikirim kembali saat registrasi gagal)
//     Contoh URL: ../auth/register?action=userexist&status=warning&username=rizky&nama_user=Rizky
$usernameLogin  = isset($_GET['username'])  ? $_GET['username']  : '';
$nama_userLogin = isset($_GET['nama_user']) ? $_GET['nama_user'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow"> <!-- Larang mesin pencari mengindeks halaman ini -->

    <title>Registrasi - Sistem Deteksi</title>
    <link rel="shortcut icon" href="../dashboard/assets/logo.png" type="image/x-icon">

    <!-- ======================================================
         STYLESHEET (CSS)
         Sama seperti login.php — template Mazer + style auth khusus
    ====================================================== -->
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/auth.css">

    <!-- SweetAlert2 CSS — untuk popup notifikasi hasil registrasi -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center; /* Pusatkan form secara horizontal */
            align-items: center;     /* Pusatkan form secara vertikal */
            height: auto;
            margin: 0;
        }

        #auth {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 420px; /* Sedikit lebih lebar dari login karena ada lebih banyak kolom -->
            width: 100%;
        }

        p     { font-size: 16px; }
        label { font-size: 14px; }
    </style>
</head>

<body>

    <!-- Script inisialisasi tema (dark/light) — dijalankan sebelum halaman tampil -->
    <script src="../dashboard/assets/static/js/initTheme.js"></script>

    <div id="app">
        <div class="content-wrapper container">
            <div class="row h-100">

                <!-- ======================================================
                     KARTU FORMULIR REGISTRASI
                     Kotak putih di tengah halaman berisi:
                     - Judul "Registrasi Akun"
                     - 4 kolom isian: nama, username, password, konfirmasi password
                     - Tombol "Daftar"
                     - Link ke halaman Login (untuk yang sudah punya akun)
                ====================================================== -->
                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="auth-title text-success">Registrasi Akun</h2>
                        <p class="auth-subtitle mb-2">
                            Daftar untuk menggunakan Sistem Deteksi Penyakit Tanaman Padi 🌱
                        </p>
                    </div>

                    <div class="card-body">
                        <!-- ======================================================
                             FORMULIR PENDAFTARAN AKUN BARU
                             action → function_auth.php memproses data registrasi
                             method → POST agar data tidak terlihat di URL
                             data-parsley-validate → aktifkan validasi form di browser
                        ====================================================== -->
                        <form class="form"
                            data-parsley-validate
                            action="../functions/function_auth.php"
                            method="post"
                            autocomplete="off">

                            <!-- ======================================================
                                 KOLOM: NAMA LENGKAP
                                 name="nama_user"  → diterima di $_POST['nama_user']
                                                     disimpan ke kolom nama_user di tabel 'users'
                                 value             → diisi otomatis jika registrasi gagal
                                 minlength="5"     → minimal 5 karakter (validasi Parsley)
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label>Nama Lengkap</label>
                                <div class="position-relative">
                                    <input type="text"
                                        name="nama_user"
                                        class="form-control form-control-xl"
                                        placeholder="Masukkan nama lengkap"
                                        value="<?= htmlspecialchars($nama_userLogin); ?>"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-person"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================================================
                                 KOLOM: USERNAME
                                 name="username"   → diterima di $_POST['username']
                                                     disimpan ke kolom username di tabel 'users'
                                                     HARUS UNIK — function_auth.php akan cek ke DB
                                 value             → diisi otomatis jika username sudah dipakai
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label>Username</label>
                                <div class="position-relative">
                                    <input type="text"
                                        name="username"
                                        class="form-control form-control-xl"
                                        placeholder="Masukkan username"
                                        value="<?= htmlspecialchars($usernameLogin); ?>"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================================================
                                 KOLOM: PASSWORD
                                 name="password"   → diterima di $_POST['password']
                                                     akan di-hash MD5 sebelum disimpan ke tabel 'users'
                                 type="password"   → teks tersembunyi (tampil sebagai ***)
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label>Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password"
                                        name="password"
                                        class="form-control form-control-xl"
                                        placeholder="*****"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================================================
                                 KOLOM: KONFIRMASI PASSWORD
                                 name="konfirmasi_password" → harus sama dengan kolom password
                                 Pengecekan kesamaan dilakukan di function_auth.php (sisi server),
                                 bukan di sini (sisi browser) — untuk keamanan lebih terjamin.
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label>Konfirmasi Password</label>
                                <div class="position-relative">
                                    <input type="password"
                                        name="konfirmasi_password"
                                        class="form-control form-control-xl"
                                        placeholder="Ulangi password"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Input tersembunyi: role pengguna (selalu "admin" di sistem ini) -->
                            <input type="hidden" name="role" value="admin">

                            <!-- ======================================================
                                 TOMBOL DAFTAR
                                 name="btn_register" → dipakai function_auth.php untuk
                                                       membedakan ini form REGISTRASI (bukan login)
                            ====================================================== -->
                            <button type="submit"
                                name="btn_register"
                                class="btn btn-success btn-block btn-lg shadow-lg mt-2">
                                Daftar
                            </button>
                        </form>

                        <div class="text-center mt-3 text-lg fs-4">
                            <p class="text-gray-600">
                                Sudah punya akun?
                                <a href="login" class="font-bold text-success">Masuk</a>.
                            </p>
                            <p>© Sistem Deteksi Penyakit Tanaman Padi</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================
         JAVASCRIPT (CDN)
         Urutan loading: jQuery → ParsleyJS → SweetAlert2
    ====================================================== -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <!-- ParsleyJS — validasi form sebelum data dikirim ke server -->
    <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>
    <script src="../dashboard/assets/static/js/pages/parsley.js"></script>

    <!-- SweetAlert2 — popup notifikasi -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ======================================================
         SWEETALERT NOTIFIKASI (dari sweetalert.php)
         Membaca parameter URL dari function_auth.php dan
         menampilkan popup yang sesuai.

         Contoh parameter yang bisa datang:
         - ?action=userexist&status=warning     → popup kuning "Username sudah dipakai"
         - ?action=passwordnotsame&status=warning → popup kuning "Password tidak cocok"
         - ?action=registered&status=success    → popup hijau "Berhasil daftar!"
    ====================================================== -->
    <?php include '../dashboard/pages/sweetalert.php'; ?>

</body>

</html>