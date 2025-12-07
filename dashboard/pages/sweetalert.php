<script>
  // Ambil parameter dari URL
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get("status");
  const action = urlParams.get("action");
  const ket = urlParams.get("ket");

  if (status === "success") {
    if (action === "adduser") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "User baru berhasil ditambahkan 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "edituser") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "User berhasil diedit 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deleteriwayat") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Riwayat donor berhasil dihapus 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deleteuser") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "User berhasil dihapus permanen 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "tambahkegiatan") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Kegiatan donor darah baru berhasil ditambah 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deletekegiatan") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Kegiatan donor darah berhasil dihapus 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "editkriteria") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Kriteria donor berhasil diedit 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "editkegiatan") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Kegiatan donor darah berhasil diedit 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "mulaidonor") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Pendonor lulus semua cek kesehatan. Silakan ke ruangan donor darah 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "uploadimgkegiatan") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Gambar dokumentasi kegiatan donor berhasil ditambah 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deletegalerikegiatan") {
      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Gambar dokumentasi kegiatan donor berhasil dihapus 😁",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    }
  } else if (status === "error") {
    if (action === "add") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menambahkan user 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "edituser") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat mengedit user 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deleteuser") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menghapus user 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "editkriteria") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat mengedit kriteria donor 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "addimg") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat mengupload foto 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "tambahkegiatan") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menambah kegiatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deletekegiatan") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menghapus kegiatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "uploadimgkegiatan") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menambah dokumentasi kegiatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deletegalerikegiatan") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat menghapus dokumentasi kegiatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "editkegiatan") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat mengedit kegiatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "deleteriwayat") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Riwayat donor gagal dihapus 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "mulaidonor") {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: "Terjadi kesalahan saat mengecek kesehatan 🤬",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    }
  } else if (status === "warning") {
    if (action === "userexist") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Username telah dipakai. Silahkan cari baru 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (action === "passwordnotsame") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Password tidak sama. Silahkan ulangi 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "usianotvalid") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, usia anda tidak sesuai ketentuan sehingga tidak berhasil 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "bbnotvalid") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, berat badan anda tidak sesuai ketentuan sehingga tidak berhasil 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "tensinotvalid") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, tekanan darah anda tidak sesuai ketentuan sehingga tidak berhasil 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "nilaihbnotvalid") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, kadar hemoglobin anda tidak sesuai ketentuan sehingga tidak berhasil 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "datanotvalid") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, data pendonor tidak sesuai dengan foto identitas 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "tidaksehat") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, kondisi anda sedang tidak sehat sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "adagejala") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, kondisi anda sedang tidak prima sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "sedanghamil") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda sedang dalam kondisi hamil sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "baruvaksin") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda baru saja menerima vaksin sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "adariwayatpenyakit") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda mempunyai riwayat penyakit berat sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "barutransfusi") {
      Swal.fire({
        icon: "warning",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda mempunyai riwayat menerima transfusi darah sehingga tidak diperbolehkan donor 🤗",
        title: "Peringatan!",
        text: "Password tidak sama. Silahkan ulangi 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "adatindik") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda mempunyai tindik, tato, akupuntur sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "barukonsumsiobat") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda sedang mengonsumsi obat sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    } else if (ket === "belum60hari") {
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Terima kasih sudah mendaftar. Tapi maaf, anda belum genap 60 hari sejak donor terakhir sehingga tidak diperbolehkan donor 🤗",
        footer: "@ Deteksi",
        timer: 3000,
        showConfirmButton: false,
      });
    }
  }
</script>