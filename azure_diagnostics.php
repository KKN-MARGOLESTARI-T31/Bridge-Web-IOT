<?php
/**
 * Azure Server Diagnostic Script
 * Run this via SSH: php azure_diagnostics.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== AZURE VM DATABASE DIAGNOSTIC ===\n\n";

// 1. PHP Version
echo "1. PHP VERSION\n";
echo "   Version: " . phpversion() . "\n";
echo "   OS: " . PHP_OS . "\n\n";

// 2. Check PDO Drivers
echo "2. PDO DRIVERS\n";
$drivers = PDO::getAvailableDrivers();
echo "   Available: " . implode(', ', $drivers) . "\n";
echo "   MySQL: " . (in_array('mysql', $drivers) ? '✅' : '❌') . "\n";
echo "   PostgreSQL: " . (in_array('pgsql', $drivers) ? '✅' : '❌') . "\n\n";

// 3. Check Extensions
echo "3. PHP EXTENSIONS\n";
echo "   pdo_mysql: " . (extension_loaded('pdo_mysql') ? '✅' : '❌') . "\n";
echo "   pdo_pgsql: " . (extension_loaded('pdo_pgsql') ? '✅' : '❌') . "\n";
echo "   mysqli: " . (extension_loaded('mysqli') ? '✅' : '❌') . "\n";
echo "   pgsql: " . (extension_loaded('pgsql') ? '✅' : '❌') . "\n\n";

// 4. Check .env file
echo "4. ENVIRONMENT FILE CHECK\n";
$envFile = __DIR__ . '/.env';
echo "   Path: $envFile\n";
echo "   Exists: " . (file_exists($envFile) ? '✅' : '❌') . "\n";
if (file_exists($envFile)) {
    echo "   Readable: " . (is_readable($envFile) ? '✅' : '❌') . "\n";
    echo "   Size: " . filesize($envFile) . " bytes\n";
    echo "   Permissions: " . substr(sprintf('%o', fileperms($envFile)), -4) . "\n";
    
    // Check DATABASE_URL
    $content = file_get_contents($envFile);
    if (strpos($content, 'DATABASE_URL') !== false) {
        echo "   DATABASE_URL: ✅ Found\n";
        preg_match('/DATABASE_URL=(.+)/', $content, $matches);
        if (isset($matches[1])) {
            $url = trim($matches[1]);
            $parsed = parse_url($url);
            echo "   DB Type: " . ($parsed['scheme'] ?? 'unknown') . "\n";
            echo "   DB Host: " . ($parsed['host'] ?? 'unknown') . "\n";
        }
    } else {
        echo "   DATABASE_URL: ❌ Not found\n";
    }
} else {
    echo "   ⚠️  .env file does not exist!\n";
}
echo "\n";

// 5. Test config.php loading
echo "5. CONFIG.PHP TEST\n";
try {
    require_once __DIR__ . '/config.php';
    if (isset($pdo)) {
        echo "   PDO Object: ✅ Created\n";
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        echo "   Driver: $driver\n";
        
        // Test query
        try {
            if ($driver === 'pgsql') {
                $stmt = $pdo->query("SELECT version()");
            } else {
                $stmt = $pdo->query("SELECT VERSION()");
            }
            $version = $stmt->fetchColumn();
            echo "   Connection: ✅ SUCCESSFUL\n";
            echo "   Database Version: " . substr($version, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "   Query Test: ❌ FAILED\n";
            echo "   Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   PDO Object: ❌ Not created\n";
    }
} catch (Exception $e) {
    echo "   Config Load: ❌ FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Network Test (for Neon)
echo "6. NETWORK CONNECTIVITY\n";
$neonHost = 'ep-snowy-butterfly-a12pm14d-pooler.ap-southeast-1.aws.neon.tech';
echo "   Testing connection to: $neonHost\n";
$socket = @fsockopen($neonHost, 5432, $errno, $errstr, 5);
if ($socket) {
    echo "   Port 5432: ✅ Reachable\n";
    fclose($socket);
} else {
    echo "   Port 5432: ❌ Not reachable\n";
    echo "   Error: $errstr ($errno)\n";
}
echo "\n";

// 7. Web Server Info
echo "7. WEB SERVER INFO\n";
echo "   SAPI: " . php_sapi_name() . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "   Script Filename: " . (__FILE__) . "\n";
echo "   Current User: " . get_current_user() . "\n";
if (function_exists('posix_getpwuid')) {
    $processUser = posix_getpwuid(posix_geteuid());
    echo "   Process User: " . $processUser['name'] . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nRecommendations:\n";

// Generate recommendations
$issues = [];
if (!in_array('pgsql', $drivers)) {
    $issues[] = "- Install PostgreSQL PDO driver: sudo apt install php-pgsql";
}
if (!file_exists($envFile)) {
    $issues[] = "- Create .env file with DATABASE_URL";
}
if (file_exists($envFile) && !is_readable($envFile)) {
    $issues[] = "- Fix .env permissions: sudo chmod 644 .env";
}

if (empty($issues)) {
    echo "✅ All checks passed! Database should be working.\n";
} else {
    foreach ($issues as $issue) {
        echo "$issue\n";
    }
}
?>
