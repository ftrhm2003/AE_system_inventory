<?php
session_start();
require 'toko_function.php';


$stokToko = getStokTokoAgg();
$stokGudangList = getSemuaStokGudang();
$hargaProdukList = [];
$hargaProdukResult = mysqli_query($conn, "SELECT * FROM toko_price ORDER BY produk ASC");
if ($hargaProdukResult) {
    while ($row = mysqli_fetch_assoc($hargaProdukResult)) {
        $hargaProdukList[] = $row;
    }
}
$search_value = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari_pengiriman'])) {
    $search_value = trim($_POST['src_pengiriman']);
    $stokToko = cariProdukToko($search_value);
} else {
    $stokToko = getStokTokoAgg();
}


$produk_pertama = null;

function reservedQty($produk) {
    $qty = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if ($item['produk'] === $produk) {
                $qty += $item['qty'];
            }
        }
    }
    return $qty;
}

function availableStock($produk) {
    $stok = stokToko($produk);
    $reserved = reservedQty($produk);
    return max(0, $stok - $reserved);
}

if ($stokToko && mysqli_num_rows($stokToko) > 0) {
    $produk_pertama_row = mysqli_fetch_assoc($stokToko);
    $produk_pertama = $produk_pertama_row['produk'];
    mysqli_data_seek($stokToko, 0);
}


function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
    $produk = trim($_POST['produk']);
    $qty = (int)$_POST['qty'];
    $harga = (int)getHargaToko($produk);
    $stok = stokToko($produk);
    $result = ['success' => false, 'message' => 'Terjadi kesalahan', 'cart_count' => $cart_count];

    if ($qty <= 0 || $qty > $stok) {
        $result['message'] = 'Jumlah melebihi stok atau tidak valid';
    } else {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['produk'] === $produk) {
                $item['qty'] += $qty;
                $found = true;
                break;
            }
        }
        unset($item);
        if (!$found) {
            $_SESSION['cart'][] = ['produk' => $produk, 'qty' => $qty, 'harga' => $harga];
        }
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['qty'];
        }
        $result = ['success' => true, 'message' => 'Ditambahkan ke keranjang', 'cart_count' => $cart_count];
    }

    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Toko</title>
<link rel="stylesheet" type="text/css" href="style_dash.css">

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

    .btn-tambah {
        background: #f1c40f;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-tambah:hover {
        background: #c5d402;
    }

    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        min-width: 220px;
        padding: 12px 16px;
        background: rgba(17, 24, 39, 0.95);
        color: #fff;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.25s ease, transform 0.25s ease;
        pointer-events: none;
        z-index: 9999;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .fly-item {
        position: fixed;
        z-index: 9999;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1c40f;
        color: #111;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        pointer-events: none;
        transition: transform 0.8s ease, opacity 0.8s ease;
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

<div id="toast" class="toast"></div>

    <div class="all-dash">
        <div class="out-dash" id="sidebar-container"></div>

        <div class="top-all-dash">
            <div class="top-dash">
                <button class="search-button" type="button">
                    <img src="assets/search-alt-2-svgrepo-com.svg" alt="search">
                </button>

                <div id="topSearchBar" class="top-search">
                    <form action="" method="post" class="search-form">
                        <input type="text" name="src_pengiriman" placeholder="Cari produk, gudang, waktu" autocomplete="off" value="<?= htmlspecialchars($search_value); ?>">
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
                    <h1 class="judul">Toko Catalog</h1>

                    <div class="table-card">
                      <div class="table-card-header">
                        <h2>Daftar Produk</h2>
                        <div class="header-actions">
                          <a href="manajemen_toko.html" class="button">↩ Kembali</a>
                          <button type="button" class="button" onclick="openModal('addStockModal')">✚ Tambah Produk</button>
                          <button type="button" class="button" onclick="openModal('editPriceModal')">$ Ubah Harga</button>
                          <a href="toko_log_price.php" class="button">⛛ Log Harga</a>
                          <a id="cartButton" href="toko_checkout.php" class="button">𖠩 Keranjang (<?= $cart_count; ?>)</a>
                        </div>
                      </div>

                      <table class="table-container">
                        <thead>
                          <tr>
                            <th class="col-check">No</th>
                            <th>Produk</th>
                            <th>Stok</th>
                            <th>Harga/pcs</th>
                            <th>Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!$stokToko || mysqli_num_rows($stokToko) === 0): ?>
                            <tr>
                              <td colspan="5" class="table-empty">Data yang dicari tidak ada.</td>
                            </tr>
                          <?php else: ?>
                            <?php $i = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($stokToko)): ?>
                              <tr>
                                <td class="col-check"><?= $i; ?></td>
                                <td><?= htmlspecialchars($row['produk']); ?></td>
                                <td><?= availableStock($row['produk']); ?></td>
                                <td>Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
                                <td>
                                  <form name="add_cart_form" method="post" style="display:flex; align-items:center; gap:6px; justify-content:center; margin:0;">
                                    <input type="hidden" name="produk" value="<?= htmlspecialchars($row['produk']); ?>">
                                    <input type="number"
                                          name="qty"
                                          value="1"
                                          min="1"
                                          max="<?= availableStock($row['produk']); ?>"
                                          style="width:60px; padding:4px; border:1px solid #ccc; border-radius:5px; text-align:center;">
                                    <button type="submit" name="add_cart" class="btn-tambah">+ Cart</button>
                                  </form>
                                </td>
                              </tr>
                            <?php $i++; endwhile; ?>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>

                    <div id="addStockModal" class="modal">
                      <div class="modal-content">
                        <span class="close" onclick="closeModal('addStockModal')">&times;</span>
                        <h2>Tambah Produk ke Toko</h2>
                        <form action="toko_tambah_stock.php" method="post">
                          <div class="input-group">
                            <label for="modal_produk">Pilih Produk</label>
                            <select name="produk" id="modal_produk" required>
                              <option value="">-- Pilih Produk --</option>
                              <?php foreach ($stokGudangList as $item): ?>
                                <option value="<?= htmlspecialchars($item['produk']) . '|' . $item['gudang'] ?>">
                                  <?= htmlspecialchars($item['produk']) ?> (Stok <?= (int)$item['stok']; ?> pcs, Gudang <?= htmlspecialchars($item['gudang']); ?>)
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="input-group">
                            <label for="modal_qty">Jumlah</label>
                            <input type="number" name="qty" id="modal_qty" placeholder="isi jumlah" min="1" required>
                          </div>
                          <div class="button-group">
                            <button type="submit" name="submit" class="button primary">Tambah Stock</button>
                            <button type="button" class="button secondary" onclick="closeModal('addStockModal')">Batal</button>
                          </div>
                        </form>
                      </div>
                    </div>

                    <div id="editPriceModal" class="modal">
                      <div class="modal-content">
                        <span class="close" onclick="closeModal('editPriceModal')">&times;</span>
                        <h2>Ubah Harga Produk</h2>
                        <form action="toko_ubah.php" method="post">
                          <div class="input-group">
                            <label for="modal_produk_lama">Produk</label>
                            <select name="produk_lama" id="modal_produk_lama" required>
                              <option value="">-- Pilih Produk --</option>
                              <?php foreach ($hargaProdukList as $row): ?>
                                <option value="<?= htmlspecialchars($row['produk']) ?>">
                                  <?= htmlspecialchars($row['produk']) ?> (Rp <?= number_format((int)$row['harga_jual'], 0, ',', '.') ?>)
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="input-group">
                            <label for="modal_produk_baru">Nama Produk Baru (opsional)</label>
                            <input type="text" name="produk_baru" id="modal_produk_baru" placeholder="Kosongkan jika tidak ganti nama">
                          </div>
                          <div class="input-group">
                            <label for="modal_harga_jual">Harga Jual Baru</label>
                            <input type="text" name="harga_jual" id="modal_harga_jual" placeholder="Contoh: 15000" required>
                          </div>
                          <div class="button-group">
                            <button type="submit" class="button primary">Simpan Perubahan</button>
                            <button type="button" class="button secondary" onclick="closeModal('editPriceModal')">Batal</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
