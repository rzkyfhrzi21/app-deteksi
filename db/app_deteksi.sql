-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 05:23 PM
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
  `id_penyakit` int(11) NOT NULL,
  `nm_penyakit` varchar(100) NOT NULL,
  `nm_latin_penyakit` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosa`
--

CREATE TABLE `diagnosa` (
  `id_penyakit` int(11) NOT NULL,
  `nm_penyakit` varchar(100) NOT NULL,
  `solusi_penyakit` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gejala`
--

CREATE TABLE `gejala` (
  `kd_gejala` varchar(10) NOT NULL,
  `nm_gejala` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `hasil_deteksi`
--

INSERT INTO `hasil_deteksi` (`id_deteksi`, `id_user`, `file_path`, `label_penyakit`, `confidence`, `catatan`, `created_at`) VALUES
(8, '1', 'uploads/deteksi/padi_1765119989.jpg', 'Tungro', 0.315314, '', '2025-12-07 22:06:29'),
(9, '1', 'uploads/deteksi/padi_1765120029.jpg', 'Brownspot', 0.661801, '', '2025-12-07 22:07:09'),
(11, '1', 'uploads/deteksi/padi_1765121519.jpg', 'Tungro', 0.791885, 'D', '2025-12-07 22:31:59'),
(12, '0', 'uploads/deteksi/padi_1765122941.jpg', 'Blast', 0.569969, '', '2025-12-07 22:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `kd_gejala` varchar(10) NOT NULL,
  `nm_gejala` varchar(255) NOT NULL,
  `id_penyakit` int(11) NOT NULL,
  `nm_penyakit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pertanyaan`
--

CREATE TABLE `pertanyaan` (
  `kd_gejala` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` varchar(15) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `email`, `username`, `password`, `jenis_kelamin`, `no_telp`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `img_user`, `created_at`, `updated_at`) VALUES
('USER001', 'Luluk Auliani', 'luluk@gmail.com', 'luluk1', 'fe9359201a4390b39ca893a460de0e6f', 'Perempuan', '085173200421', 'Bandar Lampung', '2025-01-01', 'Bandar Lampung', '69359dbfd90c5.jpg', '2025-12-06 04:27:15', '2025-12-06 04:27:15'),
('USER003', 'aaaaaaaaa', '', 'aaaaaaaaa', '552e6a97297c53e592208cf97fbb3b60', '', '', '', '', '', '', '2025-12-07 15:42:39', '2025-12-07 15:42:39'),
('USER021', 'Rizky Fahrezi', 'rizky01011991@gmail.com', 'rizky666', '87345ed882ed478bbb82752f8a9f7acf', 'Laki-laki', '085173200421', 'Tanjung Pinang', '2004-08-21', 'Natar', '69359ebd41d4c.jpg', '2025-12-07 15:35:25', '2025-12-07 15:35:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_penyakit`
--
ALTER TABLE `data_penyakit`
  ADD PRIMARY KEY (`id_penyakit`);

--
-- Indexes for table `diagnosa`
--
ALTER TABLE `diagnosa`
  ADD PRIMARY KEY (`id_penyakit`);

--
-- Indexes for table `gejala`
--
ALTER TABLE `gejala`
  ADD PRIMARY KEY (`kd_gejala`);

--
-- Indexes for table `hasil_deteksi`
--
ALTER TABLE `hasil_deteksi`
  ADD PRIMARY KEY (`id_deteksi`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`kd_gejala`,`id_penyakit`),
  ADD KEY `fk_konsultasi_penyakit` (`id_penyakit`);

--
-- Indexes for table `pertanyaan`
--
ALTER TABLE `pertanyaan`
  ADD PRIMARY KEY (`kd_gejala`);

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
  MODIFY `id_deteksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diagnosa`
--
ALTER TABLE `diagnosa`
  ADD CONSTRAINT `fk_diagnosa_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `data_penyakit` (`id_penyakit`) ON UPDATE CASCADE;

--
-- Constraints for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `fk_konsultasi_gejala` FOREIGN KEY (`kd_gejala`) REFERENCES `gejala` (`kd_gejala`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_konsultasi_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `data_penyakit` (`id_penyakit`) ON UPDATE CASCADE;

--
-- Constraints for table `pertanyaan`
--
ALTER TABLE `pertanyaan`
  ADD CONSTRAINT `fk_pertanyaan_gejala` FOREIGN KEY (`kd_gejala`) REFERENCES `gejala` (`kd_gejala`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
