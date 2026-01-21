<?php
session_start();

include '../functions/koneksi.php';
// include '../functions/data.php';

/* =====================
   CEK SESSION LOGIN
===================== */
if (empty($_SESSION['sesi_id'])) {
    header('location: ../auth/logout');
    exit;
}

$sesi_id = $_SESSION['sesi_id'];

// Ambil data user
$query = "SELECT * FROM users WHERE id_user = '$sesi_id'";
$sql   = mysqli_query($koneksi, $query);
$users = mysqli_fetch_assoc($sql);

$sesi_nama = $users['nama_user'] ?? '';
$sesi_img  = $users['img_user'] ?? '';

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title><?= ucfirst($page); ?> - Panel Admin Sistem Deteksi</title>

    <link rel="shortcut icon" href="assets/logo.png">
    <?php include 'pages/css.php'; ?>
</head>

<body>
    <script src="assets/static/js/initTheme.js"></script>

    <div id="app">

        <!-- ================= SIDEBAR ================= -->
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo text-center">
                            <img src="assets/logo.png" width="110">
                        </div>
                        <div class="theme-toggle d-flex gap-2  align-items-center mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                role="img" class="iconify iconify--system-uicons" width="20" height="20"
                                preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                                <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                        opacity=".3"></path>
                                    <g transform="translate(-210 -1)">
                                        <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                        <circle cx="220.5" cy="11.5" r="4"></circle>
                                        <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                                    </g>
                                </g>
                            </svg>
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                                <label class="form-check-label"></label>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                                </path>
                            </svg>
                        </div>
                        <div class="sidebar-toggler  x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu Utama</li>

                        <li class="sidebar-item">
                            <a href="admin" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class="sidebar-link">
                                <i class="bi bi-cpu-fill"></i>
                                <span>Deteksi Penyakit</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="?page=mulai deteksi">Mulai Deteksi</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="?page=riwayat deteksi">Riwayat Deteksi</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="?page=registrasi" class="sidebar-link">
                                <i class="bi bi-people-fill"></i>
                                <span>Registrasi Pengguna Lain</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="?page=profile" class="sidebar-link">
                                <i class="bi bi-person-circle"></i>
                                <span>Profil Saya</span>
                            </a>
                        </li>

                        <!-- LOGOUT (ICON LEBIH TEBAL) -->
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link fw-bold"
                                data-bs-toggle="modal" data-bs-target="#modal-logout">
                                <i class="bi bi-box-arrow-right fs-5"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ================= MAIN ================= -->
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-xl-none d-block">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <?php
            switch ($page) {
                case 'registrasi':
                    include 'pages/registrasi_user.php';
                    break;
                case 'data user':
                    include 'pages/data_user.php';
                    break;
                case 'mulai deteksi':
                    include 'pages/mulai_deteksi.php';
                    break;
                case 'riwayat deteksi':
                    include 'pages/riwayat_deteksi.php';
                    break;
                case 'profile':
                    include 'pages/profile.php';
                    break;
                default:
                    include 'pages/dashboard.php';
            }
            ?>

            <footer>
                <div class="container">
                    <div class="footer clearfix mb-0 text-muted">
                        <div class="float-start">
                            <p>
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> &copy; Sistem Deteksi Penyakit Tanaman Padi
                            </p>
                        </div>
                        <div class="float-end">
                            <p>
                                Dikembangkan oleh
                                <a href="https://www.instagram.com/lulukaulani/" target="_blank">
                                    Luluk Auliani
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <!-- ================= MODAL LOGOUT ================= -->
    <div class="modal fade" id="modal-logout" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin keluar dari sistem?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="../auth/logout" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'pages/js.php'; ?>
</body>

</html>