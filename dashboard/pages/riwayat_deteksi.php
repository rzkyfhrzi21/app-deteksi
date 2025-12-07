<?php

// Hanya admin yang boleh lihat
if ($_SESSION['sesi_role'] !== 'admin') {
    return;
}

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
                                    <img src="<?= htmlspecialchars($img_src); ?>"
                                        alt="Gambar daun padi"
                                        style="max-width: 80px; max-height: 80px; object-fit: cover;">
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
                                                    class="btn btn-primary ms-1">
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