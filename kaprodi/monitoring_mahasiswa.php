<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['kaprodi_logged_in'])) {
    $_SESSION['kaprodi_logged_in'] = true;
    $_SESSION['kaprodi_id'] = 1;
    $_SESSION['prodi_id'] = 1;
}

$kaprodi_id = $_SESSION['kaprodi_id'];
$prodi_id = $_SESSION['prodi_id'];

$stmt = $pdo->prepare("SELECT nama_lengkap, nip FROM kaprodi WHERE id = ?");
$stmt->execute([$kaprodi_id]);
$kaprodi = $stmt->fetch();

$page = "monitoring_mahasiswa";
$nama_kaprodi = $kaprodi ? $kaprodi['nama_lengkap'] : "Prof. Dr. Budi Susanto, M.Kom.";
$nip = $kaprodi ? $kaprodi['nip'] : "197001011995121001";

// Ambil data monitoring
$stmtMon = $pdo->prepare("
    SELECT m.nim, m.nama_lengkap, p.nama_perusahaan, d.nama_lengkap as nama_dosen,
           pm.status, pm.tgl_mulai, pm.tgl_selesai
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    LEFT JOIN dosen d ON pm.dosen_id = d.id
    WHERE m.prodi_id = ? AND pm.status = 'Disetujui'
    ORDER BY m.nama_lengkap ASC
");
$stmtMon->execute([$prodi_id]);
$data_monitoring = $stmtMon->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemantauan Mahasiswa - SIMMAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/kaprodi.css">
    <link rel="stylesheet" href="assets/css/monitoring.css">
</head>
<body>
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
                        <div class="profile-avatar"><i class="bi bi-person"></i></div>
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

    <div class="main-container">
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h4 class="mb-0 fw-bold page-title">Data Monitoring Mahasiswa Magang</h4>
                <div class="filter-section">
                    <select class="form-select form-select-sm filter-select">
                        <option value="">Semua Angkatan</option>
                        <option value="2022">Angkatan 2022</option>
                        <option value="2023" selected>Angkatan 2023</option>
                    </select>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Cari nama atau NIM...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Nama Mahasiswa</th>
                            <th class="py-3">Tempat Magang</th>
                            <th class="py-3">Dosen Pembimbing</th>
                            <th class="py-3 col-progress">Progress Magang</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 px-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_monitoring as $row): 
                            // Hitung progress sederhana berdasarkan waktu (bisa diganti logbook nanti)
                            $mulai = strtotime($row['tgl_mulai']);
                            $selesai = strtotime($row['tgl_selesai']);
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
                            if ($progress >= 100) {
                                $status_badge = "bg-primary-subtle text-primary border border-primary-subtle";
                                $status_text = "Selesai Magang";
                            }
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                <div class="text-muted sub-text"><?= htmlspecialchars($row['nim']) ?></div>
                            </td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                            <td class="py-3"><div class="text-dark dosen-name"><?= htmlspecialchars($row['nama_dosen'] ?? 'Belum Diplot') ?></div></td>
                            <td class="py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 progress-info">
                                    <span class="progress-percent"><?= $progress ?>%</span>
                                    <span class="progress-week">Minggu <?= $minggu ?></span>
                                </div>
                                <div class="progress-bar-container"><div class="progress-bar-fill" style="width:<?= $progress ?>%;"></div></div>
                            </td>
                            <td class="py-3 text-center"><span class="badge <?= $status_badge ?> px-3 py-1 rounded-pill"><?= $status_text ?></span></td>
                            <td class="px-4 py-3 text-end"><button class="btn btn-sm btn-light text-purple" title="Lihat Detail" onclick="alert('Menampilkan detail mahasiswa...')"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_monitoring)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data monitoring mahasiswa magang.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 text-muted pagination-footer">
                <span>Menampilkan 1 hingga 4 dari 156 data</span>
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
            <div class="modal-icon"><i class="bi bi-box-arrow-right"></i></div>
            <h5>Keluar dari Akun?</h5>
            <p>Apakah Anda yakin ingin keluar dari sistem?</p>
            <div class="modal-actions">
                <button class="btn-cancel" id="btnBatal">Batal</button>
                <a href="../index.php" class="modal-logout-link"><button class="btn-logout">Ya, Keluar</button></a>
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
            <div class="profile-modal-avatar"><i class="bi bi-person"></i></div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/kaprodi.js"></script>
</body>
</html>
