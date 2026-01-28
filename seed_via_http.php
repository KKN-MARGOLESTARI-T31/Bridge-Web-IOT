<?php
// seed_via_http.php - Seeding via HTTP Request to input.php
// This script simulates an ESP32 sending data

// Configuration
$target_url = "http://localhost/web-iot-receiver/input.php"; // Change this to your actual URL if different
$total_requests = 10;
$delay_between_requests = 1000; // milliseconds (optional to not flood)

// Check if URL is reachable
$ch = curl_init($target_url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code == 0 || $code == 404) {
    echo "Warning: Target URL $target_url might not be reachable (HTTP Code: $code).\n";
    echo "Make sure your local server is running (e.g., via 'php -S localhost:80')\n\n";
}
curl_close($ch);

echo "Starting seeding to $target_url...\n";
$locations = ['sawah', 'sumur', 'kolam'];

for ($i = 1; $i <= $total_requests; $i++) {
    $ph = rand(60, 85) / 10;
    $battery = rand(500, 1000) / 10;
    $location = $locations[array_rand($locations)];
    
    $postData = [
        'ph' => $ph,
        'battery' => $battery,
        'location' => $location
    ];
    
    // Simulate cURL request like ESP32
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "[$i/$total_requests] SUCCESS: pH=$ph, Bat=$battery, Loc=$location => Response: $response\n";
    } else {
        echo "[$i/$total_requests] FAILED: HTTP $httpCode. Error: $error\n";
        echo "Response: $response\n";
    }
    
    usleep($delay_between_requests * 1000);
}

echo "\nSeeding Loop Finished.\n";
echo "NOTE: If this failed, ensure your web server is running and has PostgreSQL drivers enabled.\n";
?>
