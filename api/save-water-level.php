<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['level']) || !isset($data['location'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        $id = uniqid('wl_', true);
        
        $stmt = $pdo->prepare("
            INSERT INTO water_level_readings (id, level, location, timestamp, \"deviceId\", status)
            VALUES (:id, :level, :location, NOW(), :deviceId, :status)
        ");
        
        $stmt->execute([
            'id' => $id,
            'level' => floatval($data['level']),
            'location' => $data['location'],
            'deviceId' => $data['deviceId'] ?? null,
            'status' => $data['status'] ?? 'normal'
        ]);
        
        echo json_encode([
            'success' => true,
            'id' => $id,
            'message' => 'Data ketinggian air berhasil disimpan'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
