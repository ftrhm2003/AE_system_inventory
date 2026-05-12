<?php 
require 'kirim_luar_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=kirim_luar_export.xls");

// Ambil data
$query = "
  SELECT produk, SUM(jumlah) as stok, sumber FROM (
    SELECT produk, jumlah, 'gudang' as sumber FROM gudang
    UNION ALL
    SELECT produk, jumlah, 'gudang1' as sumber FROM gudang1
    UNION ALL
    SELECT produk, jumlah, 'gudang2' as sumber FROM gudang2
  ) as semua
  GROUP BY produk, sumber
";
$result = mysqli_query($conn, $query);

$kirim_luar = mysqli_query($conn, "
    SELECT id, pemesan, produk, jumlah, harga, 
           (jumlah * harga) AS total_penjualan, 
           jenis_produk, waktu
    FROM kirim_luar
    ORDER BY id DESC
");

?>
<h2>Data Pengiriman Barang</h2>
<table border="1">
    <tr>
        <th>No</th>
        <th>Pemesan</th>
        <th>Produk</th>
        <th>Terkirim</th>
        <th>Harga</th>
        <th>Penjualan</th>
        <th>Waktu</th>
    </tr>
    <?php $i = 1; foreach ($kirim_luar as $row): ?>
    <tr>
        <td><?= $i++; ?></td>
        <td><?= $row['pemesan']; ?></td>
        <td><?= $row['produk']; ?></td>
        <td><?= $row['jumlah']; ?> pcs</td>
        <td>Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
        <td>Rp <?= number_format($row["total_penjualan"], 2, '.', '.'); ?></td>
        <td><?= $row['waktu']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
