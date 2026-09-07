<?php
require_once "../config/koneksi.php";$page_title="Data Pelanggan";$aksi=$_GET['aksi']??'';
if(isset($_POST['simpan'])){
$id=(int)($_POST['id_pelanggan']??0);$nama=trim($_POST['nama']);$hp=trim($_POST['no_hp']);$alamat=trim($_POST['alamat']);$email=trim($_POST['email']);
if($id){$st=$conn->prepare("UPDATE pelanggan SET nama=?,no_hp=?,alamat=?,email=? WHERE id_pelanggan=?");$st->bind_param("ssssi",$nama,$hp,$alamat,$email,$id);}
else{$st=$conn->prepare("INSERT INTO pelanggan(nama,no_hp,alamat,email) VALUES(?,?,?,?)");$st->bind_param("ssss",$nama,$hp,$alamat,$email);}
$st->execute();header("Location: pelanggan.php");exit;}
if($aksi==='hapus'){$id=(int)$_GET['id'];$conn->query("DELETE FROM pelanggan WHERE id_pelanggan=$id");header("Location: pelanggan.php");exit;}
$edit=null;if($aksi==='edit'){$id=(int)$_GET['id'];$edit=$conn->query("SELECT * FROM pelanggan WHERE id_pelanggan=$id")->fetch_assoc();}
require "header.php";?>
<div class="card p-4 mb-4"><h5><?= $edit?'Edit':'Tambah' ?> Pelanggan</h5><form method="post" class="row g-3">
<input type="hidden" name="id_pelanggan" value="<?= $edit['id_pelanggan']??0 ?>">
<div class="col-md-6"><label>Nama</label><input class="form-control" name="nama" required value="<?= htmlspecialchars($edit['nama']??'') ?>"></div>
<div class="col-md-6"><label>No. HP</label><input class="form-control" name="no_hp" required value="<?= htmlspecialchars($edit['no_hp']??'') ?>"></div>
<div class="col-md-6"><label>Email</label><input class="form-control" type="email" name="email" value="<?= htmlspecialchars($edit['email']??'') ?>"></div>
<div class="col-md-6"><label>Alamat</label><textarea class="form-control" name="alamat" required><?= htmlspecialchars($edit['alamat']??'') ?></textarea></div>
<div><button class="btn btn-primary" name="simpan">Simpan</button> <?php if($edit): ?><a class="btn btn-secondary" href="pelanggan.php">Batal</a><?php endif;?></div>
</form></div>
<div class="card p-4"><h5>Daftar Pelanggan</h5><div class="table-responsive"><table class="table"><thead><tr><th>No</th><th>Nama</th><th>No HP</th><th>Email</th><th>Alamat</th><th>Aksi</th></tr></thead><tbody>
<?php $no=1;$q=$conn->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");while($r=$q->fetch_assoc()):?><tr><td><?= $no++ ?></td><td><?= htmlspecialchars($r['nama']) ?></td><td><?= htmlspecialchars($r['no_hp']) ?></td><td><?= htmlspecialchars($r['email']) ?></td><td><?= htmlspecialchars($r['alamat']) ?></td><td><a class="btn btn-sm btn-warning" href="?aksi=edit&id=<?= $r['id_pelanggan'] ?>">Edit</a> <a class="btn btn-sm btn-danger" onclick="return confirm('Hapus pelanggan?')" href="?aksi=hapus&id=<?= $r['id_pelanggan'] ?>">Hapus</a></td></tr><?php endwhile;?></tbody></table></div></div>
<?php require "footer.php";?>