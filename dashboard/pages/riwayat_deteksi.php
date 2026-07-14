<?php
// ============================================================
// FILE: riwayat_deteksi.php (halaman tabel riwayat)
// TUJUAN: Menampilkan semua riwayat hasil deteksi penyakit daun padi
//         dalam bentuk tabel, lengkap dengan tombol hapus dan modal preview.
//
// Data diambil dari 2 tabel:
// - Tabel 'hasil_deteksi' (alias h): data hasil deteksi
// - Tabel 'users'         (alias u): data nama pengguna
//
// Kolom yang ditampilkan di tabel:
// - h.id_deteksi     → nomor urut baris
// - u.nama_user      → nama pengguna yang melakukan deteksi
// - h.file_path      → path file gambar (contoh: uploads/deteksi/padi_xxx.jpg)
// - h.label_penyakit → nama penyakit asli dari model (contoh: Bacterialblight)
// - h.confidence     → nilai kepercayaan model (0.0 s.d. 1.0)
// - h.catatan        → catatan opsional dari pengguna
// - h.created_at     → waktu deteksi dilakukan
//
// Gambar disimpan di folder: uploads/deteksi/
// ============================================================

// data.php berisi fungsi label_display() untuk konversi nama label model → tampilan UI
require_once __DIR__ . '/../../functions/data.php';
?>

<!-- ======================================================
     TABEL RIWAYAT DETEKSI PENYAKIT DAUN PADI

     Sistem akan membaca SEMUA data dari tabel hasil_deteksi,
     digabungkan (JOIN) dengan tabel users untuk mendapatkan nama pengguna.
     Data diurutkan dari yang paling baru (ORDER BY id_deteksi DESC).

     Untuk setiap baris data, sistem akan:
     (1) Menampilkan satu baris di tabel
     (2) Membuat satu modal hapus unik (id: modalhapus[id_deteksi])
     Setelah semua baris selesai ditampilkan, barulah muncul:
     (3) Satu modal preview gambar bersama (id: deteksiModal)
