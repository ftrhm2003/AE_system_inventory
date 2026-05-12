<?php
require 'bawang_function.php';

$conn = mysqli_connect("localhost", "root", "", "ae_database");

// Ambil data log gudang
$query = "SELECT * FROM log_gudang1 ORDER BY waktu DESC";
$result = mysqli_query($conn, $query);

// Set header untuk unduh sebagai file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=log_gudang_bawang.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<h3>Log Gudang Produk Bawang</h3>";

echo "<br>";
echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Aksi</th>
        <th>Produk</th>
        <th>Jumlah</th>
        <th>Waktu</th>
        <th>Data Lama</th>
        <th>Data Baru</th>
      </tr>";

// Counter nomor urut
$no = 1;

// Isi tabel
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $no++ . "</td>"; // nomor urut
    echo "<td>{$row['aksi']}</td>";
    echo "<td>{$row['produk']}</td>";
    echo "<td>{$row['jumlah']}</td>";
    echo "<td>{$row['waktu']}</td>";
    echo "<td>{$row['data_lama']}</td>";
    echo "<td>{$row['data_baru']}</td>";
    echo "</tr>";
}

echo "</table>";
?>
