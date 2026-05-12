<?php
date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect("localhost", "root", "", "ae_database");

function get_pengiriman($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM kirim_luar WHERE id = $id LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($res);
}

function jualBarang($produk, $jumlah, $jenis, $harga, $pemesan) {
    global $conn;

    $produk  = mysqli_real_escape_string($conn, $produk);
    $jenis   = mysqli_real_escape_string($conn, $jenis);
    $pemesan = mysqli_real_escape_string($conn, $pemesan);
    $jumlah  = (int)$jumlah;
    $harga   = (int)$harga;

    $allowed_tables = ['gudang','gudang1','gudang2'];
    if (!in_array($jenis, $allowed_tables, true)) {
        echo "<script>alert('Gudang tidak valid.');history.back();</script>";
        return false;
    }

    $sqlStok = "SELECT SUM(jumlah) AS stok FROM $jenis WHERE produk = '$produk'";
    $resStok = mysqli_query($conn, $sqlStok);
    $data    = mysqli_fetch_assoc($resStok);
    $stok    = (int)($data['stok'] ?? 0);

    if ($stok < $jumlah) {
        echo "<script>alert('Stok tidak cukup');history.back();</script>";
        return false;
    }


    mysqli_begin_transaction($conn);
    try {
        $sqlInsert = "INSERT INTO kirim_luar
            (produk, jumlah, harga, jenis_produk, pemesan, waktu)
            VALUES ('$produk', $jumlah, $harga, '$jenis', '$pemesan', NOW())";
        mysqli_query($conn, $sqlInsert);

        $minus = -$jumlah;
        $sqlMinus = "INSERT INTO $jenis (produk, jumlah, waktu)
                     VALUES ('$produk', $minus, NOW())";
        mysqli_query($conn, $sqlMinus);

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("jualBarang error: ".$e->getMessage());
        return false;
    }
}

