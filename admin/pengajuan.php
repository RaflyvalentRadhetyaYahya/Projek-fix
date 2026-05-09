<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page = "pengajuan";

// Ambil Data Pengajuan dari DB
$stmt = $pdo->query("
    SELECT pm.kode_pengajuan as id, DATE_FORMAT(pm.tgl_pengajuan, '%d %b %Y') as tanggal,
           m.nama_lengkap as nama, m.nim as nim, pr.nama_prodi as prodi,
           m.no_telp as no_hp, m.email as email, pm.judul_proposal,
           p.nama_perusahaan as perusahaan, p.alamat as alamat_perusahaan,
           p.provinsi as provinsi, p.kota as kota, '-' as kecamatan, '-' as kode_pos,
           pm.bidang_magang, DATE_FORMAT(pm.tgl_mulai, '%d %b %Y') as tgl_mulai,
           DATE_FORMAT(pm.tgl_selesai, '%d %b %Y') as tgl_selesai,
           COALESCE(d.nama_lengkap, 'Belum Diplot') as dosen_pembimbing,
           pm.catatan_mahasiswa as catatan, pm.berkas_proposal as berkas,
           pm.status
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN prodi pr ON m.prodi_id = pr.id
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    LEFT JOIN dosen d ON pm.dosen_id = d.id
    ORDER BY pm.tgl_pengajuan DESC
");
$data_pengajuan = $stmt->fetchAll();

// Ambil Data Dosen untuk dropdown plotting
$stmtDosen = $pdo->query("SELECT id, nama_lengkap as nama, bidang_keahlian as bidang FROM dosen ORDER BY nama_lengkap ASC");
$data_dosen = $stmtDosen->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pengajuan - Sistem Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/pengajuan.css">
</head>

<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">

    <div class="logo-container mb-4">
        <img src="assets/logo.png" class="logo-sidebar">
    </div>

    <div class="sidebar-menu flex-grow-1">
        <a href="beranda.php">
            <i class="bi bi-grid"></i> Beranda
        </a>

        <a href="mahasiswa.php">
            <i class="bi bi-people"></i> Mahasiswa
        </a>

        <a href="dosen.php">
            <i class="bi bi-person-badge"></i> Dosen
        </a>

        <a href="pengajuan.php" class="<?= ($page == 'pengajuan') ? 'active' : '' ?>">
            <i class="bi bi-send"></i> Pengajuan
        </a>
    </div>

</div>

<!-- CONTENT AREA -->
<div class="p-4 w-100">

<!-- HEADER -->
<div class="header-top">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari mahasiswa, dosen, atau pengajuan...">
    </div>
    
    <div class="profile-section">
        <div class="profile-wrapper">
            <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                <div class="profile-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div class="profile-text">
                    <div class="name">Admin Sistem</div>
                    <div class="email">admin@magang.ac.id</div>
                </div>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">Akun Saya</div>
                <a href="#" id="btnProfil"><i class="bi bi-person me-2"></i>Profil</a>
                <a href="login.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
            </div>
        </div>
    </div>
</div>

<!-- PAGE TITLE -->
<div class="mb-4">
    <h4 style="color:#2d6cdf;">Manajemen Pengajuan</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Kelola pengajuan tempat magang, pembimbing, dan laporan dari mahasiswa.</p>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <h5 class="m-0" style="color: #1e293b;">Daftar Pengajuan Terbaru</h5>
        <div class="d-flex gap-3 align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel text-muted me-2"></i>
                <select class="form-select form-select-sm" style="border-radius: 8px; font-size: 13px; color: #475569; width: 150px; border-color: #e2e8f0;">
                    <option value="">Semua Status</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Nama Mahasiswa</th>
                    <th>Judul Proposal</th>
                    <th>Perusahaan</th>
                    <th>Berkas</th>
                    <th>Status</th>
                    <th class="text-center">Proses</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_pengajuan as $idx => $pgj): ?>
                <tr>
                    <td style="font-weight: 600; color: #64748b; font-size: 13px;"><?= $pgj['id'] ?></td>
                    <td><span style="font-size: 13px; color: #475569;"><i class="bi bi-calendar3 me-1"></i><?= $pgj['tanggal'] ?></span></td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?= $pgj['nama'] ?></div>
                        <div style="font-size: 12px; color: #94a3b8;"><?= $pgj['nim'] ?></div>
                    </td>
                    <td><span style="font-weight: 500; font-size: 13px; max-width: 200px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= $pgj['judul_proposal'] ?>"><?= $pgj['judul_proposal'] ?></span></td>
                    <td><span style="font-size: 13px; color: #475569;"><?= $pgj['perusahaan'] ?></span></td>
                    <td>
                        <a href="#" style="font-size: 13px; text-decoration: none; color: #2563eb; font-weight: 600;">
                            <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i><?= $pgj['berkas'] ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge-status status-<?= $pgj['status'] ?>">
                            <?= $pgj['status'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="#" class="btn-action btn-detail" title="Lihat Detail" data-idx="<?= $idx ?>">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($pgj['status'] === 'Menunggu'): ?>
                            <a href="#" class="btn-action btn-approve" title="Setujui" data-idx="<?= $idx ?>">
                                <i class="bi bi-check-lg"></i>
                            </a>
                            <a href="#" class="btn-action btn-reject" title="Tolak" data-idx="<?= $idx ?>">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-4 d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size: 13px;">Menampilkan 1 hingga 5 dari 248 entri</span>
        <ul class="pagination pagination-sm m-0">
            <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Selanjutnya</a></li>
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
            <a href="login.php" style="flex:1;text-decoration:none;"><button class="btn-logout" style="width:100%;">Ya, Keluar</button></a>
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
        <div class="profile-modal-name">Admin Sistem</div>
        <div class="profile-modal-role">Admin</div>

        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-envelope"></i></div>
            <div>
                <div class="profile-detail-label">Surel</div>
                <div class="profile-detail-value">admin@magang.ac.id</div>
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
            <div class="profile-detail-icon"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="profile-detail-label">Peran</div>
                <div class="profile-detail-value">Admin Utama</div>
            </div>
        </div>
        <div class="profile-detail">
            <div class="profile-detail-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="profile-detail-label">Masuk Terakhir</div>
                <div class="profile-detail-value">20 April 2026, 21:00</div>
            </div>
        </div>
    </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box" style="width: 640px; max-height: 90vh; overflow-y: auto; text-align: left; padding: 28px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0" style="color: #1e293b;"><i class="bi bi-file-text me-2 text-primary"></i>Detail Pengajuan Proposal</h5>
            <button class="profile-modal-close" id="btnTutupDetail">&times;</button>
        </div>

        <!-- ID & Tanggal -->
        <div style="display:flex; gap:16px; margin-bottom:16px;">
            <div style="flex:1; background:#f8fafc; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase;">ID Pengajuan</div>
                <div id="det-id" style="font-size:14px; font-weight:600; color:#1e293b;">-</div>
            </div>
            <div style="flex:1; background:#f8fafc; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase;">Tanggal Pengajuan</div>
                <div id="det-tanggal" style="font-size:14px; font-weight:600; color:#1e293b;">-</div>
            </div>
            <div style="flex:1; background:#f8fafc; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase;">Status</div>
                <div id="det-status" style="font-size:14px; font-weight:600;">-</div>
            </div>
        </div>

        <!-- Judul Proposal -->
        <div style="background:linear-gradient(135deg,#eef2ff,#f0f4ff); padding:14px 16px; border-radius:8px; margin-bottom:16px; border-left:4px solid #2563eb;">
            <div style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; margin-bottom:4px;">Judul Proposal</div>
            <div id="det-judul" style="font-size:14px; font-weight:600; color:#1e293b;">-</div>
        </div>

        <!-- Data Mahasiswa -->
        <div style="background:#f8fafc; padding:16px; border-radius:8px; margin-bottom:16px;">
            <div class="detail-section-title"><i class="bi bi-person me-1"></i>Data Mahasiswa</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 24px;">
                <div class="detail-row"><span class="detail-label">NIM</span><span class="detail-value" id="det-nim">-</span></div>
                <div class="detail-row"><span class="detail-label">Nama Lengkap</span><span class="detail-value" id="det-nama">-</span></div>
                <div class="detail-row"><span class="detail-label">Program Studi</span><span class="detail-value" id="det-prodi">-</span></div>
                <div class="detail-row"><span class="detail-label">No. HP</span><span class="detail-value" id="det-hp">-</span></div>
                <div class="detail-row" style="grid-column:1/-1;"><span class="detail-label">Email</span><span class="detail-value" id="det-email">-</span></div>
            </div>
        </div>

        <!-- Data Perusahaan -->
        <div style="background:#f8fafc; padding:16px; border-radius:8px; margin-bottom:16px;">
            <div class="detail-section-title"><i class="bi bi-building me-1"></i>Data Perusahaan / Tempat Magang</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 24px;">
                <div class="detail-row" style="grid-column:1/-1;"><span class="detail-label">Nama Perusahaan</span><span class="detail-value" id="det-perusahaan">-</span></div>
                <div class="detail-row" style="grid-column:1/-1;"><span class="detail-label">Alamat</span><span class="detail-value" id="det-alamat">-</span></div>
                <div class="detail-row"><span class="detail-label">Provinsi</span><span class="detail-value" id="det-provinsi">-</span></div>
                <div class="detail-row"><span class="detail-label">Kota / Kabupaten</span><span class="detail-value" id="det-kota">-</span></div>
                <div class="detail-row"><span class="detail-label">Kecamatan</span><span class="detail-value" id="det-kecamatan">-</span></div>
                <div class="detail-row"><span class="detail-label">Kode Pos</span><span class="detail-value" id="det-kodepos">-</span></div>
            </div>
        </div>

        <!-- Detail Magang -->
        <div style="background:#f8fafc; padding:16px; border-radius:8px; margin-bottom:16px;">
            <div class="detail-section-title"><i class="bi bi-briefcase me-1"></i>Detail Magang</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 24px;">
                <div class="detail-row"><span class="detail-label">Bidang Magang</span><span class="detail-value" id="det-bidang">-</span></div>
                <div class="detail-row" style="grid-column:1/-1;"><span class="detail-label">Dosen Pembimbing</span><span class="detail-value" id="det-dosen-text">-</span></div>
                <!-- Penentuan Dosen Pembimbing (admin) -->
                <div class="detail-row" id="det-dosen-assign" style="grid-column:1/-1; display:none;">
                    <span class="detail-label">Tentukan Dosen</span>
                    <div style="flex:1;">
                        <select id="det-dosen-select" class="form-select form-select-sm" style="font-size:13px; border-radius:8px; border-color:#e2e8f0; color:#1e293b; font-weight:500;">
                            <option value="" disabled>-- Pilih Dosen Pembimbing --</option>
                            <?php foreach ($data_dosen as $dsn): ?>
                            <option value="<?= $dsn['nama'] ?>"><?= $dsn['nama'] ?> — <?= $dsn['bidang'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size:11px; color:#94a3b8; margin-top:4px;"><i class="bi bi-info-circle me-1"></i>Pilih dosen yang akan membimbing mahasiswa ini</div>
                    </div>
                </div>
                <div class="detail-row"><span class="detail-label">Tanggal Mulai</span><span class="detail-value" id="det-mulai">-</span></div>
                <div class="detail-row"><span class="detail-label">Tanggal Selesai</span><span class="detail-value" id="det-selesai">-</span></div>
            </div>
        </div>

        <!-- Catatan -->
        <div style="margin-bottom:16px;">
            <div style="font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Catatan Tambahan</div>
            <div id="det-catatan" style="font-size:13px; color:#475569; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; min-height:40px;">-</div>
        </div>

        <!-- Berkas -->
        <div style="margin-bottom:20px;">
            <div style="font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Berkas Lampiran</div>
            <a href="#" class="btn btn-outline-primary btn-sm" id="det-berkas-link" style="font-size:13px; font-weight:600;">
                <i class="bi bi-download me-1"></i> <span id="det-berkas">-</span>
            </a>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-2 pt-3" id="det-actions" style="border-top:1px solid #e2e8f0;">
            <button type="button" class="btn btn-cancel" id="btnBatalDetail" style="font-size:14px; font-weight:600;">Tutup</button>
            <button type="button" class="btn" id="btnRejectDetail" style="font-size:14px; font-weight:600; background:#dc3545; color:#fff; border:none; padding:10px 20px; border-radius:8px;"><i class="bi bi-x-circle me-1"></i>Tolak</button>
            <button type="button" class="btn" id="btnApproveDetail" style="font-size:14px; font-weight:600; background:linear-gradient(135deg,#198754,#20c997); color:#fff; border:none; padding:10px 20px; border-radius:8px;"><i class="bi bi-check-circle me-1"></i>Setujui</button>
        </div>
    </div>
</div>

<!-- APPROVE CONFIRMATION MODAL -->
<div class="modal-overlay" id="approveModal">
    <div class="modal-box">
        <div class="modal-icon" style="background:rgba(25,135,84,0.1); color:#198754;">
            <i class="bi bi-check-circle"></i>
        </div>
        <h5>Setujui Pengajuan?</h5>
        <p>Apakah Anda yakin ingin menyetujui pengajuan ini?</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatalApprove">Batal</button>
            <button style="flex:1; padding:10px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; border:none; background:#198754; color:#fff;" id="btnConfirmApprove">Ya, Setujui</button>
        </div>
    </div>
</div>

<!-- REJECT CONFIRMATION MODAL -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="bi bi-x-circle"></i>
        </div>
        <h5>Tolak Pengajuan?</h5>
        <p>Apakah Anda yakin ingin menolak pengajuan ini?</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatalReject">Batal</button>
            <button class="btn-logout" id="btnConfirmReject">Ya, Tolak</button>
        </div>
    </div>
</div>

<script>
// Data pengajuan dari PHP ke JS
const dataPengajuan = <?= json_encode($data_pengajuan) ?>;
</script>
<script src="assets/js/admin.js"></script>
<script src="assets/js/pengajuan.js"></script>

</body>
</html>