<script src="script.js"></script>
<script>
    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function showToast(message) {
        var toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(function () {
            toast.classList.remove('show');
        }, 2000);
    }

    function animateAddToCart(button) {
        var cartButton = document.getElementById('cartButton');
        if (!cartButton || !button) return;

        var start = button.getBoundingClientRect();
        var end = cartButton.getBoundingClientRect();
        var fly = document.createElement('div');
        fly.className = 'fly-item';
        fly.textContent = '+1';
        fly.style.left = start.left + start.width / 2 - 14 + 'px';
        fly.style.top = start.top + start.height / 2 - 14 + 'px';
        document.body.appendChild(fly);

        requestAnimationFrame(function () {
            fly.style.transform = 'translate(' + (end.left - start.left) + 'px, ' + (end.top - start.top) + 'px) scale(0.3)';
            fly.style.opacity = '0';
        });

        fly.addEventListener('transitionend', function () {
            fly.remove();
        });
    }

    function setupAddCartForms() {
        var forms = document.querySelectorAll('form[name="add_cart_form"]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var formData = new FormData(form);
                formData.append('ajax', '1');
                formData.append('add_cart', '1');

                fetch(form.action || window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        var cartButton = document.getElementById('cartButton');
                        if (cartButton) {
                            cartButton.textContent = '𖠩 Keranjang (' + data.cart_count + ')';
                        }
                        var qtyInput = form.querySelector('input[name="qty"]');
                        var product = form.querySelector('input[name="produk"]').value;
                        var qty = parseInt(qtyInput.value, 10) || 0;
                        if (qty > 0) {
                            var row = form.closest('tr');
                            if (row) {
                                var stockCell = row.querySelector('td:nth-child(3)');
                                if (stockCell) {
                                    var currentStock = parseInt(stockCell.textContent, 10) || 0;
                                    stockCell.textContent = Math.max(0, currentStock - qty);
                                }
                                qtyInput.max = Math.max(0, (parseInt(qtyInput.max, 10) || 0) - qty);
                            }
                        }
                        showToast(data.message);
                        animateAddToCart(form.querySelector('button[type="submit"]'));
                    } else {
                        showToast(data.message);
                    }
                })
                .catch(function () {
                    showToast('Gagal menambahkan ke keranjang');
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupAddCartForms();

        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal.id);
                }
            });
        });

        var topSearchBar = document.getElementById('topSearchBar');
        var searchButton = document.querySelector('.search-button');

        if (searchButton && topSearchBar) {
            searchButton.addEventListener('click', function () {
                topSearchBar.classList.toggle('active');
                var input = topSearchBar.querySelector('input');
                if (topSearchBar.classList.contains('active') && input) {
                    input.focus();
                }
            });
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(function (modal) {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
</script>
</body>
</html>