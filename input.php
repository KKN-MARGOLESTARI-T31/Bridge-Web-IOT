<?php
// input.php - IoT Ingest Endpoint
// Accepts: POST (x-www-form-urlencoded or JSON)
// Params: ph, battery, location

require_once 'config.php';

// Helper to get input
function get_input($key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

// Handle JSON input as fallback
$json_input = json_decode(file_get_contents('php://input'), true);
if ($json_input) {
    $ph = $json_input['ph'] ?? $json_input['ph_value'] ?? null;
    $battery = $json_input['battery'] ?? $json_input['battery_level'] ?? null;
    $location = $json_input['location'] ?? null;
} else {
    $ph = get_input('ph');
    $battery = get_input('battery');
    $location = get_input('location');
}

// Validation
if ($ph === null || $battery === null || $location === null) {
    http_response_code(400);
    echo "Error: Missing parameters. Required: ph, battery, location.";
    exit;
}

try {
    // Insert Data (PDO is driver-agnostic for standard INSERTs)
    $sql = "INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) VALUES (:ph, :bat, :loc, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':ph' => $ph,
        ':bat' => $battery,
        ':loc' => $location
    ]);

    if ($result) {
        http_response_code(200);
        echo "OK"; // Keep response short for IoT devices
    } else {
        http_response_code(500);
        echo "Error: Insert failed.";
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
