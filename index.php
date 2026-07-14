<?php
// ============================================================
// FILE: index.php (Halaman Utama / Landing Page Publik)
// TUJUAN: Menampilkan informasi publik (SEO Friendly) untuk 
//         pengunjung sebelum mereka mendaftar atau login.
// ============================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>Sistem Pakar Deteksi Penyakit Daun Padi</title>
    
    <!-- Meta SEO Lengkap -->
    <meta name="description" content="Aplikasi Deteksi Penyakit Tanaman Padi. Kenali penyakit daun padi Anda dengan cepat dan akurat menggunakan model klasifikasi gambar (Machine Learning).">
    <meta name="keywords" content="Aplikasi Deteksi Penyakit Padi, Sistem Pakar Tanaman Padi, Deteksi Penyakit Daun Padi, Machine Learning Pertanian, Smart Farming Indonesia, Model Deteksi Kustom">
    <meta name="author" content="Sistem Deteksi Padi">
    
    <!-- Open Graph Tags (Untuk Share WhatsApp/Facebook) -->
    <meta property="og:title" content="Sistem Pakar Deteksi Penyakit Daun Padi">
    <meta property="og:description" content="Deteksi dini penyakit tanaman padi menggunakan model deteksi akurat yang dilatih secara khusus untuk mendiagnosis masalah tanaman.">
    <meta property="og:image" content="https://deteksi-padi.ngekos.mikrosite.web.id/dashboard/assets/logo.png">
    <meta property="og:url" content="https://deteksi-padi.ngekos.mikrosite.web.id/">
    <meta property="og:type" content="website">

    <link rel="shortcut icon" href="dashboard/assets/logo.png" type="image/x-icon">
    
    <!-- CSS Bootstrap dari Mazer -->
    <link rel="stylesheet" href="dashboard/assets/compiled/css/app.css">
    
    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            overflow-x: hidden; 
            background-color: #f2f7ff;
        }
        /* Navbar Custom */
        .navbar-brand { font-weight: 800; color: #435ebe !important; font-size: 1.5rem; }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #435ebe 0%, #293a7d 100%);
            color: white;
            padding: 120px 0 160px;
            border-bottom-left-radius: 80px;
            border-bottom-right-radius: 80px;
            position: relative;
        }
        .hero h1 { color: white; font-weight: 900; font-size: 3.5rem; line-height: 1.2; margin-bottom: 25px; }
        .hero p { font-size: 1.25rem; margin-bottom: 40px; opacity: 0.9; font-weight: 300; }
        
        /* Animasi mengambang untuk logo */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .hero-img { 
            max-height: 350px; 
            animation: float 4s ease-in-out infinite; 
            filter: drop-shadow(0px 15px 25px rgba(0,0,0,0.3));
        }

        /* Card Fitur */
        .feature-container { margin-top: -80px; position: relative; z-index: 10; }
        .feature-card {
            padding: 40px 30px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.02);
        }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 20px 50px rgba(67, 94, 190, 0.15); }
        .feature-icon {
            font-size: 3.5rem;
            color: #435ebe;
            margin-bottom: 25px;
            background: #eef2ff;
            width: 90px;
            height: 90px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 25px;
        }
        
        /* Footer */
        .footer { background: #ffffff; padding: 40px 0; margin-top: 100px; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="dashboard/assets/logo.png" alt="Logo" height="40" class="me-3">
                <span class="d-none d-sm-inline">Sistem Deteksi Padi</span>
            </a>
            <div class="d-flex gap-2">
                <a href="auth/login" class="btn btn-outline-primary px-3 px-md-4 rounded-pill font-bold">Masuk</a>
                <a href="auth/register" class="btn btn-primary px-3 px-md-4 rounded-pill font-bold shadow-sm d-none d-sm-inline-block">Daftar Gratis</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container mt-5">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                    <h1>Lindungi Panen Anda dengan Sistem Pakar Kami</h1>
                    <p>Sistem pendeteksi penyakit daun padi. Unggah foto daun padi Anda dan biarkan model klasifikasi kami mendiagnosis penyakitnya secara instan dan akurat berdasarkan data latih yang spesifik.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="auth/register" class="btn btn-light btn-lg px-5 rounded-pill font-bold text-primary shadow-lg">Coba Sekarang</a>
                        <a href="#fitur" class="btn btn-outline-light btn-lg px-4 rounded-pill">Pelajari Fitur</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="dashboard/assets/logo.png" alt="Ilustrasi Padi" class="img-fluid hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR SECTION -->
    <section id="fitur" class="container feature-container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="bi bi-cpu-fill"></i></div>
                    <h4 class="font-bold">Model Klasifikasi Kustom</h4>
                    <p class="text-muted mb-0">Didukung oleh model klasifikasi gambar yang dilatih sendiri (custom trained) dengan dataset ekstensif untuk memastikan akurasi deteksi yang tinggi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <h4 class="font-bold">Analisis Instan</h4>
                    <p class="text-muted mb-0">Hanya butuh beberapa detik! Hasil deteksi penyakit beserta tingkat persentase akurasi langsung tampil di layar Anda.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="bi bi-phone-vibrate-fill"></i></div>
                    <h4 class="font-bold">Akses Kapan Saja</h4>
                    <p class="text-muted mb-0">Antarmuka website responsif yang sangat ramah pengguna, baik saat diakses lewat komputer maupun langsung dari sawah menggunakan HP Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION (CTA) -->
    <section class="container mt-5 pt-5 text-center">
        <div class="card border-0 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%); border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-5 my-3">
                <h2 class="font-black mb-3 text-dark">Siap Menyelamatkan Panen Anda?</h2>
                <p class="text-muted mb-4 lead mx-auto" style="max-width: 600px;">Bergabunglah dengan para petani dan akademisi lainnya. Identifikasi dini penyakit adalah kunci untuk panen yang melimpah.</p>
                <a href="auth/register" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg font-bold">
                    <i class="bi bi-person-plus-fill me-2"></i> Buat Akun Gratis
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p class="text-muted mb-1">&copy; <?= date('Y') ?> Sistem Pakar Deteksi Penyakit Padi. Hak cipta dilindungi.</p>
            <small class="text-muted font-semibold">Dibangun untuk mendukung inovasi Pertanian Indonesia.</small>
        </div>
    </footer>

    <!-- Bootstrap Icons (Untuk Icon Fitur) -->
    <link rel="stylesheet" href="dashboard/assets/extensions/bootstrap-icons/font/bootstrap-icons.css">

</body>
</html>
