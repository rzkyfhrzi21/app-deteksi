<script>
  const params = new URLSearchParams(window.location.search);
  const status = params.get("status");
  const action = params.get("action");

  if (!status || !action) return;

  const alerts = {
    success: {
      adduser: "User baru berhasil ditambahkan 😁",
      edituser: "Data user berhasil diperbarui 😁",
      deleteuser: "User berhasil dihapus 😁",
      deleteriwayat: "Riwayat berhasil dihapus 😁",
      deteksi: "Deteksi penyakit berhasil dilakukan 🌱",
    },
    error: {
      adduser: "Gagal menambahkan user 🤬",
      edituser: "Gagal memperbarui data user 🤬",
      deleteuser: "Gagal menghapus user 🤬",
      deleteriwayat: "Gagal menghapus riwayat 🤬",
      deteksi: "Tidak berhasil mendeteksi penyakit 🌾",
      server: "Server deteksi tidak dapat dihubungi 🚫",
    },
    warning: {
      userexist: "Username sudah digunakan 🤗",
      passwordnotsame: "Password tidak sama 🤗",
    }
  };

  const message = alerts[status]?.[action];
  if (!message) return;

  Swal.fire({
    icon: status,
    title: status === "success" ? "Berhasil!" : status === "error" ? "Gagal!" : "Peringatan!",
    text: message,
    footer: "@ Deteksi Penyakit Padi",
    timer: 3000,
    showConfirmButton: false,
  });

  // Optional: bersihkan URL agar alert tidak muncul saat refresh
  window.history.replaceState({}, document.title, window.location.pathname);
</script>