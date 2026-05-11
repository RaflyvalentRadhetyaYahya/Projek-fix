<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['perusahaan_logged_in']) || $_SESSION['perusahaan_logged_in'] !== true) {
    header("Location: ../login.php?role=perusahaan");
    exit;
}

$page = "validasi_logbook";
$perusahaan_id = $_SESSION['perusahaan_id'];

// Proses Validasi (Setuju / Revisi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['logbook_id'])) {
        $id = $_POST['logbook_id'];
        $action = $_POST['action']; // 'Setujui' atau 'Revisi'
        $catatan = $_POST['catatan_mentor'] ?? '';
        
        $status = ($action === 'Setujui') ? 'Disetujui' : 'Revisi';
        
        $update = $pdo->prepare("UPDATE logbook SET status_validasi = ?, catatan_mentor = ? WHERE id = ?");
        $update->execute([$status, $catatan, $id]);
        
        header("Location: validasi_logbook.php");
        exit;
    }
}

// Ambil data logbook menunggu
$stmt = $pdo->prepare("
    SELECT l.id, l.tanggal, l.kegiatan, l.status_validasi, 
           m.nama_lengkap as nama_mahasiswa, m.nim
    FROM logbook l
    JOIN pengajuan_magang pm ON l.pengajuan_id = pm.id
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    WHERE pm.perusahaan_id = ? AND l.status_validasi = 'Menunggu'
    ORDER BY l.tanggal ASC
");
$stmt->execute([$perusahaan_id]);
$data_logbook = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Validasi Logbook - Perusahaan</title>
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
            <a href="mahasiswa_aktif.php"><i class="bi bi-people"></i> Mahasiswa Aktif</a>
            <a href="validasi_logbook.php" class="active"><i class="bi bi-journal-check"></i> Validasi Logbook</a>
            <a href="penilaian.php"><i class="bi bi-star"></i> Penilaian</a>
        </div>
    </div>

    <div class="p-4 w-100" style="background:#f8fafc; min-height:100vh;">
        <div class="mb-4">
            <h4 style="color:#064e3b; font-weight:700;">Validasi Logbook</h4>
            <p class="text-muted mb-0">Tinjau dan setujui jurnal kegiatan harian mahasiswa.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($data_logbook as $log): ?>
            <div class="col-md-6">
                <div class="card p-4" style="border-radius:12px; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($log['nama_mahasiswa']) ?></div>
                            <div style="font-size:13px; color:#64748b;"><?= htmlspecialchars($log['nim']) ?></div>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark border px-2 py-1">
                                <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($log['tanggal'])) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3 p-3 bg-light" style="border-radius:8px; font-size:14px; color:#475569;">
                        <strong>Kegiatan:</strong><br>
                        <?= nl2br(htmlspecialchars($log['kegiatan'])) ?>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="logbook_id" value="<?= $log['id'] ?>">
                        <div class="mb-3">
                            <label style="font-size:12px; font-weight:600; color:#64748b;">Catatan Evaluasi (Opsional)</label>
                            <input type="text" name="catatan_mentor" class="form-control form-control-sm" placeholder="Tulis catatan jika ada...">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="Revisi" class="btn btn-outline-danger btn-sm w-50" style="font-weight:600;">Minta Revisi</button>
                            <button type="submit" name="action" value="Setujui" class="btn btn-success btn-sm w-50" style="font-weight:600; background:#10b981; border:none;">Setujui Logbook</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($data_logbook)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-check-circle" style="font-size:48px; color:#10b981;"></i>
                <p class="mt-3">Tidak ada logbook yang perlu divalidasi saat ini.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
