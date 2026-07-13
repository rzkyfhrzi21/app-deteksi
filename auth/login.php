<?php
session_start();
// ============================================================
// FILE: login.php (halaman antarmuka masuk)
// TUJUAN: Menampilkan formulir login untuk pengguna agar bisa
//         masuk ke dalam sistem deteksi penyakit daun padi.
//
// Ibarat "pintu masuk gedung" — pengguna harus memasukkan
// username dan password yang benar sebelum bisa mengakses
// dashboard. Jika salah, pintu tidak akan terbuka.
//
// ALUR KERJA HALAMAN INI:
// (1) PHP: cek apakah ada username dari URL (?username=xxx)
//     → Fitur ini mengisi otomatis kolom username jika pengguna
//       baru saja gagal login, agar tidak perlu mengetik ulang
// (2) HTML: tampilkan formulir username + password
// (3) JavaScript: validasi form di sisi browser (ParsleyJS)
//     sebelum data dikirim ke server
// (4) Saat tombol "Masuk" diklik:
//     → Data POST dikirim ke ../functions/function_auth.php
//     → function_auth.php memeriksa ke database dan memutuskan
//       apakah login berhasil atau gagal
// (5) SweetAlert2: tampilkan popup notifikasi berdasarkan
//     parameter ?action=login&status=error yang dikembalikan
//     dari function_auth.php jika login gagal
//
// DATA YANG DIKIRIM KE function_auth.php (method POST):
//   - username (name="username") → diperiksa di tabel 'users'
//   - password (name="password") → di-hash MD5 lalu diperiksa
//   - role     (name="role")     → hidden input, nilai "admin"
//   - btn_login                  → penanda tombol yang diklik
// ============================================================

// (1) Ambil username dari URL jika ada — untuk mengisi otomatis kolom username
//     Contoh URL: ../auth/login?username=rizky&action=login&status=error
$usernameLogin = isset($_GET['username']) ? $_GET['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow"> <!-- Larang mesin pencari mengindeks halaman login -->

    <title>Login - Sistem Deteksi</title>

    <link rel="shortcut icon" href="../dashboard/assets/logo.png" type="image/x-icon">

    <!-- ======================================================
         STYLESHEET (CSS)

         Urutan loading:
         1. app.css      → style utama template Mazer (mode terang)
         2. app-dark.css → override untuk mode gelap (dark mode)
         3. auth.css     → style khusus halaman login & register
            (kotak kartu di tengah layar, font lebih besar, dsb.)
    ====================================================== -->
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/auth.css">

    <!-- ======================================================
         SWEETALERT2 CSS (CDN)
         Library untuk menampilkan popup notifikasi yang cantik
         (lebih menarik dari alert() bawaan browser)
    ====================================================== -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- ======================================================
         STYLE TAMBAHAN (OVERRIDE)
         Penyesuaian tampilan khusus halaman login agar kotak
         formulir tampak lebih rapi dan terpusat di layar.
    ====================================================== -->
    <style>
        body {
            background-size: cover;       /* Gambar background menutupi seluruh layar */
            background-position: center;  /* Pusatkan gambar background */
            display: flex;
            justify-content: center;      /* Pusatkan konten secara horizontal */
            align-items: center;          /* Pusatkan konten secara vertikal */
            height: auto;
            margin: 0;
        }

        #auth {
            background-color: rgba(255, 255, 255, 0.9); /* Kotak putih semi-transparan */
            border-radius: 15px;                        /* Sudut membulat */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Bayangan halus */
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }

        p     { font-size: 16px; }
        label { font-size: 14px; }
    </style>
</head>

