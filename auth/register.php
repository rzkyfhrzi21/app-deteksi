<?php
session_start();

$usernameLogin  = isset($_GET['username']) ? $_GET['username'] : '';
$nama_userLogin = isset($_GET['nama_user']) ? $_GET['nama_user'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>Registrasi - Sistem Deteksi</title>
    <link rel="shortcut icon" href="../dashboard/assets/logo.png" type="image/x-icon">

    <!-- ================= CORE TEMPLATE (LOCAL) ================= -->
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/auth.css">

    <!-- ================= SWEETALERT2 CSS (CDN) ================= -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
            margin: 0;
        }

        #auth {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 420px;
            width: 100%;
        }

        p {
            font-size: 16px;
        }

        label {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <!-- INIT THEME (LOCAL TEMPLATE) -->
    <script src="../dashboard/assets/static/js/initTheme.js"></script>

    <div id="app">
        <div class="content-wrapper container">
            <div class="row h-100">
                <div class="card mt-5">
                    <div class="card-header">
                        <h2 class="auth-title text-success">Registrasi Akun</h2>
                        <p class="auth-subtitle mb-2">
                            Daftar untuk menggunakan Sistem Deteksi Penyakit Tanaman Padi 🌱
                        </p>
                    </div>

                    <div class="card-body">
                        <form class="form"
                            data-parsley-validate
                            action="../functions/function_auth.php"
                            method="post"
                            autocomplete="off">

                            <!-- Nama Lengkap -->
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

                            <!-- Username -->
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

                            <!-- Password -->
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

                            <!-- Konfirmasi Password -->
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

                            <!-- Role (opsional) -->
                            <input type="hidden" name="role" value="admin">

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

    <!-- ================= JS CDN ================= -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <!-- Parsley -->
    <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>
    <script src="../dashboard/assets/static/js/pages/parsley.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ================= SWEETALERT GLOBAL ================= -->
    <?php include '../dashboard/pages/sweetalert.php'; ?>

</body>

</html>