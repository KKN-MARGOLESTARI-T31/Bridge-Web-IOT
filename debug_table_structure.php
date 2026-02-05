<?php
// debug_table_structure.php
// Inspect actual table columns and types

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config_psql.php';

echo "<h2>📊 Table Inspection: monitoring_logs</h2>";

$sql = "SELECT column_name, data_type, udt_name, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'monitoring_logs' 
        ORDER BY ordinal_position;";

$tmpFile = tempnam(sys_get_temp_dir(), 'debug_struct_');
file_put_contents($tmpFile, $sql);

// Use -c for formatted output directly, or CSV
$cmd = sprintf('psql "%s" -c "%s" 2>&1', $dbUrl, $sql);

echo "<pre>" . htmlspecialchars(shell_exec($cmd)) . "</pre>";
?>