<body>

    <!-- Script ini dijalankan SEBELUM halaman tampil untuk menghindari "flash" -->
    <!-- (layar putih sekejap sebelum dark mode aktif) -->
    <script src="../dashboard/assets/static/js/initTheme.js"></script>

    <div id="app">
        <div class="content-wrapper container">
            <div class="row h-100">

                <!-- ======================================================
                     KARTU FORMULIR LOGIN
                     Kotak putih di tengah halaman yang berisi:
                     - Judul "Log In Sistem"
                     - Kolom username
                     - Kolom password
                     - Tombol "Masuk"
                     - Link ke halaman Daftar
                ====================================================== -->
                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="auth-title text-success">Log In Sistem</h2>
                        <p class="auth-subtitle mb-2">
                            Hi, Selamat datang di Sistem Deteksi Penyakit Padi 🌱
                        </p>
                    </div>

                    <div class="card-body">
                        <!-- ======================================================
                             FORMULIR LOGIN
                             action  → dikirim ke function_auth.php untuk diproses
                             method  → POST (data tidak terlihat di URL)
                             data-parsley-validate → aktifkan validasi ParsleyJS di browser
                        ====================================================== -->
                        <form class="form"
                            data-parsley-validate
                            action="../functions/function_auth.php"
                            method="post"
                            autocomplete="off">

                            <!-- ======================================================
                                 KOLOM USERNAME
                                 name="username"  → diterima di $_POST['username'] di function_auth.php
                                 value            → diisi otomatis dari $usernameLogin (URL parameter)
                                                    agar pengguna tidak perlu mengetik ulang jika gagal login
                                 minlength="5"    → validasi ParsleyJS: minimal 5 karakter
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="position-relative">
                                    <input type="text"
                                        name="username"
                                        class="form-control form-control-xl"
                                        placeholder="Masukkan username"
                                        value="<?= htmlspecialchars($usernameLogin); ?>"
                                        id="username"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-person"></i> <!-- Ikon orang di sebelah kiri kolom -->
                                    </div>
                                </div>
                            </div>

                            <!-- ======================================================
                                 KOLOM PASSWORD
                                 name="password"  → diterima di $_POST['password'] di function_auth.php
                                 type="password"  → teks disembunyikan (tampil sebagai ***)
                                 minlength="5"    → validasi ParsleyJS: minimal 5 karakter
                            ====================================================== -->
                            <div class="form-group position-relative has-icon-left mb-3">
                                <label for="password" class="form-label">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="password"
                                        name="password"
                                        class="form-control form-control-xl"
                                        placeholder="*****"
                                        id="password"
                                        data-parsley-required="true"
                                        minlength="5">
                                    <div class="form-control-icon">
                                        <i class="bi bi-shield-lock"></i> <!-- Ikon gembok di sebelah kiri kolom -->
                                    </div>
                                </div>
                            </div>

                            <!-- Input tersembunyi: role pengguna (selalu "admin" untuk sistem ini) -->
                            <input type="hidden" name="role" value="admin">

                            <!-- ======================================================
                                 TOMBOL MASUK
                                 name="btn_login" → dipakai function_auth.php untuk mendeteksi
                                                    bahwa form ini adalah form LOGIN (bukan register)
                                 type="submit"    → mengirim semua data form ke server
                            ====================================================== -->
                            <button name="btn_login"
                                type="submit"
                                class="btn btn-success btn-block btn-lg shadow-lg mt-2">
                                Masuk
                            </button>
                        </form>

                        <div class="text-center mt-3 text-lg fs-4">
                            <p class="text-gray-600">
                                Belum memiliki akun?
                                <a href="register" class="font-bold text-success">Daftar</a>.
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

         Urutan loading sangat penting — jangan diubah:
         1. jQuery     → dibutuhkan oleh Parsley & SweetAlert
         2. ParsleyJS  → validasi form sebelum dikirim ke server
         3. parsley.js → konfigurasi lokal Parsley (pesan error Bahasa Indonesia)
         4. SweetAlert2 → popup notifikasi cantik
    ====================================================== -->

    <!-- jQuery — library JavaScript paling dasar, dibutuhkan yang lain -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <!-- ParsleyJS — validasi form: cek apakah kolom sudah diisi sebelum dikirim -->
    <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>
    <script src="../dashboard/assets/static/js/pages/parsley.js"></script>

    <!-- SweetAlert2 — popup notifikasi (menggantikan alert() yang kaku) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ======================================================
         SWEETALERT NOTIFIKASI (dari sweetalert.php)
         File ini membaca parameter URL (?action=...&status=...)
         yang dikirim oleh function_auth.php setelah proses login,
         lalu menampilkan popup yang sesuai.

         Contoh:
         - ?action=login&status=error   → popup merah "Username/password salah"
         - ?action=registered&status=success → popup hijau "Berhasil mendaftar"
    ====================================================== -->
    <?php include '../dashboard/pages/sweetalert.php'; ?>

</body>

</html>