<?php

require_once "../config/koneksi.php";
require_once "../config/auth.php";

$page_title = "Transaksi Penyewaan";

$aksi = $_GET['aksi'] ?? '';
$err = "";


/* =====================================================
   HAPUS DATA PENYEWAAN
   ===================================================== */

if ($aksi == "hapus") {

    $id = (int)($_GET['id'] ?? 0);

    // Cek apakah sudah mempunyai data pengembalian
    $cek = $conn->query("
        SELECT id_pengembalian 
        FROM pengembalian 
        WHERE id_sewa = $id
    ");

    if ($cek && $cek->num_rows > 0) {

        $err = "Data tidak bisa dihapus karena sudah memiliki data pengembalian.";

    } else {

        $data = $conn->query("
            SELECT id_barang, status 
            FROM penyewaan 
            WHERE id_sewa = $id
        ")->fetch_assoc();

        if ($data) {

            $conn->begin_transaction();

            try {

                // Jika status masih Disewa, kembalikan stok
                if ($data['status'] == "Disewa") {

                    $id_barang = (int)$data['id_barang'];

                    $conn->query("
                        UPDATE barang
                        SET stok = stok + 1,
                            status = 'Tersedia'
                        WHERE id_barang = $id_barang
                    ");
                }

                $conn->query("
                    DELETE FROM penyewaan
                    WHERE id_sewa = $id
                ");

                $conn->commit();

                header("Location: penyewaan.php");
                exit;

            } catch (Exception $e) {

                $conn->rollback();

                $err = "Data gagal dihapus.";
            }
        }
    }
}


/* =====================================================
   SIMPAN / TAMBAH / EDIT PENYEWAAN
   ===================================================== */

if (isset($_POST['simpan'])) {

    $id_sewa = (int)($_POST['id_sewa'] ?? 0);

    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $id_barang = (int)$_POST['id_barang'];

    $tanggal_sewa = $_POST['tanggal_sewa'];
    $tanggal_kembali = $_POST['tanggal_rencana_kembali'];

    $status = $_POST['status'] ?? "Disewa";


    // Hitung lama sewa
    $d1 = new DateTime($tanggal_sewa);
    $d2 = new DateTime($tanggal_kembali);

    $lama_sewa = max(1, $d1->diff($d2)->days);


    // Ambil harga barang
    $barang = $conn->query("
        SELECT *
        FROM barang
        WHERE id_barang = $id_barang
    ")->fetch_assoc();


    if (!$barang) {

        $err = "Barang tidak ditemukan.";

    } else {

        $total = $lama_sewa * $barang['harga_sewa'];


        /* =================================================
           EDIT DATA
           ================================================= */

        if ($id_sewa > 0) {

            $lama_lama = $conn->query("
                SELECT *
                FROM penyewaan
                WHERE id_sewa = $id_sewa
            ")->fetch_assoc();


            if (!$lama_lama) {

                $err = "Data penyewaan tidak ditemukan.";

            } else {

                $conn->begin_transaction();

                try {

                    $barang_lama = (int)$lama_lama['id_barang'];
                    $status_lama = $lama_lama['status'];


                    /* -------------------------------------
                       Jika status lama Disewa
                       ------------------------------------- */

                    if ($status_lama == "Disewa") {

                        // Kembalikan stok barang lama
                        $conn->query("
                            UPDATE barang
                            SET stok = stok + 1,
                                status = 'Tersedia'
                            WHERE id_barang = $barang_lama
                        ");
                    }


                    /* -------------------------------------
                       Jika status baru Disewa
                       ------------------------------------- */

                    if ($status == "Disewa") {

                        // Pastikan stok tersedia
                        $cek_stok = $conn->query("
                            SELECT stok
                            FROM barang
                            WHERE id_barang = $id_barang
                        ")->fetch_assoc();

                        if (!$cek_stok || $cek_stok['stok'] < 1) {

                            throw new Exception(
                                "Stok barang tidak tersedia."
                            );
                        }


                        // Kurangi stok
                        $conn->query("
                            UPDATE barang
                            SET stok = stok - 1,
                                status = IF(stok - 1 <= 0,
                                'Disewa',
                                'Tersedia')
                            WHERE id_barang = $id_barang
                        ");
                    }


                    // Update data penyewaan
                    $stmt = $conn->prepare("
                        UPDATE penyewaan SET
                            id_pelanggan = ?,
                            id_barang = ?,
                            tanggal_sewa = ?,
                            tanggal_rencana_kembali = ?,
                            lama_sewa = ?,
                            total = ?,
                            status = ?
                        WHERE id_sewa = ?
                    ");

                    $stmt->bind_param(
                        "iissidsi",
                        $id_pelanggan,
                        $id_barang,
                        $tanggal_sewa,
                        $tanggal_kembali,
                        $lama_sewa,
                        $total,
                        $status,
                        $id_sewa
                    );

                    $stmt->execute();

                    $conn->commit();

                    header("Location: penyewaan.php");
                    exit;

                } catch (Exception $e) {

                    $conn->rollback();

                    $err = $e->getMessage();
                }
            }
        }


        /* =================================================
           TAMBAH DATA
           ================================================= */

        else {

            if ($barang['stok'] < 1) {

                $err = "Barang tersebut sedang tidak tersedia.";

            } else {

                $kode = "SEWA-" .
                        date("YmdHis") .
                        "-" .
                        rand(10, 99);


                $conn->begin_transaction();

                try {

                    $stmt = $conn->prepare("
                        INSERT INTO penyewaan
                        (
                            kode_sewa,
                            id_pelanggan,
                            id_barang,
                            tanggal_sewa,
                            tanggal_rencana_kembali,
                            lama_sewa,
                            total,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "siissids",
                        $kode,
                        $id_pelanggan,
                        $id_barang,
                        $tanggal_sewa,
                        $tanggal_kembali,
                        $lama_sewa,
                        $total,
                        $status
                    );

                    $stmt->execute();


                    // Jika langsung Disewa, kurangi stok
                    if ($status == "Disewa") {

                        $conn->query("
                            UPDATE barang
                            SET stok = stok - 1,
                                status = IF(stok - 1 <= 0,
                                'Disewa',
                                'Tersedia')
                            WHERE id_barang = $id_barang
                        ");
                    }


                    $conn->commit();

                    header("Location: penyewaan.php");
                    exit;

                } catch (Exception $e) {

                    $conn->rollback();

                    $err = "Transaksi gagal dibuat.";
                }
            }
        }
    }
}


/* =====================================================
   DATA EDIT
   ===================================================== */

$edit = null;

if ($aksi == "edit") {

    $id = (int)($_GET['id'] ?? 0);

    $edit = $conn->query("
        SELECT *
        FROM penyewaan
        WHERE id_sewa = $id
    ")->fetch_assoc();
}


require "header.php";

?>


<!-- PESAN ERROR -->

<?php if ($err != ""): ?>

<div class="alert alert-danger">

    ⚠️ <?= htmlspecialchars($err) ?>

</div>

<?php endif; ?>


<!-- =====================================================
     FORM PENYEWAAN
===================================================== -->

<div class="card p-4 mb-4">

    <h5 class="fw-bold mb-4">

        <?= $edit ? "✏️ Edit Penyewaan" : "📝 Buat Penyewaan" ?>

    </h5>


    <form method="POST">

        <input
            type="hidden"
            name="id_sewa"
            value="<?= $edit['id_sewa'] ?? 0 ?>"
        >


        <div class="row g-3">


            <!-- PELANGGAN -->

            <div class="col-md-6">

                <label class="form-label">
                    Pelanggan
                </label>

                <select
                    name="id_pelanggan"
                    class="form-select"
                    required
                >

                    <option value="">
                        -- pilih pelanggan --
                    </option>

                    <?php

                    $pelanggan = $conn->query("
                        SELECT *
                        FROM pelanggan
                        ORDER BY nama ASC
                    ");

                    while ($p = $pelanggan->fetch_assoc()):

                    ?>

                    <option
                        value="<?= $p['id_pelanggan'] ?>"
                        <?= (
                            isset($edit['id_pelanggan']) &&
                            $edit['id_pelanggan'] == $p['id_pelanggan']
                        ) ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($p['nama']) ?>

                    </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- BARANG -->

            <div class="col-md-6">

                <label class="form-label">
                    Barang
                </label>

                <select
                    name="id_barang"
                    class="form-select"
                    required
                >

                    <option value="">
                        -- pilih barang --
                    </option>

                    <?php

                    $barang_list = $conn->query("
                        SELECT *
                        FROM barang
                        ORDER BY nama_barang ASC
                    ");

                    while ($b = $barang_list->fetch_assoc()):

                    ?>

                    <option
                        value="<?= $b['id_barang'] ?>"
                        <?= (
                            isset($edit['id_barang']) &&
                            $edit['id_barang'] == $b['id_barang']
                        ) ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($b['nama_barang']) ?>

                        - Rp <?= number_format(
                            $b['harga_sewa'],
                            0,
                            ',',
                            '.'
                        ) ?>

                        /hari

                        (Stok <?= $b['stok'] ?>)

                    </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- TANGGAL SEWA -->

            <div class="col-md-4">

                <label class="form-label">
                    Tanggal Sewa
                </label>

                <input
                    type="date"
                    name="tanggal_sewa"
                    class="form-control"
                    value="<?= $edit['tanggal_sewa'] ?? date('Y-m-d') ?>"
                    required
                >

            </div>


            <!-- TANGGAL KEMBALI -->

            <div class="col-md-4">

                <label class="form-label">
                    Rencana Kembali
                </label>

                <input
                    type="date"
                    name="tanggal_rencana_kembali"
                    class="form-control"
                    value="<?= $edit['tanggal_rencana_kembali'] ?? date('Y-m-d', strtotime('+1 day')) ?>"
                    required
                >

            </div>


            <!-- STATUS -->

            <div class="col-md-4">

                <label class="form-label">
                    Status
                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="Disewa"
                        <?= (
                            !isset($edit['status']) ||
                            $edit['status'] == "Disewa"
                        ) ? "selected" : "" ?>
                    >
                        Disewa
                    </option>

                    <option
                        value="Selesai"
                        <?= (
                            isset($edit['status']) &&
                            $edit['status'] == "Selesai"
                        ) ? "selected" : "" ?>
                    >
                        Selesai
                    </option>

                </select>

            </div>


            <!-- BUTTON -->

            <div class="col-12">

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-primary"
                >

                    💾
                    <?= $edit ? "Update Penyewaan" : "Simpan Penyewaan" ?>

                </button>


                <?php if ($edit): ?>

                <a
                    href="penyewaan.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

                <?php endif; ?>

            </div>

        </div>

    </form>

</div>



<!-- =====================================================
     RIWAYAT PENYEWAAN
===================================================== -->

<div class="card p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="fw-bold mb-0">
            📋 Riwayat Penyewaan
        </h5>

    </div>


    <div class="table-responsive">

        <table class="table">

            <thead>

                <tr>

                    <th>No</th>

                    <th>Kode</th>

                    <th>Pelanggan</th>

                    <th>Barang</th>

                    <th>Periode</th>

                    <th>Lama</th>

                    <th>Total</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $no = 1;

            $q = $conn->query("
                SELECT
                    s.*,
                    p.nama AS pelanggan,
                    b.nama_barang
                FROM penyewaan s

                JOIN pelanggan p
                    ON p.id_pelanggan = s.id_pelanggan

                JOIN barang b
                    ON b.id_barang = s.id_barang

                ORDER BY s.id_sewa DESC
            ");


            while ($r = $q->fetch_assoc()):

            ?>

                <tr>

                    <td>
                        <?= $no++ ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($r['kode_sewa']) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($r['pelanggan']) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($r['nama_barang']) ?>
                    </td>


                    <td>

                        <?= date(
                            'd/m/Y',
                            strtotime($r['tanggal_sewa'])
                        ) ?>

                        s/d

                        <?= date(
                            'd/m/Y',
                            strtotime($r['tanggal_rencana_kembali'])
                        ) ?>

                    </td>


                    <td>
                        <?= $r['lama_sewa'] ?> hari
                    </td>


                    <td>
                        <strong>
                            Rp <?= number_format(
                                $r['total'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </strong>
                    </td>


                    <!-- STATUS -->

                    <td>

                        <?php if ($r['status'] == "Disewa"): ?>

                            <span class="badge bg-warning text-dark">
                                Disewa
                            </span>

                        <?php else: ?>

                            <span class="badge bg-success">
                                Selesai
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- AKSI -->

                    <td>

                        <a
                            href="penyewaan.php?aksi=edit&id=<?= $r['id_sewa'] ?>"
                            class="btn btn-sm btn-warning"
                        >
                            ✏️ Edit
                        </a>


                        <a
                            href="penyewaan.php?aksi=hapus&id=<?= $r['id_sewa'] ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm(
                                'Apakah kamu yakin ingin menghapus transaksi ini?'
                            )"
                        >
                            🗑️ Hapus
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>


<?php require "footer.php"; ?>