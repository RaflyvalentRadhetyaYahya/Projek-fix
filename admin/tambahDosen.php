<?php
$page = "dosen";

// Mock Data Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses simpan data (mock)
    header("Location: dosen.php?status=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Dosen - Sistem Magang</title>

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

        <a href="mahasiswa.php">
            <i class="bi bi-people"></i> Mahasiswa
        </a>

        <a href="dosen.php" class="<?= ($page == 'dosen') ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Dosen
        </a>

        <a href="pengajuan.php">
            <i class="bi bi-send"></i> Pengajuan
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
                <a href="login.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
            </div>
        </div>
    </div>
</div>

<!-- PAGE TITLE -->
<div class="mb-4">
    <h4 style="color:#2d6cdf;">Tambah Dosen Pembimbing</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Isi formulir di bawah ini untuk mendaftarkan dosen pembimbing magang.</p>
</div>

<!-- FORM CARD -->
<div class="card form-card">
    <form action="" method="POST">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">Nomor Induk Dosen Nasional (NIDN)</label>
                <input type="text" class="form-control custom-input" name="nidn" placeholder="Masukkan NIDN Dosen" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap (Beserta Gelar)</label>
                <input type="text" class="form-control custom-input" name="nama" placeholder="Contoh: Dr. Budi Santoso, M.Kom." required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Email Aktif</label>
                <input type="email" class="form-control custom-input" name="email" placeholder="contoh: dosen@magang.ac.id" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Nomor Telepon / WhatsApp</label>
                <input type="text" class="form-control custom-input" name="telepon" placeholder="Contoh: 081234567890">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Status Dosen</label>
                <select class="form-select custom-input" name="status" required>
                    <option value="Aktif">Aktif Mengajar</option>
                    <option value="Cuti">Sedang Cuti</option>
                </select>
            </div>
        </div>
        
        <hr class="my-4" style="border-color: #f1f5f9;">
        
        <div class="d-flex justify-content-end gap-2">
            <a href="dosen.php" class="btn btn-cancel-form">Batal</a>
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
            <a href="login.php" style="flex:1;text-decoration:none;"><button class="btn-logout" style="width:100%;">Ya, Keluar</button></a>
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

<script src="assets/js/admin.js"></script>

</body>
</html>
