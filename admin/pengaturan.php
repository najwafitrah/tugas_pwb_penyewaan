<?php

require_once "../config/koneksi.php";
require_once "../config/auth.php";

$page_title = "Pengaturan";

$msg = "";
$err = "";

/* =========================
   PROSES UPDATE PENGATURAN
   ========================= */
if (isset($_POST['ubah'])) {

    $id       = $_SESSION['user_id'];
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($password != "") {

        $password_hash = hash('sha256', $password);

        $stmt = $conn->prepare("
            UPDATE users 
            SET nama = ?, username = ?, password = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssi",
            $nama,
            $username,
            $password_hash,
            $id
        );

    } else {

        $stmt = $conn->prepare("
            UPDATE users 
            SET nama = ?, username = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssi",
            $nama,
            $username,
            $id
        );
    }

    if ($stmt->execute()) {

        $_SESSION['nama'] = $nama;

        $msg = "Pengaturan berhasil disimpan.";

    } else {

        $err = "Gagal menyimpan pengaturan. Username mungkin sudah digunakan.";
    }
}


/* =========================
   AMBIL DATA USER
   ========================= */

$id_user = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, nama, username
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id_user);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

require "header.php";

?>

<div class="row">

    <div class="col-md-8">

        <div class="card p-4">

            <h5 class="fw-bold mb-4">
                ⚙️ Pengaturan Akun
            </h5>

            <?php if ($msg != ""): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($msg) ?>
                </div>

            <?php endif; ?>


            <?php if ($err != ""): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($err) ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($user['nama']) ?>"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($user['username']) ?>"
                        required
                    >

                </div>


                <div class="mb-3">

                    <label class="form-label">
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Kosongkan jika tidak ingin mengubah password"
                    >

                    <small class="text-muted">
                        Isi password hanya jika ingin mengganti password.
                    </small>

                </div>


                <button
                    type="submit"
                    name="ubah"
                    class="btn btn-primary"
                >
                    💾 Simpan Perubahan
                </button>


                <a
                    href="dashboard.php"
                    class="btn btn-secondary"
                >
                    Kembali
                </a>

            </form>

        </div>

    </div>


    <div class="col-md-4">

        <div class="card p-4">

            <h6 class="fw-bold">
                Informasi Akun
            </h6>

            <hr>

            <p class="mb-2">
                <strong>Nama:</strong><br>
                <?= htmlspecialchars($user['nama']) ?>
            </p>

            <p class="mb-2">
                <strong>Username:</strong><br>
                <?= htmlspecialchars($user['username']) ?>
            </p>

            <p class="mb-0">
                <strong>Status:</strong><br>
                <span class="badge bg-success">
                    Administrator
                </span>
            </p>

        </div>

    </div>

</div>

<?php require "footer.php"; ?>