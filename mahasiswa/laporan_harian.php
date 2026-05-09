<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['mahasiswa_logged_in'])) {
    $_SESSION['mahasiswa_logged_in'] = true;
    $_SESSION['mahasiswa_id'] = 1; 
}

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data mahasiswa
$stmtM = $pdo->prepare("SELECT m.*, p.nama_prodi FROM mahasiswa m JOIN prodi p ON m.prodi_id = p.id WHERE m.id = ?");
$stmtM->execute([$mahasiswa_id]);
$mhs = $stmtM->fetch();

$page = "laporan_harian";
$nama_mahasiswa = $mhs ? $mhs['nama_lengkap'] : "Ahmad Fauzi";

// Cek apakah mahasiswa punya pengajuan magang yg disetujui
$stmtPengajuan = $pdo->prepare("SELECT id FROM pengajuan_magang WHERE mahasiswa_id = ? AND status = 'Disetujui' ORDER BY id DESC LIMIT 1");
$stmtPengajuan->execute([$mahasiswa_id]);
$pengajuan = $stmtPengajuan->fetch();
$pengajuan_id = $pengajuan ? $pengajuan['id'] : null;

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pengajuan_id) {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $tanggal = $_POST['tanggal'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $kegiatan = $_POST['kegiatan'];
        
        $file_dok = '';
        if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] == 0) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_ext = pathinfo($_FILES['dokumentasi']['name'], PATHINFO_EXTENSION);
            $file_dok = 'logbook_' . time() . '.' . $file_ext;
            move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $upload_dir . $file_dok);
        }

        $insLog = $pdo->prepare("INSERT INTO logbook (pengajuan_id, tanggal, jam_mulai, jam_selesai, kegiatan, file_dokumentasi, status_validasi) VALUES (?, ?, ?, ?, ?, ?, 'Menunggu')");
        $insLog->execute([$pengajuan_id, $tanggal, $jam_mulai, $jam_selesai, $kegiatan, $file_dok]);
        header("Location: laporan_harian.php");
        exit;
    }
}

// Ambil data logbook mahasiswa ini
$data_logbook = [];
if ($pengajuan_id) {
    $stmtLog = $pdo->prepare("SELECT * FROM logbook WHERE pengajuan_id = ? ORDER BY tanggal DESC");
    $stmtLog->execute([$pengajuan_id]);
    $data_logbook = $stmtLog->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - SIMMAG</title>
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
        
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold" style="color: #1e293b;">Daftar Logbook Harian</h4>
                <?php if($pengajuan_id): ?>
                <button class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: 8px; background: linear-gradient(135deg, #2563eb, #7c3aed); border: none;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Laporan
                </button>
                <?php else: ?>
                <span class="text-danger">Anda belum memiliki magang aktif.</span>
                <?php endif; ?>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead style="background-color: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase;">
                        <tr>
                            <th class="py-3 px-4 rounded-start" style="font-weight: 600;">Tanggal</th>
                            <th class="py-3" style="font-weight: 600;">Minggu Ke</th>
                            <th class="py-3" style="font-weight: 600;">Aktivitas</th>
                            <th class="py-3" style="font-weight: 600;">Jam Kerja</th>
                            <th class="py-3 text-center" style="font-weight: 600;">Status</th>
                            <th class="py-3 px-4 rounded-end text-end" style="font-weight: 600;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_logbook as $log): 
                            $minggu = ceil(date('j', strtotime($log['tanggal'])) / 7);
                            $badge_class = "bg-warning-subtle text-warning border border-warning-subtle";
                            $icon = "bi-clock";
                            if ($log['status_validasi'] == 'Disetujui') {
                                $badge_class = "bg-success-subtle text-success border border-success-subtle";
                                $icon = "bi-check-circle";
                            } else if ($log['status_validasi'] == 'Revisi') {
                                $badge_class = "bg-danger-subtle text-danger border border-danger-subtle";
                                $icon = "bi-x-circle";
                            }
                        ?>
                        <tr>
                            <td class="px-4 py-3"><div class="fw-semibold text-dark"><?= date('l, d M Y', strtotime($log['tanggal'])) ?></div></td>
                            <td class="py-3"><span class="badge bg-light text-primary border border-primary-subtle">Minggu <?= $minggu ?></span></td>
                            <td class="py-3 text-muted" style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($log['kegiatan']) ?></td>
                            <td class="py-3 text-muted"><?= date('H:i', strtotime($log['jam_mulai'])) ?> - <?= date('H:i', strtotime($log['jam_selesai'])) ?></td>
                            <td class="py-3 text-center">
                                <span class="badge <?= $badge_class ?> px-3 py-2 rounded-pill"><i class="bi <?= $icon ?> me-1"></i> <?= $log['status_validasi'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-primary me-2" data-bs-toggle="modal" data-bs-target="#modalView<?= $log['id'] ?>" title="Detail"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>

                        <!-- Modal Detail/View Laporan -->
                        <div class="modal fade text-start" id="modalView<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0" style="border-radius: 16px;">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold" style="color: #1e293b;">Detail Logbook Harian</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Tanggal Aktivitas</label>
                                            <p class="text-dark mb-0 fw-medium"><?= date('d F Y', strtotime($log['tanggal'])) ?></p>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Jam Mulai</label>
                                                <p class="text-dark mb-0 fw-medium"><?= date('H:i', strtotime($log['jam_mulai'])) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Jam Selesai</label>
                                                <p class="text-dark mb-0 fw-medium"><?= date('H:i', strtotime($log['jam_selesai'])) ?></p>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Deskripsi Aktivitas</label>
                                            <div class="p-3 bg-light" style="border-radius: 8px; color: #1e293b; font-size: 14px;">
                                                <?= nl2br(htmlspecialchars($log['kegiatan'])) ?>
                                            </div>
                                        </div>
                                        <?php if($log['file_dokumentasi']): ?>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Lampiran</label>
                                            <div>
                                                <a href="../uploads/<?= $log['file_dokumentasi'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf me-2"></i>Lihat Lampiran</a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if($log['catatan_dosen'] || $log['catatan_mentor']): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-danger" style="font-size: 13px;">Catatan Mentor/Dosen</label>
                                            <div class="p-3 bg-danger-subtle text-danger" style="border-radius: 8px; font-size: 14px;">
                                                <?= nl2br(htmlspecialchars($log['catatan_mentor'] . "\n" . $log['catatan_dosen'])) ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light w-100 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_logbook)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada catatan logbook.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Tambah Laporan -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 16px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: #1e293b;">Tambah Logbook Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Tanggal Aktivitas</label>
                            <input type="date" name="tanggal" class="form-control" style="border-radius: 8px;" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control" style="border-radius: 8px;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control" style="border-radius: 8px;" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Deskripsi Aktivitas</label>
                            <textarea name="kegiatan" class="form-control" rows="4" style="border-radius: 8px; resize: none;" placeholder="Ceritakan apa yang Anda kerjakan hari ini..." required></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="color: #475569; font-size: 13px;">Lampiran / Bukti Kerja (Opsional)</label>
                            <input class="form-control" name="dokumentasi" type="file" accept=".pdf,.png,.jpg,.jpeg" style="border-radius: 8px;">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 8px; background: linear-gradient(135deg, #2563eb, #7c3aed); border: none;">Simpan Laporan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit dan View lama dihapus karena di-generate dinamis di atas -->

    <!-- Bootstrap JS (optional but good to have) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/mahasiswa.js"></script>
</body>
</html>
