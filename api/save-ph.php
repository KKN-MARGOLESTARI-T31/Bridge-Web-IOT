<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input
    if (!isset($data['value']) || !isset($data['location'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Generate CUID-like ID (simplified version)
        $id = uniqid('ph_', true);
        
        $stmt = $pdo->prepare("
            INSERT INTO ph_readings (id, value, location, timestamp, \"deviceId\", temperature)
            VALUES (:id, :value, :location, NOW(), :deviceId, :temperature)
        ");
        
        $stmt->execute([
            'id' => $id,
            'value' => floatval($data['value']),
            'location' => $data['location'],
            'deviceId' => $data['deviceId'] ?? null,
            'temperature' => isset($data['temperature']) ? floatval($data['temperature']) : null
        ]);
        
        echo json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'Data pH berhasil disimpan'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
