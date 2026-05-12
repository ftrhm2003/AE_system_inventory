<?php
require 'toko_function.php';

$conn = mysqli_connect("localhost", "root", "", "ae_database");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Header untuk file XLS
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=log_toko_price_" . date('Y-m-d_H-i-s') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// --- Helper ---
function formatCurrencyIDR($value) {
    $num = preg_replace('/[^\d]/', '', (string)$value);
    if ($num === '') return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return 'Rp ' . number_format((float)$num, 2, '.', '.');
}

function formatLogInline($text) {
    $parts = explode('|', (string)$text);
    $out = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;

        if (strpos(strtolower($part), 'harga') !== false) {
            // Pecah "Harga: 15000"
            [$label, $val] = array_map('trim', explode(':', $part, 2));
            $out[] = htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ': ' . formatCurrencyIDR($val);
        } else {
            $out[] = htmlspecialchars($part, ENT_QUOTES, 'UTF-8');
        }
    }
    return implode('<br>', $out);
}
// -------------

// Ambil data log
$query = "SELECT * FROM log_toko_price ORDER BY updated_at DESC";
$result = mysqli_query($conn, $query);

echo "<h2>Log Harga Toko</h2>";

echo "<br>";
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Aksi</th>
        <th>Produk</th>
        <th>Harga Jual</th>
        <th>Waktu Update</th>
        <th>Data Lama</th>
        <th>Data Baru</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . (int)$row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['aksi'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . htmlspecialchars($row['produk'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . formatCurrencyIDR($row['harga_jual']) . "</td>";
    echo "<td>" . htmlspecialchars($row['updated_at'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . formatLogInline($row['data_lama']) . "</td>";
    echo "<td>" . formatLogInline($row['data_baru']) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
