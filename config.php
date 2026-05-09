<?php
// config.php
$host = 'localhost';
$dbname = 'db_magang';
$username = 'root';
$password = ''; // Default laragon root password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Note: Do not expose actual connection error details in production
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