====================================================== -->
<section class="section table">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                Database Riwayat Deteksi Penyakit Daun Padi
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <!-- Tabel dengan DataTables (id="example1") untuk fitur pencarian & pengurutan otomatis -->
                <table class="table table-hover table-striped" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengguna</th>
                            <th>Gambar</th>      <!-- Thumbnail kecil yang bisa diklik untuk preview -->
                            <th>Penyakit</th>    <!-- Label penyakit yang sudah dikonversi ke tampilan rapi -->
                            <th>Confidence</th>  <!-- Tingkat keyakinan model (%) -->
                            <th>Catatan</th>     <!-- Catatan opsional dari pengguna -->
                            <th>Waktu</th>       <!-- Waktu deteksi dilakukan -->
                            <th>Aksi</th>        <!-- Tombol hapus -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1; // Nomor urut baris, dimulai dari 1

                        // ======================================================
                        // QUERY: Ambil semua riwayat deteksi
                        // Gabungkan tabel hasil_deteksi (h) dengan users (u)
                        // menggunakan LEFT JOIN berdasarkan id_user
                        // agar nama pengguna bisa ikut ditampilkan
                        // ======================================================
                        $sql = "
                            SELECT h.*, u.nama_user 
                            FROM hasil_deteksi h
                            LEFT JOIN users u ON h.id_user = u.id_user
                            ORDER BY h.id_deteksi DESC
                        ";
                        $query = mysqli_query($koneksi, $sql);

                        // ======================================================
                        // LOOPING: Sistem akan mengecek setiap baris hasil deteksi satu per satu
                        // Untuk SETIAP baris, sistem membuat:
                        // - 1 baris di tabel (berisi data deteksi)
                        // - 1 modal konfirmasi hapus (popup yang muncul saat tombol Hapus diklik)
                        // ======================================================
                        while ($row = mysqli_fetch_assoc($query)) :
                            $id_deteksi = $row['id_deteksi'];               // Kolom id_deteksi dari tabel hasil_deteksi
                            $nama_user  = $row['nama_user'] ?? 'Tidak diketahui'; // Kolom nama_user dari tabel users
                            $file_path  = $row['file_path'];                // Kolom file_path (contoh: uploads/deteksi/padi_xxx.jpg)
                            $label      = $row['label_penyakit'];           // Kolom label_penyakit — label asli dari model (disimpan di DB)
                            $label_ui   = label_display($label);            // Konversi label asli → nama tampilan rapi (via fungsi di data.php)
                            $confidence = (float) $row['confidence'];       // Kolom confidence — nilai 0.0 s.d. 1.0
                            $catatan    = $row['catatan'];                  // Kolom catatan — bisa null jika tidak diisi
                            $created_at = $row['created_at'];               // Kolom created_at — waktu deteksi

                            // Buat path gambar untuk tag <img> dari sudut pandang folder dashboard/
                            // (file_path dimulai dari root project, tambahkan ../ untuk naik satu level)
                            $img_src = '../' . $file_path;
                        ?>
                            <!-- ======================================================
                                 BARIS TABEL DATA DETEKSI
                                 Setiap baris mewakili satu proses deteksi yang pernah dilakukan
                            ====================================================== -->
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($nama_user); ?></td>

                                <!-- ======================================================
                                     THUMBNAIL GAMBAR (BISA DIKLIK)
                                     Gambar kecil yang bisa diklik untuk membuka modal preview.
                                     Data-attribute di tag <a> digunakan oleh JavaScript untuk
                                     mengisi konten modal preview secara dinamis.

                                     File gambar diambil dari folder: uploads/deteksi/
                                     Path lengkap: ../uploads/deteksi/[nama_file]
                                ====================================================== -->
                                <td>
                                    <!-- Bungkus img dengan <a> agar bisa klik & buka modal -->
                                    <a href="#"
                                        class="deteksi-thumb"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deteksiModal"
                                        data-img_src="<?= htmlspecialchars($img_src); ?>"
                                        data-user="<?= htmlspecialchars($nama_user); ?>"
                                        data-label="<?= htmlspecialchars($label_ui); ?>"
                                        data-confidence="<?= round($confidence * 100, 2); ?>%"
                                        data-catatan="<?= htmlspecialchars($catatan ?? '-'); ?>"
                                        data-waktu="<?= htmlspecialchars($created_at); ?>">
                                        <!-- Gambar thumbnail berukuran kecil (max 80x80 piksel) -->
                                        <img src="<?= htmlspecialchars($img_src); ?>"
                                            alt="Gambar daun padi"
                                            style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                    </a>
                                </td>

                                <td><?= htmlspecialchars($label_ui); ?></td>              <!-- Nama penyakit yang sudah dikonversi -->
                                <td><?= round($confidence * 100, 2); ?>%</td>             <!-- Confidence diubah ke persentase -->
                                <td><?= htmlspecialchars($catatan ?? '-'); ?></td>        <!-- Catatan, tampilkan '-' jika kosong -->
                                <td><?= htmlspecialchars($created_at); ?></td>            <!-- Waktu deteksi dilakukan -->
                                <td>
                                    <!-- ======================================================
                                         TOMBOL HAPUS
                                         Saat diklik, akan membuka modal konfirmasi hapus
                                         yang unik untuk baris ini (id: modalhapus[id_deteksi])
                                    ====================================================== -->
                                    <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalhapus<?= htmlspecialchars($id_deteksi); ?>">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- ======================================================
                                 MODAL KONFIRMASI HAPUS
                                 Popup yang muncul ketika tombol "Hapus" diklik.
                                 Setiap baris memiliki modal hapus sendiri (id unik berdasarkan id_deteksi)
                                 agar tidak saling bentrok.

                                 Saat tombol "Hapus" di modal ini diklik:
                                 → Form dikirim ke function_deteksi.php dengan method POST
                                 → function_deteksi.php akan:
                                    (1) Hapus file gambar fisik dari folder uploads/deteksi/
                                    (2) Hapus baris dari tabel hasil_deteksi berdasarkan id_deteksi

                                 Data yang dikirim:
                                 - id_deteksi  (hidden) → untuk menentukan baris mana yang dihapus dari DB
                                 - file_path   (hidden) → untuk menentukan file mana yang dihapus dari server
                                 - btn_hapusdeteksi     → nama tombol yang memicu aksi hapus di function_deteksi.php
                            ====================================================== -->
                            <div class="modal fade"
                                id="modalhapus<?= htmlspecialchars($id_deteksi); ?>"
                                tabindex="-1"
                                role="dialog"
                                aria-labelledby="modalHapusLabel<?= htmlspecialchars($id_deteksi); ?>"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
                                    role="document">
                                    <form action="../functions/function_deteksi.php" method="post">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalHapusLabel<?= htmlspecialchars($id_deteksi); ?>">
                                                    Hapus Riwayat Deteksi
                                                </h5>
                                                <button type="button" class="close" data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                    <i data-feather="x"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>
                                                    Hapus riwayat deteksi #<?= htmlspecialchars($id_deteksi); ?> ?
                                                </p>
                                            </div>
                                            <!-- Data tersembunyi yang dikirim ke function_deteksi.php -->
                                            <input type="hidden" name="id_deteksi" value="<?= htmlspecialchars($id_deteksi); ?>"> <!-- ID baris di tabel hasil_deteksi -->
                                            <input type="hidden" name="file_path" value="<?= htmlspecialchars($file_path); ?>">   <!-- Path file gambar untuk dihapus dari server -->
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <div class="modal-footer">
                                                <!-- Tombol Batal: menutup modal tanpa melakukan apapun -->
                                                <button type="button"
                                                    class="btn btn-light-secondary"
                                                    data-bs-dismiss="modal">
                                                    <span class="d-none d-sm-block">Batal</span>
                                                </button>
                                                <!-- Tombol Hapus: mengirim form ke function_deteksi.php untuk proses hapus -->
                                                <button type="submit"
                                                    name="btn_hapusdeteksi"
                                                    class="btn btn-danger ms-1">
                                                    <span class="d-none d-sm-block">Hapus</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- END MODAL HAPUS -->

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- ======================================================
     MODAL PREVIEW DETAIL DETEKSI (SATU UNTUK SEMUA BARIS)

     Modal ini muncul ketika pengguna mengklik thumbnail gambar di tabel.
     Kontennya diisi secara dinamis oleh JavaScript (lihat blok <script> di bawah)
     berdasarkan data-attribute yang ada di tag <a class="deteksi-thumb">.

     Kolom yang ditampilkan:
     - Gambar         → dari data-img_src (path file di folder uploads/deteksi/)
     - Pengguna       → dari data-user (nama_user dari tabel users)
     - Penyakit       → dari data-label (label penyakit yang sudah dikonversi)
     - Confidence     → dari data-confidence (dalam format %)
     - Catatan        → dari data-catatan (catatan dari tabel hasil_deteksi)
     - Waktu          → dari data-waktu (created_at dari tabel hasil_deteksi)
