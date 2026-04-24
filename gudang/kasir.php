<?php
require 'function.php';

// Ambil semua produk dari 3 tabel
$query = "
  SELECT produk, SUM(jumlah) as stok, harga, 'produksi' as sumber FROM produksi GROUP BY produk
  UNION ALL
  SELECT produk, SUM(jumlah) as stok, harga, 'produksi1' as sumber FROM produksi1 GROUP BY produk
  UNION ALL
  SELECT produk, SUM(jumlah) as stok, harga, 'produksi2' as sumber FROM produksi2 GROUP BY produk
";
$result = mysqli_query($conn, $query);

// Ambil data riwayat penjualan
$penjualan = mysqli_query($conn, "SELECT * FROM penjualan ORDER BY id DESC");
?>

<h2>Transaksi Kasir</h2>

<form action="function.php" method="post">
  <label>Pilih Produk:</label>
  <select name="produk">
    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
      <?php if ($row['stok'] > 0): ?>
        <option value="<?= $row['produk'] ?>|<?= $row['sumber'] ?>">
          <?= $row['produk'] ?> (<?= $row['stok'] ?> stok)
        </option>
      <?php endif; ?>
    <?php endwhile; ?>
  </select>

  <br><br>
  <label>Jumlah:</label>
  <input type="number" name="jumlah" min="1" required>

  <br><br>
  <button type="submit">Jual Sekarang</button>
</form>

<hr>

<h3>Riwayat Penjualan</h3>
<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>No</th>
    <th>Produk</th>
    <th>Jumlah</th>
    <th>Harga</th>
    <th>Subtotal</th>
    <th>Jenis Produk</th>
  </tr>

  <?php $no = 1; ?>
  <?php while ($row = mysqli_fetch_assoc($penjualan)) : ?>
    <tr>
      <td><?= $no++; ?></td>
      <td><?= $row['produk']; ?></td>
      <td><?= $row['jumlah']; ?> pcs</td>
      <td><?= $row['harga']; ?></td>
      <td>Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
      <td><?= $row['jenis_produk']; ?></td>
    </tr>
  <?php endwhile; ?>
</table>
