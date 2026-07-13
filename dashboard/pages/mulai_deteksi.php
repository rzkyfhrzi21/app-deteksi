<?php

// ============================================================
// FILE: mulai_deteksi.php (halaman utama fitur deteksi)
// TUJUAN: Menampilkan form upload gambar daun padi dan hasil deteksinya
//
// Ibarat "meja layanan lab" — pengguna menyerahkan foto daun padi,
// sistem mengirimkannya ke API Flask untuk dianalisis oleh model AI,
// lalu menampilkan hasilnya langsung di halaman yang sama.
//
// ALUR KERJA HALAMAN INI:
// (1) Ambil hasil deteksi dari SESSION (jika ada dari proses sebelumnya)
// (2) Tampilkan form upload gambar + field catatan opsional
// (3) Saat tombol "Upload & Deteksi" diklik:
//     → Data dikirim ke function_deteksi.php via POST (multipart)
//     → function_deteksi.php memproses, menyimpan ke DB, dan menyimpan hasil ke SESSION
//     → Halaman ini di-reload dan menampilkan hasil dari SESSION
// (4) Setelah hasil ditampilkan, SESSION dihapus agar tidak tampil terus saat refresh
// ============================================================

// (1) Ambil hasil deteksi dari session (kalau ada), lalu hapus supaya tidak tampil terus
$hasil = $_SESSION['hasil_deteksi'] ?? null;
unset($_SESSION['hasil_deteksi']); // Hapus dari session setelah dibaca agar tidak muncul lagi saat refresh
?>

<!-- ======================================================
     JUDUL HALAMAN & BREADCRUMB NAVIGASI
