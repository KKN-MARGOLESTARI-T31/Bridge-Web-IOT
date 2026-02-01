<?php
// check_php_drivers.php - Check PHP extension status
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Configuration Check</h1>";
echo "<h2>PHP Version</h2>";
echo "<p>" . phpversion() . "</p>";

echo "<h2>Available PDO Drivers</h2>";
$drivers = PDO::getAvailableDrivers();
echo "<pre>";
print_r($drivers);
echo "</pre>";

echo "<h2>PostgreSQL Extensions</h2>";
echo "<p>pdo_pgsql: " . (extension_loaded('pdo_pgsql') ? '✅ LOADED' : '❌ NOT LOADED') . "</p>";
echo "<p>pgsql: " . (extension_loaded('pgsql') ? '✅ LOADED' : '❌ NOT LOADED') . "</p>";

echo "<h2>MySQL Extensions</h2>";
echo "<p>pdo_mysql: " . (extension_loaded('pdo_mysql') ? '✅ LOADED' : '❌ NOT LOADED') . "</p>";
echo "<p>mysqli: " . (extension_loaded('mysqli') ? '✅ LOADED' : '❌ NOT LOADED') . "</p>";

echo "<h2>Environment Check</h2>";
$envFile = __DIR__ . '/.env';
echo "<p>.env file exists: " . (file_exists($envFile) ? '✅ YES' : '❌ NO') . "</p>";

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
    
    $db_url = $_ENV['DATABASE_URL'] ?? '';
    if ($db_url) {
        echo "<p>DATABASE_URL: " . (strlen($db_url) > 50 ? substr($db_url, 0, 50) . '...' : $db_url) . "</p>";
        $parsed = parse_url($db_url);
        echo "<p>Database Type: <strong>" . ($parsed['scheme'] ?? 'unknown') . "</strong></p>";
    }
}

echo "<h2>Database Connection Test</h2>";
try {
    require_once 'config.php';
    if (isset($pdo)) {
        echo "<p style='color: green; font-weight: bold;'>✅ DATABASE CONNECTED SUCCESSFULLY!</p>";
        
        // Test query
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "<p>Database Version: " . $version . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ DATABASE CONNECTION FAILED</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
