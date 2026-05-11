<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php?role=admin");
    exit;
}

$page = "mahasiswa";

// Ambil data prodi untuk dropdown
$stmtProdi = $pdo->query("SELECT id, nama_prodi FROM prodi ORDER BY nama_prodi ASC");
$data_prodi = $stmtProdi->fetchAll();

$success = false;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $prodi_id = (int)$_POST['prodi_id'];
    $no_telp = trim($_POST['no_telp'] ?? '');
    $email = trim($_POST['email']);

    // Validasi email harus @student.polije.ac.id
    $domain = strtolower(explode('@', $email)[1] ?? '');
    if ($domain !== 'student.polije.ac.id') {
        $error = 'Email mahasiswa harus menggunakan domain @student.polije.ac.id untuk login SSO.';
    }

    if (!$error) {
        // Cek apakah email sudah terdaftar
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $cek->execute([$email, $email]);
        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar di sistem.';
        }
    }

    if (!$error) {
        // Buat user account (password random karena login via SSO)
        $randomPass = md5(bin2hex(random_bytes(16)));
        $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'mahasiswa')");
        $stmtUser->execute([$email, $randomPass]);
        $user_id = $pdo->lastInsertId();

        // Buat data mahasiswa
        $stmtMhs = $pdo->prepare("INSERT INTO mahasiswa (user_id, prodi_id, nim, nama_lengkap, no_telp, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtMhs->execute([$user_id, $prodi_id, $nim, $nama, $no_telp, $email]);

        header("Location: mahasiswa.php?status=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Mahasiswa - Sistem Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/tambahDosen.css">
</head>

<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">

    <div class="logo-container mb-4">
        <img src="assets/logo.png" class="logo-sidebar">
    </div>

    <div class="sidebar-menu flex-grow-1">
        <a href="beranda.php">
            <i class="bi bi-grid"></i> Beranda
        </a>

        <a href="mahasiswa.php" class="<?= ($page == 'mahasiswa') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Mahasiswa
        </a>

        <a href="dosen.php">
            <i class="bi bi-person-badge"></i> Dosen
        </a>

        <a href="pengajuan.php">
            <i class="bi bi-send"></i> Pengajuan
        </a>

        <a href="perusahaan.php">
            <i class="bi bi-building"></i> Perusahaan
        </a>
    </div>

</div>

<!-- CONTENT AREA -->
<div class="p-4 w-100">

<!-- HEADER -->
<div class="header-top">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari mahasiswa, dosen, atau pengajuan...">
    </div>
    
    <div class="profile-section">
        <div class="profile-wrapper">
            <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                <div class="profile-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div class="profile-text">
                    <div class="name">Admin Sistem</div>
                    <div class="email">admin@magang.ac.id</div>
                </div>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">Akun Saya</div>
                <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                <a href="../logout.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
            </div>
        </div>
    </div>
</div>

<!-- PAGE TITLE -->
<div class="mb-4">
    <h4 style="color:#2d6cdf;">Tambah Mahasiswa</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Daftarkan mahasiswa baru. Mahasiswa akan login menggunakan Google SSO dengan email <strong>@student.polije.ac.id</strong>.</p>
</div>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 14px; border-radius: 10px;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- FORM CARD -->
<div class="card form-card">
    <form action="" method="POST">

        <!-- SSO Info Banner -->
        <div style="background: linear-gradient(135deg, #eef2ff, #f0f4ff); padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; border-left: 4px solid #2563eb;">
            <div class="d-flex align-items-center">
                <i class="bi bi-shield-check me-2" style="font-size: 20px; color: #2563eb;"></i>
                <div>
                    <div style="font-weight: 600; color: #1e293b; font-size: 14px;">Login via Google SSO</div>
                    <div style="font-size: 12px; color: #64748b;">Mahasiswa tidak memerlukan password. Mereka akan login menggunakan akun Google sesuai email yang didaftarkan.</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">Email Google (SSO Login) <span class="text-danger">*</span></label>
                <input type="email" class="form-control custom-input" name="email" placeholder="contoh: nama@student.polije.ac.id" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <small class="text-muted" style="font-size:11px;">Harus menggunakan domain @student.polije.ac.id</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
                <input type="text" class="form-control custom-input" name="nim" placeholder="Masukkan NIM Mahasiswa" required value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control custom-input" name="nama" placeholder="Contoh: Ahmad Fauzi" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                <select class="form-select custom-input" name="prodi_id" required>
                    <option value="" disabled selected>Pilih Program Studi</option>
                    <?php foreach ($data_prodi as $prodi): ?>
                    <option value="<?= $prodi['id'] ?>" <?= (($_POST['prodi_id'] ?? '') == $prodi['id']) ? 'selected' : '' ?>><?= htmlspecialchars($prodi['nama_prodi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nomor Telepon / WhatsApp</label>
                <input type="text" class="form-control custom-input" name="no_telp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>">
            </div>
        </div>
        
        <hr class="my-4" style="border-color: #f1f5f9;">
        
        <div class="d-flex justify-content-end gap-2">
            <a href="mahasiswa.php" class="btn btn-cancel-form">Batal</a>
            <button type="submit" class="btn btn-save-form">
                <i class="bi bi-save me-1"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

</div>
</div>

<!-- LOGOUT MODAL -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="bi bi-box-arrow-right"></i>
        </div>
        <h5>Keluar dari Akun?</h5>
        <p>Apakah Anda yakin ingin keluar dari sistem?</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatal">Batal</button>
            <a href="../logout.php" style="flex:1;text-decoration:none;"><button class="btn-logout" style="width:100%;">Ya, Keluar</button></a>
        </div>
    </div>
</div>

<!-- PROFILE MODAL -->
<div class="modal-overlay" id="profileModal">
    <div class="profile-modal-box">
        <div class="profile-modal-header">
            <h5>Info Profil</h5>
            <button class="profile-modal-close" id="btnTutupProfil">&times;</button>
        </div>
        <div class="profile-modal-avatar">
            <i class="bi bi-person"></i>
        </div>
        <div class="profile-modal-name">Admin Sistem</div>
        <div class="profile-modal-role">Admin</div>

        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
            <div>
                <div class="profile-detail-label">Surel</div>
                <div class="profile-detail-value">admin@magang.ac.id</div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-telephone"></i></div>
            <div>
                <div class="profile-detail-label">No. Telepon</div>
                <div class="profile-detail-value">+62 812-3456-7890</div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="profile-detail-label">Peran</div>
                <div class="profile-detail-value">Admin Utama</div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="profile-detail-label">Masuk Terakhir</div>
                <div class="profile-detail-value">20 April 2026, 21:00</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>

</body>
</html>
