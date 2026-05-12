<?php
require 'toko_function.php';

$id = (int)($_GET['id'] ?? 0);
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM toko_kirim WHERE id=$id"));
if (!$data) alertBack("Data tidak ditemukan");

if (isset($_POST['simpan'])) {
    list($produk, $gudang_asal) = explode('|', $_POST['produk_gudang']);
    $qty = (int)$_POST['jumlah'];

    if (ubahStokToko($id, $produk, $qty)) {
        echo "<script>alert('Berhasil diubah');location.href='toko_history.php';</script>";
        exit;
    }
}


$produk_gudang = [];
foreach ($ALLOWED_GUDANG as $gudang) {
    $res = mysqli_query($conn, "
    SELECT produk, SUM(jumlah) AS stok 
    FROM $gudang 
    GROUP BY produk
    ORDER BY produk
");

    while ($row = mysqli_fetch_assoc($res)) {
        $key = $row['produk'].'|'.$gudang;
        $produk_gudang[$key] = [
            'produk' => $row['produk'],
            'stok' => (int)$row['stok'],
            'gudang' => $gudang
        ];
    }
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Stok Toko</title>
<link rel="stylesheet" type="text/css" href="input.css">
    <script src="https://unpkg.com/imask"></script>
</head>
<body>
<form action="" method="post">
  <div class="container">
    <div class="title">Input ubah produk</div>
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
        <?php foreach ($produk_gudang as $key => $pg): ?>
        <tr>
              <td>
                <input type="radio" name="produk_gudang" value="<?= htmlspecialchars($key); ?>"<?= $pg['produk'] == $data['produk'] && $pg['gudang'] == $data['gudang_asal'] ? 'checked' : ''; ?>>
              </td>
              <td><?= htmlspecialchars($pg['produk']) ?></td>
              <td><?= $pg['stok'] ?> pcs</td>
              <td><?= $pg['gudang'] ?></td>
        </tr>
        <?php endforeach; ?>

        </tbody>
        </table>
      </div>
      <label for="jumlah">Jumlah</label>
      <div class="input-group">
        <input type="number" name="jumlah" id="jumlah" placeholder="isi jumlah" value="<?= $data['jumlah']; ?>" required>
      </div>

     <button type="submit" name="simpan">Simpan perubahan</button>
      <div style="text-align: center; margin-top: 10px;">
        <a href="toko_history.php">Batal</a>
      </div>
    </div>
  </div>
</form>
</body>
</html>
