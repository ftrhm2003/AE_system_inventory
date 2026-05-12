<?php 
require 'toko_function.php';

$stokGudangList = getSemuaStokGudang(); 

if (isset($_POST["submit"])) {
    $combined = $_POST["produk"];
list($produk, $gudang_asal) = explode('|', $combined);
$qty = $_POST["qty"];


    if (tambahStokToko($produk, $qty, $gudang_asal)) {
        echo "<script>
            alert('Stok berhasil ditambahkan ke toko!');
            document.location.href = 'toko.php';
        </script>";
    } else {
        echo "<script>alert('Gagal menambahkan stok ke toko');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Stok Toko</title>
  <link rel="stylesheet" type="text/css" href="input.css">
</head>
<body>

<form action="" method="post">
  <div class="container">
    <div class="title">Tambah Stock</div>
    <div class="form">

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Pilih</th>
              <th>Produk</th>
              <th>Stok</th>
              <th>Gudang</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stokGudangList as $item): ?>
              <tr>
                <td>
                  <input type="radio" 
                         name="produk" 
                         value="<?= htmlspecialchars($item['produk']) . '|' . $item['gudang'] ?>" 
                         required>
                </td>
                <td><?= htmlspecialchars($item['produk']) ?></td>
                <td><?= $item['stok'] ?> pcs</td>
                <td><?= htmlspecialchars($item['gudang']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <label for="qty">Jumlah</label>
      <div class="input-group">
        <input type="number" name="qty" id="qty" placeholder="isi jumlah" min="1" required>
      </div>

      <button type="submit" name="submit">Tambah Stock</button>
      <div style="text-align: center; margin-top: 10px;">
        <a href="toko.php">Batal</a>
      </div>

    </div>
  </div>
</form>

</body>
</html>
