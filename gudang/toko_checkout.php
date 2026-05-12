<?php
session_start();
require 'toko_function.php';

$cart = $_SESSION['cart'] ?? [];

if (isset($_GET['del'])) {
    $idx = (int)$_GET['del'];
    if (isset($cart[$idx])) {
        unset($cart[$idx]);
        $_SESSION['cart'] = array_values($cart);
    }
    header("Location: toko_checkout.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_checkout'])) {
    $pembeli = trim($_POST['pembeli']);
    if ($pembeli === '') {
        echo "<script>alert('Nama pembeli wajib diisi');</script>";
    } elseif (empty($cart)) {
        echo "<script>alert('Keranjang kosong');</script>";
    } else {
        $items = [];
        foreach ($cart as $d) {
            $items[] = ['produk' => $d['produk'], 'qty' => $d['qty'], 'harga' => $d['harga']];
        }
        $order_id = checkoutToko($pembeli, $items);
        if ($order_id) {
            $_SESSION['cart'] = [];
            echo "<script>alert('Checkout sukses!');window.location.href='toko_invoice.php?id=$order_id';</script>";
            exit;
        } else {
            echo "<script>alert('Checkout gagal');</script>";
        }
    }
}

$grand = 0;
foreach ($cart as $i => $d) {
    $sub = $d['qty'] * $d['harga'];
    $cart[$i]['subtotal'] = $sub;
    $grand += $sub;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Keranjang Belanja</title>
<link rel="stylesheet" type="text/css" href="input.css">
</head>
<body>

<form method="post">
  <div class="container">
    <div class="title">Keranjang Belanja</div>
    <div style="text-align: center;">
        <a href="toko.php">⬅ Kembali ke Toko</a>
    </div>
    <div class="form">
    <?php if (empty($cart)): ?>
        <p style="text-align: center; margin: 20px 0;">Keranjang kosong.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
        <thead>
          <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga/pcs</th>
            <th>Subtotal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($cart as $i => $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['produk']); ?></td>
            <td><?= $d['qty']; ?></td>
            <td>Rp <?= number_format($d["harga"], 2, '.', '.'); ?></td>
            <td>Rp <?= number_format($d["subtotal"], 2, '.', '.'); ?></td>
            <td><a href="?del=<?= $i; ?>" onclick="return confirm('Hapus item?')">Hapus</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    </div>

    <div class="total">Total: Rp <?= number_format($grand, 2, '.', '.'); ?></div>

        <label for="pembeli">Nama Pembeli</label>
        <div class="input-group">
            <input type="text" name="pembeli" id="pembeli" placeholder="Isi nama pembeli" required>
        </div>

        <button type="submit" name="submit_checkout">Checkout Sekarang</button>
    <?php endif; ?>
    </div>
  </div>
</form>

</body>
</html>
