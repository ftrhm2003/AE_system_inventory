<?php
require 'kirim_luar_function.php';

$sqlStok = "
    SELECT produk, SUM(jumlah) AS stok, sumber
    FROM (
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari_pengiriman'])) {
    $search_value = trim($_POST['src_pengiriman'] ?? '');
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
    .wrapper {
        width: 100%;
        margin: auto;
    }

    .judul {
        text-align: center;
        margin: 0 0 10px;
    }

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

    .button:hover {
        background: #0b5ed7;
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

    .btn {
        display: inline-block;
        padding: 6px 12px;
        font-size: 14px;
        border-radius: 5px;
        text-decoration: none;
        color: #fff;
    }

    .btn-ubah {
        background-color: #007bff;
    }

    .btn-ubah:hover {
        background-color: #0056b3;
    }

    .btn-hapus {
        background-color: #dc3545;
    }

    .btn-hapus:hover {
        background-color: #a71d2a;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        padding: 8px;
        text-align: center;
        border: 1px solid #ccc;
    }

    th {
        background: #f2f2f2;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.48);
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
        box-shadow: 0 20px 40px rgba(17, 24, 39, 0.15);
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

    .modal-content .close:hover {
        color: #ef4444;
    }

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
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
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

    .modal-content .input-group .table-wrap {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .modal-content .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
    }

    .modal-content .product-table th,
    .modal-content .product-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }

    .modal-content .product-table th {
        background: #f8fafc;
        font-size: 13px;
        color: #475569;
    }

    .modal-content .product-table tr:last-child td {
        border-bottom: none;
    }

    .table-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        padding: 16px;
        margin-top: 10px;
        width: 100%;
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .table-card-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .all-btn {
        background: #0d6efd;
        color: #fff;
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
    }

    .all-btn:hover {
        background: #0b5ed7;
    }

    table.table-container {
        width: 100%;
        border-collapse: collapse;
    }

    table.table-container thead th {
        text-align: left;
        padding: 12px;
        background: #f5f6f7;
        color: #444;
        font-weight: 600;
        border-bottom: 1px solid #e8e8e8;
    }

    table.table-container tbody td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .col-check {
        width: 40px;
        text-align: center;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e9ecef;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #333;
    }

    .product-title {
        font-weight: 600;
    }

    .product-sub {
        font-size: 12px;
        color: #888;
    }

    tbody tr:hover {
        background: #fbfdff;
    }

    .icon-btn {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        margin-left: 6px;
        text-decoration: none;
    }

    .icon-btn.edit {
        background: #198754;
    }

    .icon-btn.del {
        background: #dc3545;
    }

    .icon-btn.cetak {
        background: #c5d402;
    }

    .icon-btn svg {
        display: block;
    }

    @media (max-width: 800px) {
        .product-sub {
            display: none;
        }
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
                                <h2>Daftar Produk</h2>
                                <div class="header-actions">
                                    <a href="pengiriman.html" class="button">↩ Kembali</a>
                                    <button type="button" id="openAddProduct" class="button">⛍ Kirim Produk</button>
                                    <button id="openModal" class="button">🏠︎ Stock Gudang</button>
                                    <a href="kirim_luar_convert.php" class="button">⛛ Cetak Data</a>
                                    <a href="kirim_luar_logcetak.php" class="button">⛛ Cetak Log</a>
                                </div>
                            </div>

                            <table class="table-container">
                                <thead>
                                    <tr>
                                        <th class="col-check">No</th>
                                        <th class="col-name">Produk</th>
                                        <th class="col-count">Terkirim</th>
                                        <th class="col-price">Harga/pcs</th>
                                        <th class="col-time">Waktu</th>
                                        <th class="col-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($kirim_luar) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="table-empty">Belum ada data pengiriman.</td>
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
                                                <td class="col-count"><?= (int) $row['jumlah']; ?> pcs</td>
                                                <td class="col-price">Rp <?= number_format($row['harga'], 2, '.', '.'); ?></td>
                                                <td class="col-time"><?= $row['waktu']; ?></td>
                                                <td class="col-action">
                                                    <a href="kirim_luar_cetak.php?id=<?= $row['id']; ?>" target="_blank" class="icon-btn cetak" title="Cetak">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z" fill="#fff"/>
                                                            <path d="M9 4h6v2H9z" fill="#fff"/>
                                                        </svg>
                                                    </a>
                                                    <a href="kirim_luar_ubah.php?id=<?= $row['id']; ?>" class="icon-btn edit" title="Ubah">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" fill="#fff"/>
                                                        </svg>
                                                    </a>
                                                    <a href="kirim_luar_hapus.php?id=<?= $row['id']; ?>" class="icon-btn del" title="Hapus" onclick="return confirm('Yakin mau hapus?');">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z" fill="#fff"/>
                                                            <path d="M9 4h6v2H9z" fill="#fff"/>
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php $i++; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close close-add-product">&times;</span>
            <h2>Kirim Produk ke Luar</h2>
            <form action="kirim_luar_function.php" method="post">
                <div class="input-group">
                    <label for="pemesan">Pemesan</label>
                    <input type="text" id="pemesan" name="pemesan" placeholder="Isi nama pemesan" required>
                </div>

                <div class="input-group">
                    <label>Pilih Produk</label>
                    <div class="table-wrap">
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Pilih</th>
                                    <th>Produk</th>
                                    <th>Stok</th>
                                    <th>Gudang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($stock_count === 0): ?>
                                    <tr><td colspan="4">Belum ada data stok.</td></tr>
                                <?php else: ?>
                                    <?php $j = 1; foreach ($stocks as $s): ?>
                                        <tr>
                                            <td><input type="radio" name="produk" value="<?= htmlspecialchars($s['produk']) ?>|<?= htmlspecialchars($s['sumber']) ?>" required></td>
                                            <td><?= htmlspecialchars($s['produk']); ?></td>
                                            <td><?= (int) $s['stok']; ?> pcs</td>
                                            <td><?= htmlspecialchars($s['sumber']); ?></td>
                                        </tr>
                                    <?php $j++; endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="input-group">
                    <label for="harga">Harga</label>
                    <input type="number" id="harga" name="harga" placeholder="Isi harga" min="1" required>
                </div>

                <div class="input-group">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" placeholder="Isi jumlah" min="1" required>
                </div>

                <div class="button-group">
                    <button type="submit" name="submit_kirim" class="button primary">Kirim</button>
                    <button type="button" class="button secondary" id="closeAddProduct">Batal</button>
                </div>
            </form>
        </div>
    </div>

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
                    <?php $i = 1; foreach ($stocks as $s): ?>
                        <tr>
                            <td><?= $i; ?></td>
                            <td><?= htmlspecialchars($s["produk"]); ?></td>
                            <td><?= (int) $s["stok"]; ?> pcs</td>
                            <td><?= htmlspecialchars($s["sumber"]); ?></td>
                        </tr>
                    <?php $i++; endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        var stockModal = document.getElementById("myModal");
        var addProductModal = document.getElementById("addProductModal");
        var stockBtn = document.getElementById("openModal");
        var addProductBtn = document.getElementById("openAddProduct");
        var closeStock = stockModal ? stockModal.querySelector(".close") : null;
        var closeAddProduct = addProductModal ? addProductModal.querySelector(".close") : null;
        var cancelAddProduct = document.getElementById("closeAddProduct");

        function openModal(modal) {
            if (modal) {
                modal.style.display = "block";
            }
        }

        function closeModal(modal) {
            if (modal) {
                modal.style.display = "none";
            }
        }

        if (stockBtn && stockModal) {
            stockBtn.onclick = function () {
                openModal(stockModal);
            };
        }

        if (addProductBtn && addProductModal) {
            addProductBtn.onclick = function () {
                openModal(addProductModal);
            };
        }

        if (closeStock && stockModal) {
            closeStock.onclick = function () {
                closeModal(stockModal);
            };
        }

        if (closeAddProduct && addProductModal) {
            closeAddProduct.onclick = function () {
                closeModal(addProductModal);
            };
        }

        if (cancelAddProduct) {
            cancelAddProduct.onclick = function () {
                closeModal(addProductModal);
            };
        }

        window.onclick = function (e) {
            if (stockModal && e.target === stockModal) {
                closeModal(stockModal);
            }
            if (addProductModal && e.target === addProductModal) {
                closeModal(addProductModal);
            }
        };

        var topSearchBar = document.getElementById("topSearchBar");
        var searchButton = document.querySelector(".search-button");

        if (searchButton && topSearchBar) {
            searchButton.addEventListener("click", function () {
                topSearchBar.classList.toggle("active");
                var input = topSearchBar.querySelector("input");
                if (topSearchBar.classList.contains("active") && input) {
                    input.focus();
                }
            });
        }
    </script>
</body>
</html>