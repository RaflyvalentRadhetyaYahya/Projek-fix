<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['perusahaan_logged_in']) || $_SESSION['perusahaan_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page = "penilaian";
$perusahaan_id = $_SESSION['perusahaan_id'];

// Proses Penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pengajuan_id']) && isset($_POST['nilai_pembimbing_lapangan'])) {
        $peng_id = $_POST['pengajuan_id'];
        $nilai = $_POST['nilai_pembimbing_lapangan'];
        $catatan = $_POST['catatan_mentor'] ?? '';
        
        // Cek apakah sudah ada di tabel nilai
        $cek = $pdo->prepare("SELECT id FROM nilai WHERE pengajuan_id = ?");
        $cek->execute([$peng_id]);
        $ada = $cek->fetch();
        
        if ($ada) {
            $upd = $pdo->prepare("UPDATE nilai SET nilai_pembimbing_lapangan = ?, catatan_mentor = ? WHERE pengajuan_id = ?");
            $upd->execute([$nilai, $catatan, $peng_id]);
        } else {
            $ins = $pdo->prepare("INSERT INTO nilai (pengajuan_id, nilai_pembimbing_lapangan, catatan_mentor) VALUES (?, ?, ?)");
            $ins->execute([$peng_id, $nilai, $catatan]);
        }
        
        header("Location: penilaian.php");
        exit;
    }
}

// Ambil data mahasiswa untuk dinilai
$stmt = $pdo->prepare("
    SELECT pm.id as pengajuan_id, m.nama_lengkap, m.nim, pm.tgl_selesai,
           n.nilai_pembimbing_lapangan
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    LEFT JOIN nilai n ON pm.id = n.pengajuan_id
    WHERE pm.perusahaan_id = ? AND pm.status = 'Disetujui'
    ORDER BY pm.tgl_selesai ASC
");
$stmt->execute([$perusahaan_id]);
$data_penilaian = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Penilaian Kinerja - Perusahaan</title>
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
            <a href="validasi_logbook.php"><i class="bi bi-journal-check"></i> Validasi Logbook</a>
            <a href="penilaian.php" class="active"><i class="bi bi-star"></i> Penilaian</a>
        </div>
    </div>

    <div class="p-4 w-100" style="background:#f8fafc; min-height:100vh;">
        <div class="mb-4">
            <h4 style="color:#064e3b; font-weight:700;">Penilaian Kinerja Mahasiswa</h4>
            <p class="text-muted mb-0">Berikan nilai evaluasi kinerja mahasiswa selama magang.</p>
        </div>

        <div class="card p-0" style="border-radius:12px; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#eef2ff;">
                        <tr>
                            <th class="py-3 px-4">Nama Mahasiswa</th>
                            <th class="py-3">Tgl Selesai Magang</th>
                            <th class="py-3">Status Penilaian</th>
                            <th class="py-3 text-end px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_penilaian as $pnl): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($pnl['nama_lengkap']) ?></div>
                                <div style="font-size:12px; color:#64748b;"><?= htmlspecialchars($pnl['nim']) ?></div>
                            </td>
                            <td class="py-3"><?= date('d M Y', strtotime($pnl['tgl_selesai'])) ?></td>
                            <td class="py-3">
                                <?php if($pnl['nilai_pembimbing_lapangan']): ?>
                                    <span class="badge bg-success">Sudah Dinilai (<?= $pnl['nilai_pembimbing_lapangan'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-end px-4">
                                <button type="button" class="btn btn-sm btn-primary" style="background:#10b981; border:none; font-weight:600;" data-bs-toggle="modal" data-bs-target="#modalNilai<?= $pnl['pengajuan_id'] ?>">
                                    Beri Nilai
                                </button>
                                
                                <!-- MODAL NILAI -->
                                <div class="modal fade text-start" id="modalNilai<?= $pnl['pengajuan_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border-radius:16px; border:none; padding:10px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title" style="font-weight:600; color:#1e293b;">Input Nilai Lapangan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="pengajuan_id" value="<?= $pnl['pengajuan_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" style="font-size:13px; font-weight:600;">Nilai Kinerja (0-100)</label>
                                                        <input type="number" name="nilai_pembimbing_lapangan" class="form-control" min="0" max="100" value="<?= $pnl['nilai_pembimbing_lapangan'] ?? '' ?>" required>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label" style="font-size:13px; font-weight:600;">Catatan Evaluasi / Pesan</label>
                                                        <textarea name="catatan_mentor" class="form-control" rows="3" placeholder="Contoh: Kinerja sangat baik..."></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success w-100" style="background:#10b981; border:none; border-radius:8px; font-weight:600;">Simpan Nilai</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data_penilaian)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-5">Belum ada data mahasiswa untuk dinilai.</td></tr>
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
