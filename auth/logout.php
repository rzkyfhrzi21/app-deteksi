<?php
// ============================================================
// FILE: logout.php
// TUJUAN: Menghapus sesi login pengguna dan mengarahkan ke halaman login
//
// Ibarat "menutup pintu" — ketika pengguna menekan tombol keluar,
// semua data sesi (username, id, nama) yang tersimpan sementara
// di server langsung dihapus bersih agar tidak bisa diakses lagi.
//
// ALUR KERJA:
// (1) Hidupkan/baca sesi yang sedang aktif          → session_start()
// (2) Hancurkan semua data sesi secara permanen     → session_destroy()
// (3) Arahkan browser ke halaman login              → header('Location: ...')
// ============================================================

session_start();    // (1) Buka akses ke data sesi yang sedang berjalan
session_destroy();  // (2) Hapus semua data sesi (username, id, nama, dsb.) — pengguna resmi keluar
header('Location: ../auth/login'); // (3) Redirect otomatis ke halaman login
