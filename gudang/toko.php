<?php
session_start();
require 'toko_function.php';


$stokToko = getStokTokoAgg();
$search_value = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari_pengiriman'])) {
    $search_value = trim($_POST['src_pengiriman']);
    $stokToko = cariProdukToko($search_value);
} else {
    $stokToko = getStokTokoAgg();
}


$produk_pertama = null;

if ($stokToko && mysqli_num_rows($stokToko) > 0) {
    $produk_pertama_row = mysqli_fetch_assoc($stokToko);
    $produk_pertama = $produk_pertama_row['produk'];
    
    
    mysqli_data_seek($stokToko, 0);
}


$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
//     $produk = trim($_POST['produk']);
//     $qty = (int)$_POST['qty'];
//     $harga = (int)getHargaToko($produk);
//     $stok = stokToko($produk);

//     if ($qty <= 0 || $qty > $stok) {
//         echo "<script>alert('Jumlah melebihi stok atau tidak valid');</script>";
//     } else {
//         if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
//         $found = false;
//         foreach ($_SESSION['cart'] as &$item) {
//             if ($item['produk'] === $produk) {
//                 $item['qty'] += $qty;
//                 $found = true;
//                 break;
//             }
//         }
//         unset($item);
//         if (!$found) {
//             $_SESSION['cart'][] = ['produk' => $produk, 'qty' => $qty, 'harga' => $harga];
//         }
//         echo "<script>alert('Ditambahkan ke keranjang');</script>";
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
    $produk = trim($_POST['produk']);
    $qty = (int)$_POST['qty'];
    $harga = (int)getHargaToko($produk);
    $stok = stokToko($produk);

    if ($qty <= 0 || $qty > $stok) {
        $_SESSION['msg'] = "Jumlah melebihi stok atau tidak valid";
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
        $_SESSION['msg'] = "Ditambahkan ke keranjang";
    }

    // Redirect supaya tidak reload POST
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Toko</title>
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

    .btn-tambah {
      background-color:#ffc107;
      border:1px solid #e0a800;
      color:#000;
      padding:4px 10px;
      border-radius:6px;
      cursor:pointer;
      font-size:13px;
      transition: all 0.2s ease;
    }
    .btn-tambah:hover {
      background-color:#e0a800;
      color:#fff;
    }

</style>
</head>
<body>

<?php if (isset($_SESSION['msg'])): ?>
  <script>alert("<?= $_SESSION['msg']; ?>");</script>
  <?php unset($_SESSION['msg']); ?>
<?php endif; ?>


<div class="wrapper">
  <h1 class="judul">AE Toko</h1>

  <div class="toolbar">
    <a href="index.php" class="button">↩ Kembali</a>
    <a href="toko_tambah_stock.php" class="button">✚ Tambah Produk</a>
    <a href="toko_ubah.php?produk=<?= urlencode($produk_pertama); ?>" class="button">💰 Ubah Harga</a>
    <a href="toko_log_price.php" class="button">🔽 Log Harga</a>
    <form action="" method="post" class="search-form">
      <input type="text" name="src_pengiriman" placeholder="cari data" autocomplete="off"
       value="<?= htmlspecialchars($search_value); ?>">
      <button type="submit" name="cari_pengiriman">🔍 Cari</button>
    </form>
    <a href="toko_checkout.php" class="button">🛒 Keranjang (<?= $cart_count; ?>)</a>
    <a href="toko_riwayat_penjualan.php" class="button">📈 Riwayat Penjualan</a>
  </div>

  <table class="table-container">
    <tr class="table-row header">
      <th>No</th>
      <th>Produk</th>
      <th>Stok</th>
      <th>Harga/pcs</th>
      <th>Aksi</th>
    </tr>
    <?php $i=1; ?>
    <?php while($row = mysqli_fetch_assoc($stokToko)): ?>
      <tr class="table-row">
        <td><?= $i; ?></td>
        <td><?= htmlspecialchars($row['produk']); ?></td>
        <td><?= (int)$row['stok']; ?></td>
        <td>Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
        <td>
        <form method="post" style="display:flex; align-items:center; gap:6px; justify-content:center;">
        <input type="hidden" name="produk" value="<?= htmlspecialchars($row['produk']); ?>">

        <input type="number" 
               name="qty" 
               value="1" 
               min="1" 
               max="<?= (int)$row['stok']; ?>" 
               style="width:60px; padding:4px; border:1px solid #ccc; border-radius:5px; text-align:center;">

        <button type="submit" 
                name="add_cart" 
                class="btn-tambah">+ Cart</button>
        </form>
        </td>
      </tr>
    <?php $i++; endwhile; ?>
  </table>
</div>

</script>
</body>
</html>