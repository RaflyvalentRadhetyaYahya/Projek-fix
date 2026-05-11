<?php
session_start();
require_once __DIR__ . '/config.php';

// Jika sudah login, arahkan ke halaman sesuai role
if (!empty($_SESSION['admin_logged_in'])) {
    \App\Http\Redirect::to('admin/beranda.php');
}
if (!empty($_SESSION['kaprodi_logged_in'])) {
    \App\Http\Redirect::to('kaprodi/dashboard.php');
}
if (!empty($_SESSION['dosen_logged_in'])) {
    \App\Http\Redirect::to('dosen/dashboard.php');
}
if (!empty($_SESSION['mahasiswa_logged_in'])) {
    \App\Http\Redirect::to('mahasiswa/beranda.php');
}
if (!empty($_SESSION['perusahaan_logged_in'])) {
    \App\Http\Redirect::to('perusahaan/beranda.php');
}

\App\Http\Redirect::to('login.php');
