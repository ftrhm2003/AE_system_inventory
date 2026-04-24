<?php 

// show data
$conn = mysqli_connect("localhost", "root", "", "ae_database");


// fungction untuk data produksi

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
    mysqli_query($conn, "DELETE FROM gudang2 WHERE id = $id");

    return mysqli_affected_rows($conn);
}


function ubah_produksi($data_produksi) {
    global $conn;

    $id = $data_produksi["id"];
    $produk = htmlspecialchars($data_produksi["produk"]);
    $jumlah= htmlspecialchars($data_produksi["jumlah"]);
    $waktu = htmlspecialchars($data_produksi["waktu"]);


    $query_produksi = "UPDATE gudang2 Set
             produk = '$produk',
             jumlah = '$jumlah',
             waktu = '$waktu'
             WHERE id = $id
             ";
    mysqli_query($conn, $query_produksi);

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
    $jumlah = abs((int)$data["jumlah"]); // ubah jadi positif
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
