<?php
require 'toko_function.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (hapusRiwayatPenjualan($id)) {
        header("Location: toko_riwayat_penjualan.php?hapus=berhasil");
    } else {
        header("Location: toko_riwayat_penjualan.php?hapus=gagal");
    }
} else {
    header("Location: toko_riwayat_penjualan.php");
}
