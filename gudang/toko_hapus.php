<?php
require 'toko_function.php';

$id = (int)($_GET['id'] ?? 0);

if (hapusStokToko($id)) {
    echo "<script>alert('Data berhasil dihapus');location.href='toko_history.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data');location.href='toko_history.php';</script>";
}
