<?php
session_start();
require_once "config/koneksi.php";

if (isset($_SESSION['login'])) {
    header("Location: admin/dashboard.php"); exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id,nama,username,password FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && hash('sha256', $password) === $user['password']) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        header("Location: admin/dashboard.php"); exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Sistem Penyewaan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="text-center mb-4">
        <div class="logo-circle">S</div>
        <h3 class="fw-bold mt-3">Sistem Penyewaan</h3>
        <p class="text-muted">Silakan masuk ke akun admin</p>
    </div>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <label class="form-label">Username</label>
        <input class="form-control mb-3" name="username" required autofocus>
        <label class="form-label">Password</label>
        <input class="form-control mb-4" type="password" name="password" required>
        <button class="btn btn-primary w-100 py-2">Login</button>
    </form>
    <div class="small text-muted text-center mt-3">Default: admin / admin123</div>
</div>
</body></html>
