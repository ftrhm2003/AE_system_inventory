<?php
require 'toko_function.php';

$search_value = '';
if (isset($_POST['cari_pengiriman'])) {
    $search_value = $_POST['src_pengiriman'] ?? '';
    $result = cariRiwayatPenjualan($search_value);
} else {
    $result = getRiwayatPenjualan();
}

function rp($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            <button class="search-button">
                <img src="assets/search-alt-2-svgrepo-com.svg" alt="search">
            </button>

            <div id="topSearchBar" class="top-search">
              <form action="" method="post" class="search-form">
                <input type="text" name="src_pengiriman" placeholder="cari data" autocomplete="off"
                value="<?= htmlspecialchars($search_value); ?>">
                <button type="submit" name="cari_pengiriman"> Cari</button>
              </form>
            </div>

            <button class="account-button">
                <img src="assets/profile-1335-svgrepo-com-light.svg" alt="account">
            </button>
        </div>

        <div class="main-dashboard">
            <div class="in-main-dashboard">
              <div class="wrapper">
                  <h1 class="judul">Penjualan Toko</h1>
                  <div class="table-card">
                      <div class="table-card-header">
                          <h2>Daftar Riwayat</h2>
                          <div class="header-actions">
                              <a href="manajemen_toko.html" class="button">↩ Kembali</a>
                              <a href="toko_riwayat_convert.php" class="button">⛛ Export ke Excel</a>
                              <a href="toko_log_hapus_penjualan.php" class="button">⛛ Log Penjualan</a>
                          </div>
                      </div>

                      <table class="table-container">
                           <thead>
                                <tr>
                                    <th class="col-check">No</th>
                                    <th class="col-time">Waktu Checkout</th>
                                    <th class="col-name">Pembeli</th>
                                    <th class="col-name">Produk</th>
                                    <th class="col-count">Qty</th>
                                    <th class="col-price">Harga/pcs</th>
                                    <th class="col-price">Subtotal</th>
                                    <th class="col-count">Total Order</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
    <?php
    // 1. Grouping data terlebih dahulu
    $data_grouped = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data_grouped[$row['order_id']][] = $row;
        }
    }
    ?>

    <?php if (empty($data_grouped)): ?>
        <tr>
            <td colspan="9" class="table-empty">Data riwayat penjualan tidak ada.</td>
        </tr>
    <?php else: ?>
        <?php 
        $i = 1; 
        foreach ($data_grouped as $order_id => $rows): 
            $rowspan = count($rows);
            foreach ($rows as $index => $row): 
        ?>
            <tr class="table-row">
                <?php if ($index === 0): ?>
                    <td class="col-check" rowspan="<?= $rowspan ?>"><?= $i; ?></td>
                    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($row['waktu']) ?></td>
                    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($row['pembeli']) ?></td>
                <?php endif; ?>

                <td><?= htmlspecialchars($row['produk']) ?></td>
                <td><?= (int)$row['qty'] ?></td>
                <td>Rp <?= number_format($row['harga'], 2, '.', '.') ?></td>
                <td>Rp <?= number_format($row['subtotal'], 2, '.', '.') ?></td>

                <?php if ($index === 0): ?>
                    <td rowspan="<?= $rowspan ?>">Rp <?= number_format($row['total'], 2, '.', '.') ?></td>
                    <td rowspan="<?= $rowspan ?>">
                        <a href="toko_penjualan_hapus.php?id=<?= $order_id ?>" 
                           class="icon-btn del" 
                           title="Hapus" 
                           onclick="return confirm('Yakin mau hapus?');">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z" fill="#fff"/>
                                <path d="M9 4h6v2H9z" fill="#fff"/>
                            </svg>
                        </a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php 
            endforeach; 
            $i++; 
        endforeach; 
        ?>
    <?php endif; ?>
</tbody>
                      </table>
                  </div>    
              </div>
            </div>
        </div>
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
        var topSearchBar = document.getElementById("topSearchBar");
        var searchButton = document.querySelector(".search-button");

        function openModal(modalElement) {
            if (modalElement) {
                modalElement.style.display = "block";
            }
        }

        function closeModal(modalElement) {
            if (modalElement) {
                modalElement.style.display = "none";
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

        if (window) {
            window.onclick = function (e) {
                if (stockModal && e.target === stockModal) {
                    closeModal(stockModal);
                }
                if (addProductModal && e.target === addProductModal) {
                    closeModal(addProductModal);
                }
            };
        }

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