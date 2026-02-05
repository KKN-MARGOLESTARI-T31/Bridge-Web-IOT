<?php
// test_pump_on.php
// Simulasi: "Jika Tombol Web ditekan ON"
// Script ini akan mengirim data ke input.php dengan pump_status = true

error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = "http://localhost/input.php"; // Target Server Lokal

// Payload: "Web memerintahkan ON"
$data = '{
    "ph": 7.0,
    "battery": 100,
    "level": 50,
    "location": "sawah",
    "signal": 30,
    "pump_status": true 
}';

echo "<h2>🔘 Simulasi Tombol Web: ON</h2>";
echo "<b>Target:</b> $url<br>";
echo "<b>Payload:</b> <pre>$data</pre><br>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<hr>";
if ($httpCode == 200) {
    echo "<h3 style='color:green;'>✅ Request Berhasil Dikirim</h3>";
} else {
    echo "<h3 style='color:red;'>❌ Gagal ($httpCode)</h3>";
}

echo "<b>Respon Server:</b><br>";
echo "<pre style='background:#ddd; padding:10px;'>" . htmlspecialchars($response) . "</pre>";
echo "<br>";
echo "👉 Sekarang cek `debug_view_data.php`. Harusnya baris paling atas `pump_status` = <b>true</b>.";
?>
