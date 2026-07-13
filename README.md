# 🌾 app-deteksi — Aplikasi Web Deteksi Penyakit Daun Padi

Ini adalah folder **aplikasi web PHP** yang diakses pengguna melalui browser.
Aplikasi ini menghubungkan pengguna dengan model AI (CNN) yang berjalan di server Flask terpisah (`app-deteksi_ml`).

---

## ✨ Fitur Utama: Deteksi Penyakit Daun Padi dengan CNN

Sistem ini memungkinkan pengguna untuk **mengunggah foto daun padi** dan secara otomatis mendeteksi jenis penyakitnya menggunakan **model Convolutional Neural Network (CNN)**.

### 🔬 Cara Kerja Fitur Deteksi (Alur Lengkap)

```
Pengguna upload foto daun padi
        ↓
[mulai_deteksi.php]          ← Halaman form upload (di browser)
        ↓  (POST multipart)
[function_deteksi.php]       ← Proses: validasi, simpan file, panggil API
        ↓  (cURL POST ke /predict)
[api_flask.py]               ← Server Flask: preprocess gambar + jalankan model CNN
        ↓  (model/model_fp16.tflite)
Model CNN menganalisis gambar → 5 angka probabilitas
        ↓
Hasil JSON dikembalikan ke PHP
        ↓
[function_deteksi.php]       ← Simpan hasil ke tabel 'hasil_deteksi' di database
        ↓
[mulai_deteksi.php]          ← Tampilkan hasil: nama penyakit + confidence (%)
```

### 🦠 Penyakit yang Dapat Dideteksi

| Label Model       | Tampilan UI          | Keterangan                        |
|-------------------|----------------------|-----------------------------------|
| `Healthy`         | Healthy (Daun Sehat) | Daun normal, tidak ada penyakit   |
| `Bacterialblight` | Bacterial Blight     | Hawar bakteri — ujung daun cokelat|
| `Blast`           | Blast                | Blas/busuk leher — bercak belah ketupat |
| `Brownspot`       | Brown Spot           | Bercak cokelat — titik-titik bulat |
| `Tungro`          | Tungro               | Virus tungro — daun menguning     |

### 📁 File Kunci Fitur Deteksi

| File | Peran |
|------|-------|
| `dashboard/pages/mulai_deteksi.php` | **Halaman utama** — form upload gambar & tampilan hasil |
| `functions/function_deteksi.php` | **Otak proses** — validasi file, panggil API Flask, simpan ke DB |
| `functions/ping_render.php` | **Cek koneksi** — ping server Flask sebelum upload |
| `functions/data.php` | **Label mapper** — konversi nama label model → tampilan rapi |
| `dashboard/pages/riwayat_deteksi.php` | **Riwayat** — tabel semua hasil deteksi yang pernah dilakukan |

---

## 📂 Struktur Folder Lengkap

