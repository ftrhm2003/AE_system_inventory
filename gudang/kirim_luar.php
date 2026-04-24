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

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>kirim ke luar</title>

  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="wrapper">
<h1 class="judul">Produk Singkong</h1>

<table class="table-container">
  <tr class="table-row header">
    <th class="col no"><a href="kirim.php">Kembali</a></th>
    <th class="col total"><a href="kirim_luar_tambah.php">Kirim produk</a></th>
    <th class="col total">
      <form action="" method="post">
      <input type="text" name="src_produksi" placeholder="cari data" autocomplete="off" size="">
      <button type="submit" name="cari_produksi">cari</button>
      </form>
    </th>
    <th class="col total"><a href=""></a></th>
     <th class="col total"><a href=""></a></th>
      <th class="col total"><a href=""></a></th>
    <th class="col action"><a href="" class="button">🔽 Export ke Excel</a></th>
  </tr>

  <tr class="table-row header">
    <th class="col no"></th>
    <th class="col total"></th>
    <th class="col total">Produk</th>
    <th class="col total">Stok</th>
    <th class="col total">Asal gudang</th>
    <th class="col total"></th>
    <th class="col action"></th>
  </tr>

  <tr class="table-row header">
    <th class="col no">No</th>
    <th class="col total">Pemesan</th>
    <th class="col total">Produk</th>
    <th class="col total">Terkirim</th>
    <th class="col total">Harga</th>
    <th class="col total">Jenis Produk</th>
    <th class="col action">Waktu</th>
  </tr>

<?php $i = 1; ?>
<?php while ($row = mysqli_fetch_assoc($kirim_luar)) : ?>
  <tr class="table-row">
    <td class="col no"><?php echo $i; ?></td>
    <td class="col total" ><?= $row['pemesan']; ?></td>
    <td class="col total"><?= $row['produk']; ?></td>
    <td class="col total"><?= $row['jumlah']; ?> pcs</td>
    <td class="col total">Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
    <td class="col total"><?= $row['jenis_produk']; ?></td>
    <td class="col action"><?= $row['waktu']; ?></td>
  </tr>
<?php $i++; ?>
 <?php endwhile; ?>


  <!-- <tr class="table-row header">
    <th class="col no">No</th>
    <th class="col total">Nama produk</th>
    <th class="col total">Total jumlah</th>
    <th class="col total"></th>
    <th class="col action"></th>
  </tr>


<?php $i = 1; ?>
<?php foreach ($produk_total as $row) { ?>
  <tr class="table-row">
    <td class="col no"><?php echo $i; ?></td>
    <td class="col total" ><?php echo htmlspecialchars($row['produk']); ?></td>
    <td class="col total"><?php echo $row['total_jumlah']; ?> pcs</td>
    <td class="col total"></td>
    <td class="col action"></td>
  </tr>
<?php $i++; ?>
<?php }; ?> -->
  


</table>



</div>

</body>
</html>
