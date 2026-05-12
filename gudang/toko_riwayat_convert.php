<?php 
require 'toko_function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=toko_penjualan_export.xls");

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

<h2>Data Penjualan Toko</h2>
<table border="1" cellspacing="0" cellpadding="5">
    <tr style="font-weight: bold; text-align: center;">
        <th>No</th>
        <th>Waktu Checkout</th>
        <th>Pembeli</th>
        <th>Produk</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
        <th>Total Order</th>
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
        <tr>
            <?php if ($index === 0): ?>
                <td rowspan="<?= $rowspan ?>" style="vertical-align: middle;"><?= $i++ ?></td>
                <td rowspan="<?= $rowspan ?>" style="vertical-align: middle;"><?= $row['waktu'] ?></td>
                <td rowspan="<?= $rowspan ?>" style="vertical-align: middle;"><?= htmlspecialchars($row['pembeli']) ?></td>
            <?php endif; ?>

            <td><?= htmlspecialchars($row['produk']) ?></td>
            <td><?= (int)$row['qty'] ?></td>
            <td>Rp <?= number_format($row["harga"], 2, '.', '.'); ?></td>
            <td>Rp <?= number_format($row["subtotal"], 2, '.', '.'); ?></td>

            <?php if ($index === 0): ?>
                <td rowspan="<?= $rowspan ?>" style="vertical-align: middle;">Rp <?= number_format($row["total"], 2, '.', '.'); ?></td>
            <?php endif; ?>
        </tr>
    <?php
        endforeach;
    endforeach;
    ?>
</table>
