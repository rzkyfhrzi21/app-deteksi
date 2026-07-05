# 🌾 Sistem Deteksi Penyakit Daun Padi

Aplikasi web berbasis **PHP + MySQL** untuk mendeteksi jenis penyakit pada daun tanaman padi secara otomatis menggunakan **model kecerdasan buatan (AI/CNN)**. Pengguna cukup upload foto daun padi, dan sistem akan langsung memberikan hasil diagnosis beserta tingkat keyakinannya.

> **Catatan:** Aplikasi ini terhubung ke API Flask (repo `app-deteksi_ml`) yang berjalan di server terpisah (Render.com) sebagai "otak" pemroses gambar.

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| 🔐 Login & Registrasi | Sistem akun pengguna dengan keamanan sesi |
| 🌿 Deteksi Penyakit | Upload foto daun → AI langsung analisis dan berikan hasil |
| 📋 Riwayat Deteksi | Lihat semua hasil deteksi sebelumnya dalam tabel |
| 👤 Kelola Profil | Update data pribadi, foto profil, username & password |
| 📊 Dashboard Statistik | Ringkasan: total pengguna, total deteksi, rata-rata confidence |

---

## 🗂️ Struktur Folder

```
app-deteksi/
├── auth/                        → Halaman autentikasi pengguna
│   ├── login.php                → Halaman form login
│   ├── register.php             → Halaman form pendaftaran akun baru
│   └── logout.php               → Proses keluar / hapus sesi login
│
├── dashboard/                   → Halaman-halaman utama setelah login
│   ├── admin.php                → Router halaman (membaca ?page=... dari URL)
│   ├── assets/                  → CSS, JS, gambar, foto profil pengguna
│   │   └── profile/             → Foto profil pengguna yang diupload
│   └── pages/                   → Konten setiap halaman dashboard
│       ├── dashboard.php        → Beranda: 4 kartu statistik sistem
│       ├── mulai_deteksi.php    → Form upload gambar + tampilan hasil deteksi
│       ├── riwayat_deteksi.php  → Tabel semua riwayat deteksi + modal hapus
│       ├── profile.php          → Form edit profil, foto, akun, hapus akun
│       ├── sweetalert.php       → Notifikasi popup otomatis (sukses/gagal/peringatan)
│       ├── js.php               → Kumpulan skrip JavaScript yang dipakai di seluruh dashboard
│       └── css.php              → CSS tambahan khusus dashboard
│
├── functions/                   → Logika backend (proses data dari form)
│   ├── function_auth.php        → Proses login & registrasi akun publik
│   ├── function_deteksi.php     → Proses upload gambar, kirim ke API Flask, simpan ke DB
│   ├── function_admin.php       → Proses update profil, foto, username, password, hapus akun
│   ├── data.php                 → Query statistik dashboard & fungsi bantu label_display()
│   ├── koneksi.php              → Koneksi ke database MySQL
│   └── log_akses.php            → Rekam jejak login ke tabel rekam_akses_web
│
└── uploads/
    └── deteksi/                 → Folder penyimpanan foto daun padi yang diupload pengguna
```

---

## 🗄️ Tabel Database yang Dipakai

| Tabel | Fungsi |
|---|---|
| `users` | Menyimpan data akun pengguna (id, nama, username, password hash, foto, dsb.) |
| `hasil_deteksi` | Menyimpan setiap hasil deteksi (gambar, label penyakit, confidence, catatan, waktu) |
| `rekam_akses_web` | Mencatat setiap akses login (untuk statistik total pengunjung) |

---

## 🚀 Cara Instalasi Lokal

### Prasyarat
- **XAMPP** (PHP 8.0+, MySQL, Apache)
- Browser modern (Chrome, Firefox, Edge)
- API Flask dari repo `app-deteksi_ml` sudah berjalan

### Langkah Instalasi
1. Clone atau copy folder `app-deteksi` ke dalam:
   ```
   C:\xampp\htdocs\App Deteksi\app-deteksi\
   ```

2. Import database ke phpMyAdmin:
   - Buka `http://localhost/phpmyadmin`
   - Buat database baru (contoh: `db_deteksi_padi`)
   - Import file `.sql` yang tersedia

3. Konfigurasi koneksi database di file `functions/koneksi.php`:
   ```php
   $host     = 'localhost';
   $user     = 'root';
   $password = '';
   $database = 'db_deteksi_padi';
   ```

4. Pastikan API Flask sudah berjalan. Cek pengaturan di `functions/function_deteksi.php`:
   ```php
   define('API_MODE', 'local');  // Ubah ke 'local' untuk testing
   // API berjalan di: http://127.0.0.1:5000
   ```

