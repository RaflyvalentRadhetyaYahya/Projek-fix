<?php
$page = "laporan_akhir";
$nama_mahasiswa = "Ahmad Fauzi";
$nim = "10123001";
$prodi = "Teknik Informatika";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Akhir - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/mahasiswa_navbar.css">
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
            <div class="col-md-8">
                <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px; height: 100%;">
                    <h4 class="mb-1 fw-bold" style="color: #1e293b;">Unggah Laporan Akhir</h4>
                    <p class="text-muted mb-4" style="font-size: 14px;">Silakan unggah dokumen laporan akhir magang Anda yang telah disetujui oleh pembimbing lapangan.</p>
                    
                    <form>
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Judul Laporan</label>
                            <input type="text" class="form-control" style="border-radius: 8px;" value="Pengembangan Sistem Informasi Berbasis Web di PT TechCorp Solutions">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Dokumen Laporan Akhir (PDF)</label>
                            
                            <div class="border border-dashed p-5 text-center mt-2" style="border-radius: 12px; border-width: 2px; border-color: #cbd5e1; background: #f8fafc; border-style: dashed;">
                                <div class="mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 60px; height: 60px; border-radius: 50%;">
                                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 28px;"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-1" style="color: #1e293b;">Klik untuk unggah file</h6>
                                <p class="text-muted small mb-0">Atau seret dan lepas file ke area ini (Maks. 10MB)</p>
                                <input type="file" class="d-none">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary px-4 py-2 fw-semibold w-100" style="border-radius: 8px; background: linear-gradient(135deg, #2563eb, #7c3aed); border: none;">Kirim Laporan Akhir</button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <h6 class="fw-bold mb-3" style="color: #1e293b;">Status Laporan Akhir</h6>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-warning-subtle text-warning d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px; border-radius: 10px;">
                            <i class="bi bi-hourglass-split" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold" style="color: #1e293b;">Belum Dikumpulkan</h6>
                            <p class="mb-0 text-muted" style="font-size: 12px;">Batas waktu: 30 Mei 2026</p>
                        </div>
                    </div>
                </div>
                
                <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe;">
                    <h6 class="fw-bold mb-3" style="color: #1e3a8a;">Informasi Penting</h6>
                    <ul class="text-muted ps-3 mb-0" style="font-size: 13px;">
                        <li class="mb-2">Pastikan laporan sudah ditandatangani oleh pembimbing lapangan.</li>
                        <li class="mb-2">Format file wajib PDF.</li>
                        <li class="mb-0">Ukuran maksimal file adalah 10MB.</li>
                    </ul>
                    <hr class="my-3 border-primary opacity-25">
                    <a href="#" class="btn btn-outline-primary btn-sm w-100 fw-semibold" style="border-radius: 6px;"><i class="bi bi-download me-2"></i>Unduh Template Laporan</a>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS (optional but good to have) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/mahasiswa.js"></script>
</body>
</html>
