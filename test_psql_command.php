<?php
// test_psql_command.php - Test if psql command works
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing PSQL Command Availability ===\n\n";

// 1. Check if psql is installed
echo "1. Checking if 'psql' command exists...\n";
$which_psql = shell_exec('which psql 2>&1');
if (empty($which_psql)) {
    echo "   ❌ PSQL NOT FOUND!\n";
    echo "   Install with: sudo apt-get install postgresql-client\n\n";
    die("Cannot proceed without psql.\n");
} else {
    echo "   ✓ PSQL found at: " . trim($which_psql) . "\n\n";
}

// 2. Load .env
echo "2. Loading DATABASE_URL from .env...\n";
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("   ❌ .env file not found!\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$dbUrl = '';
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, 'DATABASE_URL=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $dbUrl = trim($value);
        break;
    }
}

if (empty($dbUrl)) {
    die("   ❌ DATABASE_URL not found in .env!\n");
}
echo "   ✓ DATABASE_URL loaded\n\n";

// 3. Test database connection
echo "3. Testing database connection...\n";
$testQuery = "SELECT NOW() as current_time;";
$tmpFile = tempnam(sys_get_temp_dir(), 'sql_');
file_put_contents($tmpFile, $testQuery);

$cmd = sprintf('psql "%s" -t -A -f "%s" 2>&1', $dbUrl, $tmpFile);
$output = shell_exec($cmd);
unlink($tmpFile);

if (empty($output)) {
    echo "   ❌ No output from psql (connection may have failed)\n";
    echo "   Full command output:\n";
    var_dump($output);
} else if (strpos($output, 'ERROR') !== false || strpos($output, 'FATAL') !== false) {
    echo "   ❌ Database connection error:\n";
    echo "   " . $output . "\n";
} else {
    echo "   ✓ Connection successful! Server time: " . trim($output) . "\n\n";
}

// 4. Test INSERT query
echo "4. Testing INSERT into monitoring_logs...\n";
$testInsert = "INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) VALUES (7.5, 99, 'test', NOW());";
$tmpFile2 = tempnam(sys_get_temp_dir(), 'sql_');
file_put_contents($tmpFile2, $testInsert);

$cmd2 = sprintf('psql "%s" -f "%s" 2>&1', $dbUrl, $tmpFile2);
$output2 = shell_exec($cmd2);
unlink($tmpFile2);

echo "   Output: " . $output2 . "\n";

if (strpos($output2, 'INSERT') !== false) {
    echo "   ✓ INSERT successful!\n\n";
} else {
    echo "   ❌ INSERT may have failed\n\n";
}

// 5. Check if data exists
echo "5. Checking recent data in monitoring_logs...\n";
$checkQuery = "SELECT COUNT(*) FROM monitoring_logs WHERE location = 'test';";
$tmpFile3 = tempnam(sys_get_temp_dir(), 'sql_');
file_put_contents($tmpFile3, $checkQuery);

$cmd3 = sprintf('psql "%s" -t -A -f "%s" 2>&1', $dbUrl, $tmpFile3);
$output3 = shell_exec($cmd3);
unlink($tmpFile3);

echo "   Test records found: " . trim($output3) . "\n\n";

// 6. Check water_level_readings table
echo "6. Checking water_level_readings table structure...\n";
$checkTable = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'water_level_readings' ORDER BY ordinal_position;";
$tmpFile4 = tempnam(sys_get_temp_dir(), 'sql_');
file_put_contents($tmpFile4, $checkTable);

$cmd4 = sprintf('psql "%s" -t -A -f "%s" 2>&1', $dbUrl, $tmpFile4);
$output4 = shell_exec($cmd4);
unlink($tmpFile4);

if (!empty($output4)) {
    echo "   Columns:\n";
    $lines = explode("\n", trim($output4));
    foreach ($lines as $line) {
        if (!empty($line)) {
            echo "      - " . str_replace('|', ': ', $line) . "\n";
        }
    }
} else {
    echo "   ❌ Table may not exist or query failed\n";
}

echo "\n=== Test Complete ===\n";
?>
