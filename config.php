<?php
// config.php - Database Connection (PDO Version)
// Supports both MySQL and PostgreSQL (Neon)

// Load .env manually if not using a library
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

// Determine Driver
$db_url = $_ENV['DATABASE_URL'] ?? ''; // For Neon/Postgres provided styles
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? 'iot_database';
$db_driver = $_ENV['DB_DRIVER'] ?? 'mysql'; // 'mysql' or 'pgsql'

// If DATABASE_URL is present (standard for Neon/Fly), parse it
if ($db_url) {
    $parsed = parse_url($db_url);
    $db_driver = $parsed['scheme'] ?? 'pgsql';
    $db_host = $parsed['host'] ?? '';
    $db_port = $parsed['port'] ?? ($db_driver === 'mysql' ? 3306 : 5432);
    $db_user = $parsed['user'] ?? '';
    $db_pass = $parsed['pass'] ?? '';
    $db_name = ltrim($parsed['path'] ?? '', '/');
    
    // Construct PDO DSN
    $dsn = "$db_driver:host=$db_host;port=$db_port;dbname=$db_name";
    
    // Parse and include query parameters (sslmode, channel_binding, etc.)
    if ($db_driver === 'pgsql') {
        $sslmode = 'require'; // default
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query_params);
            $sslmode = $query_params['sslmode'] ?? 'require';
            // Note: channel_binding is handled by connection, not DSN
        }
        $dsn .= ";sslmode=$sslmode";
    }
} else {
    // Construct PDO DSN from individual fields
    $dsn = "$db_driver:host=$db_host;dbname=$db_name";
    if ($db_driver === 'mysql') {
        $dsn .= ";charset=utf8mb4";
    } elseif ($db_driver === 'pgsql') {
        $dsn .= ";sslmode=require";
    }
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Return simple error for API, complex for Debug
    if (php_sapi_name() === 'cli' || isset($_GET['debug'])) {
        die("Connection failed: " . $e->getMessage());
    } else {
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed']));
    }
}
?>
