<?php 
require 'luar_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=luar_produksi_export.xls");

// Ambil data
$produksi = query_produksi("SELECT * FROM produksi1 ORDER BY waktu DESC");
$stok_total = total_per_produk();
?>

<!-- TABEL 1: Riwayat Produksi -->
<h2>Riwayat Produksi Barang</h2>
<table border="1">
    <tr>
        <th>No</th>
        <th>Nama Produk</th>
        <th>Jumlah</th>
        <th>Harga/pcs</th>
        <th>Waktu</th>
    </tr>
    <?php $i = 1; foreach ($produksi as $row): ?>
    <tr>
        <td><?= $i++; ?></td>
        <td><?= htmlspecialchars($row['produk']); ?></td>
        <td><?= $row['jumlah']; ?></td>
        <td>Rp <?php echo number_format($row["harga"], 2, '.', '.'); ?></td>
        <td><?= $row['waktu']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<br><br>

<!-- TABEL 2: Total Stok Barang -->
<h2>Total Stok Barang</h2>
<table border="1">
    <tr>
        <th>No</th>
        <th>Nama Produk</th>
        <th>Total Jumlah</th>
        <th>Harga/pcs</th>
        <th>Harga total</th>
        <th>Keuntungan</th>
    </tr>
    <?php $i = 1; foreach ($stok_total as $row): ?>
    <tr>
        <td><?= $i++; ?></td>
        <td><?= htmlspecialchars($row['produk']); ?></td>
        <td><?= $row['total_jumlah']; ?> pcs</td>
        <td>Rp <?php echo number_format($row["harga"], 2, '.', '.'); ?></td>
        <td>Rp <?php echo number_format($row["total_harga"], 2, '.', '.'); ?></td>
        <td>Rp <?php echo number_format($row['keuntungan'], 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