====================================================== -->
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Deteksi Penyakit Daun Padi</h3>
                <p class="text-subtitle text-muted">
                    Upload foto daun padi untuk dideteksi secara otomatis menggunakan model CNN.
                    <br><br>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btnPingRender" onclick="pingRender()">
                        <i class="bi bi-lightning-charge"></i> Bangunkan Server AI (Render)
                    </button>
                    <span id="pingResult" class="ms-2" style="font-size: 0.9em;"></span>
                </p>

                <script>
                function pingRender() {
                    const btn = document.getElementById('btnPingRender');
                    const res = document.getElementById('pingResult');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Membangunkan...';
                    res.innerHTML = '<span class="text-warning">Harap tunggu, server Render gratis butuh 1-2 menit untuk bangun dari tidur...</span>';
                    
                    fetch('https://app-deteksi.onrender.com/')
                        .then(response => {
                            res.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Server AI Aktif & Siap Menerima Gambar!</span>';
                        })
                        .catch(error => {
                            res.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Gagal menghubungi server. (Bisa jadi masalah CORS, tapi server mungkin sudah bangun)</span>';
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Bangunkan Server AI (Render)';
                        });
                }
                </script>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <!-- Navigasi roti remah (breadcrumb): Dashboard > Mulai Deteksi -->
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

    <!-- ======================================================
         FORM UPLOAD GAMBAR DAUN PADI

         Form ini digunakan pengguna untuk mengirimkan foto daun padi
         ke server agar bisa dianalisis oleh model AI.

         Kolom Isian:
         - gambar_daun (name="gambar_daun") : Wajib. File gambar JPG/JPEG/PNG, maks 2MB.
                                              Disimpan di folder uploads/deteksi/ setelah diproses.
         - catatan (name="catatan")         : Opsional. Textarea bebas untuk keterangan tambahan
                                              (contoh: lokasi sawah, tanggal foto, varietas padi)

         Tombol:
         - "Upload & Deteksi" (name="btn_upload_daun") : Mengirim form ke function_deteksi.php
         - "Reset"                                     : Mengosongkan semua isian form

         Form dikirim ke: ../functions/function_deteksi.php
         Method: POST | enctype: multipart/form-data (wajib karena ada upload file)
    ====================================================== -->
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
                        data-parsley-validate> <!-- data-parsley-validate = validasi form di sisi browser sebelum dikirim -->
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row">

                                    <!-- ======================================================
                                         INPUT FILE GAMBAR DAUN PADI
                                         name="gambar_daun" → diterima di $_FILES['gambar_daun'] di function_deteksi.php
                                         Hanya menerima format gambar (accept="image/*")
                                         File akan disimpan di folder: uploads/deteksi/
                                    ====================================================== -->
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
                                                data-parsley-required="true" /> <!-- Wajib diisi — Parsley akan tampilkan error jika kosong -->
                                            <small class="text-muted">
                                                Format: JPG / JPEG / PNG. Maksimal 2MB.
                                            </small>
                                        </div>
                                    </div>

                                    <!-- ======================================================
                                         CATATAN OPSIONAL
                                         name="catatan" → diterima di $_POST['catatan'] di function_deteksi.php
                                         Disimpan ke kolom 'catatan' di tabel hasil_deteksi di database
                                    ====================================================== -->
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

                                <!-- ======================================================
                                     TOMBOL AKSI FORM

                                     - Tombol "Upload & Deteksi" (name="btn_upload_daun"):
                                       Mengirim form ke function_deteksi.php untuk diproses.
                                       Di sana, gambar akan dikirim ke API Flask dan hasilnya disimpan ke DB.

                                     - Tombol "Reset":
                                       Mengosongkan semua isian form (tidak mengirim data ke server)
                                ====================================================== -->
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

                                <!-- ======================================================
                                     AREA HASIL DETEKSI (MUNCUL SETELAH PROSES SELESAI)

                                     Blok ini HANYA tampil jika variabel $hasil tidak kosong.
                                     $hasil diisi oleh function_deteksi.php melalui SESSION['hasil_deteksi']
                                     setelah API Flask berhasil mengembalikan prediksi.

                                     Isi $hasil (array):
                                     - $hasil['label']         → label asli dari model (contoh: 'Bacterialblight')
                                     - $hasil['label_display'] → nama rapi untuk UI (contoh: 'Bacterial Blight')
                                     - $hasil['confidence']    → angka kepercayaan model (0.0 s.d. 1.0)
                                     - $hasil['file_public']   → path relatif gambar untuk ditampilkan di <img>
                                                                  (file ada di folder uploads/deteksi/)
                                     - $hasil['message']       → pesan error jika deteksi gagal
                                     - $hasil['waktu']         → waktu proses deteksi (format Y-m-d H:i:s)
                                ====================================================== -->
                                <?php if ($hasil): ?>
                                    <hr>

                                    <?php
                                    // Tentukan tipe alert (sukses / error) berdasarkan label dan pesan error
                                    $isError = ($hasil['label'] === 'Error') || !empty($hasil['message']);
                                    ?>

                                    <?php if ($isError): ?>
                                        <!-- Kotak merah: Jika deteksi gagal (API error, file tidak valid, dsb.) -->
                                        <div class="alert alert-danger">
                                            <strong>Terjadi kesalahan:</strong>
                                            <?= htmlspecialchars($hasil['message'] ?? 'Tidak diketahui.'); ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Kotak hijau: Jika deteksi berhasil -->
                                        <div class="alert alert-success">
                                            Deteksi berhasil dilakukan.
                                        </div>
                                    <?php endif; ?>

                                    <h5>Hasil Deteksi</h5>

                                    <!-- ======================================================
                                         TAMPILAN GAMBAR YANG SUDAH DIUPLOAD
                                         Diambil dari $hasil['file_public']
                                         Path relatif dari sudut pandang folder dashboard/
                                         File fisiknya ada di folder: uploads/deteksi/
                                    ====================================================== -->
                                    <?php if (!empty($hasil['file_public'])): ?>
                                        <div class="mb-3 text-center">
                                            <img src="<?= htmlspecialchars($hasil['file_public']); ?>"
                                                alt="Gambar daun padi"
                                                class="d-block mx-auto"
                                                style="max-width: 60%; height: auto; border-radius: 8px;">
                                        </div>
                                    <?php endif; ?>

                                    <!-- ======================================================
                                         DETAIL HASIL PREDIKSI MODEL CNN
                                    ====================================================== -->
                                    <p>
                                        <strong>Penyakit terdeteksi:</strong>
                                        <?= htmlspecialchars($hasil['label_display'] ?? $hasil['label'] ?? '-'); ?>
                                        <!-- Tampilkan label rapi (label_display), jika tidak ada tampilkan label asli -->
                                    </p>
                                    <p>
                                        <strong>Confidence:</strong>
                                        <?php
                                        // Ubah angka desimal (0.0-1.0) menjadi persen (0%-100%)
                                        // Contoh: 0.9345 → "93.45%"
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
                                        <!-- Waktu proses deteksi dalam format Y-m-d H:i:s, contoh: 2025-07-05 14:30:22 -->
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