<?php
date_default_timezone_set('Asia/Jakarta');


$conn = mysqli_connect("localhost", "root", "", "ae_database");

function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

$ALLOWED_GUDANG = ['gudang', 'gudang1', 'gudang2'];


function stokGudang($produk, $gudang) {
    global $conn, $ALLOWED_GUDANG;
    if (!in_array($gudang, $ALLOWED_GUDANG, true)) return 0;
    $produk = mysqli_real_escape_string($conn, $produk);
    $sql = "SELECT SUM(jumlah) AS stok FROM $gudang WHERE produk = '$produk'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return (int)($row['stok'] ?? 0);
}


function transferKeToko($produk, $gudang_asal, $qty, $harga_jual) {
    global $conn, $ALLOWED_GUDANG;

    $qty = (int)$qty;
    $harga_jual = (int)$harga_jual;
    $produk = mysqli_real_escape_string($conn, $produk);
    $gudang_asal = mysqli_real_escape_string($conn, $gudang_asal);

    if ($qty <= 0) { alertBack('Jumlah harus > 0'); return false; }
    if (!in_array($gudang_asal, $ALLOWED_GUDANG, true)) { alertBack('Gudang tidak valid'); return false; }

    $stok = stokGudang($produk, $gudang_asal);
    if ($stok < $qty) { alertBack('Stok gudang tidak cukup'); return false; }

    mysqli_begin_transaction($conn);
    try {
        // Insert ke stok toko
        mysqli_query($conn, "
            INSERT INTO toko_stock (produk, jumlah, harga_jual, gudang_asal, waktu)
            VALUES ('$produk', $qty, $harga_jual, '$gudang_asal', NOW())
        ");

        // Insert ke tabel log
        mysqli_query($conn, "
            INSERT INTO toko_kirim (produk, jumlah, harga_jual, gudang_asal, waktu)
            VALUES ('$produk', $qty, $harga_jual, '$gudang_asal', NOW())
        ");

        // Update harga di toko_price
        mysqli_query($conn, "
            INSERT INTO toko_price (produk, harga_jual) VALUES ('$produk', $harga_jual)
            ON DUPLICATE KEY UPDATE harga_jual = VALUES(harga_jual)
        ");

        // Kurangi stok dari gudang asal
        $minus = -$qty;
        mysqli_query($conn, "
            INSERT INTO $gudang_asal (produk, jumlah, waktu)
            VALUES ('$produk', $minus, NOW())
        ");

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("transferKeToko error: ".$e->getMessage());
        return false;
    }
}



function getStokTokoAgg() {
    global $conn;
    $sql = "
        SELECT ts.produk,
               SUM(ts.jumlah) AS stok,
               tp.harga_jual AS harga
        FROM toko_stock ts
        LEFT JOIN toko_price tp ON tp.produk = ts.produk
        GROUP BY ts.produk, tp.harga_jual
        ORDER BY ts.produk
    ";
    return mysqli_query($conn, $sql);
}


function getHargaToko($produk) {
    global $conn;
    $produk = mysqli_real_escape_string($conn, $produk);
    $h = mysqli_query($conn, "SELECT harga_jual FROM toko_price WHERE produk='$produk'");
    if ($r = mysqli_fetch_assoc($h)) return (int)$r['harga_jual'];
    $h2 = mysqli_query($conn, "SELECT harga_jual FROM toko_stock WHERE produk='$produk' AND harga_jual IS NOT NULL ORDER BY waktu DESC LIMIT 1");
    if ($r2 = mysqli_fetch_assoc($h2)) return (int)$r2['harga_jual'];
    return 0;
}


function stokToko($produk) {
    global $conn;
    $produk = mysqli_real_escape_string($conn, $produk);
    $sql = "SELECT SUM(jumlah) AS stok FROM toko_stock WHERE produk='$produk'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return (int)($row['stok'] ?? 0);
}


function checkoutToko($pembeli, $items) {
    global $conn;
    $pembeli = mysqli_real_escape_string($conn, $pembeli);

    // Gabungkan produk sama
    $agg = [];
    foreach ($items as $it) {
        $p = trim($it['produk']);
        $q = (int)$it['qty'];
        $h = (int)$it['harga'];
        if ($q <= 0) continue;

        if (!isset($agg[$p])) {
            $agg[$p] = ['produk'=>$p,'qty'=>$q,'harga'=>$h];
        } else {
            $agg[$p]['qty'] += $q;
        }
    }

    if (empty($agg)) { alertBack('Tidak ada item valid'); return false; }

    // Cek stok cukup
    foreach ($agg as $p => $row) {
        if (stokToko($p) < $row['qty']) {
            alertBack("Stok toko kurang untuk $p");
            return false;
        }
    }

    $grand = 0;
    foreach ($agg as $p => &$row) {
        $row['subtotal'] = $row['qty'] * $row['harga'];
        $grand += $row['subtotal'];
    }
    unset($row);

    mysqli_begin_transaction($conn);
    try {
        // Simpan order
        mysqli_query($conn, "
            INSERT INTO toko_order (pembeli, total, waktu)
            VALUES ('$pembeli', $grand, NOW())
        ");
        $order_id = mysqli_insert_id($conn);

        foreach ($agg as $p => $row) {
            $produk = mysqli_real_escape_string($conn, $row['produk']);
            $qty    = (int)$row['qty'];
            $harga  = (int)$row['harga'];
            $sub    = (int)$row['subtotal'];

            // Simpan detail order
            mysqli_query($conn, "
                INSERT INTO toko_order_items (order_id, produk, qty, harga, subtotal)
                VALUES ($order_id, '$produk', $qty, $harga, $sub)
            ");

            // Update stok toko
            mysqli_query($conn, "
                UPDATE toko_stock
                SET jumlah = jumlah - $qty
                WHERE produk = '$produk'
                LIMIT 1
            ");

            // Catat log di log_transfer_toko (jumlah minus)
            $qty_minus = -$qty;
            mysqli_query($conn, "
                INSERT INTO toko_kirim (produk, jumlah, harga_jual, gudang_asal, waktu)
                VALUES ('$produk', $qty_minus, $harga, 'TOKO', NOW())
            ");
        }

        mysqli_commit($conn);
        return $order_id;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("checkoutToko error: ".$e->getMessage());
        return false;
    }
}


function getOrder($id) {
    global $conn;
    $id = (int)$id;
    $h = mysqli_query($conn, "SELECT * FROM toko_order WHERE id=$id");
    $header = mysqli_fetch_assoc($h);

    $d = mysqli_query($conn, "
        SELECT produk, SUM(qty) AS qty, MAX(harga) AS harga, SUM(subtotal) AS subtotal
        FROM toko_order_items
        WHERE order_id=$id
        GROUP BY produk
    ");
    $items = [];
    while ($r = mysqli_fetch_assoc($d)) $items[] = $r;

    return [$header, $items];
}


function alertBack($msg){
    echo "<script>alert('".addslashes($msg)."');history.back();</script>";
}


function tambahStokToko($produk, $qty) {
    global $conn, $ALLOWED_GUDANG;

    $qty = (int)$qty;
    $produk = mysqli_real_escape_string($conn, $produk);

    if ($qty <= 0) {
        alertBack('Jumlah harus > 0');
        return false;
    }

    $harga_jual = getHargaToko($produk); // Ambil harga lama
    if ($harga_jual <= 0) {
        alertBack('Harga produk tidak ditemukan di toko');
        return false;
    }

    
    $gudang_asal = null;
    foreach ($ALLOWED_GUDANG as $gudang) {
        if (stokGudang($produk, $gudang) >= $qty) {
            $gudang_asal = $gudang;
            break;
        }
    }

    if (!$gudang_asal) {
        alertBack("Stok produk '$produk' tidak cukup di semua gudang");
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
       
        mysqli_query($conn, "
            INSERT INTO toko_stock (produk, jumlah, harga_jual, gudang_asal, waktu)
            VALUES ('$produk', $qty, $harga_jual, '$gudang_asal', NOW())
        ");

        mysqli_query($conn, "
            INSERT INTO toko_kirim (produk, jumlah, harga_jual, gudang_asal, waktu)
            VALUES ('$produk', $qty, $harga_jual, '$gudang_asal', NOW())
        ");

        $minus = -$qty;
        mysqli_query($conn, "
            INSERT INTO $gudang_asal (produk, jumlah, waktu)
            VALUES ('$produk', $minus, NOW())
        ");

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("tambahStokToko error: ".$e->getMessage());
        alertBack("Terjadi kesalahan saat menambahkan stok");
        return false;
    }
}

function getSemuaStokGudang() {
    global $ALLOWED_GUDANG;
    $produk_list = query("SELECT DISTINCT produk FROM toko_stock ORDER BY produk");
    $result = [];

    foreach ($produk_list as $p) {
        $produk = $p['produk'];
        $stok_tertinggi = -1;  
        $gudang_tertinggi = '';

        foreach ($ALLOWED_GUDANG as $gudang) {
            $stok = stokGudang($produk, $gudang);
            if ($stok > $stok_tertinggi) {
                $stok_tertinggi = $stok;
                $gudang_tertinggi = $gudang;
            }

            if ($stok_tertinggi === -1) {
                $stok_tertinggi = 0;
                $gudang_tertinggi = $ALLOWED_GUDANG[0]; 
            }
        }

        $result[] = [
            'produk' => $produk,
            'gudang' => $gudang_tertinggi,
            'stok'   => $stok_tertinggi
        ];
    }

    return $result;
}



function getNamaProdukToko() {
    global $conn;
    $result = mysqli_query($conn, "SELECT DISTINCT produk FROM toko_stock ORDER BY produk ASC");
    $produkList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $produkList[] = $row['produk'];
    }
    return $produkList;
}

function ubahStokToko($id, $produk_baru, $jumlah_baru) {
    global $conn, $ALLOWED_GUDANG;

    $id = (int)$id;
    $jumlah_baru = (int)$jumlah_baru;
    $produk_baru = mysqli_real_escape_string($conn, $produk_baru);

    if ($jumlah_baru == 0) {
        alertBack('Jumlah tidak boleh 0');
        return false;
    }

    // Ambil data lama
    $res = mysqli_query($conn, "SELECT * FROM toko_kirim WHERE id = $id LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        alertBack('Data tidak ditemukan');
        return false;
    }

    $produk_lama  = $row['produk'];
    $jumlah_lama  = (int)$row['jumlah'];    // bisa positif (restock) atau negatif (sale)
    $gudang_asal  = $row['gudang_asal'];
    $harga_jual   = isset($row['harga_jual']) ? (int)$row['harga_jual'] : 0;

    // Positif bagian (hanya bagian positif mempengaruhi gudang)
    $pos_old = max(0, $jumlah_lama);
    $pos_new = max(0, $jumlah_baru);

    // Delta yang harus diterapkan ke toko_stock (langsung = new - old)
    $delta_toko = $jumlah_baru - $jumlah_lama;

    // Validasi nama tabel gudang (hindari SQL injection pada nama tabel)
    $is_real_gudang = !empty($ALLOWED_GUDANG) && in_array($gudang_asal, $ALLOWED_GUDANG, true);

    // Jika perlu mengambil tambahan dari gudang (produk sama), cek stok gudang cukup
    if ($produk_baru === $produk_lama) {
        if ($is_real_gudang && $pos_new > $pos_old) {
            $need = $pos_new - $pos_old; // tambahan yang harus diambil dari gudang
            if (stokGudang($produk_baru, $gudang_asal) < $need) {
                alertBack("Stok di gudang $gudang_asal tidak cukup untuk menambah $need item.");
                return false;
            }
        }
    } else {
        // Produk berubah -> jika produk baru butuh pos_new dari gudang, cek stok gudang cukup
        if ($is_real_gudang && $pos_new > 0) {
            if (stokGudang($produk_baru, $gudang_asal) < $pos_new) {
                alertBack("Stok di gudang $gudang_asal tidak cukup untuk memindahkan $pos_new item ke produk baru.");
                return false;
            }
        }
    }

    mysqli_begin_transaction($conn);
    try {
        // 1) Update log_transfer_toko
        $sql = "UPDATE toko_kirim SET produk = '$produk_baru', jumlah = $jumlah_baru WHERE id = $id";
        mysqli_query($conn, $sql);

        // 2) Update toko_stock
        if ($produk_baru === $produk_lama) {
            // produk sama -> apply delta langsung
            $q = mysqli_query($conn, "SELECT 1 FROM toko_stock WHERE produk = '$produk_baru' LIMIT 1");
            if ($q && mysqli_num_rows($q) > 0) {
                mysqli_query($conn, "UPDATE toko_stock SET jumlah = jumlah + ($delta_toko) WHERE produk = '$produk_baru' LIMIT 1");
            } else {
                // bila belum ada baris, buat baris baru dengan jumlah = jumlah_baru
                mysqli_query($conn, "
                    INSERT INTO toko_stock (produk, jumlah, harga_jual, gudang_asal, waktu)
                    VALUES ('$produk_baru', $jumlah_baru, $harga_jual, '$gudang_asal', NOW())
                ");
            }
        } else {
            // produk berubah -> undo efek lama, apply efek baru
            // undo untuk produk lama
            $q1 = mysqli_query($conn, "SELECT 1 FROM toko_stock WHERE produk = '$produk_lama' LIMIT 1");
            if ($q1 && mysqli_num_rows($q1) > 0) {
                mysqli_query($conn, "UPDATE toko_stock SET jumlah = jumlah - ($jumlah_lama) WHERE produk = '$produk_lama' LIMIT 1");
            } else {
                // jika tidak ada row lama, masukkan negatif (mengindikasikan koreksi)
                mysqli_query($conn, "INSERT INTO toko_stock (produk, jumlah, waktu) VALUES ('$produk_lama', -$jumlah_lama, NOW())");
            }

            // apply untuk produk baru
            $q2 = mysqli_query($conn, "SELECT 1 FROM toko_stock WHERE produk = '$produk_baru' LIMIT 1");
            if ($q2 && mysqli_num_rows($q2) > 0) {
                mysqli_query($conn, "UPDATE toko_stock SET jumlah = jumlah + ($jumlah_baru) WHERE produk = '$produk_baru' LIMIT 1");
            } else {
                mysqli_query($conn, "
                    INSERT INTO toko_stock (produk, jumlah, harga_jual, gudang_asal, waktu)
                    VALUES ('$produk_baru', $jumlah_baru, $harga_jual, '$gudang_asal', NOW())
                ");
            }
        }

        // 3) Update gudang (hanya bagian positif berpengaruh pada gudang)
        if ($is_real_gudang) {
            if ($produk_baru === $produk_lama) {
                // satu gudang, satu produk -> cukup satu insert: delta_gudang = pos_old - pos_new
                $delta_gudang = $pos_old - $pos_new; // >0 => gudang + (kembali), <0 => gudang - (diambil)
                if ($delta_gudang != 0) {
                    // catat perubahan ke tabel gudang
                    mysqli_query($conn, "INSERT INTO `$gudang_asal` (produk, jumlah, waktu) VALUES ('$produk_baru', $delta_gudang, NOW())");
                }
            } else {
                // produk berubah -> kembalikan pos_old ke gudang untuk produk_lama (jika ada)
                if ($pos_old > 0) {
                    mysqli_query($conn, "INSERT INTO `$gudang_asal` (produk, jumlah, waktu) VALUES ('$produk_lama', $pos_old, NOW())");
                }
                // ambil pos_new dari gudang untuk produk_baru (jika ada)
                if ($pos_new > 0) {
                    $take = -$pos_new; // gudang berkurang
                    mysqli_query($conn, "INSERT INTO `$gudang_asal` (produk, jumlah, waktu) VALUES ('$produk_baru', $take, NOW())");
                }
            }
        }

        // 4) Simpan audit UBAH
        $data_lama_text = "Produk: $produk_lama\nJumlah: $jumlah_lama\nHarga Jual: $harga_jual\nGudang: $gudang_asal";
        $data_baru_text = "Produk: $produk_baru\nJumlah: $jumlah_baru\nHarga Jual: $harga_jual\nGudang: $gudang_asal";

        $stmt = mysqli_prepare($conn, "
            INSERT INTO log_kirim_toko (aksi, produk, jumlah, harga_jual, gudang_asal, waktu, data_lama, data_baru)
            VALUES ('UBAH', ?, ?, ?, ?, NOW(), ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "siisss", $produk_baru, $jumlah_baru, $harga_jual, $gudang_asal, $data_lama_text, $data_baru_text);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("ubahStokToko error: ".$e->getMessage());
        alertBack("Gagal mengubah data");
        return false;
    }
}



function hapusStokToko($id) {
    global $conn, $ALLOWED_GUDANG;

    $id = (int)$id;

    $res = mysqli_query($conn, "SELECT * FROM toko_kirim WHERE id = $id");
    if (!$res) {
        alertBack('Query error: ' . mysqli_error($conn));
        return false;
    }
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        alertBack('Data tidak ditemukan');
        return false;
    }

    $produk      = $row['produk'];
    $jumlah      = (int)$row['jumlah'];
    $gudang_asal = $row['gudang_asal'];
    $harga_jual  = isset($row['harga_jual']) ? (int)$row['harga_jual'] : 0;

    if (!in_array($gudang_asal, $ALLOWED_GUDANG, true)) {
        alertBack("Gudang asal tidak valid");
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
        
        $data_lama_text = "Produk: $produk\nJumlah: $jumlah\nHarga: $harga_jual\nGudang: $gudang_asal";
        $data_lama_esc = mysqli_real_escape_string($conn, $data_lama_text);

        
        $aksi = 'HAPUS';
        $waktu_log = date('Y-m-d H:i:s');

        
        $sqlLog = "
            INSERT INTO log_kirim_toko
                (aksi, produk, jumlah, harga_jual, gudang_asal, waktu, data_lama, data_baru)
            VALUES
                ('$aksi', '".mysqli_real_escape_string($conn, $produk)."', $jumlah, $harga_jual, '".mysqli_real_escape_string($conn, $gudang_asal)."', '$waktu_log', '$data_lama_esc', NULL)
        ";
        if (!mysqli_query($conn, $sqlLog)) {
            throw new Exception('Gagal insert log: ' . mysqli_error($conn));
        }

        if (!mysqli_query($conn, "DELETE FROM toko_kirim WHERE id = $id")) {
            throw new Exception('Gagal hapus toko_stock: ' . mysqli_error($conn));
        }
        if (mysqli_affected_rows($conn) === 0) {
            throw new Exception('Baris tidak dihapus (tidak ada perubahan).');
        }

        mysqli_commit($conn);
        return true;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("hapusStokToko error: " . $e->getMessage());
        alertBack("Gagal menghapus data: " . addslashes($e->getMessage()));
        return false;
    }
}

function cariTokoStock($keyword) {
    global $conn;

    $keyword = mysqli_real_escape_string($conn, $keyword);

    $query = "
        SELECT * FROM toko_kirim
        WHERE (
            produk LIKE '%$keyword%' OR
            jumlah LIKE '%$keyword%' OR
            harga_jual LIKE '%$keyword%' OR
            gudang_asal LIKE '%$keyword%' OR
            waktu LIKE '%$keyword%'
        )
        AND jumlah >= 0
        ORDER BY waktu DESC
    ";

    return mysqli_query($conn, $query);
}


function ubahHargaProduk($produk_lama, $produk_baru, $harga_jual_baru) {
    global $conn;

    $produk_lama = mysqli_real_escape_string($conn, $produk_lama);
    $produk_baru = trim($produk_baru) === '' ? $produk_lama : mysqli_real_escape_string($conn, $produk_baru);
    $harga_jual_baru = (int)$harga_jual_baru;

    if ($harga_jual_baru <= 0) {
        alertBack('Harga harus lebih dari 0');
        return false;
    }

    // Ambil data lama
    $oldRes = mysqli_query($conn, "SELECT * FROM toko_price WHERE produk = '$produk_lama' LIMIT 1");
    $old = mysqli_fetch_assoc($oldRes);
    if (!$old) {
        alertBack('Produk tidak ditemukan');
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
        // Update (boleh hanya harga, atau sekaligus rename)
        $sql = "
            UPDATE toko_price
            SET produk = '$produk_baru', harga_jual = $harga_jual_baru
            WHERE produk = '$produk_lama'
        ";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception('Gagal update harga: '.mysqli_error($conn));
        }

        // Ambil data baru (pakai nama baru)
        $newRes = mysqli_query($conn, "SELECT * FROM toko_price WHERE produk = '$produk_baru' LIMIT 1");
        $new = mysqli_fetch_assoc($newRes);
        if (!$new) {
            throw new Exception('Gagal mengambil data baru setelah update');
        }

        // Siapkan format data lama & baru agar mudah dibaca
        $data_lama_str = "Produk: {$old['produk']} | Harga: {$old['harga_jual']}";
        $data_baru_str = "Produk: {$new['produk']} | Harga: {$new['harga_jual']}";

        // Simpan log
        $aksi = 'UBAH';
        $produk_log = mysqli_real_escape_string($conn, $produk_baru);
        $harga_log  = (int)$new['harga_jual'];

        $logSql = "
            INSERT INTO log_toko_price (aksi, produk, harga_jual, data_lama, data_baru)
            VALUES ('$aksi', '$produk_log', $harga_log, '".mysqli_real_escape_string($conn, $data_lama_str)."', '".mysqli_real_escape_string($conn, $data_baru_str)."')
        ";

        if (!mysqli_query($conn, $logSql)) {
            throw new Exception('Gagal insert log: '.mysqli_error($conn));
        }

        mysqli_commit($conn);
        return true;

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log('ubahHargaProduk error: '.$e->getMessage());
        return false;
    }
}





function cariProdukToko($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $sql = "
        SELECT ts.produk,
               SUM(ts.jumlah) AS stok,
               tp.harga_jual AS harga
        FROM toko_stock ts
        LEFT JOIN toko_price tp ON tp.produk = ts.produk
        WHERE ts.produk LIKE '%$keyword%'
        GROUP BY ts.produk, tp.harga_jual
        ORDER BY ts.produk
    ";
    return mysqli_query($conn, $sql);
}

function getRiwayatPenjualan() {
    global $conn;
    $sql = "
        SELECT o.id AS order_id,
               o.pembeli,
               o.total,
               o.waktu,
               i.produk,
               i.qty,
               i.harga,
               i.subtotal
        FROM toko_order o
        JOIN toko_order_items i ON o.id = i.order_id
        ORDER BY o.waktu DESC, o.id DESC
    ";
    return mysqli_query($conn, $sql);
}

function cariRiwayatPenjualan($keyword) {
    global $conn;
    $keyword = mysqli_real_escape_string($conn, $keyword);
    
    $sql = "
        SELECT o.id AS order_id,
               o.pembeli,
               o.total,
               o.waktu,
               i.produk,
               i.qty,
               i.harga,
               i.subtotal
        FROM toko_order o
        JOIN toko_order_items i ON o.id = i.order_id
        WHERE 
            o.waktu LIKE '%$keyword%' OR
            o.pembeli LIKE '%$keyword%' OR
            i.produk LIKE '%$keyword%'
        ORDER BY o.waktu DESC, o.id DESC
    ";
    
    return mysqli_query($conn, $sql);
}

function hapusRiwayatPenjualan($order_id) {
    global $conn;

    $order_id = (int)$order_id;

    // Ambil data order
    $orderRes = mysqli_query($conn, "SELECT * FROM toko_order WHERE id = $order_id LIMIT 1");
    $order = mysqli_fetch_assoc($orderRes);
    if (!$order) {
        return false; // Order tidak ditemukan
    }

    // Ambil detail produk
    $itemsRes = mysqli_query($conn, "SELECT * FROM toko_order_items WHERE order_id = $order_id");
    $detail_produk_arr = [];
    while ($row = mysqli_fetch_assoc($itemsRes)) {
        $detail_produk_arr[] = "{$row['produk']} ({$row['qty']} x {$row['harga']})";
    }
    $detail_produk_str = implode(", ", $detail_produk_arr);

    // Simpan log
    $aksi = 'HAPUS';
    $logSql = "
        INSERT INTO log_hapus_penjualan (aksi, order_id, pembeli, total, waktu_transaksi, detail_produk)
        VALUES (
            '$aksi',
            {$order['id']},
            '".mysqli_real_escape_string($conn, $order['pembeli'])."',
            {$order['total']},
            '{$order['waktu']}',
            '".mysqli_real_escape_string($conn, $detail_produk_str)."'
        )
    ";
    mysqli_query($conn, $logSql);

    // Hapus data
    mysqli_query($conn, "DELETE FROM toko_order_items WHERE order_id = $order_id");
    mysqli_query($conn, "DELETE FROM toko_order WHERE id = $order_id");

    return true;
}



?>