function ubah_pengiriman($id, $produk_baru, $jumlah_baru, $jenis_baru, $harga_baru, $pemesan_baru) {
    global $conn;

    $allowed_tables = ['gudang','gudang1','gudang2'];

    $lama = get_pengiriman($id);
    if (!$lama) {
        echo "<script>alert('Data tidak ditemukan');history.back();</script>";
        return false;
    }

    $produk_lama  = $lama['produk'];
    $jumlah_lama  = (int)$lama['jumlah'];
    $jenis_lama   = $lama['jenis_produk'];
    $harga_lama   = (int)$lama['harga']; 
    $pemesan_lama = $lama['pemesan'];

    // sanitasi baru
    $produk_baru = mysqli_real_escape_string($conn, $produk_baru);
    $jenis_baru  = mysqli_real_escape_string($conn, $jenis_baru);
    $pemesan_baru= mysqli_real_escape_string($conn, $pemesan_baru);
    $jumlah_baru = (int)$jumlah_baru;
    $harga_baru  = (int)$harga_baru;

    if (!in_array($jenis_baru, $allowed_tables, true) || !in_array($jenis_lama, $allowed_tables, true)) {
        echo "<script>alert('Gudang tidak valid');history.back();</script>";
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
        // Kembalikan stok lama
        $sqlBalik = "INSERT INTO $jenis_lama (produk, jumlah, waktu)
                     VALUES ('$produk_lama', $jumlah_lama, NOW())";
        mysqli_query($conn, $sqlBalik);

        // Cek stok baru
        $sqlStokBaru = "SELECT SUM(jumlah) AS stok FROM $jenis_baru WHERE produk = '$produk_baru'";
        $resStokBaru = mysqli_query($conn, $sqlStokBaru);
        $dataBaru    = mysqli_fetch_assoc($resStokBaru);
        $stokBaru    = (int)($dataBaru['stok'] ?? 0);

        if ($stokBaru < $jumlah_baru) {
            mysqli_rollback($conn);
            echo "<script>alert('Stok gudang tidak cukup setelah perubahan');history.back();</script>";
            return false;
        }

        // Kurangi stok baru
        $minus = -$jumlah_baru;
        $sqlKurang = "INSERT INTO $jenis_baru (produk, jumlah, waktu)
                      VALUES ('$produk_baru', $minus, NOW())";
        mysqli_query($conn, $sqlKurang);

        // Update data kirim_luar
        $sqlUpdate = "
            UPDATE kirim_luar SET
                pemesan        = '$pemesan_baru',
                produk         = '$produk_baru',
                jumlah         = $jumlah_baru,
                harga          = $harga_baru,
                jenis_produk   = '$jenis_baru',
                waktu          = NOW()
            WHERE id = $id
            LIMIT 1
        ";
        mysqli_query($conn, $sqlUpdate);

        // Log perubahan
        $waktu_log = date('Y-m-d H:i:s');
        $data_lama_text = "Pemesan: $pemesan_lama\nProduk: $produk_lama\nJumlah: $jumlah_lama\nHarga: $harga_lama";
        $data_baru_text = "Pemesan: $pemesan_baru\nProduk: $produk_baru\nJumlah: $jumlah_baru\nHarga: $harga_baru";

        $stmt = mysqli_prepare($conn, "
            INSERT INTO log_kirim_luar (aksi, pemesan, produk, jumlah, harga, waktu, data_lama, data_baru) 
            VALUES ('UBAH', ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ssiisss", $pemesan_baru, $produk_baru, $jumlah_baru, $harga_baru, $waktu_log, $data_lama_text, $data_baru_text);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        return true;

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');history.back();</script>";
        return false;
    }
}



function cari_pengiriman($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, trim($keyword));
    $sql = "
    SELECT id, pemesan, produk, jumlah, harga,
           (jumlah * harga) AS total_penjualan,
           jenis_produk, waktu
    FROM kirim_luar
    WHERE pemesan LIKE '%$keyword%'
       OR produk LIKE '%$keyword%'
       OR jenis_produk LIKE '%$keyword%'
       OR waktu LIKE '%$keyword%'
    ORDER BY id DESC
    ";
    return mysqli_query($conn, $sql);
}

function hapus_pengiriman($id) {
    global $conn;

    $query = mysqli_query($conn, "SELECT * FROM kirim_luar WHERE id = $id");
    $data_lama = mysqli_fetch_assoc($query);

    if (!$data_lama) return 0;

    $pemesan = $data_lama['pemesan'];
    $produk  = $data_lama['produk'];
    $jumlah  = (int)$data_lama['jumlah'];
    $harga   = (int)$data_lama['harga'];
    $waktu   = date('Y-m-d H:i:s');
    $data_lama_text = "Pemesan: $pemesan\nProduk: $produk\nJumlah: $jumlah\nHarga: $harga";

    $stmt = mysqli_prepare($conn, "INSERT INTO log_kirim_luar (aksi, pemesan, produk, jumlah, harga, waktu, data_lama) VALUES ('HAPUS', ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiiss", $pemesan, $produk, $jumlah, $harga, $waktu, $data_lama_text);
    mysqli_stmt_execute($stmt);

    // Hapus data
    mysqli_query($conn, "DELETE FROM kirim_luar WHERE id = $id");
    return mysqli_affected_rows($conn);
}


if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['submit_kirim'])
) {
    if (empty($_POST['produk'])) {
        echo "<script>alert('Produk belum dipilih');history.back();</script>";
        exit;
    }

    $raw = $_POST['produk'];
    $produk = $raw;
    $jenis_produk = null;
    if (strpos($raw, '|') !== false) {
        list($produk, $jenis_produk) = explode('|', $raw, 2);
    }

    $jumlah  = isset($_POST['jumlah'])  ? (int)preg_replace('/\D/','',$_POST['jumlah']) : 0;
    $harga   = isset($_POST['harga'])   ? (int)preg_replace('/\D/','',$_POST['harga'])   : 0;
    $pemesan = isset($_POST['pemesan']) ? $_POST['pemesan'] : '';

    if ($jenis_produk === null) {
        echo "<script>alert('Format produk tidak valid');history.back();</script>";
        exit;
    }

    if (jualBarang($produk, $jumlah, $jenis_produk, $harga, $pemesan)) {
        echo "<script>alert('Barang berhasil dikirim!');window.location.href='kirim_luar.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal kirim barang');history.back();</script>";
        exit;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['submit_update'])
) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        echo "<script>alert('ID tidak valid');history.back();</script>";
        exit;
    }

    if (empty($_POST['produk'])) {
        echo "<script>alert('Produk belum dipilih');history.back();</script>";
        exit;
    }

    $raw = $_POST['produk'];
    $produk_baru = $raw;
    $jenis_baru  = null;
    if (strpos($raw, '|') !== false) {
        list($produk_baru, $jenis_baru) = explode('|', $raw, 2);
    }

    $jumlah_baru  = isset($_POST['jumlah'])  ? (int)preg_replace('/\D/','',$_POST['jumlah']) : 0;
    $harga_baru   = isset($_POST['harga'])   ? (int)preg_replace('/\D/','',$_POST['harga'])   : 0;
    $pemesan_baru = isset($_POST['pemesan']) ? $_POST['pemesan'] : '';

    if ($jenis_baru === null) {
        echo "<script>alert('Format produk tidak valid');history.back();</script>";
        exit;
    }

    if (ubah_pengiriman($id, $produk_baru, $jumlah_baru, $jenis_baru, $harga_baru, $pemesan_baru)) {
        echo "<script>alert('Data berhasil diubah!');window.location.href='kirim_luar.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal ubah data');history.back();</script>";
        exit;
    }
}
?>
