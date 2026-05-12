-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 06:06 AM
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
-- Database: `ae_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `gudang`
--

CREATE TABLE `gudang` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gudang`
--

INSERT INTO `gudang` (`id`, `produk`, `jumlah`, `waktu`) VALUES
(31, 'Singkong Keju', '70', '2026-05-12 10:57:00'),
(32, 'Singkong Keju', '-20', '2026-05-12 11:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `gudang1`
--

CREATE TABLE `gudang1` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gudang2`
--

CREATE TABLE `gudang2` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kirim_luar`
--

CREATE TABLE `kirim_luar` (
  `id` int(11) NOT NULL,
  `pemesan` varchar(100) DEFAULT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` int(12) DEFAULT NULL,
  `jenis_produk` enum('gudang','gudang1','gudang2') DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_gudang`
--

CREATE TABLE `log_gudang` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_gudang1`
--

CREATE TABLE `log_gudang1` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_gudang2`
--

CREATE TABLE `log_gudang2` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_hapus_penjualan`
--

CREATE TABLE `log_hapus_penjualan` (
  `id` int(11) NOT NULL,
  `aksi` varchar(50) NOT NULL,
  `order_id` int(11) NOT NULL,
  `pembeli` varchar(100) NOT NULL,
  `total` int(11) NOT NULL,
  `waktu_transaksi` datetime NOT NULL,
  `detail_produk` text NOT NULL,
  `waktu_hapus` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_kirim_luar`
--

CREATE TABLE `log_kirim_luar` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `pemesan` varchar(255) DEFAULT NULL,
  `produk` varchar(255) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_kirim_toko`
--

CREATE TABLE `log_kirim_toko` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `produk` varchar(255) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  `gudang_asal` varchar(20) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL,
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_toko_price`
--

CREATE TABLE `log_toko_price` (
  `id` int(11) NOT NULL,
  `aksi` enum('HAPUS','UBAH') DEFAULT NULL,
  `produk` varchar(255) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data_lama` text DEFAULT NULL,
  `data_baru` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toko_kirim`
--

CREATE TABLE `toko_kirim` (
  `id` int(11) NOT NULL,
  `produk` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  `gudang_asal` varchar(20) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toko_kirim`
--

INSERT INTO `toko_kirim` (`id`, `produk`, `jumlah`, `harga_jual`, `gudang_asal`, `waktu`) VALUES
(86, 'Singkong Keju', 20, 40, 'gudang', '2026-05-12 11:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `toko_order`
--

CREATE TABLE `toko_order` (
  `id` int(11) NOT NULL,
  `pembeli` varchar(100) NOT NULL,
  `total` int(11) NOT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toko_order_items`
--

CREATE TABLE `toko_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `produk` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toko_price`
--

CREATE TABLE `toko_price` (
  `produk` varchar(255) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toko_price`
--

INSERT INTO `toko_price` (`produk`, `harga_jual`, `updated_at`) VALUES
('Singkong Keju', 40, '2026-05-12 11:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `toko_stock`
--

CREATE TABLE `toko_stock` (
  `id` int(11) NOT NULL,
  `produk` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_jual` int(11) DEFAULT NULL,
  `gudang_asal` varchar(20) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toko_stock`
--

INSERT INTO `toko_stock` (`id`, `produk`, `jumlah`, `harga_jual`, `gudang_asal`, `waktu`) VALUES
(81, 'Singkong Keju', 20, 40, 'gudang', '2026-05-12 11:00:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gudang`
--
ALTER TABLE `gudang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gudang1`
--
ALTER TABLE `gudang1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gudang2`
--
ALTER TABLE `gudang2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kirim_luar`
--
ALTER TABLE `kirim_luar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_gudang`
--
ALTER TABLE `log_gudang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_gudang1`
--
ALTER TABLE `log_gudang1`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_gudang2`
--
ALTER TABLE `log_gudang2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_hapus_penjualan`
--
ALTER TABLE `log_hapus_penjualan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_kirim_luar`
--
ALTER TABLE `log_kirim_luar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_kirim_toko`
--
ALTER TABLE `log_kirim_toko`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_toko_price`
--
ALTER TABLE `log_toko_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toko_kirim`
--
ALTER TABLE `toko_kirim`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toko_order`
--
ALTER TABLE `toko_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toko_order_items`
--
ALTER TABLE `toko_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `toko_price`
--
ALTER TABLE `toko_price`
  ADD PRIMARY KEY (`produk`);

--
-- Indexes for table `toko_stock`
--
ALTER TABLE `toko_stock`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gudang`
--
ALTER TABLE `gudang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `gudang1`
--
ALTER TABLE `gudang1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `gudang2`
--
ALTER TABLE `gudang2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `kirim_luar`
--
ALTER TABLE `kirim_luar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `log_gudang`
--
ALTER TABLE `log_gudang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `log_gudang1`
--
ALTER TABLE `log_gudang1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `log_gudang2`
--
ALTER TABLE `log_gudang2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `log_hapus_penjualan`
--
ALTER TABLE `log_hapus_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `log_kirim_luar`
--
ALTER TABLE `log_kirim_luar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `log_kirim_toko`
--
ALTER TABLE `log_kirim_toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `log_toko_price`
--
ALTER TABLE `log_toko_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `toko_kirim`
--
ALTER TABLE `toko_kirim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `toko_order`
--
ALTER TABLE `toko_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `toko_order_items`
--
ALTER TABLE `toko_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `toko_stock`
--
ALTER TABLE `toko_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `toko_order_items`
--
ALTER TABLE `toko_order_items`
  ADD CONSTRAINT `toko_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `toko_order` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
