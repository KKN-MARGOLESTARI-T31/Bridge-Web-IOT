<?php
// config_psql.php - Workaround using psql CLI directly
// Used because PHP extensions for Postgres are missing

// 1. Load Environment Variables
$envFile = __DIR__ . '/.env';
$envVars = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
}

// 2. Get Connection String
$dbUrl = $envVars['DATABASE_URL'] ?? '';
// Ensure we have the connection string. If strict, we could die here.

// 3. Helper Functions

/**
 * Execute a query that doesn't return data (INSERT, UPDATE)
 */
function psql_execute($sql) {
    global $dbUrl;
    // Escape the SQL string for the shell
    // We wrap the SQL in double quotes for the -c flag
    // But we must be careful about double quotes inside the SQL
    
    // Strategy: Write SQL to a temp file to avoid shell escaping hell
    $tmpFile = tempnam(sys_get_temp_dir(), 'sql_');
    file_put_contents($tmpFile, $sql);
    
    $cmd = sprintf('psql "%s" -f "%s"', $dbUrl, $tmpFile);
    $output = shell_exec($cmd . " 2>&1");
    
    unlink($tmpFile); // Cleanup
    
    return $output;
}

/**
 * Fetch a single value (SELECT)
 */
function psql_fetch_value($sql) {
    global $dbUrl;
    
    // -t: tuples only (no header/footer)
    // -A: unaligned (no whitespace padding)
    
     $tmpFile = tempnam(sys_get_temp_dir(), 'sql_');
    file_put_contents($tmpFile, $sql);
    
    $cmd = sprintf('psql "%s" -t -A -f "%s"', $dbUrl, $tmpFile);
    $output = shell_exec($cmd);
    
    unlink($tmpFile);
    
    return trim($output);
}
?>
