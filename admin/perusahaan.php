<?php
session_start();
require_once '../config.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php?role=admin");
    exit;
}

$page = "perusahaan";

// Ambil data perusahaan dari DB
$stmt = $pdo->query("
    SELECT p.*, u.username as mentor_username 
    FROM perusahaan p 
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.nama_perusahaan ASC
");
$data_perusahaan = $stmt->fetchAll();

// Handle Tambah / Edit / Hapus via POST (Sederhana)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'tambah') {
            $nama = $_POST['nama_perusahaan'];
            $kota = $_POST['kota'];
            $kuota = $_POST['kuota_magang'];
            
            $insert = $pdo->prepare("INSERT INTO perusahaan (nama_perusahaan, alamat, kota, kuota_magang) VALUES (?, '', ?, ?)");
            $insert->execute([$nama, $kota, $kuota]);
            header("Location: perusahaan.php");
            exit;
        } elseif ($action === 'hapus') {
            $id = $_POST['id'];
            $del = $pdo->prepare("DELETE FROM perusahaan WHERE id = ?");
            $del->execute([$id]);
            header("Location: perusahaan.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Perusahaan - Sistem Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<!-- Kita pakai css mahasiswa agar tabel seragam -->
<link rel="stylesheet" href="assets/css/mahasiswa.css">
</head>

<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">

    <div class="logo-container mb-4">
        <img src="assets/logo.png" class="logo-sidebar" alt="Logo">
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

        <a href="pengajuan.php">
            <i class="bi bi-send"></i> Pengajuan
        </a>
        
        <a href="perusahaan.php" class="active">
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
        <input type="text" placeholder="Cari perusahaan...">
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
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 style="color:#2d6cdf;">Manajemen Perusahaan</h4>
        <p class="text-muted mb-0" style="font-size:14px;">Kelola daftar tempat magang atau mitra perusahaan.</p>
    </div>
    <div>
        <button class="btn btn-primary" style="border-radius:8px; font-weight:600; font-size:14px;" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Perusahaan
        </button>
    </div>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <h5 class="m-0" style="color: #1e293b;">Daftar Perusahaan Mitra</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Perusahaan</th>
                    <th>Kota</th>
                    <th>Kuota Magang</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_perusahaan as $p): ?>
                <tr>
                    <td style="font-weight: 600; color: #64748b; font-size: 13px;">PR-<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 14px; margin-right: 12px; background: #eef2ff; color: #2563eb;">
                                <i class="bi bi-building"></i>
                            </div>
                            <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($p['nama_perusahaan']) ?></div>
                        </div>
                    </td>
                    <td><span style="font-size: 13px; color: #64748b;"><i class="bi bi-geo-alt-fill me-1" style="color: #cbd5e1;"></i><?= htmlspecialchars($p['kota'] ?? '-') ?></span></td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size:12px; font-weight:600;">
                            <?= $p['kuota_magang'] ?> Orang
                        </span>
                    </td>
                    <td class="text-center">
                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus perusahaan ini?');">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="border:none; background:none;" title="Hapus">
                                <i class="bi bi-trash-fill text-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($data_perusahaan)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada data perusahaan.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>

<!-- TAMBAH MODAL -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; padding:10px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="font-weight:600; color:#1e293b;">Tambah Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Kota</label>
                        <input type="text" name="kota" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Kuota Magang</label>
                        <input type="number" name="kuota_magang" class="form-control" min="1" value="5" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:8px; font-weight:600;">Simpan Perusahaan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
