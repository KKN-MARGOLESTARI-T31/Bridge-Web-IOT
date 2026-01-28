<?php
// Standalone debug script - no dependencies

$envFile = __DIR__ . '/.env';
echo "Loading env from: $envFile\n";

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

$dbUrl = $_ENV['DATABASE_URL'] ?? '';
echo "Raw URL found: " . (empty($dbUrl) ? "NO" : "YES") . "\n";

$parsed = parse_url($dbUrl);
$host = $parsed['host'] ?? 'localhost';
$port = $parsed['port'] ?? 5432;
$dbname = ltrim($parsed['path'] ?? '/neondb', '/');
$user = $parsed['user'] ?? '';
$password = $parsed['pass'] ?? '';

echo "Checking Drivers...\n";
if (!class_exists('PDO')) {
    die("Error: PDO class not found. PHP is broken.\n");
}

$drivers = PDO::getAvailableDrivers();
echo "Available Drivers: " . implode(', ', $drivers) . "\n";

if (!in_array('pgsql', $drivers)) {
    echo "CRITICAL ERROR: 'pgsql' driver is NOT enabled in this PHP installation.\n";
    echo "You need to enable 'extension=pdo_pgsql' and 'extension=pgsql' in your php.ini\n";
    exit(1);
}

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
echo "Attempting connection to $host...\n";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "Connection Success!\n";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
?>
