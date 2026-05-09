<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['dosen_logged_in'])) {
    $_SESSION['dosen_logged_in'] = true;
    $_SESSION['dosen_id'] = 1;
}

$dosen_id = $_SESSION['dosen_id'];
$stmtD = $pdo->prepare("SELECT nama_lengkap, nip FROM dosen WHERE id = ?");
$stmtD->execute([$dosen_id]);
$dosen = $stmtD->fetch();

$page = "mahasiswa_bimbingan";
$nama_dosen = $dosen ? $dosen['nama_lengkap'] : "Dr. Ir. Suyanto, M.T.";
$nip = $dosen ? $dosen['nip'] : "197508232005011002";

// Ambil data mahasiswa bimbingan
$stmtMhs = $pdo->prepare("
    SELECT m.nim, m.nama_lengkap, p.nama_perusahaan, 
           pm.tgl_mulai, pm.tgl_selesai, pm.id as pengajuan_id
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    WHERE pm.dosen_id = ? AND pm.status = 'Disetujui'
    ORDER BY m.nama_lengkap ASC
");
$stmtMhs->execute([$dosen_id]);
$data_mhs = $stmtMhs->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Bimbingan - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/dosen.css">
    <link rel="stylesheet" href="assets/css/mahasiswa_bimbingan.css">
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
        
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="mb-0 fw-bold" style="color: #1e293b;">Daftar Mahasiswa Bimbingan</h4>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" placeholder="Cari nama atau NIM...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase;">
                        <tr>
                            <th class="py-3 px-4 rounded-start" style="font-weight: 600;">Nama Mahasiswa</th>
                            <th class="py-3" style="font-weight: 600;">NIM</th>
                            <th class="py-3" style="font-weight: 600;">Tempat Magang</th>
                            <th class="py-3" style="font-weight: 600; width: 180px;">Progress Magang</th>
                            <th class="py-3 text-center" style="font-weight: 600;">Status</th>
                            <th class="py-3 px-4 rounded-end text-end" style="font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_mhs as $mhs): 
                            // Hitung progress berdasarkan waktu (sama seperti Kaprodi)
                            $mulai = strtotime($mhs['tgl_mulai']);
                            $selesai = strtotime($mhs['tgl_selesai']);
                            $sekarang = time();
                            
                            $total_hari = ($selesai - $mulai) / (60 * 60 * 24);
                            $hari_berjalan = ($sekarang - $mulai) / (60 * 60 * 24);
                            
                            if ($hari_berjalan < 0) $hari_berjalan = 0;
                            if ($hari_berjalan > $total_hari) $hari_berjalan = $total_hari;
                            
                            $progress = ($total_hari > 0) ? round(($hari_berjalan / $total_hari) * 100) : 0;
                            
                            $minggu = ceil($hari_berjalan / 7);
                            if ($minggu == 0) $minggu = 1;
                            
                            $status_badge = "bg-success-subtle text-success border border-success-subtle";
                            $status_text = "Aktif";
                            $progress_color = "#2563eb";
                            if ($progress >= 100) {
                                $status_badge = "bg-primary-subtle text-primary border border-primary-subtle";
                                $status_text = "Selesai Magang";
                                $progress_color = "#10b981"; // green
                                $minggu = "Selesai";
                            }
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                                        <?= substr($mhs['nama_lengkap'], 0, 2) ?>
                                    </div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($mhs['nama_lengkap']) ?></div>
                                </div>
                            </td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($mhs['nim']) ?></td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($mhs['nama_perusahaan']) ?></td>
                            <td class="py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size: 12px; font-weight: 600; color: <?= $progress_color ?>;"><?= $progress ?>%</span>
                                    <span style="font-size: 11px; color: #94a3b8;"><?= is_numeric($minggu) ? 'Minggu '.$minggu : $minggu ?></span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: <?= $progress ?>%; background-color: <?= $progress_color ?>;"></div>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge <?= $status_badge ?> px-3 py-1 rounded-pill"><?= $status_text ?></span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="review_logbook.php" class="btn btn-sm btn-light text-primary" title="Lihat Logbook"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_mhs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada mahasiswa bimbingan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3 text-muted" style="font-size: 13px;">
                <span>Menampilkan 1 hingga 3 dari 15 data</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Sebelumnya</a></li>
                        <li class="page-item active"><a class="page-link" href="javascript:void(0)">1</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:void(0)">2</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:void(0)">3</a></li>
                        <li class="page-item"><a class="page-link" href="javascript:void(0)">Selanjutnya</a></li>
                    </ul>
                </nav>
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
