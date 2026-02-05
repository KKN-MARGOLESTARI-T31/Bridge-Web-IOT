<?php
// test_esp32_simulation.php
// Script simulasikan POST request persis seperti ESP32
// Upload ke server dan jalankan di browser

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📱 ESP32 Simulation Test</h2>";

$url = "http://localhost/input.php"; // Localhost karena dijalankan di server yang sama
// Jika dijalankan di server yang berbeda, ganti dengan IP Address

// Payload persis seperti ESP32
$data = '{
    "ph": 7.25,
    "battery": 95,
    "level": 45.5,
    "location": "sawah",
    "signal": 28,
    "pump_status": false
}';

echo "<b>Target:</b> $url<br>";
echo "<b>Payload:</b> <pre>$data</pre><br>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "<hr>";
if ($httpCode == 200) {
    echo "<h3 style='color:green;'>✅ HTTP 200 OK</h3>";
} else {
    echo "<h3 style='color:red;'>❌ HTTP $httpCode</h3>";
}

echo "<b>Response Body:</b><br>";
echo "<pre style='background:#ddd; padding:10px;'>" . htmlspecialchars($response) . "</pre>";

if ($error) {
    echo "<b>Curl Error:</b> $error<br>";
}
?>
