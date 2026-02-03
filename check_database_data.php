<?php
// check_database_data.php - Quick check (Updated for simplified schema)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "<h2>Database Data Check</h2>";

// Check monitoring_logs
echo "<h3>1. Latest Monitoring Logs (Last 10)</h3>";
$sql1 = "SELECT id, ph_value, battery_level, level, created_at FROM monitoring_logs ORDER BY created_at DESC LIMIT 10;";
$output1 = psql_execute($sql1);
echo "<pre>$output1</pre>";

// Count total records
echo "<h3>2. Record Count</h3>";
$sql2 = "SELECT COUNT(*) FROM monitoring_logs;";
$count = psql_fetch_value($sql2);
echo "Total Monitoring Logs: <strong>$count</strong> records<br>";

// Check latest timestamp
echo "<h3>3. Latest Data Timestamp</h3>";
$sql3 = "SELECT MAX(created_at) FROM monitoring_logs;";
$latest = psql_fetch_value($sql3);
echo "Last data received: <strong>$latest</strong><br>";

// Show table structure
echo "<h3>4. Table Structure</h3>";
$sql4 = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'monitoring_logs' ORDER BY ordinal_position;";
$output4 = psql_execute($sql4);
echo "<pre>$output4</pre>";

echo "<hr>";
echo "<p><em>Last checked: " . date('Y-m-d H:i:s') . " (Server Time)</em></p>";
?>
