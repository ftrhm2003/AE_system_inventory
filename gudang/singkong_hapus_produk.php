<?php 

require 'singkong_function.php';

$id = $_GET["id"];

if (hapus_produksi($id) > 0) {
		echo "
			<script>
				alert('data berhasil dihapus');
				document.location.href = 'singkong_produksi.php';
			</script>
			 ";
	} else {
		echo "
			<script>
				alert('data gagal dihapus');
				document.location.href = 'singkong_produksi.php';
			</script>
			 ";
}


?>