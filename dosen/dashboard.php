<?php
$page = "dashboard";
$nama_dosen = "Dr. Ir. Suyanto, M.T.";
$nip = "197508232005011002";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Dosen - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/dosen.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

</head>
<body>

        <!-- Top Navigation -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <img src="../admin/assets/logo.png" alt="SIMMAG" style="height:35px;" onerror="this.src='https://ui-avatars.com/api/?name=S+M&background=2563eb&color=fff&rounded=true&font-size=0.5'">
        </div>
        
        <div class="nav-center">
            <a href="dashboard.php" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Beranda</a>
            <a href="mahasiswa_bimbingan.php" class="<?= ($page == 'mahasiswa_bimbingan') ? 'active' : '' ?>">Mahasiswa Bimbingan</a>
            <a href="review_logbook.php" class="<?= ($page == 'review_logbook') ? 'active' : '' ?>">Review Logbook</a>
            <a href="review_laporan.php" class="<?= ($page == 'review_laporan') ? 'active' : '' ?>">Review Laporan</a>
        </div>
        
        <div class="nav-right">
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-text">
                            <div class="name"><?= $nama_dosen ?? "Dr. Ir. Suyanto, M.T." ?></div>
                            <div class="role">Dosen Pembimbing</div>
                        </div>
                        <i class="bi bi-chevron-down ms-2 text-muted" style="font-size: 12px;"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">Akun Saya</div>
                        <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                        <div class="dropdown-divider my-1"></div>
                        <a href="../index.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
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
                <div class="welcome-banner">
                    <h2>Selamat Datang, Bapak Suyanto!</h2>
                    <p>Ada 12 logbook baru dan 3 laporan akhir yang menunggu persetujuan Anda hari ini.</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <!-- Card 1 -->
                    <div class="stat-card">
                        <div class="stat-title text-blue">Total Mahasiswa</div>
                        <div class="stat-subtitle">Sedang Magang</div>
                        <div class="stat-number">15</div>
                    </div>
                    <!-- Card 2 -->
                    <div class="stat-card">
                        <div class="stat-title text-orange">Menunggu Review</div>
                        <div class="stat-subtitle">Logbook Harian</div>
                        <div class="stat-number">12</div>
                    </div>
                    <!-- Card 3 -->
                    <div class="stat-card">
                        <div class="stat-title text-red">Perlu Dinilai</div>
                        <div class="stat-subtitle">Laporan Akhir</div>
                        <div class="stat-number">3</div>
                    </div>
                    <!-- Card 4 -->
                    <div class="stat-card">
                        <div class="stat-title text-green">Bimbingan</div>
                        <div class="stat-subtitle">Terjadwal Minggu Ini</div>
                        <div class="stat-number">4</div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="recent-card">
                    <div class="card-header-custom">
                        <span>Aktivitas Mahasiswa Bimbingan</span>
                        <a href="review_logbook.php" class="btn btn-sm btn-light text-primary fw-semibold" style="font-size:12px;">Lihat Semua</a>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon orange">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="activity-content w-100">
                            <h6>Ahmad Fauzi (10123001)</h6>
                            <p>Mengajukan logbook harian untuk minggu ke-12.</p>
                            <div class="activity-time">2 Jam yang lalu</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon orange">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="activity-content w-100">
                            <h6>Rina Melati (10123045)</h6>
                            <p>Mengunggah draft Laporan Akhir Magang.</p>
                            <div class="activity-time">Kemarin, 15:30</div>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon green">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="activity-content w-100">
                            <h6>Budi Santoso (10123022)</h6>
                            <p>Telah menyelesaikan seluruh logbook minggu ke-10.</p>
                            <div class="activity-time">2 Hari yang lalu</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-lg-4 col-xl-3">
                <div class="recent-card">
                    <div class="card-header-custom mb-3">
                        <span>Tindakan Cepat</span>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="review_logbook.php" class="btn btn-warning text-dark fw-semibold py-2 d-flex align-items-center justify-content-center" style="border-radius: 8px; background: #fef08a; border: 1px solid #fde047;">
                            <i class="bi bi-journal-check me-2"></i> Review Logbook
                        </a>
                        <a href="review_laporan.php" class="btn btn-primary fw-semibold py-2 d-flex align-items-center justify-content-center" style="border-radius: 8px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                            <i class="bi bi-file-earmark-text me-2"></i> Nilai Laporan Akhir
                        </a>
                        <a href="mahasiswa_bimbingan.php" class="btn btn-light text-dark fw-semibold py-2 d-flex align-items-center justify-content-center" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                            <i class="bi bi-people me-2"></i> Daftar Mahasiswa
                        </a>
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
                <a href="../index.php" style="flex:1;text-decoration:none;"><button class="btn-logout" style="width:100%;">Ya, Keluar</button></a>
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
            <div class="profile-modal-name"><?= $nama_dosen ?? "Dr. Ir. Suyanto, M.T." ?></div>
            <div class="profile-modal-role">Dosen Pembimbing</div>

            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="profile-detail-label">Surel</div>
                    <div class="profile-detail-value">suyanto@univ.ac.id</div>
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
                <div class="profile-detail-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="profile-detail-label">NIP</div>
                    <div class="profile-detail-value"><?= $nip ?? "197508232005011002" ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dosen.js"></script>
</body>
</html>
