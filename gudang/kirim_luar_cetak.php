<?php
require 'kirim_luar_function.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID tidak valid");
}

$q = mysqli_query($conn, "SELECT * FROM kirim_luar WHERE id = $id");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Data tidak ditemukan");
}

// Normalisasi angka
$harga  = isset($data['harga']) ? (int)$data['harga'] : 0;
$jumlah = isset($data['jumlah']) ? (int)$data['jumlah'] : 0;

// Ambil total dari DB jika ada
$total_penjualan = isset($data['total_penjualan']) ? (int)$data['total_penjualan'] : 0;

// Fallback hitung ulang jika kosong / nol
if ($total_penjualan <= 0) {
    $total_penjualan = $harga * $jumlah;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice Pengiriman</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body{font-family:Arial,sans-serif;background:#fff;margin:0;padding:20px;}
    .invoice-container{width:800px;max-width:100%;margin:auto;padding:20px;border:2px solid #000;border-radius:10px;}
    h1{text-align:center;margin-bottom:10px;}
    .header{display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;}
    .invoice-info{font-size:14px;}
    h3{margin-top:0;}
    table{width:100%;border-collapse:collapse;margin-top:20px;font-size:15px;}
    th,td{border:1px solid #000;padding:10px;text-align:left;}
    th{background:#f2f2f2;}
    .total{text-align:right;font-size:18px;font-weight:bold;margin-top:20px;}
    .footer{text-align:center;font-size:14px;margin-top:30px;}
    .btn-print{display:block;width:100%;padding:10px;margin-top:20px;background:#000;color:#fff;font-size:16px;cursor:pointer;border:none;border-radius:5px;}
    .btn-print:hover{background:#444;}
    @media print{.btn-print{display:none;}}
</style>
</head>
<body>
<div class="invoice-container">
    <h1>INVOICE PENGIRIMAN</h1>

    <div class="header">
        <div class="invoice-info">
            <strong>No Invoice:</strong> INV-<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?><br>
            <strong>Tanggal:</strong> <?= date('d-m-Y H:i', strtotime($data['waktu'])); ?><br>
        </div>
    </div>

    <h3>Data Pemesan</h3>
    <p><strong>Nama:</strong> <?= htmlspecialchars($data['pemesan']); ?></p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($data['produk']); ?></td>
                <td><?= $jumlah; ?> pcs</td>
                <td>Rp <?= number_format($harga, 2, '.', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <p class="total">TOTAL: Rp <?= number_format($total_penjualan, 2, '.', '.'); ?></p>

    <div class="footer">
        Terima kasih telah memesan!<br>
        Barang akan segera dikirim sesuai jadwal.
    </div>

    <button class="btn-print" onclick="window.print()">🖨 Cetak Invoice</button>
</div>
</body>
</html>
