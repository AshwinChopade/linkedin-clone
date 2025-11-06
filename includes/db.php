<?php
$host = getenv('DB_HOST') ?: 'switchback.proxy.rlwy.net';
$port = getenv('DB_PORT') ?: '57236';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'bTMzzoxOrKUYOHVJQLnavawvPwKCVDfr';
$dbname = getenv('DB_NAME') ?: 'railway';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
