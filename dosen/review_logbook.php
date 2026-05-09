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

$page = "review_logbook";
$nama_dosen = $dosen ? $dosen['nama_lengkap'] : "Dr. Ir. Suyanto, M.T.";
$nip = $dosen ? $dosen['nip'] : "197508232005011002";

// Handle aksi persetujuan / revisi logbook
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logbook_id'])) {
    $logbook_id = $_POST['logbook_id'];
    $status_validasi = $_POST['status_validasi']; // 'Disetujui' atau 'Revisi'
    $catatan_dosen = $_POST['catatan_dosen'] ?? '';
    
    $upd = $pdo->prepare("UPDATE logbook SET status_validasi = ?, catatan_dosen = ? WHERE id = ?");
    $upd->execute([$status_validasi, $catatan_dosen, $logbook_id]);
    
    header("Location: review_logbook.php");
    exit;
}

// Ambil data logbook mahasiswa bimbingan yang sudah divalidasi pembimbing lapangan
$stmtLog = $pdo->prepare("
    SELECT l.*, m.nama_lengkap, m.nim, p.nama_perusahaan
    FROM logbook l
    JOIN pengajuan_magang pm ON l.pengajuan_id = pm.id
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    WHERE pm.dosen_id = ?
    ORDER BY l.tanggal DESC
");
$stmtLog->execute([$dosen_id]);
$data_logbook = $stmtLog->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Logbook - SIMMAG</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/dosen.css">
    <link rel="stylesheet" href="assets/css/review.css">
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
            <h4 class="mb-3 fw-bold" style="color: #1e293b;">Persetujuan Logbook Harian</h4>
            
            <div class="filter-pills">
                <div class="filter-pill active">Menunggu Persetujuan (12)</div>
                <div class="filter-pill">Telah Disetujui (154)</div>
                <div class="filter-pill">Perlu Revisi (3)</div>
            </div>
            
            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase;">
                        <tr>
                            <th class="py-3 px-4 rounded-start" style="font-weight: 600;">Mahasiswa</th>
                            <th class="py-3" style="font-weight: 600;">Tanggal / Minggu</th>
                            <th class="py-3" style="font-weight: 600;">Ringkasan Aktivitas</th>
                            <th class="py-3" style="font-weight: 600;">Jam Kerja</th>
                            <th class="py-3 px-4 rounded-end text-end" style="font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_logbook as $log): 
                            $badge_class = "bg-warning";
                            if ($log['status_validasi'] == 'Disetujui') $badge_class = "bg-success";
                            if ($log['status_validasi'] == 'Revisi') $badge_class = "bg-danger";
                        ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($log['nama_lengkap']) ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($log['nim']) ?></div>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark" style="font-size: 13px;"><?= date('l, d M Y', strtotime($log['tanggal'])) ?></div>
                                <span class="badge bg-light text-primary border border-primary-subtle mt-1">Minggu <?= ceil(date('j', strtotime($log['tanggal'])) / 7) ?></span>
                                <span class="badge <?= $badge_class ?> mt-1"><?= $log['status_validasi'] ?></span>
                            </td>
                            <td class="py-3">
                                <div class="text-dark" style="font-size: 14px; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($log['kegiatan']) ?></div>
                            </td>
                            <td class="py-3 text-muted"><?= date('H:i', strtotime($log['jam_mulai'])) ?> - <?= date('H:i', strtotime($log['jam_selesai'])) ?></td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#modalReview<?= $log['id'] ?>" title="Lihat Detail"><i class="bi bi-eye"></i> Review</button>
                                
                                <!-- Modal Review Laporan -->
                                <div class="modal fade text-start" id="modalReview<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0" style="border-radius: 16px;">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold" style="color: #1e293b;">Detail Logbook Mahasiswa</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-size: 18px; font-weight: 600;">
                                                        <?= substr($log['nama_lengkap'], 0, 2) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($log['nama_lengkap']) ?></h6>
                                                        <div class="text-muted" style="font-size: 13px;"><?= htmlspecialchars($log['nim']) ?> • <?= htmlspecialchars($log['nama_perusahaan']) ?></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-4">
                                                    <div class="col-md-4 mb-3 mb-md-0">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Tanggal Aktivitas</label>
                                                        <p class="text-dark mb-0 fw-medium"><?= date('d F Y', strtotime($log['tanggal'])) ?></p>
                                                    </div>
                                                    <div class="col-md-4 mb-3 mb-md-0">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Jam Magang</label>
                                                        <p class="text-dark mb-0 fw-medium"><?= date('H:i', strtotime($log['jam_mulai'])) ?> - <?= date('H:i', strtotime($log['jam_selesai'])) ?></p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Status Validasi Mentor</label>
                                                        <p class="text-dark mb-0 fw-medium"><?= htmlspecialchars($log['status_validasi']) ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Deskripsi Aktivitas</label>
                                                    <div class="p-3 bg-light" style="border-radius: 8px; color: #1e293b; font-size: 14px;">
                                                        <?= nl2br(htmlspecialchars($log['kegiatan'])) ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if($log['file_dokumentasi']): ?>
                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Lampiran Dokumentasi</label>
                                                    <div>
                                                        <a href="../uploads/<?= htmlspecialchars($log['file_dokumentasi']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf me-2"></i><?= htmlspecialchars($log['file_dokumentasi']) ?></a>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <form method="POST" action="">
                                                    <input type="hidden" name="logbook_id" value="<?= $log['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Catatan Dosen / Revisi</label>
                                                        <textarea name="catatan_dosen" class="form-control" rows="3" style="border-radius: 8px; resize: none;" placeholder="Beri catatan atau feedback untuk mahasiswa ini..."><?= htmlspecialchars($log['catatan_dosen'] ?? '') ?></textarea>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                                        <button type="submit" name="status_validasi" value="Revisi" class="btn btn-outline-danger fw-semibold" style="border-radius: 8px;">Minta Revisi</button>
                                                        <button type="submit" name="status_validasi" value="Disetujui" class="btn btn-success fw-semibold px-4" style="border-radius: 8px;">Setujui Logbook</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_logbook)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-5">Belum ada logbook dari mahasiswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
