<?php
// setup_db.php - Standalone version
// 1. Manual parsing .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'iot_database';

echo "Connecting to MySQL Server ($host)...\n";

try {
    // Connect WITHOUT dbname first
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Connected. Creating database '$dbname'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    
    echo "Switching to '$dbname'...\n";
    $pdo->exec("USE `$dbname`");
    
    echo "Creating tables...\n";
    $sql = "
        CREATE TABLE IF NOT EXISTS monitoring_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ph_value DECIMAL(4,2) NOT NULL,
            battery_level DECIMAL(5,2) NOT NULL,
            location VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    $pdo->exec($sql);
    
    echo "Success! Database and Table created.\n";
    
} catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage() . "\n");
}
?>
