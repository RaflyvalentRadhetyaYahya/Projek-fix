<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['perusahaan_logged_in']) || $_SESSION['perusahaan_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page = "mahasiswa_aktif";
$perusahaan_id = $_SESSION['perusahaan_id'];

// Ambil Mahasiswa Aktif
$stmt = $pdo->prepare("
    SELECT m.nim, m.nama_lengkap, pr.nama_prodi, pm.tgl_mulai, pm.tgl_selesai,
           pm.bidang_magang
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN prodi pr ON m.prodi_id = pr.id
    WHERE pm.perusahaan_id = ? AND pm.status = 'Disetujui'
    ORDER BY m.nama_lengkap ASC
");
$stmt->execute([$perusahaan_id]);
$data_mahasiswa = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Mahasiswa Aktif - Perusahaan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../admin/assets/css/admin.css">
<style>
.sidebar { background: #064e3b; }
.sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: #fff; }
</style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column">
        <div class="logo-container mb-4 text-white text-center pt-3">
            <h5 class="fw-bold">MITRA MAGANG</h5>
        </div>
        <div class="sidebar-menu flex-grow-1">
            <a href="beranda.php"><i class="bi bi-grid"></i> Beranda</a>
            <a href="mahasiswa_aktif.php" class="active"><i class="bi bi-people"></i> Mahasiswa Aktif</a>
            <a href="validasi_logbook.php"><i class="bi bi-journal-check"></i> Validasi Logbook</a>
            <a href="penilaian.php"><i class="bi bi-star"></i> Penilaian</a>
        </div>
    </div>

    <div class="p-4 w-100" style="background:#f8fafc; min-height:100vh;">
        <div class="mb-4">
            <h4 style="color:#064e3b; font-weight:700;">Daftar Mahasiswa Aktif</h4>
            <p class="text-muted mb-0">Mahasiswa yang saat ini sedang melaksanakan magang di tempat Anda.</p>
        </div>

        <div class="card p-0" style="border-radius:12px; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#eef2ff;">
                        <tr>
                            <th class="py-3 px-4">NIM</th>
                            <th class="py-3">Nama Mahasiswa</th>
                            <th class="py-3">Program Studi</th>
                            <th class="py-3">Bidang Magang</th>
                            <th class="py-3">Periode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_mahasiswa as $mhs): ?>
                        <tr>
                            <td class="px-4 py-3" style="font-weight:600; color:#475569;"><?= $mhs['nim'] ?></td>
                            <td class="py-3" style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($mhs['nama_lengkap']) ?></td>
                            <td class="py-3"><?= htmlspecialchars($mhs['nama_prodi']) ?></td>
                            <td class="py-3"><?= htmlspecialchars($mhs['bidang_magang']) ?></td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border">
                                    <?= date('d M Y', strtotime($mhs['tgl_mulai'])) ?> - <?= date('d M Y', strtotime($mhs['tgl_selesai'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data_mahasiswa)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada mahasiswa magang aktif saat ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
