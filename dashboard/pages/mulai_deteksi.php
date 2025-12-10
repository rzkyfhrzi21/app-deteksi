<?php

// Ambil hasil deteksi dari session (kalau ada), lalu hapus supaya tidak tampil terus
$hasil = $_SESSION['hasil_deteksi'] ?? null;
unset($_SESSION['hasil_deteksi']);
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Deteksi Penyakit Daun Padi</h3>
                <p class="text-subtitle text-muted">
                    Upload foto daun padi untuk dideteksi secara otomatis menggunakan model CNN.
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin">Dashboard</a></li>
                        <li class="breadcrumb-item active text-capitalize" aria-current="page">
                            <?= htmlspecialchars($page ?? 'mulai deteksi'); ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- FORM UPLOAD GAMBAR -->
    <section id="multiple-column-form">
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Form Upload Gambar Daun Padi</h4>
                    </div>

                    <form action="../functions/function_deteksi.php"
                        method="post"
                        enctype="multipart/form-data"
                        class="form"
                        data-parsley-validate>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row">

                                    <!-- Input File Gambar -->
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mandatory">
                                            <label for="gambar_daun" class="form-label">
                                                Pilih Gambar Daun Padi
                                            </label>
                                            <input
                                                type="file"
                                                id="gambar_daun"
                                                class="form-control"
                                                name="gambar_daun"
                                                accept="image/*"
                                                data-parsley-required="true" />
                                            <small class="text-muted">
                                                Format: JPG / JPEG / PNG. Maksimal 2MB.
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Catatan Opsional -->
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="catatan" class="form-label">
                                                Catatan (opsional)
                                            </label>
                                            <textarea
                                                rows="3"
                                                id="catatan"
                                                class="form-control"
                                                name="catatan"
                                                placeholder="Contoh: lokasi sawah, tanggal foto, varietas padi, dsb."></textarea>
                                        </div>
                                    </div>

                                </div>

                                <!-- Tombol Submit -->
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit"
                                            name="btn_upload_daun"
                                            class="btn btn-primary me-1 mb-1">
                                            Upload &amp; Deteksi
                                        </button>
                                        <button type="reset"
                                            class="btn btn-light-secondary me-1 mb-1">
                                            Reset
                                        </button>
                                    </div>
                                </div>

                                <!-- HASIL DETEKSI (JIKA ADA) -->
                                <?php if ($hasil): ?>
                                    <hr>

                                    <?php
                                    // Tentukan tipe alert (sukses / error)
                                    $isError = ($hasil['label'] === 'Error') || !empty($hasil['message']);
                                    ?>

                                    <?php if ($isError): ?>
                                        <div class="alert alert-danger">
                                            <strong>Terjadi kesalahan:</strong>
                                            <?= htmlspecialchars($hasil['message'] ?? 'Tidak diketahui.'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success">
                                            Deteksi berhasil dilakukan.
                                        </div>
                                    <?php endif; ?>

                                    <h5>Hasil Deteksi</h5>

                                    <!-- Gambar hasil upload -->
                                    <?php if (!empty($hasil['file_public'])): ?>
                                        <div class="mb-3 text-center">
                                            <img src="<?= htmlspecialchars($hasil['file_public']); ?>"
                                                alt="Gambar daun padi"
                                                class="d-block mx-auto"
                                                style="max-width: 60%; height: auto; border-radius: 8px;">
                                        </div>
                                    <?php endif; ?>

                                    <!-- Detail hasil CNN -->
                                    <p>
                                        <strong>Penyakit terdeteksi:</strong>
                                        <?= htmlspecialchars($hasil['label'] ?? '-'); ?>
                                    </p>
                                    <p>
                                        <strong>Confidence:</strong>
                                        <?php
                                        if (isset($hasil['confidence'])) {
                                            echo round($hasil['confidence'] * 100, 2) . '%';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <strong>Waktu deteksi:</strong>
                                        <?= htmlspecialchars($hasil['waktu'] ?? '-'); ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>