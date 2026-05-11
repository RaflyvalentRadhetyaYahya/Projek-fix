<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php?role=admin");
    exit;
}

$page = "dosen";

// Ambil Data Dosen dari DB
$stmt = $pdo->query("
    SELECT d.nip as nidn, d.nama_lengkap as nama, 
           (SELECT COUNT(*) FROM pengajuan_magang pm WHERE pm.dosen_id = d.id AND pm.status = 'Disetujui') as bimbingan,
           'Aktif' as status 
    FROM dosen d
    ORDER BY d.nama_lengkap ASC
");
$data_dosen = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Dosen - Sistem Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/dosen.css">
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

        <a href="dosen.php" class="<?= ($page == 'dosen') ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Dosen
        </a>

        <a href="pengajuan.php">
            <i class="bi bi-send"></i> Pengajuan
        </a>

        <a href="perusahaan.php">
            <i class="bi bi-building"></i> Perusahaan
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
                <a href="../logout.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
            </div>
        </div>
    </div>
</div>

<!-- PAGE TITLE -->
<div class="mb-4">
    <h4 style="color:#2d6cdf;">Manajemen Dosen Pembimbing</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Kelola data dosen pembimbing magang (Teknik Informatika) di sini.</p>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <h5 class="m-0" style="color: #1e293b;">Daftar Dosen</h5>
        <div class="d-flex gap-3 align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel text-muted me-2"></i>
                <select class="form-select form-select-sm" style="border-radius: 8px; font-size: 13px; color: #475569; width: 160px; border-color: #e2e8f0;">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif Mengajar</option>
                    <option value="Cuti">Sedang Cuti</option>
                </select>
            </div>
            <a href="tambahDosen.php" class="btn-add">
                <i class="bi bi-plus-lg me-2"></i> Tambah Dosen
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr>
                    <th>NIDN</th>
                    <th>Nama Lengkap</th>
                    <th>Mahasiswa Bimbingan</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_dosen as $dsn): ?>
                <tr>
                    <td><?= $dsn['nidn'] ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 14px; margin-right: 12px; background: #eef2ff; color: #2563eb;">
                                <?= substr($dsn['nama'], 0, 1) ?>
                            </div>
                            <span style="font-weight: 600;"><?= $dsn['nama'] ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-bimbingan">
                            <i class="bi bi-people-fill me-1"></i> <?= $dsn['bimbingan'] ?> Mahasiswa
                        </span>
                    </td>
                    <td>
                        <span class="badge-status status-<?= $dsn['status'] ?>">
                            <?= $dsn['status'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="#" class="btn-action btn-edit" title="Edit Data">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="#" class="btn-action btn-delete" title="Hapus Data">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-4 d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size: 13px;">Menampilkan 1 hingga 5 dari 32 entri</span>
        <ul class="pagination pagination-sm m-0">
            <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
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

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box" style="width: 500px; text-align: left; padding: 25px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0" style="color: #1e293b;"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Dosen</h5>
            <button class="profile-modal-close" id="btnTutupEdit">&times;</button>
        </div>
        
        <form>
            <div class="mb-3">
                <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">NIDN</label>
                <input type="text" class="form-control" style="background:#f8fafc; font-size:14px;" value="0412345601" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Nama Lengkap (Bebeserta Gelar)</label>
                <input type="text" class="form-control" style="font-size:14px;" value="Dr. Budi Santoso, M.Kom.">
            </div>
            <div class="mb-4">
                <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Status</label>
                <select class="form-select" style="font-size:14px;">
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Cuti">Cuti</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-cancel" id="btnBatalEdit" style="font-size:14px; font-weight:600;">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanEdit" style="font-size:14px; font-weight:600; background: linear-gradient(135deg, #2563eb, #7c3aed); border:none; padding: 10px 20px; border-radius: 8px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h5>Hapus Data Dosen?</h5>
        <p>Apakah Anda yakin ingin menghapus data dosen ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatalHapus">Batal</button>
            <button class="btn-logout" id="btnKonfirmasiHapus" style="flex:1;">Ya, Hapus</button>
        </div>
    </div>
</div>

<script src="assets/js/admin.js"></script>
<script src="assets/js/dosen.js"></script>

</body>
</html>
