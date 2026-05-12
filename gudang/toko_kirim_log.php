<?php
require 'toko_function.php'; 

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=log_kirim_toko_export.xls");

$query = "SELECT * FROM log_kirim_toko ORDER BY waktu DESC";
$result = mysqli_query($conn, $query);

function formatDataLog($dataString) {
    // Pecah berdasarkan baris
    $lines = explode("\n", $dataString);
    $output = "";

    foreach ($lines as $line) {
        // Hilangkan spasi berlebih
        $line = trim($line);

        // Cek apakah baris mengandung "Harga:"
        if (stripos($line, "Harga:") === 0) {
            $parts = explode(":", $line, 2);
            $harga = trim($parts[1]);

            if (is_numeric($harga)) {
                $harga = "Rp " . number_format($harga, 2, '.', '.');
            }

            $output .= $parts[0] . ": " . $harga . "\n";

        // Cek apakah baris mengandung "Jumlah:"
        } elseif (stripos($line, "Jumlah:") === 0) {
            $parts = explode(":", $line, 2);
            $jumlah = trim($parts[1]);

            if (is_numeric($jumlah)) {
                $jumlah .= " pcs";
            }

            $output .= $parts[0] . ": " . $jumlah . "\n";

        } else {
            $output .= $line . "\n";
        }
    }

    return nl2br(htmlspecialchars(trim($output)));
}
?>

<h2>Log Aktivitas Kirim Toko</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Aksi</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Harga Jual</th>
            <th>Gudang Asal</th>
            <th>Waktu</th>
            <th>Data Lama</th>
            <th>Data Baru</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['aksi']) ?></td>
            <td><?= htmlspecialchars($row['produk']) ?></td>
            <td><?= $row['jumlah'] ?> pcs</td>
            <td>Rp <?= number_format($row['harga_jual'], 2, '.', '.') ?></td>
            <td><?= htmlspecialchars($row['gudang_asal']) ?></td>
            <td><?= $row['waktu'] ?></td>
            <td><?= formatDataLog($row['data_lama']) ?></td>
            <td><?= formatDataLog($row['data_baru']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
