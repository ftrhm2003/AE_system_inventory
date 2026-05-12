<?php 
require 'toko_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=toko_kirim_export.xls");

$history = mysqli_query($conn, "
    SELECT * FROM toko_kirim
    WHERE jumlah > 0
    ORDER BY waktu DESC
");

$sqlStok = "
  SELECT produk, SUM(jumlah) AS stok, sumber FROM (
    SELECT produk, jumlah, 'gudang'  AS sumber FROM gudang
    UNION ALL
    SELECT produk, jumlah, 'gudang1' AS sumber FROM gudang1
    UNION ALL
    SELECT produk, jumlah, 'gudang2' AS sumber FROM gudang2
  ) AS semua
  GROUP BY produk, sumber
";
$resStok = mysqli_query($conn, $sqlStok);

$stocks = [];
if ($resStok) {
    while ($r = mysqli_fetch_assoc($resStok)) {
        $stocks[] = $r;
    }
}
$stock_count = count($stocks);

?>
<h2>Data Pengiriman ke toko</h2>
<table border="1">
    <tr>
        <th>No</th>
        <th>Produk</th>
        <th>Terkirim</th>
        <th>Harga</th>
        <th>Gudang asal</th>
        <th>Waktu</th>
    </tr>
    <?php $i=1; ?>
    <?php while ($r = mysqli_fetch_assoc($history)): ?>
    <tr>
        <td><?= $i; ?></td>
        <td><?= htmlspecialchars($r['produk']); ?></td>
        <td><?= $r['jumlah']; ?> pcs</td>
        <td>Rp <?= number_format($r["harga_jual"], 2, '.', '.'); ?></td>
        <td><?= htmlspecialchars($r['gudang_asal']); ?></td>
        <td><?= $r['waktu']; ?></td>
    </tr>
    <?php $i++; endwhile; ?>
</table>
