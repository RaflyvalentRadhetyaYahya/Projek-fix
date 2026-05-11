<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php?role=admin");
    exit;
}

// Fetch stats from DB
$total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$total_dosen = $pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
$total_perusahaan = $pdo->query("SELECT COUNT(*) FROM perusahaan")->fetchColumn();
$total_magang = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang WHERE status = 'Disetujui'")->fetchColumn();

$status_selesai = 0; // We can base this on laporan_akhir or dates, mock for now
$status_berlangsung = $total_magang;
$status_menunggu = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang WHERE status = 'Menunggu'")->fetchColumn();

$total_status = $status_selesai + $status_berlangsung + $status_menunggu;
if ($total_status == 0) $total_status = 1; // Prevent division by zero

$deg_selesai = ($status_selesai / $total_status) * 360;
$deg_berlangsung = ($status_berlangsung / $total_status) * 360;
$deg_menunggu = ($status_menunggu / $total_status) * 360;

$page = "beranda";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Beranda Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- FONT -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/beranda.css">
</head>

<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">

    <div class="logo-container mb-4">
        <img src="assets/logo.png" class="logo-sidebar">
    </div>

    <div class="sidebar-menu flex-grow-1">
        <a href="beranda.php" class="<?= ($page == 'beranda') ? 'active' : '' ?>">
            <i class="bi bi-grid"></i> Beranda
        </a>

        <a href="mahasiswa.php">
            <i class="bi bi-people me-2"></i> Mahasiswa
        </a>

        <a href="dosen.php">
            <i class="bi bi-person-badge me-2"></i> Dosen
        </a>

        <a href="pengajuan.php">
            <i class="bi bi-send me-2"></i> Pengajuan
        </a>
    </div>


</div>

<!-- HEADER -->
<div class="p-4 w-100">

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

<!-- CONTENT -->
<div class="mb-2">
    <h4 style="color:#2d6cdf;">Gambaran Umum Beranda</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Selamat datang kembali, Admin.</p>
</div>

<div class="row mt-4 g-2">

<div class="col-md-3">
    <div class="card p-3">
        <h6>Total Mahasiswa</h6>
        <h3><?= $total_mahasiswa ?></h3>
    </div>
</div>

<div class="col-md-3">
    <div class="card p-3">
        <h6>Total Dosen</h6>
        <h3><?= $total_dosen ?></h3>
    </div>
</div>

<div class="col-md-3">
    <div class="card p-3">
        <h6>Perusahaan Mitra</h6>
        <h3><?= $total_perusahaan ?></h3>
    </div>
</div>

<div class="col-md-3">
    <div class="card p-3">
        <h6>Magang Aktif</h6>
        <h3><?= $total_magang ?></h3>
    </div>
</div>

</div>

<div class="row mt-3 align-items-stretch">

<div class="col-md-4 d-flex">
    <div class="card p-4 text-center h-100 d-flex flex-column w-100">
        <h5>Status Magang</h5>

        <div class="pie-chart my-4"
            style="background: conic-gradient(
                #198754 0deg <?= $deg_selesai ?>deg,
                #0d6efd <?= $deg_selesai ?>deg <?= $deg_selesai + $deg_berlangsung ?>deg,
                #ffc107 <?= $deg_selesai + $deg_berlangsung ?>deg 360deg
            );">
        </div>

        <div class="text-start">
            <div class="status-item">
                <span><span class="legend-dot" style="background:#198754;"></span> Selesai</span>
                <span><?= $status_selesai ?></span>
            </div>
            <div class="status-item">
                <span><span class="legend-dot" style="background:#0d6efd;"></span> Berlangsung</span>
                <span><?= $status_berlangsung ?></span>
            </div>
            <div class="status-item">
                <span><span class="legend-dot" style="background:#ffc107;"></span> Menunggu</span>
                <span><?= $status_menunggu ?></span>
            </div>
        </div>
    </div>
</div>

<div class="col-md-8 d-flex">
    <div class="card p-4 h-100 d-flex flex-column w-100">
        <h5>Aktivitas Terbaru</h5>

        <div class="mt-3 flex-grow-1">

            <div class="d-flex mb-3">
                <div class="activity-icon bg-warning-soft me-3">
                    <i class="bi bi-clock"></i>
                </div>
                <div>
                    <small>Ahmad Fauzi mengajukan proposal magang</small><br>
                    <small class="text-muted">5 menit lalu</small>
                </div>
            </div>

            <hr>

            <div class="d-flex mb-3">
                <div class="activity-icon bg-success-soft me-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <small>Data mahasiswa diperbarui</small><br>
                    <small class="text-muted">30 menit lalu</small>
                </div>
            </div>

            <hr>

            <div class="d-flex mb-3">
                <div class="activity-icon bg-success-soft me-3">
                    <i class="bi bi-upload"></i>
                </div>
                <div>
                    <small>Laporan akhir dikirim</small><br>
                    <small class="text-muted">2 jam lalu</small>
                </div>
            </div>

            <hr>

            <div class="d-flex">
                <div class="activity-icon bg-danger-soft me-3">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <small>Batas waktu jurnal harian</small><br>
                    <small class="text-muted">5 jam lalu</small>
                </div>
            </div>

        </div>
    </div>
</div>

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

<script src="assets/js/admin.js"></script>

</body>
</html>