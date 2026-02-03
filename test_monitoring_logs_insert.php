<?php
// test_monitoring_logs_insert.php - Test INSERT to monitoring_logs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "<h2>Testing monitoring_logs INSERT</h2>";

// Test data
$ph_clean = 6.5;
$battery_clean = 75.0;
$location_clean = 'test_insert';

// Try INSERT
echo "<h3>1. Attempting INSERT...</h3>";
$sql1 = "INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) VALUES ($ph_clean, $battery_clean, '$location_clean', NOW());";
echo "<p><strong>SQL:</strong> <code>$sql1</code></p>";

$output1 = psql_execute($sql1);
echo "<p><strong>Output:</strong></p>";
echo "<pre>$output1</pre>";

// Check if successful
if (strpos($output1, 'INSERT') !== false) {
    echo "<p style='color:green; font-weight:bold;'>✓ INSERT SUCCESSFUL!</p>";
} else if (strpos($output1, 'ERROR') !== false) {
    echo "<p style='color:red; font-weight:bold;'>✗ INSERT FAILED - ERROR DETECTED</p>";
} else {
    echo "<p style='color:orange; font-weight:bold;'>⚠ UNKNOWN RESULT</p>";
}

// Verify by querying
echo "<h3>2. Verifying Data...</h3>";
$sql2 = "SELECT * FROM monitoring_logs WHERE location = 'test_insert' ORDER BY created_at DESC LIMIT 3;";
$output2 = psql_execute($sql2);
echo "<pre>$output2</pre>";

// Check table structure
echo "<h3>3. Checking Table Structure...</h3>";
$sql3 = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'monitoring_logs' ORDER BY ordinal_position;";
$output3 = psql_execute($sql3);
echo "<pre>$output3</pre>";

echo "<hr>";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
