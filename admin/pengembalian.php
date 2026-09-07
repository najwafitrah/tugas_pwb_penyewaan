<?php
require_once "../config/koneksi.php";$page_title="Pengembalian";
if(isset($_POST['kembali'])){
$id=(int)$_POST['id_sewa'];$tgl=$_POST['tanggal_pengembalian'];$s=$conn->query("SELECT * FROM penyewaan WHERE id_sewa=$id AND status='Disewa'")->fetch_assoc();
if($s){
$d=new DateTime($s['tanggal_rencana_kembali']);$k=new DateTime($tgl);$telat=max(0,(int)$d->diff($k)->days);if($k<$d)$telat=0;
$denda=$telat*25000;
$conn->begin_transaction();
try{
$st=$conn->prepare("INSERT INTO pengembalian(id_sewa,tanggal_pengembalian,keterlambatan,denda) VALUES(?,?,?,?)");$st->bind_param("isid",$id,$tgl,$telat,$denda);$st->execute();
$conn->query("UPDATE penyewaan SET status='Selesai' WHERE id_sewa=$id");
$conn->query("UPDATE barang SET stok=stok+1,status='Tersedia' WHERE id_barang=".$s['id_barang']);
$conn->commit();header("Location: pengembalian.php");exit;
}catch(Exception $e){$conn->rollback();$err="Pengembalian gagal.";}}
}
require "header.php";?>
<?php if(!empty($err)):?><div class="alert alert-danger"><?= $err ?></div><?php endif;?>
<div class="card p-4 mb-4"><h5>Barang yang Masih Disewa</h5><div class="table-responsive"><table class="table"><thead><tr><th>Kode</th><th>Pelanggan</th><th>Barang</th><th>Rencana Kembali</th><th>Aksi</th></tr></thead><tbody>
<?php $q=$conn->query("SELECT s.*,p.nama pelanggan,b.nama_barang FROM penyewaan s JOIN pelanggan p ON p.id_pelanggan=s.id_pelanggan JOIN barang b ON b.id_barang=s.id_barang WHERE s.status='Disewa' ORDER BY s.tanggal_rencana_kembali");while($r=$q->fetch_assoc()):?><tr><td><?= $r['kode_sewa'] ?></td><td><?= htmlspecialchars($r['pelanggan']) ?></td><td><?= htmlspecialchars($r['nama_barang']) ?></td><td><?= $r['tanggal_rencana_kembali'] ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="id_sewa" value="<?= $r['id_sewa'] ?>"><input class="form-control" type="date" name="tanggal_pengembalian" value="<?= date('Y-m-d') ?>" required><button class="btn btn-success" name="kembali" onclick="return confirm('Proses pengembalian?')">Kembalikan</button></form></td></tr><?php endwhile;?></tbody></table></div></div>
<div class="card p-4"><h5>Riwayat Pengembalian</h5><div class="table-responsive"><table class="table"><thead><tr><th>Kode</th><th>Tanggal</th><th>Keterlambatan</th><th>Denda</th></tr></thead><tbody>
<?php $q=$conn->query("SELECT p.*,s.kode_sewa FROM pengembalian p JOIN penyewaan s ON s.id_sewa=p.id_sewa ORDER BY p.id_pengembalian DESC");while($r=$q->fetch_assoc()):?><tr><td><?= $r['kode_sewa'] ?></td><td><?= $r['tanggal_pengembalian'] ?></td><td><?= $r['keterlambatan'] ?> hari</td><td>Rp <?= number_format($r['denda'],0,',','.') ?></td></tr><?php endwhile;?></tbody></table></div></div>
<?php require "footer.php";?>