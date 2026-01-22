-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 07:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `app_deteksi`
--

-- --------------------------------------------------------

--
-- Table structure for table `data_penyakit`
--

CREATE TABLE `data_penyakit` (
  `id_penyakit` varchar(10) NOT NULL,
  `nama_penyakit` varchar(100) NOT NULL,
  `nama_latin` varchar(150) NOT NULL,
  `desc` text NOT NULL,
  `solusi` text NOT NULL,
  `cara_pencegahan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_penyakit`
--

INSERT INTO `data_penyakit` (`id_penyakit`, `nama_penyakit`, `nama_latin`, `desc`, `solusi`, `cara_pencegahan`) VALUES
('P001', 'Hawar Daun Bakteri (Bacterial Blight)', 'Xanthomonas oryzae pv. oryzae', 'Penyakit bakteri pada padi yang menyebabkan bercak memanjang berwarna kekuningan hingga cokelat pada tepi daun. Daun dapat mengering seperti terbakar, pertumbuhan terhambat, dan hasil panen menurun terutama pada kondisi lembap dan angin kencang.', 'Gunakan varietas tahan; atur jarak tanam agar sirkulasi udara baik; hindari pemupukan nitrogen berlebih; perbaiki drainase; lakukan sanitasi lahan (buang tanaman/daun terinfeksi). Jika serangan berat, gunakan bakterisida sesuai anjuran setempat (berdasarkan rekomendasi PPL/instansi pertanian).', 'Gunakan benih sehat dan varietas tahan; tanam serempak; rotasi tanaman bila memungkinkan; jaga kebersihan gulma/inang; hindari genangan berkepanjangan; pemupukan berimbang (NPK) dan pengairan teratur.'),
('P002', 'Penyakit Blas (Blast)', 'Magnaporthe oryzae (syn. Pyricularia oryzae)', 'Penyakit jamur yang menyerang daun, pelepah, hingga malai. Gejala umum berupa bercak berbentuk belah ketupat (spindle) berwarna abu-abu di tengah dan tepi cokelat. Serangan pada leher malai dapat menyebabkan malai patah atau gabah hampa.', 'Gunakan varietas tahan; lakukan pemupukan berimbang (kurangi nitrogen berlebih); perbaiki pengaturan air dan aerasi; buang sisa tanaman sakit; gunakan fungisida bila diperlukan sesuai rekomendasi (mis. fungisida berbahan aktif yang dianjurkan untuk blas).', 'Tanam varietas tahan; tanam serempak; jarak tanam cukup; sanitasi lahan; hindari kelembapan tinggi berkepanjangan; pemupukan seimbang; pengamatan rutin terutama fase vegetatif hingga bunting.'),
('P003', 'Bercak Cokelat (Brown Spot)', 'Bipolaris oryzae (syn. Helminthosporium oryzae)', 'Penyakit jamur yang ditandai bercak kecil cokelat pada daun, dapat melebar dengan bagian tengah lebih pucat. Umumnya parah pada tanaman yang kekurangan hara (terutama kalium/silikat) atau stres lingkungan, sehingga menurunkan kualitas dan hasil.', 'Perbaiki kesuburan tanah dan pemupukan berimbang (terutama K dan unsur mikro bila diperlukan); gunakan benih sehat; sanitasi sisa tanaman; jika serangan berat dapat menggunakan fungisida sesuai rekomendasi setempat.', 'Gunakan benih bermutu; perlakuan benih bila diperlukan; pemupukan berimbang; pengelolaan air baik; jaga kebersihan lahan; hindari kondisi stres tanaman (kekeringan/defisiensi hara).'),
('P004', 'Tungro', 'Rice tungro bacilliform virus (RTBV) & Rice tungro spherical virus (RTSV)', 'Penyakit virus pada padi yang menyebabkan daun menguning-oranye, tanaman kerdil, anakan berkurang, dan pertumbuhan terhambat. Penularan utama melalui wereng hijau (Nephotettix spp.), sehingga sering meningkat saat populasi vektor tinggi.', 'Cabut dan musnahkan tanaman sakit sedini mungkin; kendalikan wereng hijau (vektor) dengan pengendalian terpadu (PHT); tanam varietas tahan; tanam serempak dan kurangi sumber inokulum di sekitar lahan.', 'Tanam varietas tahan; tanam serempak; atur waktu tanam untuk memutus siklus vektor; monitoring dan pengendalian wereng hijau; sanitasi gulma/inang; gunakan bibit sehat dan hindari perpindahan bibit dari area terinfeksi.');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_deteksi`
--

CREATE TABLE `hasil_deteksi` (
  `id_deteksi` int(11) NOT NULL,
  `id_user` varchar(25) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `label_penyakit` varchar(100) NOT NULL,
  `confidence` float NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekam_akses_web`
--

CREATE TABLE `rekam_akses_web` (
  `id_rekam` int(11) NOT NULL,
  `id_user` varchar(20) DEFAULT NULL COMMENT 'ID pengguna jika sudah login (contoh: USER001)',
  `alamat_ip` varchar(45) NOT NULL COMMENT 'Alamat IP pengunjung',
  `agen_pengguna` text NOT NULL COMMENT 'User agent lengkap dari browser',
  `browser` varchar(50) NOT NULL COMMENT 'Nama browser (Chrome, Firefox, dll)',
  `sistem_operasi` varchar(50) NOT NULL COMMENT 'Sistem operasi pengguna (Windows, Android, dll)',
  `perangkat` varchar(30) NOT NULL COMMENT 'Jenis perangkat (Desktop / Mobile)',
  `tanggal_akses` date NOT NULL COMMENT 'Tanggal akses',
  `waktu_akses` time NOT NULL COMMENT 'Waktu akses (jam:menit:detik)',
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu data dicatat di server'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabel rekaman akses pengguna ke sistem';

--
-- Dumping data for table `rekam_akses_web`
--

INSERT INTO `rekam_akses_web` (`id_rekam`, `id_user`, `alamat_ip`, `agen_pengguna`, `browser`, `sistem_operasi`, `perangkat`, `tanggal_akses`, `waktu_akses`, `dibuat_pada`) VALUES
(1, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2025-12-17', '00:25:16', '2025-12-17 00:25:16'),
(2, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2025-12-31', '13:17:09', '2025-12-31 13:17:09'),
(3, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-06', '02:26:31', '2026-01-06 02:26:31'),
(4, 'USER003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '00:17:42', '2026-01-22 00:17:42'),
(5, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '00:17:52', '2026-01-22 00:17:52'),
(6, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:14:54', '2026-01-22 01:14:54'),
(7, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:16:07', '2026-01-22 01:16:07'),
(8, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:23:53', '2026-01-22 01:23:53'),
(9, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:29:56', '2026-01-22 01:29:56'),
(10, 'USER004', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:33:42', '2026-01-22 01:33:42'),
(11, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:35:12', '2026-01-22 01:35:12'),
(12, 'USER001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'Desktop', '2026-01-22', '01:37:05', '2026-01-22 01:37:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` varchar(25) NOT NULL,
  `nama_user` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jenis_kelamin` varchar(10) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` varchar(25) NOT NULL,
  `alamat` text NOT NULL,
  `img_user` varchar(25) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `email`, `username`, `password`, `jenis_kelamin`, `no_telp`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `img_user`, `updated_at`, `created_at`) VALUES
('USER001', 'Luluk Auliani', 'luluk@gmail.com', 'luluk1', '2c8ac5fd4a73a621f7c3e63992c979c7', 'Perempuan', '085173200421', 'Bandar Lampung', '2025-01-01', 'Bandar Lampung', '697109ed85b4c.jpg', '2025-12-06 04:27:15', '2025-12-06 04:27:15'),
('USER002', 'rahayu1', '', 'rahayu1', '8070fc22ccbd824ba15b95d03c394eb7', '', '', '', '', '', '', '2025-12-08 04:16:38', '2025-12-08 04:16:38'),
('USER003', 'testing1', 'testing1@gmail.com', 'testing1', '6b7330782b2feb4924020cc4a57782a9', 'Laki-laki', '08123451111', 'testing1', '2026-01-22', 'testing1', '69710a3063331.jpg', '2026-01-21 17:17:36', '2026-01-21 17:17:36'),
('USER004', 'rizky1', '', 'rizky1', '96ba210301f08a2eb677df096c3d48fe', '', '', '', '', '', '', '2026-01-21 18:33:37', '2026-01-21 18:33:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_penyakit`
--
ALTER TABLE `data_penyakit`
  ADD PRIMARY KEY (`id_penyakit`);

--
-- Indexes for table `hasil_deteksi`
--
ALTER TABLE `hasil_deteksi`
  ADD PRIMARY KEY (`id_deteksi`);

--
-- Indexes for table `rekam_akses_web`
--
ALTER TABLE `rekam_akses_web`
  ADD PRIMARY KEY (`id_rekam`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hasil_deteksi`
--
ALTER TABLE `hasil_deteksi`
  MODIFY `id_deteksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `rekam_akses_web`
--
ALTER TABLE `rekam_akses_web`
  MODIFY `id_rekam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
