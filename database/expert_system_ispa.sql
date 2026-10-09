-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2026 at 12:16 PM
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
-- Database: `expert_system_ispa`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_gejala`
--

CREATE TABLE `tb_gejala` (
  `id_gejala` int(11) NOT NULL,
  `kode_gejala` varchar(10) NOT NULL,
  `nama_gejala` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_gejala`
--

INSERT INTO `tb_gejala` (`id_gejala`, `kode_gejala`, `nama_gejala`, `created_at`, `updated_at`) VALUES
(1, 'G01', 'Flu atau Bersin-bersin', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(2, 'G02', 'Tenggorokan kering', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(3, 'G03', 'Demam tinggi atau menggigil', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(4, 'G04', 'Hidung tersumbat', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(5, 'G05', 'Suara serak', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(6, 'G06', 'Suara hilang', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(7, 'G07', 'Batuk', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(8, 'G08', 'Sakit tenggorokan', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(9, 'G09', 'Sulit menelan', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(10, 'G10', 'Nyeri saat menelan', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(11, 'G11', 'Demam', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(12, 'G12', 'Sesak napas atau tarikan napas lebih berat', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(13, 'G13', 'Napas berbunyi (Stridor)', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(14, 'G14', 'Batuk berdahak', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(15, 'G15', 'Napas berbunyi mengi (Wheezing)', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(16, 'G16', 'Napas lebih cepat dari biasanya', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(17, 'G17', 'Tampak rewel atau lemas (Letargi)', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(18, 'G18', 'Nafsu makan menurun', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(19, 'G19', 'Tarikan dinding dada bagian bawah lebih dalam', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(20, 'G20', 'Sulit minum atau menyusu', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(21, 'G21', 'Bibir atau kuku kebiruan (Sianosis)', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(22, 'G22', 'Penurunan Kesadaran (Delirium)', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(23, 'G23', 'Kejang', '2026-07-29 10:12:37', '2026-07-29 12:28:43'),
(24, 'G24', 'Nyeri dada saat batuk', '2026-07-29 10:12:37', '2026-07-29 12:28:43');

-- --------------------------------------------------------

--
-- Table structure for table `tb_hasil_diagnosis`
--

CREATE TABLE `tb_hasil_diagnosis` (
  `id_hasil` int(11) NOT NULL,
  `id_konsultasi` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_penyakit` int(11) NOT NULL,
  `nilai_cf` decimal(5,4) NOT NULL,
  `persentase` decimal(5,2) NOT NULL,
  `tanggal_diagnosis` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_hasil_diagnosis`
--

INSERT INTO `tb_hasil_diagnosis` (`id_hasil`, `id_konsultasi`, `id_user`, `id_penyakit`, `nilai_cf`, `persentase`, `tanggal_diagnosis`) VALUES
(1, 1, 2, 1, 0.4000, 40.00, '2026-08-01 16:55:03'),
(2, 3, 2, 1, 1.0000, 100.00, '2026-08-01 16:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `tb_konsultasi`
--

CREATE TABLE `tb_konsultasi` (
  `id_konsultasi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_konsultasi`
--

INSERT INTO `tb_konsultasi` (`id_konsultasi`, `id_user`, `tanggal`) VALUES
(1, 2, '2026-08-01 16:55:03'),
(2, 2, '2026-08-01 16:55:30'),
(3, 2, '2026-08-01 16:56:14'),
(4, 2, '2026-08-01 16:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `tb_penyakit`
--

CREATE TABLE `tb_penyakit` (
  `id_penyakit` int(11) NOT NULL,
  `kode_penyakit` varchar(10) NOT NULL,
  `nama_penyakit` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `gejala_umum` text DEFAULT NULL,
  `penanganan` text NOT NULL,
  `gambar` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_penyakit`
--

INSERT INTO `tb_penyakit` (`id_penyakit`, `kode_penyakit`, `nama_penyakit`, `deskripsi`, `gejala_umum`, `penanganan`, `gambar`, `created_at`) VALUES
(1, 'P01', 'Laringitis', 'Laringitis adalah suatu kondisi peradangan yang terjadi pada laring atau pita suara, yang bisa bersifat akut atau kronis. Penyebab umum dari kondisi ini adalah infeksi virus pada saluran pernapasan bawah, tetapi bisa juga disebabkan oleh bakteri, penggunaan suara yang intens, asap rokok, polusi, alergi, dan iritasi akibat bahan kimia.', 'Batuk, Demam, Sesak Napas, Nyeri Dada.', '1.Kurangi aktivitas yang menyebabkan anak banyak berbicara, menangis, atau berteriak agar pita suara tidak semakin teriritasi.\r\n2.Berikan ASI, susu, atau air minum sesuai usia secara cukup untuk membantu menjaga kelembapan tenggorokan dan mencegah dehidrasi. Cairan yang cukup juga membantu meredakan iritasi pada saluran napas\r\n3.Gunakan humidifier apabila udara di dalam ruangan kering, udara yang lembap dapat membantu mengurangi iritasi pada laring dan membuat anak lebih nyaman saat bernapas. Apabila tidak tersedia humidifier, orang tua dapat menjaga kelembapan ruangan dengan cara lain yang aman.\r\n4.Hindari paparan asap rokok, asap rokok dan iritan lainnya dapat memperparah peradangan pada pita suara serta memperlambat proses penyembuhan.\r\n5.Untuk anak yang sudah mendapatkan makanan pendamping ASI, makanan bertekstur lunak dan minuman hangat dapat membantu mengurangi rasa tidak nyaman pada tenggorokan.\r\n6.Apabila anak mengalami demam, dapat diberikan Paracetamol sesuai usia dan berat badan dengan mengikuti aturan dosis pada kemasan atau sesuai anjuran tenaga kesehatan. Untuk anak berusia di atas 6 bulan, Ibuprofen juga dapat digunakan sesuai dosis yang dianjurkan. \r\n', 'laringitis.png', '2026-07-29 07:44:25'),
(2, 'P02', 'Bronkitis', 'Bronkitis adalah inflamasi/peradangan pada saluran pernapasan utama (bronkus), yang berfungsi menghubungkan trakea/batang tenggorokan dengan paru-paru. Pada kondisi ini terjadi iritasi, pembengkakan, dan peningkatan produksi lendir yang dapat mengganggu proses pernapasan.  Penyebab dari bronkitis adalah virus seperti, virus Influenza, Rhinovirus, Coronavirus, infeksi Bakteri seperti Mycoplasma Pneumoniae dan Bordatella Pertusiss.', 'Batuk berdahak, Demam, Sesak Napas.', '1.Berikan ASI, susu, atau air putih sesuai usia untuk membantu mengencerkan dahak, menjaga tubuh tetap terhidrasi, dan mencegah dehidrasi akibat demam maupun batuk. Cairan yang cukup juga dapat membantu meredakan iritasi pada saluran pernapasan.\r\n2.Berikan istirahat yang cukup, istirahat membantu meningkatkan daya tahan tubuh sehingga proses penyembuhan infeksi dapat berlangsung lebih optimal. \r\n3.Hindari paparan asap rokok, asap kendaraan, debu, maupun polusi udara karena dapat memperburuk peradangan pada saluran bronkus dan meningkatkan frekuensi batuk.\r\n4.Gunakan humidifier apabila udara di dalam ruangan kering, udara yang lembap dapat membantu mengurangi iritasi saluran napas dan membuat anak lebih nyaman saat bernapas maupun batuk..\r\n5.Gunakan larutan saline (NaCl 0,9%) apabila hidung tersumbat, larutan saline yang tersedia di apotek dapat digunakan untuk membantu mengencerkan lendir pada hidung sehingga anak lebih nyaman bernapas, terutama sebelum makan atau menyusu.\r\n6.Apabila anak mengalami demam, dapat diberikan Paracetamol sesuai usia dan berat badan dengan mengikuti aturan dosis pada kemasan atau sesuai anjuran tenaga kesehatan. Untuk anak berusia di atas 6 bulan, Ibuprofen juga dapat digunakan sesuai dosis yang dianjurkan.\r\n', 'bronkitis.png', '2026-07-29 07:44:25'),
(3, 'P03', 'Bronkiolitis', 'Bronkiolitis adalah kondisi di mana saluran napas kecil mengalami peradangan atau penyumbatan yang biasanya disebabkan oleh infeksi respiratory syncytial virus (RSV) yang merupakan infeksi terbanyak, khususnya pada bayi dan anak kecil. Penyakit ini masuk dalam kategori infeksi saluran pernapasan bagian bawah yang umum terjadi selama tahun pertama kehidupan. Selain dari RSV, bronkiolitis dapat juga disebabkan oleh virus lain seperti influenza, adenovirus, coronavirus, parainfluenza, dan metapneumovirus.', 'Pilek, Wheezing, Sesak Napas.', '1.Berikan ASI, susu, atau cairan sesuai usia dalam jumlah yang cukup. Apabila anak sulit minum dalam satu kali pemberian, berikan sedikit demi sedikit tetapi lebih sering untuk mencegah dehidrasi.\r\n2.Gunakan larutan saline (NaCl 0,9%) dan alat penyedot lendir (nasal aspirator) bila diperlukan untuk membantu membersihkan sekret hidung sehingga anak lebih mudah bernapas, terutama sebelum menyusu atau makan.\r\n3.Istirahat membantu meningkatkan daya tahan tubuh sehingga proses pemulihan berlangsung lebih optimal.\r\n4.Hindari paparan asap rokok, asap kendaraan, debu, dan polusi udara karena dapat memperberat gangguan pada saluran pernapasan.\r\n5.Gunakan humidifier apabila udara di dalam ruangan kering, udara yang lembap dapat membantu menjaga kenyamanan saluran pernapasan, meskipun penggunaannya harus tetap memperhatikan kebersihan alat agar tidak menjadi sumber infeksi.\r\n6.Apabila anak mengalami demam, dapat diberikan Paracetamol sesuai usia dan berat badan dengan mengikuti aturan dosis pada kemasan atau sesuai anjuran tenaga kesehatan. Untuk anak berusia di atas 6 bulan, Ibuprofen juga dapat digunakan sesuai dosis yang dianjurkan.\r\n', 'bronkiolitis.png', '2026-07-29 07:44:25'),
(9, 'P04', 'Pneumonia', 'fdjgkfjgjdf', 'sdjsjosk', '2454646', '1785386366_6a6ad57e2a4a4.png', '2026-07-30 04:39:26');

-- --------------------------------------------------------

--
-- Table structure for table `tb_riwayat_diagnosis`
--

CREATE TABLE `tb_riwayat_diagnosis` (
  `id_riwayat` int(11) NOT NULL,
  `id_hasil` int(11) NOT NULL,
  `id_gejala` int(11) NOT NULL,
  `cf_user` decimal(3,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_rule`
--

CREATE TABLE `tb_rule` (
  `id_rule` int(11) NOT NULL,
  `kode_rule` varchar(5) NOT NULL,
  `id_penyakit` int(11) NOT NULL,
  `cf_rule` decimal(3,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_rule`
--

INSERT INTO `tb_rule` (`id_rule`, `kode_rule`, `id_penyakit`, `cf_rule`, `created_at`, `updated_at`) VALUES
(1, 'R1', 1, 1.00, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(2, 'R2', 1, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(3, 'R3', 1, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(4, 'R4', 1, 0.60, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(5, 'R5', 1, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(6, 'R6', 2, 0.60, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(7, 'R7', 2, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(8, 'R8', 2, 1.00, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(9, 'R9', 2, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(10, 'R10', 2, 0.60, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(11, 'R11', 3, 0.60, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(12, 'R12', 3, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(13, 'R13', 3, 1.00, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(14, 'R14', 3, 0.80, '2026-07-29 14:59:46', '2026-07-29 14:59:46'),
(15, 'R15', 3, 1.00, '2026-07-29 14:59:46', '2026-07-29 14:59:46');

-- --------------------------------------------------------

--
-- Table structure for table `tb_rule_detail`
--

CREATE TABLE `tb_rule_detail` (
  `id_detail` int(11) NOT NULL,
  `id_rule` int(11) NOT NULL,
  `id_gejala` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_rule_detail`
--

INSERT INTO `tb_rule_detail` (`id_detail`, `id_rule`, `id_gejala`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(2, 1, 5, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(3, 1, 6, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(4, 2, 5, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(5, 2, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(6, 2, 8, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(7, 2, 9, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(8, 3, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(9, 3, 10, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(10, 3, 11, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(11, 3, 13, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(12, 4, 1, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(13, 4, 3, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(14, 4, 4, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(15, 5, 3, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(16, 5, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(17, 5, 24, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(18, 6, 1, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(19, 6, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(20, 6, 11, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(21, 7, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(22, 7, 11, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(23, 7, 14, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(24, 8, 14, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(25, 8, 15, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(26, 8, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(27, 9, 15, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(28, 9, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(29, 9, 17, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(30, 9, 18, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(31, 10, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(32, 10, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(33, 10, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(34, 11, 1, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(35, 11, 4, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(36, 11, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(37, 12, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(38, 12, 11, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(39, 12, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(40, 13, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(41, 13, 15, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(42, 13, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(43, 14, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(44, 14, 17, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(45, 14, 18, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(46, 15, 19, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(47, 15, 20, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(48, 15, 21, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(49, 16, 3, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(50, 16, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(51, 16, 11, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(52, 17, 7, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(53, 17, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(54, 17, 15, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(55, 17, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(56, 18, 16, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(57, 18, 17, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(58, 18, 18, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(59, 18, 19, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(60, 19, 19, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(61, 19, 20, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(62, 19, 21, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(63, 20, 12, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(64, 20, 19, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(65, 20, 22, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(66, 21, 19, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(67, 21, 21, '2026-07-29 14:41:56', '2026-07-29 14:41:56'),
(68, 21, 23, '2026-07-29 14:41:56', '2026-07-29 14:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default.png',
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `email`, `password`, `no_hp`, `alamat`, `foto`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin@gmail.com', 'admin123', '08111111111', 'Jakarta', 'default.png', 'admin', '2026-07-29 07:40:50'),
(2, 'Dean', 'dean', 'dean123@gmail.com', 'user123', '081234567890', 'Tangerang Selatan', 'user_6a6a469888753.png', 'user', '2026-07-29 15:17:11'),
(3, 'dean billal', 'deanbilal8@gmail.com', 'deanbilal8@gmail.com', '$2y$10$XLau.WldczxtFtBEBAjqbO9OyuaSTMSn00MXfH3s7n/gUfjD5m9La', NULL, NULL, 'default.png', 'user', '2026-07-31 08:36:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_gejala`
--
ALTER TABLE `tb_gejala`
  ADD PRIMARY KEY (`id_gejala`),
  ADD UNIQUE KEY `kode_gejala` (`kode_gejala`),
  ADD UNIQUE KEY `kode_gejala_2` (`kode_gejala`);

--
-- Indexes for table `tb_hasil_diagnosis`
--
ALTER TABLE `tb_hasil_diagnosis`
  ADD PRIMARY KEY (`id_hasil`);

--
-- Indexes for table `tb_konsultasi`
--
ALTER TABLE `tb_konsultasi`
  ADD PRIMARY KEY (`id_konsultasi`);

--
-- Indexes for table `tb_penyakit`
--
ALTER TABLE `tb_penyakit`
  ADD PRIMARY KEY (`id_penyakit`),
  ADD UNIQUE KEY `kode_penyakit` (`kode_penyakit`);

--
-- Indexes for table `tb_riwayat_diagnosis`
--
ALTER TABLE `tb_riwayat_diagnosis`
  ADD PRIMARY KEY (`id_riwayat`);

--
-- Indexes for table `tb_rule`
--
ALTER TABLE `tb_rule`
  ADD PRIMARY KEY (`id_rule`),
  ADD UNIQUE KEY `kode_rule` (`kode_rule`),
  ADD KEY `fk_rule_penyakit` (`id_penyakit`);

--
-- Indexes for table `tb_rule_detail`
--
ALTER TABLE `tb_rule_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD UNIQUE KEY `id_rule` (`id_rule`,`id_gejala`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_gejala`
--
ALTER TABLE `tb_gejala`
  MODIFY `id_gejala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tb_hasil_diagnosis`
--
ALTER TABLE `tb_hasil_diagnosis`
  MODIFY `id_hasil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_konsultasi`
--
ALTER TABLE `tb_konsultasi`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_penyakit`
--
ALTER TABLE `tb_penyakit`
  MODIFY `id_penyakit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_riwayat_diagnosis`
--
ALTER TABLE `tb_riwayat_diagnosis`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_rule`
--
ALTER TABLE `tb_rule`
  MODIFY `id_rule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tb_rule_detail`
--
ALTER TABLE `tb_rule_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_rule`
--
ALTER TABLE `tb_rule`
  ADD CONSTRAINT `fk_rule_penyakit` FOREIGN KEY (`id_penyakit`) REFERENCES `tb_penyakit` (`id_penyakit`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
