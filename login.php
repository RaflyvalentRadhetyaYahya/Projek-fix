<?php
require_once __DIR__ . '/config.php';
session_start();

// Jika sudah login, arahkan sesuai role
if (!empty($_SESSION['admin_logged_in'])) {
    \App\Http\Redirect::to('admin/beranda.php');
}
if (!empty($_SESSION['kaprodi_logged_in'])) {
    \App\Http\Redirect::to('kaprodi/dashboard.php');
}
if (!empty($_SESSION['dosen_logged_in'])) {
    \App\Http\Redirect::to('dosen/dashboard.php');
}
if (!empty($_SESSION['mahasiswa_logged_in'])) {
    \App\Http\Redirect::to('mahasiswa/beranda.php');
}
if (!empty($_SESSION['perusahaan_logged_in'])) {
    \App\Http\Redirect::to('perusahaan/beranda.php');
}

$error = null;
$info = null;

if (empty($error) && isset($_GET['error'])) {
    $qErr = trim((string)$_GET['error']);
    if ($qErr === 'profile') {
        $info = 'Akun berhasil terverifikasi, tetapi data profil untuk role ini belum terdaftar/terhubung di database. Hubungi admin untuk melakukan pemetaan akun.';
    }
}

// Login manual khusus admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $usernameInput = trim($_POST['username']);
    $passwordHash = md5($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$usernameInput, $passwordHash]);
    $user = $stmt->fetch();

    if ($user) {
        foreach (['admin_logged_in','kaprodi_logged_in','dosen_logged_in','mahasiswa_logged_in','perusahaan_logged_in'] as $k) {
            unset($_SESSION[$k]);
        }
        \App\Auth\SessionMapper::setRoleSession($pdo, $user);
        \App\Http\Redirect::to('admin/beranda.php');
    }
    $error = 'Username atau password salah, atau Anda bukan Admin!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Magang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="admin/assets/css/login.css">
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="admin/assets/logoLogin.png" alt="Logo Sistem" onerror="this.style.display='none';">
            </div>
            <h4>Selamat Datang Admin</h4>
            <p>Silakan masuk untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 14px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($info): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="font-size: 14px;">
                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($info) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <form method="POST" action="login.php">
            <div class="mb-3 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Username</label>
                <input type="text" name="username" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan username">
            </div>
            <div class="mb-4 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3" style="padding: 12px; border-radius: 12px; font-weight: 700; background: #1d4ed8; border: none;">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="divider">
            <span>Atau Masuk Dengan</span>
        </div>

        <div class="sso-container mb-4">
            <a class="btn btn-sso w-100" href="auth/google_start.php">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google" class="sso-icon" onerror="this.style.display='none';">
                Sign in with Google
            </a>
        </div>

        <div class="text-center mt-3 mb-2">
            <small class="text-muted" style="font-size: 12px;">Akses SSO ditentukan oleh role akun yang terdaftar di database.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