====================================================== -->
<div class="modal fade" id="deteksiModal" tabindex="-1" role="dialog" aria-labelledby="deteksiModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deteksiModalTitle">Detail Deteksi</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Gambar daun padi yang dideteksi — src diisi oleh JavaScript saat diklik -->
                <img id="deteksiModalImage"
                    class="d-block mx-auto"
                    src=""
                    alt="Gambar Deteksi"
                    style="max-width: 60%; height: auto;">

                <!-- Informasi detail hasil deteksi — diisi oleh JavaScript -->
                <p><strong>Pengguna:</strong> <span id="deteksiModalUser"></span></p>
                <p><strong>Penyakit:</strong> <span id="deteksiModalLabel"></span></p>
                <p><strong>Confidence:</strong> <span id="deteksiModalConfidence"></span></p>
                <p><strong>Catatan:</strong> <span id="deteksiModalCatatan"></span></p>
                <p><strong>Waktu:</strong> <span id="deteksiModalWaktu"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================
     SKRIP JAVASCRIPT — PENGISI MODAL PREVIEW GAMBAR

     Alur kerjanya:
     (1) Tunggu sampai seluruh halaman selesai dimuat (DOMContentLoaded).
     (2) Cari semua elemen <a> dengan class "deteksi-thumb" (thumbnail gambar di tabel).
     (3) Pasang "pendengar klik" di setiap thumbnail tersebut.
     (4) Saat salah satu thumbnail diklik:
         - Ambil semua informasi dari data-attribute elemen tersebut
           (img_src, user, label, confidence, catatan, waktu)
         - Masukkan informasi tersebut ke dalam elemen-elemen di dalam modal preview
         - Modal otomatis terbuka karena sudah ada data-bs-toggle="modal" di tag <a>
