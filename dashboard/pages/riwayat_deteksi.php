<?php


?>
<section class="section table">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                Database Riwayat Deteksi Penyakit Daun Padi
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengguna</th>
                            <th>Gambar</th>
                            <th>Penyakit</th>
                            <th>Confidence</th>
                            <th>Catatan</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../functions/koneksi.php';
                        $no = 1;

                        // Ambil semua riwayat deteksi, join ke users supaya dapat nama
                        $sql = "
                            SELECT h.*, u.nama_user 
                            FROM hasil_deteksi h
                            LEFT JOIN users u ON h.id_user = u.id_user
                            ORDER BY h.id_deteksi DESC
                        ";
                        $query = mysqli_query($koneksi, $sql);

                        while ($row = mysqli_fetch_assoc($query)) :
                            $id_deteksi   = $row['id_deteksi'];
                            $nama_user    = $row['nama_user'] ?? 'Tidak diketahui';
                            $file_path    = $row['file_path'];         // contoh: uploads/deteksi/...
                            $label        = $row['label_penyakit'];
                            $confidence   = (float) $row['confidence'];
                            $catatan      = $row['catatan'];
                            $created_at   = $row['created_at'];

                            // path untuk <img> dari sudut pandang dashboard/
                            $img_src = '../' . $file_path;
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($nama_user); ?></td>
                                <td>
                                    <!-- Bungkus img dengan <a> agar bisa klik & buka modal -->
                                    <a href="#"
                                        class="deteksi-thumb"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deteksiModal"
                                        data-img_src="<?= htmlspecialchars($img_src); ?>"
                                        data-user="<?= htmlspecialchars($nama_user); ?>"
                                        data-label="<?= htmlspecialchars($label); ?>"
                                        data-confidence="<?= round($confidence * 100, 2); ?>%"
                                        data-catatan="<?= htmlspecialchars($catatan ?? '-'); ?>"
                                        data-waktu="<?= htmlspecialchars($created_at); ?>">
                                        <img src="<?= htmlspecialchars($img_src); ?>"
                                            alt="Gambar daun padi"
                                            style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($label); ?></td>
                                <td><?= round($confidence * 100, 2); ?>%</td>
                                <td><?= htmlspecialchars($catatan ?? '-'); ?></td>
                                <td><?= htmlspecialchars($created_at); ?></td>
                                <td>
                                    <!-- Button trigger modal hapus -->
                                    <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalhapus<?= htmlspecialchars($id_deteksi); ?>">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL HAPUS -->
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
                                            <input type="hidden" name="id_deteksi" value="<?= htmlspecialchars($id_deteksi); ?>">
                                            <input type="hidden" name="file_path" value="<?= htmlspecialchars($file_path); ?>">
                                            <div class="modal-footer">
                                                <button type="button"
                                                    class="btn btn-light-secondary"
                                                    data-bs-dismiss="modal">
                                                    <span class="d-none d-sm-block">Batal</span>
                                                </button>
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

<!-- MODAL PREVIEW GAMBAR DETEKSI -->
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
                <img id="deteksiModalImage"
                    class="d-block mx-auto"
                    src=""
                    alt="Gambar Deteksi"
                    style="max-width: 60%; height: auto;">

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

<script>
    // JS untuk mengisi modal dengan data gambar yang diklik
    document.addEventListener('DOMContentLoaded', function() {
        const thumbs = document.querySelectorAll('.deteksi-thumb');
        const modalImage = document.getElementById('deteksiModalImage');
        const modalUser = document.getElementById('deteksiModalUser');
        const modalLabel = document.getElementById('deteksiModalLabel');
        const modalConfidence = document.getElementById('deteksiModalConfidence');
        const modalCatatan = document.getElementById('deteksiModalCatatan');
        const modalWaktu = document.getElementById('deteksiModalWaktu');

        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-img_src');
                const user = this.getAttribute('data-user');
                const label = this.getAttribute('data-label');
                const confidence = this.getAttribute('data-confidence');
                const catatan = this.getAttribute('data-catatan');
                const waktu = this.getAttribute('data-waktu');

                modalImage.src = imgSrc;
                modalUser.textContent = user || '-';
                modalLabel.textContent = label || '-';
                modalConfidence.textContent = confidence || '-';
                modalCatatan.textContent = catatan || '-';
                modalWaktu.textContent = waktu || '-';
            });
        });
    });
</script>