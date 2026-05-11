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

// Handle Tambah / Edit / Hapus via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'tambah') {
            $nama = $_POST['nama_perusahaan'];
            $alamat = $_POST['alamat'] ?? '';
            $kota = $_POST['kota'];
            $provinsi = $_POST['provinsi'] ?? '';
            $kontak = $_POST['kontak_person'] ?? '';
            $telp = $_POST['no_telp'] ?? '';
            $email = $_POST['email'] ?? '';
            $kuota = $_POST['kuota_magang'];
            
            $insert = $pdo->prepare("INSERT INTO perusahaan (nama_perusahaan, alamat, kota, provinsi, kontak_person, no_telp, email, kuota_magang) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$nama, $alamat, $kota, $provinsi, $kontak, $telp, $email, $kuota]);
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
<link rel="stylesheet" href="assets/css/mahasiswa.css">
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

        <a href="pengajuan.php">
            <i class="bi bi-send"></i> Pengajuan
        </a>

        <a href="perusahaan.php" class="<?= ($page == 'perusahaan') ? 'active' : '' ?>">
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
    <h4 style="color:#2d6cdf;">Manajemen Perusahaan</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Kelola daftar tempat magang atau mitra perusahaan.</p>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <h5 class="m-0" style="color: #1e293b;">Daftar Perusahaan Mitra</h5>
        <div class="d-flex gap-3 align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-funnel text-muted me-2"></i>
                <select class="form-select form-select-sm" style="border-radius: 8px; font-size: 13px; color: #475569; width: 160px; border-color: #e2e8f0;">
                    <option value="">Semua Kota</option>
                    <?php
                    $kota_list = array_unique(array_filter(array_column($data_perusahaan, 'kota')));
                    sort($kota_list);
                    foreach ($kota_list as $k): ?>
                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="#" class="btn-add" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-lg me-2"></i> Tambah Perusahaan
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Perusahaan</th>
                    <th>Kota</th>
                    <th>Kontak</th>
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
                            <div>
                                <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($p['nama_perusahaan']) ?></div>
                                <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($p['email'] ?? '-') ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-size: 13px; color: #64748b;"><i class="bi bi-geo-alt-fill me-1" style="color: #cbd5e1;"></i><?= htmlspecialchars($p['kota'] ?? '-') ?></span></td>
                    <td><span style="font-size: 13px; color: #475569;"><?= htmlspecialchars($p['kontak_person'] ?? '-') ?></span></td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size:12px; font-weight:600;">
                            <?= $p['kuota_magang'] ?> Orang
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="#" class="btn-action btn-edit" title="Edit Data">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="#" class="btn-action btn-delete" title="Hapus Data" data-id="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama_perusahaan']) ?>">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($data_perusahaan)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data perusahaan.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-4 d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size: 13px;">Menampilkan <?= count($data_perusahaan) ?> entri</span>
        <ul class="pagination pagination-sm m-0">
            <li class="page-item disabled"><a class="page-link" href="#">Sebelumnya</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">Selanjutnya</a></li>
        </ul>
    </nav>
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Kota</label>
                            <input type="text" name="kota" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Kontak Person</label>
                            <input type="text" name="kontak_person" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">No. Telepon</label>
                            <input type="text" name="no_telp" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:#475569;">Kuota Magang</label>
                            <input type="number" name="kuota_magang" class="form-control" min="1" value="5" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:8px; font-weight:600;">Simpan Perusahaan</button>
                </form>
            </div>
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

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h5>Hapus Data Perusahaan?</h5>
        <p>Apakah Anda yakin ingin menghapus data perusahaan ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="btnBatalHapus">Batal</button>
            <form method="POST" action="" id="formHapus" style="flex:1;">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="id" id="hapusId" value="">
                <button type="submit" class="btn-logout" style="width:100%;">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin.js"></script>
<script>
// Delete button handler
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        document.getElementById('hapusId').value = id;
        document.getElementById('deleteModal').classList.add('active');
    });
});

document.getElementById('btnBatalHapus').addEventListener('click', function() {
    document.getElementById('deleteModal').classList.remove('active');
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
</script>

</body>
</html>
