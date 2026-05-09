<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['perusahaan_logged_in']) || $_SESSION['perusahaan_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page = "beranda";
$perusahaan_id = $_SESSION['perusahaan_id'];
$nama_perusahaan = $_SESSION['nama_perusahaan'];

// Fetch stats
$total_mhs = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_magang WHERE perusahaan_id = ? AND status = 'Disetujui'");
$total_mhs->execute([$perusahaan_id]);
$total_mahasiswa = $total_mhs->fetchColumn();

$total_log = $pdo->prepare("SELECT COUNT(*) FROM logbook l JOIN pengajuan_magang p ON l.pengajuan_id = p.id WHERE p.perusahaan_id = ? AND l.status_validasi = 'Menunggu'");
$total_log->execute([$perusahaan_id]);
$logbook_menunggu = $total_log->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Beranda Perusahaan - Sistem Magang</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../admin/assets/css/admin.css">

<style>
/* Override for Perusahaan theme */
.sidebar { background: #064e3b; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: #fff; }
.card { border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
</style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div class="sidebar d-flex flex-column">
        <div class="logo-container mb-4 text-white text-center pt-3">
            <h5 class="fw-bold">MITRA MAGANG</h5>
        </div>
        <div class="sidebar-menu flex-grow-1">
            <a href="beranda.php" class="active"><i class="bi bi-grid"></i> Beranda</a>
            <a href="mahasiswa_aktif.php"><i class="bi bi-people"></i> Mahasiswa Aktif</a>
            <a href="validasi_logbook.php"><i class="bi bi-journal-check"></i> Validasi Logbook</a>
            <a href="penilaian.php"><i class="bi bi-star"></i> Penilaian</a>
        </div>
    </div>

    <!-- CONTENT AREA -->
    <div class="p-4 w-100" style="background:#f8fafc; min-height:100vh;">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 style="color:#064e3b; font-weight:700;">Dashboard Mitra: <?= htmlspecialchars($nama_perusahaan) ?></h4>
                <p class="text-muted mb-0">Selamat datang kembali di portal mentor perusahaan.</p>
            </div>
            <div class="profile-section">
                <a href="logout.php" class="btn btn-outline-danger btn-sm" style="border-radius:8px; font-weight:600;"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card p-4 h-100" style="border-left: 4px solid #10b981;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Mahasiswa Magang Aktif</h6>
                            <h2 class="mb-0 fw-bold" style="color:#1e293b;"><?= $total_mahasiswa ?></h2>
                        </div>
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; color:#10b981; font-size:24px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card p-4 h-100" style="border-left: 4px solid #f59e0b;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Logbook Menunggu Validasi</h6>
                            <h2 class="mb-0 fw-bold" style="color:#1e293b;"><?= $logbook_menunggu ?></h2>
                        </div>
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; color:#f59e0b; font-size:24px;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
