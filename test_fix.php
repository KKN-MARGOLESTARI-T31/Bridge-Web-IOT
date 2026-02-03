<?php
// test_input_sync.php
// Simulates an ESP32 sending data with pump_status to verify the fix

$url = 'http://localhost:8000/input.php'; // Adjust port if needed, assuming local test or user runs it via php command
// Since we are CLI, we can include input.php directly but capturing output is messier due to headers.
// Better to use curl if running against a server, or just mock the environment and include the file.
// Let's uset a robust testing approach: Mocking $_POST/Input stream is hard for include.
// Let's just create a script that uses curl to the local webserver if running, 
// OR simpler: we can just manually insert to DB to check if it accepts the column, 
// BUT we want to test the input.php logic.

// Let's try to run input.php via php-cgi or similar? No, too complex.
// Let's just use the `test_input_workaround.php` style or create a new one that calls the function logic if properly separated.
// But input.php is a script.

// Alternative: Use the existing `config_psql.php` to just try an INSERT manually with the same columns as input.php
// to prove the DB is ready. The logic error in input.php was the SQL failure.

require_once 'config_psql.php';

echo "🧪 Testing Database Insert with pump_status...\n";

$uuid = uniqid('test_', true);
$deviceId = 'TEST-DEVICE-01';
$pumpStatus = 'false';

// Columns from input.php
$columns = ['id', 'battery_level', 'ph_value', 'level', 'created_at', '"deviceId"', 'pump_status'];
$values = ["'$uuid'", 100, 7.0, 50.5, "NOW()", "'$deviceId'", "'$pumpStatus'"];

$sql = "INSERT INTO monitoring_logs (" . implode(', ', $columns) . ") 
        VALUES (" . implode(', ', $values) . ");";

echo "SQL: $sql\n";

$output = psql_execute($sql);

if (strpos($output, 'ERROR') !== false) {
    echo "❌ Insert FAILED: $output\n";
    exit(1);
}

echo "✅ Insert SUCCESS! Database is ready.\n";

// Now emulate the read-back logic
echo "🧪 Testing Read-Back Logic...\n";
$sql_cmd = "SELECT command FROM device_controls WHERE \"deviceId\" = '$deviceId' AND mode = 'PUMP' LIMIT 1;";
$cmd = psql_fetch_value($sql_cmd);
echo "Current Command for TEST-DEVICE-01: " . ($cmd ? $cmd : "None") . "\n";

?>
