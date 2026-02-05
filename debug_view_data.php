<?php
// debug_view_data.php
// Script untuk melihat data Aktual di Database (Last 5 Rows)
// FIX: Menggunakan file temp untuk query agar quotes aman

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config_psql.php';

echo "<h2>🕵️ Intip Data Database (Last 5 Logs)</h2>";

// 1. Cek Monitoring Logs
$sql_logs = "SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 5";
$tmpFile = tempnam(sys_get_temp_dir(), 'sql_view_logs_');
file_put_contents($tmpFile, $sql_logs);

$cmd = sprintf('psql "%s" -c "%s" 2>&1', $dbUrl, $sql_logs); // Logs biasanya aman tanpa quotes neko-neko
// Use file approach for consistency actually, but logs schema is simple safe for shell usually.
// Let's use the file approach for controls which IS risky.

echo "<h3>Tabel: monitoring_logs</h3>";
// Manual exec logs
echo "<pre style='background:#f0f0f0; padding:10px;'>" . htmlspecialchars(psql_execute($sql_logs)) . "</pre>";
unlink($tmpFile);

// 2. Cek Device Controls (The one that failed previously)
// Quotes "updatedAt" is critical
$sql_ctrl = "SELECT * FROM device_controls ORDER BY \"updatedAt\" DESC LIMIT 5";
$tmpFileCtrl = tempnam(sys_get_temp_dir(), 'sql_view_ctrl_');
file_put_contents($tmpFileCtrl, $sql_ctrl);

// Pass file to psql directly
$cmd_ctrl = sprintf('psql "%s" -f "%s" 2>&1', $dbUrl, $tmpFileCtrl);

echo "<h3>Tabel: device_controls</h3>";
echo "<pre style='background:#e0e0ff; padding:10px;'>" . htmlspecialchars(shell_exec($cmd_ctrl)) . "</pre>";

unlink($tmpFileCtrl);
?>
