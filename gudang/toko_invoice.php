<?php
require 'toko_function.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID tidak valid');

list($header, $items) = getOrder($id);
if (!$header) die('Data order tidak ditemukan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice #<?= $id; ?></title>
<style>
body { font-family: Arial, sans-serif; background: #fff; margin: 0; padding: 20px; }
.wrapper { width: 800px; max-width: 100%; margin: auto; border: 2px solid #000; padding: 20px; border-radius: 10px; }
h1 { text-align: center; margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #000; padding: 8px; text-align: left; }
th { background: #f2f2f2; }
.total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
.print-btn { margin-top: 20px; padding: 10px 20px; font-size: 16px; cursor: pointer; }
@media print { .print-btn { display: none } .wrapper { border: none } }
</style>
</head>
<body>
<div class="wrapper">
  <h1>INVOICE TOKO</h1>
  <p><strong>No Invoice:</strong> INV-<?= str_pad($header['id'],5,'0',STR_PAD_LEFT); ?><br>
     <strong>Tanggal:</strong> <?= $header['waktu']; ?><br>
     <strong>Pembeli:</strong> <?= htmlspecialchars($header['pembeli']); ?></p>

  <table>
    <tr>
      <th>Produk</th>
      <th>Qty</th>
      <th>Harga/pcs</th>
      <th>Subtotal</th>
    </tr>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= htmlspecialchars($it['produk']); ?></td>
        <td><?= (int)$it['qty']; ?></td>
        <td>Rp <?= number_format($it["harga"], 2, '.', '.'); ?></td>
        <td>Rp <?= number_format($it["subtotal"], 2, '.', '.'); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p class="total">TOTAL: Rp <?= number_format($header["total"], 2, '.', '.'); ?></p>

  <button class="print-btn" onclick="window.print()">Cetak</button>
  <a class="print-btn" href="toko.php">Kembali ke Toko</a>
</div>
</body>
</html>
