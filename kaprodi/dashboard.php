<?php
session_start();
require_once '../config.php';

require_once '../auth/guard.php';
require_role('kaprodi');

$kaprodi_id = $_SESSION['kaprodi_id'];
$prodi_id = $_SESSION['prodi_id'];

$stmt = $pdo->prepare("SELECT nama_lengkap, nip FROM kaprodi WHERE id = ?");
$stmt->execute([$kaprodi_id]);
$kaprodi = $stmt->fetch();

$page = "dashboard";
$nama_kaprodi = $kaprodi ? $kaprodi['nama_lengkap'] : "Prof. Dr. Budi Susanto, M.Kom.";
$nip = $kaprodi ? $kaprodi['nip'] : "197001011995121001";

$total_mahasiswa = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.prodi_id = $prodi_id")->fetchColumn();
$total_dosen = $pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn();
$total_perusahaan = $pdo->query("SELECT COUNT(*) FROM perusahaan")->fetchColumn();

// Status magang untuk chart
$selesai = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.prodi_id = $prodi_id AND pm.status = 'Disetujui' AND pm.tgl_selesai < CURDATE()")->fetchColumn();
$berlangsung = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.prodi_id = $prodi_id AND pm.status = 'Disetujui' AND pm.tgl_selesai >= CURDATE()")->fetchColumn();
$menunggu = $pdo->query("SELECT COUNT(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.prodi_id = $prodi_id AND (pm.status = 'Menunggu' OR pm.dosen_id IS NULL)")->fetchColumn();

