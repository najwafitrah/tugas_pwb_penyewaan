<?php
require_once "../config/koneksi.php";
$page_title="Dashboard";
require "header.php";
$barang=$conn->query("SELECT COUNT(*) c FROM barang")->fetch_assoc()['c'];
$pelanggan=$conn->query("SELECT COUNT(*) c FROM pelanggan")->fetch_assoc()['c'];
$sewa=$conn->query("SELECT COUNT(*) c FROM penyewaan")->fetch_assoc()['c'];
$aktif=$conn->query("SELECT COUNT(*) c FROM penyewaan WHERE status='Disewa'")->fetch_assoc()['c'];
$pendapatan=$conn->query("SELECT COALESCE(SUM(total),0) c FROM penyewaan")->fetch_assoc()['c'];
?>
<div class="row g-3">
<?php foreach([
['Total Barang',$barang,'📦'],['Total Pelanggan',$pelanggan,'👥'],['Total Penyewaan',$sewa,'📝'],['Sedang Disewa',$aktif,'⏳'],['Total Pendapatan','Rp '.number_format($pendapatan,0,',','.'),'💰']
] as $s): ?>
<div class="col-md-4 col-lg">
<div class="card stat"><div class="text-muted"><?= $s[2] ?> <?= $s[0] ?></div><h3 class="fw-bold mt-2"><?= $s[1] ?></h3></div>
</div>
<?php endforeach; ?>
</div>
<div class="card p-4 mt-4">
<h5>Transaksi Terbaru</h5>
<div class="table-responsive"><table class="table">
<thead><tr><th>Kode</th><th>Pelanggan</th><th>Barang</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php $q=$conn->query("SELECT s.*,p.nama pelanggan,b.nama_barang FROM penyewaan s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang ORDER BY s.id_sewa DESC LIMIT 8");
while($r=$q->fetch_assoc()): ?>
<tr><td><?= htmlspecialchars($r['kode_sewa']) ?></td><td><?= htmlspecialchars($r['pelanggan']) ?></td><td><?= htmlspecialchars($r['nama_barang']) ?></td><td>Rp <?= number_format($r['total'],0,',','.') ?></td><td><span class="badge <?= $r['status']=='Disewa'?'bg-warning text-dark':'bg-success' ?>"><?= $r['status'] ?></span></td></tr>
<?php endwhile; ?></tbody></table></div>
</div>
<?php require "footer.php"; ?>