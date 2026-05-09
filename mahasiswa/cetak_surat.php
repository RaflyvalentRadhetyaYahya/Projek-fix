<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['mahasiswa_logged_in'])) {
    // Mock login fallback
    $_SESSION['mahasiswa_logged_in'] = true;
    $_SESSION['mahasiswa_id'] = 1; // 1 = Ahmad Fauzi di mock DB
}

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data pengajuan
$stmt = $pdo->prepare("
    SELECT m.nama_lengkap, m.nim, p.nama_prodi, pm.kode_pengajuan, 
           pm.tgl_pengajuan, pm.status, perush.nama_perusahaan, perush.alamat
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN prodi p ON m.prodi_id = p.id
    JOIN perusahaan perush ON pm.perusahaan_id = perush.id
    WHERE pm.mahasiswa_id = ? AND pm.status = 'Disetujui'
    ORDER BY pm.tgl_pengajuan DESC LIMIT 1
");
$stmt->execute([$mahasiswa_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Anda belum memiliki pengajuan magang yang disetujui untuk dicetak.");
}

// Set header untuk print view
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar Magang - <?= $data['nim'] ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 2cm; }
        .kop-surat { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .kop-surat p { margin: 2px 0; font-size: 11pt; }
        .nomor-surat { margin-bottom: 20px; }
        .isi-surat { text-align: justify; }
        .data-mahasiswa { margin-left: 20px; margin-bottom: 20px; }
        .data-mahasiswa td { padding: 3px 10px; }
        .ttd { width: 300px; float: right; margin-top: 50px; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 10px; background: #f0f0f0; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-weight: bold;">Cetak Surat</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    <div class="kop-surat">
        <h1>Universitas Teknologi Nusantara</h1>
        <p>Fakultas Ilmu Komputer dan Teknologi Informasi</p>
        <p>Jl. Pendidikan No. 123, Kota Cendekia, Telp. (021) 555-1234</p>
    </div>

    <div class="nomor-surat">
        <table>
            <tr><td width="80">Nomor</td><td>: <?= $data['kode_pengajuan'] ?>/UNIV-TI/<?= date('Y') ?></td></tr>
            <tr><td>Lampiran</td><td>: 1 (satu) Berkas</td></tr>
            <tr><td>Perihal</td><td>: <b>Permohonan Izin Kerja Praktik / Magang</b></td></tr>
        </table>
    </div>

    <div class="tujuan-surat" style="margin-bottom: 20px;">
        <p>Kepada Yth.,<br>
        <b>Pimpinan / HRD <?= htmlspecialchars($data['nama_perusahaan']) ?></b><br>
        <?= htmlspecialchars($data['alamat']) ?></p>
    </div>

    <div class="isi-surat">
        <p>Dengan hormat,</p>
        <p>Sehubungan dengan kurikulum program Sarjana (S1) pada Fakultas Ilmu Komputer dan Teknologi Informasi Universitas Teknologi Nusantara, mahasiswa diwajibkan untuk melaksanakan mata kuliah Kerja Praktik / Magang. Oleh karena itu, kami mohon kesediaan Bapak/Ibu untuk memberikan izin magang kepada mahasiswa kami berikut ini:</p>
        
        <table class="data-mahasiswa">
            <tr><td width="150">Nama Lengkap</td><td>: <b><?= htmlspecialchars($data['nama_lengkap']) ?></b></td></tr>
            <tr><td>Nomor Induk Mahasiswa</td><td>: <?= htmlspecialchars($data['nim']) ?></td></tr>
            <tr><td>Program Studi</td><td>: <?= htmlspecialchars($data['nama_prodi']) ?></td></tr>
        </table>

        <p>Adapun waktu pelaksanaan magang direncanakan selama 3 (tiga) bulan, menyesuaikan dengan kebijakan dan ketersediaan waktu di instansi/perusahaan yang Bapak/Ibu pimpin.</p>
        
        <p>Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerjasama Bapak/Ibu yang baik, kami ucapkan terima kasih.</p>
    </div>

    <div class="ttd">
        <p>Kota Cendekia, <?= date('d F Y') ?></p>
        <p>Dekan Fakultas,</p>
        <br><br><br>
        <p><b><u>Prof. Dr. Antonius Wijaya, M.Kom.</u></b><br>NIP. 19650212 199003 1 002</p>
    </div>

    <script>
        // Auto print prompt
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
