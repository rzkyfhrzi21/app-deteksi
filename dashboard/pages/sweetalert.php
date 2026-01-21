<?php
// File: ../dashboard/pages/sweetalert.php
// Pusat SweetAlert universal berbasis ?action=...&status=...
?>
<script>
  (() => {
    const params = new URLSearchParams(window.location.search);
    const status = params.get("status");
    const action = params.get("action");

    if (!status || !action) return;

    /* ==========================================
       KONFIGURASI ALERT
       ========================================== */
    const ALERTS = {
      /* ================= SUCCESS ================= */
      success: {
        // ===== AUTH =====
        registered: "Akun berhasil dibuat. Silakan masuk ke sistem 🌱",
        deleteuser: "Akun berhasil dihapus dari sistem ✅",

        // (opsional, hanya jika suatu saat kamu pakai)
        login: "Login berhasil 👋",

        // ===== USER =====
        adduser: "User baru berhasil ditambahkan 😁",
        edituser: "Data user berhasil diperbarui 😁",

        // ===== RIWAYAT / DETEKSI =====
        deleteriwayat: "Riwayat berhasil dihapus 😁",
        deteksi: "Deteksi penyakit berhasil dilakukan 🌱",

        // ===== UMUM =====
        upload: "Data berhasil disimpan 📁",
      },

      /* ================= ERROR ================= */
      error: {
        // ===== AUTH =====
        login: "Username atau password tidak valid ❌",
        register: "Terjadi kesalahan saat registrasi.",

        // ===== USER =====
        adduser: "Gagal menambahkan user 🤬",
        edituser: "Gagal memperbarui data user 🤬",
        deleteuser: "Gagal menghapus user 🤬",

        // ===== RIWAYAT / DETEKSI =====
        deleteriwayat: "Gagal menghapus riwayat 🤬",
        deteksi: "Deteksi penyakit gagal 🌾",
        server: "Server deteksi tidak dapat dihubungi 🚫",

        // ===== UMUM =====
        upload: "Gagal upload file 📁",
        unauthorized: "Akses tidak diizinkan 🚫",
      },

      /* ================= WARNING ================= */
      warning: {
        // ===== REGISTER =====
        userexist: "Username sudah digunakan. Silakan pilih yang lain 🤗",
        passwordnotsame: "Password dan konfirmasi tidak sama 🤗",

        // ===== FOTO PROFIL =====
        invalidext: "Ekstensi foto profil tidak valid. Gunakan JPG / JPEG / PNG ⚠️",
        filesize: "Ukuran foto profil terlalu besar. Maksimal 1MB ⚠️",

        // ===== UMUM =====
        notfound: "Data tidak ditemukan ⚠️",
        invalid: "Input tidak valid ⚠️",
      }
    };

    const message = ALERTS?.[status]?.[action];
    if (!message) return;

    /* ==========================================
       TAMPILKAN SWEETALERT
       ========================================== */
    Swal.fire({
      icon: status,
      title: status === "success" ? "Berhasil!" : status === "error" ? "Gagal!" : "Peringatan!",
      text: message,
      footer: "@ Deteksi Penyakit Padi",
      timer: 3000,
      showConfirmButton: false,
      timerProgressBar: true
    });

    /* ==========================================
       BERSIHKAN URL (ANTI ALERT MUNCUL LAGI)
       ========================================== */
    window.history.replaceState({}, document.title, window.location.pathname);
  })();
</script>