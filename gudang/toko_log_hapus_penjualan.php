<?php
require 'toko_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=log_hapus_penjualan_" . date('Y-m-d_H-i-s') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$conn = mysqli_connect("localhost", "root", "", "ae_database");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// --- Helper format Rp ---
function formatCurrencyIDR($value) {
    $num = preg_replace('/[^\d]/', '', (string)$value);
    if ($num === '') return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return 'Rp ' . number_format((float)$num, 2, '.', '.'); // Rp 15.000,00
}

// --- Format detail produk ---
function formatDetailProduk($detail) {
    // regex: cari angka di dalam kurung setelah "x"
    return preg_replace_callback('/\(\s*(\d+)\s*x\s*(\d+)\s*\)/', function($matches) {
        $qty = $matches[1];
        $price = formatCurrencyIDR($matches[2]);
        return "($qty x $price)";
    }, $detail);
}
// ---------------------------

$result = mysqli_query($conn, "SELECT * FROM log_hapus_penjualan ORDER BY waktu_hapus DESC");


echo "<h2>Log Penjualan Toko</h2>";

echo "<br>";
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Aksi</th>
        <th>Order ID</th>
        <th>Pembeli</th>
        <th>Total</th>
        <th>Waktu Transaksi</th>
        <th>Detail Produk</th>
        <th>Waktu Hapus</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>" . (int)$row['id'] . "</td>
            <td>" . htmlspecialchars($row['aksi'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($row['pembeli'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . formatCurrencyIDR($row['total']) . "</td>
            <td>" . htmlspecialchars($row['waktu_transaksi'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . formatDetailProduk($row['detail_produk']) . "</td>
            <td>" . htmlspecialchars($row['waktu_hapus'], ENT_QUOTES, 'UTF-8') . "</td>
          </tr>";
}
echo "</table>";
?>
