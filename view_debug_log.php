<?php
// view_debug_log.php
error_reporting(E_ALL);
$file = 'debug_payload.txt';
if (file_exists($file)) {
    echo "<h2>📜 Raw Request Logs (Last 10 Lines)</h2>";
    echo "<pre style='background:#eee; padding:10px;'>";
    // Read file into array, slice last 10, print
    $lines = file($file);
    $last = array_slice($lines, -10);
    echo htmlspecialchars(implode("", $last));
    echo "</pre>";
    echo "<br><a href='view_debug_log.php'>Refresh</a> | <a href='?clear=yes'>Clear Log</a>";
    
    if (isset($_GET['clear'])) {
        file_put_contents($file, "");
        echo "<br><i>Log cleared.</i>";
    }
} else {
    echo "Log file empty or not found yet. Wait for ESP32 data.";
}
?>
