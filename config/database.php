<?php
// KasiTrade Database Configuration
// PDO with prepared statements for SQL injection prevention

$host = 'localhost';
$dbname = 'kasitrade';
$username = 'root';      // Changed for production
$password = '';          // Changed for production
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die("System unavailable. Try again later.");
}
?>