<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['kaprodi_logged_in'])) {
    $_SESSION['kaprodi_logged_in'] = true;
    $_SESSION['kaprodi_id'] = 1;
    $_SESSION['prodi_id'] = 1;
}

$kaprodi_id = $_SESSION['kaprodi_id'];
$prodi_id = $_SESSION['prodi_id'];

$stmt = $pdo->prepare("SELECT nama_lengkap, nip FROM kaprodi WHERE id = ?");
$stmt->execute([$kaprodi_id]);
$kaprodi = $stmt->fetch();

$page = "ploting_pembimbing";
$nama_kaprodi = $kaprodi ? $kaprodi['nama_lengkap'] : "Prof. Dr. Budi Susanto, M.Kom.";
$nip = $kaprodi ? $kaprodi['nip'] : "197001011995121001";

// Proses ploting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pengajuan_id']) && isset($_POST['dosen_id'])) {
        $upd = $pdo->prepare("UPDATE pengajuan_magang SET dosen_id = ? WHERE id = ?");
        $upd->execute([$_POST['dosen_id'], $_POST['pengajuan_id']]);
        header("Location: ploting_pembimbing.php");
        exit;
    }
}

// Ambil data pengajuan mahasiswa di prodinya yang sudah disetujui (siap magang) tapi belum diplot
$stmtPloting = $pdo->prepare("
    SELECT pm.id, pm.kode_pengajuan, m.nama_lengkap, m.nim, p.nama_perusahaan, 
           pm.bidang_magang, pm.dosen_id, d.nama_lengkap as nama_dosen
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN perusahaan p ON pm.perusahaan_id = p.id
    LEFT JOIN dosen d ON pm.dosen_id = d.id
    WHERE m.prodi_id = ? AND pm.status = 'Disetujui'
    ORDER BY pm.dosen_id ASC, pm.tgl_pengajuan DESC
");
$stmtPloting->execute([$prodi_id]);
$data_ploting = $stmtPloting->fetchAll();

// Ambil data dosen
$stmtDosen = $pdo->query("SELECT id, nama_lengkap, bidang_keahlian FROM dosen ORDER BY nama_lengkap ASC");
$data_dosen = $stmtDosen->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ploting Pembimbing - SIMMAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/kaprodi.css">
</head>
<body style="background:#f8fafc;">
    <div class="top-navbar">
        <div class="navbar-brand"><img src="../admin/assets/logo.png" alt="SIMMAG"></div>
        <div class="nav-center">
            <a href="dashboard.php">Beranda</a>
            <a href="monitoring_mahasiswa.php">Pemantauan Mahasiswa</a>
            <a href="pemantauan_dosen.php">Kinerja Dosen</a>
            <a href="ploting_pembimbing.php" class="active">Ploting Pembimbing</a>
        </div>
        <div class="nav-right">
            <div class="profile-section">
                <a href="../index.php" class="btn btn-outline-danger btn-sm">Keluar</a>
            </div>
        </div>
    </div>

    <div class="main-container p-4">
        <h4 class="fw-bold mb-4 text-purple">Ploting Dosen Pembimbing</h4>
        
        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Nama Mahasiswa</th>
                            <th>Perusahaan</th>
                            <th>Bidang Magang</th>
                            <th>Dosen Pembimbing</th>
                            <th class="px-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data_ploting as $row): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($row['nim']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['nama_perusahaan']) ?></td>
                            <td><?= htmlspecialchars($row['bidang_magang']) ?></td>
                            <td>
                                <?php if($row['dosen_id']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-person-check me-1"></i> <?= htmlspecialchars($row['nama_dosen']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Belum Diplot</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary" style="background:#7c3aed; border:none;" data-bs-toggle="modal" data-bs-target="#plotModal<?= $row['id'] ?>">
                                    <?= $row['dosen_id'] ? 'Ubah Plot' : 'Ploting Dosen' ?>
                                </button>
                                
                                <!-- Modal -->
                                <div class="modal fade text-start" id="plotModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border-radius:12px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="fw-bold text-purple">Ploting Dosen Pembimbing</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 p-3 bg-light rounded text-dark">
                                                    <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong><br>
                                                    <small><?= htmlspecialchars($row['nama_perusahaan']) ?> - <?= htmlspecialchars($row['bidang_magang']) ?></small>
                                                </div>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="pengajuan_id" value="<?= $row['id'] ?>">
                                                    <div class="mb-4">
                                                        <label class="form-label fw-semibold">Pilih Dosen Pembimbing</label>
                                                        <select class="form-select" name="dosen_id" required>
                                                            <option value="" disabled selected>-- Pilih Dosen --</option>
                                                            <?php foreach($data_dosen as $d): ?>
                                                            <option value="<?= $d['id'] ?>" <?= ($row['dosen_id'] == $d['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($d['nama_lengkap']) ?> (<?= htmlspecialchars($d['bidang_keahlian']) ?>)
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100" style="background:#7c3aed; border:none; border-radius:8px; font-weight:600;">Simpan Ploting</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data_ploting)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada pengajuan magang yang siap diplot.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
