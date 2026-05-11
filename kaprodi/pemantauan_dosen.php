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

$page = "pemantauan_dosen";
$nama_kaprodi = $kaprodi ? $kaprodi['nama_lengkap'] : "Prof. Dr. Budi Susanto, M.Kom.";
$nip = $kaprodi ? $kaprodi['nip'] : "197001011995121001";

// Ambil data dosen pembimbing dan statistik bimbingannya
$stmtDosen = $pdo->prepare("
    SELECT d.id, d.nama_lengkap, d.nip, d.bidang_keahlian,
           (SELECT COUNT(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE pm.dosen_id = d.id AND pm.status = 'Disetujui' AND m.prodi_id = ?) as jml_bimbingan,
           (SELECT COUNT(*) FROM logbook l JOIN pengajuan_magang pm ON l.pengajuan_id = pm.id JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE pm.dosen_id = d.id AND l.status_validasi = 'Disetujui' AND m.prodi_id = ?) as logbook_belum_dinilai
    FROM dosen d
    ORDER BY d.nama_lengkap ASC
");
$stmtDosen->execute([$prodi_id, $prodi_id]);
$data_dosen = $stmtDosen->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinerja Dosen - SIMMAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/kaprodi.css">
    <link rel="stylesheet" href="assets/css/pemantauan.css">
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
                <h4 class="mb-0 fw-bold page-title">Pemantauan Kinerja Dosen Pembimbing</h4>
                <div class="filter-section">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Cari nama dosen atau NIP...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Nama Dosen</th>
                            <th class="py-3 text-center">Mahasiswa Bimbingan</th>
                            <th class="py-3 text-center">Logbook Belum Dinilai</th>
                            <th class="py-3 text-center">Laporan Belum Dinilai</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 px-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_dosen as $d): 
                            $jml = $d['jml_bimbingan'];
                            $logbook_tunggak = $d['logbook_belum_dinilai']; // mockup untuk belum dinilai oleh dosen, untuk saat ini pakai jumlah yang disetujui
                            
                            $status_badge = "bg-success-subtle text-success border border-success-subtle";
                            $status_text = "Sangat Baik";
                            if ($logbook_tunggak > 0) {
                                $status_badge = "bg-warning-subtle text-warning border border-warning-subtle";
                                $status_text = "Perlu Perhatian";
                            }
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($d['nama_lengkap']) ?></div>
                                <div class="text-muted sub-text">NIP. <?= htmlspecialchars($d['nip']) ?></div>
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fw-bold bimbingan-count"><?= $jml ?></span>
                                    <div class="avatar-group mt-1">
                                        <div class="avatar-item" style="background:#3b82f6;"><i class="bi bi-people"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <?php if($logbook_tunggak > 0): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill badge-lg"><?= $logbook_tunggak ?> Logbook</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border px-3 py-1 rounded-pill">0 Logbook</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-light text-muted border px-3 py-1 rounded-pill">0 Laporan</span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge <?= $status_badge ?> px-3 py-1 rounded-pill"><?= $status_text ?></span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-purple" title="Kirim Pengingat" onclick="alert('Pengingat telah dikirim ke dosen terkait')"><i class="bi bi-bell"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_dosen)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data kinerja dosen.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 text-muted pagination-footer">
                <span>Menampilkan 1 hingga 3 dari 32 dosen</span>
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
