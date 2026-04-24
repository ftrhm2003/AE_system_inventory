<?php 
require 'singkong_function.php';

if (isset($_POST["submit"])) {
	if (tambah_produksi($_POST) > 0) {
		echo "
			<script>
				alert('data berhasil ditambahkan');
				document.location.href = 'singkong_produksi.php';
			</script>
		";
	} else {
		echo "
			<script>
				alert('data gagal ditambahkan');
				document.location.href = 'singkong_produksi.php';
			</script>
		";
	}
}

 ?>

<!-- 
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>tambah data</title>
	<link rel="stylesheet" type="text/css" href="bagusin.css">
</head>
<body>

<h1>Tambah data gudang</h1>

<a href="index.php">kembali</a>

<form action="" method="post">
	<ul>
		<li>
			<label for="singkong">Sinkong :</label>
			<input type="text" name="singkong" id="singkong">
			<label>kg</label>
		</li>
		<li>
			<label for="minyak">Minyak :</label>
			<input type="text" name="minyak" id="minyak">
			<label>kg</label>
		</li>
		<li>
			<label for="rasa">Bumbu :</label>
			<input type="text" name="rasa" id="rasa">
			<label>kg</label>
		</li>
		<li>
			<button type="submit" name="submit">Tambah data</button>
		</li>
	</ul>
</form>



</body>
</html> -->

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

<script src="https://unpkg.com/imask"></script>
</head>
<body>



<form action="" method="post">
	<div class="container">
    <div class="title">Input produk</div>
    <div class="form">
      <label for="produk">Nama produk</label>
      <div class="input-group">
        <input type="text" id="produk" name="produk" placeholder="isi produk" min="1" required>
      </div>

      <label for="jumlah">Jumlah</label>
      <div class="input-group">
        <input type="text" name="jumlah" id="jumlah" placeholder="isi jumlah" min="1" required>
      </div>

      <label for="waktu">waktu</label>
      <div class="input-group">
        <input type="datetime-local" name="waktu" id="waktu" placeholder="isi waktu" min="1" required>
      </div>

      <button type="submit" name="submit">Submit</button>
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


