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
  <!-- <link rel="stylesheet" href="ss.css"> -->
  <style>
      * {
  box-sizing: border-box;
  font-family: sans-serif;
}

body {
  background-color: #d9d9d9;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.container {
  background-color: #fff;
  padding: 30px 20px;
  border-radius: 8px;
  width: 300px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.title {
  background-color: #f2f2f2;
  text-align: center;
  font-weight: bold;
  padding: 15px;
  margin: -30px -20px 20px -20px;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.form label {
  display: block;
  margin-bottom: 5px;
  margin-top: 15px;
  font-weight: bold;
}

.input-group {
  display: flex;
  align-items: center;
  margin-bottom: 5px;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 5px 10px;
  background-color: white;
}

.input-group input {
  border: none;
  outline: none;
  flex: 1;
  font-size: 14px;
  background-color: transparent;
}

.input-group span {
  color: gray;
  font-size: 14px;
  padding-left: 8px;
}

button {
  margin-top: 20px;
  width: 100%;
  padding: 10px;
  font-size: 16px;
  font-weight: bold;
  background-color: white;
  border: 2px solid black;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

button:hover {
  background-color: #eee;
}

  </style>
</head>
<body>



<form action="bawang_tambah_jumlah.php" method="post">
    <div class="container">
    <div class="title">Input produk</div>
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

      <label for="jumlah">Jumlah yang di ambil</label>
      <div class="input-group">
        <input type="number" name="jumlah" id="jumlah" placeholder="isi jumlah" required min="1">
      </div>

       <label for="waktu">waktu</label>
       <div class="input-group">
         <input type="datetime-local" name="waktu" id="waktu" placeholder="isi waktu" min="1">
       </div>

      <button type="submit" name="submit">Tambah</button>
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
