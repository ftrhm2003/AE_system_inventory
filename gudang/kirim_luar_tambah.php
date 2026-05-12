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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Kirim Produk</title>
<link rel="stylesheet" type="text/css" href="input.css">

</style>

<script src="https://unpkg.com/imask"></script>
</head>
<body>



<form action="kirim_luar_function.php" method="post">
	<div class="container">
    <div class="title">Input produk</div>
    <div class="form">
      <label for="produk">Pemesan</label>
      <div class="input-group">
        <input type="text" id="pemesan" name="pemesan" placeholder="isi pemesan" min="1" required>
      </div>

      <div class="table-container">
        <table>
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
      </div>
      <label for="harga">Harga</label>
      <div class="input-group">
        <input type="text" name="harga" id="harga" placeholder="isi harga" min="1" required>
      </div>
      
      <label for="jumlah">Jumlah</label>
      <div class="input-group">
        <input type="text" name="jumlah" id="jumlah" placeholder="isi jumlah" min="1" required>
      </div>

      <button type="submit" name="submit_kirim">Submit</button>
       <div style="text-align: center; margin-top: 10px;">
          <a href="kirim_luar.php">Batal</a>
       </div>
    </div>
  </div>
</form>
  
<script>
  var inputElement = document.getElementById('harga');

  var maskOptions = {
    mask: 'Rp num',
    lazy: false,
    blocks: {
      num: {
        mask: Number,
        thousandsSeparator: '.',
        radix: ',',
        mapToRadix: ['.']
      }
    }
  };

  var mask = IMask(inputElement, maskOptions);

  document.querySelector('form').addEventListener('submit', function(e) {
    var angkaMurni = mask.unmaskedValue; 
    inputElement.value = angkaMurni; 
  });
</script>


</body>
</html>





