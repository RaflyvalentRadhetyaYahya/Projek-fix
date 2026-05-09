<?php
$page = "riwayat";
$nama_mahasiswa = "Ahmad Fauzi";
$nim = "10123001";
$prodi = "Teknik Informatika";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Magang - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/mahasiswa_navbar.css">
    <link rel="stylesheet" href="assets/css/riwayat.css">
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
        
        <div class="mx-auto" style="max-width: 800px;">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-white rounded-circle shadow-sm me-3 text-primary d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold" style="color: #1e293b;">Riwayat Aktivitas</h4>
                    <p class="text-muted mb-0" style="font-size: 14px;">Pantau seluruh aktivitas magang Anda di sini</p>
                </div>
            </div>
            
            <div class="timeline-container">
                <!-- Timeline Item: Edit -->
                <div class="timeline-item">
                    <div class="timeline-icon warning">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> Hari Ini, 14:05
                        </div>
                        <h6 class="timeline-title">Mengedit Laporan Harian</h6>
                        <p class="timeline-desc">Anda telah mengubah isi laporan harian untuk tanggal 12 Mei 2026. Laporan berhasil disimpan.</p>
                    </div>
                </div>

                <!-- Timeline Item: View -->
                <div class="timeline-item">
                    <div class="timeline-icon info">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> Hari Ini, 13:42
                        </div>
                        <h6 class="timeline-title">Melihat Laporan Harian</h6>
                        <p class="timeline-desc">Anda melihat detail laporan harian untuk minggu ke-12. Tidak ada perubahan yang dilakukan.</p>
                    </div>
                </div>

                <!-- Timeline Item: Approved Logbook -->
                <div class="timeline-item">
                    <div class="timeline-icon success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> Hari Ini, 09:41
                        </div>
                        <h6 class="timeline-title text-success">Logbook Minggu 12 Disetujui</h6>
                        <p class="timeline-desc">Dosen pembimbing telah menyetujui logbook harian Anda untuk minggu ke-12.</p>
                    </div>
                </div>
                
                <!-- Timeline Item: Bimbingan -->
                <div class="timeline-item">
                    <div class="timeline-icon primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> 11 Mei 2026, 14:30
                        </div>
                        <h6 class="timeline-title">Bimbingan dengan Dosen</h6>
                        <p class="timeline-desc">Melakukan bimbingan progress magang via Zoom meeting dengan Bapak Dr. Ir. Suyanto, M.T.</p>
                    </div>
                </div>
                
                <!-- Timeline Item: Mulai Magang -->
                <div class="timeline-item">
                    <div class="timeline-icon primary">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> 01 Mei 2026, 08:00
                        </div>
                        <h6 class="timeline-title">Mulai Program Magang</h6>
                        <p class="timeline-desc">Hari pertama pelaksanaan program magang di PT TechCorp Solutions.</p>
                    </div>
                </div>
                
                <!-- Timeline Item: Proposal Approved -->
                <div class="timeline-item">
                    <div class="timeline-icon success">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">
                            <i class="bi bi-calendar3"></i> 28 April 2026, 10:15
                        </div>
                        <h6 class="timeline-title text-success">Pengajuan Proposal Disetujui</h6>
                        <p class="timeline-desc">Admin program studi telah menyetujui pengajuan proposal magang Anda.</p>
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
