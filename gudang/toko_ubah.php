<?php
require 'toko_function.php'; // pastikan sudah ada koneksi $conn dan fungsi ubahHargaProduk()

// ==== PROSES FORM ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_lama       = isset($_POST['produk_lama']) ? trim($_POST['produk_lama']) : '';
    $produk_baru_input = isset($_POST['produk_baru']) ? trim($_POST['produk_baru']) : '';
    $harga_input       = isset($_POST['harga_jual'])  ? $_POST['harga_jual'] : '0';

    if ($produk_lama === '') {
        alertBack('Produk lama tidak ditemukan di form');
        exit;
    }

    // Jika produk_baru kosong → gunakan produk_lama
    $produk_baru = $produk_baru_input !== '' ? $produk_baru_input : $produk_lama;

    // Bersihkan harga (hapus titik/koma/simbol)
    $harga_jual_baru = (int)preg_replace('/\D/', '', $harga_input);

    if ($harga_jual_baru <= 0) {
        alertBack('Harga harus lebih dari 0');
        exit;
    }

    if (ubahHargaProduk($produk_lama, $produk_baru, $harga_jual_baru)) {
        echo "<script>alert('Harga berhasil diubah');window.location.href='toko.php';</script>";
        exit;
    } else {
        alertBack('Gagal mengubah harga');
        exit;
    }
}

// ==== AMBIL DATA UNTUK FORM ====
$produkList = mysqli_query($conn, "SELECT * FROM toko_price ORDER BY produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Harga Produk</title>
    <link rel="stylesheet" type="text/css" href="input.css">
    <script src="https://unpkg.com/imask"></script>
</head>
<body>

<h2>Ubah Harga Produk</h2>
<form action="" method="post">
    <label for="produk_lama">Produk Lama:</label>
    <select name="produk_lama" id="produk_lama" required>
        <option value="">-- Pilih Produk --</option>
        <?php while ($row = mysqli_fetch_assoc($produkList)): ?>
            <option value="<?= htmlspecialchars($row['produk']) ?>">
                <?= htmlspecialchars($row['produk']) ?> (Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>)
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="produk_baru">Produk Baru (opsional):</label>
    <input type="text" name="produk_baru" id="produk_baru" placeholder="Kosongkan jika tidak ganti nama">
    <br><br>

    <label for="harga_jual">Harga Jual Baru:</label>
    <input type="text" name="harga_jual" id="harga_jual" required>
    <br><br>

    <button type="submit">Simpan Perubahan</button>
</form>

<script>
var priceMask = IMask(document.getElementById('harga_jual'), {
    mask: Number,
    thousandsSeparator: '.',
    radix: ',',
    mapToRadix: ['.']
});
</script>

</body>
</html>
