<?php
// apply_migration.php
// Script to apply the add_pump_status_column.sql migration

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "🔧 Applying migration: add_pump_status_column.sql...\n";

$sqlFile = __DIR__ . '/add_pump_status_column.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: Migration file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Split by semicolon to execute statements individually if needed, 
// but psql -f usually handles it. config_psql usually calls psql -f.
// However, psql_execute expects a string, not a file path usually, 
// OR it writes the string to a temp file.
// Let's rely on psql_execute which takes a SQL string.

echo "Executing SQL...\n";
$output = psql_execute($sql);

echo "Output:\n$output\n";

if (strpos($output, 'ERROR') !== false) {
    echo "❌ Migration FAILED.\n";
    exit(1);
}

echo "✅ Migration executed successfully (or column already existed).\n";
echo "Verifying column existence...\n";

$verifySql = "SELECT column_name FROM information_schema.columns 
              WHERE table_name = 'monitoring_logs' AND column_name = 'pump_status';";

$result = psql_fetch_value($verifySql);

if ($result === 'pump_status') {
    echo "✅ Column 'pump_status' CONFIRMED in monitoring_logs.\n";
} else {
    echo "⚠️ Warning: Column verification returned: '$result' (Expected 'pump_status')\n";
}
?>
