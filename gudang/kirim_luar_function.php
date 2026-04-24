<?php 

$conn = mysqli_connect("localhost", "root", "", "ae_database");

function jualBarang($produk, $jumlah, $jenis, $harga, $pemesan) {
    global $conn;

    $queryStok = mysqli_query($conn, "SELECT SUM(jumlah) as stok FROM $jenis WHERE produk = '$produk'");
    $data = mysqli_fetch_assoc($queryStok);
    $stok = $data['stok'];

    if ($stok < $jumlah) {
        echo "<script>alert('Stok tidak cukup'); window.history.back();</script>";
        return false;
    }

    mysqli_query($conn, "INSERT INTO kirim_luar 
        (produk, jumlah, harga, jenis_produk, pemesan) 
        VALUES ('$produk', $jumlah, $harga, '$jenis', '$pemesan')");

    $minus = -1 * $jumlah;
    mysqli_query($conn, "INSERT INTO $jenis 
        (produk, jumlah, waktu) 
        VALUES ('$produk', '$minus', NOW())");

    return true;
}




if ($_SERVER["REQUEST_METHOD"] === "POST") {
    list($produk, $jenis_produk) = explode('|', $_POST['produk']);
    $jumlah = (int) $_POST['jumlah'];
    $harga = (int) $_POST['harga'];
    $pemesan = htmlspecialchars($_POST['pemesan']);

    if (jualBarang($produk, $jumlah, $jenis_produk, $harga, $pemesan)) {
        echo "<script>alert('Barang berhasil dikirim!'); window.location.href='kirim_luar.php';</script>";
    }
}


?>
