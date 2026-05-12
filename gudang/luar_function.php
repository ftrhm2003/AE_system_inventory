<?php 

date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect("localhost", "root", "", "ae_database");


function query_produksi($query_produksi) {
    global $conn;
    $result = mysqli_query($conn, $query_produksi);
    $isis = [];
    while ($isi = mysqli_fetch_assoc($result)) {
        $isis[] = $isi;
    }
    return $isis;
}

function tambah_produksi($data_produksi) {
    global $conn;

    $produk = htmlspecialchars($data_produksi["produk"]);
    $jumlah = htmlspecialchars($data_produksi["jumlah"]);
    $waktu = htmlspecialchars($data_produksi["waktu"]);

    $query_produksi = "INSERT INTO gudang2 values ('', '$produk', '$jumlah', '$waktu')";

    mysqli_query($conn, $query_produksi);

    return mysqli_affected_rows($conn);
}

function hapus_produksi($id) {
    global $conn;

    // Ambil data lama sebelum dihapus
    $res = mysqli_query($conn, "SELECT * FROM gudang2 WHERE id = $id LIMIT 1");
    $data_lama = mysqli_fetch_assoc($res);

    if ($data_lama) {
        $produk = $data_lama['produk'];
        $jumlah = $data_lama['jumlah'];
        $waktu  = date('Y-m-d H:i:s');

        // Simpan log ke log_gudang
        $sql_log = "INSERT INTO log_gudang2 (aksi, produk, jumlah, waktu, data_lama, data_baru)
                    VALUES ('HAPUS', '$produk', '$jumlah', '$waktu',
                            'Produk: {$data_lama['produk']} | Jumlah: {$data_lama['jumlah']} | Waktu: {$data_lama['waktu']}',
                            '-')";
        mysqli_query($conn, $sql_log);
    }

    // Hapus data dari gudang
    mysqli_query($conn, "DELETE FROM gudang2 WHERE id = $id");

    return mysqli_affected_rows($conn);
}



function ubah_produksi($data_produksi) {
    global $conn;

    $id     = (int)$data_produksi["id"];
    $produk = htmlspecialchars($data_produksi["produk"]);
    $jumlah = htmlspecialchars($data_produksi["jumlah"]);
    $waktu  = htmlspecialchars($data_produksi["waktu"]);

    // 1️⃣ Ambil data lama
    $res_lama = mysqli_query($conn, "SELECT * FROM gudang2 WHERE id = $id LIMIT 1");
    $row_lama = mysqli_fetch_assoc($res_lama);
    $data_lama_text = "Produk: {$row_lama['produk']} | Jumlah: {$row_lama['jumlah']} | Waktu: {$row_lama['waktu']}";

    // 2️⃣ Update data
    $query_produksi = "UPDATE gudang2 SET
        produk = '$produk',
        jumlah = '$jumlah',
        waktu  = '$waktu'
        WHERE id = $id
    ";
    mysqli_query($conn, $query_produksi);

    // 3️⃣ Ambil data baru
    $res_baru = mysqli_query($conn, "SELECT * FROM gudang2 WHERE id = $id LIMIT 1");
    $row_baru = mysqli_fetch_assoc($res_baru);
    $data_baru_text = "Produk: {$row_baru['produk']} | Jumlah: {$row_baru['jumlah']} | Waktu: {$row_baru['waktu']}";

    // 4️⃣ Catat log UBAH
    $sql_log = "INSERT INTO log_gudang2 (aksi, produk, jumlah, waktu, data_lama, data_baru)
                VALUES ('UBAH', '{$row_baru['produk']}', '{$row_baru['jumlah']}', '{$row_baru['waktu']}', 
                        '$data_lama_text', '$data_baru_text')";
    mysqli_query($conn, $sql_log);

    return mysqli_affected_rows($conn);
}


function cari_produksi($src_produksi){
    $query_produksi = "SELECT * FROM gudang2 
        WHERE produk LIKE '%$src_produksi%' 
           OR jumlah LIKE '%$src_produksi%'  
           OR waktu LIKE '%$src_produksi%'
        ORDER BY waktu DESC
    ";
    return query_produksi($query_produksi);
}


function total_per_produk() {
    global $conn;

    $result = mysqli_query($conn, "
        SELECT 
            produk, 
            SUM(CAST(jumlah AS SIGNED)) AS total_jumlah
        FROM gudang2
        GROUP BY produk
    ");

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $produk = $row['produk'];
        $total_jumlah = (int)$row['total_jumlah'];

        $query_keluar = mysqli_query($conn, "
            SELECT SUM(ABS(jumlah)) AS jumlah_keluar 
            FROM gudang2 
            WHERE produk = '$produk' AND jumlah < 0
        ");
        $keluar = mysqli_fetch_assoc($query_keluar);
        $jumlah_keluar = (int)$keluar['jumlah_keluar'];

        $data[] = [
            'produk' => $produk,
            'total_jumlah' => $total_jumlah,
        ];
    }

    return $data;
}


function ambil_barang($data) {
    global $conn;

    $produk = htmlspecialchars($data["produk"]);
    $jumlah = -abs((int)$data["jumlah"]); // ubah jadi negatif
    $waktu = htmlspecialchars($data["waktu"]);

    
    $hargaRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga FROM produksi WHERE produk = '$produk' ORDER BY waktu DESC LIMIT 1"));
    $harga = $hargaRow ? $hargaRow['harga'] : '0';

    
    $query = "INSERT INTO produksi (produk, jumlah, harga, waktu) 
              VALUES ('$produk', '$jumlah', '$harga', '$waktu')";
    
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function tambah_jumlah($data) {
    global $conn;

    $produk = htmlspecialchars($data["produk"]);
    $jumlah = abs((int)$data["jumlah"]); 
    $waktu = htmlspecialchars($data["waktu"]);


    $query = "INSERT INTO gudang2 (produk, jumlah, waktu) 
              VALUES ('$produk', '$jumlah', '$waktu')";
    
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}


function cari_total_per_produk($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);

    $result = mysqli_query($conn, "
        SELECT produk, 
               SUM(CAST(jumlah AS SIGNED)) AS total_jumlah
        FROM gudang2
        WHERE produk LIKE '%$keyword%'
        GROUP BY produk
    ");

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $produk = $row['produk'];
        $total_jumlah = (int)$row['total_jumlah'];

        $query_keluar = mysqli_query($conn, "
            SELECT SUM(ABS(jumlah)) AS jumlah_keluar 
            FROM gudang2     
            WHERE produk = '$produk' AND jumlah < 0
        ");
        $keluar = mysqli_fetch_assoc($query_keluar);
        $jumlah_keluar = (int)$keluar['jumlah_keluar'];


        $data[] = [
            'produk' => $produk,
            'total_jumlah' => $total_jumlah,
        ];
    }

    return $data;
}





?>
