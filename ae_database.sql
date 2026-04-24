-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Jul 2025 pada 12.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Struktur dari tabel `gudang`
--

CREATE TABLE `gudang` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gudang`
--

INSERT INTO `gudang` (`id`, `produk`, `jumlah`, `waktu`) VALUES
(2, 'Singkong Sambalado', '100', '2025-07-14 17:39:00'),
(3, 'Singkong Sambalado', '200', '2025-07-14 17:42:00'),
(4, 'Singkong Keju', '100', '2025-07-14 17:51:00'),
(5, 'Singkong Sambalado', '-100', '2025-07-14 18:52:21'),
(6, 'Singkong Sambalado', '-100', '2025-07-14 18:58:48'),
(7, 'Singkong Keju', '-20', '2025-07-14 19:00:06'),
(8, 'Singkong Keju', '-20', '2025-07-14 19:14:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gudang1`
--

CREATE TABLE `gudang1` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gudang1`
--

INSERT INTO `gudang1` (`id`, `produk`, `jumlah`, `waktu`) VALUES
(2, 'keripik bawang 100g', '100', '2025-07-14 18:12:00'),
(3, 'keripik bawang 10g', '200', '2025-07-14 18:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gudang2`
--

CREATE TABLE `gudang2` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` varchar(100) DEFAULT NULL,
  `waktu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gudang2`
--

INSERT INTO `gudang2` (`id`, `produk`, `jumlah`, `waktu`) VALUES
(2, 'Nabati 100g', '100', '2025-07-14 18:21:00'),
(3, 'Rengginang 100g', '200', '2025-07-14 18:22:00'),
(4, 'Nabati 100g', '-20', '2025-07-14 19:11:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kirim_luar`
--

CREATE TABLE `kirim_luar` (
  `id` int(11) NOT NULL,
  `produk` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `jenis_produk` enum('gudang','gudang1','gudang2') DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kirim_luar`
--

INSERT INTO `kirim_luar` (`id`, `produk`, `jumlah`, `jenis_produk`, `waktu`) VALUES
(2, 'Singkong Sambalado', 100, 'gudang', '2025-07-14 18:58:48'),
(3, 'Singkong Keju', 20, 'gudang', '2025-07-14 19:00:06'),
(4, 'Nabati 100g', 20, 'gudang2', '2025-07-14 19:11:53'),
(5, 'Singkong Keju', 20, 'gudang', '2025-07-14 19:14:11');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `gudang`
--
ALTER TABLE `gudang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `gudang1`
--
ALTER TABLE `gudang1`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `gudang2`
--
ALTER TABLE `gudang2`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kirim_luar`
--
ALTER TABLE `kirim_luar`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `gudang`
--
ALTER TABLE `gudang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `gudang1`
--
ALTER TABLE `gudang1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `gudang2`
--
ALTER TABLE `gudang2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kirim_luar`
--
ALTER TABLE `kirim_luar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
