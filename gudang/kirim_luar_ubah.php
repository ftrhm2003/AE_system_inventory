<?php
require 'kirim_luar_function.php';

// ambil id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID tidak valid.");
}

$data = get_pengiriman($id);
if (!$data) {
    die("Data tidak ditemukan.");
}

// Ambil stok semua gudang untuk radio pilihan
$sqlProduk = "
  SELECT produk, SUM(jumlah) as stok, sumber FROM (
    SELECT produk, jumlah, 'gudang'  as sumber FROM gudang
    UNION ALL
    SELECT produk, jumlah, 'gudang1' as sumber FROM gudang1
    UNION ALL
    SELECT produk, jumlah, 'gudang2' as sumber FROM gudang2
  ) as semua
  GROUP BY produk, sumber
";
$resProduk = mysqli_query($conn, $sqlProduk);

// nilai lama
$produk_lama = $data['produk'];
$jenis_lama  = $data['jenis_produk'];
$pemesan_lama= $data['pemesan'];
$jumlah_lama = (int)$data['jumlah'];
$harga_lama  = (int)$data['harga'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ubah Pengiriman</title>
<link rel="stylesheet" type="text/css" href="input.css">
<script src="https://unpkg.com/imask"></script>
</head>
<body>

<form action="kirim_luar_function.php" method="post">
  <div class="container">
    <input type="hidden" name="id" value="<?= $id; ?>">
    <div class="title">Input ubah produk</div>
    <div class="form">
      <label for="produk">Pemesan</label>
      <div class="input-group">
        <input type="text" id="pemesan" name="pemesan" value="<?= htmlspecialchars($pemesan_lama); ?>" required>
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
            <?php while ($row = mysqli_fetch_assoc($resProduk)) : 
                $val = $row['produk'].'|'.$row['sumber'];
                $checked = ($row['produk'] === $produk_lama && $row['sumber'] === $jenis_lama) ? 'checked' : '';
            ?>
              <tr>
                <td><input type="radio" name="produk" value="<?= $val; ?>" <?= $checked; ?> required></td>
                <td><?= $row['produk']; ?></td>
                <td><?= $row['stok'] ?? 0; ?> pcs</td>
                <td><?= $row['sumber']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <label for="harga">Harga</label>
      <div class="input-group">
       <input type="text" name="harga" id="harga" value="<?= $harga_lama; ?>" required>
      </div>

      <label for="jumlah">Jumlah</label>
      <div class="input-group">
         <input type="text" name="jumlah" id="jumlah" value="<?= $jumlah_lama; ?>" required>
      </div>

      <button type="submit" name="submit_update">Simpan Perubahan</button>

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
