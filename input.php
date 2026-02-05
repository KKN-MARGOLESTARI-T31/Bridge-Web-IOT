<?php
/**
 * input.php - FINAL SCHEMA MATCH (deviceId Fixed)
 * Version: v2.0 (Fix ID NULL Error)
 * Updated: 2026-02-05
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');

require_once 'config_psql.php';

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
$raw_input = file_get_contents('php://input');
file_put_contents('debug_payload.txt', date('Y-m-d H:i:s') . " - " . $raw_input . PHP_EOL, FILE_APPEND);
$json_input = json_decode($raw_input, true);

if ($json_input) {
    $ph = $json_input['ph'] ?? null;
    $battery = $json_input['battery'] ?? null;
    $location = $json_input['location'] ?? 'sawah'; 
    $level = $json_input['level'] ?? 0;
    $signal = $json_input['signal'] ?? 0;
    $temperature = $json_input['temperature'] ?? 0;
    $hardware_pump_status = $json_input['pump_status'] ?? false; 
} else {
    $ph = $_POST['ph'] ?? null;
    $battery = $_POST['battery'] ?? null;
    $location = $_POST['location'] ?? 'sawah';
    $level = $_POST['level'] ?? 0;
    $signal = $_POST['signal'] ?? 0;
    $temperature = $_POST['temperature'] ?? 0;
    $hardware_pump_status = $_POST['pump_status'] ?? 'false';
}

// Konversi & Sanitasi
$hw_status_str = ($hardware_pump_status === true || $hardware_pump_status === 'true' || $hardware_pump_status == 1) ? 'ON' : 'OFF';
$pump_status_text = ($hw_status_str === 'ON') ? 'true' : 'false'; // Sesuaikan tipe data TEXT di DB

if ($ph === null) { http_response_code(400); echo "Error: Data pH Kosong"; exit; }

$ph_clean = (float)$ph;
$battery_clean = (float)$battery;
$level_clean = (float)$level;
$signal_clean = (int)$signal;
$temp_clean = (float)$temperature;
$location_clean = preg_replace('/[^a-zA-Z0-9\-\_]/', '', $location); 
if(empty($location_clean)) $location_clean = 'sawah'; 

// --- DATABASE MAPPING ---
// Input 'location' --> DB Column 'deviceId'
$deviceId = $location_clean;

$new_id = gen_uuid();

try {
    // ---------------------------------------------------------
    // 1. INSERT LOGS (FIXED COLUMN NAMES)
    // ---------------------------------------------------------
    // Schema Check Result: 
    // id, ph_value, battery_level, level, temperature, signal_strength, created_at, deviceId, pump_status
    
    // Perhatikan: "deviceId" (camelCase) wajib pakai tanda kutip dua.
    $sql_log = "INSERT INTO monitoring_logs 
                (id, ph_value, battery_level, level, temperature, signal_strength, \"deviceId\", pump_status, created_at) 
                VALUES 
                ('$new_id', $ph_clean, $battery_clean, $level_clean, $temp_clean, $signal_clean, '$deviceId', '$pump_status_text', NOW());";
                
    $log_result = psql_execute($sql_log);
    
    // Debugging jika insert gagal (opsional, bisa dilihat di output JSON)
    if (strpos($log_result, 'ERROR') !== false) {
        $log_error = $log_result;
    } else {
        $log_error = null;
    }

    // ---------------------------------------------------------
    // 2. LOGIKA KONTROL (SYNC)
    // ---------------------------------------------------------
    
    // A. Ambil status Web terakhir
    $sql_cmd = "SELECT command, EXTRACT(EPOCH FROM (NOW() - \"updatedAt\")) as age_seconds 
                FROM device_controls WHERE \"deviceId\" = '$deviceId' 
                ORDER BY \"updatedAt\" DESC LIMIT 1;";
    
    $result = psql_fetch_row($sql_cmd);
    
    $web_command = $result['command'] ?? 'OFF';
    $command_age = floatval($result['age_seconds'] ?? 999999); 

    $final_command_to_esp = "OFF";
    $sync_mode = "UNKNOWN";

    // B. PENENTUAN MENANG/KALAH
    if ($command_age < 15) {
        // --- WEB PRIORITY (< 15 Detik) ---
        $final_command_to_esp = $web_command;
        $sync_mode = "WEB_PRIORITY";
    } else {
        // --- HARDWARE PRIORITY ---
        $final_command_to_esp = $hw_status_str;
        $sync_mode = "HARDWARE_PRIORITY";

        // C. UPDATE DATABASE JIKA BEDA
        if ($web_command !== $hw_status_str) {
            
            $control_id = 'ctrl_' . $deviceId . '_' . time();

            // Query UPSERT (Fix "deviceId" & "updatedAt")
            $sql_upsert = "INSERT INTO device_controls 
                           (id, \"deviceId\", mode, command, \"updatedAt\", \"createdAt\", \"actionBy\", reason) 
                           VALUES 
                           ('$control_id', '$deviceId', 'PUMP', '$hw_status_str', NOW(), NOW(), 'HARDWARE', 'Sync from Device')
                           ON CONFLICT (\"deviceId\", mode) 
                           DO UPDATE SET 
                               command = '$hw_status_str', 
                               \"updatedAt\" = NOW(),
                               \"actionBy\" = 'HARDWARE';";
            
            psql_execute($sql_upsert);
        }
    }

    // 3. KIRIM BALASAN KE ESP32
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "success", 
        "command" => $final_command_to_esp,
        "sync_mode" => $sync_mode,
        "hardware_report" => $hw_status_str,
        "db_insert_error" => $log_error, // Tampilkan error insert jika ada
        "mapped_device_id" => $deviceId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server Error", "details" => $e->getMessage()]);
}

// Helper fetch (Pastikan tidak duplikat di config_psql.php)
if (!function_exists('psql_fetch_row')) {
    function psql_fetch_row($query) {
        // Fallback simple parser jika fungsi ini belum ada
        global $dbUrl;
        $tmp = tempnam(sys_get_temp_dir(), 'sql_');
        file_put_contents($tmp, $query);
        $cmd = sprintf('psql "%s" --csv -f "%s"', $dbUrl, $tmp);
        $out = shell_exec($cmd);
        unlink($tmp);
        if(!$out) return null;
        $lines = explode("\n", trim($out));
        if(count($lines)<2) return null;
        return array_combine(str_getcsv($lines[0]), str_getcsv($lines[1]));
    }
}
?>