$total_status = $selesai + $berlangsung + $menunggu;
if ($total_status == 0) $total_status = 1;
$deg_selesai = ($selesai / $total_status) * 360;
$deg_berlangsung = ($berlangsung / $total_status) * 360;
$deg_menunggu = ($menunggu / $total_status) * 360;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Kaprodi - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/kaprodi.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

    <!-- Top Navigation -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <img src="../admin/assets/logo.png" alt="SIMMAG" onerror="this.src='https://ui-avatars.com/api/?name=S+M&background=7c3aed&color=fff&rounded=true&font-size=0.5'">
        </div>
        
        <div class="nav-center">
            <a href="dashboard.php" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Beranda</a>
            <a href="monitoring_mahasiswa.php" class="<?= ($page == 'monitoring_mahasiswa') ? 'active' : '' ?>">Pemantauan Mahasiswa</a>
            <a href="pemantauan_dosen.php" class="<?= ($page == 'pemantauan_dosen') ? 'active' : '' ?>">Kinerja Dosen</a>
        </div>
        
        <div class="nav-right">
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-text">
                            <div class="name"><?= $nama_kaprodi ?></div>
                            <div class="role">Ketua Program Studi</div>
                        </div>
                        <i class="bi bi-chevron-down ms-2 text-muted chevron-icon"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">Akun Saya</div>
                        <a href="javascript:void(0)" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                        <div class="dropdown-divider my-1"></div>
                        <a href="javascript:void(0)" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
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
                    <h2>Selamat Datang, Bapak Budi Susanto!</h2>
                    <p>Program Studi Teknik Informatika memiliki 156 mahasiswa aktif dalam program magang saat ini.</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title text-purple">Total Mahasiswa</div>
                        <div class="stat-subtitle">Sedang Magang</div>
                        <div class="stat-number"><?= $total_mahasiswa ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title text-blue">Dosen Pembimbing</div>
                        <div class="stat-subtitle">Terlibat Program</div>
                        <div class="stat-number"><?= $total_dosen ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title text-orange">Perusahaan Mitra</div>
                        <div class="stat-subtitle">Kerjasama Aktif</div>
                        <div class="stat-number"><?= $total_perusahaan ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title text-green">Tingkat Kelulusan</div>
                        <div class="stat-subtitle">Rata-rata Nilai A</div>
                        <div class="stat-number">85%</div>
                    </div>
                </div>

                <!-- Chart & Monitoring -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="recent-card">
                            <div class="card-header-custom">
                                <span>Status Magang Mahasiswa</span>
                            </div>
                            <div class="chart-container">
                                <div class="pie-chart" style="background: conic-gradient(
                                    #10b981 0deg <?= $deg_selesai ?>deg,
                                    #3b82f6 <?= $deg_selesai ?>deg <?= $deg_selesai + $deg_berlangsung ?>deg,
                                    #f59e0b <?= $deg_selesai + $deg_berlangsung ?>deg 360deg
                                );"></div>
                                <div class="legend">
                                    <div class="legend-item">
                                        <div class="legend-color legend-green"></div>
                                        Selesai Magang (<?= $selesai ?>)
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color legend-blue"></div>
                                        Sedang Berlangsung (<?= $berlangsung ?>)
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color legend-yellow"></div>
                                        Menunggu Plotting (<?= $menunggu ?>)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="recent-card">
                            <div class="card-header-custom">
                                <span>Aktivitas Program Studi</span>
                                <a href="monitoring_mahasiswa.php" class="btn btn-sm btn-light text-primary fw-semibold view-all-link">Lihat Semua</a>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon purple">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="activity-content w-100">
                                    <h6>Laporan Akhir Disetujui</h6>
                                    <p>Dr. Ir. Suyanto, M.T. menyetujui laporan akhir Budi Santoso.</p>
                                    <div class="activity-time">1 Jam yang lalu</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon green">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <div class="activity-content w-100">
                                    <h6>Penempatan Baru</h6>
                                    <p>5 Mahasiswa baru saja di-plot ke PT Telkom Indonesia.</p>
                                    <div class="activity-time">Kemarin, 10:30</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon orange">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="activity-content w-100">
                                    <h6>Keterlambatan Logbook</h6>
                                    <p>Terdapat 12 mahasiswa yang terlambat mengumpulkan logbook minggu ini.</p>
                                    <div class="activity-time">2 Hari yang lalu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-lg-4 col-xl-3">
                <div class="recent-card">
                    <div class="card-header-custom mb-3">
                        <span>Akses Cepat</span>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="monitoring_mahasiswa.php" class="btn fw-semibold py-3 d-flex align-items-center justify-content-center flex-column gap-2 quick-link quick-link-purple">
                            <i class="bi bi-mortarboard quick-link-icon"></i>
                            Data Mahasiswa
                        </a>
                        <a href="pemantauan_dosen.php" class="btn fw-semibold py-3 d-flex align-items-center justify-content-center flex-column gap-2 quick-link quick-link-blue">
                            <i class="bi bi-person-video3 quick-link-icon"></i>
                            Kinerja Dosen
                        </a>
                        <a href="javascript:alert('Mengunduh Laporan Rekapitulasi...')" class="btn btn-light text-dark fw-semibold py-3 d-flex align-items-center justify-content-center flex-column gap-2 quick-link quick-link-neutral">
                            <i class="bi bi-file-earmark-spreadsheet text-success quick-link-icon"></i>
                            Unduh Rekap Nilai
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
                <a href="../logout.php" class="modal-logout-link"><button class="btn-logout">Ya, Keluar</button></a>
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
            <div class="profile-modal-name"><?= $nama_kaprodi ?></div>
            <div class="profile-modal-role">Ketua Program Studi</div>

            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="profile-detail-label">Surel</div>
                    <div class="profile-detail-value">budisusanto@univ.ac.id</div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-telephone"></i></div>
                <div>
                    <div class="profile-detail-label">No. Telepon</div>
                    <div class="profile-detail-value">+62 811-2233-4455</div>
                </div>
            </div>
            <div class="profile-detail">
                <div class="profile-detail-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="profile-detail-label">NIP</div>
                    <div class="profile-detail-value"><?= $nip ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/kaprodi.js"></script>
</body>
</html>
