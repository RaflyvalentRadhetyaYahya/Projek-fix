<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php?role=admin");
    exit;
}

$page = "periode";

// Ambil data periode
$stmt = $pdo->query("SELECT * FROM periode_magang ORDER BY tgl_mulai DESC");
$data_periode = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'tambah') {
            $nama = $_POST['nama_periode'];
            $mulai = $_POST['tgl_mulai'];
            $selesai = $_POST['tgl_selesai'];
            
            $insert = $pdo->prepare("INSERT INTO periode_magang (nama_periode, tgl_mulai, tgl_selesai, is_active) VALUES (?, ?, ?, 0)");
            $insert->execute([$nama, $mulai, $selesai]);
            header("Location: periode.php");
            exit;
        } elseif ($_POST['action'] === 'hapus') {
            $id = $_POST['id'];
            $del = $pdo->prepare("DELETE FROM periode_magang WHERE id = ?");
            $del->execute([$id]);
            header("Location: periode.php");
            exit;
        } elseif ($_POST['action'] === 'set_aktif') {
            $id = $_POST['id'];
            // Nonaktifkan semua dulu
            $pdo->query("UPDATE periode_magang SET is_active = 0");
            // Aktifkan yang dipilih
            $act = $pdo->prepare("UPDATE periode_magang SET is_active = 1 WHERE id = ?");
            $act->execute([$id]);
            header("Location: periode.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Periode Magang - Sistem Magang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">
<link rel="stylesheet" href="assets/css/mahasiswa.css">
</head>

<body>

<div class="d-flex">

<div class="sidebar d-flex flex-column">
    <div class="logo-container mb-4">
        <img src="assets/logo.png" class="logo-sidebar" alt="Logo">
    </div>

    <div class="sidebar-menu flex-grow-1">
        <a href="beranda.php"><i class="bi bi-grid"></i> Beranda</a>
        <a href="mahasiswa.php"><i class="bi bi-people"></i> Mahasiswa</a>
        <a href="dosen.php"><i class="bi bi-person-badge"></i> Dosen</a>
        <a href="pengajuan.php"><i class="bi bi-send"></i> Pengajuan</a>
        <a href="perusahaan.php"><i class="bi bi-building"></i> Perusahaan</a>
        <a href="prodi.php"><i class="bi bi-journal-bookmark"></i> Program Studi</a>
        <a href="periode.php" class="active"><i class="bi bi-calendar3"></i> Periode Magang</a>
    </div>
</div>

<div class="p-4 w-100">
<!-- HEADER -->
<div class="header-top">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari periode...">
    </div>
    <div class="profile-section">
        <div class="profile-wrapper">
            <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                <div class="profile-avatar"><i class="bi bi-person"></i></div>
                <div class="profile-text">
                    <div class="name">Admin Sistem</div>
                    <div class="email">admin@magang.ac.id</div>
                </div>
            </div>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="../logout.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 style="color:#2d6cdf;">Manajemen Periode Magang</h4>
        <p class="text-muted mb-0" style="font-size:14px;">Atur gelombang dan jadwal magang mahasiswa.</p>
    </div>
    <div>
        <button class="btn btn-primary" style="border-radius:8px; font-weight:600; font-size:14px;" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Periode
        </button>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h5 class="m-0" style="color: #1e293b;">Daftar Periode</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr>
                    <th>Nama Periode</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_periode as $p): ?>
                <tr>
                    <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($p['nama_periode']) ?></td>
                    <td><?= htmlspecialchars($p['tgl_mulai']) ?></td>
                    <td><?= htmlspecialchars($p['tgl_selesai']) ?></td>
                    <td>
                        <?php if($p['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if(!$p['is_active']): ?>
                        <form method="POST" action="" class="d-inline">
                            <input type="hidden" name="action" value="set_aktif">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success" title="Jadikan Aktif" style="border-radius:6px; font-size:12px;">Set Aktif</button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus periode ini?');">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="border:none; background:none;" title="Hapus">
                                <i class="bi bi-trash-fill text-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($data_periode)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data periode.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; padding:10px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="font-weight:600; color:#1e293b;">Tambah Periode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Nama Periode</label>
                        <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: Ganjil 2026/2027" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:8px; font-weight:600;">Simpan Periode</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
