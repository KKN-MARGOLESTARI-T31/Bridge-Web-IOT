&lt;?php
// setup_db_neon.php - Setup Database Tables for Neon PostgreSQL
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== IoT Database Setup for Neon PostgreSQL ===\n\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ Error: File .env tidak ditemukan!\n" .
        "   Silakan copy .env.example menjadi .env dan isi DATABASE_URL\n" .
        "   Lihat SETUP_NEON.md untuk panduan lengkap.\n");
}

// Parse .env file
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Get DATABASE_URL
$db_url = $_ENV['DATABASE_URL'] ?? '';
if (empty($db_url)) {
    die("❌ Error: DATABASE_URL tidak ditemukan di file .env\n" .
        "   Silakan isi DATABASE_URL dengan connection string dari Neon.\n" .
        "   Lihat SETUP_NEON.md untuk panduan lengkap.\n");
}

echo "✓ File .env ditemukan\n";
echo "✓ DATABASE_URL terdeteksi\n\n";

// Parse DATABASE_URL
$parsed = parse_url($db_url);
$db_driver = $parsed['scheme'] ?? 'postgresql';
$db_host = $parsed['host'] ?? '';
$db_port = $parsed['port'] ?? 5432;
$db_user = $parsed['user'] ?? '';
$db_pass = $parsed['pass'] ?? '';
$db_name = ltrim($parsed['path'] ?? '', '/');

// Extract query parameters
$query_params = [];
if (isset($parsed['query'])) {
    parse_str($parsed['query'], $query_params);
}
$sslmode = $query_params['sslmode'] ?? 'require';

echo "Connecting to:\n";
echo "  Host: $db_host\n";
echo "  Port: $db_port\n";
echo "  Database: $db_name\n";
echo "  User: $db_user\n";
echo "  SSL Mode: $sslmode\n\n";

// Construct PDO DSN
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=$sslmode";

try {
    echo "Connecting to database...\n";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "✓ Connected successfully!\n\n";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n\n" .
        "Troubleshooting:\n" .
        "  1. Periksa kembali DATABASE_URL di file .env\n" .
        "  2. Pastikan connection string benar dari Neon dashboard\n" .
        "  3. Pastikan ada ?sslmode=require di akhir URL\n" .
        "  4. Install ekstensi pgsql: sudo apt-get install php-pgsql\n");
}

// Create tables
echo "Creating tables...\n\n";

// Table 1: ph_readings
echo "1. Creating table: ph_readings\n";
$sql_ph = "
CREATE TABLE IF NOT EXISTS ph_readings (
    id SERIAL PRIMARY KEY,
    ph_value DECIMAL(4,2) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_id VARCHAR(50),
    notes TEXT
);
";

try {
    $pdo->exec($sql_ph);
    echo "   ✓ Table ph_readings created\n";
} catch (PDOException $e) {
    echo "   ❌ Error creating ph_readings: " . $e->getMessage() . "\n";
}

// Table 2: water_level_readings
echo "2. Creating table: water_level_readings\n";
$sql_water = "
CREATE TABLE IF NOT EXISTS water_level_readings (
    id SERIAL PRIMARY KEY,
    water_level INTEGER NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_id VARCHAR(50),
    notes TEXT
);
";

try {
    $pdo->exec($sql_water);
    echo "   ✓ Table water_level_readings created\n";
} catch (PDOException $e) {
    echo "   ❌ Error creating water_level_readings: " . $e->getMessage() . "\n";
}

// Create indexes for better performance
echo "\n3. Creating indexes for performance\n";

try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ph_timestamp ON ph_readings(timestamp DESC);");
    echo "   ✓ Index on ph_readings.timestamp created\n";
} catch (PDOException $e) {
    echo "   ⚠ Index already exists or error: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_water_timestamp ON water_level_readings(timestamp DESC);");
    echo "   ✓ Index on water_level_readings.timestamp created\n";
} catch (PDOException $e) {
    echo "   ⚠ Index already exists or error: " . $e->getMessage() . "\n";
}

// Verify tables
echo "\n4. Verifying tables...\n";

$tables = $pdo->query("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_type = 'BASE TABLE'
")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "   ✓ Found table: $table\n";
}

// Show table structure
echo "\n5. Table structures:\n\n";

echo "   📋 ph_readings:\n";
$columns_ph = $pdo->query("
    SELECT column_name, data_type, character_maximum_length 
    FROM information_schema.columns 
    WHERE table_name = 'ph_readings'
    ORDER BY ordinal_position
")->fetchAll();

foreach ($columns_ph as $col) {
    $length = $col['character_maximum_length'] ? "({$col['character_maximum_length']})" : "";
    echo "      - {$col['column_name']}: {$col['data_type']}$length\n";
}

echo "\n   📋 water_level_readings:\n";
$columns_water = $pdo->query("
    SELECT column_name, data_type, character_maximum_length 
    FROM information_schema.columns 
    WHERE table_name = 'water_level_readings'
    ORDER BY ordinal_position
")->fetchAll();

foreach ($columns_water as $col) {
    $length = $col['character_maximum_length'] ? "({$col['character_maximum_length']})" : "";
    echo "      - {$col['column_name']}: {$col['data_type']}$length\n";
}

// Get row counts
$count_ph = $pdo->query("SELECT COUNT(*) FROM ph_readings")->fetchColumn();
$count_water = $pdo->query("SELECT COUNT(*) FROM water_level_readings")->fetchColumn();

echo "\n6. Current data:\n";
echo "   - ph_readings: $count_ph rows\n";
echo "   - water_level_readings: $count_water rows\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Setup completed successfully!\n\n";
echo "Next steps:\n";
echo "  1. Test connection: php debug_db.php\n";
echo "  2. Seed sample data: php seed.php\n";
echo "  3. Test API: curl http://localhost/api/save-ph.php\n";
echo "\n";
?>
