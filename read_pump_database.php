<?php
// read_pump_database.php - Comprehensive pump control database analysis
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "<h1>Pump Control Database Analysis</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { border-collapse: collapse; margin: 20px 0; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .sql-code { background: #f4f4f4; padding: 10px; border-left: 3px solid #4CAF50; margin: 10px 0; font-family: monospace; }
    h2 { color: #4CAF50; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
</style>";

// 1. Check if device_controls table exists
echo "<div class='section'>";
echo "<h2>1. Table Existence Check</h2>";
$sql1 = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'device_controls'
);";
$exists = psql_fetch_value($sql1);
if ($exists === 't' || $exists === 'true') {
    echo "<p class='success'>✓ Table 'device_controls' EXISTS</p>";
} else {
    echo "<p class='error'>✗ Table 'device_controls' DOES NOT EXIST</p>";
    echo "<p>Run <code>setup_device_controls.sql</code> to create it.</p>";
    echo "</div>";
    exit;
}
echo "</div>";

// 2. Get table structure
echo "<div class='section'>";
echo "<h2>2. Table Structure (device_controls)</h2>";
$sql2 = "SELECT 
    column_name, 
    data_type,
    character_maximum_length,
    is_nullable,
    column_default,
    ordinal_position
FROM information_schema.columns 
WHERE table_name = 'device_controls' 
ORDER BY ordinal_position;";
$output2 = psql_execute($sql2);
echo "<pre>$output2</pre>";
echo "</div>";

// 3. Get all pump commands (history)
echo "<div class='section'>";
echo "<h2>3. All Pump Commands (Complete History)</h2>";
$sql3 = 'SELECT * FROM device_controls ORDER BY "updatedAt" DESC;';
$output3 = psql_execute($sql3);
echo "<pre>$output3</pre>";

// Count total commands
$sql3b = "SELECT COUNT(*) FROM device_controls;";
$count = psql_fetch_value($sql3b);
echo "<p><strong>Total Commands in History: $count</strong></p>";
echo "</div>";

// 4. Get latest pump command
echo "<div class='section'>";
echo "<h2>4. Current Pump Status (Latest Command)</h2>";
$sql4 = 'SELECT id, command, "updatedAt" FROM device_controls ORDER BY "updatedAt" DESC LIMIT 1;';
$output4 = psql_execute($sql4);

if (empty(trim($output4)) || strpos($output4, '(0 rows)') !== false) {
    echo "<p class='warning'>⚠ No pump commands found! Table is EMPTY.</p>";
    echo "<p>Insert a default command:</p>";
    echo "<div class='sql-code'>INSERT INTO device_controls (command, \"updatedAt\") VALUES ('POMPA_OFF', NOW());</div>";
} else {
    echo "<pre>$output4</pre>";
    
    // Parse command
    $cmd = psql_fetch_value('SELECT command FROM device_controls ORDER BY "updatedAt" DESC LIMIT 1;');
    if ($cmd === 'POMPA_ON') {
        echo "<p class='success'>Current Status: ✓ POMPA ON</p>";
    } else if ($cmd === 'POMPA_OFF') {
        echo "<p class='error'>Current Status: ✗ POMPA OFF</p>";
    } else {
        echo "<p>Current Status: $cmd</p>";
    }
}
echo "</div>";

// 5. Command distribution
echo "<div class='section'>";
echo "<h2>5. Command Distribution</h2>";
$sql5 = "SELECT command, COUNT(*) as count FROM device_controls GROUP BY command ORDER BY count DESC;";
$output5 = psql_execute($sql5);
echo "<pre>$output5</pre>";
echo "</div>";

// 6. Recent commands timeline
echo "<div class='section'>";
echo "<h2>6. Recent Commands (Last 10)</h2>";
$sql6 = 'SELECT id, command, "updatedAt" FROM device_controls ORDER BY "updatedAt" DESC LIMIT 10;';
$output6 = psql_execute($sql6);
echo "<pre>$output6</pre>";
echo "</div>";

// 7. Indexes
echo "<div class='section'>";
echo "<h2>7. Table Indexes</h2>";
$sql7 = "SELECT indexname, indexdef FROM pg_indexes WHERE tablename = 'device_controls';";
$output7 = psql_execute($sql7);
echo "<pre>$output7</pre>";
echo "</div>";

// 8. Quick Actions
echo "<div class='section'>";
echo "<h2>8. Quick Actions (SQL Commands)</h2>";

echo "<h3>Turn Pump ON:</h3>";
echo "<div class='sql-code'>INSERT INTO device_controls (command, \"updatedAt\") VALUES ('POMPA_ON', NOW());</div>";

echo "<h3>Turn Pump OFF:</h3>";
echo "<div class='sql-code'>INSERT INTO device_controls (command, \"updatedAt\") VALUES ('POMPA_OFF', NOW());</div>";

echo "<h3>Clear old history (keep last 100):</h3>";
echo "<div class='sql-code'>DELETE FROM device_controls WHERE id NOT IN (SELECT id FROM device_controls ORDER BY \"updatedAt\" DESC LIMIT 100);</div>";

echo "</div>";

echo "<hr>";
echo "<p><em>Analysis completed at: " . date('Y-m-d H:i:s') . " (Jakarta time)</em></p>";
?>
