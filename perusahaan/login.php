<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'perusahaan'");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['perusahaan_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        
        // Dapatkan ID Perusahaan
        $stmtPerush = $pdo->prepare("SELECT id, nama_perusahaan FROM perusahaan WHERE user_id = ?");
        $stmtPerush->execute([$user['id']]);
        $perush = $stmtPerush->fetch();
        
        $_SESSION['perusahaan_id'] = $perush ? $perush['id'] : null;
        $_SESSION['nama_perusahaan'] = $perush ? $perush['nama_perusahaan'] : 'Mentor Perusahaan';
        
        header("Location: beranda.php");
        exit;
    } else {
        $error = "Username atau password salah, atau Anda bukan Mentor Perusahaan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Perusahaan - Sistem Magang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Using admin login css for consistency -->
    <link rel="stylesheet" href="../admin/assets/css/login.css">
</head>
<body>

    <div class="shape shape-1" style="background: linear-gradient(45deg, #10b981, #059669);"></div>
    <div class="shape shape-2" style="background: linear-gradient(45deg, #34d399, #10b981);"></div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <img src="../admin/assets/logoLogin.png" alt="Logo Sistem" onerror="this.style.display='none';">
            </div>
            <h4>Portal Mitra Perusahaan</h4>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 14px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Username / Email</label>
                <input type="text" name="username" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan username">
            </div>
            <div class="mb-4 text-start">
                <label class="form-label text-muted" style="font-size: 13px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control" required style="padding: 10px 14px; border-radius: 8px; font-size: 14px;" placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-4" style="padding: 10px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #10b981, #059669); border: none;">
                Masuk ke Dashboard
            </button>
            
            <div class="text-center mt-3 mb-2">
                <small class="text-muted" style="font-size: 12px;">Portal khusus bagi pembimbing/mentor dari perusahaan mitra.</small>
            </div>
        </form>
    </div>

</body>
</html>
