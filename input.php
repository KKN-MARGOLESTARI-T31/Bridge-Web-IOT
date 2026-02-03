<?php
/**
 * input.php - SMART SYNC LOGIC (ADAPTED FOR CLI/NEON)
 * Mengatasi Case 1, 2, & 3 dengan Time-Based Priority
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');

require_once 'config_psql.php';

// --- FUNGSI UUID ---
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

// 1. TERIMA DATA
$json_input = json_decode(file_get_contents('php://input'), true);

if ($json_input) {
    $ph = $json_input['ph'] ?? null;
    $battery = $json_input['battery'] ?? null;
    $location = $json_input['location'] ?? 'sawah';
    $level = $json_input['level'] ?? 0;
    $signal = $json_input['signal'] ?? 0;
    $temperature = $json_input['temperature'] ?? null;
    $deviceId = $json_input['deviceId'] ?? 'ESP32-DEFAULT';
    $hardware_pump_status = $json_input['pump_status'] ?? false; 
} else {
    $ph = $_POST['ph'] ?? null;
    $battery = $_POST['battery'] ?? null;
    $location = $_POST['location'] ?? 'sawah';
    $level = $_POST['level'] ?? 0;
    $signal = $_POST['signal'] ?? 0;
    $temperature = $_POST['temperature'] ?? null;
    $deviceId = $_POST['deviceId'] ?? 'ESP32-DEFAULT';
    $hardware_pump_status = $_POST['pump_status'] ?? 'false';
}

// Konversi Status Hardware menjadi string 'ON'/'OFF' untuk Logika
$hw_status_str = ($hardware_pump_status === true || $hardware_pump_status === 'true' || $hardware_pump_status == 1) ? 'ON' : 'OFF';
$pump_val_log = strtolower($hw_status_str) === 'on' ? 'true' : 'false';

// Validasi & Sanitasi
if ($ph === null) { 
    http_response_code(400); 
    echo json_encode(['error' => "Error: Data pH Kosong"]); 
    exit; 
}

$ph_clean = (float)$ph;
$battery_clean = (float)$battery;
$level_clean = (float)$level;
$signal_clean = (int)$signal;
$location_clean = preg_replace('/[^a-zA-Z0-9\-\_]/', '', $location);
$deviceId_clean = preg_replace('/[^a-zA-Z0-9\-\_]/', '', $deviceId);

// --- PROSES UTAMA ---
$new_id = gen_uuid();

try {
    // 1. SIMPAN LOG (HISTORY)
    // Mencoba menyimpan dengan pump_status (karena sudah difix).
    // Jika gagal, akan error 500 dan ditangkap catch.
    
    $columns = ['id', 'ph_value', 'battery_level', 'location', 'signal_strength', 'created_at', '"deviceId"', 'pump_status'];
    $values = ["'$new_id'", $ph_clean, $battery_clean, "'$location_clean'", $signal_clean, "NOW()", "'$deviceId_clean'", "'$pump_val_log'"];
    
    if ($level !== null) { $columns[] = 'level'; $values[] = $level_clean; }
    if ($temperature !== null) { $columns[] = 'temperature'; $values[] = (float)$temperature; }

    $sql = "INSERT INTO monitoring_logs (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");";
    
    $output = psql_execute($sql);
    
    // Self-Healing Logic (Mini version)
    if (strpos($output, 'pump_status') !== false && strpos($output, 'does not exist') !== false) {
        // Fallback: Insert without pump_status if server schema is stubborn
        $columns_fallback = array_diff($columns, ['pump_status']);
        $values_fallback = array_diff_key($values, array_flip(array_keys($columns, 'pump_status'))); // rough logic
        // Easier: Just try insert without pump_status
         $sql_fallback = "INSERT INTO monitoring_logs (id, ph_value, battery_level, location, signal_strength, created_at, \"deviceId\") 
                          VALUES ('$new_id', $ph_clean, $battery_clean, '$location_clean', $signal_clean, NOW(), '$deviceId_clean');";
         psql_execute($sql_fallback);
    }

    // 2. LOGIKA SINKRONISASI KONTROL (THE CORE FIX)
    
    // A. Ambil status Web terakhir
    // NOTE: Schema uses "deviceId" and "updatedAt" (camelCase with quotes)
    $sql_cmd = "SELECT command, EXTRACT(EPOCH FROM (NOW() - \"updatedAt\")) as age_seconds 
                FROM device_controls 
                WHERE \"deviceId\" = '$deviceId_clean' 
                ORDER BY \"updatedAt\" DESC LIMIT 1;";
    
    $result = psql_fetch_row($sql_cmd);
    
    // Jika belum ada data kontrol, default OFF
    $web_command = $result['command'] ?? 'OFF';
    // Gunakan floatval untuk memastikan age_seconds berupa angka
    $command_age = isset($result['age_seconds']) ? floatval($result['age_seconds']) : 99999; 

    $final_command_to_esp = "OFF";
    $sync_mode = "UNKNOWN";

    // B. PENENTUAN SIAPA YANG MENANG
    // Ambang batas: 15 Detik. 
    // Jika tombol Web diklik < 15 detik lalu, Web Menang. 
    // Jika lebih lama, Hardware Menang.

    if ($command_age < 15) {
        // --- WEB PRIORITY ---
        // User baru saja klik tombol di Web (< 15s). Abaikan status hardware.
        $final_command_to_esp = $web_command;
        $sync_mode = "WEB_PRIORITY";
    } else {
        // --- HARDWARE PRIORITY ---
        // Web diam (> 15s). Hardware adalah Boss.
        $final_command_to_esp = $hw_status_str;
        $sync_mode = "HARDWARE_PRIORITY";

        // SYNC BACK: Jika Database beda dengan Hardware, Update Database!
        if ($web_command !== $hw_status_str) {
            
            // Generate IDs
            $control_id = 'pump_' . $deviceId_clean;
            
            // TRICK: Set updated_at mundur 20 detik
            // Supaya update ini tidak dianggap "User Activity"
            $safe_timestamp = "NOW() - INTERVAL '20 seconds'";
            
            // Upsert with safe timestamp
            $sql_upsert = "INSERT INTO device_controls (id, \"deviceId\", mode, command, \"updatedAt\", \"createdAt\", \"actionBy\", reason) 
                           VALUES ('$control_id', '$deviceId_clean', 'PUMP', '$hw_status_str', $safe_timestamp, NOW(), 'HARDWARE', 'Sync from Device')
                           ON CONFLICT (\"deviceId\", mode) 
                           DO UPDATE SET 
                               command = '$hw_status_str', 
                               \"updatedAt\" = $safe_timestamp,
                               \"actionBy\" = 'HARDWARE';";
            
            psql_execute($sql_upsert);
        }
    }

    // 3. KIRIM BALASAN KE ESP32
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "success", 
        "command" => $final_command_to_esp, // Perintah final untuk Relay
        "sync_mode" => $sync_mode,
        "hardware_report" => $hw_status_str,
        "web_record" => $web_command,
        "age" => $command_age
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server Error", "details" => $e->getMessage()]);
}
?>
