<?php
// check_table_csv.php
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

$tmpFile = tempnam(sys_get_temp_dir(), 'sql_');
file_put_contents($tmpFile, $sql);

// FORCE --csv output
$cmd = sprintf('psql "%s" --csv -f "%s"', $dbUrl, $tmpFile);
$output = shell_exec($cmd);
unlink($tmpFile);

echo $output;
?>
