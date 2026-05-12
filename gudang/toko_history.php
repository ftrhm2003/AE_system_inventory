<?php
require 'toko_function.php';

$history = mysqli_query($conn, "
    SELECT * FROM toko_kirim
    WHERE jumlah > 0
    ORDER BY waktu DESC
");

if (isset($_POST['cari']) && !empty($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    $history = cariTokoStock($keyword);
} else {
    $history = mysqli_query($conn, "
        SELECT * FROM toko_kirim
        WHERE jumlah > 0
        ORDER BY waktu DESC
    ");
}

$sqlStok = "
  SELECT produk, SUM(jumlah) AS stok, sumber FROM (
    SELECT produk, jumlah, 'gudang'  AS sumber FROM gudang
    UNION ALL
    SELECT produk, jumlah, 'gudang1' AS sumber FROM gudang1
    UNION ALL
    SELECT produk, jumlah, 'gudang2' AS sumber FROM gudang2
  ) AS semua
  GROUP BY produk, sumber
";
$resStok = mysqli_query($conn, $sqlStok);

$stocks = [];
if ($resStok) {
    while ($r = mysqli_fetch_assoc($resStok)) {
        $stocks[] = $r;
    }
}
$stock_count = count($stocks);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Transfer ke Toko</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .wrapper { width:95%; margin:auto; }
    .judul { text-align:center; margin:20px 0 10px; }

    /* toolbar tombol */
    .toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;            /* jarak antar tombol lebih lebar */
    justify-content: center;
    margin-bottom: 20px;  /* beri ruang ke tabel di bawah */
    padding: 10px 0;      /* jarak atas-bawah toolbar */
    }
    .button {
      background:#4CAF50;
      color:white;
      padding:6px 14px;
      border-radius:5px;
      text-decoration:none;
      font-size:14px;
      border:none;
      cursor:pointer;
    }
    .button:hover { background:#45a049; }

    /* tombol aksi */
    .btn {
      display:inline-block;
      padding:6px 12px;
      font-size:14px;
      border-radius:5px;
      text-decoration:none;
      color:#fff;
    }
    .btn-ubah { background-color: #007bff; }
    .btn-ubah:hover { background-color: #0056b3; }
    .btn-hapus { background-color:#dc3545; }
    .btn-hapus:hover { background-color:#a71d2a; }

    /* tabel */
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td {
      padding:8px;
      text-align:center;
      border:1px solid #ccc;
    }
    th { background:#f2f2f2; }

    /* modal stok */
    .modal {
      display:none;
      position:fixed;
      z-index:1000;
      left:0; top:0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.5);
    }
    .modal-content {
      background:#fff;
      margin:5% auto;
      padding:20px;
      border-radius:8px;
      width:90%;
      max-width:800px;
      box-shadow:0 4px 8px rgba(0,0,0,0.2);
      animation:fadeIn 0.3s;
    }
    .close {
      color:#333;
      float:right;
      font-size:24px;
      font-weight:bold;
      cursor:pointer;
    }
    .close:hover { color:red; }
    @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }

    /* form cari */
    .search-form {
      display:flex;
      align-items:center;
      gap:6px;
    }
    .search-form input {
      padding:6px;
      border:1px solid #ccc;
      border-radius:4px;
    }
    .search-form button {
      padding:6px 12px;
      background:#4CAF50;
      color:white;
      border:none;
      border-radius:4px;
      cursor:pointer;
    }
    .search-form button:hover { background:#45a049; }
  </style>
</head>
<body>

<div class="wrapper">
  <h1 class="judul">Kirim Toko</h1>

  <!-- Toolbar -->
  <div class="toolbar">
    <a href="kirim.php" class="button">↩ Kembali</a>
    <a href="toko_transfer.php" class="button">✚ Kirim Produk</a>
    <button id="openModal" class="button">🏣 Stok Gudang</button>
    <form action="" method="post" class="search-form">
      <input type="text" name="keyword" placeholder="Cari Produk" 
             value="<?= htmlspecialchars($_POST['keyword'] ?? '') ?>">
      <button type="submit" name="cari">🔍 Cari</button>
    </form>
    <a href="toko_kirim_convert.php" class="button">🔽 Export Excel</a>
    <a href="toko_kirim_log.php" class="button">🔽 Cetak Log</a>
  </div>

  <!-- Tabel Riwayat -->
  <table class="table-container">
    <tr class="table-row header">
      <th>No</th>
      <th>Produk</th>
      <th>Terkirim</th>
      <th>Harga/pcs</th>
      <th>Gudang Asal</th>
      <th>Waktu</th>
      <th>Aksi</th>
    </tr>
    <?php $i=1; ?>
    <?php while ($r = mysqli_fetch_assoc($history)): ?>
      <tr class="table-row">
        <td><?= $i; ?></td>
        <td><?= htmlspecialchars($r['produk']); ?></td>
        <td><?= $r['jumlah']; ?> pcs</td>
        <td>Rp <?= number_format($r["harga_jual"], 2, '.', '.'); ?></td>
        <td><?= htmlspecialchars($r['gudang_asal']); ?></td>
        <td><?= $r['waktu']; ?></td>
        <td>
          <a href="toko_edit.php?id=<?= $r['id']; ?>" class="btn btn-ubah">Ubah</a>
          <a href="toko_hapus.php?id=<?= $r['id']; ?>" class="btn btn-hapus"
             onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
        </td>
      </tr>
    <?php $i++; endwhile; ?>
  </table>

  <!-- Modal stok gudang -->
  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Total Stok Gudang</h2>
      <table>
        <tr>
          <th>No</th>
          <th>Produk</th>
          <th>Stok</th>
          <th>Gudang</th>
        </tr>
        <?php if ($stock_count === 0): ?>
          <tr><td colspan="4">Belum ada data stok.</td></tr>
        <?php else: $i=1; foreach ($stocks as $s): ?>
          <tr>
            <td><?= $i; ?></td>
            <td><?= htmlspecialchars($s['produk']); ?></td>
            <td><?= (int)$s['stok']; ?></td>
            <td><?= htmlspecialchars($s['sumber']); ?></td>
          </tr>
        <?php $i++; endforeach; endif; ?>
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
