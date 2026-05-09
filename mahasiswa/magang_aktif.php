<?php
$page = "magang_aktif";
$nama_mahasiswa = "Ahmad Fauzi";
$nim = "10123001";
$prodi = "Teknik Informatika";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Magang - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/mahasiswa_navbar.css">
    <link rel="stylesheet" href="assets/css/magang_aktif.css">
</head>
<body>

    <!-- Top Navigation -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <img src="../admin/assets/logo.png" alt="SIMMAG">
        </div>
        
        <div class="nav-center">
            <a href="magang_aktif.php" class="<?= ($page == 'magang_aktif') ? 'active' : '' ?>">Beranda</a>
            <a href="laporan_harian.php" class="<?= ($page == 'laporan_harian') ? 'active' : '' ?>">Laporan Harian</a>
            <a href="laporan_akhir.php" class="<?= ($page == 'laporan_akhir') ? 'active' : '' ?>">Laporan Akhir</a>
            <a href="riwayat.php" class="<?= ($page == 'riwayat') ? 'active' : '' ?>">Riwayat</a>
        </div>
        
        <div class="nav-right">
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-text">
                            <div class="name"><?= $nama_mahasiswa ?></div>
                            <div class="role">Mahasiswa</div>
                        </div>
                        <i class="bi bi-chevron-down ms-2 text-muted" style="font-size: 12px;"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">Akun Saya</div>
                        <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                        <div class="dropdown-divider my-1"></div>
                        <a href="login.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="row g-4">
            <!-- Left Content -->
            <div class="col-lg-8 col-xl-9">
                <!-- Welcome Banner -->
                <div class="welcome-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>Selamat Datang!</h2>
                        <p>Kamu telah menyelesaikan 70% dari program magangmu...</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="cetak_surat.php" target="_blank" class="btn btn-light text-primary fw-bold" style="border-radius: 8px;"><i class="bi bi-printer me-2"></i>Cetak Surat Pengantar</a>
                        <a href="cetak_nilai.php" target="_blank" class="btn btn-light text-success fw-bold" style="border-radius: 8px;"><i class="bi bi-award me-2"></i>Cetak Nilai Akhir</a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <!-- Card 1 -->
                    <div class="stat-card">
                        <div class="stat-title text-blue">Total logbook</div>
                        <div class="stat-subtitle">Membuat UI Dashboard</div>
                        <div class="stat-number">40</div>
                    </div>
                    <!-- Card 2 -->
                    <div class="stat-card">
                        <div class="stat-title text-green">Diterima</div>
                        <div class="stat-subtitle">Membuat Halaman Login</div>
                        <div class="stat-number">20</div>
                    </div>
                    <!-- Card 3 -->
                    <div class="stat-card">
                        <div class="stat-title text-orange">Ditunda</div>
                        <div class="stat-subtitle">Membuat UI Dashboard</div>
                        <div class="stat-number">16</div>
                    </div>
                    <!-- Card 4 -->
                    <div class="stat-card">
                        <div class="stat-title text-red">Ditolak</div>
                        <div class="stat-subtitle">Integrasi Database</div>
                        <div class="stat-number">4</div>
                    </div>
                </div>

                <!-- Progress Section -->
                <div class="progress-card">
                    <div class="progress-header">Progress Magang</div>
                    
                    <div class="d-flex justify-content-between align-items-end">
                        <div class="progress-company">TechCorp<br>Solutions</div>
                        <div class="text-end">
                            <div class="progress-percentage">45%</div>
                            <div class="progress-week">Minggu ke 12</div>
                        </div>
                    </div>
                    
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 45%;"></div>
                    </div>
                </div>
            </div>

            <!-- Right Content (Notifications) -->
            <div class="col-lg-4 col-xl-3">
                <div class="notifications-container">
                    <div class="notif-main-title">Notifikasi Terbaru</div>

                    <!-- Notif 1 -->
                    <div class="notif-card">
                        <div class="notif-header">
                            <div class="notif-icon">
                                <i class="bi bi-check2"></i>
                            </div>
                            <div class="notif-title">Logbook Disetujui</div>
                        </div>
                        <div class="notif-body">
                            Advisor approved your Week 11 activities. Great technical depth!
                        </div>
                        <div class="notif-time">2 JAM YANG LALU</div>
                    </div>

                    <!-- Notif 2 -->
                    <div class="notif-card">
                        <div class="notif-header">
                            <div class="notif-icon orange">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="notif-title">Revisi</div>
                        </div>
                        <div class="notif-body">
                            Please update the flowchart in your Week 10 report for clarity.
                        </div>
                        <div class="notif-time">KEMARIN</div>
                    </div>

                    <!-- Notif 3 -->
                    <div class="notif-card">
                        <div class="notif-header">
                            <div class="notif-icon">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <div class="notif-title">Batas waktu pengumpulan laporan akhir</div>
                        </div>
                        <div class="notif-body">
                            Reminder: The submission window for final reports opens.
                        </div>
                        <div class="notif-time">3 MARET, 2026</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional but good to have) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/mahasiswa.js"></script>
</body>
</html>
