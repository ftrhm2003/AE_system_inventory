<?php 

require 'kirim_luar_function.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID tidak valid.");
}

$data = get_pengiriman($id);
if (!$data) {
    die("Data tidak ditemukan.");
}

if (hapus_pengiriman($id) > 0) {
		echo "
			<script>
				alert('data berhasil dihapus');
				document.location.href = 'kirim_luar.php';
			</script>
			 ";
	} else {
		echo "
			<script>
				alert('data gagal dihapus');
				document.location.href = 'kirim_luar.php';
			</script>
			 ";
}


?>