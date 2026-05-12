<?php
require 'toko_function.php';

$stokToko = getStokTokoAgg();
$produk_pertama = null;

if ($stokToko && mysqli_num_rows($stokToko) > 0) {
    $produk_pertama_row = mysqli_fetch_assoc($stokToko);
    $produk_pertama = $produk_pertama_row['produk'];
    
    
    mysqli_data_seek($stokToko, 0);
}

$history = mysqli_query($conn, "
    SELECT * FROM toko_stock
    WHERE jumlah > 0
    ORDER BY waktu DESC
");

if (isset($_POST['cari']) && !empty($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    $history = cariTokoStock($keyword);
} else {
    $history = mysqli_query($conn, "
        SELECT * FROM toko_stock
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
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
       .header-bar {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}
.header-bar a { text-decoration:none; }

.accordion-btn {
    display:inline-block;
    padding:6px 12px;
    font-size:15px;
    border:1px solid #666;
    border-radius:6px;
    background:#f5f5f5;
    cursor:pointer;
    line-height:1.2;
    user-select:none;
}
.accordion-btn:after {
    content:'▼';
    margin-left:8px;
    font-size:12px;
    transition:transform .15s;
}
.accordion-btn[aria-expanded="true"]:after {
    transform:rotate(-180deg);
}

.stock-panel {
    margin:10px 0 25px 0;
    display:none;            
}
.stock-panel.show {
    display:block;
}
.stock-scroll {
    max-height:260px;          
    overflow-y:auto;
    border:1px solid #ccc;
    border-radius:8px;
    background:#fff;
}

.stock-table {
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}
.stock-table th,
.stock-table td {
    border-bottom:1px solid #eee;
    padding:6px 10px;
    text-align:left;
    white-space:nowrap;
}
.stock-table th {
    position:sticky;
    top:0;
    background:#f2f2f2;
    z-index:1;
    font-weight:bold;
}
.stock-table td.stok {
    text-align:right;
    width:60px;
}
.stock-table td.gudang {
    width:90px;
}

.table-container {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
.table-container th,
.table-container td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}
.table-container .header {
    background:#f2f2f2;
    font-weight:bold;
}
.judul {
    text-align:center;
    margin:20px 0 5px;
}
.section-title {
    margin:0 0 5px;
    font-size:18px;
    font-weight:bold;
}
@media (max-width:600px){
    .header-bar{flex-direction:column;align-items:flex-start}
    .accordion-btn{width:100%;text-align:left}
    .stock-table th, .stock-table td{font-size:13px}
    .table-container th, .table-container td{font-size:13px}
}
    </style>
</head>
<body>

 <div class="wrapper">
  <h1 class="judul">Stock Toko</h1>
  <br><br>

  <div class="header-bar">
    <a href="toko_ae.php">⬅ Kembali</a>
    <a href="toko_tambah_stock.php">+ Tambah Stock</a>
    <a href="toko_ubah.php?produk=<?= urlencode($produk_pertama); ?>"># Ubah Harga</a>
    <form action="" method="post">
    <input type="text" name="keyword" placeholder="Cari Produk" 
           value="<?= htmlspecialchars($_POST['keyword'] ?? '') ?>">
    <button type="submit" name="cari">🔍 Cari</button>
    </form>
    <button type="button" class="accordion-btn" id="btnStok"
            aria-expanded="false" aria-controls="stokPanel">
            Stok Gudang (<?= $stock_count; ?>)
    </button>
    <a href="toko_kirim_convert.php" class="button">🔽 Export ke Excel</a>
    <a href="toko_kirim_log.php" class="button">🔽 Log Cetak</a>
  </div>

  <div id="stokPanel" class="stock-panel" role="region" aria-labelledby="btnStok">
    <div class="stock-scroll">
      <table class="stock-table">
        <thead>
          <tr>
            <th>Produk</th>
            <th class="stok">Stok</th>
            <th class="gudang">Gudang</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($stock_count === 0): ?>
          <tr><td colspan="3">Belum ada data stok.</td></tr>
        <?php else: ?>
          <?php foreach ($stocks as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s["produk"]); ?></td>
              <td class="stok"><?= (int)$s["stok"]; ?></td>
              <td class="gudang"><?= htmlspecialchars($s["sumber"]); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<h3 class="section-title">Data Stock Toko</h3>
  <table class="table-container">
    <tr class="table-row header">
      <th>No</th>
      <th>Produk</th>
      <th>Stock</th>
      <th>Harga/pcs</th>
      <th>Gudang asal</th>
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
        <td>
            <a href="toko_hapus.php?id=<?= $r['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">hapus</a>
        </td>
      </tr>
    <?php $i++; endwhile; ?>
  </table>
</div>

<script>
const btnStok = document.getElementById('btnStok');
const stokPanel = document.getElementById('stokPanel');

btnStok.addEventListener('click', function(){
    const isOpen = stokPanel.classList.toggle('show');
    btnStok.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});
</script>

</body>
</html>