5. Buka browser dan akses:
   ```
   http://localhost/App%20Deteksi/app-deteksi/auth/register
   ```

---

## 📖 Cara Penggunaan Aplikasi

### Langkah 1 — Daftar Akun Baru
1. Buka halaman: `http://localhost/App%20Deteksi/app-deteksi/auth/register`
2. Isi formulir pendaftaran:
   - **Nama Lengkap** (min. 5 karakter)
   - **Username** (min. 5 karakter, harus unik)
   - **Password** (min. 5 karakter)
   - **Konfirmasi Password** (harus sama dengan password)
3. Klik tombol **"Daftar"**
4. Jika berhasil, Anda akan diarahkan ke halaman login

### Langkah 2 — Login
1. Buka halaman: `http://localhost/App%20Deteksi/app-deteksi/auth/login`
2. Masukkan **Username** dan **Password** yang sudah didaftarkan
3. Klik tombol **"Masuk"**

### Langkah 3 — Lengkapi Profil (Wajib Pertama Kali)
> ⚠️ Jika foto profil belum diupload, sistem akan otomatis mengarahkan ke halaman profil terlebih dahulu.

1. Di halaman **Profil**, upload foto profil Anda
2. Klik tombol **"Update Foto"**
3. Lengkapi data pribadi (nama, no. telepon, email, tanggal lahir, alamat)
4. Klik **"Simpan Data Pribadi"**

### Langkah 4 — Mulai Deteksi Penyakit
1. Klik menu **"Mulai Deteksi"** di sidebar
2. Klik tombol **"Pilih Gambar Daun Padi"** dan pilih foto dari komputer Anda
   - Format yang diterima: **JPG / JPEG / PNG**
   - Ukuran maksimal: **2 MB**
   - Tips: Ambil foto daun yang jelas, cukup cahaya, dan fokus pada daun
3. (Opsional) Isi kolom **Catatan** dengan keterangan tambahan
4. Klik tombol **"Upload & Deteksi"**
5. Tunggu beberapa detik (server sedang memproses gambar)

### Langkah 5 — Baca Hasil Deteksi
Setelah selesai, hasil akan muncul di bawah form:
- **Penyakit terdeteksi**: Nama jenis penyakit atau "Healthy (Daun Sehat)"
- **Confidence**: Tingkat keyakinan model (contoh: 93.45% = model 93% yakin)
- **Waktu deteksi**: Waktu saat deteksi dilakukan

### Langkah 6 — Lihat Riwayat Deteksi
1. Klik menu **"Riwayat Deteksi"** di sidebar
2. Tabel menampilkan semua hasil deteksi yang pernah dilakukan
3. Klik thumbnail gambar untuk melihat detail lengkapnya
4. Klik tombol **"Hapus"** untuk menghapus riwayat tertentu

---

## 🌿 Kelas Penyakit yang Dideteksi

| Label Model | Tampilan di Aplikasi | Keterangan |
|---|---|---|
| `Healthy` | Healthy (Daun Sehat) | Daun normal, tidak ada penyakit |
| `Bacterialblight` | Bacterial Blight | Penyakit hawar bakteri (Xanthomonas oryzae) |
| `Blast` | Blast | Penyakit blas / busuk leher (Magnaporthe oryzae) |
| `Brownspot` | Brown Spot | Penyakit bercak cokelat (Bipolaris oryzae) |
| `Tungro` | Tungro | Penyakit tungro (virus, disebarkan wereng hijau) |

---

## 🔗 Koneksi ke API Flask

File yang mengatur koneksi: [`functions/function_deteksi.php`](functions/function_deteksi.php)

```php
define('API_MODE', 'online');  // Ganti ke 'local' untuk testing lokal

// Mode online → server Render.com
define('API_URL', 'https://app-deteksi.onrender.com/predict');

// Mode local → server Flask di komputer sendiri
// define('API_URL', 'http://127.0.0.1:5000/predict');
```

> ⚠️ **Perhatian:** Server Render.com versi gratis bisa "tidur" dan butuh waktu 50-60 detik untuk bangun kembali (*cold start*). Timeout sudah diatur ke **60 detik** untuk mengantisipasi ini.

---

## 🔒 Keamanan

- Password disimpan dalam format **MD5 hash** (bukan teks biasa)
- Semua input dibersihkan dengan `htmlspecialchars()` untuk mencegah XSS
- Akses halaman dilindungi dengan pemeriksaan sesi login

---

## 📝 Catatan Pengembangan

- Dibuat untuk keperluan **Tugas Akhir / Skripsi**
- Teknologi: PHP native, MySQL, Bootstrap 5, SweetAlert2, DataTables
- Model AI: CNN (Convolutional Neural Network) dengan 5 kelas output
