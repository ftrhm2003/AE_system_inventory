<?php 

require 'bawang_function.php';

if (isset($_POST['submit'])) {
    if (tambah_jumlah($_POST) > 0) {
        echo "<script>
            alert('Barang berhasil ditambah!');
            document.location.href = 'bawang_produksi.php';
        </script>";
    } else {
        echo "<script>alert('Gagal menambah barang!');</script>";
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



<form action="bawang_tambah_jumlah.php" method="post">
    <div class="container">
    <div class="title">Tambah Produk</div>
    <div class="form">
      <label for="produk">Pilih produk</label>
      <div class="input-group">
            <select name="produk" id="produk">
            <?php
            $produkList = query_produksi("SELECT DISTINCT produk FROM gudang1");
            foreach ($produkList as $p) {
                echo "<option value=\"{$p['produk']}\">{$p['produk']}</option>";
            }
            ?>
            </select>
      </div>

      <label for="jumlah">Jumlah yang di tambahkan</label>
      <div class="input-group">
        <input type="number" name="jumlah" id="jumlah" placeholder="isi jumlah" required min="1">
      </div>

       <label for="waktu">Waktu</label>
       <div class="input-group">
         <input type="datetime-local" name="waktu" id="waktu" placeholder="isi waktu" min="1">
       </div>

      <button type="submit" name="submit">Tambah</button>
      <div style="text-align: center; margin-top: 10px;">
          <a href="bawang_produksi.php">Batal</a>
       </div>
    </div>
  </div>
</form>
  
</body>
</html>




<!-- <form action="ambil_produk.php" method="post">
  <label for="produk">Pilih Produk</label>
  <select name="produk" id="produk">
    <?php
    $produkList = query_produksi("SELECT DISTINCT produk FROM produksi");
    foreach ($produkList as $p) {
        echo "<option value=\"{$p['produk']}\">{$p['produk']}</option>";
    }
    ?>
  </select>

  <label for="jumlah">Jumlah yang diambil</label>
  <input type="number" name="jumlah" id="jumlah" required min="1">

  <button type="submit" name="submit">Ambil</button>
</form> -->
