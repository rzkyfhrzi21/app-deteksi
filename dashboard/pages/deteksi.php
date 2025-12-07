<?php

if ($_SESSION['sesi_role'] !== 'admin') {
    return;
}

?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Deteksi Penyakit Daun Padi</h3>
                <p class="text-subtitle text-muted">
                    Upload foto daun padi untuk dideteksi secara otomatis.
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin">Dashboard</a></li>
                        <li class="breadcrumb-item active text-capitalize" aria-current="page">
                            <?= $page; ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section id="multiple-column-form">
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Form Upload Gambar</h4>
                    </div>

                    <?php
                    // ambil hasil deteksi dari session (kalau ada)
                    $hasil = $_SESSION['hasil_deteksi'] ?? null;
                    unset($_SESSION['hasil_deteksi']);
                    ?>

                    <form action="../functions/deteksi.php"
                        method="post"
                        enctype="multipart/form-data"
                        class="form"
                        data-parsley-validate>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row">

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
                                                placeholder="Contoh: lokasi sawah, tanggal foto, dsb."></textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit"
                                            name="btn_upload_daun"
                                            class="btn btn-primary me-1 mb-1">
                                            Upload & Deteksi
                                        </button>
                                        <button type="reset"
                                            class="btn btn-light-secondary me-1 mb-1">
                                            Reset
                                        </button>
                                    </div>
                                </div>

                                <?php if ($hasil): ?>
                                    <hr>
                                    <?php if ($hasil && !empty($hasil['message'])): ?>
                                        <div class="alert alert-danger">
                                            <?= htmlspecialchars($hasil['message']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <h5>Hasil Deteksi</h5>
                                    <?php if (!empty($hasil['file_public'])): ?>
                                        <img src="<?= htmlspecialchars($hasil['file_public']) ?>"
                                            alt="Gambar daun padi"
                                            class="img-fluid mb-3" style="max-width: 300px;">
                                    <?php endif; ?>

                                    <p><strong>Penyakit:</strong> <?= htmlspecialchars($hasil['label'] ?? '-') ?></p>
                                    <p><strong>Confidence:</strong>
                                        <?= isset($hasil['confidence']) ? round($hasil['confidence'] * 100, 2) . '%' : '-' ?>
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