<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'admin'");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: beranda.php");
        exit;
    } else {
        $error = "Username atau password salah, atau Anda bukan Admin!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Magang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <!-- Decorative Animated Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="assets/logoLogin.png" alt="Logo Sistem">
            </div>
            <h4>Selamat Datang Admin</h4>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 14px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Username</label>
                <input type="text" name="username" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan username">
            </div>
            <div class="mb-4 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-4" style="padding: 10px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                Masuk ke Dashboard
            </button>
            
            <div class="divider">
                <span>Atau Masuk Dengan</span>
            </div>

            <div class="sso-container mt-3 mb-4">
                <button type="button" class="btn btn-sso" onclick="alert('SSO saat ini sedang maintenance.')">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="SSO Logo" class="sso-icon">
                    SSO Kampus
                </button>
            </div>

            <div class="text-center mt-3 mb-2">
                <small class="text-muted" style="font-size: 12px;">Pastikan Anda menggunakan jaringan yang aman. Hubungi IT Support jika mengalami kendala.</small>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
