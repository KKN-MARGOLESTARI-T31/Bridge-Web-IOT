<?php
// debug_schema.php
// Diagnostic script to check why pump_status is missing

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "🔍 DIAGNOSTIC MODE: Checking Database Schema\n";
echo "============================================\n";

// 1. Check Connection Info (Masked)
global $dbUrl;
$maskedUrl = preg_replace('/(:)([^@]+)(@)/', '$1****$3', $dbUrl);
echo "Target DB URL: $maskedUrl\n\n";

// 2. Check Table Existence
echo "Checking table 'monitoring_logs'...\n";
$checkTable = psql_fetch_value("SELECT to_regclass('monitoring_logs');");
echo "Table exists? " . ($checkTable ? "✅ YES ($checkTable)" : "❌ NO") . "\n";

// 3. List All Columns
echo "\nListing Columns in 'monitoring_logs':\n";
$sql = "SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'monitoring_logs' 
        ORDER BY ordinal_position;";

$tmpFile = tempnam(sys_get_temp_dir(), 'debug_sql_');
file_put_contents($tmpFile, $sql);
$cmd = sprintf('psql "%s" -c "%s"', $dbUrl, $sql); // Use -c to get formatted table
echo shell_exec($cmd);
unlink($tmpFile);

// 4. Force Update if Missing
echo "\nAttempting to re-add 'pump_status' column...\n";
$alterSql = "ALTER TABLE monitoring_logs ADD COLUMN IF NOT EXISTS pump_status TEXT DEFAULT 'false';";
$output = psql_execute($alterSql);
echo "Output: $output\n";

// 5. Verify Again
echo "\nFinal Verification:\n";
$verifyCtx = psql_fetch_value("SELECT column_name FROM information_schema.columns WHERE table_name = 'monitoring_logs' AND column_name = 'pump_status'");
echo "Column 'pump_status' detected? " . ($verifyCtx == 'pump_status' ? "✅ YES" : "❌ NO") . "\n";
?>