```
app-deteksi/
│
├── auth/                          ← Halaman autentikasi pengguna
│   ├── login.php                  ← Halaman form masuk (username + password)
│   ├── register.php               ← Halaman form daftar akun baru
│   ├── logout.php                 ← Proses keluar — hapus sesi login
│   └── index.php                  ← Redirect ke login
│
├── dashboard/                     ← Halaman-halaman setelah login
│   ├── admin.php                  ← Controller utama dashboard (router halaman)
│   ├── index.php                  ← Redirect ke admin.php
│   ├── assets/                    ← Aset statis (CSS, JS, gambar, template)
│   │   ├── compiled/css/          ← CSS template (app.css, app-dark.css, auth.css)
│   │   ├── static/js/             ← JavaScript template & inisialisasi tema
│   │   ├── profile/               ← Folder foto profil pengguna (upload di sini)
│   │   └── logo.png               ← Logo sistem
│   └── pages/                     ← Konten setiap halaman (di-include oleh admin.php)
│       ├── dashboard.php          ← Beranda — 4 kartu statistik utama sistem
│       ├── mulai_deteksi.php      ← ⭐ FITUR UTAMA — form upload & hasil deteksi
│       ├── riwayat_deteksi.php    ← Tabel riwayat semua hasil deteksi
│       ├── profile.php            ← Halaman profil & manajemen akun pengguna
│       ├── css.php                ← Kumpulan tag <link CSS> yang di-include template
│       ├── js.php                 ← Kumpulan tag <script JS> yang di-include template
│       └── sweetalert.php         ← Notifikasi popup (SweetAlert2) untuk semua halaman
│
├── functions/                     ← Logika backend PHP (diakses via form POST)
│   ├── koneksi.php                ← Koneksi database MySQL (auto-deteksi localhost/hosting)
│   ├── function_auth.php          ← Proses login & registrasi pengguna
│   ├── function_admin.php         ← Proses edit profil, ganti foto, hapus akun
│   ├── function_deteksi.php       ← ⭐ INTI DETEKSI — upload foto, panggil Flask, simpan ke DB
│   ├── data.php                   ← Query statistik dashboard + fungsi label_display()
│   ├── log_akses.php              ← Catat rekam jejak login ke tabel rekam_akses_web
│   ├── ping_render.php            ← Ping /health ke server Flask via cURL (bypass CORS)
│   └── index.php                  ← Keamanan: blokir akses langsung ke folder functions/
│
├── uploads/                       ← Folder penyimpanan file yang diupload pengguna
│   └── deteksi/                   ← Foto daun padi yang dikirim untuk dideteksi
│                                    (nama file: padi_[timestamp].jpg/png)
│
├── db/
│   └── app_deteksi.sql            ← File SQL untuk membuat database & tabel awal
│
├── laporan/                       ← (Opsional) File laporan / ekspor data
│
├── .htaccess                      ← Konfigurasi URL routing Apache
├── index.php                      ← Entry point utama — redirect ke dashboard/login
└── README.md                      ← File ini
```

---

## 🗄️ Struktur Database

Database bernama `app-deteksi` (lokal) / `uucdjd7c_app-deteksi` (hosting).

| Tabel | Fungsi |
|-------|--------|
| `users` | Data akun pengguna (id_user, nama, username, password, foto, dll.) |
| `hasil_deteksi` | Hasil setiap proses deteksi (label penyakit, confidence, file foto, waktu) |
| `rekam_akses_web` | Log setiap kali pengguna login (IP, browser, OS, waktu) |

---

## 🔗 Konfigurasi Koneksi ke Server Flask (API ML)

File yang perlu diperhatikan saat mengganti URL server:

```
functions/function_deteksi.php   → ubah konstanta API_URL (define('API_URL', '...'))
functions/ping_render.php        → ubah variabel $healthUrl
```

**Mode API:**
```php
// Di function_deteksi.php, baris ~12
define('API_MODE', 'online');  // 'online' = Render.com | 'local' = localhost:5000
```

---

## 🚀 Cara Menjalankan Secara Lokal

1. Pastikan **Laragon** sudah berjalan (Apache + MySQL aktif)
2. Import database: buka phpMyAdmin → import file `db/app_deteksi.sql`
3. Ubah `API_MODE` di `functions/function_deteksi.php` menjadi `'local'`
4. Jalankan server Flask dari folder `app-deteksi_ml/` (lihat README di sana)
5. Buka browser: `http://localhost/App Deteksi/app-deteksi/`

---

## ⚙️ Teknologi yang Digunakan

| Teknologi | Versi | Kegunaan |
|-----------|-------|----------|
| PHP | 8.x | Backend logika & template halaman |
| MySQL | 8.x | Database penyimpanan data |
| Bootstrap 5 | CDN | Framework CSS tampilan |
| SweetAlert2 | 11 | Notifikasi popup interaktif |
| ParsleyJS | 2 | Validasi form di sisi browser |
| jQuery | 3.7.1 | Manipulasi DOM & AJAX |
| Mazer Template | - | Tema dashboard admin |
