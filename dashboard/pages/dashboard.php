<?php
// ============================================================
// FILE: dashboard.php (halaman beranda setelah login)
// TUJUAN: Menampilkan ringkasan statistik sistem deteksi penyakit padi
//
// Halaman ini adalah "halaman depan" setelah pengguna berhasil login.
// Menampilkan 4 kotak angka statistik dan informasi profil pengguna.
//
// Data yang ditampilkan bersumber dari:
// - Tabel 'rekam_akses_web'  → statistik pengunjung
// - Tabel 'users'            → statistik pengguna
// - Tabel 'hasil_deteksi'    → statistik deteksi penyakit
// - $_SESSION                → data pengguna yang sedang login
// ============================================================

include '../functions/data.php'; // Jalankan query statistik dari data.php, hasilnya tersimpan di variabel-variabel di bawah
?>

<!-- ======================================================
     JUDUL HALAMAN DASHBOARD
====================================================== -->
<div class="page-heading">
    <h3>Statistik Sistem Deteksi</h3>
</div>

<div class="page-content">
    <section class="row">

        <!-- ======================================================
             BARIS KARTU STATISTIK UTAMA (4 KOTAK)

             Menampilkan 4 angka ringkasan penting dari sistem:
             1. Total Pengunjung  → dari tabel rekam_akses_web (COUNT semua baris)
             2. Total Pengguna    → dari tabel users (COUNT semua baris)
             3. Pengguna Baru     → dari tabel users (COUNT bulan ini)
             4. Waktu Sekarang    → waktu server saat ini (format H:i)
        ====================================================== -->
        <div class="col-12 col-lg-12">
            <div class="row">

                <!-- ======================================================
                     KARTU 1: TOTAL PENGUNJUNG
                     Menampilkan $totalPengunjung → total COUNT dari tabel rekam_akses_web
                     (Setiap kali pengguna login, satu baris baru ditambahkan ke tabel ini)
                ====================================================== -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon purple mb-0">
                                    <i class="iconly-boldShow"></i> <!-- Ikon mata / pengunjung -->
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-1">Total Pengunjung</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPengunjung ?></h6> <!-- Angka dari data.php -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================
                     KARTU 2: TOTAL PENGGUNA TERDAFTAR
                     Menampilkan $totalPengguna → total COUNT semua baris di tabel users
                     (Setiap akun yang sudah mendaftar akan dihitung di sini)
                ====================================================== -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon blue mb-0">
                                    <i class="iconly-boldProfile"></i> <!-- Ikon profil orang -->
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-1">Total Pengguna</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPengguna ?></h6> <!-- Angka dari data.php -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================
                     KARTU 3: PENGGUNA BARU BULAN INI
                     Menampilkan $totalPenggunaBaru → COUNT dari tabel users
                     dengan filter MONTH(created_at) = bulan sekarang
                ====================================================== -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon green mb-0">
                                    <i class="iconly-boldAdd-User"></i> <!-- Ikon tambah pengguna -->
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-1">Pengguna Baru</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPenggunaBaru ?></h6> <!-- Angka dari data.php -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================
                     KARTU 4: WAKTU SEKARANG
                     Menampilkan $pukul → waktu server saat ini (format H:i, contoh: 14:30)
                     Berguna sebagai referensi waktu ketika melihat laporan deteksi
                ====================================================== -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon red mb-0">
                                    <i class="iconly-boldTime-Circle"></i> <!-- Ikon jam -->
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold mb-1">Waktu Sekarang</h6>
                                    <h6 class="font-extrabold mb-0"><?= $pukul ?> WIB</h6> <!-- Jam dari data.php (format H:i) -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ======================================================
                 BARIS BAWAH: PROFIL PENGGUNA + STATISTIK DETEKSI
            ====================================================== -->
            <div class="row">

                <!-- ======================================================
                     KARTU PROFIL PENGGUNA YANG SEDANG LOGIN

                     Menampilkan foto dan identitas pengguna yang sedang aktif.
                     Data diambil dari $_SESSION (diisi saat proses login):
                     - $sesi_img  → nama file foto profil (kolom img_user dari tabel users)
                                    disimpan di folder: dashboard/assets/profile/
                                    Jika kosong, tampilkan foto default dari: dashboard/assets/static/images/faces/1.jpg
                     - $sesi_nama → nama lengkap pengguna (kolom nama_user dari tabel users)
                     - $sesi_id   → ID unik pengguna (kolom id_user, contoh: USER001)
                ====================================================== -->
                <div class="col-12 col-lg-3">
                    <div class="card">
                        <div class="card-body py-3 px-4 pb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg">
                                    <!-- Tampilkan foto profil dari folder assets/profile/, atau foto default jika belum diisi -->
                                    <img src="assets/<?= empty($sesi_img)
                                                            ? 'static/images/faces/1.jpg'
                                                            : 'profile/' . htmlspecialchars($sesi_img) ?>">
                                </div>
                                <div class="ms-3">
                                    <h5 class="font-bold"><?= htmlspecialchars($sesi_nama) ?></h5> <!-- Nama lengkap dari sesi -->
                                    <h6 class="text-muted mb-0">@<?= htmlspecialchars($sesi_id) ?></h6> <!-- ID pengguna dari sesi -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================
                     KARTU STATISTIK DETEKSI PENYAKIT

                     Menampilkan 3 angka statistik dari tabel hasil_deteksi:
                     - $totalDeteksi    → COUNT semua baris di tabel hasil_deteksi
                     - $deteksiHariIni  → COUNT baris dengan DATE(created_at) = hari ini
                     - $avgConfidence   → AVG(confidence) dari semua baris hasil_deteksi
                                          (nilai 0.0 s.d. 1.0, contoh: 0.8743 = 87.43% rata-rata keyakinan model)
                ====================================================== -->
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Statistik Deteksi</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <!-- Baris 1: Total semua deteksi yang pernah dilakukan -->
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Deteksi</span>
                                    <strong><?= $totalDeteksi ?></strong>
                                </li>
                                <!-- Baris 2: Jumlah deteksi yang dilakukan hari ini saja -->
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Deteksi Hari Ini</span>
                                    <strong><?= $deteksiHariIni ?></strong>
                                </li>
                                <!-- Baris 3: Rata-rata confidence/keyakinan model dari semua deteksi -->
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Rata-rata Confidence</span>
                                    <strong><?= $avgConfidence ?></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>