====================================================== -->
<script>
    /* ======================================================
       SKRIP JAVASCRIPT: ISI MODAL PREVIEW SAAT GAMBAR DIKLIK

       Alur Kerja:
       (1) Tunggu sampai seluruh halaman (HTML) selesai dimuat ke browser.
       (2) Kumpulkan semua thumbnail gambar (elemen dengan class deteksi-thumb).
       (3) Pasang pendengar klik pada setiap thumbnail.
       (4) Saat thumbnail diklik, ambil semua data dari atribut data-* pada elemen tersebut.
       (5) Masukkan data tersebut ke dalam elemen-elemen di dalam modal preview.
    ====================================================== */
    document.addEventListener('DOMContentLoaded', function() {
        // (1) & (2) Setelah halaman siap, kumpulkan semua thumbnail gambar di tabel
        const thumbs          = document.querySelectorAll('.deteksi-thumb');   // Semua thumbnail
        const modalImage      = document.getElementById('deteksiModalImage');  // Elemen <img> di modal
        const modalUser       = document.getElementById('deteksiModalUser');   // Elemen <span> nama pengguna di modal
        const modalLabel      = document.getElementById('deteksiModalLabel');  // Elemen <span> nama penyakit di modal
        const modalConfidence = document.getElementById('deteksiModalConfidence'); // Elemen <span> confidence di modal
        const modalCatatan    = document.getElementById('deteksiModalCatatan');    // Elemen <span> catatan di modal
        const modalWaktu      = document.getElementById('deteksiModalWaktu');      // Elemen <span> waktu di modal

        // (3) Pasang pendengar klik pada SETIAP thumbnail (sistem mengecek satu per satu)
        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                // (4) Ambil data dari atribut data-* yang sudah diisi oleh PHP saat halaman dimuat
                const imgSrc     = this.getAttribute('data-img_src');    // Path file gambar
                const user       = this.getAttribute('data-user');       // Nama pengguna
                const label      = this.getAttribute('data-label');      // Nama penyakit
                const confidence = this.getAttribute('data-confidence'); // Nilai confidence (%)
                const catatan    = this.getAttribute('data-catatan');    // Catatan opsional
                const waktu      = this.getAttribute('data-waktu');      // Waktu deteksi

                // (5) Masukkan data ke dalam elemen-elemen di modal preview
                modalImage.src                = imgSrc;            // Isi src gambar
                modalUser.textContent         = user       || '-'; // Isi nama pengguna ('-' jika kosong)
                modalLabel.textContent        = label      || '-'; // Isi nama penyakit
                modalConfidence.textContent   = confidence || '-'; // Isi nilai confidence
                modalCatatan.textContent      = catatan    || '-'; // Isi catatan
                modalWaktu.textContent        = waktu      || '-'; // Isi waktu deteksi
            });
        });
    });
</script>