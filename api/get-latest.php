<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    // Ambil data pH terbaru
    $phStmt = $pdo->query("
        SELECT * FROM ph_readings 
        ORDER BY timestamp DESC 
        LIMIT 10
    ");
    
    // Ambil data water level terbaru
    $wlStmt = $pdo->query("
        SELECT * FROM water_level_readings 
        ORDER BY timestamp DESC 
        LIMIT 10
    ");
    
    echo json_encode([
        'success' => true,
        'phReadings' => $phStmt->fetchAll(),
        'waterLevelReadings' => $wlStmt->fetchAll()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
