<?php 
require 'luar_function.php';

$produksi = query_produksi("SELECT * FROM gudang2 ORDER BY waktu DESC");

if (isset($_POST["cari_produksi"])) {
    $keyword = $_POST["src_produksi"];
    $produksi = cari_produksi($keyword);
    $produk_total = cari_total_per_produk($keyword); 
} else {
    $produksi = query_produksi("SELECT * FROM gudang2 ORDER BY waktu DESC");
    $produk_total = total_per_produk();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Produk luar</title>
  <link rel="stylesheet" href="style.css">

  <style>
    .wrapper {
      width: 95%;
      margin: auto;
    }
    .judul {
      text-align:center;
      margin:20px 0 10px;
    }

    /* toolbar tombol */
    .toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;            /* jarak antar tombol lebih lebar */
    justify-content: center;
    margin-bottom: 20px;  /* beri ruang ke tabel di bawah */
    padding: 10px 0;      /* jarak atas-bawah toolbar */
    }

    /* tombol umum */
    .button {
      background: #4CAF50;
      color: white;
      padding: 6px 14px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    .button:hover { background: #45a049; }

    /* btn ubah hapus */
    .btn {
      display: inline-block;
      padding: 6px 12px;
      font-size: 14px;
      border-radius: 5px;
      text-decoration: none;
      color: #fff;
    }
    .btn-ubah { background-color: #007bff; }
    .btn-ubah:hover { background-color: #0056b3; }
    .btn-hapus { background-color: #dc3545; }
    .btn-hapus:hover { background-color: #a71d2a; }

    /* table umum */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 8px;
      text-align: center;
      border: 1px solid #ccc;
    }
    th { background: #f2f2f2; }

    /* modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
    }
    .modal-content {
      background: #fff;
      margin: 5% auto;
      padding: 20px;
      border-radius: 8px;
      width: 90%;
      max-width: 800px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      animation: fadeIn 0.3s;
    }
    .close {
      color: #333;
      float: right;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover { color: red; }
    @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }

    /* form cari */
    .search-form {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .search-form input {
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .search-form button {
      padding: 6px 12px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .search-form button:hover { background: #45a049; }
  </style>
</head>
<body>

<div class="wrapper">
  <h1 class="judul">Produk Luar</h1>

  <!-- Toolbar Tombol -->
  <div class="toolbar">
    <a href="gudang.php" class="button">↩ Kembali</a>
    <a href="luar_tambah_produk.php" class="button">✚ Tambah Produk</a>
    <a href="luar_tambah_jumlah.php" class="button">📦 Tambah Stock</a>
    <button id="openModal" class="button">🏣 Stock Gudang</button>
    <form action="" method="post" class="search-form">
      <input type="text" name="src_produksi" placeholder="cari data" autocomplete="off">
      <button type="submit" name="cari_produksi">🔍 Cari</button>
    </form>
    <a href="luar_convert_excel.php" class="button">🔽 Cetak Data</a>
    <a href="luar_log.php" class="button">🔽 Cetak Log</a>
  </div>

  <!-- Tabel Data -->
  <table class="table-container">
    <tr class="table-row header">
      <th>No</th>
      <th>Nama Produk</th>
      <th>Jumlah</th>
      <th>Waktu</th>
      <th>Aksi</th>
    </tr>

    <?php $i = 1; ?>
    <?php foreach ($produksi as $isi): ?>
      <tr class="table-row">
        <td><?= $i; ?></td>
        <td><?= htmlspecialchars($isi["produk"]); ?></td>
        <td><?= (int)$isi["jumlah"]; ?> pcs</td>
        <td><?= $isi["waktu"]; ?></td>
        <td>
          <a href="luar_ubah_produk.php?id=<?= $isi['id']; ?>" class="btn btn-ubah">Ubah</a>
          <a href="luar_hapus_produk.php?id=<?= $isi['id']; ?>" class="btn btn-hapus"
             onclick="return confirm('Yakin mau hapus?');">Hapus</a>
        </td>
      </tr>
    <?php $i++; endforeach; ?>
  </table>

  <!-- Modal -->
  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Total Stock Gudang</h2>
      <table>
        <tr>
          <th>No</th>
          <th>Nama Produk</th>
          <th>Total Jumlah</th>
        </tr>
        <?php $i=1; foreach ($produk_total as $row): ?>
          <tr>
            <td><?= $i; ?></td>
            <td><?= htmlspecialchars($row['produk']); ?></td>
            <td><?= (int)$row['total_jumlah']; ?> pcs</td>
          </tr>
        <?php $i++; endforeach; ?>
      </table>
    </div>
  </div>
</div>

<script>
var modal = document.getElementById("myModal");
var btn   = document.getElementById("openModal");
var span  = document.getElementsByClassName("close")[0];
btn.onclick = function(){ modal.style.display = "block"; }
span.onclick = function(){ modal.style.display = "none"; }
window.onclick = function(e){ if(e.target==modal){ modal.style.display="none"; } }
</script>
</body>
</html>
