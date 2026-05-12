<?php
require 'toko_function.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_transfer'])) {

    if (empty($_POST['produk'])) {
        alertBack('Produk belum dipilih');
        exit;
    }

    $raw = $_POST['produk'];
    $produk = $raw; $gudang = null;
    if (strpos($raw,'|') !== false) {
        list($produk,$gudang) = explode('|',$raw,2);
    }

    $qty   = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    $harga = isset($_POST['harga'])  ? (int)preg_replace('/\D/','',$_POST['harga']) : 0;

    if ($gudang === null) { alertBack('Format produk tidak valid'); exit; }

    if (transferKeToko($produk,$gudang,$qty,$harga)) {
        echo "<script>alert('Stok berhasil dipindahkan ke toko');window.location.href='toko_history.php';</script>";
        exit;
    } else {
        alertBack('Transfer gagal');
    }
} else {
    header('Location: toko_transfer.php');
    exit;
}
