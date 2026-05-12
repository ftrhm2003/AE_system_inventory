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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Penjualan</title>
    <link rel="stylesheet" type="text/css" href="style.css">
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
  <h1 class="judul">Penjualan Toko</h1>

  <!-- Toolbar -->
  <div class="toolbar">
    <a href="toko.php" class="button">↩ Kembali</a>
    <form action="" method="post" class="search-form">
      <input type="text" name="src_pengiriman" placeholder="cari data" autocomplete="off"
       value="<?= htmlspecialchars($search_value); ?>">
      <button type="submit" name="cari_pengiriman">🔍 Cari</button>
    </form>
    <a href="toko_riwayat_convert.php" class="button">🔽 Export ke Excel</a>
    <a href="toko_log_hapus_penjualan.php" class="button">🔽 Log Penjualan</a>
  </div>

  <table class="table-container">
    <tr class="table-row header">
        <th>No</th>
        <th>Waktu Checkout</th>
        <th>Pembeli</th>
        <th>Produk</th>
        <th>Qty</th>
        <th>Harga/pcs</th>
        <th>Subtotal</th>
        <th>Total Order</th>
        <th>Aksi</th>
    </tr>

    <?php
    $data_grouped = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data_grouped[$row['order_id']][] = $row;
    }

    $i = 1;
    foreach ($data_grouped as $order_id => $rows):
        $rowspan = count($rows);
        foreach ($rows as $index => $row):
    ?>
        <tr class="table-row">
            <?php if ($index === 0): ?>
                <td rowspan="<?= $rowspan ?>"><?= $i++ ?></td>
                <td rowspan="<?= $rowspan ?>"><?= $row['waktu'] ?></td>
                <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($row['pembeli']) ?></td>
            <?php endif; ?>

            <td><?= htmlspecialchars($row['produk']) ?></td>
            <td><?= (int)$row['qty'] ?></td>
            <td>Rp <?= number_format($row['harga'], 2, '.', '.') ?></td>
            <td>Rp <?= number_format($row['subtotal'], 2, '.', '.') ?></td>

            <?php if ($index === 0): ?>
                <td rowspan="<?= $rowspan ?>">Rp <?= number_format($row['total'], 2, '.', '.') ?></td>
                <td rowspan="<?= $rowspan ?>">
                  <a href="toko_penjualan_hapus.php?id=<?= $order_id ?>" class="btn btn-hapus"onclick="return confirm('Yakin ingin menghapus riwayat ini?')">hapus</a>
                </td>
            <?php endif; ?>
        </tr>
    <?php
        endforeach;
    endforeach;
    ?>
  </table>
</div>

</body>
</html>
