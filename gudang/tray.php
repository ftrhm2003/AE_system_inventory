<?php
require 'kirim_luar_function.php';

$sqlStok = "
  SELECT produk, SUM(jumlah) AS stok, sumber FROM (
    SELECT produk, jumlah, 'gudang' AS sumber FROM gudang
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
  <link rel="stylesheet" href="style_dash.css">
  <style>
    body {
      margin: 0;
      font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
      background: #f3f6fb;
      color: #111827;
    }

    .all-dash {
      display: flex;
      min-height: 100vh;
      background: #f3f6fb;
    }

    .out-dash {
      width: 260px;
      flex-shrink: 0;
    }

    .top-all-dash {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .top-dash {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      padding: 18px 28px 0;
    }

    .search-button,
    .account-button {
      border: none;
      background: #fff;
      border-radius: 12px;
      width: 44px;
      height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
      cursor: pointer;
    }

    .search-button img,
    .account-button img {
      width: 20px;
      height: 20px;
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
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
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

    .main-dashboard {
      padding: 20px 28px 36px;
    }

    .in-main-dashboard {
      max-width: 1400px;
      margin: 0 auto;
    }

    .wrapper {
      width: 100%;
      margin: 0;
    }

    .judul {
      text-align: center;
      margin: 20px 0 18px;
      font-size: 40px;
      font-weight: 700;
      color: #111827;
    }

    .table-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
      padding: 22px;
      width: 100%;
      box-sizing: border-box;
    }

    .table-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .table-card-header h2 {
      margin: 0;
      font-size: 20px;
      color: #111827;
    }

    .header-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .button {
      background: #2563eb;
      color: white;
      padding: 10px 16px;
      border-radius: 12px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.2s ease, transform 0.15s ease;
    }

    .button:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }

    .icon-btn {
      display: inline-flex;
      width: 42px;
      height: 42px;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      margin-left: 6px;
      text-decoration: none;
      transition: transform 0.15s ease;
    }

    .icon-btn:hover { transform: translateY(-1px); }

    .icon-btn.edit { background: #16a34a; }
    .icon-btn.del { background: #dc2626; }

    .icon-btn svg { display: block; }

    .btn-cetak {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: #0f766e;
      color: white;
      text-decoration: none;
      margin-left: 6px;
    }

    .btn-cetak:hover { background: #115e59; }

    table.table-container {
      width: 100%;
      border-collapse: collapse;
      min-width: 720px;
    }

    table.table-container thead th {
      text-align: left;
      padding: 16px;
      background: #f8fafc;
      color: #334155;
      font-weight: 700;
      border-bottom: 1px solid #e2e8f0;
    }

    table.table-container tbody td {
      padding: 16px;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
      color: #475569;
    }

    .col-check { width: 60px; text-align: center; }
    .col-name { min-width: 280px; }
    .col-count, .col-time, .col-action { text-align: center; }

    .product-cell {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #e2e8f0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #1e293b;
    }

    .product-title {
      font-weight: 700;
      color: #0f172a;
    }

    .product-sub {
      font-size: 13px;
      color: #64748b;
      margin-top: 4px;
    }

    tbody tr:hover { background: #f8fafc; }

    .table-empty {
      text-align: center;
      color: #64748b;
      padding: 24px 0;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(15, 23, 42, 0.54);
      overflow-y: auto;
      padding: 30px 0;
    }

    .modal-content {
      background: #fff;
      margin: auto;
      padding: 24px;
      border-radius: 20px;
      width: 92%;
      max-width: 780px;
      box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
      animation: fadeIn 0.25s ease;
      position: relative;
    }

    .modal-content h2 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 22px;
      color: #111827;
    }

    .close {
      position: absolute;
      right: 16px;
      top: 16px;
      color: #6b7280;
      font-size: 24px;
      font-weight: 700;
      cursor: pointer;
      line-height: 1;
    }

    .close:hover { color: #ef4444; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    @media (max-width: 1024px) {
      .table-card { padding: 18px; }
      table.table-container { min-width: 0; }
    }

    @media (max-width: 900px) {
      .out-dash { display: none; }
      .main-dashboard { padding: 16px 16px 28px; }
      .table-card-header { align-items: flex-start; }
      .header-actions { width: 100%; justify-content: flex-start; }
    }
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
            <input type="text" name="src_pengiriman" placeholder="Cari pemesan, produk, waktu" autocomplete="off" value="<?= htmlspecialchars($search_value); ?>">
            <button type="submit" name="cari_pengiriman">Cari</button>
          </form>
        </div>
        <button class="account-button" type="button">
          <img src="assets/profile-1335-svgrepo-com-light.svg" alt="account">
        </button>
      </div>

      <div class="main-dashboard">
        <div class="in-main-dashboard">
          <div class="wrapper">
            <h1 class="judul">Kirim Produk ke Luar</h1>

            <div class="table-card">
              <div class="table-card-header">
                <h2>Daftar Pengiriman</h2>
                <div class="header-actions">
                  <a href="kirim.php" class="button">↩ Kembali</a>
                  <a href="kirim_luar_tambah.php" class="button">✚ Kirim Produk</a>
                  <button id="openModal" class="button" type="button">🏠 Stock Gudang</button>
                  <a href="kirim_luar_convert.php" class="button">⛛ Cetak Data</a>
                  <a href="kirim_luar_logcetak.php" class="button">⛛ Cetak Log</a>
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
                  <?php if (mysqli_num_rows($kirim_luar) === 0): ?>
                    <tr>
                      <td colspan="5" class="table-empty">Belum ada data pengiriman.</td>
                    </tr>
                  <?php else: ?>
                    <?php $i = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($kirim_luar)) : ?>
                      <tr>
                        <td class="col-check"><?= $i; ?></td>
                        <td class="col-name">
                          <div class="product-cell">
                            <div class="avatar"><?= htmlspecialchars(strtoupper(substr($row['produk'], 0, 1))); ?></div>
                            <div>
                              <div class="product-title"><?= htmlspecialchars($row['produk']); ?></div>
                              <div class="product-sub">Pemesan: <?= htmlspecialchars($row['pemesan']); ?></div>
                            </div>
                          </div>
                        </td>
                        <td class="col-count"><?= (int)$row['jumlah']; ?> pcs</td>
                        <td class="col-time"><?= $row['waktu']; ?></td>
                        <td class="col-action">
                          <a href="kirim_luar_cetak.php?id=<?= $row['id']; ?>" target="_blank" class="btn-cetak" title="Cetak">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2h10l4 4v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="currentColor"/><path d="M7 2v4h10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          </a>
                          <a href="kirim_luar_ubah.php?id=<?= $row['id']; ?>" class="icon-btn edit" title="Ubah">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" fill="#fff"/></svg>
                          </a>
                          <a href="kirim_luar_hapus.php?id=<?= $row['id']; ?>" class="icon-btn del" title="Hapus" onclick="return confirm('Yakin mau hapus?');">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z" fill="#fff"/><path d="M9 4h6v2H9z" fill="#fff"/></svg>
                          </a>
                        </td>
                      </tr>
                      <?php $i++; ?>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <div id="myModal" class="modal">
              <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Total Stock Gudang</h2>
                <table class="table-container">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Nama Produk</th>
                      <th>Total Jumlah</th>
                      <th>Sumber Gudang</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($stock_count === 0): ?>
                      <tr><td colspan="4" class="table-empty">Belum ada data stok.</td></tr>
                    <?php else: ?>
                      <?php $i = 1; foreach ($stocks as $s): ?>
                        <tr>
                          <td><?= $i; ?></td>
                          <td><?= htmlspecialchars($s['produk']); ?></td>
                          <td><?= (int)$s['stok']; ?> pcs</td>
                          <td><?= htmlspecialchars($s['sumber']); ?></td>
                        </tr>
                        <?php $i++; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    var modal = document.getElementById('myModal');
    var btn = document.getElementById('openModal');
    var span = modal.querySelector('.close');

    if (btn) {
      btn.onclick = function () {
        modal.style.display = 'block';
      };
    }

    if (span) {
      span.onclick = function () {
        modal.style.display = 'none';
      };
    }

    window.onclick = function (e) {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    };

    var topSearchBar = document.getElementById('topSearchBar');
    var searchButton = document.querySelector('.search-button');
    if (searchButton) {
      searchButton.addEventListener('click', function () {
        topSearchBar.classList.toggle('active');
        var input = topSearchBar.querySelector('input');
        if (topSearchBar.classList.contains('active')) {
          input.focus();
        }
      });
    }
  </script>
  <script src="script.js"></script>
</body>
</html>