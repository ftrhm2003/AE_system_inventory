<?php 
require 'bawang_function.php';

$id = $_GET["id"];

$prd = query_produksi("SELECT * FROM gudang1 WHERE id = $id")[0];


if (isset($_POST["submit"])) {
  if (ubah_produksi($_POST) > 0) {
    echo "
      <script>
        alert('data diubah');
        document.location.href = 'bawang_produksi.php';
      </script>
       ";
  } else {
    echo "
      <script>
        alert('data gagal diubah');
        document.location.href = 'bawang_produksi.php';
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
</head>
<body>



<form action="" method="post">
  <div class="container">
    <div class="title">Ubah Produk</div>
    <input type="hidden" name="id" value="<?php echo $prd["id"]; ?>">
    <div class="form">
      <label for="produk">Nama produk</label>
      <div class="input-group">
        <input type="text" id="produk" name="produk" placeholder="isi produk" value="<?php echo $prd["produk"]; ?>">
      </div>

      <label for="jumlah">Jumlah</label>
      <div class="input-group">
        <input type="text" name="jumlah" id="jumlah" placeholder="isi jumlah" value="<?php echo $prd["jumlah"]; ?>">
      </div>

      <label for="waktu">Waktu</label>
      <div class="input-group">
        <input type="datetime-local" name="waktu" id="waktu" placeholder="isi waktu" value="<?php echo $prd["waktu"]; ?>">
      </div>


      <button type="submit" name="submit">Submit</button>

      <div style="text-align: center; margin-top: 10px;">
          <a href="bawang_produksi.php">Batal</a>
       </div>
    </div>
  </div>
</form>
  
<script src="https://unpkg.com/imask"></script>
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
    inputElement.value = mask.unmaskedValue; 
  });
</script>

</body>
</html>

