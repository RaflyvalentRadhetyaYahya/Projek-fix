<?php
// setup_db.php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('database.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    echo "Database setup successful.\n";
} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>
