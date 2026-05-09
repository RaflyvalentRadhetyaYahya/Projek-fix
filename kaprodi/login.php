<?php
session_start();

// Mock login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sso_login'])) {
    $_SESSION['kaprodi_logged_in'] = true;
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kaprodi - Sistem Magang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../admin/assets/css/login.css">
</head>
<body>

    <!-- Decorative Animated Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="../admin/assets/logoLogin.png" alt="Logo Sistem">
            </div>
            <h4>Selamat Datang</h4>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="sso_login" value="1">
            
            <div class="sso-container mb-4">
                <p class="text-center text-muted mb-3" style="font-size: 14px;">Gunakan akun instansi Anda untuk masuk ke sistem</p>
                <button type="submit" class="btn btn-sso">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="SSO Logo" class="sso-icon">
                    Masuk dengan SSO Kampus
                </button>
            </div>

            <div class="divider">
                <span>Keamanan Data</span>
            </div>

            <div class="text-center mt-3 mb-2">
                <small class="text-muted" style="font-size: 12px;">Pastikan Anda menggunakan jaringan yang aman. Hubungi IT Support jika mengalami kendala.</small>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
