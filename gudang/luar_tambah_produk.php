<?php 
require 'luar_function.php';

if (isset($_POST["submit"])) {
	if (tambah_produksi($_POST) > 0) {
		echo "
			<script>
				alert('data berhasil ditambahkan');
				document.location.href = 'luar_produksi.php';
			</script>
		";
	} else {
		echo "
			<script>
				alert('data gagal ditambahkan');
				document.location.href = 'luar_produksi.php';
			</script>
		";
	}
}

 ?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Bahan</title>
  <link rel="stylesheet" type="text/css" href="input.css">
<script src="https://unpkg.com/imask"></script>
</head>
<body>



<form action="" method="post">
	<div class="container">
    <div class="title">Input Produk</div>
    <div class="form">
      <label for="produk">Nama produk</label>
      <div class="input-group">
        <input type="text" id="produk" name="produk" placeholder="isi produk" min="1" required>
      </div>

      <label for="jumlah">Jumlah</label>
      <div class="input-group">
        <input type="text" name="jumlah" id="jumlah" placeholder="isi jumlah" min="1" required>
      </div>

      <label for="waktu">Waktu</label>
      <div class="input-group">
        <input type="datetime-local" name="waktu" id="waktu" placeholder="isi waktu" min="1" required>
      </div>

      <button type="submit" name="submit">Submit</button>

      <div style="text-align: center; margin-top: 10px;">
          <a href="luar_produksi.php">Batal</a>
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


