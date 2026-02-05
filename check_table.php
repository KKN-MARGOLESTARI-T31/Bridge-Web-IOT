<?php
// check_table.php
require_once 'config_psql.php';

$sql = "
SELECT 
    column_name, 
    data_type, 
    character_maximum_length,
    is_nullable, 
    column_default 
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;
";

echo "Checking monitoring_logs schema...\n";
// Use psql_execute (or logic similar to it but causing output)
// psql_execute returns the output.
$output = psql_execute($sql);
echo $output;
?>
