<?php
/**
 * Detailed Connection Test - Shows actual PDO error
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DETAILED CONNECTION TEST ===\n\n";

// Load .env
$envFile = '/var/www/html/.env';
echo "1. Loading .env file...\n";

if (!file_exists($envFile)) {
    die("ERROR: .env file not found at $envFile\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$db_url = $_ENV['DATABASE_URL'] ?? '';
if (!$db_url) {
    die("ERROR: DATABASE_URL not found in .env\n");
}

echo "✅ DATABASE_URL loaded\n\n";

// Parse URL
echo "2. Parsing DATABASE_URL...\n";
$parsed = parse_url($db_url);

echo "   Scheme: " . ($parsed['scheme'] ?? 'MISSING') . "\n";
echo "   Host: " . ($parsed['host'] ?? 'MISSING') . "\n";
echo "   Port: " . ($parsed['port'] ?? 'default') . "\n";
echo "   User: " . ($parsed['user'] ?? 'MISSING') . "\n";
echo "   Pass: " . (isset($parsed['pass']) ? '***REDACTED***' : 'MISSING') . "\n";
echo "   DB Name: " . (ltrim($parsed['path'] ?? '', '/')) . "\n";

if (isset($parsed['query'])) {
    parse_str($parsed['query'], $query_params);
    echo "   SSL Mode: " . ($query_params['sslmode'] ?? 'not set') . "\n";
    echo "   Channel Binding: " . ($query_params['channel_binding'] ?? 'not set') . "\n";
}
echo "\n";

// Build DSN
echo "3. Building PDO DSN...\n";
$db_driver = $parsed['scheme'] ?? 'pgsql';
$db_host = $parsed['host'] ?? '';
$db_port = $parsed['port'] ?? 5432;
$db_user = $parsed['user'] ?? '';
$db_pass = $parsed['pass'] ?? '';
$db_name = ltrim($parsed['path'] ?? '', '/');

$dsn = "$db_driver:host=$db_host;port=$db_port;dbname=$db_name";

// Add SSL mode for PostgreSQL
if ($db_driver === 'pgsql') {
    $sslmode = 'require';
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $query_params);
        $sslmode = $query_params['sslmode'] ?? 'require';
    }
    $dsn .= ";sslmode=$sslmode";
}

echo "   DSN: $dsn\n\n";

// Attempt connection
echo "4. Attempting PDO connection...\n";
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✅ CONNECTION SUCCESSFUL!\n\n";
    
    // Test query
    echo "5. Testing query...\n";
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "   Database Version: " . substr($version, 0, 80) . "\n";
    echo "\n✅ ALL TESTS PASSED!\n";
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED!\n\n";
    echo "ERROR DETAILS:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    // Common error hints
    $msg = $e->getMessage();
    if (strpos($msg, 'could not find driver') !== false) {
        echo "HINT: PostgreSQL PDO driver not installed\n";
        echo "FIX: sudo apt install php8.1-pgsql\n";
    } elseif (strpos($msg, 'Connection refused') !== false) {
        echo "HINT: Cannot reach database server\n";
        echo "FIX: Check firewall/network settings\n";
    } elseif (strpos($msg, 'password authentication failed') !== false) {
        echo "HINT: Wrong username or password\n";
        echo "FIX: Verify credentials in .env\n";
    } elseif (strpos($msg, 'SSL') !== false || strpos($msg, 'sslmode') !== false) {
        echo "HINT: SSL connection issue\n";
        echo "FIX: Check sslmode parameter or firewall\n";
    }
}
?>
