<?php
require 'kirim_luar_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=log_aktivitas_export.xls");

$query = "SELECT * FROM log_kirim_luar ORDER BY waktu DESC";
$result = mysqli_query($conn, $query);

function formatDataLog($dataString) {
    // Pecah berdasarkan baris
    $lines = explode("\n", $dataString);
    $output = "";

    foreach ($lines as $line) {
        // Cek apakah baris mengandung "Harga:"
        if (stripos($line, "Harga:") !== false) {
            // Ambil angka setelah "Harga:"
            $parts = explode(":", $line, 2);
            $harga = trim($parts[1]);

            // Format angka ke Rupiah
            if (is_numeric($harga)) {
                $harga = "Rp " . number_format($harga, 2, '.', '.');
            }

            $output .= $parts[0] . ": " . $harga . "\n";
        } else {
            $output .= $line . "\n";
        }
    }

    return nl2br(htmlspecialchars(trim($output)));
}
?>

<h2>Log Aktivitas Kirim Luar</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Aksi</th>
            <th>Pemesan</th>
            <th>Produk</th>
            <th>Terkirim</th>
            <th>Harga/pcs</th>
            <th>Waktu</th>
            <th>Data Lama</th>
            <th>Data Baru</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['aksi'] ?></td>
            <td><?= htmlspecialchars($row['pemesan']) ?></td>
            <td><?= htmlspecialchars($row['produk']) ?></td>
            <td><?= $row['jumlah'] ?> pcs</td>
            <td>Rp <?= number_format($row['harga'], 2, '.', '.') ?></td>
            <td><?= $row['waktu'] ?></td>
            <td><?= formatDataLog($row['data_lama']) ?></td>
            <td><?= formatDataLog($row['data_baru']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
