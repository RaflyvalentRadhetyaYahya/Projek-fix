<?php
session_start();

// Mock reset password logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        $success = "Tautan untuk mengatur ulang kata sandi telah dikirim ke $email";
    } else {
        $error = "Silakan masukkan alamat email Anda!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Magang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 40%, #7c3aed 100%);
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Decorative Animated Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            background: #ff007a;
            top: -100px;
            left: -100px;
        }
        
        .shape-2 {
            width: 500px;
            height: 500px;
            background: #00e5ff;
            bottom: -150px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 50px); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            z-index: 1;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .login-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin: 0 auto 16px;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .login-header h4 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
            text-align: center;
        }

        .login-header p {
            color: #64748b;
            font-size: 14px;
            text-align: center;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px 12px 45px;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 25px;
        }

        /* Ikon berada di dalam input */
        .icon-wrapper {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 10;
            pointer-events: none;
        }

        .input-group-custom:focus-within .icon-wrapper {
            color: #2563eb;
        }

        .btn-login {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 14px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(1px);
        }
        
        .alert {
            font-size: 13px;
            border-radius: 10px;
            padding: 10px 15px;
        }
        
        .back-link {
            font-size: 14px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>

    <!-- Decorative Animated Shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="bi bi-key-fill"></i>
            </div>
            <h4>Lupa Password</h4>
            <p>Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group-custom">
                <div class="icon-wrapper"><i class="bi bi-envelope"></i></div>
                <input type="email" name="email" class="form-control" placeholder="Alamat Email" required>
            </div>

            <button type="submit" class="btn btn-login mb-4">
                Kirim Tautan Reset
            </button>
            
            <div class="text-center">
                <a href="login.php" class="back-link">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
