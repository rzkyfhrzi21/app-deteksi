<?php
include '../functions/data.php';
?>

<div class="page-heading">
    <h3>Statistik Sistem Deteksi</h3>
</div>

<div class="page-content">
    <section class="row">

        <!-- ===============================
             CARD STATISTIK
        ================================= -->
        <div class="col-12 col-lg-12">
            <div class="row">

                <!-- Total Pengunjung -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="stats-icon purple mb-2">
                                        <i class="iconly-boldShow"></i>
                                    </div>
                                    <h6 class="text-muted font-semibold">Total Pengunjung</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPengunjung ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Pengguna -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                    <h6 class="text-muted font-semibold">Total Pengguna</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPengguna ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengguna Baru -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="stats-icon green mb-2">
                                        <i class="iconly-boldAdd-User"></i>
                                    </div>
                                    <h6 class="text-muted font-semibold">Pengguna Baru (Bulan Ini)</h6>
                                    <h6 class="font-extrabold mb-0"><?= $totalPenggunaBaru ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waktu Sekarang -->
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="stats-icon red mb-2">
                                        <i class="iconly-boldTime-Circle"></i>
                                    </div>
                                    <h6 class="text-muted font-semibold">Waktu Sekarang</h6>
                                    <h6 class="font-extrabold mb-0"><?= $pukul ?> WIB</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ===============================
            STATISTIK DETEKSI
            ================================= -->
            <div class="row">
                <!-- ===============================
                PROFIL USER LOGIN
                ================================= -->
                <div class="col-12 col-lg-3">
                    <div class="card">
                        <div class="card-body py-3 px-4 pb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg">
                                    <img src="assets/<?= empty($sesi_img)
                                                            ? 'static/images/faces/1.jpg'
                                                            : 'profile/' . htmlspecialchars($sesi_img) ?>">
                                </div>
                                <div class="ms-3">
                                    <h5 class="font-bold"><?= htmlspecialchars($sesi_nama) ?></h5>
                                    <h6 class="text-muted mb-0">@<?= htmlspecialchars($sesi_id) ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Statistik Deteksi</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Deteksi</span>
                                    <strong><?= $totalDeteksi ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Deteksi Hari Ini</span>
                                    <strong><?= $deteksiHariIni ?></strong>
                                </li>
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