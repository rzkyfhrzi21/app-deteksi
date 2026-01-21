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

    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="../dashboard/assets/compiled/css/auth.css">
    <link rel="stylesheet" href="../dashboard/assets/extensions/sweetalert2/sweetalert2.min.css">

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
                                <label class="form-label">Nama Lengkap</label>
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
                                <label class="form-label">Username</label>
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
                                <label class="form-label">Password <span class="text-danger">*</span></label>
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
                                <label class="form-label">Konfirmasi Password</label>
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

                            <!-- Jika role tidak dipakai, boleh dihapus -->
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

    <!-- JS -->
    <script src="../dashboard/assets/extensions/jquery/jquery.min.js"></script>
    <script src="../dashboard/assets/extensions/parsleyjs/parsley.min.js"></script>
    <script src="../dashboard/assets/static/js/pages/parsley.js"></script>
    <script src="../dashboard/assets/extensions/sweetalert2/sweetalert2.min.js"></script>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get("status");
        const action = urlParams.get("action");

        if (status === "warning") {
            if (action === "userexist") {
                Swal.fire({
                    icon: "warning",
                    title: "Peringatan!",
                    text: "Username sudah digunakan. Silakan pilih yang lain.",
                    timer: 3000,
                    showConfirmButton: false,
                });
            } else if (action === "passwordnotsame") {
                Swal.fire({
                    icon: "warning",
                    title: "Peringatan!",
                    text: "Password dan konfirmasi tidak sama.",
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        } else if (status === "error") {
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: "Terjadi kesalahan saat registrasi.",
                timer: 3000,
                showConfirmButton: false,
            });
        }
    </script>
</body>

</html>