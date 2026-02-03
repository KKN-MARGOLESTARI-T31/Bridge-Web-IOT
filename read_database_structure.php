<?php
// read_database_structure.php - Read actual production database structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "<h1>Database Structure Analysis</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
    .sql-code { background: #f4f4f4; padding: 10px; border-left: 3px solid #4CAF50; margin: 10px 0; }
    h3 { color: #4CAF50; }
</style>";

// 1. Get monitoring_logs table structure
echo "<h2>1. monitoring_logs Table Structure</h2>";
$sql1 = "SELECT 
    column_name, 
    data_type,
    character_maximum_length,
    is_nullable,
    column_default,
    ordinal_position
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;";

$output1 = psql_execute($sql1);
echo "<pre>$output1</pre>";

// 2. Get sample data to understand the actual values
echo "<h2>2. Sample Data (Latest 3 Records)</h2>";
$sql2 = "SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 3;";
$output2 = psql_execute($sql2);
echo "<pre>$output2</pre>";

// 3. Get column names dynamically
echo "<h2>3. Column Names List</h2>";
$sql3 = "SELECT column_name FROM information_schema.columns WHERE table_name = 'monitoring_logs' ORDER BY ordinal_position;";
$output3 = psql_execute($sql3);
echo "<pre>$output3</pre>";

// 4. Get constraints
echo "<h2>4. Table Constraints</h2>";
$sql4 = "SELECT
    tc.constraint_name, 
    tc.constraint_type, 
    kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
WHERE tc.table_name = 'monitoring_logs';";
$output4 = psql_execute($sql4);
echo "<pre>$output4</pre>";

// 5. Get indexes
echo "<h2>5. Table Indexes</h2>";
$sql5 = "SELECT indexname, indexdef FROM pg_indexes WHERE tablename = 'monitoring_logs';";
$output5 = psql_execute($sql5);
echo "<pre>$output5</pre>";

// 6. Get table sample with all columns
echo "<h2>6. Table Schema Analysis</h2>";
echo "<p>Based on the structure above, here's what we found:</p>";

// Parse column names from output
echo "<div class='sql-code'>";
echo "<strong>Recommended INSERT statement format:</strong><br><br>";
echo "Based on the columns shown above, your input.php should use:<br><br>";
echo "<code>INSERT INTO monitoring_logs ([list_of_columns]) VALUES ([list_of_values]);</code>";
echo "</div>";

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li>Check the column names in section 1 and 3</li>";
echo "<li>Check sample data format in section 2</li>";
echo "<li>I will generate the correct input.php based on this structure</li>";
echo "</ol>";

echo "<p><em>Analysis completed at: " . date('Y-m-d H:i:s') . " (Jakarta time)</em></p>";
?>
