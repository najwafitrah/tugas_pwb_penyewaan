<?php require_once "../config/auth.php"; ?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title ?? 'Sistem Penyewaan') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head><body>
<div class="sidebar">
<div class="brand p-4"><h5 class="fw-bold mb-0">SewaApp</h5><small>Admin Panel</small></div>
<a href="dashboard.php">🏠 Dashboard</a>
<a href="barang.php">📦 Data Barang</a>
<a href="pelanggan.php">👥 Pelanggan</a>
<a href="penyewaan.php">📝 Penyewaan</a>
<a href="pengembalian.php">↩ Pengembalian</a>
<a href="laporan.php">📊 Laporan</a>
<a href="pengaturan.php">⚙ Pengaturan</a>
<a href="../logout.php">🚪 Logout</a>
</div>
<div class="main">
<div class="topbar d-flex justify-content-between align-items-center">
<div><h5 class="mb-0"><?= htmlspecialchars($page_title ?? '') ?></h5></div>
<div>Halo, <b><?= htmlspecialchars($_SESSION['nama']) ?></b></div>
</div>
