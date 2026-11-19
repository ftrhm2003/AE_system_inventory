<?php 
require 'singkong_function.php';

$produkList = query_produksi("SELECT DISTINCT produk FROM gudang ORDER BY produk ASC");

if (isset($_POST["submit_tambah_produk"])) {
    if (tambah_produksi($_POST) > 0) {
        echo "<script>alert('Data berhasil ditambahkan'); document.location.href = 'singkong_produksi.php';</script>";
        exit;
    } else {
        echo "<script>alert('Data gagal ditambahkan');</script>";
    }
}

if (isset($_POST["submit_tambah_jumlah"])) {
    if (tambah_jumlah($_POST) > 0) {
        echo "<script>alert('Barang berhasil ditambah!'); document.location.href = 'singkong_produksi.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambah barang!');</script>";
    }
}

$produksi = query_produksi("SELECT * FROM gudang ORDER BY waktu DESC");

if (isset($_POST["cari_produksi"])) {
    $keyword = $_POST["src_produksi"];
    $produksi = cari_produksi($keyword);
    $produk_total = cari_total_per_produk($keyword); 
} else {
    $produk_total = total_per_produk();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Produk Singkong</title>
  <link rel="stylesheet" href="style_dash.css">

  <style>
    .wrapper {
      width: 100%;
      margin: auto;
    }
    .judul {
      text-align:center;
      margin:0 0 10px;
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
      background: #0d6efd;
      color: white;
      padding: 6px 14px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    .button:hover { background: #0b5ed7; }

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
      background: rgba(0,0,0,0.48);
      overflow-y: auto;
      padding: 30px 0;
    }
    .modal-content {
      background: #fff;
      margin: auto;
      padding: 26px 28px;
      border-radius: 18px;
      width: 92%;
      max-width: 620px;
      box-shadow: 0 20px 40px rgba(17,24,39,0.15);
      animation: fadeIn 0.25s ease;
      position: relative;
      border: 1px solid #edf2f7;
    }
    .modal-content h2 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 22px;
      color: #111827;
    }
    .modal-content .close {
      position: absolute;
      right: 18px;
      top: 18px;
      color: #6b7280;
      float: none;
      font-size: 26px;
      font-weight: 700;
      cursor: pointer;
      line-height: 1;
    }
    .modal-content .close:hover { color: #ef4444; }
    .modal-content form {
      display: grid;
      gap: 14px;
    }
    .modal-content label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
    }
    .modal-content .input-group {
      display: block;
    }
    .modal-content .input-group input,
    .modal-content .input-group select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      background: #f8fafc;
      color: #111827;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .modal-content .input-group input:focus,
    .modal-content .input-group select:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
    }
    .modal-content .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 120px;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 14px;
      letter-spacing: 0.01em;
      transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .modal-content .button:hover {
      transform: translateY(-1px);
    }
    .modal-content .button.primary {
      background: #0d6efd;
    }
    .modal-content .button.primary:hover {
      background: #0b5ed7;
    }
    .modal-content .button.secondary {
      background: #6c757d;
    }
    .modal-content .button.secondary:hover {
      background: #5c6670;
    }
    .modal-content .button-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 8px;
    }
    .table-card-header .header-actions button {
      text-decoration: none;
      margin: 0;
    }
    .top-dash {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .top-search {
      display: none;
      flex: 1;
      min-width: 240px;
      justify-content: flex-end;
      margin-left: 12px;
    }
    .top-search.active {
      display: flex;
    }
    .top-search .search-form {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      max-width: 520px;
    }
    .top-search .search-form input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 12px;
      font-size: 14px;
      outline: none;
      background: #f8fafc;
      color: #111827;
    }
    .top-search .search-form input:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
    }
    .top-search .search-form button {
      padding: 10px 16px;
      background: #0d6efd;
      color: #fff;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 600;
    }
    .top-search .search-form button:hover {
      background: #0b5ed7;
    }
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
    /* Tabel card dan styling mirip desain */
    .table-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      padding: 16px;
      margin-top: 10px;
      width: 100%;
    }
    .table-card-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .table-card-header h2{ margin:0; font-size:18px }
    .all-btn{ background:#0d6efd; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:600 }
    .all-btn:hover{ background:#0b5ed7 }
    table.table-container{ width:100%; border-collapse:collapse; }
    table.table-container thead th{ text-align:left; padding:12px; background:#f5f6f7; color:#444; font-weight:600; border-bottom:1px solid #e8e8e8 }
    table.table-container tbody td{ padding:12px; border-bottom:1px solid #f0f0f0; vertical-align:middle }
    .col-check{ width:40px; text-align:center }
    .product-cell{ display:flex; align-items:center; gap:10px }
    .avatar{ width:36px; height:36px; border-radius:50%; background:#e9ecef; display:inline-flex; align-items:center; justify-content:center; font-weight:700; color:#333 }
    .product-title{ font-weight:600 }
    .product-sub{ font-size:12px; color:#888 }
    tbody tr:hover{ background:#fbfdff }
    .icon-btn{ display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; border-radius:6px; margin-left:6px; text-decoration:none }
    .icon-btn.edit{ background:#198754 }
    .icon-btn.del{ background:#dc3545 }
    .icon-btn svg{ display:block }
    /* responsive tweaks */
    @media (max-width:800px){ .product-sub{ display:none } }
  </style>
</head>
<body>

  <div class="all-dash">
    <div class="out-dash" id="sidebar-container"></div>

    <div class="top-all-dash">
        <div class="top-dash">
            <button class="search-button" type="button">
                <img src="assets/search-alt-2-svgrepo-com.svg" alt="search">
            </button>
            <div id="topSearchBar" class="top-search">
                <form action="" method="post" class="search-form">
                    <input type="text" name="src_produksi" placeholder="Cari produk, jumlah, waktu" autocomplete="off" value="<?= isset($keyword) ? htmlspecialchars($keyword) : ''; ?>">
                    <button type="submit" name="cari_produksi"> Cari</button>
                </form>
            </div>
            <button class="account-button" type="button">
                <img src="assets/profile-1335-svgrepo-com-light.svg" alt="account">
            </button>
        </div>

        <div class="main-dashboard">
            <div class="in-main-dashboard">

              <div class="wrapper">
                <h1 class="judul">Produk Singkong</h1>

                <!-- Toolbar Tombol -->
                <!-- <div class="toolbar">
                  <a href="inventory.html" class="button">↩ Kembali</a>
                  <a href="singkong_tambah_produk.php" class="button">✚ Tambah Produk</a>
                  <a href="singkong_tambah_jumlah.php" class="button">📦 Tambah Stock</a>
                  <button id="openModal" class="button">🏣 Stock Gudang</button>
                  <form action="" method="post" class="search-form">
                    <input type="text" name="src_produksi" placeholder="cari data" autocomplete="off">
                    <button type="submit" name="cari_produksi">🔍 Cari</button>
                  </form>
                  <a href="singkong_convert_excel.php" class="button">🔽 Cetak Data</a>
                  <a href="singkong_log.php" class="button">🔽 Cetak Log</a>
                </div> -->

                <!-- Tabel Data (Desain baru mirip screenshot) -->
                <div class="table-card">
                  <div class="table-card-header">
                    <h2>Daftar Produk</h2>
                    <div class="header-actions">
                      <a href="inventory.html" class="button">↩ Kembali</a>
                      <button type="button" id="openAddProduct" class="button">✚ Tambah Produk</button>
                      <button type="button" id="openAddStock" class="button">〽 Tambah Stock</button>
                      <button id="openModal" class="button">🏠︎ Stock Gudang</button>
                      <a href="singkong_convert_excel.php" class="button">⛛ Cetak Data</a>
                      <a href="singkong_log.php" class="button">⛛ Cetak Log</a>
                    </div>
                  </div>

                  <table class="table-container">
                    <thead>
                      <tr>
                        <th class="col-check">No</th>
                        <th class="col-name">Produk</th>
                        <th class="col-count">Jumlah</th>
                        <th class="col-time">Waktu</th>
                        <th class="col-action">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $i = 1; ?>
                      <?php foreach ($produksi as $isi): ?>
                        <tr>
                          <td class="col-check"><?= $i; ?></td>
                          <td class="col-name">
                            <div class="product-cell">
                              <div class="avatar"><?php echo htmlspecialchars(strtoupper(substr($isi['produk'],0,1))); ?></div>
                              <div class="product-info">
                                <div class="product-title"><?= htmlspecialchars($isi["produk"]); ?></div>
                              </div>
                            </div>
                          </td>
                          <td class="col-count"><?= (int)$isi["jumlah"]; ?> pcs</td>
                          <td class="col-time"><?= $isi["waktu"]; ?></td>
                          <td class="col-action">
                            <a href="singkong_ubah_produk.php?id=<?= $isi['id']; ?>" class="icon-btn edit" title="Ubah">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" fill="#fff"/></svg>
                            </a>
                            <a href="singkong_hapus_produk.php?id=<?= $isi['id']; ?>" class="icon-btn del" title="Hapus" onclick="return confirm('Yakin mau hapus?');">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z" fill="#fff"/><path d="M9 4h6v2H9z" fill="#fff"/></svg>
                            </a>
                          </td>
                        </tr>
                      <?php $i++; endforeach; ?>
                    </tbody>
                  </table>
                </div>

                <!-- Stock Gudang Modal -->
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

                <!-- Tambah Produk Modal -->
                <div id="addProductModal" class="modal">
                  <div class="modal-content">
                    <span class="close close-add-product">&times;</span>
                    <h2>Tambah Produk</h2>
                    <form action="" method="post">
                      <label for="produk">Nama produk</label>
                      <div class="input-group">
                        <input type="text" id="produk" name="produk" placeholder="isi produk" required>
                      </div>

                      <label for="jumlah">Jumlah</label>
                      <div class="input-group">
                        <input type="number" name="jumlah" id="jumlah" placeholder="isi jumlah" min="1" required>
                      </div>

                      <label for="waktu">Waktu</label>
                      <div class="input-group">
                        <input type="datetime-local" name="waktu" id="waktu" required>
                      </div>

                      <div class="button-group">
                        <button type="submit" name="submit_tambah_produk" class="button primary">Submit</button>
                        <button type="button" class="button secondary" id="closeAddProduct">Batal</button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Tambah Stock Modal -->
                <div id="addStockModal" class="modal">
                  <div class="modal-content">
                    <span class="close close-add-stock">&times;</span>
                    <h2>Tambah Stock</h2>
                    <form action="" method="post">
                      <label for="produk_stock">Pilih produk</label>
                      <div class="input-group">
                        <select name="produk" id="produk_stock" required>
                          <?php foreach ($produkList as $p): ?>
                            <option value="<?= htmlspecialchars($p['produk']); ?>"><?= htmlspecialchars($p['produk']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <label for="jumlah_stock">Jumlah yang di tambahkan</label>
                      <div class="input-group">
                        <input type="number" name="jumlah" id="jumlah_stock" placeholder="isi jumlah" min="1" required>
                      </div>

                      <label for="waktu_stock">Waktu</label>
                      <div class="input-group">
                        <input type="datetime-local" name="waktu" id="waktu_stock" required>
                      </div>

                      <div class="button-group">
                        <button type="submit" name="submit_tambah_jumlah" class="button primary">Tambah</button>
                        <button type="button" class="button secondary" id="closeAddStock">Batal</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
  </div>    

<script>
var stockModal = document.getElementById("myModal");
var addProductModal = document.getElementById("addProductModal");
var addStockModal = document.getElementById("addStockModal");

var stockBtn = document.getElementById("openModal");
var addProductBtn = document.getElementById("openAddProduct");
var addStockBtn = document.getElementById("openAddStock");

var closeStock = stockModal.querySelector(".close");
var closeAddProduct = document.querySelector(".close-add-product");
var closeAddStock = document.querySelector(".close-add-stock");
var cancelAddProduct = document.getElementById("closeAddProduct");
var cancelAddStock = document.getElementById("closeAddStock");

function openModal(modal) {
    modal.style.display = "block";
}
function closeModal(modal) {
    modal.style.display = "none";
}

stockBtn.onclick = function() { openModal(stockModal); }
addProductBtn.onclick = function() { openModal(addProductModal); }
addStockBtn.onclick = function() { openModal(addStockModal); }

closeStock.onclick = function() { closeModal(stockModal); }
closeAddProduct.onclick = function() { closeModal(addProductModal); }
closeAddStock.onclick = function() { closeModal(addStockModal); }

cancelAddProduct.onclick = function() { closeModal(addProductModal); }
cancelAddStock.onclick = function() { closeModal(addStockModal); }

window.onclick = function(e) {
    if (e.target === stockModal) closeModal(stockModal);
    if (e.target === addProductModal) closeModal(addProductModal);
    if (e.target === addStockModal) closeModal(addStockModal);
}

var topSearchBar = document.getElementById('topSearchBar');
var searchButton = document.querySelector('.search-button');
searchButton.addEventListener('click', function() {
    topSearchBar.classList.toggle('active');
    var input = topSearchBar.querySelector('input');
    if (topSearchBar.classList.contains('active')) {
        input.focus();
    }
});
</script>

<script>
// check/uncheck all
</script>

<script src="script.js"></script>

</body>
</html>


