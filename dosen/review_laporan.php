<?php
session_start();
require_once '../config.php';

require_once '../auth/guard.php';
require_role('dosen');

$dosen_id = $_SESSION['dosen_id'];
$stmtD = $pdo->prepare("SELECT nama_lengkap, nip FROM dosen WHERE id = ?");
$stmtD->execute([$dosen_id]);
$dosen = $stmtD->fetch();

$page = "review_laporan";
$nama_dosen = $dosen ? $dosen['nama_lengkap'] : "Dr. Ir. Suyanto, M.T.";
$nip = $dosen ? $dosen['nip'] : "197508232005011002";

// Proses Penilaian Laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pengajuan_id'])) {
    $peng_id = $_POST['pengajuan_id'];
    $nilai = $_POST['nilai_dosen_pembimbing'];
    $catatan = $_POST['catatan_dosen'] ?? '';
    $action = $_POST['action']; // 'simpan' atau 'revisi'
    
    // Status revisi pada laporan akhir
    $status_laporan = ($action === 'revisi') ? 'Revisi' : 'Selesai';
    
    $updLaporan = $pdo->prepare("UPDATE laporan_akhir SET status_laporan = ? WHERE pengajuan_id = ?");
    $updLaporan->execute([$status_laporan, $peng_id]);

    if ($action === 'simpan') {
        $cek = $pdo->prepare("SELECT id FROM nilai WHERE pengajuan_id = ?");
        $cek->execute([$peng_id]);
        if ($cek->fetch()) {
            $upd = $pdo->prepare("UPDATE nilai SET nilai_dosen_pembimbing = ? WHERE pengajuan_id = ?");
            $upd->execute([$nilai, $peng_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO nilai (pengajuan_id, nilai_dosen_pembimbing) VALUES (?, ?)");
            $ins->execute([$peng_id, $nilai]);
        }
    }
    
    header("Location: review_laporan.php");
    exit;
}

// Ambil Laporan Akhir yang dikumpulkan
$stmtLaporan = $pdo->prepare("
    SELECT la.id, pm.id as pengajuan_id, m.nama_lengkap, m.nim, 
           la.tgl_upload, la.judul_laporan, la.file_laporan, la.status_laporan,
           n.nilai_dosen_pembimbing
    FROM laporan_akhir la
    JOIN pengajuan_magang pm ON la.pengajuan_id = pm.id
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    LEFT JOIN nilai n ON pm.id = n.pengajuan_id
    WHERE pm.dosen_id = ?
    ORDER BY la.tgl_upload DESC
");
$stmtLaporan->execute([$dosen_id]);
$data_laporan = $stmtLaporan->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Laporan Akhir - SIMMAG</title>
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
                        <a href="../logout.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        
        <div class="table-card">
            <h4 class="mb-3 fw-bold" style="color: #1e293b;">Penilaian Laporan Akhir Magang</h4>
            
            <div class="filter-pills">
                <div class="filter-pill active">Perlu Penilaian (3)</div>
                <div class="filter-pill">Sudah Dinilai (12)</div>
                <div class="filter-pill">Revisi Laporan (1)</div>
            </div>
            
            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase;">
                        <tr>
                            <th class="py-3 px-4 rounded-start" style="font-weight: 600;">Mahasiswa</th>
                            <th class="py-3" style="font-weight: 600;">Tanggal Submit</th>
                            <th class="py-3" style="font-weight: 600;">Judul Laporan</th>
                            <th class="py-3" style="font-weight: 600;">File</th>
                            <th class="py-3 px-4 rounded-end text-end" style="font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_laporan as $lap): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['nama_lengkap']) ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($lap['nim']) ?></div>
                            </td>
                            <td class="py-3">
                                <div class="text-dark" style="font-size: 14px;"><?= date('d M Y', strtotime($lap['tgl_upload'])) ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?= date('H:i', strtotime($lap['tgl_upload'])) ?> WIB</div>
                            </td>
                            <td class="py-3">
                                <div class="text-dark fw-medium" style="font-size: 14px;"><?= htmlspecialchars($lap['judul_laporan']) ?></div>
                                <?php if($lap['status_laporan'] === 'Revisi'): ?>
                                    <span class="badge bg-danger mt-1">Revisi</span>
                                <?php elseif($lap['status_laporan'] === 'Selesai'): ?>
                                    <span class="badge bg-success mt-1">Selesai (Nilai: <?= $lap['nilai_dosen_pembimbing'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-warning mt-1 text-dark">Menunggu Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3">
                                <a href="../uploads/<?= htmlspecialchars($lap['file_laporan']) ?>" target="_blank" class="btn btn-sm btn-light text-primary border"><i class="bi bi-file-earmark-pdf me-2"></i>Unduh</a>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalNilai<?= $lap['pengajuan_id'] ?>">Nilai Laporan</button>
                                
                                <!-- Modal Penilaian -->
                                <div class="modal fade text-start" id="modalNilai<?= $lap['pengajuan_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0" style="border-radius: 16px;">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold" style="color: #1e293b;">Beri Nilai Laporan Akhir</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; font-size: 18px; font-weight: 600;">
                                                        <?= substr($lap['nama_lengkap'], 0, 2) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($lap['nama_lengkap']) ?></h6>
                                                        <div class="text-muted" style="font-size: 13px;"><?= htmlspecialchars($lap['nim']) ?></div>
                                                    </div>
                                                </div>
                                                
                                                <form method="POST" action="">
                                                    <input type="hidden" name="pengajuan_id" value="<?= $lap['pengajuan_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Nilai Laporan (0-100)</label>
                                                        <input type="number" name="nilai_dosen_pembimbing" class="form-control form-control-lg fw-bold text-primary" style="border-radius: 8px; font-size: 24px;" min="0" max="100" value="<?= $lap['nilai_dosen_pembimbing'] ?? '' ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Catatan Revisi / Ulasan</label>
                                                        <textarea name="catatan_dosen" class="form-control" rows="4" style="border-radius: 8px; resize: none;" placeholder="Tuliskan ulasan mengenai kinerja dan kualitas laporan akhir mahasiswa..."></textarea>
                                                    </div>
                                                    
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" name="action" value="revisi" class="btn btn-outline-danger w-50 fw-semibold" style="border-radius: 8px;">Minta Revisi</button>
                                                        <button type="submit" name="action" value="simpan" class="btn btn-primary w-50 fw-semibold" style="border-radius: 8px;">Simpan Nilai</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_laporan)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-5">Belum ada laporan akhir yang dikumpulkan.</td></tr>
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
                <a href="../logout.php" style="flex:1;text-decoration:none;"><button class="btn-logout" style="width:100%;">Ya, Keluar</button></a>
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
