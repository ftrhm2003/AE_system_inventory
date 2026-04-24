<?php
require 'kirim_luar_function.php';


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


$kirim_luar = mysqli_query($conn, "SELECT * FROM kirim_luar ORDER BY id DESC");
?>

<h2>Kirim barang ke luar</h2>

<form action="kirim_luar_function.php" method="post">
  <label>Nama Pemesan:</label>
  <input type="text" name="pemesan" required>
  <br><br>

  <table border="1" cellpadding="8" cellspacing="0">
    <thead>
      <tr>
        <th>Pilih</th>
        <th>Produk</th>
        <th>Stok</th>
        <th>Asal Gudang</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
          <td><input type="radio" name="produk" value="<?= $row['produk'] ?>|<?= $row['sumber'] ?>" required></td>
          <td><?= $row['produk'] ?></td>
          <td><?= $row['stok'] ?? 0 ?> pcs</td>
          <td><?= $row['sumber'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <br><br>
  <label>Harga (dalam angka):</label>
  <input type="number" name="harga" required>

  <br><br>
  <label>Jumlah:</label>
  <input type="number" name="jumlah" min="1" required>

  <br><br>
  <button type="submit">Kirim Produk</button>
</form>



