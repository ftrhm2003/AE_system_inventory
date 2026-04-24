<?php 
require 'bawang_function.php';

$produksi = query_produksi("SELECT * FROM gudang1 ORDER BY waktu DESC");

if (isset($_POST["cari_produksi"])) {
    $keyword = $_POST["src_produksi"];
    $produksi = cari_produksi($keyword);
    $produk_total = cari_total_per_produk($keyword); 
} else {
    $produksi = query_produksi("SELECT * FROM gudang1 ORDER BY waktu DESC");
    $produk_total = total_per_produk();
}




// $produk_total = total_per_produk();

?>


 <!DOCTYPE html>
 <html>
 <head>
 	<meta charset="utf-8">
 	<meta name="viewport" content="width=device-width, initial-scale=1">
 	<title>ae system</title>
 	<link rel="stylesheet" type="text/css" href="style.css">
 </head>
 <body>


<div class="wrapper">
<h1 class="judul">Produk Bawang</h1>

<table class="table-container">
	<tr class="table-row header">
		<th class="col no"><a href="gudang.php">kembali</a></th>
		<th class="col total"><a href="bawang_tambah_produk.php">tambah produk</a></th>
		<th class="col total">
			<form action="" method="post">
			<input type="text" name="src_produksi" placeholder="cari data" autocomplete="off" size="">
			<button type="submit" name="cari_produksi">cari</button>
			</form>
		</th>
		<th class="col total"><a href="bawang_tambah_jumlah.php">tambah stock</a></th>
		<th class="col action"><a href="bawang_convert_excel.php" class="button">🔽 Export ke Excel</a></th>
	</tr>

	<tr class="table-row header">
		<th class="col no">No</th>
		<th class="col total">Nama produk</th>
		<th class="col total">Jumlah</th>
		<th class="col total">Waktu</th>
		<th class="col action">Aksi</th>
	</tr>

<?php $i = 1; ?>
<?php foreach ($produksi as $isi) { ?>
	<tr class="table-row">
		<td class="col no"><?php echo $i; ?></td>
		<td class="col total" ><?php echo $isi["produk"]; ?></td>
		<td class="col total"><?php echo $isi["jumlah"] ?> pcs</td>
		<td class="col total"><?php echo $isi["waktu"] ?></td>
		<td class="col action">
			<a href="bawang_ubah_produk.php?id=<?php echo $isi["id"]; ?>">ubah</a> /
			<a href="bawang_hapus_produk.php?id=<?php echo $isi["id"]; ?> " onclick="return confirm('yakin mau hapus?');">hapus</a>
		</td>
	</tr>
<?php $i++; ?>
<?php }; ?>


	<tr class="table-row header">
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
<?php }; ?>
	


</table>

</div>
</body>
</html>

