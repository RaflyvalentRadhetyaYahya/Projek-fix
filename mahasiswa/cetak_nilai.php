<?php
session_start();
require_once '../config.php';

require_once '../auth/guard.php';
require_role('mahasiswa');

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// Ambil data nilai
$stmt = $pdo->prepare("
    SELECT m.nama_lengkap, m.nim, p.nama_prodi, pm.kode_pengajuan, 
           pm.tgl_mulai, pm.tgl_selesai, perush.nama_perusahaan,
           n.nilai_pembimbing_lapangan, n.nilai_dosen_pembimbing, n.nilai_akhir, n.huruf_mutu
    FROM pengajuan_magang pm
    JOIN mahasiswa m ON pm.mahasiswa_id = m.id
    JOIN prodi p ON m.prodi_id = p.id
    JOIN perusahaan perush ON pm.perusahaan_id = perush.id
    JOIN nilai n ON pm.id = n.pengajuan_id
    WHERE pm.mahasiswa_id = ? AND pm.status = 'Disetujui'
    ORDER BY pm.tgl_pengajuan DESC LIMIT 1
");
$stmt->execute([$mahasiswa_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data nilai magang belum tersedia atau magang belum selesai.");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Nilai Magang - <?= $data['nim'] ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 2cm; }
        .kop-surat { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .kop-surat h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .kop-surat p { margin: 2px 0; font-size: 11pt; }
        h3 { text-align: center; text-transform: uppercase; margin-bottom: 30px; text-decoration: underline; }
        .data-mahasiswa { width: 100%; margin-bottom: 30px; }
        .data-mahasiswa td { padding: 5px; }
        .table-nilai { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table-nilai th, .table-nilai td { border: 1px solid #000; padding: 10px; text-align: center; }
        .table-nilai th { background-color: #f0f0f0; }
        .ttd-container { width: 100%; display: flex; justify-content: space-between; margin-top: 50px; }
        .ttd { text-align: center; width: 45%; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 10px; background: #f0f0f0; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-weight: bold;">Cetak Transkrip</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    <div class="kop-surat">
        <h1>Universitas Teknologi Nusantara</h1>
        <p>Fakultas Ilmu Komputer dan Teknologi Informasi</p>
        <p>Jl. Pendidikan No. 123, Kota Cendekia, Telp. (021) 555-1234</p>
    </div>

    <h3>Transkrip Nilai Kerja Praktik / Magang</h3>

    <table class="data-mahasiswa">
        <tr><td width="200">Nama Mahasiswa</td><td width="10">:</td><td><b><?= htmlspecialchars($data['nama_lengkap']) ?></b></td></tr>
        <tr><td>Nomor Induk Mahasiswa</td><td>:</td><td><?= htmlspecialchars($data['nim']) ?></td></tr>
        <tr><td>Program Studi</td><td>:</td><td><?= htmlspecialchars($data['nama_prodi']) ?></td></tr>
        <tr><td>Tempat Magang</td><td>:</td><td><?= htmlspecialchars($data['nama_perusahaan']) ?></td></tr>
        <tr><td>Periode Pelaksanaan</td><td>:</td><td><?= date('d M Y', strtotime($data['tgl_mulai'])) ?> s.d. <?= date('d M Y', strtotime($data['tgl_selesai'])) ?></td></tr>
    </table>

    <table class="table-nilai">
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="45%">Komponen Penilaian</th>
                <th width="15%">Bobot</th>
                <th width="15%">Nilai Angka</th>
                <th width="15%">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align: left;">Penilaian Pembimbing Lapangan / Mentor</td>
                <td>60%</td>
                <td><?= $data['nilai_pembimbing_lapangan'] ?></td>
                <td><?= ($data['nilai_pembimbing_lapangan'] * 0.6) ?></td>
            </tr>
            <tr>
                <td>2</td>
                <td style="text-align: left;">Penilaian Dosen Pembimbing (Laporan)</td>
                <td>40%</td>
                <td><?= $data['nilai_dosen_pembimbing'] ?></td>
                <td><?= ($data['nilai_dosen_pembimbing'] * 0.4) ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: right;">Total Nilai Akhir (100%)</th>
                <th><?= $data['nilai_akhir'] ?></th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: right;">Huruf Mutu</th>
                <th><?= $data['huruf_mutu'] ?></th>
            </tr>
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd">
            <p>Mengetahui,<br>Ketua Program Studi</p>
            <br><br><br>
            <p><b><u>Prof. Dr. Budi Susanto, M.Kom.</u></b><br>NIP. 19700101 199512 1 001</p>
        </div>
        <div class="ttd">
            <p>Kota Cendekia, <?= date('d F Y') ?><br>Koordinator Magang</p>
            <br><br><br>
            <p><b><u>Dr. Andi Prasetyo, M.Kom.</u></b><br>NIP. 19800515 200501 1 003</p>
        </div>
    </div>
</body>
</html>
