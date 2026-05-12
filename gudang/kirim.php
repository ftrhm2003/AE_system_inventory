<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AE Kirim Barang</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      margin: 0;
      padding: 0;
      background: #ffffff; /* putih */
      color: #333;
    }

    .wrapper {
      max-width: 900px;
      margin: 40px auto;
      text-align: center;
    }

    h1.judul {
      font-size: 2rem;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    /* Tombol kembali */
    .back-btn {
      display: inline-block;
      margin-bottom: 30px;
      padding: 10px 20px;
      background: #3498db;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.95rem;
      transition: background 0.2s;
    }
    .back-btn:hover {
      background: #2980b9;
    }

    /* Menu grid */
    .menu-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .menu-card {
      background: #fdfdfd;
      padding: 40px 20px;
      border-radius: 12px;
      border: 1px solid #ddd;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: transform 0.2s, box-shadow 0.2s;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .menu-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .menu-card h2 {
      font-size: 1.2rem;
      margin-top: 15px;
      color: #34495e;
    }

    /* Ikon lingkaran */
    .icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.5rem;
      margin-bottom: 10px;
    }
    .kirim-luar { background: #8e44ad; }
    .kirim-toko { background: #27ae60; }
  </style>
</head>
<body>

<div class="wrapper">
  <h1 class="judul">AE Kirim Barang</h1>

  <a href="index.php" class="back-btn">⬅ Kembali</a>

  <div class="menu-container">
    <a href="kirim_luar.php" class="menu-card">
      <div class="icon kirim-luar">🚚</div>
      <h2>Kirim ke Luar</h2>
    </a>

    <a href="toko_history.php" class="menu-card">
      <div class="icon kirim-toko">🏪</div>
      <h2>Kirim ke Toko</h2>
    </a>
  </div>
</div>

</body>
</html>
