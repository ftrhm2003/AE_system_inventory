<?php
require 'kirim_luar_function.php';  

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

$search_value = '';
if (isset($_POST['cari_pengiriman'])) {
    $search_value = $_POST['src_pengiriman'] ?? '';
    $kirim_luar = cari_pengiriman($search_value);
} else {
    $kirim_luar = mysqli_query($conn, "
        SELECT id, pemesan, produk, jumlah, harga,
               (jumlah * harga) AS total_penjualan,
               jenis_produk, waktu
        FROM kirim_luar
        ORDER BY id DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kirim ke Luar</title>
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
.btn-cetak { background-color: #ffc107; }
.btn-cetak:hover { background-color: #e0a800; }

/* table umum */
table {
  width: 100%;
  border-collapse: collapse;
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
  <h1 class="judul">Kirim Produk ke Luar</h1>

  <!-- Toolbar Tombol -->
  <div class="toolbar">
    <a href="kirim.php" class="button">↩ Kembali</a>
    <a href="kirim_luar_tambah.php" class="button">✚ Kirim Produk</a>
    <button id="openModal" class="button">🏣 Stock Gudang</button>
    <form action="" method="post" class="search-form">
      <input type="text" name="src_pengiriman" placeholder="cari data" autocomplete="off"
             value="<?= htmlspecialchars($search_value); ?>">
      <button type="submit" name="cari_pengiriman">🔍 Cari</button>
    </form>
    <a href="kirim_luar_convert.php" class="button">🔽 Export Excel</a>
    <a href="kirim_luar_logcetak.php" class="button">🔽 Log Cetak</a>
  </div>

  <!-- Tabel Data -->
  <table class="table-container">
    <tr class="table-row header">
      <th>No</th>
      <th>Pemesan</th>
      <th>Produk</th>
      <th>Jumlah</th>
      <th>Harga/pcs</th>
      <th>Total</th>
      <th>Waktu</th>
      <th>Aksi</th>
    </tr>

    <?php $i=1; ?>
    <?php while ($row = mysqli_fetch_assoc($kirim_luar)) : ?>
      <tr class="table-row">
        <td><?= $i; ?></td>
        <td><?= htmlspecialchars($row['pemesan']); ?></td>
        <td><?= htmlspecialchars($row['produk']); ?></td>
        <td><?= (int)$row['jumlah']; ?> pcs</td>
        <td>Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
        <td>Rp <?= number_format($row["total_penjualan"], 2, '.', '.'); ?></td>
        <td><?= $row['waktu']; ?></td>
        <td>
          <a href="kirim_luar_cetak.php?id=<?= $row['id']; ?>" target="_blank" class="btn btn-cetak">Cetak</a>
          <a href="kirim_luar_ubah.php?id=<?= $row['id']; ?>" class="btn btn-ubah">Ubah</a>
          <a href="kirim_luar_hapus.php?id=<?= $row['id']; ?>" class="btn btn-hapus"
             onclick="return confirm('Yakin mau hapus?');">Hapus</a>
        </td>
      </tr>
    <?php $i++; endwhile; ?>
  </table>

  <!-- Modal stok -->
  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Total Stock Gudang</h2>
      <table>
        <tr>
          <th>No</th>
          <th>Nama Produk</th>
          <th>Total Jumlah</th>
          <th>Sumber Gudang</th>
        </tr>
        <?php if ($stock_count === 0): ?>
          <tr><td colspan="4">Belum ada data stok.</td></tr>
        <?php else: ?>
          <?php $i=1; foreach ($stocks as $s): ?>
            <tr>
              <td><?= $i; ?></td>
              <td><?= htmlspecialchars($s["produk"]); ?></td>
              <td><?= (int)$s["stok"]; ?> pcs</td>
              <td><?= htmlspecialchars($s["sumber"]); ?></td>
            </tr>
          <?php $i++; endforeach; ?>
        <?php endif; ?>
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
