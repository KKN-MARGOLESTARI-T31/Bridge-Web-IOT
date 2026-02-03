<?php
// debug_check_clean.php
// Clean verification of the database state

error_reporting(E_ALL);
require_once 'config_psql.php';

global $dbUrl;

// Extract Host for identification
$host = 'unknown';
if (preg_match('/@([^:\/]+)/', $dbUrl, $matches)) {
    $host = $matches[1];
}

echo json_encode([
    "check_source" => "LOCAL_SCRIPT",
    "db_host" => $host, 
    "table_check" => psql_fetch_value("SELECT to_regclass('monitoring_logs')"),
    "column_check" => psql_fetch_value("SELECT column_name FROM information_schema.columns WHERE table_name = 'monitoring_logs' AND column_name = 'pump_status'"),
    "row_count" => psql_fetch_value("SELECT count(*) FROM monitoring_logs")
], JSON_PRETTY_PRINT);
?>
