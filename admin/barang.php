<?php
require_once "../config/koneksi.php";
$page_title="Data Barang";
$aksi=$_GET['aksi']??'';
if(isset($_POST['simpan'])){
    $id=(int)($_POST['id_barang']??0); $nama=trim($_POST['nama_barang']); $kat=trim($_POST['kategori']);
    $harga=(float)$_POST['harga_sewa']; $stok=(int)$_POST['stok'];
    if($id){$st=$conn->prepare("UPDATE barang SET nama_barang=?,kategori=?,harga_sewa=?,stok=? WHERE id_barang=?");$st->bind_param("ssdii",$nama,$kat,$harga,$stok,$id);}
    else{$st=$conn->prepare("INSERT INTO barang(nama_barang,kategori,harga_sewa,stok) VALUES(?,?,?,?)");$st->bind_param("ssdi",$nama,$kat,$harga,$stok);}
    $st->execute(); header("Location: barang.php");exit;
}
if($aksi==='hapus'){ $id=(int)$_GET['id']; $conn->query("DELETE FROM barang WHERE id_barang=$id"); header("Location: barang.php");exit; }
$edit=null;if($aksi==='edit'){$id=(int)$_GET['id'];$edit=$conn->query("SELECT * FROM barang WHERE id_barang=$id")->fetch_assoc();}
require "header.php";
?>
<div class="card p-4 mb-4">
<h5><?= $edit?'Edit':'Tambah' ?> Barang</h5>
<form method="post" class="row g-3">
<input type="hidden" name="id_barang" value="<?= $edit['id_barang']??0 ?>">
<div class="col-md-6"><label>Nama Barang</label><input class="form-control" name="nama_barang" required value="<?= htmlspecialchars($edit['nama_barang']??'') ?>"></div>
<div class="col-md-6"><label>Kategori</label><input class="form-control" name="kategori" required value="<?= htmlspecialchars($edit['kategori']??'') ?>"></div>
<div class="col-md-6"><label>Harga Sewa / Hari</label><input class="form-control" type="number" name="harga_sewa" min="0" required value="<?= $edit['harga_sewa']??'' ?>"></div>
<div class="col-md-6"><label>Stok</label><input class="form-control" type="number" name="stok" min="1" required value="<?= $edit['stok']??1 ?>"></div>
<div><button class="btn btn-primary" name="simpan">Simpan</button> <?php if($edit): ?><a class="btn btn-secondary" href="barang.php">Batal</a><?php endif; ?></div>
</form></div>
<div class="card p-4"><h5>Daftar Barang</h5><div class="table-responsive"><table class="table">
<thead><tr><th>No</th><th>Barang</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php $no=1;$q=$conn->query("SELECT * FROM barang ORDER BY id_barang DESC");while($r=$q->fetch_assoc()): ?>
<tr><td><?= $no++ ?></td><td><?= htmlspecialchars($r['nama_barang']) ?></td><td><?= htmlspecialchars($r['kategori']) ?></td><td>Rp <?= number_format($r['harga_sewa'],0,',','.') ?></td><td><?= $r['stok'] ?></td><td><span class="badge <?= $r['status']=='Tersedia'?'bg-success':'bg-warning text-dark' ?>"><?= $r['status'] ?></span></td><td><a class="btn btn-sm btn-warning" href="?aksi=edit&id=<?= $r['id_barang'] ?>">Edit</a> <a class="btn btn-sm btn-danger" onclick="return confirm('Hapus barang ini?')" href="?aksi=hapus&id=<?= $r['id_barang'] ?>">Hapus</a></td></tr>
<?php endwhile; ?></tbody></table></div></div>
<?php require "footer.php"; ?>