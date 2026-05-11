<?php
session_start();
require_once '../config.php';

require_once '../auth/guard.php';
require_role('mahasiswa');

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data mahasiswa
$stmtM = $pdo->prepare("SELECT m.*, p.nama_prodi FROM mahasiswa m JOIN prodi p ON m.prodi_id = p.id WHERE m.id = ?");
$stmtM->execute([$mahasiswa_id]);
$mhs = $stmtM->fetch();

$page = "pengajuan";
$nama_mahasiswa = $mhs ? $mhs['nama_lengkap'] : "Ahmad Fauzi";
$nim = $mhs ? $mhs['nim'] : "10123001";
$prodi = $mhs ? $mhs['nama_prodi'] : "Teknik Informatika";
$no_hp = "0812-3456-7890"; // Mock
$email = "ahmad.fauzi@student.ac.id"; // Mock

// Ambil data mitra (perusahaan)
$stmtP = $pdo->query("SELECT * FROM perusahaan ORDER BY nama_perusahaan ASC");
$data_mitra = $stmtP->fetchAll();

$success = false;

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_perusahaan = $_POST['jenis_perusahaan'];
    $perusahaan_id = null;

    if ($jenis_perusahaan === 'tersedia') {
        $perusahaan_id = $_POST['perusahaan_id'];
    } else {
        // Insert perusahaan baru
        $nama_p = $_POST['nama_perusahaan'];
        $alamat_p = $_POST['alamat_perusahaan'];
        $kota_p = $_POST['kota_perusahaan'];
        
        $insP = $pdo->prepare("INSERT INTO perusahaan (nama_perusahaan, alamat, kota) VALUES (?, ?, ?)");
        $insP->execute([$nama_p, $alamat_p, $kota_p]);
        $perusahaan_id = $pdo->lastInsertId();
    }

    // Handle File Upload
    $file_proposal = '';
    if (isset($_FILES['berkas_proposal']) && $_FILES['berkas_proposal']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['berkas_proposal']['name'], PATHINFO_EXTENSION);
        $file_proposal = 'proposal_' . time() . '_' . $nim . '.' . $file_ext;
        move_uploaded_file($_FILES['berkas_proposal']['tmp_name'], $upload_dir . $file_proposal);
    }

    // Insert Pengajuan
    $judul = $_POST['judul_proposal'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $kode_pengajuan = 'PM-' . date('Ymd') . '-' . rand(100,999);

    $insPeng = $pdo->prepare("INSERT INTO pengajuan_magang (mahasiswa_id, perusahaan_id, kode_pengajuan, tgl_pengajuan, tgl_mulai, tgl_selesai, judul_proposal, file_proposal, status) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, 'Menunggu')");
    $insPeng->execute([$mahasiswa_id, $perusahaan_id, $kode_pengajuan, $tgl_mulai, $tgl_selesai, $judul, $file_proposal]);

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Proposal - Sistem Magang</title>

<!-- BOOTSTRAP & ICONS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- FONT -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/mahasiswa.css">
<link rel="stylesheet" href="assets/css/pengajuan.css">
</head>

<body>

<div class="d-flex">

    <!-- SIDEBAR -->
    <div class="sidebar d-flex flex-column">
        <div>
            <div class="logo-container">
                <img src="../admin/assets/logo.png" class="logo-sidebar" alt="Logo">
            </div>

            <a href="beranda.php" class="<?= ($page == 'beranda') ? 'active' : '' ?>">
                <i class="bi bi-grid me-2"></i> Beranda
            </a>

            <a href="pengajuan.php" class="<?= ($page == 'pengajuan') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Pengajuan Proposal
            </a>
        </div>
    </div>

    <!-- CONTENT AREA -->
    <div class="p-4 w-100" style="max-height: 100vh; overflow-y: auto;">

        <!-- HEADER TOP -->
        <div class="header-top">
            <h4 class="page-title">Formulir Pengajuan Magang</h4>
            
            <div class="profile-section">
                <div class="profile-wrapper">
                    <div class="profile-info" id="profileToggle" style="cursor:pointer;">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-text">
                            <div class="name"><?= $nama_mahasiswa ?></div>
                            <div class="role">Mahasiswa</div>
                        </div>
                        <i class="bi bi-chevron-down ms-2 text-muted" style="font-size: 12px;"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">Akun Saya</div>
                        <a href="../logout.php" class="text-danger" id="btnKeluar"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT (FORM) -->
        <form id="formPengajuan" method="POST" enctype="multipart/form-data">
            <div class="form-card">
                
                <!-- Data Mahasiswa (Read-Only) -->
                <div class="form-section-title">
                    <i class="bi bi-person-badge"></i> Data Mahasiswa
                </div>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Induk Mahasiswa (NIM)</label>
                        <input type="text" class="form-control" value="<?= $nim ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?= $nama_mahasiswa ?>" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control" value="<?= $prodi ?>" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" class="form-control" value="<?= $no_hp ?>" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= $email ?>" readonly>
                    </div>
                </div>

                <!-- Data Perusahaan -->
                <div class="form-section-title">
                    <i class="bi bi-building"></i> Data Perusahaan Tujuan
                </div>
                
                <div class="mb-4">
                    <label class="form-label d-block">Pilih Jenis Perusahaan <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_perusahaan" id="mitraTersedia" value="tersedia" checked>
                        <label class="form-check-label" for="mitraTersedia">Mitra Tersedia</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_perusahaan" id="perusahaanBaru" value="baru">
                        <label class="form-check-label" for="perusahaanBaru">Perusahaan Baru</label>
                    </div>
                </div>

                <!-- Opsi Mitra Tersedia -->
                <div class="row mb-3" id="opsiMitraTersedia">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Pilih Mitra Perusahaan <span class="text-danger">*</span></label>
                        <select class="form-select" name="perusahaan_id" id="selectMitra" required>
                            <option value="" disabled selected>-- Pilih Mitra Perusahaan --</option>
                            <?php foreach ($data_mitra as $mitra): ?>
                            <option value="<?= $mitra['id'] ?>" data-alamat="<?= htmlspecialchars($mitra['alamat']) ?>" data-kota="<?= htmlspecialchars($mitra['kota']) ?>"><?= htmlspecialchars($mitra['nama_perusahaan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-4" id="formPerusahaanDetails">
                    <div class="col-md-12 mb-3" id="fieldNamaPerusahaan" style="display:none;">
                        <label class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_perusahaan" class="form-control" id="inputNamaPerusahaan" placeholder="Contoh: PT Bangun Persada">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alamat Lengkap Perusahaan <span class="text-danger">*</span></label>
                        <textarea name="alamat_perusahaan" class="form-control" id="inputAlamat" rows="2" placeholder="Nama Jalan, Gedung, Nomor" required readonly></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota / Kabupaten <span class="text-danger">*</span></label>
                        <input type="text" name="kota_perusahaan" class="form-control" id="inputKota" placeholder="Kota/Kabupaten" required readonly>
                    </div>
                    <!-- Sembunyikan field yang tidak ada di db perusahaan untuk sementara -->
                    <div style="display:none;">
                        <input type="text" id="inputProvinsi">
                        <input type="text" id="inputKecamatan">
                        <input type="text" id="inputKodePos">
                    </div>
                </div>

                <!-- Detail Magang -->
                <div class="form-section-title">
                    <i class="bi bi-briefcase"></i> Detail Magang & Proposal
                </div>
                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Judul Proposal <span class="text-danger">*</span></label>
                        <input type="text" name="judul_proposal" class="form-control" placeholder="Masukkan judul proposal magang" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bidang Magang <span class="text-danger">*</span></label>
                        <input type="text" name="bidang_magang" class="form-control" placeholder="Contoh: Software Development" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Mulai Magang <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_mulai" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Selesai Magang <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_selesai" class="form-control" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan_tambahan" class="form-control" rows="3" placeholder="Informasi tambahan yang perlu diketahui admin atau dosen pembimbing..."></textarea>
                    </div>
                </div>

                <!-- Upload Berkas -->
                <div class="form-section-title">
                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Berkas Proposal
                </div>
                <div class="mb-4">
                    <div class="file-upload-wrapper">
                        <input type="file" name="berkas_proposal" id="berkasProposal" accept=".pdf" required>
                        <div class="file-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                        <div class="file-upload-text" id="fileName">Pilih file PDF atau seret ke sini</div>
                        <div class="file-upload-hint">Format yang diizinkan: PDF. Maksimal ukuran: 5MB</div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end border-top pt-4 mt-2">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send me-2"></i> Kirim Pengajuan
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <div class="modal-icon success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h5>Berhasil Terkirim!</h5>
        <p>Proposal magang Anda telah berhasil dikirim. Silakan tunggu proses review oleh Admin dan penentuan Dosen Pembimbing.</p>
        <div class="modal-actions">
            <a href="beranda.php" style="flex:1;text-decoration:none;">
                <button class="btn-success-modal" style="width:100%;">Kembali ke Beranda</button>
            </a>
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

<script src="assets/js/mahasiswa.js"></script>
<script src="assets/js/pengajuan.js"></script>

<?php if ($success): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('successModal').classList.add('show');
    });
</script>
<?php endif; ?>

</body>
</html>
