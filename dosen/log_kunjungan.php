<?php
session_start();
require_once '../config.php';

require_once '../auth/guard.php';
require_role('dosen');

$dosen_id = $_SESSION['dosen_id'];
$stmtD = $pdo->prepare("SELECT nama_lengkap, nip FROM dosen WHERE id = ?");
$stmtD->execute([$dosen_id]);
$dosen = $stmtD->fetch();

$page = "log_kunjungan";
$nama_dosen = $dosen ? $dosen['nama_lengkap'] : "Dr. Ir. Suyanto, M.T.";

// Buat tabel jika belum ada (mencegah error jika migration belum dijalankan)
$stmtTable = $pdo->prepare("SHOW TABLES LIKE 'kunjungan_dosen'");
$stmtTable->execute();
if (!$stmtTable->fetch()) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS kunjungan_dosen (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dosen_id INT NOT NULL,
        perusahaan_id INT NOT NULL,
        tanggal DATE NOT NULL,
        catatan TEXT,
        dokumentasi VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perusahaan_id = $_POST['perusahaan_id'];
    $tanggal = $_POST['tanggal'];
    $catatan = $_POST['catatan'];
    
    $ins = $pdo->prepare("INSERT INTO kunjungan_dosen (dosen_id, perusahaan_id, tanggal, catatan) VALUES (?, ?, ?, ?)");
    $ins->execute([$dosen_id, $perusahaan_id, $tanggal, $catatan]);
    header("Location: log_kunjungan.php");
    exit;
}

// Ambil riwayat kunjungan
$stmtLog = $pdo->prepare("
    SELECT k.*, p.nama_perusahaan 
    FROM kunjungan_dosen k
    JOIN perusahaan p ON k.perusahaan_id = p.id
    WHERE k.dosen_id = ?
    ORDER BY k.tanggal DESC
");
$stmtLog->execute([$dosen_id]);
$riwayat = $stmtLog->fetchAll();

// Ambil perusahaan tempat mahasiswa bimbingan magang
$stmtPerush = $pdo->prepare("
    SELECT DISTINCT p.id, p.nama_perusahaan 
    FROM pengajuan_magang pm 
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    WHERE pm.dosen_id = ? AND pm.status = 'Disetujui'
");
$stmtPerush->execute([$dosen_id]);
$perusahaan_bimbingan = $stmtPerush->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Kunjungan - SIMMAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dosen.css">
</head>
<body style="background:#f8fafc;">
    <div class="top-navbar">
        <div class="navbar-brand"><img src="../admin/assets/logo.png" alt="SIMMAG" style="height:35px;"></div>
        <div class="nav-center">
            <a href="dashboard.php">Beranda</a>
            <a href="mahasiswa_bimbingan.php">Mahasiswa Bimbingan</a>
            <a href="review_logbook.php">Review Logbook</a>
            <a href="review_laporan.php">Review Laporan</a>
            <a href="log_kunjungan.php" class="active">Log Kunjungan</a>
        </div>
        <div class="nav-right">
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Keluar</a>
        </div>
    </div>

    <div class="main-container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-primary">Log Kunjungan Dosen</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-lg me-1"></i> Catat Kunjungan
            </button>
        </div>
        
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Tanggal Kunjungan</th>
                            <th>Perusahaan / Tempat Magang</th>
                            <th>Catatan Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($riwayat as $r): ?>
                        <tr>
                            <td class="px-4 py-3 fw-semibold"><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($r['nama_perusahaan']) ?></td>
                            <td class="text-muted"><?= nl2br(htmlspecialchars($r['catatan'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($riwayat)): ?>
                        <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada riwayat kunjungan lapangan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Catat Kunjungan Lapangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tempat Kunjungan</label>
                            <select name="perusahaan_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Perusahaan --</option>
                                <?php foreach($perusahaan_bimbingan as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_perusahaan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan Evaluasi / Hasil Kunjungan</label>
                            <textarea name="catatan" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius:8px;">Simpan Catatan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
